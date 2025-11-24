<?php
require_once 'config.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if already logged in
if (isUserLoggedIn()) {
    header("Location: home.php");
    exit;
}

$error = '';
$success = '';

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        $terms_accepted = isset($_POST['terms_accepted']);

        // Validation
        $errors = [];
        
        if (empty($full_name)) {
            $errors[] = "Full name is required";
        } elseif (strlen($full_name) < 2) {
            $errors[] = "Full name must be at least 2 characters";
        }

        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }

        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain both letters and numbers";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }

        if (!$terms_accepted) {
            $errors[] = "Please accept the terms and conditions";
        }

        if (empty($errors)) {
            try {
                // Check if email already exists using PDO
                $check_user = getUserByEmail($email);

                if ($check_user) {
                    $error = "An account with this email already exists.";
                } else {
                    // Create new user
                    global $pdo;
                    
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    // Use email as username
                    $username = $email;
                    
                    // Insert user - main operation
                    $insert_query = "
                        INSERT INTO users (username, password, email, full_name, role, is_active, reward_points, created_at) 
                        VALUES (:username, :password, :email, :full_name, 'user', 1, 0, NOW())
                    ";
                    
                    $stmt = $pdo->prepare($insert_query);
                    
                    // Execute the insert
                    $result = $stmt->execute([
                        ':username' => $username,
                        ':password' => $hashed_password,
                        ':email' => $email,
                        ':full_name' => $full_name
                    ]);

                    if (!$result) {
                        $error = "Failed to execute user registration query.";
                    } else {
                        // Get the inserted user ID
                        $user_id = $pdo->lastInsertId();
                        
                        if ($user_id && $user_id > 0) {
                            // User created successfully - try optional operations
                            
                            // Try to send welcome notification (optional)
                            try {
                                $welcome_query = "
                                    INSERT INTO notifications (user_id, title, message, type, created_at) 
                                    VALUES (:user_id, 'Welcome to Kindora!', 'Thank you for joining Kindora. Start exploring amazing destinations!', 'system', NOW())
                                ";
                                $welcome_stmt = $pdo->prepare($welcome_query);
                                $welcome_stmt->execute([':user_id' => $user_id]);
                            } catch (Exception $e) {
                                error_log("Could not create welcome notification: " . $e->getMessage());
                            }
                            
                            // Verify user was actually created before redirecting
                            $verify_query = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND email = ?");
                            $verify_query->execute([$user_id, $email]);
                            $verified = $verify_query->fetch();
                            
                            if ($verified) {
                                // User successfully created - redirect to login
                                header("Location: login.php?registered=1");
                                exit;
                            } else {
                                $error = "User account creation verification failed. Please try again.";
                            }
                        } else {
                            $error = "Failed to create user account. No user ID was returned.";
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Registration PDO error: " . $e->getMessage());
                $error_code = $e->getCode();
                $error_message = $e->getMessage();
                
                // Check for duplicate entry error (MySQL error 1062)
                if ($error_code == 23000 || $error_code == 1062 || strpos($error_message, 'Duplicate entry') !== false || strpos($error_message, 'Integrity constraint violation') !== false) {
                    $error = "An account with this email already exists. Please use a different email or try logging in.";
                } else {
                    $error = "Registration failed. Please try again.";
                    error_log("Full error details: " . $error_message);
                }
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $error = "An unexpected error occurred. Please try again.";
            }
        } else {
            $error = implode(", ", $errors);
        }
    }
    
    // Regenerate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - Kindora</title>
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
      }
      .container {
        background-color: #ffffff;
        width: 100%;
        max-width: 420px;
        padding: 30px;
        border-radius: 14px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        text-align: center;
        margin: 60px auto;
        flex: 0 0 auto;
      }
      /* Ensure footer container doesn't use form styles */
      footer .container {
        background-color: transparent !important;
        max-width: 1200px !important;
        padding: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        text-align: left !important;
        margin: 0 auto !important;
        flex: none !important;
      }
      .container h1 {
        margin-bottom: 20px;
        color: #2c3e50;
        font-size: 26px;
      }
      .form-group {
        margin-bottom: 18px;
        text-align: left;
      }
      .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: 500;
        font-size: 14px;
      }
      input[type="text"],
      input[type="email"],
      input[type="password"] {
        width: 100%;
        padding: 14px;
        margin: 0;
        border-radius: 10px;
        border: 1px solid #ccc;
        font-size: 15px;
        outline: none;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        box-sizing: border-box;
      }
      input[type="text"]:focus,
      input[type="email"]:focus,
      input[type="password"]:focus {
        border-color: #007acc;
        box-shadow: 0 0 6px rgba(0, 122, 204, 0.5);
      }
      .password-field-wrapper {
        position: relative;
      }
      .password-field-wrapper input[type="password"] {
        margin: 0;
        padding-right: 45px;
      }
      .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        font-size: 18px;
        padding: 4px 8px;
        z-index: 10;
      }
      .password-requirements {
        margin-top: 8px;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 8px;
        text-align: left;
        font-size: 11px;
        border: 1px solid #e9ecef;
      }
      .password-requirements h4 {
        font-size: 11px;
        color: #2c3e50;
        margin-bottom: 6px;
        font-weight: 600;
      }
      .password-requirements ul {
        list-style: none;
        padding: 0;
        margin: 0;
      }
      .password-requirements li {
        font-size: 11px;
        color: #666;
        margin: 3px 0;
        padding-left: 16px;
        position: relative;
        line-height: 1.3;
      }
      .password-requirements li:before {
        content: "○";
        position: absolute;
        left: 0;
        font-size: 11px;
      }
      .password-requirements li.valid {
        color: #28a745;
      }
      .password-requirements li.valid:before {
        content: "✓";
        font-weight: bold;
      }
      .checkbox-group {
        margin: 14px 0;
        text-align: left;
      }
      .checkbox-container {
        display: flex;
        align-items: flex-start;
        cursor: pointer;
        font-size: 13px;
        color: #2c3e50;
        line-height: 1.4;
      }
      .checkbox-container input[type="checkbox"] {
        width: 16px;
        height: 16px;
        margin-right: 8px;
        margin-top: 2px;
        cursor: pointer;
        accent-color: #007acc;
        flex-shrink: 0;
      }
      .checkbox-container a {
        color: #007acc;
        text-decoration: none;
      }
      .checkbox-container a:hover {
        text-decoration: underline;
      }
      #but {
        width: 100%;
        background-color: #007acc;
        color: white;
        padding: 14px;
        margin-top: 12px;
        border-radius: 10px;
        border: none;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
      }
      #but:hover:not(:disabled) {
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
        margin-top: 18px;
        font-size: 14px;
      }
      .link a {
        color: #007acc;
        text-decoration: none;
      }
      .link a:hover {
        text-decoration: underline;
      }
      .error-message {
        margin-bottom: 15px;
        font-size: 14px;
        font-weight: bold;
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        padding: 10px;
        border-radius: 5px;
        text-align: left;
      }
      .success-message {
        margin-bottom: 15px;
        font-size: 14px;
        font-weight: bold;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 10px;
        border-radius: 5px;
        text-align: left;
      }
      /* Footer styling to match common_nav_footer.css - override all default styles */
      footer.footer {
        background: linear-gradient(135deg, #003366, #001a33) !important;
        color: white !important;
        padding: 60px 20px 30px !important;
        margin-top: 60px !important;
        font-family: Arial, sans-serif !important;
        width: 100% !important;
        position: relative !important;
        z-index: 1 !important;
      }
      footer .container {
        max-width: 1200px !important;
        margin: 0 auto !important;
        padding: 0 !important;
      }
      footer .footer-content {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: space-between !important;
        gap: 40px !important;
        margin-bottom: 20px !important;
      }
      footer .footer-section {
        flex: 1 1 300px !important;
        min-width: 260px !important;
        color: white !important;
      }
      footer .footer-logo {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        margin-bottom: 15px !important;
      }
      footer .footer-logo i {
        font-size: 2rem !important;
        color: #ffcc00 !important;
      }
      footer .footer-logo span {
        font-size: 2rem !important;
        color: #ffcc00 !important;
        font-weight: bold !important;
      }
      footer h3 {
        color: #ffcc00 !important;
        font-size: 1.2rem !important;
        margin-bottom: 15px !important;
        font-weight: bold !important;
      }
      footer h4 {
        margin-bottom: 15px !important;
        color: #ffcc00 !important;
        font-size: 1.2rem !important;
        font-weight: bold !important;
      }
      footer p {
        line-height: 1.6 !important;
        font-size: 1rem !important;
        color: white !important;
        margin: 8px 0 !important;
      }
      footer ul {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
      }
      footer ul li {
        margin-bottom: 10px !important;
      }
      footer ul li a {
        color: white !important;
        text-decoration: none !important;
        font-size: 1rem !important;
        transition: color 0.3s !important;
      }
      footer ul li a:hover {
        color: #ffcc00 !important;
      }
      footer .social-links {
        margin-top: 15px !important;
        display: flex !important;
        gap: 12px !important;
      }
      footer .social-links a {
        color: white !important;
        font-size: 20px !important;
        text-decoration: none !important;
        transition: color 0.3s !important;
      }
      footer .social-links a:hover {
        color: #ffcc00 !important;
      }
      footer .social-links i {
        color: white !important;
        transition: color 0.3s !important;
      }
      footer .social-links a:hover i {
        color: #ffcc00 !important;
      }
      footer .contact-info {
        color: white !important;
      }
      footer .contact-info p {
        color: white !important;
        margin: 8px 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
      }
      footer .contact-info i {
        color: #ffcc00 !important;
      }
      footer .footer-bottom {
        text-align: center !important;
        padding: 15px 0 !important;
        margin-top: 40px !important;
        border-top: 1px solid rgba(255, 255, 255, 0.2) !important;
        font-size: 0.95rem !important;
        color: white !important;
      }
      footer .footer-bottom p {
        margin: 0 !important;
        color: white !important;
      }
    </style>
  </head>
  <body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <br /><br /><br /><br />
    <br /><br /><br /><br />

    <div class="container">
        <h1>📝 Create Account</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input 
                    type="text" 
                    id="full_name"
                    name="full_name" 
                    placeholder="Enter your full name" 
                    value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                    required 
                    autocomplete="name"
                    minlength="2"
                    maxlength="100"
                >
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email"
                    name="email" 
                    placeholder="Enter your email address" 
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required 
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-field-wrapper">
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Create a strong password" 
                        required 
                        autocomplete="new-password"
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                </div>
                <div class="password-requirements">
                    <h4>Password Requirements:</h4>
                    <ul id="password-checklist">
                        <li id="length-check">At least 8 characters</li>
                        <li id="letter-check">Contains letters</li>
                        <li id="number-check">Contains numbers</li>
                        <li id="match-check">Passwords match</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-field-wrapper">
                    <input 
                        type="password" 
                        id="confirm_password"
                        name="confirm_password" 
                        placeholder="Confirm your password" 
                        required 
                        autocomplete="new-password"
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">👁️</button>
                </div>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-container">
                    <input type="checkbox" name="terms_accepted" id="terms_accepted" required>
                    <span>I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></span>
                </label>
            </div>

            <button type="submit" id="but" disabled>Create Account</button>

            <div class="link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>
    
    

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    
    <script src="common_nav_footer.js"></script>
    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleBtn = passwordInput.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            if (password.length >= 8) {
                document.getElementById('length-check').classList.add('valid');
            } else {
                document.getElementById('length-check').classList.remove('valid');
            }
            
            if (/[a-zA-Z]/.test(password)) {
                document.getElementById('letter-check').classList.add('valid');
            } else {
                document.getElementById('letter-check').classList.remove('valid');
            }
            
            if (/[0-9]/.test(password)) {
                document.getElementById('number-check').classList.add('valid');
            } else {
                document.getElementById('number-check').classList.remove('valid');
            }
        }

        // Real-time validation
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validateForm();
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchCheck = document.getElementById('match-check');
            
            if (confirmPassword && password === confirmPassword) {
                matchCheck.classList.add('valid');
            } else {
                matchCheck.classList.remove('valid');
            }
            validateForm();
        });

        document.getElementById('terms_accepted').addEventListener('change', validateForm);
        document.getElementById('full_name').addEventListener('input', validateForm);
        document.getElementById('email').addEventListener('input', validateForm);

        function validateForm() {
            const fullName = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const termsAccepted = document.getElementById('terms_accepted').checked;
            const submitBtn = document.getElementById('but');
            
            const isValid = fullName.length >= 2 && 
                           validateEmail(email) && 
                           password.length >= 8 && 
                           /[A-Za-z]/.test(password) && 
                           /[0-9]/.test(password) && 
                           password === confirmPassword && 
                           termsAccepted;
            
            submitBtn.disabled = !isValid;
            return isValid;
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('but');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating account...';
        });

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.error-message, .success-message');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 6000);
            });
        });
    </script>
  </body>
</html>
