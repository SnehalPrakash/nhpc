<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'check_session.php';
require 'db.php';

// --- Server-Side Filtering Logic ---
$db_error = null;
$hospitals = [];
$states = [];

try {
    // Base SQL query with correct column names and JOIN
    $sql = "SELECT 
                h.hosp_id, h.Hosp_name, h.Hosp_name_H, h.hosp_add, h.hosp_add_H, 
                h.VALID_UPTO, h.RegValidUptoDt, h.Rem, h.ACC_Link_Add, h.LINK_ADD, 
                h.Hosp_Offer, h.SCHEME, h.Cont_person, h.Cont_no, h.valid_from, l.loc_name 
            FROM emp_hosp_name AS h
            LEFT JOIN emp_hosp_loc AS l ON h.LOC_CODE = l.Loc_id";

    $params = [];
    $whereClauses = [];

    // Handle search term filter (now includes Hindi fields)
    if (!empty($_GET['search'])) {
        $searchTerm = '%' . htmlspecialchars($_GET['search']) . '%';
        $whereClauses[] = "(h.Hosp_name LIKE ? OR h.hosp_add LIKE ? OR h.Hosp_name_H LIKE ? OR h.hosp_add_H LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Handle state filter using LOC_CODE
    if (!empty($_GET['state'])) {
        $whereClauses[] = "h.LOC_CODE = ?";
        $params[] = $_GET['state'];
    }

    // Handle payment scheme filter
    if (!empty($_GET['scheme'])) {
        $whereClauses[] = "h.SCHEME = ?";
        $params[] = $_GET['scheme'];
    }

    // Handle status filter
    if (!empty($_GET['status'])) {
        $status = $_GET['status'];
        if ($status === 'Active') {
            $whereClauses[] = "h.VALID_UPTO > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        } elseif ($status === 'Expires Soon') {
            $whereClauses[] = "(h.VALID_UPTO >= CURDATE() AND h.VALID_UPTO <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))";
        } elseif ($status === 'Expired') {
            $whereClauses[] = "h.VALID_UPTO < CURDATE()";
        } elseif ($status === 'Unknown') {
            $whereClauses[] = "h.VALID_UPTO IS NULL";
        }
    }

    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(' AND ', $whereClauses);
    }

    $sql .= " ORDER BY h.Hosp_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $hospitals = $stmt->fetchAll();

    // Fetch states for the dropdown filter
    $states_stmt = $pdo->query("SELECT Loc_id, loc_name FROM emp_hosp_loc ORDER BY loc_name ASC");
    $states = $states_stmt->fetchAll();

} catch (PDOException $e) {
    $db_error = "Database error: Could not retrieve hospital data. Please contact the administrator.";
    error_log("Index page DB error: " . $e->getMessage());
}

