<?php
/**
 * KINDORA - MAIN CONFIGURATION FILE
 * Location: E:\xampp\htdocs\Kindora\config.php
 * 
 * This file handles:
 * - Database connection (PDO)
 * - Path management (via paths.php)
 * - Global functions and helpers
 * - Database query functions
 * - Error handling
 */

// ============================================================
// 1. DATABASE CONFIGURATION
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kindora');

// ============================================================
// 2. DATABASE CONNECTION (PDO)
// ============================================================

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );
} catch (PDOException $e) {
    die('Database Connection Error: ' . $e->getMessage());
}

// ============================================================
// 3. LOAD PATHS
// ============================================================

require_once __DIR__ . '/paths.php';

// ============================================================
// 4. TIMEZONE SETTING
// ============================================================

date_default_timezone_set('Asia/Kolkata');

// ============================================================
// 5. SITE CONFIGURATION
// ============================================================

define('SITE_TITLE', 'Kindora - Travel Planning Platform');
define('SITE_URL', 'http://localhost/Kindora/');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// ============================================================
// 6. GLOBAL HELPER FUNCTIONS FOR DATABASE
// ============================================================

/**
 * Execute SELECT query and return results
 * @param string $query SQL query
 * @param array $params Parameters for prepared statement
 * @return array Results
 */
function KindoraDatabase_select($query, $params = array()) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return array();
    }
}

/**
 * Execute INSERT/UPDATE/DELETE query
 * @param string $query SQL query
 * @param array $params Parameters for prepared statement
 * @return bool Success status
 */
function KindoraDatabase_execute($query, $params = array()) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get single row
 * @param string $query SQL query
 * @param array $params Parameters
 * @return array Single row or null
 */
function KindoraDatabase_fetchOne($query, $params = array()) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get last inserted ID
 * @return int Last insert ID
 */
function KindoraDatabase_lastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}

/**
 * Get row count from query
 * @param string $query SQL query
 * @param array $params Parameters
 * @return int Row count
 */
function KindoraDatabase_count($query, $params = array()) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Static wrapper class for database operations
 */
class KindoraDatabase {
    
    /**
     * Execute query and return results
     */
    public static function query($sql, $params = array()) {
        return KindoraDatabase_select($sql, $params);
    }
    
    /**
     * Execute INSERT/UPDATE/DELETE
     */
    public static function execute($sql, $params = array()) {
        return KindoraDatabase_execute($sql, $params);
    }
    
    /**
     * Fetch single row
     */
    public static function fetchOne($sql, $params = array()) {
        return KindoraDatabase_fetchOne($sql, $params);
    }
    
    /**
     * Get last insert ID
     */
    public static function lastId() {
        return KindoraDatabase_lastInsertId();
    }
    
    /**
     * Get count from query
     */
    public static function count($sql, $params = array()) {
        return KindoraDatabase_count($sql, $params);
    }
}

// ============================================================
// 7. GLOBAL HELPER FUNCTIONS FOR UTILITY
// ============================================================

/**
 * Sanitize input string
 * @param string $str Input string
 * @return string Sanitized string
 */
function sanitize($str) {
    return trim(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
}

/**
 * Check if user is logged in
 * @return bool Is logged in
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int User ID or 0
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
}

/**
 * Redirect to another page
 * @param string $page Page path
 */
function redirect($page) {
    header('Location: ' . $page);
    exit();
}

/**
 * Log error message
 * @param string $message Error message
 */
function logError($message) {
    error_log('[Kindora Error] ' . date('Y-m-d H:i:s') . ' - ' . $message);
}

/**
 * Format price for display
 * @param float $price Price amount
 * @return string Formatted price
 */
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

/**
 * Format date for display
 * @param string $date Date string
 * @return string Formatted date
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

// ============================================================
// 8. START SESSION (if not already started)
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// 9. ERROR HANDLING AND REPORTING
// ============================================================

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// ============================================================
// 10. EXAMPLE: GET DESTINATIONS DATA
// ============================================================

/**
 * Get all destinations
 * @return array All destinations
 */
function getAllDestinations() {
    $query = "SELECT destination_id, name, image_url, description FROM destinations ORDER BY destination_id";
    return KindoraDatabase::query($query);
}

/**
 * Get destination by ID
 * @param int $id Destination ID
 * @return array Destination data or null
 */
function getDestinationById($id) {
    $query = "SELECT * FROM destinations WHERE destination_id = :id LIMIT 1";
    return KindoraDatabase::fetchOne($query, array(':id' => $id));
}

/**
 * Get destinations by continent
 * @param string $continent Continent name
 * @return array Destinations from continent
 */
function getDestinationsByContinent($continent) {
    $query = "SELECT destination_id, name, image_url FROM destinations WHERE continent = :continent ORDER BY name";
    return KindoraDatabase::query($query, array(':continent' => $continent));
}

// ============================================================
// 11. EXAMPLE: USER FUNCTIONS
// ============================================================

/**
 * Get user by ID
 * @param int $id User ID
 * @return array User data or null
 */
function getUserById($id) {
    $query = "SELECT * FROM users WHERE user_id = :id LIMIT 1";
    return KindoraDatabase::fetchOne($query, array(':id' => $id));
}

/**
 * Get user by email
 * @param string $email User email
 * @return array User data or null
 */
function getUserByEmail($email) {
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    return KindoraDatabase::fetchOne($query, array(':email' => $email));
}

/**
 * Create new user
 * @param string $name User name
 * @param string $email User email
 * @param string $password Password (will be hashed)
 * @return bool Success status
 */
function createUser($name, $email, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())";
    return KindoraDatabase::execute($query, array(
        ':name' => $name,
        ':email' => $email,
        ':password' => $hashed_password
    ));
}

/**
 * Verify user login
 * @param string $email User email
 * @param string $password Password
 * @return array User data if valid, false otherwise
 */
function verifyUserLogin($email, $password) {
    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// ============================================================
// 12. SECURITY FUNCTIONS
// ============================================================

/**
 * Escape string for SQL
 * @param string $str String to escape
 * @return string Escaped string
 */
function escapeString($str) {
    global $pdo;
    return $pdo->quote($str);
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool Is valid
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// ============================================================
// 13. INITIALIZATION COMPLETE
// ============================================================

// Config loaded successfully - ready to use!

?>
