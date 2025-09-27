<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$base_path = '../assets/img/png/';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_structure':
            $category = $_GET['category'] ?? '';
            if (empty($category)) {
                throw new Exception('Category is required');
            }
            
            $category_path = $base_path . $category;
            if (!is_dir($category_path)) {
                throw new Exception('Category directory does not exist');
            }

            // Simple function to get ALL subdirectories without duplicates - ULTRA SIMPLE VERSION
            function getDirectoryStructure($path, $basePath) {
                $directories = [];
                $processedPaths = [];
                
                if (!is_dir($path)) {
                    return [];
                }
                
                // Get immediate subdirectories first
                $items = glob($path . '/*', GLOB_ONLYDIR);
                
                foreach ($items as $item) {
                    $dirName = basename($item);
                    
                    // Add to processed so we don't duplicate
                    if (!in_array($dirName, $processedPaths)) {
                        $processedPaths[] = $dirName;
                        $directories[] = [
                            'path' => $dirName,
                            'type' => 'directory'
                        ];
                        
                        // Now check for subdirectories of this directory
                        $subitems = glob($item . '/*', GLOB_ONLYDIR);
                        foreach ($subitems as $subitem) {
                            $subDirName = basename($subitem);
                            $fullSubPath = $dirName . '/' . $subDirName;
                            
                            // Add subdirectory with full path if not already processed
                            if (!in_array($fullSubPath, $processedPaths)) {
                                $processedPaths[] = $fullSubPath;
                                $directories[] = [
                                    'path' => $fullSubPath,
                                    'type' => 'directory'
                                ];
                            }
                        }
                    }
                }
                
                return $directories;
            }
            
            $structure = getDirectoryStructure($category_path, $category_path);
            echo json_encode([
                'success' => true,
                'structure' => $structure
            ]);
            break;

        case 'list':
            $category = $_GET['category'] ?? '';
            $subdir = $_GET['subdir'] ?? '';
            if (empty($category)) {
                throw new Exception('Category is required');
            }
            
            $category_path = $base_path . $category;
            if (!is_dir($category_path)) {
                throw new Exception('Category directory does not exist');
            }

            // If subdir is specified, only look in that directory
            if (!empty($subdir)) {
                $category_path .= '/' . $subdir;
                if (!is_dir($category_path)) {
                    throw new Exception('Subdirectory does not exist');
                }
                // Get images only from this subdirectory
                $images = [];
                $files = scandir($category_path);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && !is_dir($category_path . '/' . $file)) {
                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $images[] = $file;
                        }
                    }
                }
            } else {
                // Get images only from the root of the category directory (no subdirectories)
                $images = [];
                $files = scandir($category_path);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && !is_dir($category_path . '/' . $file)) {
                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $images[] = $file;
                        }
                    }
                }
            }
            
            // Sort images by path
            sort($images);
            
            echo json_encode([
                'success' => true,
                'images' => $images,
                'count' => count($images)
            ]);
            break;
            
        case 'rename':
            $category = $_POST['category'] ?? '';
            $old_name = $_POST['old_name'] ?? '';
            $new_name = $_POST['new_name'] ?? '';
            
            if (empty($category) || empty($old_name) || empty($new_name)) {
                throw new Exception('Category, old name, and new name are required');
            }
            
            // Validate file names
            if (!preg_match('/^[a-zA-Z0-9._-]+$/', $new_name)) {
                throw new Exception('Invalid filename. Use only letters, numbers, dots, hyphens, and underscores.');
            }
            
            $category_path = $base_path . $category;
            $old_file_path = $category_path . '/' . $old_name;
            $new_file_path = $category_path . '/' . $new_name;
            
            if (!file_exists($old_file_path)) {
                throw new Exception('Original file does not exist');
            }
            
            if (file_exists($new_file_path)) {
                throw new Exception('A file with the new name already exists');
            }
            
            if (!rename($old_file_path, $new_file_path)) {
                throw new Exception('Failed to rename file');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'File renamed successfully'
            ]);
            break;
            
        case 'delete':
            $category = $_POST['category'] ?? '';
            $filename = $_POST['filename'] ?? '';
            
            if (empty($category) || empty($filename)) {
                throw new Exception('Category and filename are required');
            }
            
            $file_path = $base_path . $category . '/' . $filename;
            
            if (!file_exists($file_path)) {
                throw new Exception('File does not exist');
            }
            
            if (!unlink($file_path)) {
                throw new Exception('Failed to delete file');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
