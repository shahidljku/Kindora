<?php
// haxd4.php - dynamic version: loads content from DB while preserving markup/styles
// Uses existing project config/helpers (config.php loads paths.php)
require_once __DIR__ . '/config.php'; // provides $pdo and KindoraDatabase_* helpers and getPath(), getImagePath(). :contentReference[oaicite:3]{index=3} :contentReference[oaicite:4]{index=4}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- improved resolve_img ---
if (!function_exists('resolve_img')) {
    function resolve_img($url) {
        // placeholder path (use getPath or fallback)
        $placeholder = function_exists('getPath') ? getPath('images_places') . 'placeholder.avif' : (defined('SITE_URL') ? SITE_URL . 'assets/images/placeholder.avif' : 'assets/images/placeholder.avif');

        if (empty($url)) return $placeholder;

        // if absolute URL or data URL or root-absolute path -> leave as-is
        if (preg_match('#^(https?://|data:|/)#i', $url)) return $url;

        // If path already contains assets/ -> prefix SITE_URL so browser gets /Kindora/assets/...
        if (stripos($url, 'assets/') === 0 || stripos($url, 'assets/') !== false) {
            if (defined('SITE_URL')) {
                return rtrim(SITE_URL, '/') . '/' . ltrim($url, '/');
            }
            // fallback: try basePath from paths.php
            if (function_exists('getPath')) {
                $base = getPath('images_web') ?: '';
                return $base . ltrim($url, ' /');
            }
            return $url;
        }

        // If it's a filename only (no folder slash), try common image folders from paths.php
        if (strpos($url, '/') === false) {
            // list of path keys to try
            $tryKeys = ['images_web','images_7wonders','images_packages','images_places','images_deals','images_icons'];
            foreach ($tryKeys as $k) {
                if (function_exists('getPath')) {
                    $p = getPath($k);
                    if (!empty($p)) {
                        // ensure trailing slash
                        if (substr($p, -1) !== '/') $p .= '/';
                        $candidate = $p . $url;
                        return $candidate; // return first likely path
                    }
                }
            }
        }

        // If url looks like "places/X.jpg" or "7wonders/..." use getPath base
        if (preg_match('#^(places|7wonders|our_packages|images|icons|web images)#i', $url)) {
            if (defined('SITE_URL')) {
                return rtrim(SITE_URL, '/') . '/' . ltrim($url, '/');
            }
        }

        // finally, return original (may still 404) or placeholder
        return $url ?: $placeholder;
    }
}


// fallback wrapper for getPath/getImagePath if missing (should be present from paths.php)
if (!function_exists('path_or_default')) {
    function path_or_default($key, $default) {
        if (function_exists('getPath')) {
            $p = getPath($key);
            if (!empty($p)) return $p;
        }
        return $default;
    }
}

// --------------- Load DB-driven data ---------------
// We'll use KindoraDatabase_select (from config.php). If not available, fall back to PDO.
function db_select($query, $params = array()) {
    if (function_exists('KindoraDatabase_select')) {
        return KindoraDatabase_select($query, $params);
    }
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('DB SELECT ERROR: ' . $e->getMessage());
        return [];
    }
}

function db_fetchone($query, $params = array()) {
    if (function_exists('KindoraDatabase_fetchOne')) {
        return KindoraDatabase_fetchOne($query, $params);
    }
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('DB FETCHONE ERROR: ' . $e->getMessage());
        return null;
    }
}

// Continents (Be Inspired)
try {
    $continents = db_select("SELECT destination_id, name, image_url FROM destinations WHERE is_active = 1 AND type = 'Continent' ORDER BY name ASC");
} catch (Exception $e) {
    error_log("CONTINENTS ERROR: " . $e->getMessage());
    $continents = [];
}
if (empty($continents)) {
    // fallback to the same defaults you used
    $continents = [
        ['destination_id' => 1, 'name' => 'Asia', 'image_url' => path_or_default('banner_asia','web images/banner/asia.jpg')],
        ['destination_id' => 2, 'name' => 'Europe', 'image_url' => path_or_default('banner_europe','web images/banner/europe.jpeg')],
        ['destination_id' => 3, 'name' => 'Africa', 'image_url' => path_or_default('banner_africa','web images/banner/africa.jpg')],
        ['destination_id' => 4, 'name' => 'North America', 'image_url' => path_or_default('banner_north_america','web images/banner/north america.jpg')],
        ['destination_id' => 5, 'name' => 'South America', 'image_url' => path_or_default('banner_south_america','7wonders/7_wonders/Machu Picchu.jpeg')],
        ['destination_id' => 6, 'name' => 'Australia', 'image_url' => path_or_default('banner_australia','web images/banner/australia.jpg')],
        ['destination_id' => 7, 'name' => 'Antarctica', 'image_url' => path_or_default('banner_antarctica','web images/banner/antarctica.jpeg')],
    ];
}

