<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$points = $data['points'] ?? 0;
$level = $data['level'] ?? 1;

if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username tidak valid']);
    exit;
}

$leaderboard = json_decode(file_get_contents('leaderboard.json'), true) ?? [];

// Cek apakah user sudah ada
$found = false;
foreach ($leaderboard as &$entry) {
    if ($entry['username'] === $username) {
        // Update jika poin lebih tinggi
        if ($points > $entry['points']) {
            $entry['points'] = $points;
            $entry['level'] = $level;
            $entry['last_updated'] = date('Y-m-d H:i:s');
        }
        $found = true;
        break;
    }
}

if (!$found) {
    $leaderboard[] = [
        'username' => $username,
        'points' => $points,
        'level' => $level,
        'last_updated' => date('Y-m-d H:i:s')
    ];
}

// Urutkan berdasarkan poin (descending)
usort($leaderboard, function($a, $b) {
    return $b['points'] - $a['points'];
});

// Simpan hanya top 100
$leaderboard = array_slice($leaderboard, 0, 100);

file_put_contents('leaderboard.json', json_encode($leaderboard, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'message' => 'Leaderboard berhasil diperbarui',
    'data' => $leaderboard
]);
?>
