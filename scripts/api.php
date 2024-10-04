<?php
header('Content-Type: application/json');

// Function to get container status
function get_container_status($container_name) {
    $output = shell_exec("docker inspect -f '{{.State.Status}}' $container_name 2>&1");
    return trim($output);
}

// Get WordPress and WooCommerce versions
require_once('/var/www/html/wp-load.php');
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
        array('name' => 'WordPress', 'status' => get_container_status('wordpress')),
        array('name' => 'Test Tools', 'status' => get_container_status('test-tools'))
    ),
    'wordpress' => array(
        'version' => $wp_version,
        'woocommerce_version' => $wc_version
    ),
    'plugins' => $installed_plugins
);

echo json_encode($response);