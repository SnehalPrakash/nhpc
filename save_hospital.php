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
    unset($_SESSION['csrf_token']);
    
    $uploadDir = 'emp_hospital/HospRateList/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    function handle_upload(string $fileKey, string $uploadDir): array {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            if ($_FILES[$fileKey]['size'] > 10000000) { // 10MB limit
                return ['error' => 'File ' . htmlspecialchars($fileKey) . ' is too large. Max 10MB.'];
            }
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($_FILES[$fileKey]['tmp_name']);
            if (!in_array($mime_type, $allowed_types)) {
                return ['error' => 'Invalid file type for ' . htmlspecialchars($fileKey) . '. Only PDF, JPG, PNG are allowed.'];
            }
            $filename = uniqid() . '_' . basename(htmlspecialchars($_FILES[$fileKey]['name']));
            $target_path = $uploadDir . $filename;
            if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $target_path)) {
                return ['path' => $target_path];
            } else {
                return ['error' => 'Failed to move uploaded file for ' . htmlspecialchars($fileKey) . '.'];
            }
        } elseif (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
            return ['error' => 'An error occurred during file upload for ' . htmlspecialchars($fileKey) . '.'];
        }
        return ['path' => null];
    }

    $approv_order_res = handle_upload('ACC_Link_Add', $uploadDir);
    $tariff_res = handle_upload('LINK_ADD', $uploadDir);
    $facilitation_res = handle_upload('Hosp_Offer', $uploadDir);

    $errors = [];
    if (isset($approv_order_res['error'])) $errors[] = $approv_order_res['error'];
    if (isset($tariff_res['error'])) $errors[] = $tariff_res['error'];
    if (isset($facilitation_res['error'])) $errors[] = $facilitation_res['error'];

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
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

        $hosp_id = substr(uniqid(), -4); 

        $stmt->execute([
            $hosp_id,
            $_POST['Hosp_name'],
            $_POST['Hosp_name_H'],
            $_POST['hosp_add'],
            $_POST['hosp_add_H'],
            $_POST['LOC_CODE'],
            $_POST['SCHEME'],
            $_POST['Cont_person'],
            $_POST['Cont_no'],
            !empty($_POST['valid_from']) ? $_POST['valid_from'] : null,
            !empty($_POST['VALID_UPTO']) ? $_POST['VALID_UPTO'] : null,
            !empty($_POST['RegValidUptoDt']) ? $_POST['RegValidUptoDt'] : null,
            $_POST['Rem'],
            $approv_order_res['path'],
            $tariff_res['path'],
            $facilitation_res['path']
        ]);

        $_SESSION['success'] = 'Hospital added successfully.';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: add_hospital.php');
        exit;
    }
} else {
    header('Location: add_hospital.php');
    exit;
}
?>
