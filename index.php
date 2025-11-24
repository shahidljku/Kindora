<?php require_once 'includes/header.php'; ?>
<?php
// Load ALL data directly from database
$places = [];
$packages = [];
$deals = [];
$testimonials = [];
$faq = [];
$reviews = [];
$sevenWonders = [];
$currencyOptions = getCurrencyOptions();
$activeCurrency = getCurrencyPreference();
$manual_page_map_by_id = [
    1  => '/Kindora/pages/continents/asia.php',
    2  => '/Kindora/pages/continents/europe.php',
    3  => '/Kindora/pages/continents/africa.php',
    4  => '/Kindora/pages/continents/north-america.php',
    5  => '/Kindora/pages/continents/south-america.php',
    6  => '/Kindora/pages/continents/australia.php',
    7  => '/Kindora/pages/continents/antarctica.php',
    9  => '/Kindora/7wonders/taj-mahal.php',
    10  => '/Kindora/7wonders/petra.php',
    11 => '/Kindora/7wonders/machu-picchu.php',
];
// Travelers stat
$travelers = 0;
$tours = 0;
$reviewsCount = 0;
$destinations = 0;
$reviewDestinations = [];
$flashMessage = '';
$flashType = '';

try {
    $places = KindoraDatabase::query(
        "SELECT d.*, 
                dp.price_economy, 
                dp.price_standard, 
                dp.price_luxury, 
                dp.currency AS pricing_currency
         FROM destinations d
         LEFT JOIN destination_pricing dp 
            ON dp.destination_id = d.destination_id 
           AND dp.season = 'standard'
         WHERE d.is_active = 1
         ORDER BY d.destination_id ASC
         LIMIT 10"
    ) ?: [];
} catch (Exception $e) {
    error_log("PLACES ERROR: " . $e->getMessage());
}

try {
    $packages = KindoraDatabase::query(
        "SELECT * FROM travel_packages WHERE is_active = 1 ORDER BY package_id ASC LIMIT 10"
    ) ?: [];
} catch (Exception $e) {
    error_log("PACKAGES ERROR: " . $e->getMessage());
}

try {
    $deals = KindoraDatabase::query(
        "SELECT * FROM deals WHERE is_active = 1 AND (end_date IS NULL OR DATE(end_date) >= CURDATE()) ORDER BY discount DESC LIMIT 10"
    ) ?: [];
} catch (Exception $e) {
    error_log("DEALS ERROR: " . $e->getMessage());
}

try {
    $testimonials = KindoraDatabase::query(
        "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY testimonial_id DESC LIMIT 5"
    ) ?: [];
} catch (Exception $e) {
    error_log("TESTIMONIALS ERROR: " . $e->getMessage());
}

try {
    $faq = KindoraDatabase::query(
        "SELECT * FROM faq WHERE is_active = 1 ORDER BY sort_order ASC"
    ) ?: [];
} catch (Exception $e) {
    error_log("FAQ ERROR: " . $e->getMessage());
}

try {
    $reviews = KindoraDatabase::query(
        "SELECT r.*, u.full_name FROM reviews r 
         JOIN users u ON r.user_id = u.user_id 
         WHERE r.status = 'approved' ORDER BY r.created_at DESC LIMIT 4"
    ) ?: [];
} catch (Exception $e) {
    error_log("REVIEWS ERROR: " . $e->getMessage());
}

try {
    $sevenWonders = KindoraDatabase::query(
        "SELECT * FROM destinations WHERE type = '7 wonders' AND is_active = 1 ORDER BY destination_id ASC"
    ) ?: [];
} catch (Exception $e) {
    error_log("WONDERS ERROR: " . $e->getMessage());
}

// Get stats
try {
    $travelerData = KindoraDatabase::query("SELECT COUNT(DISTINCT user_id) as cnt FROM bookings WHERE status = 'completed'");
    $travelers = $travelerData[0]['cnt'] ?? 0;
} catch (Exception $e) {}

