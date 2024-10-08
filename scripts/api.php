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
if (!file_exists('/var/www/html/wp-load.php')) {
    log_message("WordPress not found at expected location");
    echo json_encode(['error' => 'WordPress not found']);
    exit;
}

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

// Handles plugin installation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        log_message("Plugin installation requested via file upload");
        $uploadDir = '/var/www/html/';
        $uploadFile = $uploadDir . 'plugins.csv';

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            // Execute the install_plugins.sh script
            $output = shell_exec("/usr/local/bin/install_plugins.sh $uploadFile 2>&1");
            echo json_encode(['success' => true, 'message' => 'Plugins installation started', 'output' => $output]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        }
    } elseif (isset($_POST['plugin'])) {
        // Install single plugin using WP-CLI
        log_message("Plugin installation requested via plugin name");
        $plugin = $_POST['plugin'];
        $output = shell_exec("wp plugin install " . $plugin . " --activate --allow-root 2>&1");
        log_message("Plugin installation output: " . $output);
        echo json_encode(['success' => true, 'message' => 'Plugin installation started', 'output' => $output]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or plugin specified']);
    }
} else {
    // Handle plugin search
    if (isset($_GET['search_plugin'])) {
        log_message("Plugin search requested for: " . $_GET['search_plugin']);
        $search = $_GET['search_plugin'];
        $api_url = add_query_arg(
            array(
                'action' => 'query_plugins',
                'search' => $search,
                'per_page' => 10
            ),
            'https://api.wordpress.org/plugins/info/1.2/'
        );
        log_message("Sending request to WordPress.org API");
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            log_message("Error from WordPress.org API: " . $response->get_error_message());
            echo json_encode(array('error' => $response->get_error_message()));
        } else {
            $body = wp_remote_retrieve_body($response);
            log_message("Received response from WordPress.org API: " . $body);
            $plugins = json_decode($body);
            if ($plugins === false) {
                log_message("Failed to unserialize response");
                echo json_encode(array('error' => 'Failed to process API response'));
            } else {
                echo json_encode($plugins->plugins);
            }
        }
    } elseif (isset($_GET['plugin_to_install'])) {
        // Install single plugin using WP-CLI
        log_message("Plugin installation requested via plugin name");
        $plugin = $_GET['plugin_to_install'];
        $output = shell_exec("wp plugin install " . $plugin . " --activate --allow-root 2>&1");
        log_message("Plugin installation output: " . $output);
        echo json_encode(['success' => true, 'message' => 'Plugin installation completed!', 'output' => $output]);
    } else {
        echo json_encode($response);
    }
}

log_message("API request completed");