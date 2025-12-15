<?php
$file = "ratings.json";
$data = json_decode(file_get_contents($file), true);

// Check if user already rated
$existingIndex = -1;
foreach ($data as $index => $rating) {
    if ($rating["user"] === $_POST["user"]) {
        $existingIndex = $index;
        break;
    }
}

if ($existingIndex !== -1) {
    // Update existing rating
    $data[$existingIndex] = [
        "user" => $_POST["user"],
        "rating" => (int)$_POST["rating"],
        "comment" => $_POST["comment"],
        "date" => date("Y-m-d"),
        "timestamp" => date("c")
    ];
} else {
    // Add new rating
    $data[] = [
        "user" => $_POST["user"],
        "rating" => (int)$_POST["rating"],
        "comment" => $_POST["comment"],
        "date" => date("Y-m-d"),
        "timestamp" => date("c")
    ];
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
?>
