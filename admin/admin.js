// Global variables
let currentCategory = '';
let currentImagePath = '';
let dropzoneInstance = null;

// Show/hide sections
function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.style.display = 'none');
    
    // Show selected section
    document.getElementById(sectionName + '-section').style.display = 'block';
    
    // Update active nav link
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    navLinks.forEach(link => link.classList.remove('active'));
    
    const activeLink = document.querySelector(`[href="#${sectionName}"]`);
    if (activeLink) activeLink.classList.add('active');
}

// Refresh statistics
function refreshStats() {
    console.log('Refreshing stats...');
    fetch('stats_handler.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-images').textContent = data.stats.total_images;
                document.getElementById('recent-uploads').textContent = data.stats.recent_uploads;
            }
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing dashboard...');
    
    // Call refreshStats immediately
    refreshStats();
    loadCategoryCounts();
    
    // Show dashboard section by default
    showSection('dashboard');
    
    // Initialize Dropzone
    try {
        const uploadForm = document.getElementById('upload-dropzone');
        if (!uploadForm) {
            console.error('Upload form element not found');
            return;
        }

        console.log('Initializing Dropzone...');
        
        // Initialize Dropzone
        Dropzone.autoDiscover = false;

        dropzoneInstance = new Dropzone('form#upload-dropzone', {
            url: 'upload_handler.php',
            method: 'post',
            maxFilesize: 5,
            timeout: 180000,
            acceptedFiles: 'image/jpeg,image/png,image/gif',
            addRemoveLinks: true,
            createImageThumbnails: true,
            paramName: 'file',
            clickable: true,
            uploadMultiple: false,
            autoProcessQueue: true,
            init: function() {
                let myDropzone = this;

                // When files are added
                this.on('addedfile', function(file) {
                    console.log('File added:', file.name);
                    const category = document.getElementById('upload-category').value;
                    const subdir = document.getElementById('upload-subdir').value;
                    
                    if (!category) {
                        console.error('No category selected');
                        alert('Please select a category first!');
                        this.removeFile(file);
                        return false;
                    }
                    
                    // Update the hidden inputs
                    document.getElementById('dropzone-category').value = category;
                    document.getElementById('dropzone-subdir').value = subdir;
                });

                // Before sending the file
                this.on('sending', function(file, xhr, formData) {
                    const category = document.getElementById('upload-category').value;
                    const subdir = document.getElementById('upload-subdir').value;
                    
                    if (!category) {
                        console.error('No category selected');
                        this.removeFile(file);
                        return false;
                    }

                    // Clean up the paths and add to form data
                    const cleanCategory = category.trim().replace(/^\/+|\/+$/g, '');
                    const cleanSubdir = subdir.trim().replace(/^\/+|\/+$/g, '');
                    
                    console.log('SENDING FILE DEBUG:', {
                        originalCategory: category,
                        originalSubdir: subdir,
                        cleanCategory: cleanCategory,
                        cleanSubdir: cleanSubdir,
                        filename: file.name
                    });
                    
                    formData.set('category', cleanCategory);
                    if (cleanSubdir) {
                        formData.set('subdir', cleanSubdir);
                    }
                    
                    console.log('Form data entries:', Array.from(formData.entries()));
                });

                // On successful upload
                this.on('success', function(file, response) {
                    console.log('Upload completed:', {
                        file: file.name,
                        response: response
                    });

                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Failed to parse response:', e);
                            showAlert('danger', 'Invalid server response');
                            this.removeFile(file);
                            return;
                        }
                    }
                    
                    if (response.success) {
                        console.log('Upload successful:', file.name);
                        showAlert('success', 'File uploaded successfully!');
                        refreshStats();
                        loadCategoryCounts();
                    } else {
                        console.error('Upload failed:', response);
                        showAlert('danger', response.message || 'Upload failed');
                        this.removeFile(file);
                    }
                });

                // On upload error
                this.on('error', function(file, message, xhr) {
                    console.error('Upload error:', {
                        file: file.name,
                        message: message,
                        category: document.getElementById('upload-category').value,
                        status: xhr?.status,
                        response: xhr?.responseText
                    });
                    
                    let errorMessage = message;
                    if (xhr?.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || message;
                        } catch (e) {
                            // Use original message if response isn't JSON
                        }
                    }
                    
                    showAlert('danger', 'Upload error: ' + errorMessage);
                    this.removeFile(file);
                });

                this.on('complete', function(file) {
                    console.log('Upload complete:', file.name);
                });
            }
        });

        console.log('Dropzone initialized successfully');
    } catch (error) {
        console.error('Error initializing Dropzone:', error);
    }
    
    // Load initial stats
    refreshStats();
    // Delay category counts slightly to ensure they don't conflict
    setTimeout(loadCategoryCounts, 100);
    
    // Add event listeners for category and subdirectory selection
    const uploadCategory = document.getElementById('upload-category');
    const uploadSubdir = document.getElementById('upload-subdir');
    
    if (uploadCategory) {
        uploadCategory.addEventListener('change', function() {
            loadUploadDirectoryStructure();
        });
    }
    
    if (uploadSubdir) {
        uploadSubdir.addEventListener('change', function() {
            const selectedSubdir = this.value;
            document.getElementById('dropzone-subdir').value = selectedSubdir;
        });
    }
});

