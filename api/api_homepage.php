<?php
/**
 * API for loading dynamic content - FIXED VERSION
 * Location: api/homepage.php
 */
session_start();
require_once '../config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'data' => null, 'message' => 'Unknown action'];

try {
    switch($action) {
        // Search destinations
        case 'search':
            $query = $_GET['q'] ?? '';
            $category = $_GET['category'] ?? '';
            
            $sql = "SELECT * FROM destinations WHERE is_active = 1";
            $params = [];
            
            if (!empty($query)) {
                $sql .= " AND (name LIKE ? OR description LIKE ?)";
                $params[] = "%$query%";
                $params[] = "%$query%";
            }
            
            if (!empty($category)) {
                $sql .= " AND type = ?";
                $params[] = $category;
            }
            
            $sql .= " LIMIT 10";
            $result = KindoraDatabase::query($sql, $params);
            $response = ['success' => true, 'data' => $result ?: []];
            break;
        
        // Get popular places
        case 'popular_places':
            $result = KindoraDatabase::query(
                "SELECT d.* FROM destinations d 
                 WHERE d.is_active = 1 
                 ORDER BY d.destination_id ASC 
                 LIMIT 10"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;
        
        // Get packages
        case 'packages':
            $result = KindoraDatabase::query(
                "SELECT * FROM travel_packages 
                 WHERE is_active = 1 
                 ORDER BY package_id ASC 
                 LIMIT 10"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;
        
        // Get deals
        case 'deals':
            $result = KindoraDatabase::query(
                "SELECT * FROM deals 
                 WHERE is_active = 1 
                 AND (end_date IS NULL OR end_date >= DATE(NOW())) 
                 ORDER BY discount DESC 
                 LIMIT 10"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;
        
        // Get testimonials
        case 'testimonials':
            $result = KindoraDatabase::query(
                "SELECT * FROM testimonials 
                 WHERE is_active = 1 
                 ORDER BY testimonial_id DESC 
                 LIMIT 5"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;
        
        // Get FAQ
        case 'faq':
            $result = KindoraDatabase::query(
                "SELECT * FROM faq 
                 WHERE is_active = 1 
                 ORDER BY sort_order ASC"
            );
            $response = ['success' => true, 'data' => $result ?: []];
            break;
        
        // Submit review (ONLY FOR LOGGED-IN USERS)
        case 'submit_review':
            if (!isset($_SESSION['user_id'])) {
                $response = ['success' => false, 'message' => 'Please login to submit a review'];
                break;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $destination_id = $_POST['destination_id'] ?? 0;
                $rating = (int)($_POST['rating'] ?? 0);
                $text = trim($_POST['text'] ?? '');
                
                if (empty($destination_id) || $rating < 1 || $rating > 5 || strlen($text) < 10) {
                    $response = ['success' => false, 'message' => 'Invalid data. Rating must be 1-5, text min 10 chars'];
                    break;
                }
                
                // Check if user has visited this destination (has booking)
                $booking = KindoraDatabase::query(
                    "SELECT * FROM bookings 
                     WHERE user_id = ? AND destination_id = ? AND status = 'completed' 
                     LIMIT 1",
                    [$_SESSION['user_id'], $destination_id]
                );
                
                if (empty($booking)) {
                    $response = ['success' => false, 'message' => 'You can only review destinations you have visited'];
                    break;
                }
                
                KindoraDatabase::query(
                    "INSERT INTO reviews (user_id, destination_id, rating, review_text, status, created_at) 
                     VALUES (?, ?, ?, ?, 'pending', NOW())",
                    [$_SESSION['user_id'], $destination_id, $rating, $text]
                );
                
                $response = ['success' => true, 'message' => 'Review submitted for approval'];
            }
            break;
        
        // Subscribe to newsletter
        case 'subscribe':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = trim($_POST['email'] ?? '');
                
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $response = ['success' => false, 'message' => 'Invalid email'];
                    break;
                }
                
                // Check if already subscribed
                $existing = KindoraDatabase::query(
                    "SELECT * FROM newsletter_subscribers WHERE email = ?",
                    [$email]
                );
                
                if (!empty($existing)) {
                    $response = ['success' => false, 'message' => 'Already subscribed'];
                    break;
                }
                
                KindoraDatabase::query(
                    "INSERT INTO newsletter_subscribers (email, subscribed_at) 
                     VALUES (?, NOW())",
                    [$email]
                );
                
                $response = ['success' => true, 'message' => 'Successfully subscribed'];
            }
            break;
        
        // Get stats
        case 'stats':
            $travelers = KindoraDatabase::query("SELECT COUNT(DISTINCT user_id) as cnt FROM bookings WHERE status = 'completed'");
            $tours = KindoraDatabase::query("SELECT COUNT(*) as cnt FROM bookings WHERE status = 'completed'");
            $reviews_count = KindoraDatabase::query("SELECT COUNT(*) as cnt FROM reviews WHERE status = 'approved'");
            $destinations = KindoraDatabase::query("SELECT COUNT(*) as cnt FROM destinations WHERE is_active = 1");
            
            $response = ['success' => true, 'data' => [
                'travelers' => $travelers[0]['cnt'] ?? 0,
                'tours' => $tours[0]['cnt'] ?? 0,
                'reviews' => $reviews_count[0]['cnt'] ?? 0,
                'destinations' => $destinations[0]['cnt'] ?? 0
            ]];
            break;
        
        default:
            $response = ['success' => false, 'message' => 'Invalid action: ' . $action];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    error_log("API ERROR [{$action}]: " . $e->getMessage());
}

echo json_encode($response);
?>
