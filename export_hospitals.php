<?php
require 'check_session.php';
require 'db.php';


if (!can_edit()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied. You do not have permission to access this feature.');
}


$filename = "hospitals_export_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');


$output = fopen('php://output', 'w');

// query
$sql = "SELECT 
    id, state, payment_scheme, name_en, name_hi, 
    address_en, address_hi, contact_person, contact_number, 
    valid_from, valid_upto, reg_valid_upto, remarks_en, remarks_hi, 
    approv_order_accomodation, tariff, facilitation, created_at 
    FROM hospitals";

$params = [];
$whereClauses = [];

//  state filter
if (!empty($_GET['state'])) {
    $whereClauses[] = "state = ?";
    $params[] = $_GET['state'];
}

// scheme filter
if (!empty($_GET['scheme'])) {
    $whereClauses[] = "payment_scheme = ?";
    $params[] = $_GET['scheme'];
}

// status filter
if (!empty($_GET['status'])) {
    if ($_GET['status'] === 'active') {
        $whereClauses[] = "(valid_upto >= CURDATE() AND reg_valid_upto >= CURDATE())";
    } elseif ($_GET['status'] === 'inactive') {
        $whereClauses[] = "(valid_upto < CURDATE() OR reg_valid_upto < CURDATE())";
    }
}

// Handle search filter
if (!empty($_GET['search'])) {
    $searchTerm = '%' . htmlspecialchars($_GET['search']) . '%';
    $whereClauses[] = "(name_en LIKE ? OR name_hi LIKE ? OR address_en LIKE ? OR address_hi LIKE ? OR state LIKE ? OR contact_person LIKE ?)";
    $params = array_merge($params, array_fill(0, 6, $searchTerm));
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}

$header = [
    'ID', 'State', 'Payment Scheme', 'Name (EN)', 'Name (HI)', 
    'Address (EN)', 'Address (HI)', 'Contact Person', 'Contact Number', 
    'Valid From', 'Valid Upto', 'Reg Valid Upto', 'Remarks (EN)', 'Remarks (HI)', 
    'Approval Order/Accommodation', 'Tariff', 'Facilitation', 'Created At'
];
fputcsv($output, $header);

try {
    $sql .= " ORDER BY name_en ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} catch (PDOException $e) {
    error_log("CSV Export Error: " . $e->getMessage());
    fputcsv($output, ['Error exporting data. Please check server logs.']);
}

fclose($output);
exit;