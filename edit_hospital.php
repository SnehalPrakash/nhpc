<?php
require 'check_session.php';
require 'db.php';

if (!can_edit()) {
    header('Location: index.php');
    exit;
}

$hospitalId = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: edit_hospital.php?id=' . $hospitalId);
        exit;
    }
    unset($_SESSION['csrf_token']);

    $uploadDir = 'emp_hospital/HospRateList/';

    function handle_upload_edit(string $fileKey, string $uploadDir, string $existingPath): array {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            if ($_FILES[$fileKey]['size'] > 10000000) {
                return ['error' => 'File is too large.'];
            }
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($_FILES[$fileKey]['tmp_name']);
            if (!in_array($mime_type, $allowed_types)) {
                return ['error' => 'Invalid file type.'];
            }
            $filename = uniqid() . '_' . basename(htmlspecialchars($_FILES[$fileKey]['name']));
            $target_path = $uploadDir . $filename;
            if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $target_path)) {
                if (!empty($existingPath) && file_exists($existingPath)) {
                    unlink($existingPath);
                }
                return ['path' => $target_path];
            } else {
                return ['error' => 'Failed to move uploaded file.'];
            }
        }
        return ['path' => $existingPath];
    }

    $approv_order_res = handle_upload_edit('ACC_Link_Add', $uploadDir, $_POST['existing_ACC_Link_Add']);
    $tariff_res = handle_upload_edit('LINK_ADD', $uploadDir, $_POST['existing_LINK_ADD']);
    $facilitation_res = handle_upload_edit('Hosp_Offer', $uploadDir, $_POST['existing_Hosp_Offer']);

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
                Hosp_name = ?, Hosp_name_H = ?, hosp_add = ?, hosp_add_H = ?, 
                LOC_CODE = ?, SCHEME = ?, Cont_person = ?, Cont_no = ?, 
                valid_from = ?, VALID_UPTO = ?, RegValidUptoDt = ?, Rem = ?, 
                ACC_Link_Add = ?, LINK_ADD = ?, Hosp_Offer = ?
            WHERE hosp_id = ?"
        );
        
        $stmt->execute([
            $_POST['Hosp_name'], $_POST['Hosp_name_H'], $_POST['hosp_add'], $_POST['hosp_add_H'],
            $_POST['LOC_CODE'], $_POST['SCHEME'], $_POST['Cont_person'], $_POST['Cont_no'],
            !empty($_POST['valid_from']) ? $_POST['valid_from'] : null,
            !empty($_POST['VALID_UPTO']) ? $_POST['VALID_UPTO'] : null,
            !empty($_POST['RegValidUptoDt']) ? $_POST['RegValidUptoDt'] : null,
            $_POST['Rem'],
            $approv_order_res['path'],
            $tariff_res['path'],
            $facilitation_res['path'],
            $hospitalId
        ]);

        $_SESSION['success'] = 'Hospital updated successfully';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: edit_hospital.php?id=' . $hospitalId);
        exit;
    }
}

// Generate a new CSRF token ONLY when the form is being displayed (not on POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch existing hospital data
$stmt = $pdo->prepare("SELECT * FROM emp_hosp_name WHERE hosp_id = ?");
$stmt->execute([$hospitalId]);
$hospital = $stmt->fetch();

if (!$hospital) {
    header('Location: index.php');
    exit;
}

// Fetch states for dropdown
$states_stmt = $pdo->query("SELECT Loc_id, loc_name FROM emp_hosp_loc ORDER BY loc_name ASC");
$states = $states_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a4b84;
            --secondary-color: #2c3e50;
            --accent-color: #16a085;
            --border-radius: 8px;
            --vintage-shadow: 0 8px 30px rgba(26, 75, 132, 0.12);
            --gradient-primary: linear-gradient(135deg, #1a4b84, #2c5282);
            --gradient-secondary: linear-gradient(to right, #f8fafc, #fff);
        }
        body {
            background: var(--gradient-secondary);
            font-family: 'Roboto', Arial, sans-serif;
            color: var(--secondary-color);
            line-height: 1.6;
            padding: 2rem;
        }
        .header-image {
            width: 100%;
            max-height: 150px;
            object-fit: contain;
            margin-bottom: 2rem;
        }
        .container {
            max-width: 1200px;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--vintage-shadow);
            padding: 2.5rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
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
        h2 {
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 2rem;
        }
        label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
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
            font-weight: 600;
            letter-spacing: 1px;
            padding: 0.8rem 2rem;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--vintage-shadow);
        }
    </style>
</head>
<body>
<div class="container">
    <img src="logo.jpeg" alt="NHPC Logo" class="header-image">
    <h2 class="animate__animated animate__fadeInDown">Edit Hospital Details</h2>
    
    <!-- Error display logic remains here -->

    <form action="edit_hospital.php?id=<?php echo htmlspecialchars($hospitalId); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label">Name (English)</label>
                <input type="text" name="Hosp_name" class="form-control" value="<?php echo htmlspecialchars($hospital['Hosp_name']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name (Hindi)</label>
                <input type="text" name="Hosp_name_H" class="form-control" value="<?php echo htmlspecialchars($hospital['Hosp_name_H']); ?>">
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
                <select name="LOC_CODE" class="form-control" required>
                    <?php foreach ($states as $state): ?>
                        <option value="<?php echo htmlspecialchars($state['Loc_id']); ?>" <?php if ($hospital['LOC_CODE'] == $state['Loc_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($state['loc_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Payment Scheme</label>
                <select name="SCHEME" class="form-control" required>
                    <option value="D" <?php if ($hospital['SCHEME'] == 'D') echo 'selected'; ?>>Direct</option>
                    <option value="N" <?php if ($hospital['SCHEME'] == 'N') echo 'selected'; ?>>Non-Direct</option>
                </select>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label">Contact Person</label>
                <input type="text" name="Cont_person" class="form-control" value="<?php echo htmlspecialchars($hospital['Cont_person']); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <input type="text" name="Cont_no" class="form-control" value="<?php echo htmlspecialchars($hospital['Cont_no']); ?>">
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Valid From</label>
                <input type="date" name="valid_from" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($hospital['valid_from']))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Valid Upto</label>
                <input type="date" name="VALID_UPTO" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($hospital['VALID_UPTO']))); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Registration Valid Upto</label>
                <input type="date" name="RegValidUptoDt" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($hospital['RegValidUptoDt']))); ?>">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label">Remarks</label>
            <textarea name="Rem" class="form-control" rows="2"><?php echo htmlspecialchars($hospital['Rem']); ?></textarea>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">New Approval Order Document</label>
                <input type="file" name="ACC_Link_Add" class="form-control">
                <input type="hidden" name="existing_ACC_Link_Add" value="<?php echo htmlspecialchars($hospital['ACC_Link_Add']); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">New Tariff Document</label>
                <input type="file" name="LINK_ADD" class="form-control">
                <input type="hidden" name="existing_LINK_ADD" value="<?php echo htmlspecialchars($hospital['LINK_ADD']); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">New Facilitation Document</label>
                <input type="file" name="Hosp_Offer" class="form-control">
                <input type="hidden" name="existing_Hosp_Offer" value="<?php echo htmlspecialchars($hospital['Hosp_Offer']); ?>">
            </div>
        </div>
        
        <div class="d-flex justify-content-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary">Update Hospital</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
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