// Popular places (top 10)
try {
    $popularPlaces = db_select("SELECT destination_id, name, image_url FROM destinations WHERE is_active = 1 ORDER BY destination_id ASC LIMIT 10");
} catch (Exception $e) {
    error_log("POPULAR PLACES ERROR: " . $e->getMessage());
    $popularPlaces = [];
}

// Packages
try {
    $packages = db_select("SELECT package_id, name, description, image_url, price FROM travel_packages WHERE is_active = 1 ORDER BY package_id ASC LIMIT 12");
} catch (Exception $e) {
    error_log("PACKAGES ERROR: " . $e->getMessage());
    $packages = [];
}

// Deals (active)
try {
    $deals = db_select("SELECT deal_id, title, description, image_url, discount FROM deals WHERE is_active = 1 AND (end_date IS NULL OR DATE(end_date) >= CURDATE()) ORDER BY discount DESC LIMIT 10");
} catch (Exception $e) {
    error_log("DEALS ERROR: " . $e->getMessage());
    $deals = [];
}

// 7 Wonders
try {
    $sevenWonders = db_select("SELECT destination_id, name, image_url FROM destinations WHERE is_active = 1 AND type = '7 wonders' ORDER BY destination_id LIMIT 7");
} catch (Exception $e) {
    error_log("7WONDERS ERROR: " . $e->getMessage());
    $sevenWonders = [];
}

// Testimonials (or testimonials table)
try {
    $testimonials = db_select("SELECT testimonial_id, name, image_url, text, rating FROM testimonials WHERE is_active = 1 ORDER BY testimonial_id DESC LIMIT 6");
} catch (Exception $e) {
    error_log("TESTIMONIALS ERROR: " . $e->getMessage());
    $testimonials = [];
}

// Reviews (approved)
try {
    $reviews = db_select("SELECT r.review_id, r.rating, r.review_text, u.full_name as name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.status = 'approved' ORDER BY r.created_at DESC LIMIT 6");
} catch (Exception $e) {
    error_log("REVIEWS ERROR: " . $e->getMessage());
    $reviews = [];
}

// FAQs
try {
    $faqs = db_select("SELECT faq_id, question, answer FROM faq WHERE is_active = 1 ORDER BY sort_order ASC");
} catch (Exception $e) {
    error_log("FAQ ERROR: " . $e->getMessage());
    $faqs = [];
}

