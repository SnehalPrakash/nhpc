<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/functions.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}