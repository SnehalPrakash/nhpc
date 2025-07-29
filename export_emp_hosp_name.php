<?php
require 'check_session.php'; 
require 'db.php';


if (!can_edit()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied.');
}


$sql = "SELECT * FROM hospitals";
$params = [];
$whereClauses = [];

// Handle search term
if (!empty($_GET['search'])) {
    $searchTerm = '%' . htmlspecialchars($_GET['search']) . '%';
    $whereClauses[] = "(name_en LIKE ? OR address_en LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Handle state filter
if (!empty($_GET['state'])) {
    $whereClauses[] = "state = ?";
    $params[] = $_GET['state'];
}

// Handle payment scheme filter
if (!empty($_GET['scheme'])) {
    $whereClauses[] = "payment_scheme = ?";
    $params[] = $_GET['scheme'];
}

// Handle status filter from index.php
if (!empty($_GET['status'])) {
    if ($_GET['status'] === 'active') {
        $whereClauses[] = "(valid_upto >= CURDATE() OR valid_upto IS NULL)";
    } elseif ($_GET['status'] === 'inactive') {
        $whereClauses[] = "valid_upto < CURDATE()";
    }
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}

$sql .= " ORDER BY name_en ASC";

// Set headers for CSV download
$filename = "hospitals_admin_export_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');


$output = fopen('php://output', 'w');


$header = [
    'ID', 'Name (EN)', 'Name (HI)', 'Address (EN)', 'Address (HI)', 
    'State', 'Payment Scheme', 'Contact Person', 'Contact Number', 
    'Valid From', 'Valid Upto', 'Reg Valid Upto', 'Remarks (EN)', 'Remarks (HI)'
];
fputcsv($output, $header);

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);


    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $csvRow = [
            $row['id'],
            $row['name_en'],
            $row['name_hi'],
            $row['address_en'],
            $row['address_hi'],
            $row['state'],
            $row['payment_scheme'],
            $row['contact_person'],
            $row['contact_number'],
            $row['valid_from'],
            $row['valid_upto'],
            $row['reg_valid_upto'],
            $row['remarks_en'],
            $row['remarks_hi'],
        ];
        fputcsv($output, $csvRow);
    }
} catch (PDOException $e) {
    error_log("Admin CSV Export Error: " . $e->getMessage());
    fputcsv($output, ['Error exporting data. Please check server logs.']);
}

fclose($output);
exit;