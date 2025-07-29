<?php
require 'db.php';

// Base query for valid hospitals, same as in viewer.php
$sql = "SELECT 
    state, payment_scheme, name_en, name_hi, 
    address_en, address_hi, contact_person, contact_number, 
    valid_from, valid_upto, reg_valid_upto, remarks_en, remarks_hi, 
    approv_order_accomodation, tariff, facilitation 
    FROM hospitals
    WHERE (valid_upto IS NULL OR valid_upto >= CURDATE())";

$params = [];
$whereClauses = [];

// Handle state filter from GET parameter
if (!empty($_GET['state'])) {
    $whereClauses[] = "state = ?";
    $params[] = $_GET['state'];
}

// Handle scheme filter from GET parameter
if (!empty($_GET['scheme'])) {
    $whereClauses[] = "payment_scheme = ?";
    $params[] = $_GET['scheme'];
}

// Handle search filter from GET parameter
if (!empty($_GET['search'])) {
    $searchTerm = '%' . htmlspecialchars($_GET['search']) . '%';
    // Search across multiple relevant text fields
    $whereClauses[] = "(name_en LIKE ? OR name_hi LIKE ? OR address_en LIKE ? OR address_hi LIKE ? OR state LIKE ? OR contact_person LIKE ?)";
    $params = array_merge($params, array_fill(0, 6, $searchTerm));
}

if (!empty($whereClauses)) {
    $sql .= " AND " . implode(' AND ', $whereClauses);
}

$sql .= " ORDER BY name_en ASC";

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
    'Approval Order/Accommodation', 'Tariff', 'Facilitation'
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