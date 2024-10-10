<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://example.com
 * @since             1.0.0
 * @package           Source_File_Interceptor
 *
 * @wordpress-plugin
 * Plugin Name:       source-file-interceptor
 * Plugin URI:        https://example.com
 * Description:       This plugin intercepts and logs outgoing requests, checking domain reputation using VirusTotal API and triggers code analysis for new plugins.
 * Version:           1.2.0
 * Author:            James O'Brien
 * Author URI:        https://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       source-file-interceptor
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SOURCE_FILE_INTERCEPTOR_VERSION', '1.2.0' );

function activate_source_file_interceptor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-source-file-interceptor-activator.php';
	Source_File_Interceptor_Activator::activate();
}

function deactivate_source_file_interceptor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-source-file-interceptor-deactivator.php';
	Source_File_Interceptor_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_source_file_interceptor' );
register_deactivation_hook( __FILE__, 'deactivate_source_file_interceptor' );

require plugin_dir_path( __FILE__ ) . 'includes/class-source-file-interceptor.php';

function run_source_file_interceptor() {
	$plugin = new Source_File_Interceptor();
	$plugin->run();
}
run_source_file_interceptor();

function check_domain_security($domain) {
    $api_key = get_option('source_file_interceptor_virustotal_api_key', '');
    if (empty($api_key)) {
        error_log("VirusTotal API key is not set");
        return array('score' => 0, 'message' => 'VirusTotal API key is not set');
    }

    $url = "https://www.virustotal.com/api/v3/domains/" . $domain;
    $args = array(
        'headers' => array(
            'x-apikey' => $api_key
        )
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        error_log("Error checking domain security: " . $response->get_error_message());
        return array('score' => 0, 'message' => 'Error checking domain security');
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['data']['attributes']['reputation'])) {
        $reputation = $data['data']['attributes']['reputation'];
        $score = max(0, min(100, $reputation + 100)); // Convert -100 to 100 scale to 0 to 100 scale
        $message = "Domain reputation score: $reputation";
    } else {
        $score = 50; // Default score if reputation is not available
        $message = "Unable to determine domain reputation";
    }

    error_log("Security check for domain {$domain}: Score {$score}, Message: {$message}");
    return array('score' => $score, 'message' => $message);
}

function inject_source_ctx($parsed_args, $url) {
	if (str_starts_with($url, $_ENV['ELASTICSEARCH_URL'])) {
		return $parsed_args;
	}

    $e = new \Exception();
	$traceString = $e->getTraceAsString();
	$trace = preg_split("/\r\n|\n|\r/", $traceString);
	foreach ($trace as $frame) {
		$parts = explode(' ', $frame);
		if (count($parts) < 3) {
			continue;
		}

		$func = explode('(', $parts[2])[0];
		if (str_starts_with($func, 'wp_remote')) {
			error_log(var_export($trace, true));
			$src = $parts[1];
			$srcParts = explode('(', $src);
			$srcFile = $srcParts[0];
			$srcLine = explode(')', $srcParts[1])[0];
			header("X-WP-Remote-Call: {$func}");
			header("X-User-Agent: {$parsed_args['user-agent']}");
			header("X-Request-URI: {$url}");
			header("X-Request-Method: {$_SERVER['REQUEST_METHOD']}");
			header("X-Source-File: {$srcFile}");
			header("X-Source-Line: {$srcLine}");

			preg_match("/(?P<asset_type>(mu\-)?plugins|themes)\/(?P<name>[^\/]+)/", $srcFile, $matches);
			$assetType = $matches['asset_type'];
			$assetName = $matches['name'];

			$dt = new DateTime();
			$iso8601 = $dt->format(DateTime::ATOM);
			$parsedURL = parse_url($url);
			$queryParams = array();
			parse_str($parsedURL['query'], $queryParams);

            // Check the security score of the domain
            $security_check = check_domain_security($parsedURL['host']);

			$payload = array(
				'plugin_name' => $assetName,
				'wp_remote_func' => $func,
				'request_uri' => $url,
				'host' => $parsedURL['host'],
				'path' => $parsedURL['path'],
				'query_params' => $queryParams,
				'method' => $parsed_args['method'],
				'source_file' => $srcFile,
				'source_line' => $srcLine,
				'asset_type' => $assetType,
				'timestamp' => $iso8601,
                'security_score' => $security_check['score'],
                'security_message' => $security_check['message']
			);

			error_log("Detected call to {$func} in file {$srcFile}, line {$srcLine}");

			$res = wp_remote_post(
				"{$_ENV['ELASTICSEARCH_URL']}/plugin-{$assetName}-logs/_doc",
				array (
					'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
					'body' => json_encode($payload),
					'method' => 'POST',
					'data_format' => 'body',
				)
			);

			error_log("ElasticSearch POST request result: " . var_export($res, true));
		}
	}

	return $parsed_args;
}

add_filter('http_request_args', 'inject_source_ctx', 999, 3);

function trigger_code_analysis($plugin) {
    // Get the path of the main plugin file
    $plugin_path = WP_PLUGIN_DIR . '/' . $plugin;

    // Trigger the code analysis service
    $analysis_service_url = 'http://code-analysis:5000/analyze';
    $response = wp_remote_post($analysis_service_url, array(
        'body' => json_encode(array('plugin_path' => $plugin_path)),
        'headers' => array('Content-Type' => 'application/json'),
    ));

    if (is_wp_error($response)) {
        error_log('Error triggering code analysis: ' . $response->get_error_message());
    } else {
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        error_log('Code analysis result: ' . print_r($result, true));

        // Extract plugin name from the path
        $plugin_name = basename(dirname($plugin_path));

        // Add plugin name to the result
        $result['plugin_name'] = $plugin_name;

        // Store the analysis result in Elasticsearch
        $es_response = wp_remote_post(
            "{$_ENV['ELASTICSEARCH_URL']}/plugin-{$plugin_name}-analysis/_doc",
            array(
                'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
                'body' => json_encode($result),
                'method' => 'POST',
                'data_format' => 'body',
            )
        );

        if (is_wp_error($es_response)) {
            error_log('Error storing code analysis result in Elasticsearch: ' . $es_response->get_error_message());
        }
    }
}

add_action('activated_plugin', 'trigger_code_analysis');
