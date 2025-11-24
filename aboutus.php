<?php
session_start();
require_once 'connection.php';

// Get team members from database
try {
    $team_result = $conn->query("
        SELECT * FROM team_members 
        WHERE is_active = 1 
        ORDER BY display_order, full_name
    ");
    $team_members = $team_result ? $team_result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    error_log("Error fetching team members: " . $e->getMessage());
    $team_members = [];
}

// Get company statistics
try {
    $stats = [];
    
    // Total happy customers
    $result = $conn->query("SELECT COUNT(DISTINCT user_id) as total FROM bookings WHERE status = 'completed'");
    $stats['happy_customers'] = $result ? $result->fetch_assoc()['total'] : 2500;
    
    // Destinations covered
    $result = $conn->query("SELECT COUNT(*) as total FROM destinations WHERE type != 'temp'");
    $stats['destinations'] = $result ? $result->fetch_assoc()['total'] : 150;
    
    // Years of experience (founded in 2020)
    $stats['years_experience'] = date('Y') - 2020;
    
    // Average rating
    $result = $conn->query("SELECT AVG(rating) as avg_rating FROM reviews WHERE status = 'approved'");
    $stats['avg_rating'] = $result ? round($result->fetch_assoc()['avg_rating'], 1) : 4.8;
    
} catch (Exception $e) {
    error_log("Error fetching statistics: " . $e->getMessage());
    $stats = [
        'happy_customers' => 2500,
        'destinations' => 150,
        'years_experience' => 5,
        'avg_rating' => 4.8
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Kindora</title>
    <link href="aboutus.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    <link href="common_nav_footer.css" rel="stylesheet">
    <meta name="description" content="Learn about Kindora - Your trusted travel companion dedicated to creating extraordinary adventures worldwide">
</head>
<body>
    <!-- Navigation -->
    <div id="nav1">
        <a href="home.php" id="logo-link">
            <div id="logo">Kindora</div>
        </a>
        <div id="nav2">
            <a class="a1 dropbtn" href="home.php">Home</a>
            <a class="a1 dropbtn" href="explore.php">Explore</a>
            <a class="a1 dropbtn" href="booking.php">Book</a>
            <a class="a1 dropbtn active" href="aboutus.php">About</a>
            <a class="a1 dropbtn" href="contactus.php">Contact</a>
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
            <a href="aboutus.php" class="active">About</a>
            <a href="contactus.php">Contact</a>
        </div>
    </div>

    <main>
        <!-- Hero Section -->
        <section class="about-hero">
            <div class="hero-background">
                <img src="web_images/about/kindora-story.avif" alt="Kindora Story" loading="lazy"
                     onerror="this.src='web_images/about/default-hero.avif'">
                <div class="hero-overlay">
                    <div class="container">
                        <h1 class="hero-title">Our Story</h1>
                        <p class="hero-subtitle">Connecting hearts with destinations, creating memories that last a lifetime</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Company Story -->
        <section class="company-story">
            <div class="container">
                <div class="story-content">
                    <div class="story-text">
                        <h2>Welcome to Kindora</h2>
                        <p class="story-intro">
                            Born from a passion for exploration and a belief that travel has the power to transform lives, 
                            Kindora began as a dream to make the world's most incredible destinations accessible to everyone.
                        </p>
                        
                        <p>
                            Founded in 2020 by a group of seasoned travelers and technology enthusiasts, we set out to revolutionize 
                            the way people discover, plan, and experience their dream destinations. Our mission is simple yet profound: 
                            to be your trusted companion in creating extraordinary adventures that go beyond the ordinary tourist experience.
                        </p>
                        
                        <p>
                            What started as a small team with big dreams has grown into a global community of travel enthusiasts, 
                            local guides, and destination experts. We believe that every journey should be as unique as the traveler 
                            embarking on it, which is why we've dedicated ourselves to providing personalized, authentic, and 
                            sustainable travel experiences.
                        </p>
                        
                        <div class="story-highlights">
                            <div class="highlight-item">
                                <span class="highlight-icon">🌍</span>
                                <div class="highlight-content">
                                    <h4>Global Reach</h4>
                                    <p>Destinations across all seven continents, from bustling cities to remote wilderness</p>
                                </div>
                            </div>
                            <div class="highlight-item">
                                <span class="highlight-icon">🤝</span>
                                <div class="highlight-content">
                                    <h4>Local Partnerships</h4>
                                    <p>Collaborating with local communities to create authentic, sustainable experiences</p>
                                </div>
                            </div>
                            <div class="highlight-item">
                                <span class="highlight-icon">💡</span>
                                <div class="highlight-content">
                                    <h4>Innovation</h4>
                                    <p>Leveraging technology to make travel planning seamless and personalized</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="story-image">
                        <img src="web_images/about/team-adventure.avif" alt="Kindora Team Adventure" loading="lazy"
                             onerror="this.src='web_images/about/default-team.avif'">
                        <div class="image-caption">
                            <p>Our team exploring new destinations to bring you the best experiences</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics -->
        <section class="company-stats">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($stats['happy_customers']) ?>+</span>
                        <span class="stat-label">Happy Travelers</span>
                        <span class="stat-description">Customers who've experienced unforgettable journeys with us</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['destinations'] ?>+</span>
                        <span class="stat-label">Destinations</span>
                        <span class="stat-description">Carefully curated locations across all continents</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['years_experience'] ?></span>
                        <span class="stat-label">Years Experience</span>
                        <span class="stat-description">Building trust and delivering exceptional service</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['avg_rating'] ?>/5</span>
                        <span class="stat-label">Customer Rating</span>
                        <span class="stat-description">Based on thousands of genuine reviews</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Mission & Values -->
        <section class="mission-values">
            <div class="container">
                <div class="mission-content">
                    <div class="mission-card">
                        <div class="card-header">
                            <span class="card-icon">🎯</span>
                            <h3>Our Mission</h3>
                        </div>
                        <div class="card-content">
                            <p>
                                To inspire and enable meaningful travel experiences that connect people with diverse cultures, 
                                breathtaking landscapes, and unforgettable adventures while promoting sustainable tourism 
                                and supporting local communities worldwide.
                            </p>
                        </div>
                    </div>

                    <div class="mission-card">
                        <div class="card-header">
                            <span class="card-icon">👁️</span>
                            <h3>Our Vision</h3>
                        </div>
                        <div class="card-content">
                            <p>
                                To become the world's most trusted travel platform, where every journey is a gateway to 
                                understanding, growth, and connection. We envision a world where travel breaks down barriers 
                                and builds bridges between cultures, fostering global understanding and environmental stewardship.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="values-grid">
                    <h2 class="values-title">Our Core Values</h2>
                    <div class="values-container">
                        <div class="value-item">
                            <span class="value-icon">🌱</span>
                            <h4>Sustainability</h4>
                            <p>Committed to responsible tourism that preserves destinations for future generations</p>
                        </div>
                        <div class="value-item">
                            <span class="value-icon">✨</span>
                            <h4>Authenticity</h4>
                            <p>Providing genuine experiences that showcase the true essence of each destination</p>
                        </div>
                        <div class="value-item">
                            <span class="value-icon">🛡️</span>
                            <h4>Safety</h4>
                            <p>Your security and well-being are our top priorities in every journey we create</p>
                        </div>
                        <div class="value-item">
                            <span class="value-icon">💝</span>
                            <h4>Excellence</h4>
                            <p>Delivering exceptional service and experiences that exceed your expectations</p>
                        </div>
                        <div class="value-item">
                            <span class="value-icon">🤝</span>
                            <h4>Community</h4>
                            <p>Building strong relationships with local partners and supporting community development</p>
                        </div>
                        <div class="value-item">
                            <span class="value-icon">🚀</span>
                            <h4>Innovation</h4>
                            <p>Continuously evolving our platform to enhance your travel planning and experience</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <?php if (!empty($team_members)): ?>
        <section class="our-team">
            <div class="container">
                <div class="team-header">
                    <h2>Meet Our Team</h2>
                    <p>The passionate individuals behind your extraordinary travel experiences</p>
                </div>
                
                <div class="team-grid">
                    <?php foreach ($team_members as $member): ?>
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="<?= str_replace('\\\\', '/', htmlspecialchars($member['photo_url'])) ?>" 
                                 alt="<?= htmlspecialchars($member['full_name']) ?>" 
                                 loading="lazy"
                                 onerror="this.src='web_images/team/default-avatar.avif'">
                        </div>
                        <div class="team-info">
                            <h4 class="member-name"><?= htmlspecialchars($member['full_name']) ?></h4>
                            <p class="member-role"><?= htmlspecialchars($member['position']) ?></p>
                            <p class="member-bio"><?= htmlspecialchars($member['bio']) ?></p>
                            
                            <?php if (!empty($member['specialties'])): ?>
                            <div class="member-specialties">
                                <strong>Specialties:</strong>
                                <span><?= htmlspecialchars($member['specialties']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Why Choose Us -->
        <section class="why-choose-us">
            <div class="container">
                <div class="why-header">
                    <h2>Why Choose Kindora?</h2>
                    <p>What sets us apart in the world of travel</p>
                </div>
                
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon">🎨</div>
                        <h4>Personalized Experiences</h4>
                        <p>Every trip is tailored to your interests, preferences, and travel style. No cookie-cutter packages, just unique adventures designed for you.</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">🌐</div>
                        <h4>Local Expertise</h4>
                        <p>Our network of local guides and partners ensures you experience destinations like a local, not just a tourist.</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">📱</div>
                        <h4>Smart Technology</h4>
                        <p>Our platform uses intelligent algorithms to suggest destinations and create itineraries that match your preferences perfectly.</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">🌿</div>
                        <h4>Sustainable Travel</h4>
                        <p>Every trip contributes to local communities and environmental conservation efforts, making your travel meaningful.</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">💎</div>
                        <h4>Premium Service</h4>
                        <p>From planning to return, enjoy 24/7 support, exclusive access, and attention to every detail of your journey.</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">🏆</div>
                        <h4>Award-Winning</h4>
                        <p>Recognized for excellence in customer service, innovation, and sustainable tourism practices worldwide.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Start Your Adventure?</h2>
                    <p>Join thousands of travelers who have discovered the world with Kindora. Your extraordinary journey begins with a single click.</p>
                    <div class="cta-buttons">
                        <a href="explore.php" class="btn-primary">Explore Destinations</a>
                        <a href="booking.php" class="btn-secondary">Plan Your Trip</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>
    <script>
        // Counter animation for statistics
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.stat-number');
            const speed = 200; // Animation speed
            
            const animateCounter = (counter) => {
                const target = parseInt(counter.textContent.replace(/[+,]/g, ''));
                const increment = target / speed;
                let current = 0;
                
                if (target > 0) {
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            counter.textContent = target.toLocaleString() + (counter.textContent.includes('+') ? '+' : '');
                            clearInterval(timer);
                        } else {
                            counter.textContent = Math.floor(current).toLocaleString() + (counter.textContent.includes('+') ? '+' : '');
                        }
                    }, 1);
                }
            };

            // Intersection Observer for counter animation
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target.querySelector('.stat-number');
                        if (counter && !counter.classList.contains('animated')) {
                            counter.classList.add('animated');
                            animateCounter(counter);
                        }
                    }
                });
            });

            document.querySelectorAll('.stat-item').forEach(item => {
                observer.observe(item);
            });

            // Smooth reveal animations for cards
            const cards = document.querySelectorAll('.benefit-card, .value-item, .team-card');
            const cardObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                cardObserver.observe(card);
            });
        });
    </script>
</body>
</html>
