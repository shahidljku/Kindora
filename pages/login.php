<?php
require_once '../config.php';
 


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get return URL from parameter or default to index.php
// At top, after your require_once/config/session:
$return_url = isset($_GET['return']) ? urldecode($_GET['return']) : '/kindora/index.php';




// If already logged in, redirect
if (isUserLoggedIn()) {
    header('Location: ' . $return_url);
    exit();
}

$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate
    if (empty($email) || empty($password)) {
        $error = '❌ Please fill in all fields';
    } else {
        // Verify login using config function
        $user = verifyUserLogin($email, $password);
        
        if ($user) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'] ?? $user['name'] ?? 'User';
            // If user is admin, redirect to admin dashboard
    if (isset($user['role']) && strtolower($user['role']) === 'admin') {
        header('Location: /kindora/adminkindora.php'); // <-- Replace this path with your admin page
        exit();
    }
            // Redirect to return URL
           // After login success:
header('Location: ' . $return_url); 
exit();
        } else {
            $error = '❌ Invalid email or password';
        }
    }
}
?>
   <?php
require_once '../includes/header.php';
?><br><br><br>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Kindora</title>
    <link rel="icon" type="image/png" href="kindora-logo.ico" />
    <link href="common_nav_footer.css" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            width: 100%;
            min-height: 100vh;
            font-family: "Open Sans", sans-serif;
            background: linear-gradient(135deg, #1e2a38, #005fa3);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .container {
            background-color: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            text-align: center;
            margin: 20px;
        }
        .container h1 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 26px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 15px;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        input:focus {
            border-color: #007acc;
            box-shadow: 0 0 6px rgba(0, 122, 204, 0.5);
        }
        #but {
            width: 100%;
            background-color: #007acc;
            color: white;
            padding: 12px;
            margin-top: 15px;
            border-radius: 6px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        #but:hover {
            background-color: #005fa3;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        }
        #but:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .link {
            margin-top: 15px;
            font-size: 14px;
        }
        .link a {
            color: #007acc;
            text-decoration: none;
            font-weight: 600;
        }
        .link a:hover {
            text-decoration: underline;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
 
</head>
<body>

    <div class="container">
        <h1>🔐 Login</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?return=' . urlencode($return_url)); ?>">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email"
                    name="email" 
                    placeholder="Enter your email" 
                    value="<?php echo htmlspecialchars($email); ?>"
                    required 
                />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password"
                    name="password" 
                    placeholder="Enter your password" 
                    required 
                />
            </div>

            <button type="submit" id="but">Login</button>

            <div class="link">
                Don't have an account? <a href="register.html">Register here</a>
            </div>
        </form>
    </div>

</body>
</html>
<?php
    require_once '../includes/footer.php';
?>