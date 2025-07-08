<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO hospitals (name_en, name_hi, address_en, address_hi, valid_from, valid_upto, reg_valid_upto, remarks_en, remarks_hi, approv_order_accomodation, tariff, facilitation, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name_en'],
        $_POST['name_hi'],
        $_POST['address_en'],
        $_POST['address_hi'],
        $_POST['valid_from'],
        $_POST['valid_upto'],
        $_POST['reg_valid_upto'],
        $_POST['remarks_en'],
        $_POST['remarks_hi'],
        $_POST['approv_order_accomodation'],
        $_POST['tariff'],
        $_POST['facilitation'],
        date('Y-m-d H:i:s')
    ]);
    header('Location: index.php');
    exit;
} else {
    header('Location: add_hospital.php');
    exit;
}
?>
