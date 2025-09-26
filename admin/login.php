<?php
session_start();

// Database configuration
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

$error = '';

if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportex Admin - Login</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #b22222, #8b0000);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Open Sans', sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: #b22222;
            color: white;
            text-align: center;
            padding: 30px;
        }
        
        .login-header h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        
        .form-control:focus {
            border-color: #b22222;
            box-shadow: 0 0 0 0.2rem rgba(178, 34, 34, 0.25);
        }
        
        .btn-login {
            background: #b22222;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            padding: 12px;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #8b0000;
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .sportex-logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="sportex-logo">SPORTEX</div>
            <h3>Admin Dashboard</h3>
            <p class="mb-0">Please sign in to continue</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="bi bi-shield-lock"></i> 
                    Secure access to Sportex management system
                </small>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