function get_status($valid_upto) {
    if (empty($valid_upto)) {
        return ['text' => 'Unknown', 'class' => 'status-unknown'];
    }
    try {
        $today = new DateTime();
        $validity_date = new DateTime($valid_upto);
        $diff = $today->diff($validity_date);

        if ($validity_date < $today) {
            return ['text' => 'Expired', 'class' => 'status-expired'];
        } elseif ($diff->days <= 30) {
            return ['text' => 'Expires Soon', 'class' => 'status-expiring'];
        } else {
            return ['text' => 'Active', 'class' => 'status-active'];
        }
    } catch (Exception $e) {
        return ['text' => 'Unknown', 'class' => 'status-unknown'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empanelled Hospitals - Admin</title>
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
        }
        .container-main {
            max-width: 1600px;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--vintage-shadow);
            padding: 1.5rem;
            margin: 1rem auto;
            position: relative;
            overflow: hidden;
        }
        .container-main::before {
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
            font-weight: 700;
        }
        .table-responsive {
            margin-top: 1rem;
            max-height: 70vh; 
            overflow-y: auto; 
        }
        .table thead th {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            position: sticky; 
            top: 0; 
            z-index: 2; 
        }
        .table tbody tr:hover {
            background-color: #f1f5f9;
        }
        .badge {
            padding: 0.5em 0.9em;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-active { background-color: #16a085; }
        .status-expiring { background-color: #f39c12; }
        .status-expired { background-color: #c0392b; }
        .status-unknown { background-color: #7f8c8d; }
        .modal-header {
            background: var(--gradient-primary);
            color: white;
        }
        .modal-body h5 {
            color: var(--primary-color);
            margin-top: 1rem;
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .detail-item {
            background: #f8f9fa;
            padding: 0.8rem;
            border-radius: var(--border-radius);
        }
        .detail-item strong {
            display: block;
            color: #555;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<?php require 'includes/header.php'; ?>
<div class="container-main">
    <?php if ($db_error): ?>
        <div class="alert alert-danger mt-4"><?php echo htmlspecialchars($db_error); ?></div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
        <h1 class="animate__animated animate__fadeInDown mb-0">List of Empanelled Hospitals</h1>
        <div class="d-flex gap-2">
            <?php if (can_edit()): ?>
                <a href="add_hospital.php" class="btn btn-success"><i class="fas fa-plus-circle me-2"></i>Add Hospital</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
    </div>

    <form method="GET" action="index.php" class="row g-3 align-items-center my-2 p-2 bg-light border rounded animate__animated animate__fadeIn">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search by name or address..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <select name="state" class="form-select">
                <option value="">All States</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?php echo htmlspecialchars($state['Loc_id']); ?>" <?php if (isset($_GET['state']) && $_GET['state'] == $state['Loc_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($state['loc_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="Active" <?php if (isset($_GET['status']) && $_GET['status'] === 'Active') echo 'selected'; ?>>Active</option>
                <option value="Expires Soon" <?php if (isset($_GET['status']) && $_GET['status'] === 'Expires Soon') echo 'selected'; ?>>Expires Soon</option>
                <option value="Expired" <?php if (isset($_GET['status']) && $_GET['status'] === 'Expired') echo 'selected'; ?>>Expired</option>
                <option value="Unknown" <?php if (isset($_GET['status']) && $_GET['status'] === 'Unknown') echo 'selected'; ?>>Unknown</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="scheme" class="form-select">
                <option value="">All Schemes</option>
                <option value="D" <?php if (isset($_GET['scheme']) && $_GET['scheme'] === 'D') echo 'selected'; ?>>Direct Payment</option>
                <option value="N" <?php if (isset($_GET['scheme']) && $_GET['scheme'] === 'N') echo 'selected'; ?>>Non-Direct Payment</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
            <a href="index.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>

    <div class="table-responsive animate__animated animate__fadeInUp">
        <table class="table table-hover align-middle">
            <thead class="text-center">
                <tr>
                    <th>S.No.</th> <th>Name</th> <th>Address</th> <th>State</th>
                    <th>Contact Person</th> <th>Status</th> <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $serialNumber = 1; foreach ($hospitals as $row): $status = get_status($row['VALID_UPTO']); ?>
                <tr>
                    <td class="text-center"><?php echo $serialNumber++; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['Hosp_name']); ?></strong><br>//<br>
                        <small class="text-muted"><?php echo htmlspecialchars($row['Hosp_name_H']); ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['hosp_add']); ?><br>//<br>
                        <small class="text-muted"><?php echo htmlspecialchars($row['hosp_add_H']); ?></small>
                    </td>
                    <td class="text-center"><?php echo htmlspecialchars($row['loc_name']); ?></td>
                    <td class="text-center">
                        <?php echo htmlspecialchars($row['Cont_person']); ?><br>
                        <small class="text-muted"><?php echo htmlspecialchars($row['Cont_no']); ?></small>
                    </td>
                    <td class="text-center"><span class="badge <?php echo $status['class']; ?>"><?php echo $status['text']; ?></span></td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-sm btn-info view-btn" 
                                data-bs-toggle="modal" data-bs-target="#viewHospitalModal"
                                data-name-en="<?php echo htmlspecialchars($row['Hosp_name']); ?>"
                                data-name-hi="<?php echo htmlspecialchars($row['Hosp_name_H']); ?>"
                                data-address-en="<?php echo htmlspecialchars($row['hosp_add']); ?>"
                                data-address-hi="<?php echo htmlspecialchars($row['hosp_add_H']); ?>"
                                data-state="<?php echo htmlspecialchars($row['loc_name']); ?>"
                                data-scheme="<?php echo htmlspecialchars($row['SCHEME']); ?>"
                                data-contact-person="<?php echo htmlspecialchars($row['Cont_person']); ?>"
                                data-contact-number="<?php echo htmlspecialchars($row['Cont_no']); ?>"
                                data-valid-from="<?php echo htmlspecialchars($row['valid_from'] ? date('d-M-Y', strtotime($row['valid_from'])) : 'N/A'); ?>"
                                data-valid-upto="<?php echo htmlspecialchars($row['VALID_UPTO'] ? date('d-M-Y', strtotime($row['VALID_UPTO'])) : 'N/A'); ?>"
                                data-reg-valid-upto="<?php echo htmlspecialchars($row['RegValidUptoDt'] ? date('d-M-Y', strtotime($row['RegValidUptoDt'])) : 'N/A'); ?>"
                                data-remarks="<?php echo htmlspecialchars($row['Rem']); ?>"
                                data-doc-approval="<?php echo htmlspecialchars($row['ACC_Link_Add']); ?>"
                                data-doc-tariff="<?php echo htmlspecialchars($row['LINK_ADD']); ?>"
                                data-doc-offer="<?php echo htmlspecialchars($row['Hosp_Offer']); ?>">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                            <?php if (can_edit()): ?>
                                <a href="edit_hospital.php?id=<?php echo $row['hosp_id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
                                <a href="delete_hospital.php?id=<?php echo $row['hosp_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="fas fa-trash-alt me-1"></i>Delete</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="viewHospitalModal" tabindex="-1" aria-labelledby="viewHospitalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewHospitalModalLabel">Hospital Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 id="modalNameEn" class="mb-0"></h4>
                <p id="modalNameHi" class="text-muted"></p>

                <h5><i class="fas fa-map-marker-alt me-2"></i>Address</h5>
                <p id="modalAddressEn" class="mb-1"></p>
                <p id="modalAddressHi" class="text-muted"></p>

                <h5><i class="fas fa-info-circle me-2"></i>General Information</h5>
                <div class="detail-grid">
                    <div class="detail-item"><strong>State: </strong><span id="modalState"></span></div>
                    <div class="detail-item"><strong>Payment Scheme: </strong><span id="modalPaymentScheme"></span></div>
                    <div class="detail-item"><strong>Contact Person: </strong><span id="modalContactPerson"></span></div>
                    <div class="detail-item"><strong>Contact Number: </strong><span id="modalContactNumber"></span></div>
                </div>

                <h5><i class="fas fa-calendar-alt me-2"></i>Validity Dates</h5>
                <div class="detail-grid">
                    <div class="detail-item"><strong>Valid From: </strong><span id="modalValidFrom"></span></div>
                    <div class="detail-item"><strong>Valid Upto: </strong><span id="modalValidUpto"></span></div>
                    <div class="detail-item"><strong>Registration Valid Upto: </strong><span id="modalRegValidUpto"></span></div>
                </div>

                <h5><i class="fas fa-file-alt me-2"></i>Documents</h5>
                <div id="modalDocuments" class="list-group"></div>

                <h5><i class="fas fa-comment-dots me-2"></i>Remarks</h5>
                <p id="modalRemarks" class="mb-1"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewModal = document.getElementById('viewHospitalModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            const setText = (id, attribute) => {
                const element = viewModal.querySelector(`#${id}`);
                if (element) {
                    element.textContent = button.getAttribute(attribute) || 'N/A';
                }
            };

            setText('modalNameEn', 'data-name-en');
            setText('modalNameHi', 'data-name-hi');
            setText('modalAddressEn', 'data-address-en');
            setText('modalAddressHi', 'data-address-hi');
            setText('modalState', 'data-state');
            
            const schemeElement = viewModal.querySelector('#modalPaymentScheme');
            if(schemeElement) {
                const schemeCode = button.getAttribute('data-scheme');
                schemeElement.textContent = schemeCode === 'D' ? 'Direct' : (schemeCode === 'N' ? 'Non-Direct' : 'N/A');
            }

            setText('modalContactPerson', 'data-contact-person');
            setText('modalContactNumber', 'data-contact-number');
            setText('modalValidFrom', 'data-valid-from');
            setText('modalValidUpto', 'data-valid-upto');
            setText('modalRegValidUpto', 'data-reg-valid-upto');
            setText('modalRemarks', 'data-remarks');

            const modalDocsContainer = viewModal.querySelector('#modalDocuments');
            modalDocsContainer.innerHTML = ''; 

            const createDocLink = (label, path) => {
                if (path && path.trim() !== 'N/A' && path.trim() !== '') {
                    return `<a href="${path}" target="_blank" class="list-group-item list-group-item-action">
                                <i class="fas fa-file-pdf me-2 text-danger"></i>${label}
                            </a>`;
                }
                return `<div class="list-group-item"><i class="fas fa-times-circle me-2 text-muted"></i>${label} (Not available)</div>`;
            };

            modalDocsContainer.innerHTML += createDocLink('Approval Order/Accommodation', button.getAttribute('data-doc-approval'));
            modalDocsContainer.innerHTML += createDocLink('Tariff Document', button.getAttribute('data-doc-tariff'));
            modalDocsContainer.innerHTML += createDocLink('Facilitation Document', button.getAttribute('data-doc-offer'));
        });
    }
});
</script>
</body>
</html>