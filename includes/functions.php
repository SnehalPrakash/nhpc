<?php

/**
 * Returns true if the current user has the 'admin' role.
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Returns true if the user has permission to edit (admin or user role).
 * @return bool
 */
function can_edit() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'user']);
}

/**
 * Handles file uploads for both create and edit operations.
 *
 * @param string $fileKey The key in the $_FILES array.
 * @param string $uploadDir The directory to upload the file to.
 * @param string|null $existingPath The path to an existing file to be replaced.
 * @return array An array with 'path' and 'error' keys.
 */
function handle_file_upload(string $fileKey, string $uploadDir, ?string $existingPath = null): array {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => $existingPath, 'error' => null];
    }

    $file = $_FILES[$fileKey];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => $existingPath, 'error' => 'An error occurred during file upload for ' . htmlspecialchars($fileKey) . '.'];
    }

    if ($file['size'] > 10000000) { // 10MB limit
        return ['path' => $existingPath, 'error' => 'File ' . htmlspecialchars($fileKey) . ' is too large. Max 10MB.'];
    }

    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    $mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']);
    if (!in_array($mime_type, $allowed_types)) {
        return ['path' => $existingPath, 'error' => 'Invalid file type for ' . htmlspecialchars($fileKey) . '. Only PDF, JPG, PNG are allowed.'];
    }

    $filename = uniqid('', true) . '_' . basename(preg_replace("/[^a-zA-Z0-9\._-]/", "", $file['name']));
    $target_path = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        if ($existingPath && file_exists($existingPath)) {
            @unlink($existingPath);
        }
        return ['path' => $target_path, 'error' => null];
    }

    return ['path' => $existingPath, 'error' => 'Failed to move uploaded file for ' . htmlspecialchars($fileKey) . '.'];
}