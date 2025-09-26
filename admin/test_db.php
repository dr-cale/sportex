<?php
// Simple database connection test
// Use this to verify your database credentials are working

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";
echo "<pre>";

$host = 'localhost';
$dbname = 'sport555_sportex';
$username = 'sport555_admin';
$password = 'WhoisthemaN123!';

echo "Testing database connection...\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "Username: $username\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";

try {
    // Test basic connection
    echo "Step 1: Attempting to connect to MySQL server...\n";
    $pdo_test = new PDO("mysql:host=$host", $username, $password);
    $pdo_test->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connection to MySQL server successful!\n\n";
    
    // Test database existence
    echo "Step 2: Checking if database exists...\n";
    $stmt = $pdo_test->prepare("SHOW DATABASES LIKE ?");
    $stmt->execute([$dbname]);
    $db_exists = $stmt->fetch();
    
    if ($db_exists) {
        echo "✓ Database '$dbname' exists!\n\n";
        
        // Test full connection with database
        echo "Step 3: Connecting to specific database...\n";
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✓ Full database connection successful!\n\n";
        
        // Test user privileges
        echo "Step 4: Checking user privileges...\n";
        $stmt = $pdo->query("SHOW GRANTS");
        $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "User privileges:\n";
        foreach ($grants as $grant) {
            echo "- $grant\n";
        }
        echo "\n";
        
        // Test table creation privileges
        echo "Step 5: Testing table creation (will be dropped immediately)...\n";
        try {
            $pdo->exec("CREATE TABLE test_table_temp (id INT)");
            echo "✓ Can create tables!\n";
            $pdo->exec("DROP TABLE test_table_temp");
            echo "✓ Can drop tables!\n\n";
        } catch (Exception $e) {
            echo "❌ Cannot create tables: " . $e->getMessage() . "\n\n";
        }
        
        echo "===================\n";
        echo "DATABASE TEST PASSED!\n";
        echo "===================\n";
        echo "Your database connection is working correctly.\n";
        echo "You can now run setup.php to create the admin system.\n\n";
        
    } else {
        echo "❌ Database '$dbname' does not exist!\n\n";
        echo "SOLUTION:\n";
        echo "Create the database in cPanel:\n";
        echo "1. Go to cPanel > MySQL Databases\n";
        echo "2. Create database: $dbname\n";
        echo "3. Make sure user '$username' has access to it\n\n";
    }
    
} catch(PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "COMMON SOLUTIONS:\n";
    echo "==================\n";
    echo "1. Check if database name is correct: $dbname\n";
    echo "2. Check if username is correct: $username\n";
    echo "3. Check if password is correct\n";
    echo "4. Verify user has privileges on the database\n";
    echo "5. Check if MySQL service is running\n";
    echo "6. Contact your hosting provider\n\n";
    
    // Try to connect without database to test basic MySQL access
    try {
        $pdo_basic = new PDO("mysql:host=$host", $username, $password);
        echo "✓ Basic MySQL connection works - issue is with database access\n";
        echo "→ Create database '$dbname' in cPanel\n";
        echo "→ Grant user '$username' privileges on '$dbname'\n";
    } catch(PDOException $e2) {
        echo "❌ Basic MySQL connection also fails\n";
        echo "→ Check username/password in cPanel\n";
        echo "→ Ensure user exists and is active\n";
    }
}

echo "</pre>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f5f5f5; padding: 20px; border-radius: 5px; }
h1 { color: #b22222; }
</style>
