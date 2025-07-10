<?php
require 'check_session.php';
require 'db.php';

if (!can_edit()) {
    header('Location: index.php');
    exit;
}

$hospitalId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare(
            "UPDATE hospitals SET 
            name_en = ?, name_hi = ?, 
            address_en = ?, address_hi = ?, 
            state = ?,
            payment_scheme = ?,
            contact_person = ?,
            contact_number = ?,
            valid_from = ?, valid_upto = ?, 
            reg_valid_upto = ?, 
            remarks_en = ?, remarks_hi = ?, 
            approv_order_accomodation = ?, 
            tariff = ?, 
            facilitation = ? 
            WHERE id = ?"
        );

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
            $hospitalId
        ]);

        $_SESSION['success'] = 'Hospital updated successfully';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error updating hospital';
    }
}

// Get hospital data
$stmt = $pdo->prepare("SELECT * FROM hospitals WHERE id = ?");
$stmt->execute([$hospitalId]);
$hospital = $stmt->fetch();

if (!$hospital) {
    header('Location: index.php');
    exit;
}
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
        <h1>Edit Hospital Details</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="edit_hospital.php?id=<?php echo $hospitalId; ?>" method="post" class="needs-validation" novalidate>
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Payment Scheme</label>
                    <select name="payment_scheme" class="form-control" required>
                        <option value="">Select Scheme</option>
                        <option value="Direct" <?php if ($hospital['payment_scheme'] === 'Direct') echo 'selected'; ?>>Direct Payment Scheme</option>
                        <option value="Non-Direct" <?php if ($hospital['payment_scheme'] === 'Non-Direct') echo 'selected'; ?>>Non-Direct Payment Scheme</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Name (English)</label>
                    <input type="text" name="name_en" class="form-control" value="<?php echo htmlspecialchars($hospital['name_en']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Name (Hindi)</label>
                    <input type="text" name="name_hi" class="form-control" value="<?php echo htmlspecialchars($hospital['name_hi']); ?>">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Address (English)</label>
                    <textarea name="address_en" class="form-control" required rows="3"><?php echo htmlspecialchars($hospital['address_en']); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address (Hindi)</label>
                    <textarea name="address_hi" class="form-control" rows="3"><?php echo htmlspecialchars($hospital['address_hi']); ?></textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <select name="state" class="form-control" required>
                        <option value="">Select State</option>
                        <?php
                        $states = ["Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh", "Goa", "Gujarat", "Haryana", "Himachal Pradesh", "Jharkhand", "Karnataka", "Kerala", "Madhya Pradesh", "Maharashtra", "Manipur", "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab", "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura", "Uttar Pradesh", "Uttarakhand", "West Bengal", "Andaman and Nicobar Islands", "Chandigarh", "Dadra and Nagar Haveli and Daman and Diu", "Delhi", "Jammu and Kashmir", "Ladakh", "Lakshadweep", "Puducherry"];
                        foreach ($states as $state) {
                            $selected = ($hospital['state'] === $state) ? 'selected' : '';
                            echo "<option value=\"$state\" $selected>$state</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($hospital['contact_person']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($hospital['contact_number']); ?>">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Valid From</label>
                    <input type="date" name="valid_from" class="form-control" value="<?php echo htmlspecialchars($hospital['valid_from']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valid Upto</label>
                    <input type="date" name="valid_upto" class="form-control" value="<?php echo htmlspecialchars($hospital['valid_upto']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Reg Valid Upto</label>
                    <input type="date" name="reg_valid_upto" class="form-control" value="<?php echo htmlspecialchars($hospital['reg_valid_upto']); ?>">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Remarks (English)</label>
                    <textarea name="remarks_en" class="form-control" rows="2"><?php echo htmlspecialchars($hospital['remarks_en']); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Remarks (Hindi)</label>
                    <textarea name="remarks_hi" class="form-control" rows="2"><?php echo htmlspecialchars($hospital['remarks_hi']); ?></textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Approval Order/Accommodation</label>
                    <input type="text" name="approv_order_accomodation" class="form-control" value="<?php echo htmlspecialchars($hospital['approv_order_accomodation']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tariff</label>
                    <input type="text" name="tariff" class="form-control" value="<?php echo htmlspecialchars($hospital['tariff']); ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Facilitation</label>
                <textarea name="facilitation" class="form-control" rows="2"><?php echo htmlspecialchars($hospital['facilitation']); ?></textarea>
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
            const validUpto = document.querySelector('input[name="valid_upto"]');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                if (validFrom.value && validUpto.value && validFrom.value > validUpto.value) {
                    event.preventDefault();
                    alert('Valid Upto date must be after Valid From date');
                }

                form.classList.add('was-validated');
            }, false);
        });
    </script>
</body>
</html>