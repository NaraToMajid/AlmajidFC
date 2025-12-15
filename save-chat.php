<?php
$file = "chat.json";
$data = json_decode(file_get_contents($file), true);

$data[] = [
  "user" => $_POST["user"],
  "message" => $_POST["message"],
  "time" => date("H:i")
];

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
?>
