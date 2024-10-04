<?php
header('Content-Type: application/json');

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
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}