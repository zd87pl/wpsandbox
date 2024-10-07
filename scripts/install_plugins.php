<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
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
        $plugin = $_POST['plugin'];
        // Install single plugin using WP-CLI
        $output = shell_exec("wp plugin install " . $plugin . " --activate --allow-root 2>&1");
        echo json_encode(['success' => true, 'message' => 'Plugin installation started', 'output' => $output]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or plugin specified']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}