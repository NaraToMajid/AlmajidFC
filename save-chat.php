<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user']) || !isset($input['message'])) {
    response(false, 'Missing required fields');
}

$user = trim($input['user']);
$message = trim($input['message']);
$timestamp = date('Y-m-d H:i:s');
$time_display = date('H:i');

if (empty($message)) {
    response(false, 'Message cannot be empty');
}

if (strlen($message) > 500) {
    response(false, 'Message too long (max 500 characters)');
}

if (USE_JSON) {
    $chat = getJsonData('chat.json');
    
    // Simpan hanya 100 pesan terakhir
    $chat[] = [
        'user' => $user,
        'message' => $message,
        'timestamp' => $timestamp,
        'time' => $time_display
    ];
    
    // Simpan hanya 100 pesan terakhir
    if (count($chat) > 100) {
        $chat = array_slice($chat, -100);
    }
    
    if (saveJsonData('chat.json', $chat)) {
        response(true, 'Message sent', [
            'user' => $user,
            'message' => $message,
            'time' => $time_display
        ]);
    } else {
        response(false, 'Failed to send message');
    }
} else {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO chat (username, message, timestamp) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user, $message, $timestamp);
    
    if ($stmt->execute()) {
        response(true, 'Message sent', [
            'user' => $user,
            'message' => $message,
            'time' => $time_display
        ]);
    } else {
        response(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->close();
    
    // Hapus pesan lama (lebih dari 100)
    $conn->query("DELETE FROM chat WHERE id NOT IN (SELECT id FROM (SELECT id FROM chat ORDER BY timestamp DESC LIMIT 100) AS temp)");
    
    $conn->close();
}
?>
