<?php

// --- Database Configuration ---
// To rename your database, change the value of $db below.
$host = 'localhost';
// The database name. This should be the name of your database, not a table.
// Based on your project path, it might be 'cws'. Please verify your actual database name.
$db   = 'emp_hosp_name'; // Change this to your actual database name
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
     throw new PDOException($e->getMessage(), (int)$e->getCode());
}
?>
