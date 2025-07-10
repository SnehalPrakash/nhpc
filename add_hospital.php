<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #2c3e50;
            --accent-color: #16a085;
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
            --border-radius: 12px;
        }
        
        body {
            background: var(--background-color);
            font-family: 'Roboto', Arial, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .header-image {
            width: 100%;
            max-height: 150px;
            object-fit: contain;
            margin-bottom: 2rem;
        }

        .container {
            max-width: 800px;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 2.5rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
            animation: fadeIn 0.6s ease-in-out;
        }
        
        h2 {
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-control {
            border-radius: var(--border-radius);
            padding: 0.8rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        
        .btn {
            font-weight: 600;
            letter-spacing: 1px;
            padding: 0.8rem 2rem;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.2);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border: none;
        }
        
        .btn-secondary:hover {
            background-color: #1a252f;
            transform: translateY(-2px);
        }
        
        .row {
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 0.6rem 1.5rem;
                font-size: 0.8rem;
            }
            
            .form-control {
                padding: 0.6rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <img src="certify.jpeg" alt="NHPC Logo" class="header-image">
    <h2 class="animate__animated animate__fadeInDown">Add Hospital Details</h2>
    <form action="save_hospital.php" method="post" enctype="multipart/form-data" class="animate__animated animate__fadeIn animate__delay-1s needs-validation" novalidate>
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                <input type="text" name="name_en" class="form-control" required placeholder="Enter hospital name in English">
                <div class="form-text text-muted">This field is required</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Name (Hindi) <span class="text-danger">*</span></label>
                <input type="text" name="name_hi" class="form-control" required placeholder="Enter hospital name in Hindi">
                <div class="form-text text-muted">This field is required</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Address (English) <span class="text-danger">*</span></label>
                <textarea name="address_en" class="form-control" required placeholder="Enter complete address in English" rows="3"></textarea>
                <div class="form-text text-muted">Please provide the full address</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Address (Hindi) <span class="text-danger">*</span></label>
                <textarea name="address_hi" class="form-control" required placeholder="Enter complete address in Hindi" rows="3"></textarea>
                <div class="form-text text-muted">Please provide the full address</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">State <span class="text-danger">*</span></label>
                <select name="state" class="form-control" required>
                    <option value="">Select State</option>
                    <?php
                    require 'db.php';
                    $stmt = $pdo->query("SELECT name FROM states ORDER BY name");
                    while ($row = $stmt->fetch()) {
                        echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Payment Scheme <span class="text-danger">*</span></label>
                <select name="payment_scheme" class="form-control" required>
                    <option value="">Select Payment Scheme</option>
                    <option value="Direct">Direct Payment Scheme</option>
                    <option value="Non-Direct">Non-Direct Payment Scheme</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                <input type="text" name="contact_person" class="form-control" required placeholder="Enter contact person name">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="tel" name="contact_number" class="form-control" required pattern="[0-9]{10}" placeholder="Enter 10-digit contact number">
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
                <input type="date" name="valid_upto" class="form-control">
                <div class="form-text text-muted">End date of validity</div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Reg Valid Upto</label>
                <input type="date" name="reg_valid_upto" class="form-control">
                <div class="form-text text-muted">Registration validity end date</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Remarks (English)</label>
                <textarea name="remarks_en" class="form-control" placeholder="Enter remarks in English" rows="2"></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Remarks (Hindi)</label>
                <textarea name="remarks_hi" class="form-control" placeholder="Enter remarks in Hindi" rows="2"></textarea>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Approval Order/Accommodation</label>
                <input type="text" name="approv_order_accomodation" class="form-control" placeholder="Enter approval order details">
                <div class="form-text text-muted">Enter the approval order number or accommodation details</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tariff</label>
                <input type="text" name="tariff" class="form-control" placeholder="Enter tariff details">
                <div class="form-text text-muted">Specify the tariff structure or rates</div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Facilitation</label>
            <textarea name="facilitation" class="form-control" placeholder="Enter facilitation details" rows="2"></textarea>
            <div class="form-text text-muted">Describe the facilities and services available</div>
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

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
    const validUpto = document.querySelector('input[name="valid_upto"]');
    const regValidUpto = document.querySelector('input[name="reg_valid_upto"]');
    const contactNumber = document.querySelector('input[name="contact_number"]');

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
