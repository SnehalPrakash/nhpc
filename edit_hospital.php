<?php
require 'check_session.php';
require 'db.php';

if (!can_edit()) {
    header('Location: index.php');
    exit;
}

$hospitalId = $_GET['id'] ?? null;

if (empty($hospitalId)) {
    $_SESSION['error'] = 'No hospital ID provided.';
    header('Location: index.php');
    exit;
}


$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: edit_hospital.php?id=' . $hospitalId);
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
    $approv_order_res = handle_file_upload('ACC_Link_Add', $uploadDir, $input['existing_ACC_Link_Add']);
    $tariff_res = handle_file_upload('LINK_ADD', $uploadDir, $input['existing_LINK_ADD']);
    $facilitation_res = handle_file_upload('Hosp_Offer', $uploadDir, $input['existing_Hosp_Offer']);

    if (isset($approv_order_res['error'])) $errors['ACC_Link_Add'] = $approv_order_res['error'];
    if (isset($tariff_res['error'])) $errors['LINK_ADD'] = $tariff_res['error'];
    if (isset($facilitation_res['error'])) $errors['Hosp_Offer'] = $facilitation_res['error'];


    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $input;
        $_SESSION['error'] = 'Please correct the errors below.';
        header('Location: edit_hospital.php?id=' . $hospitalId);
        exit;
    }

    try {
        $sql = "UPDATE emp_hosp_name SET 
                Hosp_name = ?, Hosp_name_H = ?, hosp_add = ?, hosp_add_H = ?, 
                LOC_CODE = ?, SCHEME = ?, Cont_person = ?, Cont_no = ?, 
                valid_from = ?, VALID_UPTO = ?, RegValidUptoDt = ?, Rem = ?, 
                ACC_Link_Add = ?, LINK_ADD = ?, Hosp_Offer = ?
            WHERE hosp_id = ?";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $input['Hosp_name'], $input['Hosp_name_H'], $input['hosp_add'], $input['hosp_add_H'],
            $input['LOC_CODE'], $input['SCHEME'], $input['Cont_person'], $input['Cont_no'],
            !empty($input['valid_from']) ? $input['valid_from'] : null,
            !empty($input['VALID_UPTO']) ? $input['VALID_UPTO'] : null,
            !empty($input['RegValidUptoDt']) ? $input['RegValidUptoDt'] : null,
            $input['Rem'],
            $approv_order_res['path'],
            $tariff_res['path'],
            $facilitation_res['path'],
            $hospitalId
        ]);
        unset($_SESSION['csrf_token']);
        unset($_SESSION['old_input']);
        unset($_SESSION['errors']);
        $_SESSION['success'] = 'Hospital updated successfully';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        error_log("Edit Hospital DB Error: " . $e->getMessage());
        $_SESSION['error'] = 'A database error occurred. Please try again or contact an administrator.';
        $_SESSION['old_input'] = $input;
        header('Location: edit_hospital.php?id=' . $hospitalId);
        exit;
    }
}



if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


$stmt = $pdo->prepare("SELECT * FROM emp_hosp_name WHERE hosp_id = ?");
$stmt->execute([$hospitalId]);
$hospital = $stmt->fetch();

