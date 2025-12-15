<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username dan password harus diisi']);
        exit;
    }
    
    $users = json_decode(file_get_contents('users.json'), true) ?? [];
    
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
            exit;
        }
    }
    
    $newUser = [
        'username' => $username,
        'password' => $password, // Dalam production, gunakan password_hash()
        'points' => 0,
        'level' => 1,
        'joined' => date('Y-m-d H:i:s')
    ];
    
    $users[] = $newUser;
    file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'message' => 'Registrasi berhasil',
        'data' => [
            'username' => $username,
            'points' => 0,
            'level' => 1
        ]
    ]);
    
} elseif ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username dan password harus diisi']);
        exit;
    }
    
    $users = json_decode(file_get_contents('users.json'), true) ?? [];
    
    foreach ($users as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'username' => $username,
                    'points' => $user['points'] ?? 0,
                    'level' => $user['level'] ?? 1
                ]
            ]);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Username atau password salah']);
} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
}
?>