// Stats: travelers, tours, reviews_count, destinations
try {
    $stats = db_fetchone("
        SELECT
            (SELECT COALESCE(COUNT(*),0) FROM users WHERE role='user') as travelers,
            (SELECT COALESCE(COUNT(*),0) FROM bookings) as tours,
            (SELECT COALESCE(COUNT(*),0) FROM reviews WHERE status='approved') as reviews_count,
            (SELECT COALESCE(COUNT(*),0) FROM destinations WHERE is_active=1) as destinations
    ");
    if (!$stats) {
        $stats = ['travelers'=>0,'tours'=>0,'reviews_count'=>0,'destinations'=>0];
    }
} catch (Exception $e) {
    error_log("STATS ERROR: " . $e->getMessage());
    $stats = ['travelers'=>0,'tours'=>0,'reviews_count'=>0,'destinations'=>0];
}

// Helper to build place link slug
function place_link($name) {
    return 'places/' . urlencode(str_replace(' ', '_', $name)) . '.php';
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8" />
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars(path_or_default('favicon','kindora-logo.ico')); ?>" />
    <title>Kindora</title>
    <link href="../assets/css/homepage.css" rel="stylesheet" />
     <link href="<?php echo htmlspecialchars(path_or_default('css_common','../assets/css/common_nav_footer.css')); ?>" rel="stylesheet" />
  </head>
  <body>
  
    <video autoplay muted loop id="bg-video">
      <source src="<?php echo htmlspecialchars(path_or_default('video_bg','../bgvideo.mp4')); ?>" type="video/mp4" />
    </video>

    <div class="video-overlay">
      <h1>Explore the World</h1>
      <p>Your journey starts here</p>
      <button class="cta-btn" onclick="window.location.href='explore.php'">
        Start Exploring
      </button>
    </div>

    <div id="nav1">
      <a href="/Kindora/index.php" id="logo-link">
        <div id="logo">Kindora</div>
      </a>

      <div id="nav2">
        <div class="inspire-wrapper">
          <a class="a1 dropbtn" id="inspireBtn" href="#">Be Inspired</a>
          <div class="scroll-container1" id="inspireScroll">
            <?php foreach ($continents as $c): ?>
              <a data-href="<?php echo htmlspecialchars('pages/' . strtolower(str_replace(' ', '-', $c['name'])) . '.php'); ?>">
                <div class="image-wrapper1">
                  <img src="<?php echo htmlspecialchars(resolve_img($c['image_url'] ?? path_or_default('banner_' . strtolower(str_replace(' ', '_', $c['name'])) , 'web images/banner/asia.jpg'))); ?>" alt="<?php echo htmlspecialchars($c['name']); ?>" />
                  <div class="overlay-text"><?php echo htmlspecialchars($c['name']); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <a class="a1 dropbtn" href="explore.php">Places to go</a>
        <a class="a1 dropbtn" href="things_to_do.php">Things to do</a>
        <a class="a1 dropbtn" href="../booking.php">Plan Your Trip</a>
      </div>

      <button class="menu" onclick="toggleMenu()">☰</button>

      <div id="sidebar" class="sidebar">
        <button class="closebtn" onclick="toggleMenu()">×</button>
        <a href="home.php">Home</a>
        <a href="login.php">Login/Register</a>
        <a href="../booking.php">Book Your Trips</a>
        <a href="mytrips.php">My Trips</a>
        <a href="aboutus.php">About</a>
        <a href="contactus.php">Contact</a>
      </div>
    </div>

    <div id="popular">
      <h1 class="titletext">Top 10 Most Popular Places</h1>
      <div class="scroll-container">
        <?php if (!empty($popularPlaces)): foreach ($popularPlaces as $place): ?>
          <a href="<?php echo htmlspecialchars(place_link($place['name'])); ?>">
            <div class="image-wrapper">
              <img src="<?php echo htmlspecialchars(resolve_img($place['image_url'] ?? (path_or_default('images_places','places/') . $place['name'] . '.jpg'))); ?>" alt="<?php echo htmlspecialchars($place['name']); ?>" />
              <div class="overlay-text"><?php echo htmlspecialchars($place['name']); ?></div>
            </div>
          </a>
        <?php endforeach; else: ?>
          <!-- fallback static entries -->
          <a href="places/Eiffel_Tower.html">
            <div class="image-wrapper">
              <img src="places/Eiffel Tower.jpg" alt="Eiffel Tower" />
              <div class="overlay-text">Eiffel Tower</div>
            </div>
          </a>
        <?php endif; ?>
      </div>
    </div>

    <section class="fade-in">
      <h1 class="titletext">Our Packages</h1>
      <div class="packages">
        <?php if (!empty($packages)): foreach ($packages as $pkg): ?>
          <div class="package-card">
            <img src="<?php echo htmlspecialchars(resolve_img($pkg['image_url'] ?? (path_or_default('images_packages','our_packages/') . ($pkg['name'] ? str_replace(' ', '_', $pkg['name']) : 'package') . '.avif'))); ?>" alt="<?php echo htmlspecialchars($pkg['name']); ?>" />
            <h3><?php echo htmlspecialchars($pkg['name']); ?></h3>
            <p><?php echo htmlspecialchars($pkg['description']); ?></p>
            <a href="../booking.php?package_id=<?php echo (int)$pkg['package_id']; ?>" class="package-btn">Book Now</a>
          </div>
        <?php endforeach; else: ?>
          <!-- original static cards (kept) -->
          <div class="package-card">
            <img src="our_packages/summer.avif" alt="Summer" />
            <h3>Summer Escape</h3>
            <p>Enjoy sunny beaches, tropical islands, and exotic adventures.</p>
            <a href="summer.html" class="package-btn">Book Now</a>
          </div>
          <div class="package-card">
            <img src="our_packages/winter.avif" alt="Winter" />
            <h3>Winter Wonderland</h3>
            <p>Experience snowy mountains, skiing, and cozy winter retreats.</p>
            <a href="winter.html" class="package-btn">Book Now</a>
          </div>
          <div class="package-card">
            <img src="our_packages/monsoon.avif" alt="Monsoon" />
            <h3>Monsoon Magic</h3>
            <p>Explore lush greenery, waterfalls, and refreshing rains.</p>
            <a href="mosoon.html" class="package-btn">Book Now</a>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <br><br><br>
    
    <div class="slideshow-container">
      <?php if (!empty($deals)): foreach ($deals as $k => $deal): ?>
        <a href="packages/<?php echo htmlspecialchars(strtolower(str_replace(' ', '_', $deal['title']))); ?>.html" class="mySlides fade">
          <div class="numbertext"><?php echo ($k+1); ?> / <?php echo count($deals); ?></div>
          <img src="<?php echo htmlspecialchars(resolve_img($deal['image_url'] ?? (path_or_default('images_deals','images/') . basename($deal['image_url'] ?? '')))); ?>" alt="<?php echo htmlspecialchars($deal['title']); ?>" class="im"/>
          <div class="text">
            <strong><?php echo htmlspecialchars($deal['title']); ?> – <?php echo htmlspecialchars($deal['discount'] ?? ''); ?>% Off</strong><br />
            <?php echo htmlspecialchars($deal['description'] ?? ''); ?>
          </div>
        </a>
      <?php endforeach; else: ?>
        <!-- fallback slides kept -->
        <a href="packages/europe_escape.html" class="mySlides fade">
          <div class="numbertext">1 / 6</div>
          <img src="images/europe_romantic.avif" alt="Romantic Europe Escape" class="im"/>
          <div class="text"><strong>Romantic Europe Escape – 30% Off</strong><br />Explore Paris, Venice & Santorini for couples</div>
        </a>
      <?php endif; ?>
    </div>

    <div id="wonders">
      <h1 class="titletext">7 Wonders of the World</h1>
      <div class="scroll-container">
        <?php if (!empty($sevenWonders)): foreach ($sevenWonders as $w): ?>
          <a href="7wonders/<?php echo urlencode(str_replace(' ', '_', $w['name'])); ?>.html">
            <div class="image-wrapper">
              <img src="<?php echo htmlspecialchars(resolve_img($w['image_url'] ?? (path_or_default('images_7wonders','7wonders/7_wonders/') . $w['name'] . '.jpg'))); ?>" alt="<?php echo htmlspecialchars($w['name']); ?>" />
              <div class="overlay-text"><?php echo htmlspecialchars($w['name']); ?></div>
            </div>
          </a>
        <?php endforeach; else: ?>
          <!-- keep original 7 static wonders markup if DB empty -->
          <a href="7wonders/Taj_Mahal.html">
            <div class="image-wrapper">
              <img src="<?php echo htmlspecialchars(path_or_default('images_7wonders','7wonders/7_wonders/')); ?>Taj-Mahal.jpg" alt="Taj Mahal" />
              <div class="overlay-text">Taj Mahal</div>
            </div>
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Volunteers & Events Section (kept static content exactly) -->
    <section id="volunteers-events" class="volunteers-events-section">
      <h2>Kindora Volunteers & Events (2025)</h2>
      <div class="cards-container">
        <div class="card">
          <h3>First Year Volunteers</h3>
          <p>Our journey begins with passionate travelers and volunteers joining hands to explore and promote sustainable tourism.</p>
        </div>
        <div class="card">
          <h3>Local Partners</h3>
          <p>Kindora has started collaborations with local guides and cultural storytellers to bring authentic experiences.</p>
        </div>
        <div class="card">
          <h3>Sustainability Ambassadors</h3>
          <p>Early volunteers working on eco-travel, heritage protection, and community-led projects.</p>
        </div>
        <div class="card">
          <h3>Launch Event</h3>
          <p>Kindora officially launched in 2025 with a vision to inspire world exploration.</p>
        </div>
        <div class="card">
          <h3>Virtual Culture Exchange</h3>
          <p>Hosted our first online cultural session connecting travelers from different continents.</p>
        </div>
        <div class="card">
          <h3>Eco & Community Activities</h3>
          <p>Beginning small eco-drives and heritage awareness campaigns with local groups.</p>
        </div>
        <div class="card">
          <h3>Countries Featured</h3>
          <p>Within the first year, Kindora highlights major attractions from Asia, Europe, and Africa.</p>
        </div>
        <div class="card">
          <h3>Community Growth</h3>
          <p>Thousands of explorers inspired to travel responsibly since our launch.</p>
        </div>
        <div class="card">
          <h3>Future Vision</h3>
          <p>Expanding our global reach by adding more destinations, volunteer programs, and cultural events in upcoming years.</p>
        </div>
      </div>
    </section>

    <!-- Counter Section uses $stats -->
    <section style="background: #003366; padding: 80px 20px; font-family: 'Open Sans', sans-serif; color: #fff;">
      <div style="max-width: 1000px; margin: auto; text-align: center">
        <h2 style="font-size:36px;margin-bottom:50px;font-weight:bold;color:#ffcc00;">Our Achievements</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:40px;">
          <div style="background:#fff;color:#003366;padding:30px;border-radius:15px;box-shadow:0 6px 15px rgba(0,0,0,0.15);">
            <h3 style="font-size:40px;margin:0" class="counter" data-target="<?php echo (int)($stats['travelers'] ?? 0); ?>"><?php echo (int)($stats['travelers'] ?? 0); ?></h3>
            <p style="margin-top:10px;font-size:18px;font-weight:600">Happy Travelers</p>
          </div>
          <div style="background:#fff;color:#003366;padding:30px;border-radius:15px;box-shadow:0 6px 15px rgba(0,0,0,0.15);">
            <h3 style="font-size:40px;margin:0" class="counter" data-target="<?php echo (int)($stats['tours'] ?? 0); ?>"><?php echo (int)($stats['tours'] ?? 0); ?></h3>
            <p style="margin-top:10px;font-size:18px;font-weight:600">Tours Organized</p>
          </div>
          <div style="background:#fff;color:#003366;padding:30px;border-radius:15px;box-shadow:0 6px 15px rgba(0,0,0,0.15);">
            <h3 style="font-size:40px;margin:0" class="counter" data-target="<?php echo (int)($stats['reviews_count'] ?? 0); ?>"><?php echo (int)($stats['reviews_count'] ?? 0); ?></h3>
            <p style="margin-top:10px;font-size:18px;font-weight:600">Reviews</p>
          </div>
          <div style="background:#fff;color:#003366;padding:30px;border-radius:15px;box-shadow:0 6px 15px rgba(0,0,0,0.15);">
            <h3 style="font-size:40px;margin:0" class="counter" data-target="<?php echo (int)($stats['destinations'] ?? 0); ?>"><?php echo (int)($stats['destinations'] ?? 0); ?></h3>
            <p style="margin-top:10px;font-size:18px;font-weight:600">Destinations</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Reviews Section (DB-driven + add form preserved) -->
    <section style="background:#f9f9f9;padding:80px 20px;font-family:'Open Sans',sans-serif;">
      <div style="max-width:1000px;margin:auto">
        <h2 style="text-align:center;font-size:36px;margin-bottom:50px;font-weight:bold;color:#003366;">Traveler Reviews</h2>
        <div id="reviews-list" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:25px;margin-bottom:60px;">
          <?php if (!empty($reviews)): foreach ($reviews as $r): ?>
            <div style="background:#fff;padding:25px;border-radius:15px;box-shadow:0 6px 15px rgba(0,0,0,0.1);">
              <h4 style="color:#003366;margin-bottom:8px"><?php echo htmlspecialchars($r['name'] ?? 'Guest'); ?></h4>
              <p style="color:#ffcc00;font-size:20px"><?php echo str_repeat('★', max(0,(int)($r['rating'] ?? 0))) . str_repeat('☆', 5 - max(0,(int)($r['rating'] ?? 0))); ?></p>
              <p style="color:#333"><?php echo htmlspecialchars(substr($r['review_text'] ?? '', 0, 300)); ?></p>
            </div>
          <?php endforeach; else: ?>
            <!-- keep provided static examples if DB empty -->
            <div style="background:#fff;padding:25px;border-radius:15px;box-shadow:0 6px 15px rgba(0,0,0,0.1);">
              <h4 style="color:#003366;margin-bottom:8px">Aditi Sharma</h4>
              <p style="color:#ffcc00;font-size:20px">★★★★★</p>
              <p style="color:#333">An unforgettable experience! The Taj Mahal is truly magical at sunrise.</p>
            </div>
            <div style="background:#fff;padding:25px;border-radius:15px;box-shadow:0 6px 15px rgba(0,0,0,0.1);">
              <h4 style="color:#003366;margin-bottom:8px">Rahul Verma</h4>
              <p style="color:#ffcc00;font-size:20px">★★★★☆</p>
              <p style="color:#333">Well organized trip, smooth booking experience with Kindora!</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Add Review Form (kept original) -->
        <div style="background:#fff;padding:30px;border-radius:15px;box-shadow:0 6px 15px rgba(0,0,0,0.15);">
          <h3 style="color:#003366;margin-bottom:20px">Add Your Review</h3>
          <form id="reviewForm" style="display:flex;flex-direction:column;gap:15px">
            <input type="text" id="name" placeholder="Your Name" required style="padding:12px;border:1px solid #ccc;border-radius:8px;font-size:16px;" />
            <div id="starRating" style="font-size:28px;color:#ccc;cursor:pointer">
              <span data-value="1">★</span><span data-value="2">★</span><span data-value="3">★</span><span data-value="4">★</span><span data-value="5">★</span>
            </div>
            <textarea id="comment" rows="4" placeholder="Write your review..." required style="padding:12px;border:1px solid #ccc;border-radius:8px;font-size:16px;"></textarea>
            <button type="submit" style="padding:12px 25px;background:linear-gradient(135deg,#ffcc00,#ff9900);border:none;border-radius:8px;font-size:16px;font-weight:bold;color:#003366;cursor:pointer;transition:0.3s;">Submit Review</button>
          </form>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="faq-section">
      <h2>Frequently Asked Questions</h2>
      <div class="faq-container">
        <?php if (!empty($faqs)): foreach ($faqs as $faq): ?>
          <div class="faq-item">
            <button class="faq-question"><?php echo htmlspecialchars($faq['question']); ?></button>
            <div class="faq-answer"><p><?php echo htmlspecialchars($faq['answer']); ?></p></div>
          </div>
        <?php endforeach; else: ?>
          <div class="faq-item">
            <button class="faq-question">🌍 What is Kindora?</button>
            <div class="faq-answer"><p>Kindora is a non-profit tourism guide that helps you explore the world — focusing on culture, nature, food, and unique experiences.</p></div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <footer>
      <div class="footer-container">
        <div class="footer-left">
          <h3>Kindora</h3>
          <p>Your gateway to dream destinations around the globe.<br />Explore, travel, and create unforgettable memories with us.</p>
        </div>
        <div class="footer-middle">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="home.html">Home</a></li>
            <li><a href="#">Be Inspired</a></li>
            <li><a href="explore.html">Places to Go</a></li>
            <li><a href="things_to_do.html">Things to Do</a></li>
            <li><a href="../booking.php">Plan Your Trip</a></li>
            <li><a href="contactus.html">Contact</a></li>
          </ul>
        </div>
        <div class="footer-right">
          <h4>Stay Connected</h4>
          <form class="subscribe-form">
            <input type="email" placeholder="Enter your email" required />
            <button type="submit">Subscribe</button>
          </form>
          <h4>Follow Us</h4>
          <div class="social-icons">
            <a href="#"><img src="../icons/facebook.avif" alt="Facebook" /></a>
            <a href="#"><img src="../icons/instagram.avif" alt="Instagram" /></a>
            <a href="#"><img src="<?php echo htmlspecialchars(path_or_default('icon_twitter','../icons/twitter.avif')); ?>" alt="Twitter" /></a>
          </div>
        </div>
      </div>
      <div class="footer-bottom"><p>&copy; <?php echo date('Y'); ?> Kindora. All rights reserved.</p></div>
    </footer>

    

    <!-- Counter Script -->
    <script>
      const counters = document.querySelectorAll(".counter");
      const speed = 100; // lower = faster

      const animateCounters = () => {
        counters.forEach((counter) => {
          const updateCount = () => {
            const target = +counter.getAttribute("data-target");
            const count = +counter.innerText;
            const increment = Math.ceil(target / speed);
            if (count < target) {
              counter.innerText = count + increment;
              setTimeout(updateCount, 20);
            } else {
              counter.innerText = target;
            }
          };
          updateCount();
        });
      };

      const section = document.querySelector("section");
      let started = false;
      window.addEventListener("scroll", () => {
        const sectionTop = section.getBoundingClientRect().top;
        if (sectionTop < window.innerHeight && !started) {
          animateCounters();
          started = true;
        }
      });
    </script>

    <!-- Review Script -->
    <script>
      let selectedRating = 0;
      const stars = document.querySelectorAll("#starRating span");

      stars.forEach((star) => {
        star.addEventListener("click", () => {
          selectedRating = star.getAttribute("data-value");
          stars.forEach((s) => {
            if (s.getAttribute("data-value") <= selectedRating) {
              s.style.color = "#ffcc00";
            } else {
              s.style.color = "#ccc";
            }
          });
        });
      });

      const form = document.getElementById("reviewForm");
      const reviewsList = document.getElementById("reviews-list");

      form.addEventListener("submit", function (e) {
        e.preventDefault();
        const name = document.getElementById("name").value;
        const comment = document.getElementById("comment").value;
        if (selectedRating === 0) {
          alert("Please select a star rating!");
          return;
        }
        const starsDisplay = "★★★★★".slice(0, selectedRating) + "☆☆☆☆☆".slice(0, 5 - selectedRating);
        const reviewCard = document.createElement("div");
        reviewCard.style.background = "#fff";
        reviewCard.style.padding = "25px";
        reviewCard.style.borderRadius = "15px";
        reviewCard.style.boxShadow = "0 6px 15px rgba(0,0,0,0.1)";
        reviewCard.innerHTML = `
      <h4 style="color:#003366; margin-bottom:8px;">${name}</h4>
      <p style="color:#ffcc00; font-size:20px;">${starsDisplay}</p>
      <p style="color:#333;">${comment}</p>
    `;
        reviewsList.prepend(reviewCard);
        form.reset();
        selectedRating = 0;
        stars.forEach((s) => (s.style.color = "#ccc"));
      });
    </script>

    <script src="<?php echo htmlspecialchars(path_or_default('js_common','../assets/js/common_nav_footer.js')); ?>"></script>
    <script>
      document.querySelectorAll(".scroll-container1 a").forEach((a) => {
        a.addEventListener("dblclick", (e) => {
          const href = a.getAttribute("data-href");
          if (href) window.location.href = href;
        });
        a.addEventListener("click", (e) => { e.preventDefault(); });
      });

      function toggleMenu() {
        document.getElementById("sidebar").classList.toggle("open");
      }
      document.querySelector(".cta-btn").addEventListener("click", () => {
        document.getElementById("popular").scrollIntoView({ behavior: "smooth" });
      });
    </script>

    <script>
      // FAQ accordion
      const faqQuestions = document.querySelectorAll(".faq-question");
      faqQuestions.forEach((btn) => {
        btn.addEventListener("click", () => {
          faqQuestions.forEach((q) => {
            if (q !== btn) {
              q.classList.remove("active");
              q.nextElementSibling.classList.remove("open");
            }
          });
          btn.classList.toggle("active");
          btn.nextElementSibling.classList.toggle("open");
        });
      });

      let slideIndex = 0;
      showSlides();
      function showSlides() {
        let i;
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("dot");
        for (i = 0; i < slides.length; i++) slides[i].style.display = "none";
        slideIndex++;
        if (slideIndex > slides.length) slideIndex = 1;
        for (i = 0; i < dots.length; i++) dots[i].className = dots[i].className.replace(" active", "");
        if (slides.length) {
          slides[slideIndex - 1].style.display = "block";
          if (dots.length) dots[slideIndex - 1].className += " active";
        }
        setTimeout(showSlides, 3000);
      }
    </script>
  </body>
</html>
