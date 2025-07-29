<?php
require 'db.php';

// Fetch all unique states for the filter dropdown from the correct table
$states_stmt = $pdo->query("SELECT loc_name AS name FROM emp_hosp_loc ORDER BY loc_name ASC");
$states = $states_stmt->fetchAll(PDO::FETCH_COLUMN);

/**
 * Determines the status of the hospital based on its validity date.
 * @param string|null $valid_upto The validity date string.
 * @return array An array containing the status text and a CSS class.
 */
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

// Fetch only active and expiring soon hospitals from the correct table
$stmt = $pdo->query("SELECT * FROM hospitals WHERE valid_upto IS NULL OR valid_upto >= CURDATE() ORDER BY name_en ASC");
$hospitals = $stmt->fetchAll();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empanelled Hospitals - NHPC</title>
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
            background: var(--gradient-primary);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .table tbody tr {
            transition: background-color 0.3s ease;
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
        .btn-sm {
            padding: 0.3rem 0.8rem;
        }
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
    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
        <h1 class="animate__animated animate__fadeInDown mb-0">Public List of Empanelled Hospitals</h1>
        <div class="d-flex gap-2">
            <a href="export_viewer.php" id="exportCsvBtn" class="btn btn-info"><i class="fas fa-file-csv me-2"></i>Export CSV</a>
        </div>
    </div>

    <div class="row g-3 align-items-center my-2 p-2 bg-light border rounded animate__animated animate__fadeIn">
        <div class="col-md-3">
            <input type="text" id="searchFilter" class="form-control" placeholder="Search by name or address...">
        </div>
        <div class="col-md-3">
            <select id="stateFilter" class="form-select">
                <option value="">All States</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select id="paymentSchemeFilter" class="form-select">
                <option value="">All Schemes</option>
                <option value="Direct">Direct Payment</option>
                <option value="Non-Direct">Non-Direct Payment</option>
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button id="resetFilters" class="btn btn-outline-secondary">Reset Filters</button>
        </div>
    </div>

    <div class="table-responsive animate__animated animate__fadeInUp">
        <table class="table table-hover align-middle" id="hospitalTable">
            <thead class="text-center">
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>State</th>
                    <th>Contact Person</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hospitals as $row): ?>
                    <?php $status = get_status($row['valid_upto']); ?>
                    <tr data-payment-scheme="<?php echo htmlspecialchars($row['payment_scheme']); ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($row['name_en']); ?></strong><br>
                            //<br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['name_hi']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['address_en']); ?><br>
                            //<br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['address_hi']); ?></small>
                        </td>
                        <td class="text-center"><?php echo htmlspecialchars($row['state']); ?></td>
                        <td class="text-center">
                            <?php echo htmlspecialchars($row['contact_person']); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['contact_number']); ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge <?php echo $status['class']; ?>"><?php echo $status['text']; ?></span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-sm btn-info view-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewHospitalModal"
                                    data-name-en="<?php echo htmlspecialchars($row['name_en']); ?>"
                                    data-name-hi="<?php echo htmlspecialchars($row['name_hi']); ?>"
                                    data-address-en="<?php echo htmlspecialchars($row['address_en']); ?>"
                                    data-address-hi="<?php echo htmlspecialchars($row['address_hi']); ?>"
                                    data-state="<?php echo htmlspecialchars($row['state']); ?>"
                                    data-payment-scheme="<?php echo htmlspecialchars($row['payment_scheme']); ?>"
                                    data-contact-person="<?php echo htmlspecialchars($row['contact_person']); ?>"
                                    data-contact-number="<?php echo htmlspecialchars($row['contact_number']); ?>"
                                    data-valid-from="<?php echo htmlspecialchars($row['valid_from'] ? date('d-M-Y', strtotime($row['valid_from'])) : 'N/A'); ?>"
                                    data-valid-upto="<?php echo htmlspecialchars($row['valid_upto'] ? date('d-M-Y', strtotime($row['valid_upto'])) : 'N/A'); ?>"
                                    data-reg-valid-upto="<?php echo htmlspecialchars($row['reg_valid_upto'] ? date('d-M-Y', strtotime($row['reg_valid_upto'])) : 'N/A'); ?>"
                                    data-remarks-en="<?php echo htmlspecialchars($row['remarks_en']); ?>"
                                    data-remarks-hi="<?php echo htmlspecialchars($row['remarks_hi']); ?>"
                                    data-approv-order-doc="<?php echo htmlspecialchars($row['approv_order_accomodation']); ?>"
                                    data-tariff-doc="<?php echo htmlspecialchars($row['tariff']); ?>"
                                    data-facilitation-doc="<?php echo htmlspecialchars($row['facilitation']); ?>"
                                >
                                    <i class="fas fa-eye me-1"></i>View Details
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Hospital Modal -->
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
                    <div class="detail-item"><strong>State</strong><span id="modalState"></span></div>
                    <div class="detail-item"><strong>Payment Scheme</strong><span id="modalPaymentScheme"></span></div>
                    <div class="detail-item"><strong>Contact Person</strong><span id="modalContactPerson"></span></div>
                    <div class="detail-item"><strong>Contact Number</strong><span id="modalContactNumber"></span></div>
                </div>

                <h5><i class="fas fa-calendar-alt me-2"></i>Validity Dates</h5>
                <div class="detail-grid">
                    <div class="detail-item"><strong>Valid From</strong><span id="modalValidFrom"></span></div>
                    <div class="detail-item"><strong>Valid Upto</strong><span id="modalValidUpto"></span></div>
                    <div class="detail-item"><strong>Registration Valid Upto</strong><span id="modalRegValidUpto"></span></div>
                </div>

                <h5><i class="fas fa-file-alt me-2"></i>Documents</h5>
                <div id="modalDocuments" class="list-group"></div>

                <h5><i class="fas fa-comment-dots me-2"></i>Remarks</h5>
                <p id="modalRemarksEn" class="mb-1"></p>
                <p id="modalRemarksHi" class="text-muted"></p>
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
            setText('modalPaymentScheme', 'data-payment-scheme');
            setText('modalContactPerson', 'data-contact-person');
            setText('modalContactNumber', 'data-contact-number');
            setText('modalValidFrom', 'data-valid-from');
            setText('modalValidUpto', 'data-valid-upto');
            setText('modalRegValidUpto', 'data-reg-valid-upto');
            setText('modalRemarksEn', 'data-remarks-en');
            setText('modalRemarksHi', 'data-remarks-hi');

            const modalDocsContainer = viewModal.querySelector('#modalDocuments');
            modalDocsContainer.innerHTML = ''; 

            const createDocLink = (label, path) => {
                if (path && path !== 'N/A') {
                    return `<a href="${path}" target="_blank" class="list-group-item list-group-item-action">
                                <i class="fas fa-file-pdf me-2 text-danger"></i>${label}
                            </a>`;
                }
                return `<div class="list-group-item"><i class="fas fa-times-circle me-2 text-muted"></i>${label} (Not available)</div>`;
            };

            modalDocsContainer.innerHTML += createDocLink('Approval Order/Accommodation', button.getAttribute('data-approv-order-doc'));
            modalDocsContainer.innerHTML += createDocLink('Tariff Document', button.getAttribute('data-tariff-doc'));
            modalDocsContainer.innerHTML += createDocLink('Facilitation Document', button.getAttribute('data-facilitation-doc'));
        });
    }

    const tableRows = document.querySelectorAll('#hospitalTable tbody tr');
    tableRows.forEach((row, index) => {
        row.classList.add('animate__animated', 'animate__fadeInUp');
        row.style.animationDelay = `${index * 0.05}s`;
    });

    const searchFilter = document.getElementById('searchFilter');
    const stateFilter = document.getElementById('stateFilter');
    const paymentSchemeFilter = document.getElementById('paymentSchemeFilter');
    const resetBtn = document.getElementById('resetFilters');
    const exportBtn = document.getElementById('exportCsvBtn');

    function applyFilters() {
        const searchTerm = searchFilter.value.toLowerCase();
        const selectedState = stateFilter.value;
        const selectedScheme = paymentSchemeFilter.value;

        tableRows.forEach(row => {
            const name = row.cells[0].textContent.toLowerCase();
            const address = row.cells[1].textContent.toLowerCase();
            const state = row.cells[2].textContent;
            const scheme = row.dataset.paymentScheme;

            const searchMatch = searchTerm === '' || name.includes(searchTerm) || address.includes(searchTerm);
            const stateMatch = selectedState === '' || state === selectedState;
            const schemeMatch = selectedScheme === '' || scheme === selectedScheme;

            if (searchMatch && stateMatch && schemeMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Update export link with current filters
        const params = new URLSearchParams();
        if (searchFilter.value) params.append('search', searchFilter.value);
        if (stateFilter.value) params.append('state', stateFilter.value);
        if (paymentSchemeFilter.value) params.append('scheme', paymentSchemeFilter.value);
        exportBtn.href = `export_viewer.php?${params.toString()}`;
    }

    searchFilter.addEventListener('keyup', applyFilters);
    stateFilter.addEventListener('change', applyFilters);
    paymentSchemeFilter.addEventListener('change', applyFilters);

    resetBtn.addEventListener('click', () => {
        searchFilter.value = '';
        stateFilter.value = '';
        paymentSchemeFilter.value = '';
        applyFilters();
    });

    applyFilters();
});
</script>
</body>
</html>
