<?php
/**
 * Logout Script
 * Properly destroys session and clears all login data
 */

  

require_once 'config.php';
require_once 'paths.php';
$return_url = isset($_GET['return']) ? urldecode($_GET['return']) : '/kindora/index.php';
// Ensure we're working with the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Step 1: Unset all session variables
$_SESSION = array();

// Step 2: Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Step 3: Destroy the session
session_destroy();

// Step 4: Clear any additional auth cookies/data
setcookie('PHPSESSID', '', time() - 3600, '/');

// Step 5: Log the logout
error_log("[KINDORA-LOGOUT] User logged out at " . date('Y-m-d H:i:s'));

// Step 6: Redirect to login page
  header('Location: ' . $return_url);
exit;
?>
