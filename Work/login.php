<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Fallback login logic in case database is not populated yet
    try {
        if (isset($conn) && $conn !== null) {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            // No user found, fallback
            if ($username === 'admin' && $password === 'admin') {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'Administrator';
                $_SESSION['role'] = 'Admin';
                header("Location: index.php");
                exit;
            } else if ($username === 'user' && $password === 'user') {
                $_SESSION['user_id'] = 2;
                $_SESSION['username'] = 'Staf Umum';
                $_SESSION['role'] = 'User';
                header("Location: index.php");
                exit;
            } else {
                $error = "Username tidak ditemukan! (Gunakan admin/admin untuk demo)";
            }
        }
        } else {
            // DB connection failed, immediate fallback
            throw new Exception("No DB connection");
        }
    } catch(Exception $e) {
        if ($username === 'admin' && $password === 'admin') {
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = 'Administrator';
            $_SESSION['role'] = 'Admin';
            header("Location: index.php");
            exit;
        } else if ($username === 'user' && $password === 'user') {
            $_SESSION['user_id'] = 2;
            $_SESSION['username'] = 'Staf Umum';
            $_SESSION['role'] = 'User';
            header("Location: index.php");
            exit;
        } else {
            $error = "Username tidak ditemukan! (Gunakan admin/admin untuk demo)";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pelaporan RKZ Surabaya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="brand">
            <i class="fas fa-hospital"></i> RKZ Surabaya
        </div>
        
        <?php if ($error): ?>
        <div class="alert">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>
</body>
</html>
