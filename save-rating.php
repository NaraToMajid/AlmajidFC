<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user']) || !isset($input['rating'])) {
    response(false, 'Missing required fields');
}

$user = trim($input['user']);
$rating = intval($input['rating']);
$comment = isset($input['comment']) ? trim($input['comment']) : '';
$timestamp = date('Y-m-d H:i:s');

if ($rating < 1 || $rating > 5) {
    response(false, 'Rating must be between 1 and 5');
}

if (USE_JSON) {
    // Gunakan file JSON
    $ratings = getJsonData('ratings.json');
    
    // Cek apakah user sudah memberikan rating sebelumnya
    $existingIndex = -1;
    foreach ($ratings as $index => $r) {
        if ($r['user'] === $user) {
            $existingIndex = $index;
            break;
        }
    }
    
    $ratingData = [
        'user' => $user,
        'rating' => $rating,
        'comment' => $comment,
        'timestamp' => $timestamp,
        'date' => date('d/m/Y')
    ];
    
    if ($existingIndex !== -1) {
        // Update rating yang sudah ada
        $ratings[$existingIndex] = $ratingData;
    } else {
        // Tambah rating baru
        $ratings[] = $ratingData;
    }
    
    if (saveJsonData('ratings.json', $ratings)) {
        response(true, 'Rating saved successfully', $ratingData);
    } else {
        response(false, 'Failed to save rating');
    }
} else {
    // Gunakan database MySQL
    $conn = getDBConnection();
    
    // Cek apakah user sudah memberikan rating
    $stmt = $conn->prepare("SELECT id FROM ratings WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update rating yang ada
        $stmt = $conn->prepare("UPDATE ratings SET rating = ?, comment = ?, timestamp = ? WHERE username = ?");
        $stmt->bind_param("isss", $rating, $comment, $timestamp, $user);
    } else {
        // Tambah rating baru
        $stmt = $conn->prepare("INSERT INTO ratings (username, rating, comment, timestamp) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $user, $rating, $comment, $timestamp);
    }
    
    if ($stmt->execute()) {
        response(true, 'Rating saved successfully', [
            'user' => $user,
            'rating' => $rating,
            'comment' => $comment,
            'timestamp' => $timestamp
        ]);
    } else {
        response(false, 'Database error: ' . $conn->error);
    }
    
    $stmt->close();
    $conn->close();
}
?>
