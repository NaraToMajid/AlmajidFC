<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (USE_JSON) {
    $leaderboard = getJsonData('leaderboard.json');
    
    // Urutkan berdasarkan poin
    usort($leaderboard, function($a, $b) {
        return $b['points'] - $a['points'];
    });
    
    response(true, 'Leaderboard retrieved', array_slice($leaderboard, 0, 10));
} else {
    $conn = getDBConnection();
    $result = $conn->query("SELECT username, points, level FROM leaderboard ORDER BY points DESC LIMIT 10");
    
    $leaderboard = [];
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
    
    response(true, 'Leaderboard retrieved', $leaderboard);
    $conn->close();
}
?>
