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

// Get upload parameters
$category = $_POST['category'] ?? '';
$subdir = $_POST['subdir'] ?? '';

error_log("=== UPLOAD DEBUG START ===");
error_log("RAW Category: '$category'");
error_log("RAW Subdir: '$subdir'");
error_log("POST data: " . print_r($_POST, true));

if (empty($category)) {
    error_log("Error: Category is empty. POST keys: " . implode(', ', array_keys($_POST)));
    echo json_encode([
        'success' => false, 
        'message' => 'Category is required',
        'debug' => [
            'post' => $_POST,
            'files' => array_keys($_FILES),
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'unknown'
        ]
    ]);
    exit;
}

// Clean category and subdir - SIMPLE APPROACH
$category = trim($category);
$subdir = trim($subdir);

// Remove dangerous stuff
$category = str_replace(['..', '\\'], '', $category);
$subdir = str_replace(['..', '\\'], '', $subdir);

error_log("CLEANED - Category: '$category', Subdir: '$subdir'");

// Build paths step by step
$base_path = '../assets/img/png';
$category_path = $base_path . '/' . $category;

error_log("Category path: '$category_path'");

// Check category exists
if (!is_dir($category_path)) {
    error_log("ERROR: Category path does not exist: '$category_path'");
    echo json_encode(['success' => false, 'message' => 'Category not found: ' . $category]);
    exit;
}

// IMPORTANT: The subdir should be the EXACT path relative to the category
// If subdir is "muski", we want kosarka/muski/, NOT kosarka/muski/muski/
if (!empty($subdir)) {
    $upload_directory = $category_path . '/' . $subdir;
    error_log("Upload directory with subdir: '$upload_directory'");
    
    // Create if doesn't exist
    if (!is_dir($upload_directory)) {
        error_log("Creating directory: '$upload_directory'");
        if (!mkdir($upload_directory, 0755, true)) {
            error_log("FAILED to create directory: '$upload_directory'");
            echo json_encode(['success' => false, 'message' => 'Cannot create directory']);
            exit;
        }
    }
} else {
    $upload_directory = $category_path;
    error_log("Using category root: '$upload_directory'");
}

error_log("FINAL upload directory: '$upload_directory'");
error_log("=== UPLOAD DEBUG END ===");

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
while (file_exists($upload_directory . '/' . $new_filename)) {
    $new_filename = $file_name . '_' . $counter . '.' . $file_extension;
    $counter++;
}

$destination = $upload_directory . '/' . $new_filename;

// Check write permissions
if (!is_writable($upload_directory)) {
    error_log("Error: Upload directory is not writable: " . $upload_directory);
    echo json_encode(['success' => false, 'message' => 'Upload directory is not writable']);
    exit;
}

// Move uploaded file
error_log("Attempting to move file from {$file['tmp_name']} to {$destination}");
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    error_log("Error: Failed to move uploaded file. PHP error: " . error_get_last()['message']);
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Set proper file permissions
error_log("Setting file permissions for: " . $destination);
if (!chmod($destination, 0644)) {
    error_log("Warning: Failed to set file permissions");
}

echo json_encode([
    'success' => true,
    'message' => 'File uploaded successfully',
    'filename' => $new_filename,
    'category' => $category,
    'size' => $file['size']
]);
?>
