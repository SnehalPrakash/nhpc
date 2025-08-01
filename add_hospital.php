<?php
require 'check_session.php';
require 'db.php';

if (!can_edit()) {
    $_SESSION['error'] = 'You do not have permission to add hospitals.';
    header('Location: index.php');
    exit;
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


$states_stmt = $pdo->query("SELECT Loc_id, loc_name FROM emp_hosp_loc ORDER BY loc_name ASC");
$states = $states_stmt->fetchAll();


$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Hospital</title>
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
    <h2 class="animate__animated animate__fadeInDown">Add Hospital Details</h2>
    
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

    <form action="save_hospital.php" method="post" enctype="multipart/form-data" class="animate__animated animate__fadeIn animate__delay-1s needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                <input type="text" name="Hosp_name" class="form-control <?php echo isset($errors['Hosp_name']) ? 'is-invalid' : ''; ?>" required placeholder="Enter hospital name in English" value="<?php echo htmlspecialchars($old_input['Hosp_name'] ?? ''); ?>">
                <?php if (isset($errors['Hosp_name'])): ?><div class="invalid-feedback"><?php echo $errors['Hosp_name']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Name (Hindi)</label>
                <input type="text" name="Hosp_name_H" class="form-control" placeholder="Enter hospital name in Hindi" value="<?php echo htmlspecialchars($old_input['Hosp_name_H'] ?? ''); ?>">
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Address (English) <span class="text-danger">*</span></label>
                <textarea name="hosp_add" class="form-control <?php echo isset($errors['hosp_add']) ? 'is-invalid' : ''; ?>" required placeholder="Enter complete address in English" rows="3"><?php echo htmlspecialchars($old_input['hosp_add'] ?? ''); ?></textarea>
                <?php if (isset($errors['hosp_add'])): ?><div class="invalid-feedback"><?php echo $errors['hosp_add']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Address (Hindi)</label>
                <textarea name="hosp_add_H" class="form-control" placeholder="Enter complete address in Hindi" rows="3"><?php echo htmlspecialchars($old_input['hosp_add_H'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">State <span class="text-danger">*</span></label>
                <select name="LOC_CODE" class="form-control <?php echo isset($errors['LOC_CODE']) ? 'is-invalid' : ''; ?>" required>
                    <option value="">Select State</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?php echo htmlspecialchars($state['Loc_id']); ?>" <?php if (($old_input['LOC_CODE'] ?? '') == $state['Loc_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($state['loc_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['LOC_CODE'])): ?><div class="invalid-feedback"><?php echo $errors['LOC_CODE']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Payment Scheme <span class="text-danger">*</span></label>
                <select name="SCHEME" class="form-control <?php echo isset($errors['SCHEME']) ? 'is-invalid' : ''; ?>" required>
                    <option value="">Select Payment Scheme</option>
                    <option value="D" <?php if (($old_input['SCHEME'] ?? '') == 'D') echo 'selected'; ?>>Direct Payment</option>
                    <option value="N" <?php if (($old_input['SCHEME'] ?? '') == 'N') echo 'selected'; ?>>Non-Direct Payment</option>
                </select>
                <?php if (isset($errors['SCHEME'])): ?><div class="invalid-feedback"><?php echo $errors['SCHEME']; ?></div><?php endif; ?>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Person</label>
                <input type="text" name="Cont_person" class="form-control" placeholder="Enter contact person name" value="<?php echo htmlspecialchars($old_input['Cont_person'] ?? ''); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Number</label>
                <input type="tel" name="Cont_no" class="form-control <?php echo isset($errors['Cont_no']) ? 'is-invalid' : ''; ?>" pattern="[0-9]{10}" placeholder="Enter 10-digit contact number" value="<?php echo htmlspecialchars($old_input['Cont_no'] ?? ''); ?>">
                <?php if (isset($errors['Cont_no'])): ?><div class="invalid-feedback"><?php echo $errors['Cont_no']; ?></div><?php endif; ?>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <label class="form-label">Valid From</label>
                <input type="date" name="valid_from" class="form-control" value="<?php echo htmlspecialchars($old_input['valid_from'] ?? ''); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Valid Upto</label>
                <input type="date" name="VALID_UPTO" class="form-control <?php echo isset($errors['VALID_UPTO']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($old_input['VALID_UPTO'] ?? ''); ?>">
                <?php if (isset($errors['VALID_UPTO'])): ?><div class="invalid-feedback"><?php echo $errors['VALID_UPTO']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Registration Valid Upto</label>
                <input type="date" name="RegValidUptoDt" class="form-control" value="<?php echo htmlspecialchars($old_input['RegValidUptoDt'] ?? ''); ?>">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Remarks</label>
            <textarea name="Rem" class="form-control" placeholder="Enter remarks" rows="2"><?php echo htmlspecialchars($old_input['Rem'] ?? ''); ?></textarea>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <label class="form-label">Approval Order Document</label>
                <input type="file" name="ACC_Link_Add" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <?php if (isset($errors['ACC_Link_Add'])): ?><div class="invalid-feedback d-block"><?php echo $errors['ACC_Link_Add']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tariff Document</label>
                <input type="file" name="LINK_ADD" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <?php if (isset($errors['LINK_ADD'])): ?><div class="invalid-feedback d-block"><?php echo $errors['LINK_ADD']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Facilitation Document</label>
                <input type="file" name="Hosp_Offer" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <?php if (isset($errors['Hosp_Offer'])): ?><div class="invalid-feedback d-block"><?php echo $errors['Hosp_Offer']; ?></div><?php endif; ?>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Save
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-list me-2"></i>View All
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const formGroups = document.querySelectorAll('.form-control');

    formGroups.forEach((group, index) => {
        group.addEventListener('focus', function() {
            this.closest('.col-md-6, .col-md-4, .mb-4').classList.add('animate__animated', 'animate__pulse');
        });
        group.addEventListener('blur', function() {
            this.closest('.col-md-6, .col-md-4, .mb-4').classList.remove('animate__animated', 'animate__pulse');
        });
    });

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();

            const invalidFields = form.querySelectorAll(':invalid');
            invalidFields.forEach(field => {
                const fieldContainer = field.closest('.col-md-6, .col-md-4, .mb-4');
                fieldContainer.classList.add('animate__animated', 'animate__shakeX');
                setTimeout(() => {
                    fieldContainer.classList.remove('animate__animated', 'animate__shakeX');
                }, 1000);
            });
        }

        form.classList.add('was-validated');
    }, false);

    const validFrom = document.querySelector('input[name="valid_from"]');
    const validUpto = document.querySelector('input[name="VALID_UPTO"]');
    const regValidUpto = document.querySelector('input[name="RegValidUptoDt"]');
    const contactNumber = document.querySelector('input[name="Cont_no"]');

    if (validUpto && validFrom) {
        validUpto.addEventListener('change', function() {
            if (validFrom.value && validUpto.value && validFrom.value > validUpto.value) {
                validUpto.setCustomValidity('Valid Upto date must be after Valid From date');
            } else {
                validUpto.setCustomValidity('');
            }
        });

        validFrom.addEventListener('change', function() {
            validUpto.dispatchEvent(new Event('change'));
        });
    }

    if (contactNumber) {
        contactNumber.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
    }
});
</script>
</body>
</html>
