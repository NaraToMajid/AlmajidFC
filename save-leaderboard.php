<?php
$file = "leaderboard.json";
$data = json_decode(file_get_contents($file), true);

// Check if user already exists
$existingIndex = -1;
foreach ($data as $index => $user) {
    if ($user["username"] === $_POST["username"]) {
        $existingIndex = $index;
        break;
    }
}

if ($existingIndex !== -1) {
    // Update existing user
    $data[$existingIndex]["points"] = (int)$_POST["points"];
    $data[$existingIndex]["level"] = (int)$_POST["level"];
} else {
    // Add new user
    $data[] = [
        "username" => $_POST["username"],
        "points" => (int)$_POST["points"],
        "level" => (int)$_POST["level"]
    ];
}

// Sort by points descending
usort($data, function($a, $b) {
    return $b["points"] - $a["points"];
});

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
?>
