<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $australia_destinations_result = $conn->query("
        SELECT d.*, COUNT(b.booking_id) as booking_count,
               AVG(r.rating) as avg_rating,
               COUNT(r.review_id) as review_count
        FROM destinations d 
        LEFT JOIN bookings b ON d.destination_id = b.destination_id 
        LEFT JOIN reviews r ON d.destination_id = r.destination_id AND r.status = 'approved'
        WHERE d.type = 'australia' AND d.name != 'My Trip Planner'
        GROUP BY d.destination_id
        ORDER BY d.name
    ");
    $australia_destinations = $australia_destinations_result ? $australia_destinations_result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    error_log("Error fetching Australia destinations: " . $e->getMessage());
    $australia_destinations = [];
}

$message = '';

if ($_POST && isset($_POST['action']) && isset($_SESSION['user_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid request. Please try again.";
    } else {
        if ($_POST['action'] === 'add_to_wishlist') {
            $dest_id = filter_var($_POST['destination_id'], FILTER_VALIDATE_INT);
            $user_id = $_SESSION['user_id'];
            
            if ($dest_id) {
                try {
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
                            $message = "❌ Failed to add to wishlist.";
                        }
                        $insert_stmt->close();
                    } else {
                        $message = "ℹ️ Already in your wishlist!";
                    }
                    $check_stmt->close();
                } catch (Exception $e) {
                    error_log("Error adding to wishlist: " . $e->getMessage());
                    $message = "❌ An error occurred.";
                }
            }
        }
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Australia & Oceania Destinations - Kindora</title>
    <link href="australia.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    <link href="common_nav_footer.css" rel="stylesheet">
    <meta name="description" content="Explore Australia and Oceania with Kindora - From the Outback to tropical islands">
</head>
<body>
    <!-- Background Video -->
    <div class="video-container">
        <video autoplay muted loop id="bg-video" preload="metadata">
            <source src="continents_videos/australia_intro.webm" type="video/webm">
            <source src="continents_videos/australia_intro.mp4" type="video/mp4">
        </video>
        <div class="video-overlay">
            <h1>Discover Australia & Oceania</h1>
            <p>From the Outback to paradise islands</p>
        </div>
    </div>

    <!-- Navigation -->
    <div id="nav1">
        <a href="home.php" id="logo-link">
            <div id="logo">Kindora</div>
        </a>

        <div id="nav2">
            <div class="inspire-wrapper">
                <a class="a1 dropbtn" id="inspireBtn" href="#">Be Inspired</a>
                <div class="scroll-container1" id="inspireScroll">
                    <a data-href="asia.php"><div class="image-wrapper1">
                        <img src="web_images/banner/asia.avif" alt="Asia" loading="lazy">
                        <div class="overlay-text">Asia</div>
                    </div></a>
                    <a data-href="europe.php"><div class="image-wrapper1">
                        <img src="web_images/banner/europe.jpeg" alt="Europe" loading="lazy">
                        <div class="overlay-text">Europe</div>
                    </div></a>
                    <a data-href="africa.php"><div class="image-wrapper1">
                        <img src="web_images/banner/africa.avif" alt="Africa" loading="lazy">
                        <div class="overlay-text">Africa</div>
                    </div></a>
                    <a data-href="north_america.php"><div class="image-wrapper1">
                        <img src="web_images/banner/north_america.avif" alt="North America" loading="lazy">
                        <div class="overlay-text">North America</div>
                    </div></a>
                    <a data-href="south_america.php"><div class="image-wrapper1">
                        <img src="7_wonders/Machu_Picchu.jpeg" alt="South America" loading="lazy">
                        <div class="overlay-text">South America</div>
                    </div></a>
                    <a data-href="australia.php" class="active"><div class="image-wrapper1">
                        <img src="web_images/banner/australia.avif" alt="Australia" loading="lazy">
                        <div class="overlay-text">Australia</div>
                    </div></a>
                    <a data-href="antarctica.php"><div class="image-wrapper1">
                        <img src="web_images/banner/antarctica.jpeg" alt="Antarctica" loading="lazy">
                        <div class="overlay-text">Antarctica</div>
                    </div></a>
                </div>
            </div>
            <a class="a1 dropbtn" href="explore.php">Places to go</a>
            <a class="a1 dropbtn" href="things_to_do.php">Things to do</a>
            <a class="a1 dropbtn" href="booking.php">Plan Your Trip</a>
        </div>

        <button class="menu" onclick="toggleMenu()">☰</button>
        <div id="sidebar" class="sidebar">
            <button class="closebtn" onclick="toggleMenu()">×</button>
            <a href="home.php">Home</a>
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
        <br><br><br><br>

        <?php if ($message): ?>
            <div class="message-popup <?= strpos($message, '❌') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?= count($australia_destinations) ?></span>
                        <span class="stat-label">Destinations</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= array_sum(array_column($australia_destinations, 'booking_count')) ?></span>
                        <span class="stat-label">Aussie Adventurers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= count($australia_destinations) > 0 ? number_format(array_sum(array_column($australia_destinations, 'avg_rating')) / count($australia_destinations), 1) : '0' ?></span>
                        <span class="stat-label">Avg Rating</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Grid -->
        <div class="container">
            <div class="card-grid">
                <?php if (!empty($australia_destinations)): ?>
                    <?php foreach ($australia_destinations as $destination): ?>
                        <div class="video-card destination-card" 
                             data-dest-id="<?= $destination['destination_id'] ?>" 
                             id="dest-<?= $destination['destination_id'] ?>">
                             
                            <div class="card-image">
                                <img src="<?= str_replace('\\\\', '/', htmlspecialchars($destination['image_url'])) ?>" 
                                     alt="<?= htmlspecialchars($destination['name']) ?>" 
                                     loading="lazy"
                                     onerror="this.src='continents/images/australia_images/default.avif'">
                                     
                                <?php if ($destination['booking_count'] > 4): ?>
                                    <div class="popular-badge">🦘 Aussie Favorite</div>
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
                                        <span class="booking-count">🇦🇺 <?= $destination['booking_count'] ?> travelers</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-actions">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="add_to_wishlist">
                                            <input type="hidden" name="destination_id" value="<?= $destination['destination_id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" class="save-btn">💖 Save</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <button class="view-btn" onclick="showDetails(<?= $destination['destination_id'] ?>)">
                                        👁️ View
                                    </button>
                                    
                                    <a href="booking.php?destination=<?= $destination['destination_id'] ?>" class="book-btn">
                                        🎫 G'day Adventure
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-destinations">
                        <h3>Aussie Adventures Coming Soon</h3>
                        <p>We're preparing amazing experiences across Australia and Oceania.</p>
                    </div>
                <?php endif; ?>

                <!-- Trip Planner Card -->
                <div class="video-card trip-planner-card" onclick="showTripPlanner()">
                    <div class="card-image">
                        <img src="continents/images/australia_images/trip_planner.avif" alt="My Trip Planner" loading="lazy">
                    </div>
                    <div class="card-overlay">
                        <h3>My Aussie Adventure Planner</h3>
                        <p>Plan your perfect Down Under experience from cities to the Outback.</p>
                        <div class="card-actions">
                            <button class="view-btn">📋 Plan Aussie Trip</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDetails()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>
    <script>
        function showDetails(destId) {
            fetch(`get_destination_details.php?id=${destId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = `
                        <h2>${data.name}</h2>
                        <img src="${data.image_url}" alt="${data.name}" style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px; margin: 16px 0;">
                        <p><strong>Description:</strong> ${data.description}</p>
                        <div class="modal-actions" style="margin-top: 20px;">
                            <a href="booking.php?destination=${data.destination_id}" class="book-btn">Book Aussie Adventure</a>
                        </div>
                    `;
                    document.getElementById('detailsModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load destination details');
                });
        }

        function closeDetails() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        function showTripPlanner() {
            <?php if (isset($_SESSION['user_id'])): ?>
                window.location.href = 'trip_planner.php?continent=australia';
            <?php else: ?>
                alert('Please login to use the trip planner');
                window.location.href = 'login.php';
            <?php endif; ?>
        }

        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) closeDetails();
        }
    </script>
</body>
</html>
