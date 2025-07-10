<?php
session_start();
require '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['id'])) {
    $userId = (int)$_GET['id'];

    try {
        // Check if user exists and is not an admin
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user && $user['role'] !== 'admin') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Cannot delete admin user';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting user';
    }
}

header('Location: dashboard.php');
exit;