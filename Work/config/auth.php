<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

function requireApiRole($required_role = null) {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Unauthorized. Silakan login terlebih dahulu."]);
        exit;
    }

    if ($required_role !== null) {
        $user = getCurrentUser();
        if ($user['role'] !== $required_role) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Forbidden. Akses Ditolak (Role tidak sesuai)."]);
            exit;
        }
    }
}
?>
