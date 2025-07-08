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
            --primary-color: #1a4b84;
            --secondary-color: #4a5568;
            --accent-color: #16a085;
            --border-radius: 8px;
            --table-hover-bg: rgba(26, 75, 132, 0.04);
            --table-stripe-bg: rgba(26, 75, 132, 0.02);
            --vintage-shadow: 0 8px 30px rgba(26, 75, 132, 0.12);
            --gradient-primary: linear-gradient(135deg, #1a4b84, #2c5282);
            --gradient-secondary: linear-gradient(to right, #f8fafc, #fff);
            --gradient-accent: linear-gradient(135deg, #16a085, #2c9678);
            --gradient-hover: linear-gradient(135deg, rgba(26, 75, 132, 0.05), rgba(44, 82, 130, 0.08));
        }
        
        body {
            background: var(--gradient-secondary);
            font-family: 'Roboto', Arial, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--vintage-shadow);
            padding: 2.5rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
            animation: fadeIn 0.6s ease-in-out;
            border: 1px solid rgba(26, 75, 132, 0.1);
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
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            padding-bottom: 1rem;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient-accent);
            border-radius: var(--border-radius);
        }
        
        h2 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: var(--secondary-color);
            text-align: center;
            font-weight: 500;
            opacity: 0.9;
            font-style: italic;
        }
        
        .btn-success {
            background: var(--gradient-accent);
            border: none;
            font-weight: 600;
            letter-spacing: 1px;
            border-radius: var(--border-radius);
            padding: 0.8rem 2rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--vintage-shadow);
        }

        .btn-success::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255,255,255,0.1), transparent);
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            background: var(--gradient-accent);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 98, 102, 0.3);
        }

        .btn-success:hover::before {
            opacity: 0.5;
        }
        
        .table-responsive {
            border-radius: var(--border-radius);
            overflow-x: auto;
            margin-top: 2rem;
        }
        
        .table {
            background: #fff;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 0;
            width: 100%;
        }
        
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            font-size: 0.95rem;
            padding: 1rem;
            border-color: #edf2f7;
        }
        
        .table th {
            background: var(--gradient-primary) !important;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            border: none;
            padding: 1.2rem 1rem;
            position: relative;
            overflow: hidden;
        }

        .table th::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255,255,255,0.1), transparent);
            pointer-events: none;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(247, 249, 252, 0.7);
        }
        
        .table-hover tbody tr:hover {
            background: var(--gradient-secondary);
            transform: translateY(-1px);
            box-shadow: var(--vintage-shadow);
            transition: all 0.3s ease;
        }

        .table td {
            border-color: rgba(45, 52, 54, 0.1);
            padding: 1rem;
        }

        .table {
            border: 1px solid rgba(26, 75, 132, 0.1);
            box-shadow: var(--vintage-shadow);
        }
        
        .btn-danger {
            background-color: #e74c3c;
            border: none;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-1px);
        }
        
        @media (max-width: 1200px) {
            .container {
                max-width: 95vw;
                padding: 1.5rem;
            }
            h1 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                margin-top: 1rem;
            }
            h1 {
                font-size: 1.5rem;
            }
            h2 {
                font-size: 1.2rem;
            }
            .table th, .table td {
                font-size: 0.85rem;
                padding: 0.75rem 0.5rem;
            }
            .btn-success {
                padding: 0.6rem 1.5rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="animate__animated animate__fadeInDown">NHPC Empanelled Hospitals</h1>
    <h2 class="animate__animated animate__fadeIn animate__delay-1s">सूचीबद्ध अस्पताल की सूची / LIST OF EMPANELLED HOSPITALS</h2>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div class="search-box w-100 w-md-50 position-relative">
            <input type="text" class="form-control search-input" id="searchTable" placeholder="Search hospitals...">
            <i class="fas fa-search search-icon"></i>
        </div>
        <style>
            .search-input {
                border-radius: var(--border-radius);
                padding: 0.8rem 1rem 0.8rem 2.5rem;
                border: 1px solid rgba(26, 75, 132, 0.2);
                background: var(--gradient-secondary);
                transition: all 0.3s ease;
            }
            
            .search-input:focus {
                border-color: var(--primary-color);
                box-shadow: var(--vintage-shadow);
                background: #fff;
            }
            
            .search-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--primary-color);
                opacity: 0.7;
            }
            
            .search-input:focus + .search-icon {
                opacity: 1;
            }
            
            .container::after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 150px;
                height: 150px;
                background: linear-gradient(135deg, transparent 45%, rgba(26, 75, 132, 0.05) 100%);
                border-radius: 0 var(--border-radius) 0 0;
                pointer-events: none;
            }
            
            .table-responsive::before {
                content: '';
                position: absolute;
                bottom: -10px;
                left: 50%;
                transform: translateX(-50%);
                width: 200px;
                height: 1px;
                background: var(--gradient-primary);
                opacity: 0.2;
            }
        </style>
        <a href="add_hospital.php" class="btn btn-success animate__animated animate__fadeInRight">
            <i class="fas fa-plus-circle me-2"></i>Add New Hospital
        </a>
    </div>
    <div class="table-responsive">
    <table class="table table-bordered table-hover table-striped align-middle animate__animated animate__fadeIn">
        <thead class="table-dark">
            <tr>
                <th>Serial No.</th>
                <th>Name (EN)</th>
                <th>Name (HI)</th>
                <th>Address (EN)</th>
                <th>Address (HI)</th>
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
        require 'db.php';
        $stmt = $pdo->query("SELECT * FROM hospitals ORDER BY id ASC");
        $serial = 1;
        while ($row = $stmt->fetch()) {
            echo '<tr>';
            echo '<td>' . $serial++ . '</td>';
            echo '<td>' . htmlspecialchars($row['name_en']) . '</td>';
            echo '<td>' . htmlspecialchars($row['name_hi']) . '</td>';
            echo '<td>' . htmlspecialchars($row['address_en']) . '</td>';
            echo '<td>' . htmlspecialchars($row['address_hi']) . '</td>';
            echo '<td>' . htmlspecialchars($row['valid_from']) . '</td>';
            echo '<td>' . htmlspecialchars($row['valid_upto']) . '</td>';
            echo '<td>' . htmlspecialchars($row['reg_valid_upto']) . '</td>';
            echo '<td>' . htmlspecialchars($row['remarks_en']) . '</td>';
            echo '<td>' . htmlspecialchars($row['remarks_hi']) . '</td>';

            $ao = trim($row['approv_order_accomodation']);
            if (filter_var($ao, FILTER_VALIDATE_URL) || preg_match('/^\/?uploads\//', $ao)) {
                echo '<td><a href="' . htmlspecialchars($ao) . '" target="_blank">' . htmlspecialchars($ao) . '</a></td>';
            } else {
                echo '<td>' . htmlspecialchars($ao) . '</td>';
            }

            $tariff = trim($row['tariff']);
            if (filter_var($tariff, FILTER_VALIDATE_URL) || preg_match('/^\/?uploads\//', $tariff)) {
                echo '<td><a href="' . htmlspecialchars($tariff) . '" target="_blank">' . htmlspecialchars($tariff) . '</a></td>';
            } else {
                echo '<td>' . htmlspecialchars($tariff) . '</td>';
            }

            $facil = trim($row['facilitation']);
            if (filter_var($facil, FILTER_VALIDATE_URL) || preg_match('/^\/?uploads\//', $facil)) {
                echo '<td><a href="' . htmlspecialchars($facil) . '" target="_blank">' . htmlspecialchars($facil) . '</a></td>';
            } else {
                echo '<td>' . htmlspecialchars($facil) . '</td>';
            }
            echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';

            echo '<td>
                <a href="delete_hospital.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this hospital?\')">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </a>
            </td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
    </div>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    document.getElementById('searchTable').addEventListener('keyup', function() {
        let searchText = this.value.toLowerCase();
        let table = document.querySelector('table');
        let rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            let row = rows[i];
            let cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                let cell = cells[j];
                if (cell.textContent.toLowerCase().indexOf(searchText) > -1) {
                    found = true;
                    break;
                }
            }

            row.style.display = found ? '' : 'none';
        }
    });

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
