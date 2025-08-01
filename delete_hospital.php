<?php
require 'check_session.php';
require 'db.php';

if (!can_edit()) {
    $_SESSION['error'] = 'You do not have permission to perform this action.';
    header('Location: index.php');
    exit;
}


$hospitalId = $_GET['id'] ?? null;

if (!empty($hospitalId)) {
    try {

        $stmt = $pdo->prepare("SELECT ACC_Link_Add, LINK_ADD, Hosp_Offer FROM emp_hosp_name WHERE hosp_id = ?");
        $stmt->execute([$hospitalId]);
        $files_to_delete = $stmt->fetch(PDO::FETCH_ASSOC);


        $delete_stmt = $pdo->prepare("DELETE FROM emp_hosp_name WHERE hosp_id = ?");
        $delete_stmt->execute([$hospitalId]);


        if ($delete_stmt->rowCount() > 0 && $files_to_delete) {
            foreach ($files_to_delete as $file_path) {
                if (!empty($file_path) && file_exists($file_path)) {
                    @unlink($file_path); 
                }
            }
            $_SESSION['success'] = 'Hospital deleted successfully.';
        } else {
            $_SESSION['error'] = 'Hospital could not be found or was already deleted.';
        }
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
