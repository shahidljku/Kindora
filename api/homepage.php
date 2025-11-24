<?php
/**
 * FIXED API for Kindora Homepage
 * Location: api/homepage.php
 * Has error logging and proper database connection handling
 */

session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Load config
if (!file_exists('../config.php')) {
    die(json_encode(['success' => false, 'message' => 'Config file not found', 'debug' => __DIR__]));
}

require_once '../config.php';

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'data' => null, 'message' => 'Unknown action'];

try {
    // Test database connection
    if (!isset($GLOBALS['KindoraDatabase'])) {
        throw new Exception('Database class not loaded');
    }

    switch($action) {
        // SEARCH
        case 'search':
            $query = trim($_GET['q'] ?? '');
            $category = trim($_GET['category'] ?? '');
            
            if (empty($query)) {
                $response = ['success' => false, 'data' => [], 'message' => 'Empty search query'];
                break;
            }
            
            $sql = "SELECT * FROM destinations WHERE is_active = 1";
            $params = [];
            
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%{$query}%";
            $params[] = "%{$query}%";
            
            if (!empty($category)) {
                $sql .= " AND type = ?";
                $params[] = $category;
            }
            
            $sql .= " LIMIT 10";
            
            $result = KindoraDatabase::query($sql, $params);
            $response = ['success' => true, 'data' => $result ?: [], 'message' => 'Search results'];
            break;

        // POPULAR PLACES
        case 'popular_places':
            $result = KindoraDatabase::query(
                "SELECT * FROM destinations 
                 WHERE is_active = 1 
                 ORDER BY destination_id ASC 
                 LIMIT 10"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;

        // PACKAGES
        case 'packages':
            $result = KindoraDatabase::query(
                "SELECT * FROM travel_packages 
                 WHERE is_active = 1 
                 ORDER BY package_id ASC 
                 LIMIT 10"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;

        // DEALS
        case 'deals':
            $result = KindoraDatabase::query(
                "SELECT * FROM deals 
                 WHERE is_active = 1 
                 AND (end_date IS NULL OR DATE(end_date) >= CURDATE())
                 ORDER BY discount DESC 
                 LIMIT 10"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;

        // TESTIMONIALS
        case 'testimonials':
            $result = KindoraDatabase::query(
                "SELECT * FROM testimonials 
                 WHERE is_active = 1 
                 ORDER BY testimonial_id DESC 
                 LIMIT 5"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;

        // FAQ
        case 'faq':
            $result = KindoraDatabase::query(
                "SELECT * FROM faq 
                 WHERE is_active = 1 
                 ORDER BY sort_order ASC"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;

        // STATS/COUNTERS
        case 'stats':
            $travelers = KindoraDatabase::query(
                "SELECT COUNT(DISTINCT user_id) as cnt FROM bookings WHERE status = 'completed'"
            );
            $tours = KindoraDatabase::query(
                "SELECT COUNT(*) as cnt FROM bookings WHERE status = 'completed'"
            );
            $reviews_count = KindoraDatabase::query(
                "SELECT COUNT(*) as cnt FROM reviews WHERE status = 'approved'"
            );
            $destinations = KindoraDatabase::query(
                "SELECT COUNT(*) as cnt FROM destinations WHERE is_active = 1"
            );
            
            $response = ['success' => true, 'data' => [
                'travelers' => isset($travelers[0]['cnt']) ? (int)$travelers[0]['cnt'] : 0,
                'tours' => isset($tours[0]['cnt']) ? (int)$tours[0]['cnt'] : 0,
                'reviews' => isset($reviews_count[0]['cnt']) ? (int)$reviews_count[0]['cnt'] : 0,
                'destinations' => isset($destinations[0]['cnt']) ? (int)$destinations[0]['cnt'] : 0
            ]];
            break;

        // NEWSLETTER SUBSCRIPTION
        case 'subscribe':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response = ['success' => false, 'message' => 'POST required'];
                break;
            }
            
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response = ['success' => false, 'message' => 'Invalid email address'];
                break;
            }
            
            // Check if already subscribed
            $existing = KindoraDatabase::query(
                "SELECT * FROM newsletter_subscribers WHERE email = ? LIMIT 1",
                [$email]
            );
            
            if (!empty($existing)) {
                $response = ['success' => false, 'message' => 'Email already subscribed'];
                break;
            }
            
            // Subscribe
            KindoraDatabase::query(
                "INSERT INTO newsletter_subscribers (email, subscribed_at, is_active) 
                 VALUES (?, NOW(), 1)",
                [$email]
            );
            
            $response = ['success' => true, 'message' => 'Successfully subscribed!'];
            break;

        // SUBMIT REVIEW
        case 'submit_review':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response = ['success' => false, 'message' => 'POST required'];
                break;
            }
            
            // Check login
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                $response = ['success' => false, 'message' => 'Please login to submit a review'];
                break;
            }
            
            $user_id = $_SESSION['user_id'];
            $destination_id = (int)($_POST['destination_id'] ?? 0);
            $rating = (int)($_POST['rating'] ?? 0);
            $text = trim($_POST['text'] ?? '');
            
            // Validate
            if ($destination_id < 1 || $rating < 1 || $rating > 5 || strlen($text) < 10) {
                $response = ['success' => false, 'message' => 'Invalid review data. Text must be at least 10 characters.'];
                break;
            }
            
            // Check if user has completed booking for this destination
            $booking = KindoraDatabase::query(
                "SELECT booking_id FROM bookings 
                 WHERE user_id = ? AND destination_id = ? AND status = 'completed' 
                 LIMIT 1",
                [$user_id, $destination_id]
            );
            
            if (empty($booking)) {
                $response = ['success' => false, 'message' => 'You can only review destinations you have visited'];
                break;
            }
            
            // Submit review
            KindoraDatabase::query(
                "INSERT INTO reviews (user_id, destination_id, rating, review_text, status, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())",
                [$user_id, $destination_id, $rating, $text]
            );
            
            $response = ['success' => true, 'message' => 'Review submitted for approval!'];
            break;

        default:
            $response = ['success' => false, 'message' => 'Invalid action: ' . $action];
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    error_log("API ERROR [{$action}]: " . $e->getMessage());
}

// Send JSON response
echo json_encode($response);
exit;
?>
