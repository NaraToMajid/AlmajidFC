<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Konfigurasi database (jika menggunakan database)
define('DB_HOST', 'localhost');
define('DB_USER', 'username'); // Ganti dengan username database Anda
define('DB_PASS', 'password'); // Ganti dengan password database Anda
define('DB_NAME', 'almajid_db'); // Ganti dengan nama database Anda

// Atau gunakan file JSON (untuk hosting tanpa database)
define('USE_JSON', true); // Set true untuk menggunakan file JSON
define('JSON_PATH', dirname(__FILE__) . '/data/');

// Buat folder data jika belum ada
if (!file_exists(JSON_PATH)) {
    mkdir(JSON_PATH, 0755, true);
}

function getDBConnection() {
    if (USE_JSON) return null;
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed']));
    }
    return $conn;
}

function getJsonData($filename) {
    $filepath = JSON_PATH . $filename;
    if (file_exists($filepath)) {
        return json_decode(file_get_contents($filepath), true) ?: [];
    }
    return [];
}

function saveJsonData($filename, $data) {
    $filepath = JSON_PATH . $filename;
    return file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
}

function response($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
?>
