<?php
require 'check_session.php';
require 'db.php';

if (!can_edit()) {
    $_SESSION['error'] = 'You do not have permission to perform this action.';
    header('Location: index.php');
    exit;
}

if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("SELECT approv_order_accomodation, tariff, facilitation FROM hospitals WHERE id = ?");
        $stmt->execute([$id]);
        $files_to_delete = $stmt->fetch();

        $delete_stmt = $pdo->prepare("DELETE FROM hospitals WHERE id = ?");
        $delete_stmt->execute([$id]);

        if ($delete_stmt->rowCount() > 0 && $files_to_delete) {
            foreach ($files_to_delete as $file_path) {
                if (!empty($file_path) && file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
        $_SESSION['success'] = 'Hospital deleted successfully.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting hospital. It might be referenced by other records.';
        error_log("Delete hospital error: " . $e->getMessage());
    }
} else {
    $_SESSION['error'] = 'Invalid or missing hospital ID for deletion.';
}
header('Location: index.php');
exit;
?>
