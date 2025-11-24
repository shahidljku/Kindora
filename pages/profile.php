<?php
/**
 * User Profile - Edit User Details
 * Allows users to change their information
 */

require_once 'config.php';
require_once 'paths.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = 'info';
$userData = null;

// Load current user data
try {
    $result = KindoraDatabase::query(
        "SELECT * FROM users WHERE user_id = ?",
        [$userId]
    );
    $userData = $result[0] ?? null;

    if (!$userData) {
        header('Location: logout.php');
        exit;
    }
} catch (Exception $e) {
    $message = 'Error loading profile.';
    $messageType = 'error';
}

// Handle form submission for updating profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        // Update basic info
        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $dateOfBirth = sanitize($_POST['date_of_birth'] ?? '');
        $nationality = sanitize($_POST['nationality'] ?? '');

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email address.';
            $messageType = 'error';
        } else if (empty($fullName)) {
            $message = 'Full name is required.';
            $messageType = 'error';
        } else {
            try {
                // Check if email is already taken by another user
                $emailCheck = KindoraDatabase::query(
                    "SELECT user_id FROM users WHERE email = ? AND user_id != ?",
                    [$email, $userId]
                );

                if (!empty($emailCheck)) {
                    $message = 'Email is already in use.';
                    $messageType = 'error';
                } else {
                    // Update user
                    KindoraDatabase::update(
                        'users',
                        [
                            'full_name' => $fullName,
                            'email' => $email,
                            'phone' => $phone,
                            'date_of_birth' => $dateOfBirth ?: null,
                            'nationality' => $nationality
                        ],
                        'user_id = :user_id',
                        ['user_id' => $userId]
                    );

                    // Reload user data
                    $result = KindoraDatabase::query(
                        "SELECT * FROM users WHERE user_id = ?",
                        [$userId]
                    );
                    $userData = $result[0];

                    $message = 'Profile updated successfully!';
                    $messageType = 'success';
                }
            } catch (Exception $e) {
                $message = 'Error updating profile: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'change_password') {
        // Change password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = 'All password fields are required.';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New passwords do not match.';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 6) {
            $message = 'Password must be at least 6 characters.';
            $messageType = 'error';
        } else {
            try {
                // Verify current password
                if (!password_verify($currentPassword, $userData['password'])) {
                    $message = 'Current password is incorrect.';
                    $messageType = 'error';
                } else {
                    // Update password
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    KindoraDatabase::update(
                        'users',
                        ['password' => $hashedPassword],
                        'user_id = :user_id',
                        ['user_id' => $userId]
                    );

                    $message = 'Password changed successfully!';
                    $messageType = 'success';
                }
            } catch (Exception $e) {
                $message = 'Error changing password.';
                $messageType = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Kindora</title>
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 30px;
            transition: opacity 0.3s;
        }

        nav a:hover {
            opacity: 0.8;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-header h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .profile-header p {
            color: #666;
            font-size: 16px;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            background-color: #667eea;
            color: white;
            padding: 20px;
            font-size: 18px;
            font-weight: bold;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background-color: #5568d3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            margin-right: 10px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .info-section {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-weight: 500;
        }

        .info-value {
            color: #333;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            nav {
                flex-direction: column;
                gap: 10px;
            }

            nav a {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Kindora</div>
            <div style="display: flex; gap: 20px;">
                <a href="../index.php">Home</a>
                <a href="mytrips.html">My Trips</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="profile-header">
            <h1>My Profile</h1>
            <p>Manage your account settings and personal information</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Account Overview -->
        <div class="profile-card">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <div class="info-section">
                    <div class="info-row">
                        <span class="info-label">Username:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userData['username']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">User ID:</span>
                        <span class="info-value"><?php echo $userData['user_id']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Member Since:</span>
                        <span class="info-value"><?php echo date('F j, Y', strtotime($userData['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value"><?php echo $userData['is_active'] ? 'Active' : 'Inactive'; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Role:</span>
                        <span class="info-value"><?php echo ucfirst($userData['role']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Personal Information -->
        <div class="profile-card">
            <div class="card-header">Edit Personal Information</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($userData['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($userData['date_of_birth'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nationality">Nationality</label>
                        <input type="text" id="nationality" name="nationality" value="<?php echo htmlspecialchars($userData['nationality'] ?? ''); ?>" placeholder="e.g., Indian, American">
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="profile-card">
            <div class="card-header">Change Password</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" id="new_password" name="new_password" placeholder="At least 6 characters" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your new password" required>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="profile-card" style="border-top: 3px solid #dc3545;">
            <div class="card-header" style="background-color: #dc3545;">Danger Zone</div>
            <div class="card-body">
                <p style="color: #666; margin-bottom: 15px;">
                    Be careful with these actions. They cannot be easily undone.
                </p>
                <a href="logout.php" class="btn" style="background-color: #dc3545; color: white; text-decoration: none; display: inline-block;">
                    Logout
                </a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Kindora. All rights reserved.</p>
    </footer>
</body>
</html>
