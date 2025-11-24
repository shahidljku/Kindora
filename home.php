<?php
session_start();
require_once 'connection.php';

// Get popular destinations from database
try {
    $popular_destinations = $conn->query("
        SELECT d.*, COUNT(b.booking_id) as booking_count 
        FROM destinations d 
        LEFT JOIN bookings b ON d.destination_id = b.destination_id 
        WHERE d.type != 'temp' AND d.type != 'Continent'
        GROUP BY d.destination_id 
        ORDER BY booking_count DESC, d.name 
        LIMIT 10
    ");
    $popular_places = $popular_destinations ? $popular_destinations->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    error_log("Error fetching popular destinations: " . $e->getMessage());
    $popular_places = [];
}

// Get 7_wonders from database
try {
    $seven_wonders_result = $conn->query("
        SELECT * FROM destinations 
        WHERE type = '7_wonders' 
        ORDER BY name
    ");
    $seven_wonders = $seven_wonders_result ? $seven_wonders_result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    error_log("Error fetching seven wonders: " . $e->getMessage());
    $seven_wonders = [];
}

// Get continent data
try {
    $continents_result = $conn->query("
        SELECT * FROM destinations 
        WHERE type = 'Continent' 
        ORDER BY name
    ");
    $continents = $continents_result ? $continents_result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    error_log("Error fetching continents: " . $e->getMessage());
    $continents = [];
}

// Update page views
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO page_views (user_id, page_slug, viewed_at) VALUES (?, 'home', NOW())");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error tracking page view: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kindora - Your Travel Companion</title>
    <link href="homepage.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    <link href="common_nav_footer.css" rel="stylesheet">
    <meta name="description" content="Explore the world with Kindora - Your gateway to extraordinary adventures across all continents">
    <meta name="keywords" content="travel, destinations, booking, adventure, world travel, tourism">
</head>
<body>
    <!-- Background Video -->
    <div class="video-container">
        <video autoplay muted loop id="bg-video" preload="metadata">
            <source src="video/homepage-intro.webm" type="video/webm">
            <source src="video/homepage-intro.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay">
            <h1 class="main-title">Explore the World with Kindora</h1>
            <p class="main-subtitle">Your gateway to extraordinary adventures</p>
            <a href="explore.php" class="cta-button" aria-label="Start exploring destinations">Start Exploring</a>
        </div>
    </div>

    <!-- Navigation -->
    <div id="nav1">
        <a href="home.php" id="logo-link" aria-label="Kindora Home">
            <div id="logo">Kindora</div>
        </a>

        <div id="nav2">
            <div class="inspire-wrapper">
                <a class="a1 dropbtn" id="inspireBtn" href="#" aria-label="Be Inspired - Explore continents">Be Inspired</a>
                <div class="scroll-container1" id="inspireScroll" role="menu">
                    <?php foreach ($continents as $continent): ?>
                    <a data-href="<?= strtolower(str_replace([' ', '_'], '_', $continent['name'])) ?>.php" role="menuitem" 
                       aria-label="Explore <?= htmlspecialchars($continent['name']) ?>">
                        <div class="image-wrapper1">
                            <img src="<?= str_replace('\\\\', '/', htmlspecialchars($continent['image_url'])) ?>" 
                                 alt="<?= htmlspecialchars($continent['name']) ?>" 
                                 loading="lazy"
                                 onerror="this.src='web_images/banner/default.avif'">
                            <div class="overlay-text"><?= htmlspecialchars($continent['name']) ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a class="a1 dropbtn" href="explore.php">Places to go</a>
            <a class="a1 dropbtn" href="things_to_do.php">Things to do</a>
            <a class="a1 dropbtn" href="booking.php">Plan Your Trip</a>
        </div>

        <button class="menu" onclick="toggleMenu()" aria-label="Toggle mobile menu">☰</button>
        <div id="sidebar" class="sidebar" role="navigation">
            <button class="closebtn" onclick="toggleMenu()" aria-label="Close menu">×</button>
            <a href="home.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="mytrips.php">My Dashboard</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login/Register</a>
            <?php endif; ?>
            <a href="booking.php">Book Your Trips</a>
            <a href="aboutus.php">About</a>
            <a href="contactus.php">Contact</a>
        </div>
    </div>

    <!-- Popular Places Section -->
    <div id="popular">
        <h1 class="titletext">Top 10 Most Popular Places</h1>
        <div class="scroll-container" role="list">
            <?php if (!empty($popular_places)): ?>
                <?php foreach ($popular_places as $place): ?>
                <a href="<?= strtolower(str_replace([' ', '_'], '_', $place['type'])) ?>.php#dest-<?= $place['destination_id'] ?>" 
                   role="listitem" aria-label="Visit <?= htmlspecialchars($place['name']) ?>">
                    <div class="image-wrapper">
                        <img src="<?= str_replace('\\\\', '/', htmlspecialchars($place['image_url'])) ?>" 
                             alt="<?= htmlspecialchars($place['name']) ?>" 
                             loading="lazy"
                             onerror="this.src='continents/images/default.avif'">
                        <div class="overlay-text">
                            <?= htmlspecialchars($place['name']) ?>
                            <?php if ($place['booking_count'] > 0): ?>
                                <div class="popularity">🔥 <?= $place['booking_count'] ?> bookings</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">No popular destinations available at the moment.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 7_Wonders Section -->
    <div id="7_wonders">
        <h1 class="titletext">7 Wonders of the World</h1>
        <div class="scroll-container" role="list">
            <?php if (!empty($seven_wonders)): ?>
                <?php foreach ($seven_wonders as $wonder): ?>
                <a href="7_wonders/<?= str_replace(' ', '_', $wonder['name']) ?>.php" 
                   role="listitem" aria-label="Visit <?= htmlspecialchars($wonder['name']) ?>">
                    <div class="image-wrapper">
                        <img src="<?= str_replace('\\\\', '/', htmlspecialchars($wonder['image_url'])) ?>" 
                             alt="<?= htmlspecialchars($wonder['name']) ?>" 
                             loading="lazy"
                             onerror="this.src='7_wonders/default.avif'">
                        <div class="overlay-text"><?= htmlspecialchars($wonder['name']) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">7 Wonders information coming soon.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Packages Section -->
    <div id="packages">
        <h1 class="titletext">Our Travel Packages</h1>
        <div class="package-grid">
            <?php
            try {
                $packages_result = $conn->query("
                    SELECT p.*, d.name as destination_name, d.image_url 
                    FROM packages p 
                    JOIN destinations d ON p.destination_id = d.destination_id 
                    WHERE p.is_active = 1 
                    ORDER BY p.price ASC 
                    LIMIT 6
                ");
                $packages = $packages_result ? $packages_result->fetch_all(MYSQLI_ASSOC) : [];
            } catch (Exception $e) {
                error_log("Error fetching packages: " . $e->getMessage());
                $packages = [];
            }
            
            if (!empty($packages)):
                foreach ($packages as $package):
            ?>
                <div class="package-card">
                    <img src="<?= str_replace('\\\\', '/', htmlspecialchars($package['image_url'])) ?>" 
                         alt="<?= htmlspecialchars($package['destination_name']) ?>" 
                         loading="lazy"
                         onerror="this.src='images/package-default.avif'">
                    <div class="package-info">
                        <h3><?= htmlspecialchars($package['title']) ?></h3>
                        <p><?= htmlspecialchars($package['description']) ?></p>
                        <div class="package-price">From $<?= number_format($package['price']) ?></div>
                        <a href="booking.php?package=<?= $package['package_id'] ?>" class="package-btn">Book Now</a>
                    </div>
                </div>
            <?php 
                endforeach;
            else:
            ?>
                <div class="no-packages">Travel packages coming soon!</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-left">
                <h3>Kindora</h3>
                <p>Your gateway to dream destinations around the globe.<br>
                   Explore, travel, and create unforgettable memories with us.</p>
            </div>
            <div class="footer-middle">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="#" onclick="document.getElementById('inspireBtn').click()">Be Inspired</a></li>
                    <li><a href="explore.php">Places to Go</a></li>
                    <li><a href="things_to_do.php">Things to Do</a></li>
                    <li><a href="booking.php">Plan Your Trip</a></li>
                    <li><a href="contactus.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-right">
                <h4>Stay Connected</h4>
                <form class="subscribe-form" action="subscribe.php" method="post">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                    <?php if (isset($_SESSION['csrf_token'])): ?>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <?php endif; ?>
                </form>
                <h4>Follow Us</h4>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><img src="icons/facebook.jpeg" alt="Facebook"></a>
                    <a href="#" aria-label="Instagram"><img src="icons/instagram.jpeg" alt="Instagram"></a>
                    <a href="#" aria-label="Twitter"><img src="icons/twitter.avif" alt="Twitter"></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Kindora. All rights reserved.</p>
        </div>
    </footer>

    <script src="common_nav_footer.js"></script>
    <script>
        // Preload critical images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img[loading="lazy"]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src || img.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            images.forEach(img => imageObserver.observe(img));
        });

        // Error handling for failed video loads
        document.getElementById('bg-video').addEventListener('error', function() {
            this.style.display = 'none';
            document.querySelector('.video-overlay').style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        });
    </script>
</body>
</html>
