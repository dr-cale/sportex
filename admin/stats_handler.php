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
$type = $_GET['type'] ?? 'general';

try {
    if ($type === 'categories') {
        // Get count for each category
        $categories = [];
        if (is_dir($base_path)) {
            $dirs = array_filter(scandir($base_path), function($item) use ($base_path) {
                return $item !== '.' && $item !== '..' && is_dir($base_path . $item);
            });
            
            function countImagesInDir($dir_path) {
                $structure = array(
                    'count' => 0,
                    'subdirs' => array()
                );
                
                if (!is_dir($dir_path)) {
                    error_log("Not a directory: {$dir_path}");
                    return $structure;
                }
                
                $files = scandir($dir_path);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    
                    $path = $dir_path . '/' . $file;
                    if (is_dir($path)) {
                        $subdir_result = countImagesInDir($path);
                        if ($subdir_result['count'] > 0 || !empty($subdir_result['subdirs'])) {
                            $structure['subdirs'][basename($path)] = $subdir_result;
                            $structure['count'] += $subdir_result['count'];
                        }
                    } else {
                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $structure['count']++;
                        }
                    }
                }
                
                error_log("Directory {$dir_path} has {$structure['count']} images and " . count($structure['subdirs']) . " subdirs");
                return $structure;
            }
            
            foreach ($dirs as $dir) {
                $category_path = $base_path . $dir;
                $result = countImagesInDir($category_path);
                $categories[$dir] = $result;
            }
        }
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } else {
        // General statistics
        $total_images = 0;
        $recent_uploads = 0;
        $today = date('Y-m-d');
        
        if (is_dir($base_path)) {
            function countRecursively($path, &$total_images, &$recent_uploads, $today) {
                if (!is_dir($path)) return;
                
                $files = scandir($path);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    
                    $full_path = $path . '/' . $file;
                    if (is_dir($full_path)) {
                        countRecursively($full_path, $total_images, $recent_uploads, $today);
                    } else {
                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $total_images++;
                            
                            $file_date = date('Y-m-d', filemtime($full_path));
                            if ($file_date === $today) {
                                $recent_uploads++;
                            }
                        }
                    }
                }
            }
            
            $dirs = array_filter(scandir($base_path), function($item) use ($base_path) {
                return $item !== '.' && $item !== '..' && is_dir($base_path . $item);
            });
            
            foreach ($dirs as $dir) {
                $category_path = $base_path . $dir;
                countRecursively($category_path, $total_images, $recent_uploads, $today);
            }
        }
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_images' => $total_images,
                'recent_uploads' => $recent_uploads,
                'last_updated' => date('Y-m-d H:i:s')
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
