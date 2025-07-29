<?php
require 'check_session.php';
require 'db.php';

// Check permissions
if (!can_edit()) {
    $_SESSION['error'] = 'You do not have permission to perform this action.';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: add_hospital.php');
        exit;
    }
    unset($_SESSION['csrf_token']);

    // Directory for uploads
    $uploadDir = 'emp_hospital/HospRateList/';
    
    // Ensure upload directory exists and is writable
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0775, true)) { // Use 0775 for security
            $_SESSION['error'] = 'Critical Error: Could not create upload directory. Please check server permissions.';
            error_log('Failed to create upload directory: ' . $uploadDir);
            header('Location: add_hospital.php');
            exit;
        }
    }
    if (!is_writable($uploadDir)) {
        $_SESSION['error'] = 'Critical Error: The upload directory is not writable. Please check server permissions.';
        error_log('Upload directory is not writable: ' . $uploadDir);
        header('Location: add_hospital.php');
        exit;
    }

    /**
     * Handles a single file upload.
     * @param string $fileKey The key in the $_FILES array.
     * @param string $uploadDir The directory to upload the file to.
     * @return array An array with 'path' on success or 'error' on failure.
     */
    function handle_upload(string $fileKey, string $uploadDir): array
    {
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
                return ['error' => 'Failed to move uploaded file for ' . htmlspecialchars($fileKey) . '. Check server logs.'];
            }
        } elseif (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
            return ['error' => 'An error occurred during file upload for ' . htmlspecialchars($fileKey) . '. Error code: ' . $_FILES[$fileKey]['error']];
        }
        
        return ['path' => null];
    }

    $errors = [];
    $approv_order_res = handle_upload('approv_order_accomodation', $uploadDir);
    if (isset($approv_order_res['error'])) $errors[] = $approv_order_res['error'];

    $tariff_res = handle_upload('tariff', $uploadDir);
    if (isset($tariff_res['error'])) $errors[] = $tariff_res['error'];

    $facilitation_res = handle_upload('facilitation', $uploadDir);
    if (isset($facilitation_res['error'])) $errors[] = $facilitation_res['error'];

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: add_hospital.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare(
    "INSERT INTO emp_hosp_name (
        Hosp_name, Hosp_name_H, hosp_add, hosp_add_H, state, SCHEME,
        hospital_contact_person, hospital_contact_number, valid_from, VALID_UPTO, RegValidUptoDt,
        Rem, remarks_hi, ACC_Link_Add, LINK_ADD, Hosp_Offer
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

        // Corrected execute call in save_hospital.php
            // Corrected execute call in save_hospital.php
                $stmt->execute([
                    $_POST['Hosp_name'],
                    $_POST['Hosp_name_H'],
                    $_POST['hosp_add'],
                    $_POST['hosp_add_H'],
                    $_POST['state'],
                    $_POST['SCHEME'],
                    $_POST['hospital_contact_person'],
                    $_POST['hospital_contact_number'],
                    !empty($_POST['valid_from']) ? $_POST['valid_from'] : null,
                    !empty($_POST['VALID_UPTO']) ? $_POST['VALID_UPTO'] : null,
                    !empty($_POST['RegValidUptoDt']) ? $_POST['RegValidUptoDt'] : null,
                    $_POST['Rem'],
                    $_POST['remarks_hi'],
                    $approv_order_res['path'], // Corresponds to ACC_Link_Add
                    $tariff_res['path'],       // Corresponds to LINK_ADD
                    $facilitation_res['path']  // Corresponds to Hosp_Offer
                ]);

        $_SESSION['success'] = 'Hospital added successfully.';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: Could not add hospital.';
        error_log('Save hospital error: ' . $e->getMessage());
        header('Location: add_hospital.php');
        exit;
    }
} else {
    // Redirect if not a POST request
    header('Location: add_hospital.php');
    exit;
}