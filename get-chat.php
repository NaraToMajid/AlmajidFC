<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (USE_JSON) {
    $chat = getJsonData('chat.json');
    
    // Format waktu
    foreach ($chat as &$message) {
        if (isset($message['timestamp'])) {
            $message['time'] = date('H:i', strtotime($message['timestamp']));
        }
    }
    
    response(true, 'Chat messages retrieved', $chat);
} else {
    $conn = getDBConnection();
    $result = $conn->query("SELECT username as user, message, timestamp, DATE_FORMAT(timestamp, '%H:%i') as time FROM chat ORDER BY timestamp DESC LIMIT 50");
    
    $chat = [];
    while ($row = $result->fetch_assoc()) {
        $chat[] = $row;
    }
    
    // Balik urutan agar yang terbaru di bawah
    $chat = array_reverse($chat);
    
    response(true, 'Chat messages retrieved', $chat);
    $conn->close();
}
?>
