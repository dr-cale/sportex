<?php
// Add new admin user script
// Use this to create additional admin accounts

$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_db_username';
$password = 'your_db_password';

// Configuration - Change these values
$new_admin_email = 'your_email@domain.com';
$new_admin_username = 'your_username';
$new_admin_password = 'your_secure_password'; // Use a strong password!

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE email = ?");
    $stmt->execute([$new_admin_email]);
    
    if ($stmt->fetchColumn() > 0) {
        die("Error: An admin user with this email already exists.\n");
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
    $stmt->execute([$new_admin_username]);
    
    if ($stmt->fetchColumn() > 0) {
        die("Error: An admin user with this username already exists.\n");
    }
    
    // Hash the password
    $hashed_password = password_hash($new_admin_password, PASSWORD_DEFAULT);
    
    // Insert new admin user
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$new_admin_username, $new_admin_email, $hashed_password]);
    
    echo "New admin user created successfully!\n";
    echo "Username: $new_admin_username\n";
    echo "Email: $new_admin_email\n";
    echo "You can now login with these credentials.\n";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
