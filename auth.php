<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? $input['action'] : '';

if ($action === 'register') {
    // REGISTER
    if (!isset($input['username']) || !isset($input['password'])) {
        response(false, 'Missing username or password');
    }
    
    $username = trim($input['username']);
    $password = trim($input['password']);
    
    if (strlen($username) < 3 || strlen($username) > 20) {
        response(false, 'Username must be 3-20 characters');
    }
    
    if (strlen($password) < 6) {
        response(false, 'Password must be at least 6 characters');
    }
    
    if (USE_JSON) {
        $users = getJsonData('users.json');
        
        // Cek apakah username sudah ada
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                response(false, 'Username already exists');
            }
        }
        
        $userData = [
            'id' => uniqid(),
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'points' => 0,
            'level' => 1,
            'joined' => date('Y-m-d H:i:s')
        ];
        
        $users[] = $userData;
        
        if (saveJsonData('users.json', $users)) {
            response(true, 'Registration successful', [
                'username' => $username,
                'points' => 0,
                'level' => 1
            ]);
        } else {
            response(false, 'Registration failed');
        }
    } else {
        $conn = getDBConnection();
        
        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            response(false, 'Username already exists');
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $joined = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, points, level, joined) VALUES (?, ?, 0, 1, ?)");
        $stmt->bind_param("sss", $username, $hashedPassword, $joined);
        
        if ($stmt->execute()) {
            response(true, 'Registration successful', [
                'username' => $username,
                'points' => 0,
                'level' => 1
            ]);
        } else {
            response(false, 'Registration failed: ' . $conn->error);
        }
        
        $stmt->close();
        $conn->close();
    }
    
} elseif ($action === 'login') {
    // LOGIN
    if (!isset($input['username']) || !isset($input['password'])) {
        response(false, 'Missing username or password');
    }
    
    $username = trim($input['username']);
    $password = trim($input['password']);
    
    if (USE_JSON) {
        $users = getJsonData('users.json');
        
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                // Untuk demo, bandingkan password biasa (dalam production gunakan password_hash)
                if ($user['password'] === $password) {
                    response(true, 'Login successful', [
                        'username' => $user['username'],
                        'points' => $user['points'],
                        'level' => $user['level']
                    ]);
                } else {
                    response(false, 'Incorrect password');
                }
            }
        }
        
        response(false, 'User not found');
    } else {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT username, password, points, level FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            response(false, 'User not found');
        }
        
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            response(true, 'Login successful', [
                'username' => $user['username'],
                'points' => $user['points'],
                'level' => $user['level']
            ]);
        } else {
            response(false, 'Incorrect password');
        }
        
        $stmt->close();
        $conn->close();
    }
    
} else {
    response(false, 'Invalid action');
}
?>
