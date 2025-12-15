<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$chat = json_decode(file_get_contents('chat.json'), true) ?? [];

echo json_encode([
    'success' => true,
    'message' => 'Chat berhasil diambil',
    'data' => $chat
]);
?>
