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
            
            foreach ($dirs as $dir) {
                $category_path = $base_path . $dir;
                $files = scandir($category_path);
                $image_count = 0;
                
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && !is_dir($category_path . '/' . $file)) {
                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $image_count++;
                        }
                    }
                }
                
                $categories[$dir] = $image_count;
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
            $dirs = array_filter(scandir($base_path), function($item) use ($base_path) {
                return $item !== '.' && $item !== '..' && is_dir($base_path . $item);
            });
            
            foreach ($dirs as $dir) {
                $category_path = $base_path . $dir;
                $files = scandir($category_path);
                
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && !is_dir($category_path . '/' . $file)) {
                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $total_images++;
                            
                            // Check if file was modified today (recent upload)
                            $file_path = $category_path . '/' . $file;
                            $file_date = date('Y-m-d', filemtime($file_path));
                            if ($file_date === $today) {
                                $recent_uploads++;
                            }
                        }
                    }
                }
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
