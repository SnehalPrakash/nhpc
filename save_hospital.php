<?php
require 'check_session.php';
require 'db.php';

if (!can_edit()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: add_hospital.php');
        exit;
    }
    

    $errors = [];
    $input = $_POST;

    if (empty(trim($input['Hosp_name']))) {
        $errors['Hosp_name'] = 'Hospital Name (English) is required.';
    }
    if (empty(trim($input['hosp_add']))) {
        $errors['hosp_add'] = 'Address (English) is required.';
    }
    if (empty($input['LOC_CODE'])) {
        $errors['LOC_CODE'] = 'Please select a state.';
    }
    if (empty($input['SCHEME'])) {
        $errors['SCHEME'] = 'Please select a payment scheme.';
    }
    if (!empty($input['Cont_no']) && !preg_match('/^[0-9]{10}$/', $input['Cont_no'])) {
        $errors['Cont_no'] = 'Contact number must be 10 digits.';
    }
    if (!empty($input['valid_from']) && !empty($input['VALID_UPTO'])) {
        if (strtotime($input['valid_from']) > strtotime($input['VALID_UPTO'])) {
            $errors['VALID_UPTO'] = 'The "Valid Upto" date cannot be earlier than the "Valid From" date.';
        }
    }


    $uploadDir = 'emp_hospital/HospRateList/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $approv_order_res = handle_file_upload('ACC_Link_Add', $uploadDir);
    $tariff_res = handle_file_upload('LINK_ADD', $uploadDir);
    $facilitation_res = handle_file_upload('Hosp_Offer', $uploadDir);

    if (isset($approv_order_res['error'])) $errors['ACC_Link_Add'] = $approv_order_res['error'];
    if (isset($tariff_res['error'])) $errors['LINK_ADD'] = $tariff_res['error'];
    if (isset($facilitation_res['error'])) $errors['Hosp_Offer'] = $facilitation_res['error'];


    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $input;
        $_SESSION['error'] = 'Please correct the errors below.';
        header('Location: add_hospital.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO emp_hosp_name (
                hosp_id, Hosp_name, Hosp_name_H, hosp_add, hosp_add_H, LOC_CODE, SCHEME, 
                Cont_person, Cont_no, valid_from, VALID_UPTO, RegValidUptoDt, Rem, 
                ACC_Link_Add, LINK_ADD, Hosp_Offer
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );


        $hosp_id = bin2hex(random_bytes(16)); 

        $stmt->execute([
            $hosp_id,
            $input['Hosp_name'],
            $input['Hosp_name_H'],
            $input['hosp_add'],
            $input['hosp_add_H'],
            $input['LOC_CODE'],
            $input['SCHEME'],
            $input['Cont_person'],
            $input['Cont_no'],
            !empty($input['valid_from']) ? $input['valid_from'] : null,
            !empty($input['VALID_UPTO']) ? $input['VALID_UPTO'] : null,
            !empty($input['RegValidUptoDt']) ? $input['RegValidUptoDt'] : null,
            $input['Rem'],
            $approv_order_res['path'],
            $tariff_res['path'],
            $facilitation_res['path']
        ]);
        unset($_SESSION['csrf_token']);
        unset($_SESSION['old_input']);
        unset($_SESSION['errors']);
        $_SESSION['success'] = 'Hospital added successfully.';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {

        error_log("Save Hospital DB Error: " . $e->getMessage());
        $_SESSION['error'] = 'A database error occurred. Please try again or contact an administrator.';

        $_SESSION['old_input'] = $input;
        header('Location: add_hospital.php');
        exit;
    }
} else {
    header('Location: add_hospital.php');
    exit;
}
?>
