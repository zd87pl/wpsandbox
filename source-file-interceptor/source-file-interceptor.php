<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://example.com
 * @since             1.0.0
 * @package           Source_File_Interceptor
 *
 * @wordpress-plugin
 * Plugin Name:       source-file-interceptor
 * Plugin URI:        https://example.com
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
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

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SOURCE_FILE_INTERCEPTOR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-source-file-interceptor-activator.php
 */
function activate_source_file_interceptor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-source-file-interceptor-activator.php';
	Source_File_Interceptor_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-source-file-interceptor-deactivator.php
 */
function deactivate_source_file_interceptor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-source-file-interceptor-deactivator.php';
	Source_File_Interceptor_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_source_file_interceptor' );
register_deactivation_hook( __FILE__, 'deactivate_source_file_interceptor' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-source-file-interceptor.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_source_file_interceptor() {

	$plugin = new Source_File_Interceptor();
	$plugin->run();

}
run_source_file_interceptor();

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
			$payload = array(
				'wp_remote_func' => $func,
				'request_uri' => $url,
				'host' => $parsedURL['host'],
				'path' => $parsedURL['path'],
				'query_params' => $queryParams,
				'method' => $parsed_args['method'],
				'source_file' => $srcFile,
				'source_line' => $srcLine,
				'asset_type' => $assetType,
				'asset_name' => $assetName,
				'timestamp' => $iso8601,
			);

			error_log("Detected call to {$func} in file {$srcFile}, line {$srcLine}");

			$res = wp_remote_post(
				"{$_ENV['ELASTICSEARCH_URL']}/api-calls/_doc",
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