// Load directory structure for selected category
function loadDirectoryStructure() {
    const category = document.getElementById('category-select').value;
    const subdirSelect = document.getElementById('subdir-select');
    
    // Reset subdir select
    subdirSelect.innerHTML = '<option value="">Root directory</option>';
    
    if (!category) {
        loadImages();
        return;
    }
    
    console.log('Loading directory structure for category:', category);
    
    fetch(`image_handler.php?action=get_structure&category=${encodeURIComponent(category)}`)
        .then(response => response.json())
        .then(data => {
            console.log('Directory structure response:', data);
            if (data.success && data.structure && Array.isArray(data.structure)) {
                // Add each directory to the dropdown
                data.structure.forEach(dir => {
                    const option = document.createElement('option');
                    option.value = dir.path;
                    option.textContent = dir.path;
                    subdirSelect.appendChild(option);
                });
                console.log('Added directories count:', data.structure.length);
            } else {
                console.error('Invalid directory structure response:', data);
            }
            loadImages();
        })
        .catch(error => {
            console.error('Error loading directory structure:', error);
            loadImages();
        });
}

// Load images for selected category and subdirectory
function loadImages() {
    const category = document.getElementById('category-select').value;
    const subdir = document.getElementById('subdir-select').value;
    const container = document.getElementById('images-container');
    
    if (!category) {
        container.innerHTML = '<p class="text-muted">Select a category to view images</p>';
        return;
    }
    
    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
    
    const url = `image_handler.php?action=list&category=${encodeURIComponent(category)}${subdir ? '&subdir=' + encodeURIComponent(subdir) : ''}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayImages(data.images, category, subdir);
            } else {
                container.innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
            }
        })
        .catch(error => {
            container.innerHTML = `<div class="alert alert-danger">Error loading images: ${error.message}</div>`;
        });
}

// Display images in gallery
function displayImages(images, category, subdir = '') {
    const container = document.getElementById('images-container');
    
    if (images.length === 0) {
        container.innerHTML = '<p class="text-muted">No images found in this location</p>';
        return;
    }
    
    let html = '';
    images.forEach(image => {
        // For root directory listing, image might already include subdirectory path
        let displayPath, fullPath;
        if (subdir) {
            // We're in a specific subdirectory
            displayPath = image;
            fullPath = `${category}/${subdir}/${image}`;
        } else {
            // We're in root, showing all images
            displayPath = image;
            fullPath = `${category}/${image}`;
        }
        
        // Get just the filename for display
        const filename = displayPath.split('/').pop();
        // Get subdirectory path if any
        const subdirPath = displayPath.includes('/') ? displayPath.substring(0, displayPath.lastIndexOf('/')) : '';
        
        html += `
            <div class="image-item">
                <img src="../assets/img/png/${fullPath}" alt="${filename}" loading="lazy">
                <div class="image-overlay">
                    <button class="btn btn-sm btn-warning me-1" onclick="renameImage('${category}', '${displayPath}')" title="Rename">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteImage('${category}', '${displayPath}')" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="image-name">
                    ${subdirPath ? `<span class="text-muted small">${subdirPath}/</span>` : ''}
                    ${filename}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Rename image
function renameImage(category, filepath) {
    currentCategory = category;
    currentImagePath = filepath;
    
    // Split path into directory and filename
    const pathParts = filepath.split('/');
    const filename = pathParts.pop();
    const subdir = pathParts.length > 0 ? pathParts.join('/') + '/' : '';
    
    document.getElementById('current-name').value = filepath;
    // Only allow renaming the file, not the path
    document.getElementById('new-name').value = filename.split('.').slice(0, -1).join('.');
    
    new bootstrap.Modal(document.getElementById('renameModal')).show();
}

// Confirm rename
function confirmRename() {
    const newName = document.getElementById('new-name').value.trim();
    if (!newName) {
        alert('Please enter a new name');
        return;
    }
    
    // Preserve the subdirectory path
    const pathParts = currentImagePath.split('/');
    const oldFilename = pathParts.pop();
    const extension = oldFilename.split('.').pop();
    const newFullName = (pathParts.length > 0 ? pathParts.join('/') + '/' : '') + newName + '.' + extension;
    
    const formData = new FormData();
    formData.append('action', 'rename');
    formData.append('category', currentCategory);
    formData.append('old_name', currentImagePath);
    formData.append('new_name', newFullName);
    
    fetch('image_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Image renamed successfully!');
            bootstrap.Modal.getInstance(document.getElementById('renameModal')).hide();
            loadImages(); // Reload images
        } else {
            showAlert('danger', 'Rename failed: ' + data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Error: ' + error.message);
    });
}

