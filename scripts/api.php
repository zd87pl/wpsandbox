<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to log messages
function log_message($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, '/var/www/html/debug.log');
}

log_message("API request received: " . $_SERVER['REQUEST_URI']);

// Function to get container status
function get_container_status($container_name) {
    $output = shell_exec("docker inspect -f '{{.State.Status}}' $container_name 2>&1");
    return trim($output);
}

// Load WordPress
require_once('/var/www/html/wp-load.php');

// Get WordPress and WooCommerce versions
global $wp_version;
$wc_version = get_option('woocommerce_version');

// Get installed plugins
$plugins = get_plugins();
$installed_plugins = array();
foreach ($plugins as $plugin_file => $plugin_data) {
    $installed_plugins[] = array(
        'name' => $plugin_data['Name'],
        'version' => $plugin_data['Version']
    );
}

$response = array(
    'containers' => array(
        array('name' => 'WordPress', 'status' => get_container_status('fakewoo-wordpress-1')),
        array('name' => 'Database', 'status' => get_container_status('fakewoo-db-1')),
        array('name' => 'Test Tools', 'status' => get_container_status('fakewoo-test-tools-1')),
        array('name' => 'Frontend', 'status' => get_container_status('fakewoo-frontend-1'))
    ),
    'wordpress' => array(
        'version' => $wp_version,
        'woocommerce_version' => $wc_version,
        'url' => 'http://localhost:8080',
        'admin_url' => 'http://localhost:8080/wp-admin'
    ),
    'plugins' => $installed_plugins
);

// Handle plugin search
if (isset($_GET['search_plugin'])) {
    log_message("Plugin search requested for: " . $_GET['search_plugin']);
    $search = $_GET['search_plugin'];
    $api_url = "http://api.wordpress.org/plugins/info/1.2/";
    $args = array(
        'action' => 'query_plugins',
        'request' => serialize((object) array(
            'search' => $search,
            'per_page' => 10,
            'fields' => array('name', 'version', 'author', 'short_description', 'slug')
        ))
    );
    log_message("Sending request to WordPress.org API");
    $response = wp_remote_post($api_url, array('body' => $args));
    if (is_wp_error($response)) {
        log_message("Error from WordPress.org API: " . $response->get_error_message());
        echo json_encode(array('error' => $response->get_error_message()));
    } else {
        $plugins = unserialize(wp_remote_retrieve_body($response));
        log_message("Received response from WordPress.org API: " . print_r($plugins, true));
        echo json_encode($plugins->plugins);
    }
} else {
    echo json_encode($response);
}

log_message("API request completed");