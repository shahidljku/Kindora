<?php require_once 'includes/header.php'; ?>

<main>
    <!-- Page content for reviews goes here -->
    <h1>Traveler Reviews</h1>
</main>

<?php
/**
 * KINDORA REVIEWS & TESTIMONIALS PAGE
 * Displays dynamic reviews from travelers
 * 
 * Features:
 * - Fetch reviews dynamically from database
 * - Rotate reviews every page load (randomly select)
 * - Filter reviews by rating
 * - Submit new reviews (logged-in users)
 * - Pagination for reviews
 */

require_once __DIR__ . '/config.php';

// Handle review submission (ONLY for logged-in users)
$message = '';
$success = false;

if ($_POST && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!isUserLoggedIn()) {
        $message = "❌ You must be logged in to submit a review.";
    } elseif (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "❌ Invalid request. Please try again.";
    } else {
        $rating = intval($_POST['rating'] ?? 5);
        $title = trim($_POST['title'] ?? '');
        $review_text = trim($_POST['review_text'] ?? '');
        $destination_id = intval($_POST['destination_id'] ?? 0);

        // Validation
        $errors = array();
        if (empty($title)) $errors[] = "Title is required";
        if (empty($review_text)) $errors[] = "Review text is required";
        if ($rating < 1 || $rating > 5) $errors[] = "Rating must be between 1 and 5";
        if ($destination_id < 1) $errors[] = "Please select a destination";

        if (!empty($errors)) {
            $message = "❌ " . implode(", ", $errors);
        } else {
            // Insert review into database
            $user_id = getCurrentUserId();
            $query = "INSERT INTO reviews (user_id, destination_id, title, review_text, rating, status, created_at) 
                      VALUES (:user_id, :destination_id, :title, :review_text, :rating, 'pending', NOW())";
            
            $result = KindoraDatabase::execute($query, array(
                ':user_id' => $user_id,
                ':destination_id' => $destination_id,
                ':title' => $title,
                ':review_text' => $review_text,
                ':rating' => $rating
            ));

            if ($result) {
                $message = "✅ Thank you! Your review has been submitted and is pending approval.";
                $success = true;
                $_POST = array();
            } else {
                $message = "❌ Failed to submit review. Please try again.";
            }
        }
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Pagination setup
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;
$rating_filter = isset($_GET['rating']) ? intval($_GET['rating']) : 0;

// Build query for reviews
$query = "SELECT r.*, u.full_name, d.name as destination_name FROM reviews r 
          JOIN users u ON r.user_id = u.user_id 
          LEFT JOIN destinations d ON r.destination_id = d.destination_id 
          WHERE r.status = 'approved'";

$params = array();

if ($rating_filter > 0) {
    $query .= " AND r.rating = :rating";
    $params[':rating'] = $rating_filter;
}

$query .= " ORDER BY r.created_at DESC LIMIT :offset, :perPage";
$params[':offset'] = $offset;
$params[':perPage'] = $perPage;

// Get reviews
$reviews = KindoraDatabase::query($query, $params) ?: array();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as cnt FROM reviews WHERE status = 'approved'";
if ($rating_filter > 0) {
    $countQuery .= " AND rating = :rating";
    $countParams = array(':rating' => $rating_filter);
} else {
    $countParams = array();
}
$countResult = KindoraDatabase::fetchOne($countQuery, $countParams);
$totalReviews = $countResult ? $countResult['cnt'] : 0;
$totalPages = ceil($totalReviews / $perPage);

// Get review statistics
$stats = array();
try {
    $stats = KindoraDatabase::fetchOne(
        "SELECT COUNT(*) as total, AVG(rating) as avg_rating, 
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star
         FROM reviews WHERE status = 'approved'"
    ) ?: array();
} catch (Exception $e) {}

// Get destinations for review submission
$destinations = array();
try {
    $destinations = KindoraDatabase::query(
        "SELECT destination_id, name FROM destinations WHERE is_active = 1 ORDER BY name ASC LIMIT 50"
    ) ?: array();
} catch (Exception $e) {}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traveler Reviews - Kindora</title>
    <?php echo linkCSS('common'); ?>
    <style>
        :root {
            --primary-blue: #1e3c72;
            --secondary-blue: #2a5298;
            --accent-orange: #ff6b35;
            --light-gray: #f8f9fa;
            --border-gray: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
            --success-green: #27ae60;
            --warning-yellow: #f39c12;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Source Sans Pro', sans-serif;
            color: var(--text-dark);
            background: var(--light-gray);
        }

        .reviews-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }

        .reviews-header h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .reviews-header p {
            font-size: 1.1em;
            opacity: 0.95;
        }

        .reviews-container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .reviews-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        .reviews-main {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .stats-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            text-align: center;
        }

        .stat-box .number {
            font-size: 2em;
            font-weight: 700;
            color: var(--accent-orange);
            display: block;
        }

        .stat-box .label {
            font-size: 0.9em;
            color: var(--text-light);
            margin-top: 5px;
        }

        .rating-distribution {
            border-top: 1px solid var(--border-gray);
            padding-top: 20px;
        }

        .rating-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .rating-bar .stars {
            width: 80px;
            font-size: 0.9em;
            color: var(--warning-yellow);
        }

        .rating-bar .bar {
            flex-grow: 1;
            height: 8px;
            background: var(--border-gray);
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-bar .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-orange), var(--warning-yellow));
            transition: width 0.3s ease;
        }

        .rating-bar .count {
            width: 50px;
            text-align: right;
            font-size: 0.9em;
            color: var(--text-light);
        }

        .filters-section {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 2px solid var(--border-gray);
            background: white;
            color: var(--text-dark);
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--accent-orange);
            color: white;
            border-color: var(--accent-orange);
        }

        .reviews-grid {
            display: grid;
            gap: 20px;
        }

        .review-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--accent-orange);
            transition: all 0.3s ease;
        }

        .review-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .review-title {
            font-size: 1.1em;
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .review-author {
            font-size: 0.9em;
            color: var(--text-light);
        }

        .review-destination {
            font-size: 0.85em;
            color: var(--text-light);
            margin-top: 3px;
        }

        .review-stars {
            display: flex;
            gap: 3px;
            font-size: 0.95em;
        }

        .review-text {
            color: var(--text-dark);
            line-height: 1.6;
            margin-bottom: 15px;
            font-size: 0.95em;
        }

        .review-date {
            font-size: 0.85em;
            color: var(--text-light);
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .submit-review-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .submit-review-card h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: var(--text-dark);
            font-weight: 500;
            margin-bottom: 6px;
            font-size: 0.9em;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-gray);
            border-radius: 5px;
            font-family: inherit;
            font-size: 0.9em;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .rating-input {
            display: flex;
            gap: 5px;
            margin-bottom: 15px;
        }

        .rating-input input[type="radio"] {
            display: none;
        }

        .rating-input label {
            font-size: 1.5em;
            cursor: pointer;
            color: #ddd;
            margin: 0;
        }

        .rating-input input[type="radio"]:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: var(--warning-yellow);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--accent-orange);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #ff5722;
        }

        .login-prompt {
            text-align: center;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 5px;
            color: var(--text-light);
        }

        .login-prompt a {
            color: var(--accent-orange);
            text-decoration: none;
            font-weight: 600;
        }

        .message-box {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }

        .message-box.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-box.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .no-reviews {
            text-align: center;
            padding: 40px;
            color: var(--text-light);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-gray);
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid var(--border-gray);
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: var(--primary-blue);
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: var(--primary-blue);
            color: white;
        }

        .pagination .current {
            background: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        @media (max-width: 768px) {
            .reviews-layout {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .reviews-header h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Header -->
    <div class="reviews-header">
        <h1>⭐ What Our Travelers Say</h1>
        <p>Real experiences from real adventurers</p>
    </div>

    <!-- Main Content -->
    <div class="reviews-container">
        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="reviews-layout">
            <!-- Reviews Section -->
            <div class="reviews-main">
                <!-- Stats Card -->
                <div class="stats-card">
                    <div class="stats-grid">
                        <div class="stat-box">
                            <span class="number"><?php echo isset($stats['total']) ? $stats['total'] : 0; ?></span>
                            <span class="label">Reviews</span>
                        </div>
                        <div class="stat-box">
                            <span class="number"><?php echo isset($stats['avg_rating']) ? number_format($stats['avg_rating'], 1) : 0; ?></span>
                            <span class="label">Average Rating</span>
                        </div>
                        <div class="stat-box">
                            <span class="number"><?php echo isset($stats['five_star']) ? $stats['five_star'] : 0; ?></span>
                            <span class="label">5-Star</span>
                        </div>
                    </div>

                    <!-- Rating Distribution -->
                    <div class="rating-distribution">
                        <div class="rating-bar">
                            <div class="stars">⭐⭐⭐⭐⭐</div>
                            <div class="bar">
                                <div class="bar-fill" style="width: <?php echo isset($stats['five_star'], $stats['total']) && $stats['total'] > 0 ? ($stats['five_star'] / $stats['total'] * 100) : 0; ?>%"></div>
                            </div>
                            <div class="count"><?php echo isset($stats['five_star']) ? $stats['five_star'] : 0; ?></div>
                        </div>
                        <div class="rating-bar">
                            <div class="stars">⭐⭐⭐⭐</div>
                            <div class="bar">
                                <div class="bar-fill" style="width: <?php echo isset($stats['four_star'], $stats['total']) && $stats['total'] > 0 ? ($stats['four_star'] / $stats['total'] * 100) : 0; ?>%"></div>
                            </div>
                            <div class="count"><?php echo isset($stats['four_star']) ? $stats['four_star'] : 0; ?></div>
                        </div>
                        <div class="rating-bar">
                            <div class="stars">⭐⭐⭐</div>
                            <div class="bar">
                                <div class="bar-fill" style="width: <?php echo isset($stats['three_star'], $stats['total']) && $stats['total'] > 0 ? ($stats['three_star'] / $stats['total'] * 100) : 0; ?>%"></div>
                            </div>
                            <div class="count"><?php echo isset($stats['three_star']) ? $stats['three_star'] : 0; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="filters-section">
                    <a href="reviews.php" class="filter-btn <?php echo $rating_filter === 0 ? 'active' : ''; ?>">
                        All Reviews
                    </a>
                    <a href="reviews.php?rating=5" class="filter-btn <?php echo $rating_filter === 5 ? 'active' : ''; ?>">
                        ⭐⭐⭐⭐⭐ 5 Star
                    </a>
                    <a href="reviews.php?rating=4" class="filter-btn <?php echo $rating_filter === 4 ? 'active' : ''; ?>">
                        ⭐⭐⭐⭐ 4 Star
                    </a>
                    <a href="reviews.php?rating=3" class="filter-btn <?php echo $rating_filter === 3 ? 'active' : ''; ?>">
                        ⭐⭐⭐ 3 Star
                    </a>
                </div>

                <!-- Reviews List -->
                <?php if (count($reviews) > 0): ?>
                    <div class="reviews-grid">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div>
                                        <div class="review-title"><?php echo htmlspecialchars($review['title']); ?></div>
                                        <div class="review-author">by <?php echo htmlspecialchars($review['full_name']); ?></div>
                                        <?php if (!empty($review['destination_name'])): ?>
                                            <div class="review-destination">📍 <?php echo htmlspecialchars($review['destination_name']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="review-stars">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <?php echo ($i < $review['rating']) ? '⭐' : '☆'; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></div>
                                <div class="review-date">
                                    📅 <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="reviews.php?page=1<?php echo ($rating_filter > 0 ? '&rating=' . $rating_filter : ''); ?>">« First</a>
                                <a href="reviews.php?page=<?php echo $page - 1; ?><?php echo ($rating_filter > 0 ? '&rating=' . $rating_filter : ''); ?>">‹ Previous</a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="reviews.php?page=<?php echo $i; ?><?php echo ($rating_filter > 0 ? '&rating=' . $rating_filter : ''); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="reviews.php?page=<?php echo $page + 1; ?><?php echo ($rating_filter > 0 ? '&rating=' . $rating_filter : ''); ?>">Next ›</a>
                                <a href="reviews.php?page=<?php echo $totalPages; ?><?php echo ($rating_filter > 0 ? '&rating=' . $rating_filter : ''); ?>">Last »</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <h2>🔍 No Reviews Yet</h2>
                        <p>Be the first to share your travel experience!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="sidebar">
                <!-- Submit Review Card -->
                <div class="submit-review-card">
                    <h3>✍️ Share Your Experience</h3>

                    <?php if (isUserLoggedIn()): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="submit_review">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="form-group">
                                <label>Rating</label>
                                <div style="display: flex; gap: 10px; flex-direction: row-reverse; justify-content: flex-end;">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="rating_<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo ($i === 5) ? 'checked' : ''; ?>>
                                        <label for="rating_<?php echo $i; ?>" style="margin: 0; cursor: pointer; font-size: 1.5em;">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="destination_id">Destination *</label>
                                <select name="destination_id" id="destination_id" required>
                                    <option value="">Select Destination</option>
                                    <?php foreach ($destinations as $dest): ?>
                                        <option value="<?php echo $dest['destination_id']; ?>">
                                            <?php echo htmlspecialchars($dest['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="title">Review Title *</label>
                                <input type="text" name="title" id="title" placeholder="e.g., Amazing adventure!" required>
                            </div>

                            <div class="form-group">
                                <label for="review_text">Your Review *</label>
                                <textarea name="review_text" id="review_text" placeholder="Share your travel experience..." required></textarea>
                            </div>

                            <button type="submit" class="btn-submit">Submit Review</button>
                            <p style="font-size: 0.8em; color: var(--text-light); margin-top: 10px; text-align: center;">
                                ✓ Reviews are moderated before publishing
                            </p>
                        </form>
                    <?php else: ?>
                        <div class="login-prompt">
                            <p>Login to share your travel experience</p>
                            <a href="login.php?redirect=reviews.php">🔐 Login Now →</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tips Card -->
                <div class="submit-review-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white;">
                    <h3 style="color: white;">💡 Review Tips</h3>
                    <ul style="list-style: none; padding: 0; font-size: 0.9em; line-height: 1.8;">
                        <li>✓ Be honest and specific</li>
                        <li>✓ Share photos if possible</li>
                        <li>✓ Mention best & worst parts</li>
                        <li>✓ Help other travelers decide</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>