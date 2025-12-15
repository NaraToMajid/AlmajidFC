<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['points'])) {
    response(false, 'Missing required fields');
}

$username = trim($input['username']);
$points = intval($input['points']);
$level = isset($input['level']) ? intval($input['level']) : 1;
$timestamp = date('Y-m-d H:i:s');

if (USE_JSON) {
    $leaderboard = getJsonData('leaderboard.json');
    
    // Cek apakah user sudah ada di leaderboard
    $existingIndex = -1;
    foreach ($leaderboard as $index => $entry) {
        if ($entry['username'] === $username) {
            $existingIndex = $index;
            break;
        }
    }
    
    $entryData = [
        'username' => $username,
        'points' => $points,
        'level' => $level,
        'last_updated' => $timestamp
    ];
    
    if ($existingIndex !== -1) {
        // Update poin jika lebih tinggi
        if ($points > $leaderboard[$existingIndex]['points']) {
            $leaderboard[$existingIndex] = $entryData;
        }
    } else {
        // Tambah entry baru
        $leaderboard[] = $entryData;
    }
    
    // Urutkan berdasarkan poin (descending)
    usort($leaderboard, function($a, $b) {
        return $b['points'] - $a['points'];
    });
    
    // Simpan hanya top 100
    $leaderboard = array_slice($leaderboard, 0, 100);
    
    if (saveJsonData('leaderboard.json', $leaderboard)) {
        response(true, 'Leaderboard updated', $leaderboard);
    } else {
        response(false, 'Failed to update leaderboard');
    }
} else {
    $conn = getDBConnection();
    
    // Cek apakah user sudah ada
    $stmt = $conn->prepare("SELECT id, points FROM leaderboard WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Update hanya jika poin lebih tinggi
        if ($points > $row['points']) {
            $stmt = $conn->prepare("UPDATE leaderboard SET points = ?, level = ?, last_updated = ? WHERE username = ?");
            $stmt->bind_param("iiss", $points, $level, $timestamp, $username);
        } else {
            response(true, 'Points not higher, no update needed');
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO leaderboard (username, points, level, last_updated) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $username, $points, $level, $timestamp);
    }
    
    if (isset($stmt) && $stmt->execute()) {
        response(true, 'Leaderboard updated successfully');
    } elseif (!isset($stmt)) {
        response(true, 'No update needed');
    } else {
        response(false, 'Database error: ' . $conn->error);
    }
    
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
