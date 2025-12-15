<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$user = $data['user'] ?? '';
$message = $data['message'] ?? '';

if (empty($user) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$chat = json_decode(file_get_contents('chat.json'), true) ?? [];

$chat[] = [
    'user' => $user,
    'message' => $message,
    'timestamp' => date('Y-m-d H:i:s'),
    'time' => date('H:i')
];

// Simpan hanya 100 pesan terakhir
if (count($chat) > 100) {
    $chat = array_slice($chat, -100);
}

file_put_contents('chat.json', json_encode($chat, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'message' => 'Pesan berhasil dikirim',
    'data' => [
        'user' => $user,
        'message' => $message,
        'time' => date('H:i')
    ]
]);
?>
