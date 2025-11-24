<?php
session_start();
require_once '../connection.php';

// Get Taj Mahal details from database
try {
    $stmt = $conn->prepare("
        SELECT d.*, COUNT(b.booking_id) as booking_count,
               AVG(r.rating) as avg_rating,
               COUNT(r.review_id) as review_count
        FROM destinations d 
        LEFT JOIN bookings b ON d.destination_id = b.destination_id 
        LEFT JOIN reviews r ON d.destination_id = r.destination_id AND r.status = 'approved'
        WHERE d.name = 'Taj Mahal' AND d.type = '7_wonders'
        GROUP BY d.destination_id
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $taj_mahal = $result ? $result->fetch_assoc() : null;
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching Taj Mahal details: " . $e->getMessage());
    $taj_mahal = null;
}

// Get related reviews
try {
    $reviews_stmt = $conn->prepare("
        SELECT r.*, u.full_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.destination_id = (SELECT destination_id FROM destinations WHERE name = 'Taj Mahal' AND type = '7_wonders' LIMIT 1)
        AND r.status = 'approved'
        ORDER BY r.created_at DESC 
        LIMIT 5
    ");
    $reviews_stmt->execute();
    $reviews_result = $reviews_stmt->get_result();
    $reviews = $reviews_result ? $reviews_result->fetch_all(MYSQLI_ASSOC) : [];
    $reviews_stmt->close();
} catch (Exception $e) {
    error_log("Error fetching Taj Mahal reviews: " . $e->getMessage());
    $reviews = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taj Mahal - 7 Wonders - Kindora</title>
    <link href="../7_wonders_detail.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../kindora-logo.ico">
    <link href="../common_nav_footer.css" rel="stylesheet">
    <meta name="description" content="Visit the magnificent Taj Mahal with Kindora - A symbol of eternal love and architectural marvel">
</head>
<body>
    <!-- Navigation -->
    <div id="nav1">
        <a href="../home.php" id="logo-link">
            <div id="logo">Kindora</div>
        </a>
        <div id="nav2">
            <a class="a1 dropbtn" href="../home.php">Home</a>
            <a class="a1 dropbtn" href="../explore.php">Explore</a>
            <a class="a1 dropbtn" href="../booking.php">Book</a>
            <a class="a1 dropbtn" href="../contactus.php">Contact</a>
        </div>
        
        <button class="menu" onclick="toggleMenu()">☰</button>
        <div id="sidebar" class="sidebar">
            <button class="closebtn" onclick="toggleMenu()">×</button>
            <a href="../home.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="../mytrips.php">My Dashboard</a>
                <a href="../logout.php">Logout</a>
            <?php else: ?>
                <a href="../login.php">Login/Register</a>
            <?php endif; ?>
            <a href="../booking.php">Book Your Trips</a>
            <a href="../aboutus.php">About</a>
            <a href="../contactus.php">Contact</a>
        </div>
    </div>

    <main>
        <!-- Hero Section -->
        <section class="wonder-hero">
            <div class="hero-image">
                <img src="../7_wonders/Taj_Mahal.jpeg" alt="Taj Mahal" loading="eager"
                     onerror="this.src='../7_wonders/default.jpg'">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Taj Mahal</h1>
                        <p class="hero-subtitle">A Monument to Eternal Love</p>
                        <div class="hero-badges">
                            <span class="badge wonder-badge">7 Wonders of the World</span>
                            <span class="badge location-badge">📍 Agra, India</span>
                            <?php if ($taj_mahal && $taj_mahal['avg_rating']): ?>
                                <span class="badge rating-badge">⭐ <?= number_format($taj_mahal['avg_rating'], 1) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Facts -->
        <section class="quick-facts">
            <div class="container">
                <div class="facts-grid">
                    <div class="fact-card">
                        <div class="fact-icon">🏛️</div>
                        <h4>Built</h4>
                        <p>1632-1653 CE</p>
                    </div>
                    <div class="fact-card">
                        <div class="fact-icon">👥</div>
                        <h4>Visitors</h4>
                        <p>7-8 Million/Year</p>
                    </div>
                    <div class="fact-card">
                        <div class="fact-icon">🌟</div>
                        <h4>UNESCO Site</h4>
                        <p>Since 1983</p>
                    </div>
                    <div class="fact-card">
                        <div class="fact-icon">💎</div>
                        <h4>Materials</h4>
                        <p>White Marble & Gems</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <div class="container">
            <div class="content-grid">
                <!-- Main Article -->
                <article class="main-content">
                    <!-- Overview -->
                    <section class="content-section">
                        <h2>The Crown Jewel of India</h2>
                        <p class="intro-text">
                            The Taj Mahal stands as one of the most recognizable and beloved monuments in the world. 
                            Built by Mughal Emperor Shah Jahan as a mausoleum for his beloved wife Mumtaz Mahal, 
                            this architectural masterpiece represents the pinnacle of Mughal architecture and serves 
                            as an eternal symbol of love.
                        </p>
                        
                        <p>
                            Constructed between 1632 and 1653, the Taj Mahal required the labor of thousands of artisans, 
                            craftsmen, and laborers from across the Mughal Empire and beyond. The monument's perfect 
                            symmetry, intricate inlay work, and the way it seems to change color throughout the day 
                            have captivated visitors for centuries.
                        </p>
                        
                        <p>
                            What makes the Taj Mahal truly special is not just its stunning beauty, but the love story 
                            behind it. Shah Jahan was devastated by the death of his third wife, Mumtaz Mahal, during 
                            childbirth in 1631. To honor her memory, he commissioned this magnificent mausoleum, 
                            creating what many consider to be the most beautiful building ever created.
                        </p>
                    </section>

                    <!-- Architecture & Design -->
                    <section class="content-section">
                        <h3>Architecture & Design</h3>
                        <p>
                            The Taj Mahal represents the finest example of Mughal architecture, which combines elements 
                            from Islamic, Persian, Ottoman Turkish, and Indian architectural styles. The main structure 
                            is built on a square platform and features a large central dome surrounded by four smaller domes.
                        </p>
                        
                        <p>
                            The entire structure is made of white Makrana marble, which was transported from Rajasthan. 
                            The marble is inlaid with semi-precious stones including jasper, jade, crystal, turquoise, 
                            and sapphires, creating intricate floral patterns and calligraphy that covers much of 
                            the exterior and interior surfaces.
                        </p>
                        
                        <div class="architecture-highlights">
                            <h4>Architectural Highlights:</h4>
                            <ul>
                                <li><strong>Main Dome:</strong> 35 meters in diameter and 73 meters high</li>
                                <li><strong>Minarets:</strong> Four identical 40-meter tall towers</li>
                                <li><strong>Gardens:</strong> Persian-style Charbagh with water channels</li>
                                <li><strong>Calligraphy:</strong> Quranic verses in Arabic script</li>
                                <li><strong>Pietra Dura:</strong> Intricate stone inlay work</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Cultural Significance -->
                    <section class="content-section">
                        <h3>Cultural Significance</h3>
                        <p>
                            The Taj Mahal is not just an architectural wonder; it's a cultural icon that represents 
                            India's rich Mughal heritage. Designated as a UNESCO World Heritage Site in 1983, 
                            it attracts between 7-8 million visitors annually from around the world.
                        </p>
                        
                        <p>
                            Beyond its role as a tourist destination, the Taj Mahal holds deep significance in Indian 
                            culture and has inspired countless poets, writers, and artists. It appears on the 
                            Indian 20 rupee note and has become synonymous with India itself in the global imagination.
                        </p>
                        
                        <p>
                            The monument also represents the golden age of Mughal architecture and stands as a testament 
                            to the empire's wealth, artistic achievements, and cultural sophistication during the 
                            17th century.
                        </p>
                    </section>

                    <!-- Things to Do -->
                    <section class="content-section">
                        <h3>Things to Do</h3>
                        <div class="activities-grid">
                            <div class="activity-card">
                                <div class="activity-icon">🌅</div>
                                <h4>Sunrise & Sunset Viewing</h4>
                                <p>Witness the Taj Mahal's changing colors during golden hours - an unforgettable experience.</p>
                            </div>
                            
                            <div class="activity-card">
                                <div class="activity-icon">🏛️</div>
                                <h4>Interior Exploration</h4>
                                <p>Visit the main chamber housing the cenotaphs of Shah Jahan and Mumtaz Mahal.</p>
                            </div>
                            
                            <div class="activity-card">
                                <div class="activity-icon">🌸</div>
                                <h4>Garden Strolls</h4>
                                <p>Walk through the beautiful Charbagh gardens with fountains and reflecting pools.</p>
                            </div>
                            
                            <div class="activity-card">
                                <div class="activity-icon">📸</div>
                                <h4>Photography Sessions</h4>
                                <p>Capture the perfect shot from various angles and reflective spots around the monument.</p>
                            </div>
                            
                            <div class="activity-card">
                                <div class="activity-icon">🎧</div>
                                <h4>Audio Tours</h4>
                                <p>Learn detailed history and architectural insights with professional audio guides.</p>
                            </div>
                            
                            <div class="activity-card">
                                <div class="activity-icon">🏺</div>
                                <h4>Museum Visit</h4>
                                <p>Explore artifacts and exhibits at the Taj Museum to understand its rich history.</p>
                            </div>
                        </div>
                    </section>

                    <!-- How to Reach -->
                    <section class="content-section">
                        <h3>How to Reach</h3>
                        <div class="transport-options">
                            <div class="transport-card">
                                <h4>✈️ By Air</h4>
                                <p><strong>Nearest Airport:</strong> Pandit Deen Dayal Upadhyay Airport, Agra (12 km)</p>
                                <p><strong>Major Airport:</strong> Indira Gandhi International Airport, New Delhi (230 km)</p>
                                <p>Regular flights connect Agra with major Indian cities. Delhi airport offers international connections.</p>
                            </div>
                            
                            <div class="transport-card">
                                <h4>🚂 By Train</h4>
                                <p><strong>Main Station:</strong> Agra Cantt Railway Station (5 km)</p>
                                <p><strong>Popular Trains:</strong> Gatimaan Express, Shatabdi Express, Taj Express</p>
                                <p>Excellent rail connectivity from Delhi (2-3 hours), Mumbai, and other major cities.</p>
                            </div>
                            
                            <div class="transport-card">
                                <h4>🚗 By Road</h4>
                                <p><strong>Distance from Delhi:</strong> 230 km (4-5 hours drive)</p>
                                <p><strong>Highways:</strong> NH-2 (Yamuna Expressway) - fastest route</p>
                                <p>Well-maintained highways with regular bus services and taxi options available.</p>
                            </div>
                        </div>
                    </section>

                    <!-- Tourist Information -->
                    <section class="content-section tourist-info">
                        <h3>Tourist Information</h3>
                        
                        <div class="info-grid">
                            <div class="info-card">
                                <h4>🎫 Entry Fees</h4>
                                <ul>
                                    <li><strong>Indians:</strong> ₹50</li>
                                    <li><strong>SAARC/BIMSTEC Citizens:</strong> ₹540</li>
                                    <li><strong>Other Foreigners:</strong> ₹1100</li>
                                    <li><strong>Children (Under 15):</strong> Free</li>
                                </ul>
                                <p class="note">Prices include visit to main mausoleum</p>
                            </div>
                            
                            <div class="info-card">
                                <h4>⏰ Timings</h4>
                                <ul>
                                    <li><strong>Sunrise to Sunset:</strong> 6:00 AM - 6:30 PM</li>
                                    <li><strong>Closed:</strong> Fridays (for prayer)</li>
                                    <li><strong>Night Viewing:</strong> 2 nights before/after full moon</li>
                                    <li><strong>Special:</strong> 8:30 PM - 12:30 AM (night viewing)</li>
                                </ul>
                                <p class="note">Times may vary seasonally</p>
                            </div>
                            
                            <div class="info-card">
                                <h4>📋 Important Guidelines</h4>
                                <ul>
                                    <li>Security check mandatory for all visitors</li>
                                    <li>No bags, food, or cameras inside main mausoleum</li>
                                    <li>Shoe covers provided for marble protection</li>
                                    <li>Mobile phones allowed but no photography inside tomb</li>
                                    <li>Tripods and professional equipment need permits</li>
                                </ul>
                            </div>
                            
                            <div class="info-card">
                                <h4>💡 Visitor Tips</h4>
                                <ul>
                                    <li>Visit early morning or late afternoon for best light</li>
                                    <li>Book online tickets to avoid queues</li>
                                    <li>Wear comfortable shoes and sun protection</li>
                                    <li>Hire certified guides for detailed history</li>
                                    <li>Respect photography restrictions</li>
                                    <li>Stay hydrated and carry water bottles</li>
                                </ul>
                            </div>
                        </div>
                    </section>
                </article>

                <!-- Sidebar -->
                <aside class="sidebar-content">
                    <!-- Booking Widget -->
                    <div class="widget booking-widget">
                        <h3>🎫 Visit Taj Mahal</h3>
                        <p>Book your trip to this magnificent wonder</p>
                        <?php if ($taj_mahal): ?>
                            <div class="booking-stats">
                                <p><strong>⭐ Rating:</strong> <?= $taj_mahal['avg_rating'] ? number_format($taj_mahal['avg_rating'], 1) . '/5' : 'New' ?></p>
                                <p><strong>👥 Visitors:</strong> <?= number_format($taj_mahal['booking_count']) ?> booked</p>
                            </div>
                            <a href="../booking.php?destination=<?= $taj_mahal['destination_id'] ?>" class="booking-btn">
                                Book Your Visit
                            </a>
                        <?php else: ?>
                            <a href="../booking.php" class="booking-btn">Plan Your Trip</a>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Facts Widget -->
                    <div class="widget facts-widget">
                        <h3>⚡ Quick Facts</h3>
                        <div class="fact-list">
                            <div class="fact-item">
                                <strong>Location:</strong> Agra, Uttar Pradesh, India
                            </div>
                            <div class="fact-item">
                                <strong>Built By:</strong> Shah Jahan (1632-1653)
                            </div>
                            <div class="fact-item">
                                <strong>Dedicated To:</strong> Mumtaz Mahal
                            </div>
                            <div class="fact-item">
                                <strong>Architecture:</strong> Indo-Islamic
                            </div>
                            <div class="fact-item">
                                <strong>Material:</strong> White Makrana Marble
                            </div>
                            <div class="fact-item">
                                <strong>Height:</strong> 73 meters (main dome)
                            </div>
                            <div class="fact-item">
                                <strong>Workers:</strong> ~20,000 artisans
                            </div>
                            <div class="fact-item">
                                <strong>Cost:</strong> ~32 million rupees (1653)
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Widget -->
                    <?php if (!empty($reviews)): ?>
                    <div class="widget reviews-widget">
                        <h3>💬 Recent Reviews</h3>
                        <div class="reviews-list">
                            <?php foreach (array_slice($reviews, 0, 3) as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="reviewer-name"><?= htmlspecialchars($review['full_name']) ?></span>
                                    <span class="review-rating">
                                        <?= str_repeat('⭐', $review['rating']) ?>
                                    </span>
                                </div>
                                <p class="review-text"><?= htmlspecialchars(substr($review['comment'], 0, 100)) ?>...</p>
                                <small class="review-date"><?= date('M j, Y', strtotime($review['created_at'])) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="../reviews.php?destination=taj_mahal" class="view-all-reviews">View All Reviews</a>
                    </div>
                    <?php endif; ?>

                    <!-- Related Destinations -->
                    <div class="widget related-widget">
                        <h3>🏛️ Other 7 Wonders</h3>
                        <div class="related-list">
                            <a href="Great_Wall_of_China.php" class="related-item">
                                <img src="../7_wonders/Great_Wall_of_China.jpeg" alt="Great Wall of China" loading="lazy">
                                <span>Great Wall of China</span>
                            </a>
                            <a href="Christ_the_Redeemer.php" class="related-item">
                                <img src="../7_wonders/Christ_the_Redeemer.jpeg" alt="Christ the Redeemer" loading="lazy">
                                <span>Christ the Redeemer</span>
                            </a>
                            <a href="Machu_Picchu.php" class="related-item">
                                <img src="../7_wonders/Machu_Picchu.jpeg" alt="Machu Picchu" loading="lazy">
                                <span>Machu Picchu</span>
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>

    <script src="../common_nav_footer.js"></script>
    <script>
        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Lazy loading for images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img[loading="lazy"]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => {
                img.classList.add('lazy');
                imageObserver.observe(img);
            });
        });
    </script>
</body>
</html>
