<?php
session_start(); // Start session 

// Redirect to login page if !admin or logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Returns true if the current user is an admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Returns true if the user can edit hospital data (admin or user)
function can_edit() {
 return true;
}