if (!$hospital) {
    header('Location: index.php');
    exit;
}


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

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form action="edit_hospital.php?id=<?php echo htmlspecialchars($hospitalId); ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                <input type="text" name="Hosp_name" class="form-control <?php echo isset($errors['Hosp_name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($old_input['Hosp_name'] ?? $hospital['Hosp_name']); ?>" required>
                <?php if (isset($errors['Hosp_name'])): ?><div class="invalid-feedback"><?php echo $errors['Hosp_name']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Name (Hindi)</label>
                <input type="text" name="Hosp_name_H" class="form-control" value="<?php echo htmlspecialchars($old_input['Hosp_name_H'] ?? $hospital['Hosp_name_H']); ?>">
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Address (English) <span class="text-danger">*</span></label>
                <textarea name="hosp_add" class="form-control <?php echo isset($errors['hosp_add']) ? 'is-invalid' : ''; ?>" required rows="3"><?php echo htmlspecialchars($old_input['hosp_add'] ?? $hospital['hosp_add']); ?></textarea>
                <?php if (isset($errors['hosp_add'])): ?><div class="invalid-feedback"><?php echo $errors['hosp_add']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Address (Hindi)</label>
                <textarea name="hosp_add_H" class="form-control" rows="3"><?php echo htmlspecialchars($old_input['hosp_add_H'] ?? $hospital['hosp_add_H']); ?></textarea>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">State <span class="text-danger">*</span></label>
                <select name="LOC_CODE" class="form-control <?php echo isset($errors['LOC_CODE']) ? 'is-invalid' : ''; ?>" required>
                    <?php foreach ($states as $state): ?>
                        <option value="<?php echo htmlspecialchars($state['Loc_id']); ?>" <?php if (($old_input['LOC_CODE'] ?? $hospital['LOC_CODE']) == $state['Loc_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($state['loc_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['LOC_CODE'])): ?><div class="invalid-feedback"><?php echo $errors['LOC_CODE']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Payment Scheme <span class="text-danger">*</span></label>
                <select name="SCHEME" class="form-control <?php echo isset($errors['SCHEME']) ? 'is-invalid' : ''; ?>" required>
                    <option value="D" <?php if (($old_input['SCHEME'] ?? $hospital['SCHEME']) == 'D') echo 'selected'; ?>>Direct Payment</option>
                    <option value="N" <?php if (($old_input['SCHEME'] ?? $hospital['SCHEME']) == 'N') echo 'selected'; ?>>Non-Direct Payment</option>
                </select>
                <?php if (isset($errors['SCHEME'])): ?><div class="invalid-feedback"><?php echo $errors['SCHEME']; ?></div><?php endif; ?>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Person</label>
                <input type="text" name="Cont_person" class="form-control" value="<?php echo htmlspecialchars($old_input['Cont_person'] ?? $hospital['Cont_person']); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Number</label>
                <input type="tel" name="Cont_no" class="form-control <?php echo isset($errors['Cont_no']) ? 'is-invalid' : ''; ?>" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($old_input['Cont_no'] ?? $hospital['Cont_no']); ?>">
                <?php if (isset($errors['Cont_no'])): ?><div class="invalid-feedback"><?php echo $errors['Cont_no']; ?></div><?php endif; ?>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <label class="form-label">Valid From</label>
                <input type="date" name="valid_from" class="form-control" value="<?php echo htmlspecialchars(($old_input['valid_from'] ?? ($hospital['valid_from'] ? date('Y-m-d', strtotime($hospital['valid_from'])) : ''))); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Valid Upto</label>
                <input type="date" name="VALID_UPTO" class="form-control <?php echo isset($errors['VALID_UPTO']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars(($old_input['VALID_UPTO'] ?? ($hospital['VALID_UPTO'] ? date('Y-m-d', strtotime($hospital['VALID_UPTO'])) : ''))); ?>">
                <?php if (isset($errors['VALID_UPTO'])): ?><div class="invalid-feedback"><?php echo $errors['VALID_UPTO']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Registration Valid Upto</label>
                <input type="date" name="RegValidUptoDt" class="form-control" value="<?php echo htmlspecialchars(($old_input['RegValidUptoDt'] ?? ($hospital['RegValidUptoDt'] ? date('Y-m-d', strtotime($hospital['RegValidUptoDt'])) : ''))); ?>">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label">Remarks</label>
            <textarea name="Rem" class="form-control" rows="2"><?php echo htmlspecialchars($old_input['Rem'] ?? $hospital['Rem']); ?></textarea>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <label class="form-label">Approval Order Document</label>
                <input type="file" name="ACC_Link_Add" class="form-control <?php echo isset($errors['ACC_Link_Add']) ? 'is-invalid' : ''; ?>">
                <input type="hidden" name="existing_ACC_Link_Add" value="<?php echo htmlspecialchars($hospital['ACC_Link_Add']); ?>">
                <?php if (!empty($hospital['ACC_Link_Add'])): ?>
                    <div class="mt-2"><small>Current: <a href="<?php echo htmlspecialchars($hospital['ACC_Link_Add']); ?>" target="_blank"><?php echo basename(htmlspecialchars($hospital['ACC_Link_Add'])); ?></a></small></div>
                <?php endif; ?>
                <?php if (isset($errors['ACC_Link_Add'])): ?><div class="invalid-feedback d-block"><?php echo $errors['ACC_Link_Add']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tariff Document</label>
                <input type="file" name="LINK_ADD" class="form-control <?php echo isset($errors['LINK_ADD']) ? 'is-invalid' : ''; ?>">
                <input type="hidden" name="existing_LINK_ADD" value="<?php echo htmlspecialchars($hospital['LINK_ADD']); ?>">
                <?php if (!empty($hospital['LINK_ADD'])): ?>
                    <div class="mt-2"><small>Current: <a href="<?php echo htmlspecialchars($hospital['LINK_ADD']); ?>" target="_blank"><?php echo basename(htmlspecialchars($hospital['LINK_ADD'])); ?></a></small></div>
                <?php endif; ?>
                <?php if (isset($errors['LINK_ADD'])): ?><div class="invalid-feedback d-block"><?php echo $errors['LINK_ADD']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Facilitation Document</label>
                <input type="file" name="Hosp_Offer" class="form-control <?php echo isset($errors['Hosp_Offer']) ? 'is-invalid' : ''; ?>">
                <input type="hidden" name="existing_Hosp_Offer" value="<?php echo htmlspecialchars($hospital['Hosp_Offer']); ?>">
                <?php if (!empty($hospital['Hosp_Offer'])): ?>
                    <div class="mt-2"><small>Current: <a href="<?php echo htmlspecialchars($hospital['Hosp_Offer']); ?>" target="_blank"><?php echo basename(htmlspecialchars($hospital['Hosp_Offer'])); ?></a></small></div>
                <?php endif; ?>
                <?php if (isset($errors['Hosp_Offer'])): ?><div class="invalid-feedback d-block"><?php echo $errors['Hosp_Offer']; ?></div><?php endif; ?>
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