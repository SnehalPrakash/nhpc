<?php
require 'check_session.php';
require 'db.php';

// Get all unique states
$stmt = $pdo->query("SELECT DISTINCT state FROM hospitals ORDER BY state");
$states = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NHPC Empanelled Hospitals</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #004d99;
            --secondary-color: #0066cc;
            --gradient-primary: linear-gradient(135deg, #004d99, #0066cc);
            --gradient-secondary: linear-gradient(135deg, #f8f9fa, #e9ecef);
            --border-radius: 8px;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            padding: 2rem;
            max-width: 1400px;
        }
        h1, h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        .user-info {
            background: var(--gradient-secondary);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .user-info .username {
            color: var(--primary-color);
            font-weight: 600;
        }
        .btn-edit {
            background: var(--gradient-primary);
            border: none;
            color: white;
            margin-right: 0.5rem;
        }
        .btn-edit:hover {
            background: var(--gradient-primary);
            transform: translateY(-1px);
        }
        .filters {
            background: var(--gradient-secondary);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }
        .table-responsive {
            margin-top: 2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .table {
            margin-bottom: 0;
            background: white;
        }
        .table thead th {
            background: var(--gradient-primary);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-bottom: none;
            text-align: center;
            white-space: nowrap;
        }
        .table tbody tr:hover {
            background-color: rgba(0, 77, 153, 0.05);
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: rgba(0, 77, 153, 0.1);
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 77, 153, 0.02);
        }
    </style>
</head>
<body>
<?php require 'includes/header.php'; ?>

<div class="container">
    <div class="user-info justify-content-end">
        <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <?php if (is_admin()): ?>
            <a href="admin/dashboard.php" class="btn btn-primary btn-sm">Admin Dashboard</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo htmlspecialchars($_SESSION['success']); 
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo htmlspecialchars($_SESSION['error']); 
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="filters">
        <div class="row">
            <div class="col-md-4">
                <select class="form-select" id="stateFilter">
                    <option value="">All States</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?php echo htmlspecialchars($state); ?>">
                            <?php echo htmlspecialchars($state); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select" id="schemeFilter">
                    <option value="">All Payment Schemes</option>
                    <option value="Direct">Direct Payment Scheme</option>
                    <option value="Non-Direct">Non-Direct Payment Scheme</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" id="searchTable" placeholder="Search hospitals...">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-4">
        <a href="add_hospital.php" class="btn btn-success animate__animated animate__fadeInRight">
            <i class="fas fa-plus-circle me-2"></i>Add New Hospital
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle animate__animated animate__fadeIn">
            <thead class="table-dark">
                <tr>
                    <th>Serial No.</th>
                    <th>State</th>
                    <th>Payment Scheme</th>
                    <th>Name (EN)</th>
                    <th>Name (HI)</th>
                    <th>Address (EN)</th>
                    <th>Address (HI)</th>
                    <th>Contact Person</th>
                    <th>Contact Number</th>
                    <th>Valid From</th>
                    <th>Valid Upto</th>
                    <th>Reg Valid Upto</th>
                    <th>Remarks (EN)</th>
                    <th>Remarks (HI)</th>
                    <th>Approval Order/Accommodation</th>
                    <th>Tariff</th>
                    <th>Facilitation</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM hospitals ORDER BY state, payment_scheme, name_en ASC");
            $serial = 1;
            while ($row = $stmt->fetch()) {
                echo '<tr>';
                echo '<td>' . $serial++ . '</td>';
                echo '<td>' . htmlspecialchars($row['state']) . '</td>';
                echo '<td>' . htmlspecialchars($row['payment_scheme']) . '</td>';
                echo '<td>' . htmlspecialchars($row['name_en']) . '</td>';
                echo '<td>' . htmlspecialchars($row['name_hi']) . '</td>';
                echo '<td>' . htmlspecialchars($row['address_en']) . '</td>';
                echo '<td>' . htmlspecialchars($row['address_hi']) . '</td>';
                echo '<td>' . htmlspecialchars($row['contact_person']) . '</td>';
                echo '<td>' . htmlspecialchars($row['contact_number']) . '</td>';
                echo '<td>' . htmlspecialchars($row['valid_from']) . '</td>';
                echo '<td>' . htmlspecialchars($row['valid_upto']) . '</td>';
                echo '<td>' . htmlspecialchars($row['reg_valid_upto']) . '</td>';
                echo '<td>' . htmlspecialchars($row['remarks_en']) . '</td>';
                echo '<td>' . htmlspecialchars($row['remarks_hi']) . '</td>';

                $ao = trim($row['approv_order_accomodation']);
                if (filter_var($ao, FILTER_VALIDATE_URL) || preg_match('/^\/?uploads\//', $ao)) {
                    echo '<td><a href="' . htmlspecialchars($ao) . '" target="_blank">View</a></td>';
                } else {
                    echo '<td>' . htmlspecialchars($ao) . '</td>';
                }

                $tariff = trim($row['tariff']);
                if (filter_var($tariff, FILTER_VALIDATE_URL) || preg_match('/^\/?uploads\//', $tariff)) {
                    echo '<td><a href="' . htmlspecialchars($tariff) . '" target="_blank">View</a></td>';
                } else {
                    echo '<td>' . htmlspecialchars($tariff) . '</td>';
                }

                $facil = trim($row['facilitation']);
                if (filter_var($facil, FILTER_VALIDATE_URL) || preg_match('/^\/?uploads\//', $facil)) {
                    echo '<td><a href="' . htmlspecialchars($facil) . '" target="_blank">View</a></td>';
                } else {
                    echo '<td>' . htmlspecialchars($facil) . '</td>';
                }
                echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';

                echo '<td class="d-flex gap-1 justify-content-center">';
                if (can_edit()) {
                    echo '<a href="edit_hospital.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm">';
                    echo '<i class="fas fa-edit me-1"></i> Edit</a>';
                }
                echo '<a href="delete_hospital.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this hospital?\')">';
                echo '<i class="fas fa-trash-alt me-1"></i> Delete</a>';
                echo '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    function filterTable() {
        const searchText = document.getElementById('searchTable').value.toLowerCase();
        const stateFilter = document.getElementById('stateFilter').value;
        const schemeFilter = document.getElementById('schemeFilter').value;
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const state = row.cells[1].textContent;
            const scheme = row.cells[2].textContent;
            let showRow = true;

            // Apply state filter
            if (stateFilter && state !== stateFilter) {
                showRow = false;
            }

            // Apply scheme filter
            if (schemeFilter && scheme !== schemeFilter) {
                showRow = false;
            }

            // Apply search filter
            if (searchText) {
                let found = false;
                Array.from(row.cells).forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchText)) {
                        found = true;
                    }
                });
                if (!found) showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });
    }

    document.getElementById('searchTable').addEventListener('keyup', filterTable);
    document.getElementById('stateFilter').addEventListener('change', filterTable);
    document.getElementById('schemeFilter').addEventListener('change', filterTable);

    document.addEventListener('DOMContentLoaded', function() {
        let rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.classList.add('animate__animated', 'animate__fadeIn');
            row.style.animationDelay = `${index * 0.1}s`;
        });
    });
</script>
</body>
</html>