try {
    $toursData = KindoraDatabase::query("SELECT COUNT(*) as cnt FROM bookings WHERE status = 'completed'");
    $tours = $toursData[0]['cnt'] ?? 0;
} catch (Exception $e) {}

try {
    $reviewsData = KindoraDatabase::query("SELECT COUNT(*) as cnt FROM reviews WHERE status = 'approved'");
    $reviewsCount = $reviewsData[0]['cnt'] ?? 0;
} catch (Exception $e) {}

try {
    $destData = KindoraDatabase::query("SELECT COUNT(*) as cnt FROM destinations WHERE is_active = 1");
    $destinations = $destData[0]['cnt'] ?? 0;
} catch (Exception $e) {}

if (!empty($userData) && !empty($userData['user_id'])) {
    try {
        $reviewDestinations = KindoraDatabase::query(
            "SELECT DISTINCT d.destination_id, d.name
             FROM bookings b
             JOIN destinations d ON b.destination_id = d.destination_id
             WHERE b.user_id = ?
               AND b.status IN ('completed', 'pending', 'confirmed', 'approved')
             ORDER BY d.name ASC",
            [ (int)$userData['user_id'] ]
        ) ?: [];
    } catch (Exception $e) {
        error_log("REVIEW DESTINATIONS ERROR: " . $e->getMessage());
        $reviewDestinations = [];
    }
}

