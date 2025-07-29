<?php
require 'check_session.php';
require 'db.php';

if (!can_edit()) {
    header('Location: index.php');
    exit;
}

$hospitalId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Generate a new CSRF token for the form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: edit_hospital.php?id=' . $hospitalId);
        exit;
    }
    unset($_SESSION['csrf_token']);

    $uploadDir = 'emp_hospital/HospRateList/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0775, true)) {
            $_SESSION['error'] = 'Critical Error: Could not create upload directory. Please check server permissions.';
            error_log('Failed to create upload directory on edit: ' . $uploadDir);
            header('Location: edit_hospital.php?id=' . $hospitalId);
            exit;
        }
    }
    if (!is_writable($uploadDir)) {
        $_SESSION['error'] = 'Critical Error: The upload directory is not writable. Please check server permissions.';
        error_log('Upload directory is not writable on edit: ' . $uploadDir);
        header('Location: edit_hospital.php?id=' . $hospitalId);
        exit;
    }


    /**
     * Handles a single file upload for editing.
     * @param string $fileKey The key in the $_FILES array.
     * @param string $uploadDir The directory to upload the file to.
     * @param string $existingPath The path of the existing file to be replaced.
     * @return array An array with 'path' on success or 'error' on failure.
     */
    function handle_upload_edit(string $fileKey, string $uploadDir, string $existingPath): array
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
                // Delete old file if it exists
                if (!empty($existingPath) && file_exists($existingPath)) {
                    unlink($existingPath);
                }
                return ['path' => $target_path];
            } else {
                return ['error' => 'Failed to move uploaded file for ' . htmlspecialchars($fileKey) . '. Check server logs.'];
            }
        }

        return ['path' => $existingPath];
    }

    $approv_order_res = handle_upload_edit('approv_order_accomodation', $uploadDir, $_POST['existing_approv_order_accomodation']);
    $tariff_res = handle_upload_edit('tariff', $uploadDir, $_POST['existing_tariff']);
    $facilitation_res = handle_upload_edit('facilitation', $uploadDir, $_POST['existing_facilitation']);

    $errors = [];
    if (isset($approv_order_res['error'])) $errors[] = $approv_order_res['error'];
    if (isset($tariff_res['error'])) $errors[] = $tariff_res['error'];
    if (isset($facilitation_res['error'])) $errors[] = $facilitation_res['error'];

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: edit_hospital.php?id=' . $hospitalId);
        exit;
    }
    try {
        $stmt = $pdo->prepare(
            "UPDATE emp_hosp_name SET 
            Hosp_name = ?, Hosp_name_H = ?, 
            hosp_add = ?, hosp_add_H = ?, 
            state = ?,
            SCHEME = ?,
            hospital_contact_person = ?,
            hospital_contact_number = ?,
            valid_from = ?, VALID_UPTO = ?, 
            RegValidUptoDt = ?, 
            Rem = ?, remarks_hi = ?, 
            ACC_Link_Add = ?, 
            LINK_ADD = ?, 
            Hosp_Offer = ? 
            WHERE hosp_id = ?"
        );

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
            $approv_order_res['path'],
            $tariff_res['path'],
            $facilitation_res['path'],
            $hospitalId
        ]);

        $_SESSION['success'] = 'Hospital updated successfully';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: Could not update hospital.';
        error_log("Update hospital error: " . $e->getMessage());
        header('Location: edit_hospital.php?id=' . $hospitalId);
        exit;
    }
}


$stmt = $pdo->prepare("SELECT * FROM emp_hosp_name WHERE hosp_id = ?");
$stmt->execute([$hospitalId]);
$hospital = $stmt->fetch();

if (!$hospital) {
    header('Location: index.php');
    exit;
}


