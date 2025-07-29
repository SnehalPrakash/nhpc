<?php
require 'db.php';

// Base query for valid hospitals, same as in viewer.php
$sql = "SELECT 
    state, SCHEME, Hosp_name, Hosp_name_H, 
    hosp_add, hosp_add_H, hospital_contact_person, hospital_contact_number, 
    valid_from, VALID_UPTO, RegValidUptoDt, Rem, remarks_hi, 
    ACC_Link_Add, LINK_ADD, Hosp_Offer 
    FROM emp_hosp_name
    WHERE (VALID_UPTO IS NULL OR VALID_UPTO >= CURDATE())";

$params = [];
$whereClauses = [];

// Handle state filter from GET parameter
if (!empty($_GET['state'])) {
    $whereClauses[] = "state = ?";
    $params[] = $_GET['state'];
}

// Handle scheme filter from GET parameter
if (!empty($_GET['scheme'])) {
    $whereClauses[] = "SCHEME = ?";
    $params[] = $_GET['scheme'];
}

// Handle search filter from GET parameter
if (!empty($_GET['search'])) {
    $searchTerm = '%' . htmlspecialchars($_GET['search']) . '%';
    // Search across multiple relevant text fields
    $whereClauses[] = "(Hosp_name LIKE ? OR Hosp_name_H LIKE ? OR hosp_add LIKE ? OR hosp_add_H LIKE ? OR state LIKE ? OR hospital_contact_person LIKE ?)";
    $params = array_merge($params, array_fill(0, 6, $searchTerm));
}

if (!empty($whereClauses)) {
    $sql .= " AND " . implode(' AND ', $whereClauses);
}

$sql .= " ORDER BY Hosp_name ASC";

// Set headers for CSV download
$filename = "hospitals_viewer_export_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV header, matching the viewer table (without Serial No.)
$header = [
    'State', 'Payment Scheme', 'Name (EN)', 'Name (HI)',
    'Address (EN)', 'Address (HI)', 'Contact Person', 'Contact Number',
    'Valid From', 'Valid Upto', 'Reg Valid Upto', 'Remarks (EN)', 'Remarks (HI)',
    'Approval Order/Accommodation Link', 'Tariff Link', 'Facilitation Link'
];
fputcsv($output, $header);

try {
    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Loop through results and write to CSV
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} catch (PDOException $e) {
    error_log("Viewer CSV Export Error: " . $e->getMessage());
    // Optionally, output an error in the CSV.
    fputcsv($output, ['Error exporting data. Please check server logs.']);
}

fclose($output);
exit;