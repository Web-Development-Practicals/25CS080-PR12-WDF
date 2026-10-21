<?php
function upload_poster($file) {
    if (empty($file) || $file['error'] == UPLOAD_ERR_NO_FILE) return [null, null];

    if ($file['error'] == UPLOAD_ERR_INI_SIZE || $file['size'] > 2 * 1024 * 1024) {
        return [null, "File size limit exceeded: Poster must be 2 MB or less."];
    }
    if ($file['error'] != UPLOAD_ERR_OK) return [null, "Upload failed with error code: " . $file['error']];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        return [null, "Invalid file type: Only JPG, PNG, and WEBP files are allowed."];
    }

    if (!@getimagesize($file['tmp_name'])) {
        return [null, "Validation failed: Uploaded file is not a valid image."];
    }

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $new_name = time() . '_' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], 'uploads/' . $new_name)) {
        return [null, "Could not save the uploaded file to server directory."];
    }
    return [$new_name, null];
}