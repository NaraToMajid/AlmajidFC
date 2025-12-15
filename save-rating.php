<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$user = $data['user'] ?? '';
$rating = $data['rating'] ?? 0;
$comment = $data['comment'] ?? '';

if (empty($user) || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$ratings = json_decode(file_get_contents('ratings.json'), true) ?? [];

// Cek apakah user sudah memberikan rating
$found = false;
foreach ($ratings as &$r) {
    if ($r['user'] === $user) {
        $r['rating'] = $rating;
        $r['comment'] = $comment;
        $r['timestamp'] = date('Y-m-d H:i:s');
        $r['date'] = date('d/m/Y');
        $found = true;
        break;
    }
}

if (!$found) {
    $ratings[] = [
        'user' => $user,
        'rating' => $rating,
        'comment' => $comment,
        'timestamp' => date('Y-m-d H:i:s'),
        'date' => date('d/m/Y')
    ];
}

file_put_contents('ratings.json', json_encode($ratings, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'message' => 'Rating berhasil disimpan',
    'data' => [
        'user' => $user,
        'rating' => $rating,
        'comment' => $comment
    ]
]);
?>
