<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Database configuration (same as login.php)
$host = 'localhost';
$dbname = 'sport555_sportex';
$username = 'sport555_admin';
$password = 'WhoisthemaN123!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get image categories
$image_base_path = '../assets/img/png/';
$categories = [];
if (is_dir($image_base_path)) {
    $categories = array_filter(scandir($image_base_path), function($item) use ($image_base_path) {
        return $item !== '.' && $item !== '..' && is_dir($image_base_path . $item);
    });
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportex Admin Dashboard</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet">
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <style>
        :root {
            --sportex-red: #b22222;
            --sportex-dark-red: #8b0000;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Open Sans', sans-serif;
        }
        
        .navbar {
            background: var(--sportex-red) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 76px);
            border-right: 1px solid #dee2e6;
            box-shadow: 2px 0 4px rgba(0,0,0,.05);
        }
        
        .sidebar .nav-link {
            color: #495057;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 0;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--sportex-red);
            color: white;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--sportex-red), var(--sportex-dark-red));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
        }
        
        .btn-sportex {
            background: var(--sportex-red);
            border-color: var(--sportex-red);
            color: white;
        }
        
        .btn-sportex:hover {
            background: var(--sportex-dark-red);
            border-color: var(--sportex-dark-red);
            color: white;
        }
        
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        
        .image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
            transition: transform 0.3s;
        }
        
        .image-item:hover {
            transform: translateY(-5px);
        }
        
        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .image-item:hover .image-overlay {
            opacity: 1;
        }
        
        .image-name {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 8px;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .image-name .text-muted {
            display: block;
            font-size: 0.8rem;
            opacity: 0.7;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            text-align: center;
        }
        
        .dropzone {
            border: 3px dashed var(--sportex-red) !important;
            border-radius: 12px !important;
            background: rgba(178, 34, 34, 0.05) !important;
        }
        
        .category-selector {
            margin-bottom: 20px;
        }
        
        .alert-custom {
            border-radius: 8px;
            border: none;
        }

        .subdirs {
            text-align: left;
            font-size: 0.9em;
        }

        .subdir-item {
            padding: 4px 8px;
            margin: 2px 0;
            background: rgba(178, 34, 34, 0.05);
            border-radius: 4px;
        }

        .subdir-item i {
            margin-right: 4px;
            color: var(--sportex-red);
        }

        .total-count {
            font-weight: bold;
            color: var(--sportex-red);
        }
        
        .badge {
            background-color: rgba(178, 34, 34, 0.1) !important;
            color: var(--sportex-red) !important;
            font-weight: 600;
            padding: 0.25em 0.6em;
            margin-left: 0.5em;
        }
        i.bi.bi-folder:hover {
            text-shadow: 1px 1px 2px #b22222;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-gear-fill"></i> SPORTEX Admin</a>
            
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </span>
                <a class="nav-link" href="?logout=1">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="nav flex-column p-3">
                    <a class="nav-link active" href="javascript:void(0);" onclick="showSection('dashboard')">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="javascript:void(0);" onclick="showSection('images')">
                        <i class="bi bi-images"></i> Manage Images
                    </a>
                    <a class="nav-link" href="javascript:void(0);" onclick="showSection('upload')">
                        <i class="bi bi-cloud-upload"></i> Upload Images
                    </a>
                    <a class="nav-link" href="javascript:void(0);" onclick="showSection('categories')">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <h2><i class="bi bi-speedometer2"></i> Dashboard Overview</h2>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <h3 id="total-images">0</h3>
                                    <p>Total Images</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card" style="background: linear-gradient(135deg, #2196F3, #1976D2);">
                                <div class="card-body">
                                    <h3><?php echo count($categories); ?></h3>
                                    <p>Categories</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card" style="background: linear-gradient(135deg, #FF9800, #F57C00);">
                                <div class="card-body">
                                    <h3 id="recent-uploads">0</h3>
                                    <p>Recent Uploads</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);">
                                <div class="card-body">
                                    <h3><?php echo date('M d, Y'); ?></h3>
                                    <p>Today's Date</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-info-circle"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <button class="btn btn-sportex w-100" onclick="showSection('upload'); return false;">
                                        <i class="bi bi-cloud-upload"></i> Upload New Images
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-outline-secondary w-100" onclick="showSection('images'); return false;">
                                        <i class="bi bi-images"></i> Browse Images
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-outline-info w-100" onclick="refreshStats(); return false;">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh Stats
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images Management Section -->
                <div id="images-section" class="content-section" style="display: none;">
                    <h2><i class="bi bi-images"></i> Manage Images</h2>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5>Image Gallery</h5>
                        </div>
                        <div class="card-body">
                            <div class="category-selector">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="category-select">Select Category:</label>
                                        <select id="category-select" class="form-select" onchange="loadDirectoryStructure()">
                                            <option value="">Choose a category...</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category); ?>">
                                                    <?php echo htmlspecialchars($category); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="subdir-select">Select Subdirectory:</label>
                                        <select id="subdir-select" class="form-select" onchange="loadImages()">
                                            <option value="">Root directory</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="images-container" class="image-gallery">
                                <p class="text-muted">Select a category to view images</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Section -->
                <div id="upload-section" class="content-section" style="display: none;">
                    <h2><i class="bi bi-cloud-upload"></i> Upload Images</h2>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5>Upload New Images</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="upload-category">Select Category:</label>
                                    <select id="upload-category" class="form-select" onchange="loadUploadDirectoryStructure()">
                                        <option value="">Choose category...</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category); ?>">
                                                <?php echo htmlspecialchars($category); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="upload-subdir">Select Subdirectory:</label>
                                    <select id="upload-subdir" class="form-select">
                                        <option value="">Root directory</option>
                                    </select>
                                </div>
                            </div>
                            
                            <form id="upload-dropzone" class="dropzone" action="upload_handler.php" method="post">
                                <input type="hidden" name="category" id="dropzone-category">
                                <input type="hidden" name="subdir" id="dropzone-subdir">
                                <div class="dz-message">
                                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: var(--sportex-red);"></i>
                                    <p><strong>Drop files here or click to upload</strong></p>
                                    <p class="text-muted">Supports: JPG, PNG, GIF (Max: 5MB each)</p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Categories Section -->
                <div id="categories-section" class="content-section" style="display: none;">
                    <h2><i class="bi bi-tags"></i> Manage Categories</h2>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5>Current Categories</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($categories as $category): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <i class="bi bi-folder" style="font-size: 2rem; color: var(--sportex-red);"></i>
                                                    <h6 class="mt-2"><?php echo htmlspecialchars($category); ?></h6>
                                                </div>
                                                <div class="mt-3">
                                                    <p class="mb-2">Total:&nbsp;<span class="total-count" id="count-<?php echo preg_replace('/[^a-zA-Z0-9-]/', '-', $category); ?>">Loading...</span></p>
                                                    <div class="subdirs" id="subdirs-<?php echo preg_replace('/[^a-zA-Z0-9-]/', '-', $category); ?>"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Rename Modal -->
    <div class="modal fade" id="renameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rename Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current-name" class="form-label">Current Name:</label>
                        <input type="text" id="current-name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="new-name" class="form-label">New Name:</label>
                        <input type="text" id="new-name" class="form-control" placeholder="Enter new filename">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sportex" onclick="confirmRename()">Rename</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="admin.js"></script>
    <script>
        // Verify Dropzone is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dropzone loaded:', typeof Dropzone !== 'undefined');
            console.log('Upload form found:', document.getElementById('upload-dropzone'));
        });
    </script>
</body>
</html>
