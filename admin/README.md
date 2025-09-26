# Sportex Admin Panel

## Security Notice
⚠️ **IMPORTANT**: This admin directory contains sensitive management tools. Ensure proper security measures are in place.

## Files Overview

### Core Admin Files
- `login.php` - Admin login page with secure authentication
- `dashboard.php` - Main admin dashboard with image management
- `admin.js` - Frontend JavaScript for dashboard functionality

### Backend Handlers
- `image_handler.php` - Handles image operations (list, rename, delete)
- `upload_handler.php` - Processes file uploads with validation
- `stats_handler.php` - Provides dashboard statistics

### Setup & Management
- `setup.php` - Initial database and admin user setup
- `add_admin.php` - Script to add additional admin users

## Quick Setup Guide

### 1. Database Setup in cPanel

1. **Log into cPanel**
2. **MySQL Databases**:
   - Create new database: `yourdomain_sportex`
   - Create database user with strong password
   - Add user to database with ALL PRIVILEGES

3. **Database Configuration**:
   ```sql
   Database: yourdomain_sportex
   User: yourdomain_admin
   Password: [strong password]
   Host: localhost
   ```

### 2. Configure Database Connection

Update these files with your database credentials:
- `login.php` (lines 6-9)
- `dashboard.php` (lines 14-17)

```php
$host = 'localhost';
$dbname = 'yourdomain_sportex';
$username = 'yourdomain_admin';
$password = 'your_secure_password';
```

### 3. Run Setup Script

1. Upload all admin files to `/admin/` directory
2. Run setup script: `yoursite.com/admin/setup.php`
3. Delete `setup.php` after successful setup

### 4. First Login

- URL: `yoursite.com/admin/login.php`
- Default credentials:
  - Email: `admin@sportex.rs`
  - Password: `SportexAdmin2024!`
- **Change password immediately!**

## Features

### Dashboard Overview
- Real-time statistics
- Image count by category
- Recent uploads tracking
- Quick action buttons

### Image Management
- **Browse Images**: View all images by category
- **Upload**: Drag & drop multiple files
- **Rename**: Change image filenames
- **Delete**: Remove unwanted images
- **Validation**: File type and size checking

### Security Features
- Session-based authentication
- Password hashing with PHP's `password_hash()`
- File type validation
- SQL injection protection
- XSS prevention

## Supported File Types
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- Maximum size: 5MB per file

## Directory Structure
```
admin/
├── login.php              # Login page
├── dashboard.php           # Main dashboard
├── admin.js               # Frontend JavaScript
├── image_handler.php      # Image operations API
├── upload_handler.php     # File upload handler
├── stats_handler.php      # Statistics API
├── setup.php              # Database setup (delete after use)
├── add_admin.php          # Add new admin users
└── README.md              # This file
```

## Security Recommendations

1. **Strong Passwords**: Use complex passwords for admin accounts
2. **HTTPS**: Always use SSL certificates in production
3. **File Permissions**: Set proper file permissions (644 for PHP files)
4. **Regular Updates**: Keep PHP and server software updated
5. **Backup**: Regular database and file backups
6. **Access Control**: Consider IP restrictions for admin area

## Adding New Admin Users

1. Edit `add_admin.php` with new user details
2. Run the script: `yoursite.com/admin/add_admin.php`
3. Delete the script after use

## Troubleshooting

### Database Connection Issues
- Verify cPanel database credentials
- Check database user privileges
- Ensure database exists

### File Upload Issues
- Check PHP `upload_max_filesize` setting
- Verify directory permissions (755 for directories, 644 for files)
- Ensure target directories exist

### Login Problems
- Clear browser cookies/cache
- Check session configuration
- Verify password hash in database

## Support

For technical support or questions about the admin panel, contact the development team or refer to the main project documentation.
