<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$category = $_POST['category'] ?? '';
if (empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Category is required']);
    exit;
}

$upload_dir = '../assets/img/png/' . $category . '/';

// Check if category directory exists
if (!is_dir($upload_dir)) {
    echo json_encode(['success' => false, 'message' => 'Category directory does not exist']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit',
        UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
    ];
    
    $message = $error_messages[$file['error']] ?? 'Unknown upload error';
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$file_type = $file['type'];
$file_info = finfo_open(FILEINFO_MIME_TYPE);
$detected_type = finfo_file($file_info, $file['tmp_name']);
finfo_close($file_info);

if (!in_array($detected_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
    exit;
}

// Validate file size (5MB max)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
    exit;
}

// Generate safe filename
$original_name = $file['name'];
$file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
$file_name = pathinfo($original_name, PATHINFO_FILENAME);

// Sanitize filename
$file_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file_name);
$new_filename = $file_name . '.' . $file_extension;

// Check if file already exists, add number suffix if needed
$counter = 1;
while (file_exists($upload_dir . $new_filename)) {
    $new_filename = $file_name . '_' . $counter . '.' . $file_extension;
    $counter++;
}

$destination = $upload_dir . $new_filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Set proper file permissions
chmod($destination, 0644);

echo json_encode([
    'success' => true,
    'message' => 'File uploaded successfully',
    'filename' => $new_filename,
    'category' => $category,
    'size' => $file['size']
]);
?>