// Delete image
function deleteImage(category, filename) {
    if (!confirm(`Are you sure you want to delete "${filename}"? This action cannot be undone.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('category', category);
    formData.append('filename', filename);
    
    fetch('image_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Image deleted successfully!');
            loadImages(); // Reload images
            refreshStats();
        } else {
            showAlert('danger', 'Delete failed: ' + data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Error: ' + error.message);
    });
}

// Refresh statistics
function refreshStats() {
    console.log('Refreshing stats...');
    fetch('stats_handler.php', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        }
    })
        .then(response => {
            console.log('Stats response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Stats data:', data);
            if (data.success) {
                document.getElementById('total-images').textContent = data.stats.total_images;
                document.getElementById('recent-uploads').textContent = data.stats.recent_uploads;
                console.log('Updated stats:', data.stats);
            } else {
                console.error('Invalid stats data:', data);
            }
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
}

// Load category counts
function loadCategoryCounts() {
    console.log('Loading category counts...');
    fetch('stats_handler.php?type=categories', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log('Category data:', data);
            if (data.success && data.categories) {
                Object.keys(data.categories).forEach(category => {
                    const sanitizedId = category.replace(/[^a-zA-Z0-9-]/g, '-');
                    const countContainer = document.getElementById(`count-${sanitizedId}`);
                    const subdirsContainer = document.getElementById(`subdirs-${sanitizedId}`);
                    
                    if (countContainer) {
                        const categoryData = data.categories[category];
                        
                        // Update the count display with both total count and detailed subdirs
                        let displayHtml = `${categoryData.count} files`;
                        countContainer.innerHTML = displayHtml;
                        
                        // If we have a subdirs container and there are subdirs, update it
                        if (subdirsContainer) {
                            if (categoryData.subdirs && Object.keys(categoryData.subdirs).length > 0) {
                                let subdirsHtml = '';
                                Object.entries(categoryData.subdirs)
                                    .sort(([a], [b]) => a.localeCompare(b))
                                    .forEach(([subdir, data]) => {
                                        subdirsHtml += `
                                            <div class="subdir-item">
                                                <i class="bi bi-folder2"></i>
                                                ${subdir} <span class="badge bg-light text-dark">${data.count}</span>
                                            </div>
                                        `;
                                    });
                                subdirsContainer.innerHTML = subdirsHtml;
                            } else {
                                subdirsContainer.innerHTML = '';
                            }
                        }
                        
                        console.log('Updated stats for', category);
                    } else {
                        console.log('Container not found for category:', category);
                    }
                });
            } else {
                console.error('Invalid data format:', data);
            }
        })
        .catch(error => {
            console.error('Error loading category counts:', error);
            const containers = document.querySelectorAll('[id^="count-"]');
            containers.forEach(container => {
                container.textContent = 'Error loading stats';
            });
        });
}

// Load directory structure for upload
function loadUploadDirectoryStructure() {
    const category = document.getElementById('upload-category').value;
    const subdirSelect = document.getElementById('upload-subdir');
    
    console.log('Loading upload directory structure for category:', category);
    
    // Completely clear and reset subdir select
    subdirSelect.innerHTML = '';
    subdirSelect.appendChild(new Option('Root directory', ''));
    
    if (!category) return;
    
    // Prevent multiple concurrent calls
    if (loadUploadDirectoryStructure.loading) {
        console.log('Upload directory structure already loading, skipping...');
        return;
    }
    loadUploadDirectoryStructure.loading = true;
    
    fetch(`image_handler.php?action=get_structure&category=${encodeURIComponent(category)}`)
        .then(response => response.json())
        .then(data => {
            console.log('Upload directory structure response:', data);
            if (data.success && data.structure && Array.isArray(data.structure)) {
                // Clear again to be absolutely sure
                const currentOptions = subdirSelect.querySelectorAll('option:not([value=""])');
                currentOptions.forEach(option => option.remove());
                
                // Add each directory to the dropdown
                data.structure.forEach(dir => {
                    const option = document.createElement('option');
                    option.value = dir.path;
                    option.textContent = dir.path;
                    subdirSelect.appendChild(option);
                });
                
                console.log('Added upload directories count:', data.structure.length);
                
                // Update form fields
                document.getElementById('dropzone-category').value = category;
                document.getElementById('dropzone-subdir').value = '';
            } else {
                console.error('Invalid upload directory structure response:', data);
            }
        })
        .catch(error => {
            console.error('Error loading upload directory structure:', error);
        })
        .finally(() => {
            loadUploadDirectoryStructure.loading = false;
        });
}

// Show alert message
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-custom');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-custom`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