if (!empty($_SESSION['message'])) {
    $flashMessage = $_SESSION['message'];
    $flashType = $_SESSION['message_type'] ?? 'info';
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8" />
    <link rel="icon" type="image/png" href="assets/images/kindora-logo.ico" />
    <title>Kindora - Explore the World</title>
    <link href="/Kindora/assets/css/styles.css" rel="stylesheet" />
    <link href="/Kindora/assets/css/all.min.css" rel="stylesheet" />
    <link href="/Kindora/assets/css/poppins.css" rel="stylesheet" />
    <style>
        .flash-message {
            max-width: 600px;
            margin: 0 auto 20px;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
        }
        .flash-message.success { background: #ecfdf5; color: #047857; border: 1px solid #6ee7b7; }
        .flash-message.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .flash-message.info { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .review-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e7ff;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-bottom: 12px;
        }
        .destination-search-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }
        .destination-search-input.invalid {
            border-color: #f87171;
            box-shadow: 0 0 0 2px rgba(248, 113, 113, 0.2);
        }
        .star-rating {
            display: flex;
            gap: 6px;
            font-size: 1.5rem;
        }
        .star-btn {
            cursor: pointer;
            color: #c7d2fe;
            transition: color 0.2s ease;
            user-select: none;
        }
        .star-btn.active {
            color: #f59e0b;
        }
        .star-btn:focus {
            outline: 2px solid #fbbf24;
            outline-offset: 2px;
        }
        .faq-question {
            width: 100%;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .faq-question.open {
            background: #eef2ff;
            border-color: #c7d2fe;
        }
        .faq-question i {
            transition: transform 0.2s ease;
        }
        .faq-question.open i {
            transform: rotate(180deg);
        }
        .faq-answer {
            border: 1px solid #e5e7eb;
            border-top: none;
            padding: 14px 18px;
            background: #f9fafb;
            border-radius: 0 0 10px 10px;
            display: none;
            max-height: none !important;
            overflow: visible !important;
            transition: none !important;
        }
        .faq-answer.active {
            display: block;
        }
        .no-destinations-note {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            padding: 16px;
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
  </head>
  <body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">
                <span class="title-line">Explore the</span>
                <span class="title-line highlight">World</span>
            </h1>
            <p class="hero-subtitle">Your journey to unforgettable adventures starts here</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" onclick="scrollToSection('popular')">
                    <i class="fas fa-rocket"></i>
                    Start Exploring
                </button>
                <button class="btn btn-secondary" onclick="scrollToSection('packages')">
                    <i class="fas fa-gift"></i>
                    View Packages
                </button>
            </div>
        </div>
        <div class="scroll-indicator">
            <div class="scroll-arrow"></div>
        </div>
    </section>

    <!-- Search Bar -->
    <!-- FIND YOUR PERFECT DESTINATION SECTION -->
<section class="find-destination-section" style="
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 60px 20px;
    text-align: center;
    margin: 50px 0;
">
    <h2 style="font-size: 2em; margin-bottom: 15px; font-weight: 700;">🔍 Find Your Perfect Destination</h2>
    <p style="font-size: 1.1em; margin-bottom: 30px; opacity: 0.95; max-width: 600px; margin-left: auto; margin-right: auto;">
        Search from hundreds of destinations. Filter by difficulty, season, and popularity to find your ideal adventure.
    </p>
    <a href="search.php" style="
        display: inline-block;
        background: #ff6b35;
        color: white;
        padding: 15px 40px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    " onmouseover="this.style.background='#ff5722'; this.style.transform='scale(1.05)'" 
       onmouseout="this.style.background='#ff6b35'; this.style.transform='scale(1)'">
        🔍 Search Destinations →
    </a>
</section>


    <!-- Popular Places Section -->
    <section id="popular" class="popular-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Top 10 Most Popular Places</h2>
                <p class="section-subtitle">Discover the world's most breathtaking destinations</p>
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-top:12px;">
                    <label for="currencySwitcher" style="font-weight:600; color:#555;">Currency:</label>
                    <select id="currencySwitcher" style="padding:6px 12px; border-radius:6px; border:1px solid #d0d0d0; min-width:140px;">
                        <?php foreach ($currencyOptions as $code => $meta): ?>
                            <option value="<?php echo htmlspecialchars($code); ?>" <?php echo $code === $activeCurrency ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($code); ?> (<?php echo htmlspecialchars($meta['symbol']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="places-grid" id="placesGrid">
                <?php if (!empty($places)): 
                    foreach ($places as $place): ?>
<?php
    // inside the loop
    $placeId = isset($place['destination_id']) ? (int)$place['destination_id'] : (int)($place['id'] ?? 0);

    // determine target URL: mapped by id or fallback to booking
    $targetUrl = isset($manual_page_map_by_id[$placeId]) ? $manual_page_map_by_id[$placeId] : '/Kindora/booking.php?destination_id=' . $placeId;
    $priceCurrency = $place['pricing_currency'] ?? ($place['currency'] ?? 'USD');
    $priceBaseAmount = isset($place['price_standard']) ? floatval($place['price_standard']) : floatval($place['price'] ?? 0);
    $convertedAmount = convertCurrencyAmount($priceBaseAmount, $priceCurrency, $activeCurrency);
?>
<div class="place-card" data-category="<?php echo htmlspecialchars($place['type']); ?>">
  <div class="card-link"
       role="button"
       tabindex="0"
       onclick="navigateTo('<?php echo htmlspecialchars($targetUrl, ENT_QUOTES); ?>')"
       onkeypress="if(event.key==='Enter' || event.key===' ') { event.preventDefault(); navigateTo('<?php echo htmlspecialchars($targetUrl, ENT_QUOTES); ?>'); }"
       style="cursor:pointer;">
    <div class="card-image">
      <img src="<?php echo htmlspecialchars($place['image_url']); ?>"
           alt="<?php echo htmlspecialchars($place['name']); ?>" loading="lazy" />
      <div class="card-overlay">
        <div class="card-rating"><i class="fas fa-star"></i><span>4.8</span></div>
        <div class="card-price"
             data-base-amount="<?php echo htmlspecialchars($priceBaseAmount, ENT_QUOTES, 'UTF-8'); ?>"
             data-base-currency="<?php echo htmlspecialchars($priceCurrency, ENT_QUOTES, 'UTF-8'); ?>"
             data-prefix="From ">
            From <?php echo formatCurrencyAmount($convertedAmount, $activeCurrency); ?>
        </div>
      </div>
    </div>

    <div class="card-content">
      <h3><?php echo htmlspecialchars($place['name']); ?></h3>
      <p><?php echo htmlspecialchars(substr($place['description'] ?? '', 0, 80)); ?>...</p>
    </div>
  </div>
</div>

                <?php endforeach; 
                else: ?>
                <p style="color:#999; padding:40px; text-align:center;">No places available</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <!-- OUR TRAVEL PACKAGES SECTION -->
<section class="packages-section" style="
    padding: 60px 20px;
    background: white;
    margin: 50px 0;
">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 2em; margin-bottom: 15px; font-weight: 700; color: #1e3c72;">🎒 Our Travel Packages</h2>
        <p style="font-size: 1.1em; margin-bottom: 30px; color: #666; max-width: 600px; margin-left: auto; margin-right: auto;">
            Carefully curated experiences designed for every type of traveler. Choose from budget-friendly to luxury packages.
        </p>
        <a href="packages.php" style="
            display: inline-block;
            background: #ff6b35;
            color: white;
            padding: 15px 40px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        " onmouseover="this.style.background='#ff5722'; this.style.transform='scale(1.05)'" 
           onmouseout="this.style.background='#ff6b35'; this.style.transform='scale(1)'">
            🎒 Browse All Packages →
        </a>
    </div>
</section>


    <!-- Featured Deals -->
   <!-- SPECIAL OFFERS SECTION -->
<section class="offers-section" style="
    background: linear-gradient(135deg, #e74c3c 0%, #ff6b35 100%);
    color: white;
    padding: 60px 20px;
    text-align: center;
    margin: 50px 0;
">
    <h2 style="font-size: 2em; margin-bottom: 15px; font-weight: 700;">🎁 Special Offers & Deals</h2>
    <p style="font-size: 1.1em; margin-bottom: 30px; opacity: 0.95; max-width: 600px; margin-left: auto; margin-right: auto;">
        ⏰ Limited-time discounts on your dream destinations. Save big on the best packages!
    </p>
    <a href="offers.php" style="
        display: inline-block;
        background: white;
        color: #e74c3c;
        padding: 15px 40px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    " onmouseover="this.style.transform='scale(1.05)'" 
       onmouseout="this.style.transform='scale(1)'">
        🎁 View All Offers →
    </a>
</section>

    <!-- 7 Wonders Section -->
    <section class="wonders-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">7 Wonders of the World</h2>
                <p class="section-subtitle">Discover the most magnificent man-made structures on Earth</p>
            </div>
            <div class="wonders-grid" id="wondersGrid">
                <?php if (!empty($sevenWonders)): 
                    foreach ($sevenWonders as $wonder): ?>
                <div class="wonder-card">
                    <a href="#">
                        <div class="wonder-image">
                            <img src="<?php echo htmlspecialchars($wonder['image_url']); ?>" alt="<?php echo htmlspecialchars($wonder['name']); ?>" />
                            <div class="wonder-overlay">
                                <div class="wonder-info">
                                    <h3><?php echo htmlspecialchars($wonder['name']); ?></h3>
                                    <div class="wonder-rating"><i class="fas fa-star"></i><span>4.8</span></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; 
                else: ?>
                <div style="color:#999; padding:40px; text-align:center;">No wonders available</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- WHAT OUR TRAVELERS SAY SECTION -->
<section class="reviews-section" style="
    padding: 60px 20px;
    background: #f8f9fa;
    margin: 50px 0;
">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="font-size: 2em; margin-bottom: 15px; font-weight: 700; color: #1e3c72; text-align: center;">
            ⭐ What Our Travelers Say
        </h2>
        <p style="font-size: 1.1em; margin-bottom: 40px; color: #666; text-align: center; max-width: 600px; margin-left: auto; margin-right: auto;">
            Real experiences from real adventurers. Read thousands of reviews from travelers who've used Kindora.
        </p>

        <!-- Reviews Grid (will be populated from database on reviews.php) -->
        <div style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        ">
            <!-- These are PLACEHOLDER reviews - actual reviews load dynamically on reviews.php -->
            <?php 
            // Fetch latest 3 reviews dynamically
            try {
                $latest_reviews = KindoraDatabase::query(
                    "SELECT r.*, u.full_name, d.name as destination_name FROM reviews r 
                     JOIN users u ON r.user_id = u.user_id 
                     LEFT JOIN destinations d ON r.destination_id = d.destination_id 
                     WHERE r.status = 'approved' 
                     ORDER BY r.created_at DESC 
                     LIMIT 3"
                ) ?: array();
            } catch (Exception $e) {
                $latest_reviews = array();
            }

            if (count($latest_reviews) > 0) {
                foreach ($latest_reviews as $review):
            ?>
            <div style="
                background: white;
                padding: 25px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                text-align: left;
                border-left: 4px solid #ff6b35;
            ">
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: start;
                    margin-bottom: 15px;
                ">
                    <div>
                        <h4 style="color: #1e3c72; margin-bottom: 5px; font-weight: 600;">
                            <?php echo htmlspecialchars($review['title']); ?>
                        </h4>
                        <p style="font-size: 0.9em; color: #666; margin-bottom: 3px;">
                            by <?php echo htmlspecialchars($review['full_name']); ?>
                        </p>
                        <?php if (!empty($review['destination_name'])): ?>
                        <p style="font-size: 0.85em; color: #999;">
                            📍 <?php echo htmlspecialchars($review['destination_name']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div style="color: #f39c12; font-size: 1em; letter-spacing: 2px;">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <?php echo ($i < $review['rating']) ? '⭐' : '☆'; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                <p style="color: #333; line-height: 1.6; font-size: 0.95em; margin-bottom: 15px;">
                    <?php echo htmlspecialchars(substr($review['review_text'], 0, 150)); ?>...
                </p>
                <p style="font-size: 0.85em; color: #999;">
                    📅 <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                </p>
            </div>
            <?php endforeach; ?>
            <?php } else { ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;">
                <p>Be the first to share your travel experience!</p>
            </div>
            <?php } ?>
        </div>

        <div style="text-align: center;">
            <a href="reviews.php" style="
                display: inline-block;
                background: #1e3c72;
                color: white;
                padding: 15px 40px;
                border-radius: 5px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
                cursor: pointer;
            " onmouseover="this.style.background='#2a5298'; this.style.transform='scale(1.05)'" 
               onmouseout="this.style.background='#1e3c72'; this.style.transform='scale(1)'">
                ⭐ Read All Reviews & Submit Your Own →
            </a>
        </div>
    </div>
</section>
<!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2>Stay Updated with Travel Deals</h2>
                    <p>Get exclusive offers, travel tips, and destination guides delivered to your inbox</p>
                </div>
                <form class="newsletter-form" id="newsletterForm" onsubmit="subscribeNewsletter(event)">
                    <div class="form-group">
                        <input type="email" id="newsletterEmail" placeholder="Enter your email address" required />
                        <button type="submit" class="btn btn-newsletter">
                            <i class="fas fa-paper-plane"></i>
                            Subscribe
                        </button>
                    </div>
                    <div id="newsletterMsg"></div>
                </form>
            </div>
        </div>
    </section>

    <!-- Volunteers & Events Section -->
    <section class="volunteers-events-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Kindora Volunteers & Events (2025)</h2>
                <p class="section-subtitle">Join our community of passionate travelers and volunteers</p>
            </div>
            <div class="volunteers-grid">
                <div class="volunteer-card">
                    <h3>First Year Volunteers</h3>
                    <p>Our journey begins with passionate travelers and volunteers joining hands to explore and promote sustainable tourism.</p>
                </div>
                <div class="volunteer-card">
                    <h3>Local Partners</h3>
                    <p>Kindora has started collaborations with local guides and cultural storytellers to bring authentic experiences.</p>
                </div>
                <div class="volunteer-card">
                    <h3>Sustainability Ambassadors</h3>
                    <p>Early volunteers working on eco-travel, heritage protection, and community-led projects.</p>
                </div>
                <div class="volunteer-card">
                    <h3>Launch Event</h3>
                    <p>Kindora officially launched in 2025 with a vision to inspire world exploration.</p>
                </div>
                <div class="volunteer-card">
                    <h3>Virtual Culture Exchange</h3>
                    <p>Hosted our first online cultural session connecting travelers from different continents.</p>
                </div>
                <div class="volunteer-card">
                    <h3>Eco & Community Activities</h3>
                    <p>Beginning small eco-drives and heritage awareness campaigns with local groups.</p>
                </div>
                <div class="volunteer-card">
                    <h3>Countries Featured</h3>
                    <p>Within the first year, Kindora highlights major attractions from Asia, Europe, and Africa.</p>
                </div>
                <div class="volunteer-card">
                    <h3>Community Growth</h3>
                    <p>Thousands of explorers inspired to travel responsibly since our launch.</p>
                </div>
                <div class="volunteer-card">
                    <h3>Future Vision</h3>
                    <p>Expanding our global reach by adding more destinations, volunteer programs, and cultural events in upcoming years.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Traveler Reviews</h2>
                <p class="section-subtitle">Real experiences from real adventurers</p>
            </div>
            <div class="reviews-grid" id="reviewsList">
                <?php if (!empty($reviews)): 
                    foreach ($reviews as $review): ?>
                <div class="review-card">
                    <h4><?php echo htmlspecialchars($review['full_name']); ?></h4>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= (int)$review['rating']): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <p><?php echo htmlspecialchars(substr($review['review_text'], 0, 100)); ?>...</p>
                </div>
                <?php endforeach; 
                endif; ?>
            </div>
            <div class="review-form-container">
                <h3>Add Your Review</h3>
                <?php if ($flashMessage): ?>
                <div class="flash-message <?php echo htmlspecialchars($flashType); ?>">
                    <?php echo htmlspecialchars($flashMessage); ?>
                </div>
                <?php endif; ?>
                <?php if ($userData): ?>
                    <?php if (!empty($reviewDestinations)): ?>
                <form id="reviewForm" class="review-form" action="submit_review.php" method="POST">
                    <label for="reviewerName" class="sr-only">Name</label>
                    <input id="reviewerName" type="text" value="<?php echo htmlspecialchars($userData['full_name']); ?>" readonly />
                    <label for="destinationSearch" class="sr-only">Destination</label>
                    <input
                        type="text"
                        id="destinationSearch"
                        name="destination_name"
                        class="destination-search-input"
                        list="destinationOptions"
                        placeholder="Search and select a destination"
                        autocomplete="off"
                        required
                    />
                    <datalist id="destinationOptions">
                        <?php foreach ($reviewDestinations as $dest): ?>
                        <option data-id="<?php echo (int)$dest['destination_id']; ?>" value="<?php echo htmlspecialchars($dest['name']); ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <input type="hidden" name="destination_id" id="selectedDestinationId" value="" />
                    <div id="starRating" class="star-rating">
                        <span data-value="1" class="star-btn">★</span>
                        <span data-value="2" class="star-btn">★</span>
                        <span data-value="3" class="star-btn">★</span>
                        <span data-value="4" class="star-btn">★</span>
                        <span data-value="5" class="star-btn">★</span>
                    </div>
                    <input type="hidden" name="rating" id="selectedRating" value="0" />
                    <textarea id="reviewComment" name="text" rows="4" placeholder="Share your experience..." minlength="10" required></textarea>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
                    <?php else: ?>
                <p class="no-destinations-note">You haven’t booked any trips yet. Book a destination to share your experience.</p>
                    <?php endif; ?>
                <?php else: ?>
                <p><a href="pages/login.php">Login</a> to submit a review</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Counter Section -->
    <section class="counter-section">
        <div class="container">
            <div class="counter-content">
                <h2 class="counter-title">Our Achievements</h2>
                <div class="counter-grid">
                    <div class="counter-box">
                        <h3 class="counter" data-target="<?php echo $travelers; ?>"><?php echo $travelers; ?></h3>
                        <p>Happy Travelers</p>
                    </div>
                    <div class="counter-box">
                        <h3 class="counter" data-target="<?php echo $tours; ?>"><?php echo $tours; ?></h3>
                        <p>Tours Organized</p>
                    </div>
                    <div class="counter-box">
                        <h3 class="counter" data-target="<?php echo $reviewsCount; ?>"><?php echo $reviewsCount; ?></h3>
                        <p>Reviews</p>
                    </div>
                    <div class="counter-box">
                        <h3 class="counter" data-target="<?php echo $destinations; ?>"><?php echo $destinations; ?></h3>
                        <p>Destinations</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Everything you need to know about traveling with Kindora</p>
            </div>
            <div class="faq-container" id="faqContainer">
                <?php if (!empty($faq)): 
                    foreach ($faq as $item): ?>
                <div class="faq-item">
                    <button class="faq-question" type="button" aria-expanded="false">
                        <span><?php echo htmlspecialchars($item['question']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p><?php echo htmlspecialchars($item['answer']); ?></p>
                    </div>
                </div>
                <?php endforeach; 
                else: ?>
                <div style="color:#999; padding:40px; text-align:center;">No FAQs available</div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- Footer -->
<?php require_once 'includes/footer.php'; ?>
    <script>
  // server-side login flag (rendered per-request)
  const IS_LOGGED_IN = <?php echo isUserLoggedIn() ? 'true' : 'false'; ?>;

  function handleCardClick(event, destinationId) {
    event.preventDefault();
    event.stopPropagation();

    const bookingUrl = '/Kindora/booking.php?destination_id=' + encodeURIComponent(destinationId);

    if (IS_LOGGED_IN) {
      window.location.href = bookingUrl;
    } else {
      window.location.href = '/Kindora/login.php?return=' + encodeURIComponent(bookingUrl);
    }
  }

  // Prevent Enter/Space from triggering while typing in inputs
  document.addEventListener('keydown', function(e){
    const active = document.activeElement;
    if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT' || active.isContentEditable)) {
      return;
    }
    // (no global behavior needed here because each card handles its own onkeypress)
  });
</script>
<script>
function navigateTo(url) {
  if (!url) return;
  // allow normal navigation
  window.location.href = url;
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ accordion behavior
    const faqButtons = document.querySelectorAll('.faq-question');
    faqButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const answer = btn.nextElementSibling;
            const isOpen = btn.classList.contains('open');

            // Close other items for cleaner UX
            faqButtons.forEach((other) => {
                if (other !== btn) {
                    other.classList.remove('open');
                    other.setAttribute('aria-expanded', 'false');
                    if (other.nextElementSibling) {
                        other.nextElementSibling.classList.remove('active');
                    }
                }
            });

            if (!isOpen) {
                btn.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
                if (answer) {
                    answer.classList.add('active');
                }
            } else {
                btn.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
                if (answer) {
                    answer.classList.remove('active');
                }
            }
        });
    });

    // Review star rating behavior
    const starRating = document.getElementById('starRating');
    const ratingInput = document.getElementById('selectedRating');
    if (starRating && ratingInput) {
        const stars = starRating.querySelectorAll('.star-btn');

        const updateStars = (value) => {
            stars.forEach((star) => {
                const starValue = parseInt(star.dataset.value, 10);
                star.classList.toggle('active', starValue <= value);
            });
        };

        stars.forEach((star) => {
            star.setAttribute('role', 'button');
            star.setAttribute('tabindex', '0');

            const selectStar = () => {
                const value = parseInt(star.dataset.value, 10);
                ratingInput.value = value;
                updateStars(value);
            };

            star.addEventListener('click', selectStar);
            star.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    selectStar();
                }
            });
        });
    }

    // Ensure rating selected before submit
    const destinationSearch = document.getElementById('destinationSearch');
    const destinationIdInput = document.getElementById('selectedDestinationId');
    const destinationOptions = document.getElementById('destinationOptions');

    const syncDestinationSelection = () => {
        if (!(destinationSearch && destinationIdInput && destinationOptions)) {
            return;
        }
        const optionList = Array.from(destinationOptions.options);
        const typedValue = destinationSearch.value.trim().toLowerCase();
        const matchedOption = optionList.find(
            (opt) => opt.value.toLowerCase() === typedValue
        );

        if (matchedOption) {
            destinationIdInput.value = matchedOption.dataset.id || '';
            destinationSearch.classList.remove('invalid');
        } else {
            destinationIdInput.value = '';
        }
    };

    if (destinationSearch && destinationIdInput && destinationOptions) {
        destinationSearch.addEventListener('input', syncDestinationSelection);
        destinationSearch.addEventListener('change', syncDestinationSelection);
        destinationSearch.addEventListener('blur', () => {
            syncDestinationSelection();
            if (!destinationIdInput.value) {
                destinationSearch.classList.add('invalid');
            }
        });
        destinationSearch.addEventListener('focus', () => {
            destinationSearch.classList.remove('invalid');
        });
    }

    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm && ratingInput) {
        reviewForm.addEventListener('submit', (event) => {
            syncDestinationSelection();
            const ratingValue = parseInt(ratingInput.value, 10);
            const destinationValue = destinationIdInput ? parseInt(destinationIdInput.value, 10) : 0;
            if (!ratingValue || ratingValue < 1) {
                event.preventDefault();
                alert('Please select a star rating before submitting your review.');
                return;
            }
            if (!destinationValue || destinationValue < 1) {
                event.preventDefault();
                if (destinationSearch) {
                    destinationSearch.classList.add('invalid');
                    destinationSearch.focus();
                }
                alert('Please pick a destination from the list before submitting your review.');
            }
        });
    }
});
</script>
<script>
(function(){
    const currencyRates = <?php echo json_encode($currencyOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    let activeCurrency = '<?php echo $activeCurrency; ?>';
    const selectEl = document.getElementById('currencySwitcher');

    if (!selectEl || !currencyRates) {
        return;
    }

    function convertAmount(amount, fromCurrency, toCurrency) {
        const from = currencyRates[fromCurrency];
        const to = currencyRates[toCurrency];
        if (!from || !to) {
            return amount;
        }
        if (fromCurrency === toCurrency) {
            return amount;
        }
        let usdAmount = amount;
        if (fromCurrency !== 'USD') {
            const fromRate = parseFloat(from.rate_to_usd || 0);
            if (!fromRate) {
                return amount;
            }
            usdAmount = amount * fromRate;
        }
        if (toCurrency === 'USD') {
            return usdAmount;
        }
        const targetRate = parseFloat(to.rate_to_usd || 0);
        if (!targetRate) {
            return usdAmount;
        }
        return usdAmount / targetRate;
    }

    function formatAmount(amount, currency) {
        const symbol = currencyRates[currency]?.symbol || (currency + ' ');
        return symbol + Number(amount).toFixed(2);
    }

    function persistCurrency(code) {
        fetch('api/set-currency.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({currency: code})
        }).catch(()=>{});
    }

    function applyCurrency(code, persist = true) {
        if (!currencyRates[code]) {
            return;
        }
        activeCurrency = code;
        document.querySelectorAll('[data-base-amount]').forEach((node) => {
            const baseAmount = parseFloat(node.dataset.baseAmount || '0');
            const baseCurrency = node.dataset.baseCurrency || 'USD';
            const prefix = node.dataset.prefix || '';
            const converted = convertAmount(baseAmount, baseCurrency, activeCurrency);
            node.textContent = prefix + formatAmount(converted, activeCurrency);
        });
        if (persist) {
            persistCurrency(code);
        }
    }

    selectEl.addEventListener('change', (event) => {
        applyCurrency(event.target.value);
    });

    // Ensure server-rendered text follows the latest formatting
    applyCurrency(activeCurrency, false);
})();
</script>
  </body>
</html>