<?php
// Database setup script for Sportex Admin
// Run this once to create the admin_users table

// Enable error reporting for troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Sportex Admin Setup</h1>";
echo "<pre>";

$host = 'localhost';
$dbname = 'sport555_sportex';
$username = 'sport555_admin';
$password = 'WhoisthemaN123!';

echo "Attempting to connect to database...\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "Username: $username\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection successful!\n\n";
    
    // Create admin_users table
    echo "Creating admin_users table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_email (email),
        INDEX idx_username (username)
    )";
    
    $pdo->exec($sql);
    echo "✓ Table 'admin_users' created successfully.\n\n";
    
    // Check if any admin users exist
    echo "Checking for existing admin users...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
    $count = $stmt->fetchColumn();
    echo "Found $count existing admin users.\n\n";
    
    if ($count == 0) {
        echo "Creating default admin user...\n";
        // Create default admin user
        $default_email = 'admin@sportex.rs';
        $default_username = 'admin';
        $default_password = 'SportexAdmin2024!'; // Change this!
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$default_username, $default_email, $hashed_password]);
        
        echo "✓ Default admin user created successfully!\n\n";
        echo "LOGIN CREDENTIALS:\n";
        echo "==================\n";
        echo "Email: $default_email\n";
        echo "Password: $default_password\n";
        echo "==================\n\n";
        echo "*** IMPORTANT: Change this password immediately after first login! ***\n\n";
    } else {
        echo "Admin users already exist in the database.\n";
        echo "Listing existing users:\n";
        $stmt = $pdo->query("SELECT id, username, email, created_at, last_login FROM admin_users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            echo "ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Created: {$user['created_at']}\n";
        }
        echo "\n";
    }
    
    echo "✓ Setup completed successfully!\n\n";
    echo "NEXT STEPS:\n";
    echo "===========\n";
    echo "1. Delete this setup.php file for security\n";
    echo "2. Visit your admin login page: " . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/login.php\n";
    echo "3. Login with the credentials shown above\n";
    echo "4. Change the default password immediately\n\n";
    
    echo "ADMIN PANEL ACCESS:\n";
    echo "===================\n";
    echo "Login URL: http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/login.php\n";
    
} catch(PDOException $e) {
    echo "❌ Setup failed with database error:\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "TROUBLESHOOTING:\n";
    echo "================\n";
    echo "1. Check if the database 'sport555_sportex' exists in cPanel\n";
    echo "2. Verify the database user 'sport555_admin' has been created\n";
    echo "3. Ensure the user has ALL PRIVILEGES on the database\n";
    echo "4. Check if the password 'WhoisthemaN123!' is correct\n";
    echo "5. Contact your hosting provider if issues persist\n";
} catch(Exception $e) {
    echo "❌ Setup failed with general error:\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f5f5f5; padding: 20px; border-radius: 5px; }
h1 { color: #b22222; }
</style>
    
    echo "\nSetup completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Update database credentials in login.php and dashboard.php\n";
    echo "2. Change the default admin password\n";
    echo "3. Access the admin panel at: yoursite.com/admin/login.php\n";
    
} catch(PDOException $e) {
    die("Setup failed: " . $e->getMessage() . "\n");
}
?>
