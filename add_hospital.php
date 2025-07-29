<?php
require 'check_session.php';
require 'db.php';

if (!can_edit()) {
    $_SESSION['error'] = 'You do not have permission to add hospitals.';
    header('Location: index.php');
    exit;
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrf_token = $_SESSION['csrf_token'];

// Fetch all unique states for the dropdown
$states_stmt = $pdo->query("SELECT loc_name AS name FROM emp_hosp_loc ORDER BY name ASC");
$states = $states_stmt->fetchAll(PDO::FETCH_COLUMN);
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
        
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <img src="logo.jpeg" alt="NHPC Logo" class="header-image">
    <h2 class="animate__animated animate__fadeInDown">Add Hospital Details</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <strong>Please correct the following errors:</strong>
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form action="save_hospital.php" method="post" enctype="multipart/form-data" class="animate__animated animate__fadeIn animate__delay-1s needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                <input type="text" name="Hosp_name" class="form-control" required placeholder="Enter hospital name in English">
                <div class="form-text text-muted">This field is required</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Name (Hindi) <span class="text-danger">*</span></label>
                <input type="text" name="Hosp_name_H" class="form-control" required placeholder="Enter hospital name in Hindi">
                <div class="form-text text-muted">This field is required</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Address (English) <span class="text-danger">*</span></label>
                <textarea name="hosp_add" class="form-control" required placeholder="Enter complete address in English" rows="3"></textarea>
                <div class="form-text text-muted">Please provide the full address</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Address (Hindi) <span class="text-danger">*</span></label>
                <textarea name="hosp_add_H" class="form-control" required placeholder="Enter complete address in Hindi" rows="3"></textarea>
                <div class="form-text text-muted">Please provide the full address</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">State <span class="text-danger">*</span></label>
                <select name="state" class="form-control" required>
                    <option value="">Select State</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Payment Scheme <span class="text-danger">*</span></label>
                <select name="SCHEME" class="form-control" required>
                    <option value="">Select Payment Scheme</option>
                    <option value="Direct">Direct Payment Scheme</option>
                    <option value="Non-Direct">Non-Direct Payment Scheme</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                <input type="text" name="hospital_contact_person" class="form-control" required placeholder="Enter contact person name">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="tel" name="hospital_contact_number" class="form-control" required pattern="[0-9]{10}" placeholder="Enter 10-digit contact number">
                <div class="form-text text-muted">Enter a valid 10-digit mobile number</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <label class="form-label">Valid From</label>
                <input type="date" name="valid_from" class="form-control">
                <div class="form-text text-muted">Start date of validity</div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Valid Upto</label>
                <input type="date" name="VALID_UPTO" class="form-control">
                <div class="form-text text-muted">End date of validity</div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Reg Valid Upto</label>
                <input type="date" name="RegValidUptoDt" class="form-control">
                <div class="form-text text-muted">Registration validity end date</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Remarks (English)</label>
                <textarea name="Rem" class="form-control" placeholder="Enter remarks in English" rows="2"></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Remarks (Hindi)</label>
                <textarea name="remarks_hi" class="form-control" placeholder="Enter remarks in Hindi" rows="2"></textarea>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Approval Order/Accommodation Document</label>
                <input type="file" name="approv_order_accomodation" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text text-muted">Upload the approval order document (PDF, JPG, PNG)</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tariff Document</label>
                <input type="file" name="tariff" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text text-muted">Upload the tariff document (PDF, JPG, PNG)</div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Facilitation Document</label>
            <input type="file" name="facilitation" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            <div class="form-text text-muted">Upload the facilitation document (PDF, JPG, PNG)</div>
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
    const contactNumber = document.querySelector('input[name="hospital_contact_number"]');

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

    contactNumber.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });
});
</script>
</body>
</html>
