<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Log this attempt as it could be a CSRF attack
        error_log('CSRF token validation failed for login attempt.');
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: login.php');
        exit;
    }
    // make it single-use 
    unset($_SESSION['csrf_token']);

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");        
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            // More specific error message
            $_SESSION['error'] = 'Invalid username or password.';

            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Authentication error. Please try again.';
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}