$states_stmt = $pdo->query("SELECT loc_name AS name FROM emp_hosp_loc ORDER BY name");
$states = $states_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a4b84;
            --secondary-color: #4a5568;
            --accent-color: #16a085;
            --border-radius: 8px;
            --vintage-shadow: 0 8px 30px rgba(26, 75, 132, 0.12);
            --gradient-primary: linear-gradient(135deg, #1a4b84, #2c5282);
            --gradient-secondary: linear-gradient(to right, #f8fafc, #fff);
            --gradient-accent: linear-gradient(135deg, #16a085, #2c9678);
        }

        body {
            background: var(--gradient-secondary);
            font-family: 'Roboto', Arial, sans-serif;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--vintage-shadow);
            padding: 2rem;
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: var(--gradient-primary);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-weight: 700;
            text-align: center;
        }

        .form-control {
            border-radius: var(--border-radius);
            padding: 0.8rem;
            border: 1px solid rgba(26, 75, 132, 0.2);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: var(--vintage-shadow);
        }

        .btn {
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border-radius: var(--border-radius);
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--vintage-shadow);
        }

        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="container animate__animated animate__fadeIn">
        <img src="logo.jpeg" alt="NHPC Logo" class="header-image" style=" width: 100%;
            max-height: 150px;
            object-fit:contain;
            margin-bottom: 2rem;">
        <h1>Edit Hospital Details</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="edit_hospital.php?id=<?php echo $hospitalId; ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Name (English)</label>
                    <input type="text" name="Hosp_name" class="form-control" value="<?php echo htmlspecialchars($hospital['Hosp_name']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Name (Hindi)</label>
                    <input type="text" name="Hosp_name_H" class="form-control" value="<?php echo htmlspecialchars($hospital['Hosp_name_H']); ?>" required>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Address (English)</label>
                    <textarea name="hosp_add" class="form-control" required rows="3"><?php echo htmlspecialchars($hospital['hosp_add']); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address (Hindi)</label>
                    <textarea name="hosp_add_H" class="form-control" rows="3"><?php echo htmlspecialchars($hospital['hosp_add_H']); ?></textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">State</label>
                    <select name="state" class="form-control" required>
                        <option value="">Select State</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>" <?php if ($hospital['state'] == $state) echo 'selected'; ?>><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Scheme</label>
                    <select name="SCHEME" class="form-control" required>
                        <option value="">Select Scheme</option>
                        <option value="Direct" <?php if ($hospital['SCHEME'] == 'Direct') echo 'selected'; ?>>Direct Payment Scheme</option>
                        <option value="Non-Direct" <?php if ($hospital['SCHEME'] == 'Non-Direct') echo 'selected'; ?>>Non-Direct Payment Scheme</option>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="hospital_contact_person" class="form-control" value="<?php echo htmlspecialchars($hospital['hospital_contact_person']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="hospital_contact_number" class="form-control" value="<?php echo htmlspecialchars($hospital['hospital_contact_number']); ?>" required>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Valid From</label>
                    <input type="date" name="valid_from" class="form-control" value="<?php echo htmlspecialchars($hospital['valid_from']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valid Upto</label>
                    <input type="date" name="VALID_UPTO" class="form-control" value="<?php echo htmlspecialchars($hospital['VALID_UPTO']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Reg Valid Upto</label>
                    <input type="date" name="RegValidUptoDt" class="form-control" value="<?php echo htmlspecialchars($hospital['RegValidUptoDt']); ?>">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Remarks (English)</label>
                    <textarea name="Rem" class="form-control" rows="2"><?php echo htmlspecialchars($hospital['Rem']); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Remarks (Hindi)</label>
                    <textarea name="remarks_hi" class="form-control" rows="2"><?php echo htmlspecialchars($hospital['remarks_hi']); ?></textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">New Approval Order/Accommodation Document</label>
                    <input type="file" name="approv_order_accomodation" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <input type="hidden" name="existing_approv_order_accomodation" value="<?php echo htmlspecialchars((string)$hospital['ACC_Link_Add']); ?>">
                    <?php if (!empty($hospital['ACC_Link_Add'])): ?>
                        <div class="form-text mt-2">Current: <a href="<?php echo htmlspecialchars($hospital['ACC_Link_Add']); ?>" target="_blank">View Document</a></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Tariff Document</label>
                    <input type="file" name="tariff" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <input type="hidden" name="existing_tariff" value="<?php echo htmlspecialchars((string)$hospital['LINK_ADD']); ?>">
                    <?php if (!empty($hospital['LINK_ADD'])): ?>
                        <div class="form-text mt-2">Current: <a href="<?php echo htmlspecialchars($hospital['LINK_ADD']); ?>" target="_blank">View Document</a></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <label class="form-label">New Facilitation Document</label>
                    <input type="file" name="facilitation" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <input type="hidden" name="existing_facilitation" value="<?php echo htmlspecialchars((string)$hospital['Hosp_Offer']); ?>">
                    <?php if (!empty($hospital['Hosp_Offer'])): ?>
                        <div class="form-text mt-2">Current: <a href="<?php echo htmlspecialchars($hospital['Hosp_Offer']); ?>" target="_blank">View Document</a></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Hospital
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const validFrom = document.querySelector('input[name="valid_from"]');
            const validUpto = document.querySelector('input[name="VALID_UPTO"]');

            function validateDates() {
                if (validFrom.value && validUpto.value && validFrom.value > validUpto.value) {
                    validUpto.setCustomValidity('Valid Upto date must be after Valid From date.');
                } else {
                    validUpto.setCustomValidity('');
                }
            }

            validFrom.addEventListener('change', validateDates);
            validUpto.addEventListener('change', validateDates);

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    </script>
</body>
</html>