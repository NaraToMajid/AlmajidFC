<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$sessionId = $data['sessionId'] ?? '';
$username = $data['username'] ?? '';
$timestamp = $data['timestamp'] ?? time();

if (empty($sessionId)) {
    echo json_encode(['success' => false, 'message' => 'Session ID tidak valid']);
    exit;
}

$activeUsers = json_decode(file_get_contents('active-users.json'), true) ?? [];

// Update atau tambah user aktif
$activeUsers[$sessionId] = [
    'username' => $username,
    'timestamp' => $timestamp,
    'last_seen' => date('Y-m-d H:i:s')
];

// Hapus user yang tidak aktif dalam 5 menit
$currentTime = time();
foreach ($activeUsers as $id => $user) {
    if ($currentTime - $user['timestamp'] > 300) { // 5 menit
        unset($activeUsers[$id]);
    }
}

file_put_contents('active-users.json', json_encode($activeUsers, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'message' => 'Status aktif diperbarui',
    'data' => [
        'activeUsers' => count($activeUsers)
    ]
]);
?>
