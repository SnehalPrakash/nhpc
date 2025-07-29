<?php

/**
 * Handles a single file upload, suitable for both creating and updating records.
 *
 * @param string $fileKey The key in the $_FILES array (e.g., 'tariff').
 * @param string $uploadDir The target directory for the uploaded file.
 * @param string|null $existingPath The path of an existing file to be replaced. If a new file is uploaded successfully, this old file will be deleted.
 * @return array An array with a 'path' key on success, or an 'error' key on failure.
 */
function handle_file_upload(string $fileKey, string $uploadDir, ?string $existingPath = null): array
{
    // Case 1: No new file was uploaded for this field.
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => $existingPath]; // Keep the existing path.
    }

    // Case 2: An error occurred during the upload.
    if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'An error occurred during file upload for ' . htmlspecialchars($fileKey) . '. Error code: ' . $_FILES[$fileKey]['error']];
    }

    // Security & Validation Checks
    if ($_FILES[$fileKey]['size'] > 10000000) { // 10MB limit
        return ['error' => 'File ' .
