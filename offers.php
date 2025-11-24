<?php
/**
 * KINDORA SPECIAL OFFERS PAGE
 * Displays limited-time deals and special offers
 * 
 * Features:
 * - Login check before booking
 * - Limited-time countdown timer
 * - Dynamic deals from database
 * - Special offer display with booking integration
 */

require_once __DIR__ . '/config.php';

// Get all active deals from database
$deals = array();
try {
    $deals = KindoraDatabase::query(
        "SELECT d.*, de.name as destination_name FROM deals d 
         LEFT JOIN destinations de ON d.destination_id = de.destination_id 
         WHERE d.is_active = 1 AND (d.end_date IS NULL OR DATE(d.end_date) >= CURDATE()) 
         ORDER BY d.discount DESC"
    ) ?: array();
} catch (Exception $e) {
    error_log("DEALS ERROR: " . $e->getMessage());
}

// Get deal count and statistics
$dealStats = array();
try {
    $dealStats = KindoraDatabase::fetchOne(
        "SELECT COUNT(*) as total, AVG(discount) as avg_discount, MAX(discount) as max_discount 
         FROM deals WHERE is_active = 1 AND (end_date IS NULL OR DATE(end_date) >= CURDATE())"
    ) ?: array('total' => 0);
} catch (Exception $e) {}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers - Kindora</title>
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
            --danger-red: #e74c3c;
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

        .offers-header {
            background: linear-gradient(135deg, var(--danger-red) 0%, var(--accent-orange) 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .offers-header::before {
            content: "🎉";
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 4em;
            opacity: 0.2;
        }

        .offers-header h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .offers-header p {
            font-size: 1.1em;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto 20px;
        }

        .offers-header .promo-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 0.95em;
            margin-top: 15px;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .offers-header .stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .number {
            font-size: 2em;
            font-weight: 700;
            display: block;
        }

        .stat-item .label {
            font-size: 0.9em;
            opacity: 0.9;
        }

        .offers-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-section h2 {
            color: var(--primary-blue);
            font-size: 1.3em;
            margin: 0;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid var(--border-gray);
            background: white;
            color: var(--text-dark);
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--accent-orange);
            color: white;
            border-color: var(--accent-orange);
        }

        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .offer-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .offer-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .offer-image {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5em;
            position: relative;
            overflow: hidden;
        }

        .offer-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .discount-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--danger-red);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 1.3em;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .timer-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .offer-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .offer-title {
            font-size: 1.3em;
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .offer-destination {
            font-size: 0.9em;
            color: var(--text-light);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .offer-description {
            color: var(--text-light);
            font-size: 0.9em;
            line-height: 1.6;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .offer-pricing {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 15px;
            padding: 15px 0;
            border-top: 1px solid var(--border-gray);
            border-bottom: 1px solid var(--border-gray);
        }

        .offer-price {
            font-size: 1.8em;
            color: var(--accent-orange);
            font-weight: 700;
        }

        .offer-original {
            font-size: 0.95em;
            color: var(--text-light);
            text-decoration: line-through;
        }

        .offer-savings {
            font-size: 0.85em;
            color: var(--success-green);
            font-weight: 600;
            margin-left: auto;
        }

        .offer-actions {
            display: flex;
            gap: 10px;
        }

        .btn-book-offer {
            flex: 1;
            padding: 12px 20px;
            background: var(--danger-red);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 0.95em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-book-offer:hover {
            background: #c0392b;
            transform: scale(1.02);
        }

        .btn-details-offer {
            flex: 1;
            padding: 12px 20px;
            background: transparent;
            color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
            border-radius: 5px;
            font-size: 0.95em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-details-offer:hover {
            background: var(--primary-blue);
            color: white;
        }

        .no-offers {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .no-offers h2 {
            font-size: 1.8em;
            color: var(--primary-blue);
            margin-bottom: 15px;
        }

        .login-required {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            z-index: 1000;
            display: none;
            max-width: 400px;
        }

        .login-required.show {
            display: block;
        }

        .login-required h2 {
            color: var(--primary-blue);
            margin-bottom: 15px;
        }

        .login-required p {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .login-required .buttons {
            display: flex;
            gap: 10px;
        }

        .login-required .buttons a,
        .login-required .buttons button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-required .btn-login {
            background: var(--accent-orange);
            color: white;
        }

        .login-required .btn-cancel {
            background: var(--light-gray);
            color: var(--text-dark);
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .modal-overlay.show {
            display: block;
        }

        @media (max-width: 768px) {
            .offers-header h1 {
                font-size: 1.8em;
            }

            .offers-header .stats {
                gap: 20px;
            }

            .offers-grid {
                grid-template-columns: 1fr;
            }

            .offer-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
     <?php require_once 'includes/header.php'; ?><br><br>

    <!-- Header Section -->
    <div class="offers-header">
        <h1>🎁 Special Offers & Deals</h1>
        <p>Limited-time discounts on your dream destinations</p>
        <div class="promo-badge">
            ⏰ Offers expire soon - Book now and save!
        </div>
        <div class="stats">
            <div class="stat-item">
                <span class="number"><?php echo isset($dealStats['total']) ? $dealStats['total'] : 0; ?></span>
                <span class="label">Active Offers</span>
            </div>
            <div class="stat-item">
                <span class="number">-<?php echo isset($dealStats['max_discount']) ? $dealStats['max_discount'] : 0; ?>%</span>
                <span class="label">Max Discount</span>
            </div>
            <div class="stat-item">
                <span class="number">Save ₹<?php echo isset($dealStats['avg_discount']) ? number_format($dealStats['avg_discount'] * 100, 0) : 0; ?></span>
                <span class="label">On Average</span>
            </div>
        </div>
    </div>

    <!-- Offers Section -->
    <div class="offers-container">
        <!-- Filter Section -->
        <div class="filter-section">
            <h2>Browse Offers</h2>
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterOffers('all')">All Deals</button>
                <button class="filter-btn" onclick="filterOffers('10-30')">10-30% Off</button>
                <button class="filter-btn" onclick="filterOffers('30+')">30%+ Off</button>
                <button class="filter-btn" onclick="filterOffers('expiring')">Expiring Soon</button>
            </div>
        </div>

        <!-- Offers Grid -->
        <?php if (count($deals) > 0): ?>
            <div class="offers-grid">
                <?php foreach ($deals as $deal): 
                    $discount = intval($deal['discount'] ?? 0);
                    $filterClass = ($discount >= 30) ? 'discount-30plus' : 'discount-10-30';
                ?>
                    <div class="offer-card" data-filter="<?php echo $filterClass; ?>">
                        <div class="offer-image">
                            <?php if (!empty($deal['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($deal['image_url']); ?>" alt="<?php echo htmlspecialchars($deal['title']); ?>" loading="lazy">
                            <?php else: ?>
                                ✈️
                            <?php endif; ?>
                            <div class="discount-badge">-<?php echo $discount; ?>%</div>
                            <?php if (!empty($deal['end_date']) && strtotime($deal['end_date']) - time() < 86400 * 7): ?>
                                <div class="timer-badge">⏰ Expires Soon</div>
                            <?php endif; ?>
                        </div>

                        <div class="offer-content">
                            <h3 class="offer-title"><?php echo htmlspecialchars($deal['title']); ?></h3>
                            
                            <?php if (!empty($deal['destination_name'])): ?>
                                <div class="offer-destination">
                                    📍 <?php echo htmlspecialchars($deal['destination_name']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <p class="offer-description"><?php echo htmlspecialchars(substr($deal['description'] ?? '', 0, 100)); ?>...</p>

                            <div class="offer-pricing">
                                <div class="offer-price">₹<?php echo number_format($deal['sale_price'], 0); ?></div>
                                <?php if (!empty($deal['original_price'])): ?>
                                    <span class="offer-original">₹<?php echo number_format($deal['original_price'], 0); ?></span>
                                    <span class="offer-savings">
                                        Save ₹<?php echo number_format($deal['original_price'] - $deal['sale_price'], 0); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="offer-actions">
                                <button class="btn-book-offer" onclick="bookOffer(<?php echo $deal['deal_id']; ?>, '<?php echo htmlspecialchars($deal['title']); ?>')">
                                    🎯 Grab Deal
                                </button>
                                <button class="btn-details-offer" onclick="viewOfferDetails(<?php echo $deal['deal_id']; ?>)">
                                    Learn More
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-offers">
                <h2>🔍 No Special Offers Available</h2>
                <p>Check back soon for amazing deals and limited-time offers!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Login Modal -->
    <div class="modal-overlay" id="loginModal"></div>
    <div class="login-required" id="loginDialog">
        <h2>🔐 Login Required</h2>
        <p>You need to be logged in to book a special offer. Please login to continue with your booking.</p>
        <div class="buttons">
            <a href="login.php?redirect=offers.php" class="btn-login">
                ✓ Go to Login
            </a>
            <button onclick="closeLoginDialog()" class="btn-cancel">
                Cancel
            </button>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        function bookOffer(dealId, dealTitle) {
            <?php if (isUserLoggedIn()): ?>
                // User is logged in, redirect to booking page
                window.location.href = 'booking.php?deal=' + dealId + '&deal_name=' + encodeURIComponent(dealTitle);
            <?php else: ?>
                // Show login modal
                document.getElementById('loginModal').classList.add('show');
                document.getElementById('loginDialog').classList.add('show');
            <?php endif; ?>
        }

        function viewOfferDetails(dealId) {
            window.location.href = 'deal-details.php?id=' + dealId;
        }

        function closeLoginDialog() {
            document.getElementById('loginModal').classList.remove('show');
            document.getElementById('loginDialog').classList.remove('show');
        }

        function filterOffers(type) {
            const cards = document.querySelectorAll('.offer-card');
            
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            cards.forEach(card => {
                if (type === 'all') {
                    card.style.display = '';
                } else if (type === '10-30') {
                    card.style.display = card.classList.contains('discount-10-30') ? '' : 'none';
                } else if (type === '30+') {
                    card.style.display = card.classList.contains('discount-30plus') ? '' : 'none';
                } else if (type === 'expiring') {
                    card.style.display = card.querySelector('.timer-badge') ? '' : 'none';
                }
            });
        }

        // Close modal when clicking overlay
        document.getElementById('loginModal').addEventListener('click', closeLoginDialog);
    </script>
</body>
</html>