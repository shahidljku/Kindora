<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $antarctica_destinations_result = $conn->query("
        SELECT d.*, COUNT(b.booking_id) as booking_count,
               AVG(r.rating) as avg_rating,
               COUNT(r.review_id) as review_count
        FROM destinations d 
        LEFT JOIN bookings b ON d.destination_id = b.destination_id 
        LEFT JOIN reviews r ON d.destination_id = r.destination_id AND r.status = 'approved'
        WHERE d.type = 'antarctica' AND d.name != 'My Trip Planner'
        GROUP BY d.destination_id
        ORDER BY d.name
    ");
    $antarctica_destinations = $antarctica_destinations_result ? $antarctica_destinations_result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    error_log("Error fetching Antarctica destinations: " . $e->getMessage());
    $antarctica_destinations = [];
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
    <title>Antarctica Expeditions - Kindora</title>
    <link href="antarctica.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    <link href="common_nav_footer.css" rel="stylesheet">
    <meta name="description" content="Explore Antarctica with Kindora - The ultimate polar expedition to the last wilderness">
</head>
<body>
    <!-- Background Video -->
    <div class="video-container">
        <video autoplay muted loop id="bg-video" preload="metadata">
            <source src="continents_videos/antarctica_intro.webm" type="video/webm">
            <source src="continents_videos/antarctica_intro.mp4" type="video/mp4">
        </video>
        <div class="video-overlay">
            <h1>Discover Antarctica</h1>
            <p>The last untouched wilderness on Earth</p>
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
                    <a data-href="australia.php"><div class="image-wrapper1">
                        <img src="web_images/banner/australia.avif" alt="Australia" loading="lazy">
                        <div class="overlay-text">Australia</div>
                    </div></a>
                    <a data-href="antarctica.php" class="active"><div class="image-wrapper1">
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

        <!-- Antarctica Introduction -->
        <section class="antarctica-intro">
            <div class="container">
                <div class="intro-content">
                    <h2>Welcome to Antarctica</h2>
                    <p class="intro-text">
                        Antarctica, the seventh continent, represents the ultimate frontier in travel. This pristine wilderness offers an unparalleled opportunity to witness nature in its most raw and magnificent form. From towering icebergs to colonies of penguins, every moment in Antarctica is a once-in-a-lifetime experience.
                    </p>
                    <div class="antarctica-facts">
                        <div class="fact-item">
                            <span class="fact-icon">🌡️</span>
                            <div class="fact-content">
                                <h4>Temperature</h4>
                                <p>-10°C to -60°C</p>
                            </div>
                        </div>
                        <div class="fact-item">
                            <span class="fact-icon">📏</span>
                            <div class="fact-content">
                                <h4>Size</h4>
                                <p>14 million km²</p>
                            </div>
                        </div>
                        <div class="fact-item">
                            <span class="fact-icon">🐧</span>
                            <div class="fact-content">
                                <h4>Wildlife</h4>
                                <p>Over 45 bird species</p>
                            </div>
                        </div>
                        <div class="fact-item">
                            <span class="fact-icon">🏔️</span>
                            <div class="fact-content">
                                <h4>Ice Depth</h4>
                                <p>Up to 4.8 km thick</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics -->
        <div class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?= count($antarctica_destinations) ?></span>
                        <span class="stat-label">Expedition Sites</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= array_sum(array_column($antarctica_destinations, 'booking_count')) ?></span>
                        <span class="stat-label">Polar Explorers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= count($antarctica_destinations) > 0 ? number_format(array_sum(array_column($antarctica_destinations, 'avg_rating')) / count($antarctica_destinations), 1) : '0' ?></span>
                        <span class="stat-label">Avg Rating</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">Nov-Mar</span>
                        <span class="stat-label">Best Season</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Trip Planner Section -->
        <section class="trip-planner-section">
            <div class="container">
                <div class="planner-header">
                    <h2>Plan Your Antarctic Expedition</h2>
                    <p>Customize your journey to the White Continent</p>
                </div>
                
                <div class="planner-grid">
                    <div class="planner-card">
                        <div class="card-header">
                            <span class="card-icon">🚢</span>
                            <h3>Expedition Type</h3>
                        </div>
                        <div class="card-content">
                            <div class="option-group">
                                <label class="option-item">
                                    <input type="radio" name="expedition_type" value="classic" checked>
                                    <span class="option-label">Classic Peninsula (10-12 days)</span>
                                </label>
                                <label class="option-item">
                                    <input type="radio" name="expedition_type" value="extended">
                                    <span class="option-label">Extended Antarctic Circle (14-16 days)</span>
                                </label>
                                <label class="option-item">
                                    <input type="radio" name="expedition_type" value="luxury">
                                    <span class="option-label">Luxury Expedition (18-21 days)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="planner-card">
                        <div class="card-header">
                            <span class="card-icon">📅</span>
                            <h3>Departure Month</h3>
                        </div>
                        <div class="card-content">
                            <select class="expedition-select" id="departure-month">
                                <option value="">Select Month</option>
                                <option value="november">November - Early Season</option>
                                <option value="december">December - Peak Wildlife</option>
                                <option value="january">January - Warmest Weather</option>
                                <option value="february">February - Whale Season</option>
                                <option value="march">March - Late Season</option>
                            </select>
                        </div>
                    </div>

                    <div class="planner-card">
                        <div class="card-header">
                            <span class="card-icon">🎯</span>
                            <h3>Activities</h3>
                        </div>
                        <div class="card-content">
                            <div class="activity-grid">
                                <label class="activity-item">
                                    <input type="checkbox" value="wildlife">
                                    <span class="activity-icon">🐧</span>
                                    <span class="activity-name">Wildlife Viewing</span>
                                </label>
                                <label class="activity-item">
                                    <input type="checkbox" value="kayaking">
                                    <span class="activity-icon">🛶</span>
                                    <span class="activity-name">Kayaking</span>
                                </label>
                                <label class="activity-item">
                                    <input type="checkbox" value="mountaineering">
                                    <span class="activity-icon">🏔️</span>
                                    <span class="activity-name">Mountaineering</span>
                                </label>
                                <label class="activity-item">
                                    <input type="checkbox" value="photography">
                                    <span class="activity-icon">📸</span>
                                    <span class="activity-name">Photography</span>
                                </label>
                                <label class="activity-item">
                                    <input type="checkbox" value="research">
                                    <span class="activity-icon">🔬</span>
                                    <span class="activity-name">Scientific Research</span>
                                </label>
                                <label class="activity-item">
                                    <input type="checkbox" value="camping">
                                    <span class="activity-icon">⛺</span>
                                    <span class="activity-name">Polar Camping</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="planner-card">
                        <div class="card-header">
                            <span class="card-icon">💰</span>
                            <h3>Budget Range</h3>
                        </div>
                        <div class="card-content">
                            <div class="budget-slider">
                                <input type="range" id="budget-range" min="8000" max="50000" value="20000" step="1000">
                                <div class="budget-display">
                                    <span id="budget-value">$20,000</span>
                                    <small>per person</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="planner-actions">
                    <button class="btn-calculate" onclick="calculateExpedition()">
                        Calculate My Expedition
                    </button>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btn-save-plan" onclick="saveTripPlan()">
                            Save Trip Plan
                        </button>
                    <?php endif; ?>
                </div>

                <div id="expedition-results" class="expedition-results" style="display: none;">
                    <!-- Results populated by JavaScript -->
                </div>
            </div>
        </section>

        <!-- Destinations Grid -->
        <section class="destinations-section">
            <div class="container">
                <h2 class="section-title">Antarctic Landing Sites</h2>
                <div class="card-grid">
                    <?php if (!empty($antarctica_destinations)): ?>
                        <?php foreach ($antarctica_destinations as $destination): ?>
                            <div class="video-card destination-card" 
                                 data-dest-id="<?= $destination['destination_id'] ?>" 
                                 id="dest-<?= $destination['destination_id'] ?>">
                                 
                                <div class="card-image">
                                    <img src="<?= str_replace('\\\\', '/', htmlspecialchars($destination['image_url'])) ?>" 
                                         alt="<?= htmlspecialchars($destination['name']) ?>" 
                                         loading="lazy"
                                         onerror="this.src='continents/images/antarctica_images/default.avif'">
                                         
                                    <?php if ($destination['booking_count'] > 2): ?>
                                        <div class="popular-badge">❄️ Explorer's Choice</div>
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
                                            <span class="booking-count">🧊 <?= $destination['booking_count'] ?> polar explorers</span>
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
                                            👁️ Explore
                                        </button>
                                        
                                        <a href="booking.php?destination=<?= $destination['destination_id'] ?>" class="book-btn">
                                            🚢 Book Expedition
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-destinations">
                            <h3>Antarctic Expeditions Coming Soon</h3>
                            <p>We're preparing the most exclusive polar expeditions to the White Continent.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Essential Information -->
        <section class="essential-info">
            <div class="container">
                <h2>Essential Antarctica Information</h2>
                <div class="info-grid">
                    <div class="info-card">
                        <h3>🧥 What to Pack</h3>
                        <ul>
                            <li>Waterproof jacket and pants</li>
                            <li>Insulated boots (provided)</li>
                            <li>Warm layers and thermals</li>
                            <li>Sun protection (crucial)</li>
                            <li>Camera with extra batteries</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <h3>🏥 Health & Safety</h3>
                        <ul>
                            <li>Medical clearance required</li>
                            <li>Travel insurance mandatory</li>
                            <li>No rescue guarantee</li>
                            <li>Physical fitness essential</li>
                            <li>Experienced guides only</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <h3>🌍 Environmental Impact</h3>
                        <ul>
                            <li>Strict visitor guidelines</li>
                            <li>Leave no trace principles</li>
                            <li>Wildlife protection zones</li>
                            <li>Limited group sizes</li>
                            <li>Carbon offset programs</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <h3>📋 Requirements</h3>
                        <ul>
                            <li>Valid passport (6+ months)</li>
                            <li>No visa required</li>
                            <li>IAATO certified operators</li>
                            <li>Pre-trip briefings</li>
                            <li>Emergency contacts</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
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
        // Budget slider functionality
        document.getElementById('budget-range').addEventListener('input', function() {
            const value = parseInt(this.value);
            document.getElementById('budget-value').textContent = '$' + value.toLocaleString();
        });

        // Expedition calculator
        function calculateExpedition() {
            const expeditionType = document.querySelector('input[name="expedition_type"]:checked').value;
            const departureMonth = document.getElementById('departure-month').value;
            const budget = parseInt(document.getElementById('budget-range').value);
            const activities = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);
            
            if (!departureMonth) {
                alert('Please select a departure month');
                return;
            }
            
            // Calculate expedition details
            const expeditionData = {
                classic: { duration: '10-12 days', basePrice: 12000, description: 'Antarctic Peninsula expedition with daily landings' },
                extended: { duration: '14-16 days', basePrice: 18000, description: 'Cross Antarctic Circle, extended wildlife viewing' },
                luxury: { duration: '18-21 days', basePrice: 35000, description: 'Ultimate Antarctic experience with all amenities' }
            };
            
            const monthData = {
                november: { temp: '-2°C to -8°C', wildlife: 'Penguin courtship, early seals', conditions: 'Variable weather, some snow' },
                december: { temp: '0°C to -5°C', wildlife: 'Penguin chicks, active wildlife', conditions: 'Best weather, long daylight' },
                january: { temp: '1°C to -3°C', wildlife: 'Penguin feeding, whale arrivals', conditions: 'Warmest period, calm seas' },
                february: { temp: '0°C to -4°C', wildlife: 'Whale season peak, penguin molting', conditions: 'Good weather, active wildlife' },
                march: { temp: '-1°C to -6°C', wildlife: 'Last whales, penguin dispersal', conditions: 'Cooler, variable conditions' }
            };
            
            const selected = expeditionData[expeditionType];
            const monthInfo = monthData[departureMonth];
            
            const resultsDiv = document.getElementById('expedition-results');
            resultsDiv.innerHTML = `
                <div class="results-header">
                    <h3>Your Antarctic Expedition Plan</h3>
                </div>
                <div class="results-grid">
                    <div class="result-card">
                        <h4>🚢 Expedition Details</h4>
                        <p><strong>Type:</strong> ${expeditionType.charAt(0).toUpperCase() + expeditionType.slice(1)} Expedition</p>
                        <p><strong>Duration:</strong> ${selected.duration}</p>
                        <p><strong>Description:</strong> ${selected.description}</p>
                        <p><strong>Estimated Cost:</strong> $${selected.basePrice.toLocaleString()} - $${(selected.basePrice * 1.3).toLocaleString()}</p>
                    </div>
                    <div class="result-card">
                        <h4>🌡️ ${departureMonth.charAt(0).toUpperCase() + departureMonth.slice(1)} Conditions</h4>
                        <p><strong>Temperature:</strong> ${monthInfo.temp}</p>
                        <p><strong>Wildlife:</strong> ${monthInfo.wildlife}</p>
                        <p><strong>Conditions:</strong> ${monthInfo.conditions}</p>
                    </div>
                    <div class="result-card">
                        <h4>🎯 Selected Activities</h4>
                        ${activities.length > 0 ? 
                            activities.map(activity => `<span class="activity-tag">${activity}</span>`).join('') : 
                            '<p>Standard expedition activities included</p>'
                        }
                    </div>
                </div>
                <div class="results-actions">
                    <a href="booking.php?expedition=${expeditionType}&month=${departureMonth}&budget=${budget}" class="btn-book-expedition">
                        Book This Expedition
                    </a>
                    <button onclick="requestCustomQuote()" class="btn-custom-quote">
                        Request Custom Quote
                    </button>
                </div>
            `;
            
            resultsDiv.style.display = 'block';
            resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function saveTripPlan() {
            // Save trip plan functionality
            alert('Trip plan saved to your dashboard!');
        }

        function requestCustomQuote() {
            alert('Custom quote request sent! Our polar specialists will contact you within 24 hours.');
        }

        function showDetails(destId) {
            fetch(`get_destination_details.php?id=${destId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = `
                        <h2>${data.name}</h2>
                        <img src="${data.image_url}" alt="${data.name}" style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px; margin: 16px 0;">
                        <p><strong>Description:</strong> ${data.description}</p>
                        <div class="modal-actions" style="margin-top: 20px;">
                            <a href="booking.php?destination=${data.destination_id}" class="book-btn">Book Antarctic Expedition</a>
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

        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) closeDetails();
        }

        // Auto-hide messages
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
