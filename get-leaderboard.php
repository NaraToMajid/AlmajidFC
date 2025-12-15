<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$leaderboard = json_decode(file_get_contents('leaderboard.json'), true) ?? [];

// Urutkan berdasarkan poin
usort($leaderboard, function($a, $b) {
    return $b['points'] - $a['points'];
});

echo json_encode([
    'success' => true,
    'message' => 'Leaderboard berhasil diambil',
    'data' => array_slice($leaderboard, 0, 10) // Ambil top 10
]);
?>
