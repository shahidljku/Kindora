<?php
session_start();
require_once 'connection.php';

// Log logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO user_activities (user_id, activity_type, ip_address, user_agent, created_at) 
            VALUES (?, 'logout', ?, ?, NOW())
        ");
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $stmt->bind_param("iss", $_SESSION['user_id'], $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
        
        // Remove remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            $token_stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $token_stmt->bind_param("s", $_COOKIE['remember_token']);
            $token_stmt->execute();
            $token_stmt->close();
            
            // Clear remember cookie
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}

// Clear all session data
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to home page with logout message
header("Location: index.php?logged_out=1");
exit;
?>
