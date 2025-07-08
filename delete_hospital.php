<?php
require 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM hospitals WHERE id = ?");
    $stmt->execute([$id]);
}
header('Location: index.php');
exit;
?>
