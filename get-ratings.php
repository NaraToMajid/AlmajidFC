<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$ratings = json_decode(file_get_contents('ratings.json'), true) ?? [];

echo json_encode([
    'success' => true,
    'message' => 'Data rating berhasil diambil',
    'data' => $ratings
]);
?>
