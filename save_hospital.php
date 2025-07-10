<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['name_en', 'name_hi', 'address_en', 'address_hi', 'state', 'payment_scheme', 'contact_person', 'contact_number'];
    $errors = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }

    // Validate contact number format
    if (!empty($_POST['contact_number']) && !preg_match('/^[0-9]{10}$/', $_POST['contact_number'])) {
        $errors[] = 'Contact number must be a 10-digit number';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: add_hospital.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO hospitals (name_en, name_hi, address_en, address_hi, state, payment_scheme, contact_person, contact_number, valid_from, valid_upto, reg_valid_upto, remarks_en, remarks_hi, approv_order_accomodation, tariff, facilitation, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['name_en'],
            $_POST['name_hi'],
            $_POST['address_en'],
            $_POST['address_hi'],
            $_POST['state'],
            $_POST['payment_scheme'],
            $_POST['contact_person'],
            $_POST['contact_number'],
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

        $_SESSION['success'] = 'Hospital added successfully';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['errors'] = ['Database error: Unable to save hospital details'];
        header('Location: add_hospital.php');
        exit;
    }
} else {
    header('Location: add_hospital.php');
    exit;
}
?>
