<?php
session_start();
require_once 'connection.php';

// Get featured destinations
try {
    $featured_stmt = $conn->query("
        SELECT d.*, COUNT(b.booking_id) as booking_count,
               AVG(r.rating) as avg_rating,
               COUNT(r.review_id) as review_count
        FROM destinations d 
        LEFT JOIN bookings b ON d.destination_id = b.destination_id 
        LEFT JOIN reviews r ON d.destination_id = r.destination_id AND r.status = 'approved'
        WHERE d.is_active = 1 AND d.featured = 1 AND d.type != 'temp'
        GROUP BY d.destination_id
        ORDER BY d.name
        LIMIT 12
    ");
    $featured_destinations = $featured_stmt ? $featured_stmt->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    error_log("Error fetching featured destinations: " . $e->getMessage());
    $featured_destinations = [];
}

// Get destinations by continent
$continents = ['asia', 'europe', 'africa', 'north_america', 'south_america', 'australia', 'antarctica'];
$continent_destinations = [];

foreach ($continents as $continent) {
    try {
        $continent_stmt = $conn->prepare("
            SELECT d.*, COUNT(b.booking_id) as booking_count,
                   AVG(r.rating) as avg_rating
            FROM destinations d 
            LEFT JOIN bookings b ON d.destination_id = b.destination_id 
            LEFT JOIN reviews r ON d.destination_id = r.destination_id AND r.status = 'approved'
            WHERE d.type = ? AND d.is_active = 1 AND d.name != 'My Trip Planner'
            GROUP BY d.destination_id
            ORDER BY booking_count DESC, avg_rating DESC
            LIMIT 6
        ");
        $continent_stmt->bind_param("s", $continent);
        $continent_stmt->execute();
        $result = $continent_stmt->get_result();
        $continent_destinations[$continent] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $continent_stmt->close();
    } catch (Exception $e) {
        error_log("Error fetching $continent destinations: " . $e->getMessage());
        $continent_destinations[$continent] = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Destinations - Kindora</title>
    <link href="explore.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    <link href="common_nav_footer.css" rel="stylesheet">
    <meta name="description" content="Explore amazing destinations worldwide with Kindora - Find your perfect travel experience">
</head>
<body>
    <!-- Navigation -->
    <div id="nav1">
        <a href="home.php" id="logo-link">
            <div id="logo">Kindora</div>
        </a>
        <div id="nav2">
            <a class="a1 dropbtn" href="home.php">Home</a>
            <a class="a1 dropbtn active" href="explore.php">Explore</a>
            <a class="a1 dropbtn" href="booking.php">Book</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="a1 dropbtn" href="mytrips.php">My Trips</a>
            <?php else: ?>
                <a class="a1 dropbtn" href="login.php">Login</a>
            <?php endif; ?>
            <a class="a1 dropbtn" href="contactus.php">Contact</a>
        </div>
        
        <button class="menu" onclick="toggleMenu()">☰</button>
        <div id="sidebar" class="sidebar">
            <button class="closebtn" onclick="toggleMenu()">×</button>
            <a href="home.php">Home</a>
            <a href="explore.php" class="active">Explore</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="mytrips.php">My Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login/Register</a>
            <?php endif; ?>
            <a href="booking.php">Book Your Trips</a>
            <a href="aboutus.php">About</a>
            <a href="contactus.php">Contact</a>
        </div>
    </div>

    <main>
        <!-- Hero Section -->
        <section class="explore-hero">
            <div class="hero-background">
                <img src="web_images/explore/hero-bg.avif" alt="Explore the World" loading="eager"
                     onerror="this.src='web_images/explore/default-hero.avif'">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Explore the World</h1>
                        <p>Discover extraordinary destinations across all seven continents</p>
                        
                        <!-- Search Bar -->
                        <div class="search-container">
                            <form action="search.php" method="get" class="search-form">
                                <div class="search-input-group">
                                    <input type="text" name="q" placeholder="Search destinations, cities, or activities..." 
                                           class="search-input" autocomplete="off">
                                    <button type="submit" class="search-btn">🔍 Search</button>
                                </div>
                                <div class="search-filters">
                                    <select name="continent" class="filter-select">
                                        <option value="">All Continents</option>
                                        <option value="asia">Asia</option>
                                        <option value="europe">Europe</option>
                                        <option value="africa">Africa</option>
                                        <option value="north_america">North America</option>
                                        <option value="south_america">South America</option>
                                        <option value="australia">Australia & Oceania</option>
                                        <option value="antarctica">Antarctica</option>
                                    </select>
                                    <select name="budget" class="filter-select">
                                        <option value="">Any Budget</option>
                                        <option value="budget">Budget ($0-$1000)</option>
                                        <option value="mid">Mid-range ($1000-$3000)</option>
                                        <option value="luxury">Luxury ($3000+)</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Destinations -->
        <section class="featured-destinations">
            <div class="container">
                <div class="section-header">
                    <h2>✨ Featured Destinations</h2>
                    <p>Handpicked experiences for unforgettable journeys</p>
                </div>
                
                <div class="featured-grid">
                    <?php foreach (array_slice($featured_destinations, 0, 8) as $destination): ?>
                        <div class="featured-card" onclick="window.location.href='booking.php?destination=<?= $destination['destination_id'] ?>'">
                            <div class="card-image">
                                <img src="<?= str_replace('\\\\', '/', htmlspecialchars($destination['image_url'])) ?>" 
                                     alt="<?= htmlspecialchars($destination['name']) ?>" loading="lazy"
                                     onerror="this.src='images/default-destination.avif'">
                                <div class="card-overlay">
                                    <div class="overlay-content">
                                        <h3><?= htmlspecialchars($destination['name']) ?></h3>
                                        <p><?= ucfirst($destination['type']) ?></p>
                                        <?php if ($destination['avg_rating'] > 0): ?>
                                            <div class="rating">
                                                <span>⭐ <?= number_format($destination['avg_rating'], 1) ?></span>
                                                <span>(<?= $destination['review_count'] ?> reviews)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Explore by Continent -->
        <section class="explore-continents">
            <div class="container">
                <div class="section-header">
                    <h2>🌍 Explore by Continent</h2>
                    <p>Journey across diverse landscapes and cultures</p>
                </div>

                <!-- Asia -->
                <div class="continent-section" id="asia-section">
                    <div class="continent-header">
                        <h3>
                            <a href="asia.php" class="continent-link">
                                🏮 Asia - Where Tradition Meets Innovation
                                <span class="view-all">View All →</span>
                            </a>
                        </h3>
                    </div>
                    <div class="continent-grid">
                        <?php foreach ($continent_destinations['asia'] as $destination): ?>
                            <div class="destination-card" onclick="window.location.href='booking.php?destination=<?= $destination['destination_id'] ?>'">
                                <div class="card-image">
                                    <img src="<?= str_replace('\\\\', '/', htmlspecialchars($destination['image_url'])) ?>" 
                                         alt="<?= htmlspecialchars($destination['name']) ?>" loading="lazy">
                                </div>
                                <div class="card-content">
                                    <h4><?= htmlspecialchars($destination['name']) ?></h4>
                                    <?php if ($destination['avg_rating'] > 0): ?>
                                        <div class="rating">⭐ <?= number_format($destination['avg_rating'], 1) ?></div>
                                    <?php endif; ?>
                                    <?php if ($destination['booking_count'] > 0): ?>
                                        <p class="booking-count"><?= $destination['booking_count'] ?> travelers</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Europe -->
                <div class="continent-section" id="europe-section">
                    <div class="continent-header">
                        <h3>
                            <a href="europe.php" class="continent-link">
                                🏰 Europe - History & Culture
                                <span class="view-all">View All →</span>
                            </a>
                        </h3>
                    </div>
                    <div class="continent-grid">
                        <?php foreach ($continent_destinations['europe'] as $destination): ?>
                            <div class="destination-card" onclick="window.location.href='booking.php?destination=<?= $destination['destination_id'] ?>'">
                                <div class="card-image">
                                    <img src="<?= str_replace('\\\\', '/', htmlspecialchars($destination['image_url'])) ?>" 
                                         alt="<?= htmlspecialchars($destination['name']) ?>" loading="lazy">
                                </div>
                                <div class="card-content">
                                    <h4><?= htmlspecialchars($destination['name']) ?></h4>
                                    <?php if ($destination['avg_rating'] > 0): ?>
                                        <div class="rating">⭐ <?= number_format($destination['avg_rating'], 1) ?></div>
                                    <?php endif; ?>
                                    <?php if ($destination['booking_count'] > 0): ?>
                                        <p class="booking-count"><?= $destination['booking_count'] ?> travelers</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Africa -->
                <div class="continent-section" id="africa-section">
                    <div class="continent-header">
                        <h3>
                            <a href="africa.php" class="continent-link">
                                🦁 Africa - Wild & Wonderful
                                <span class="view-all">View All →</span>
                            </a>
                        </h3>
                    </div>
                    <div class="continent-grid">
                        <?php foreach ($continent_destinations['africa'] as $destination): ?>
                            <div class="destination-card" onclick="window.location.href='booking.php?destination=<?= $destination['destination_id'] ?>'">
                                <div class="card-image">
                                    <img src="<?= str_replace('\\\\', '/', htmlspecialchars($destination['image_url'])) ?>" 
                                         alt="<?= htmlspecialchars($destination['name']) ?>" loading="lazy">
                                </div>
                                <div class="card-content">
                                    <h4><?= htmlspecialchars($destination['name']) ?></h4>
                                    <?php if ($destination['avg_rating'] > 0): ?>
                                        <div class="rating">⭐ <?= number_format($destination['avg_rating'], 1) ?></div>
                                    <?php endif; ?>
                                    <?php if ($destination['booking_count'] > 0): ?>
                                        <p class="booking-count"><?= $destination['booking_count'] ?> adventurers</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- More continents with similar structure -->
                <?php foreach (['north_america', 'south_america', 'australia', 'antarctica'] as $continent): ?>
                    <?php if (!empty($continent_destinations[$continent])): ?>
                        <div class="continent-section">
                            <div class="continent-header">
                                <h3>
                                    <a href="<?= str_replace('_', '_', $continent) ?>.php" class="continent-link">
                                        <?php
                                        $continent_info = [
                                            'north_america' => ['🗽', 'North America - Land of Opportunity'],
                                            'south_america' => ['🏔️', 'South America - Ancient Mysteries'],
                                            'australia' => ['🦘', 'Australia & Oceania - Down Under'],
                                            'antarctica' => ['🐧', 'Antarctica - The Last Frontier']
                                        ];
                                        echo $continent_info[$continent][0] . ' ' . $continent_info[$continent][1];
                                        ?>
                                        <span class="view-all">View All →</span>
                                    </a>
                                </h3>
                            </div>
                            <div class="continent-grid">
                                <?php foreach ($continent_destinations[$continent] as $destination): ?>
                                    <div class="destination-card" onclick="window.location.href='booking.php?destination=<?= $destination['destination_id'] ?>'">
                                        <div class="card-image">
                                            <img src="<?= str_replace('\\\\', '/', htmlspecialchars($destination['image_url'])) ?>" 
                                                 alt="<?= htmlspecialchars($destination['name']) ?>" loading="lazy">
                                        </div>
                                        <div class="card-content">
                                            <h4><?= htmlspecialchars($destination['name']) ?></h4>
                                            <?php if ($destination['avg_rating'] > 0): ?>
                                                <div class="rating">⭐ <?= number_format($destination['avg_rating'], 1) ?></div>
                                            <?php endif; ?>
                                            <?php if ($destination['booking_count'] > 0): ?>
                                                <p class="booking-count"><?= $destination['booking_count'] ?> travelers</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="explore-cta">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Start Your Adventure?</h2>
                    <p>Join thousands of travelers who have discovered their dream destinations with Kindora</p>
                    <div class="cta-buttons">
                        <a href="booking.php" class="btn-primary">Plan Your Trip</a>
                        <a href="contactus.php" class="btn-secondary">Talk to Expert</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>

    <script src="common_nav_footer.js"></script>
    <script>
        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.querySelector('.search-form');
            const searchInput = document.querySelector('.search-input');
            
            searchForm.addEventListener('submit', function(e) {
                if (!searchInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter a search term');
                    searchInput.focus();
                }
            });
        });
    </script>
</body>
</html>
