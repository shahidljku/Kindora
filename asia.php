<?php

require_once 'connection.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get all Asia destinations from database
try {
    $asia_destinations_result = $conn->query("
        SELECT d.*, COUNT(b.booking_id) as booking_count,
               AVG(r.rating) as avg_rating,
               COUNT(r.review_id) as review_count
        FROM destinations d 
        LEFT JOIN bookings b ON d.destination_id = b.destination_id 
        LEFT JOIN reviews r ON d.destination_id = r.destination_id AND r.status = 'approved'
        WHERE d.type = 'asia' AND d.name != 'My Trip Planner'
        GROUP BY d.destination_id
        ORDER BY d.name
    ");
    $asia_destinations = $asia_destinations_result ? $asia_destinations_result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    error_log("Error fetching Asia destinations: " . $e->getMessage());
    $asia_destinations = [];
}

$message = '';

// Handle wishlist actions
if ($_POST && isset($_POST['action']) && isset($_SESSION['user_id'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid request. Please try again.";
    } else {
        if ($_POST['action'] === 'add_to_wishlist') {
            $dest_id = filter_var($_POST['destination_id'], FILTER_VALIDATE_INT);
            $user_id = $_SESSION['user_id'];
            
            if ($dest_id) {
                try {
                    // Check if already in wishlist
                    $check_stmt = $conn->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = ? AND destination_id = ?");
                    $check_stmt->bind_param("ii", $user_id, $dest_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows === 0) {
                        $insert_stmt = $conn->prepare("INSERT INTO wishlists (user_id, destination_id, created_at) VALUES (?, ?, NOW())");
                        $insert_stmt->bind_param("ii", $user_id, $dest_id);
                        
                        if ($insert_stmt->execute()) {
                            $message = "✅ Added to wishlist!";
                        } else {
                            $message = "❌ Failed to add to wishlist. Please try again.";
                        }
                        $insert_stmt->close();
                    } else {
                        $message = "ℹ️ Already in your wishlist!";
                    }
                    $check_stmt->close();
                } catch (Exception $e) {
                    error_log("Error adding to wishlist: " . $e->getMessage());
                    $message = "❌ An error occurred. Please try again.";
                }
            } else {
                $message = "❌ Invalid destination selected.";
            }
        }
    }
    
    // Regenerate CSRF token after use
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Track page view
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO page_views (user_id, page_slug, viewed_at) VALUES (?, 'asia', NOW())");
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
    <title>Asia Destinations - Kindora</title>
    <link href="asia.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    <link href="common_nav_footer.css" rel="stylesheet">
    <meta name="description" content="Explore breathtaking destinations across Asia with Kindora - From ancient temples to modern cities">
    <meta name="keywords" content="Asia travel, Asian destinations, Asia tours, travel Asia, Asian countries">
</head>
<body>
    <!-- Background Video -->
    <div class="video-container">
        <video autoplay muted loop id="bg-video" preload="metadata">
            <source src="continents_videos/asia_intro.webm" type="video/webm">
            <source src="continents_videos/asia_intro.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay">
            <h1>Discover Asia</h1>
            <p>Ancient wisdom meets modern marvels</p>
        </div>
    </div>

    <!-- Navigation -->
    <div id="nav1">
        <a href="home.php" id="logo-link" aria-label="Kindora Home">
            <div id="logo">Kindora</div>
        </a>

        <div id="nav2">
            <div class="inspire-wrapper">
                <a class="a1 dropbtn" id="inspireBtn" href="#" aria-label="Be Inspired">Be Inspired</a>
                <div class="scroll-container1" id="inspireScroll" role="menu">
                    <a data-href="asia.php" class="active" role="menuitem">
                        <div class="image-wrapper1">
                            <img src="web_images/banner/asia.avif" alt="Asia" loading="lazy">
                            <div class="overlay-text">Asia</div>
                        </div>
                    </a>
                    <a data-href="europe.php" role="menuitem">
                        <div class="image-wrapper1">
                            <img src="web_images/banner/europe.jpeg" alt="Europe" loading="lazy">
                            <div class="overlay-text">Europe</div>
                        </div>
                    </a>
                    <a data-href="africa.php" role="menuitem">
                        <div class="image-wrapper1">
                            <img src="web_images/banner/africa.avif" alt="Africa" loading="lazy">
                            <div class="overlay-text">Africa</div>
                        </div>
                    </a>
                    <a data-href="north_america.php" role="menuitem">
                        <div class="image-wrapper1">
                            <img src="web_images/banner/north_america.avif" alt="North America" loading="lazy">
                            <div class="overlay-text">North America</div>
                        </div>
                    </a>
                    <a data-href="south_america.php" role="menuitem">
                        <div class="image-wrapper1">
                            <img src="7_wonders/Machu_Picchu.jpeg" alt="South America" loading="lazy">
                            <div class="overlay-text">South America</div>
                        </div>
                    </a>
                    <a data-href="australia.php" role="menuitem">
                        <div class="image-wrapper1">
                            <img src="web_images/banner/australia.avif" alt="Australia" loading="lazy">
                            <div class="overlay-text">Australia</div>
                        </div>
                    </a>
                    <a data-href="antarctica.php" role="menuitem">
                        <div class="image-wrapper1">
                            <img src="web_images/banner/antarctica.jpeg" alt="Antarctica" loading="lazy">
                            <div class="overlay-text">Antarctica</div>
                        </div>
                    </a>
                </div>
            </div>
            <a class="a1 dropbtn" href="explore.php">Places to go</a>
            <a class="a1 dropbtn" href="things_to_do.php">Things to do</a>
            <a class="a1 dropbtn" href="booking.php">Plan Your Trip</a>
        </div>

        <button class="menu" onclick="toggleMenu()" aria-label="Toggle menu">☰</button>
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

    <main>
        <br><br><br><br>

        <!-- Message Display -->
        <?php if ($message): ?>
            <div class="message-popup <?= strpos($message, '❌') !== false ? 'error' : 'success' ?>" 
                 role="alert" aria-live="polite">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Search and Filter Section -->
        <div class="filter-section">
            <div class="container">
                <div class="search-bar">
                    <input type="text" id="destination-search" placeholder="Search Asia destinations..." 
                           aria-label="Search destinations">
                    <button type="button" onclick="searchDestinations()" aria-label="Search">🔍</button>
                </div>
                <div class="filter-options">
                    <select id="rating-filter" aria-label="Filter by rating">
                        <option value="">All Ratings</option>
                        <option value="4.5">4.5+ Stars</option>
                        <option value="4.0">4.0+ Stars</option>
                        <option value="3.5">3.5+ Stars</option>
                    </select>
                    <select id="popularity-filter" aria-label="Filter by popularity">
                        <option value="">All Destinations</option>
                        <option value="popular">Most Popular</option>
                        <option value="new">Trending</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Destinations Statistics -->
        <div class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?= count($asia_destinations) ?></span>
                        <span class="stat-label">Destinations</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?= array_sum(array_column($asia_destinations, 'booking_count')) ?>
                        </span>
                        <span class="stat-label">Happy Travelers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?= number_format(array_sum(array_column($asia_destinations, 'avg_rating')) / max(count($asia_destinations), 1), 1) ?>
                        </span>
                        <span class="stat-label">Avg Rating</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Grid -->
        <div class="container">
            <div class="card-grid" id="destinations-grid">
                <?php if (!empty($asia_destinations)): ?>
                    <?php foreach ($asia_destinations as $destination): ?>
                        <div class="video-card destination-card" 
                             data-dest-id="<?= $destination['destination_id'] ?>" 
                             data-rating="<?= $destination['avg_rating'] ?>"
                             data-popularity="<?= $destination['booking_count'] ?>"
                             id="dest-<?= $destination['destination_id'] ?>">
                             
                            <div class="card-image">
                                <img src="<?= str_replace('\\\\', '/', htmlspecialchars($destination['image_url'])) ?>" 
                                     alt="<?= htmlspecialchars($destination['name']) ?>" 
                                     loading="lazy"
                                     onerror="this.src='continents/images/asia_images/default.avif'">
                                     
                                <?php if ($destination['booking_count'] > 10): ?>
                                    <div class="popular-badge">🔥 Popular</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-overlay">
                                <div class="card-header">
                                    <h3><?= htmlspecialchars($destination['name']) ?></h3>
                                    <?php if ($destination['avg_rating'] > 0): ?>
                                        <div class="rating">
                                            <span class="stars">⭐</span>
                                            <span><?= number_format($destination['avg_rating'], 1) ?></span>
                                            <span class="reviews">(<?= $destination['review_count'] ?>)</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="card-description">
                                    <?= htmlspecialchars(substr($destination['description'], 0, 120)) ?><?= strlen($destination['description']) > 120 ? '...' : '' ?>
                                </p>
                                
                                <?php if ($destination['booking_count'] > 0): ?>
                                    <div class="booking-info">
                                        <span class="booking-count">👥 <?= $destination['booking_count'] ?> travelers</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-actions">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form method="post" style="display: inline;" onsubmit="return addToWishlist(this)">
                                            <input type="hidden" name="action" value="add_to_wishlist">
                                            <input type="hidden" name="destination_id" value="<?= $destination['destination_id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" class="save-btn" aria-label="Add to wishlist">
                                                💖 Save
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <button class="view-btn" onclick="showDetails(<?= $destination['destination_id'] ?>)" 
                                            aria-label="View details for <?= htmlspecialchars($destination['name']) ?>">
                                        👁️ View
                                    </button>
                                    
                                    <a href="booking.php?destination=<?= $destination['destination_id'] ?>" 
                                       class="book-btn" aria-label="Book trip to <?= htmlspecialchars($destination['name']) ?>">
                                        🎫 Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-destinations">
                        <h3>No destinations available</h3>
                        <p>We're working on adding amazing Asian destinations. Check back soon!</p>
                    </div>
                <?php endif; ?>

                <!-- Trip Planner Card -->
                <div class="video-card trip-planner-card" onclick="showTripPlanner()">
                    <div class="card-image">
                        <img src="continents/images/asia_images/28th_card.avif" alt="My Trip Planner" loading="lazy">
                    </div>
                    <div class="card-overlay">
                        <h3>My Trip Planner</h3>
                        <p>Review your selected destinations and plan your perfect Asian adventure.</p>
                        <div class="card-actions">
                            <button class="view-btn">📋 Open Planner</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Destination Details</h2>
                <span class="close" onclick="closeDetails()" aria-label="Close modal">&times;</span>
            </div>
            <div id="modalContent" class="modal-body">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>
    <script>
        // Search and filter functionality
        function searchDestinations() {
            const searchTerm = document.getElementById('destination-search').value.toLowerCase();
            const ratingFilter = document.getElementById('rating-filter').value;
            const popularityFilter = document.getElementById('popularity-filter').value;
            
            const cards = document.querySelectorAll('.destination-card');
            
            cards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('.card-description').textContent.toLowerCase();
                const rating = parseFloat(card.dataset.rating) || 0;
                const popularity = parseInt(card.dataset.popularity) || 0;
                
                let showCard = true;
                
                // Text search
                if (searchTerm && !name.includes(searchTerm) && !description.includes(searchTerm)) {
                    showCard = false;
                }
                
                // Rating filter
                if (ratingFilter && rating < parseFloat(ratingFilter)) {
                    showCard = false;
                }
                
                // Popularity filter
                if (popularityFilter === 'popular' && popularity < 10) {
                    showCard = false;
                } else if (popularityFilter === 'new' && popularity > 5) {
                    showCard = false;
                }
                
                card.style.display = showCard ? 'block' : 'none';
            });
        }

        // Add event listeners for real-time filtering
        document.getElementById('destination-search').addEventListener('input', searchDestinations);
        document.getElementById('rating-filter').addEventListener('change', searchDestinations);
        document.getElementById('popularity-filter').addEventListener('change', searchDestinations);

        // Show destination details
        function showDetails(destId) {
            fetch(`get_destination_details.php?id=${destId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('modalContent').innerHTML = `
                        <div class="destination-details">
                            <img src="${data.image_url.replace(/\\\\/g, '/')}" 
                                 alt="${data.name}" 
                                 style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px; margin-bottom: 16px;"
                                 onerror="this.src='continents/images/asia_images/default.avif'">
                            <h3>${data.name}</h3>
                            <p><strong>Description:</strong> ${data.description}</p>
                            <div class="modal-actions" style="margin-top: 20px;">
                                <a href="booking.php?destination=${data.destination_id}" 
                                   class="book-btn" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                                   Book Now
                                </a>
                            </div>
                        </div>
                    `;
                    document.getElementById('detailsModal').style.display = 'block';
                    document.getElementById('detailsModal').setAttribute('aria-hidden', 'false');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load destination details. Please try again.');
                });
        }

        // Close modal
        function closeDetails() {
            document.getElementById('detailsModal').style.display = 'none';
            document.getElementById('detailsModal').setAttribute('aria-hidden', 'true');
        }

        // Trip planner
        function showTripPlanner() {
            <?php if (isset($_SESSION['user_id'])): ?>
                window.location.href = 'trip_planner.php';
            <?php else: ?>
                if (confirm('Please login to use the trip planner. Would you like to login now?')) {
                    window.location.href = 'login.php?redirect=trip_planner.php';
                }
            <?php endif; ?>
        }

        // Wishlist functionality
        function addToWishlist(form) {
            const formData = new FormData(form);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Reload page to show the message
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add to wishlist. Please try again.');
            });
            
            return false; // Prevent default form submission
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                closeDetails();
            }
        }

        // Keyboard navigation for modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDetails();
            }
        });

        // Auto-hide messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.querySelector('.message-popup');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>
