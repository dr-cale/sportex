// Global variables
let currentCategory = '';
let currentImagePath = '';
let dropzoneInstance = null;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Dropzone
    Dropzone.autoDiscover = false;
    
    dropzoneInstance = new Dropzone("#upload-dropzone", {
        url: "upload_handler.php",
        maxFilesize: 5, // MB
        acceptedFiles: ".jpeg,.jpg,.png,.gif",
        addRemoveLinks: true,
        dictDefaultMessage: `
            <i class="bi bi-cloud-upload" style="font-size: 3rem; color: var(--sportex-red);"></i><br>
            <strong>Drop files here or click to upload</strong><br>
            <span class="text-muted">Supports: JPG, PNG, GIF (Max: 5MB each)</span>
        `,
        init: function() {
            this.on("sending", function(file, xhr, formData) {
                const category = document.getElementById('upload-category').value;
                if (!category) {
                    alert('Please select a category first!');
                    this.removeFile(file);
                    return;
                }
                formData.append("category", category);
            });
            
            this.on("success", function(file, response) {
                const result = JSON.parse(response);
                if (result.success) {
                    showAlert('success', 'File uploaded successfully!');
                    refreshStats();
                } else {
                    showAlert('danger', 'Upload failed: ' + result.message);
                }
            });
            
            this.on("error", function(file, errorMessage) {
                showAlert('danger', 'Upload error: ' + errorMessage);
            });
        }
    });
    
    // Load initial stats
    refreshStats();
    loadCategoryCounts();
});

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

// Load images for selected category
function loadImages() {
    const category = document.getElementById('category-select').value;
    const container = document.getElementById('images-container');
    
    if (!category) {
        container.innerHTML = '<p class="text-muted">Select a category to view images</p>';
        return;
    }
    
    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
    
    fetch(`image_handler.php?action=list&category=${encodeURIComponent(category)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayImages(data.images, category);
            } else {
                container.innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
            }
        })
        .catch(error => {
            container.innerHTML = `<div class="alert alert-danger">Error loading images: ${error.message}</div>`;
        });
}

// Display images in gallery
function displayImages(images, category) {
    const container = document.getElementById('images-container');
    
    if (images.length === 0) {
        container.innerHTML = '<p class="text-muted">No images found in this category</p>';
        return;
    }
    
    let html = '';
    images.forEach(image => {
        html += `
            <div class="image-item">
                <img src="../assets/img/png/${category}/${image}" alt="${image}" loading="lazy">
                <div class="image-overlay">
                    <button class="btn btn-sm btn-warning me-1" onclick="renameImage('${category}', '${image}')" title="Rename">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteImage('${category}', '${image}')" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="image-name">${image}</div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Rename image
function renameImage(category, filename) {
    currentCategory = category;
    currentImagePath = filename;
    
    document.getElementById('current-name').value = filename;
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
    
    const extension = currentImagePath.split('.').pop();
    const newFullName = newName + '.' + extension;
    
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

// Load category counts
function loadCategoryCounts() {
    fetch('stats_handler.php?type=categories')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.categories) {
                Object.keys(data.categories).forEach(category => {
                    const countElement = document.getElementById(`count-${category}`);
                    if (countElement) {
                        const count = data.categories[category];
                        countElement.textContent = `${count} image${count !== 1 ? 's' : ''}`;
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading category counts:', error);
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
