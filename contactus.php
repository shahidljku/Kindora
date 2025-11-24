<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$success = false;

// Handle contact form submission
if ($_POST) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid request. Please try again.";
    } else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject']);
        $message_text = trim($_POST['message']);
        $inquiry_type = $_POST['inquiry_type'] ?? 'general';
        
        // Validation
        $errors = [];
        
        if (empty($name)) $errors[] = "Name is required";
        if (empty($email)) $errors[] = "Email is required";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($subject)) $errors[] = "Subject is required";
        if (empty($message_text)) $errors[] = "Message is required";
        if (strlen($message_text) < 10) $errors[] = "Message must be at least 10 characters";
        
        if (empty($errors)) {
            try {
                // Insert contact message
                $stmt = $conn->prepare("
                    INSERT INTO contact_messages (name, email, phone, subject, message, inquiry_type, user_id, created_at, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'new')
                ");
                $user_id = $_SESSION['user_id'] ?? null;
                $stmt->bind_param("ssssssi", $name, $email, $phone, $subject, $message_text, $inquiry_type, $user_id);
                
                if ($stmt->execute()) {
                    $contact_id = $conn->insert_id;
                    $stmt->close();
                    
                    // Send auto-reply (in real implementation, this would be an email)
                    $auto_reply_stmt = $conn->prepare("
                        INSERT INTO contact_replies (contact_id, reply_text, reply_type, created_at) 
                        VALUES (?, ?, 'auto', NOW())
                    ");
                    $auto_reply_text = "Thank you for contacting Kindora! We've received your message and will respond within 24 hours. Reference ID: #" . str_pad($contact_id, 6, '0', STR_PAD_LEFT);
                    $auto_reply_stmt->bind_param("is", $contact_id, $auto_reply_text);
                    $auto_reply_stmt->execute();
                    $auto_reply_stmt->close();
                    
                    $message = "✅ Thank you for your message! We'll get back to you within 24 hours. Reference ID: #" . str_pad($contact_id, 6, '0', STR_PAD_LEFT);
                    $success = true;
                    
                    // Clear form data on success
                    $_POST = [];
                } else {
                    $message = "❌ Failed to send message. Please try again.";
                }
            } catch (Exception $e) {
                error_log("Contact form error: " . $e->getMessage());
                $message = "❌ An error occurred. Please try again later.";
            }
        } else {
            $message = "❌ " . implode(", ", $errors);
        }
    }
    
    // Regenerate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Kindora</title>
    <link href="contactus.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    <link href="common_nav_footer.css" rel="stylesheet">
    <meta name="description" content="Contact Kindora - Get in touch for travel support, bookings, or any questions about your adventures">
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
            <a class="a1 dropbtn" href="aboutus.php">About</a>
            <a class="a1 dropbtn active" href="contactus.php">Contact</a>
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
            <a href="contactus.php" class="active">Contact</a>
        </div>
    </div>

    <main>
        <!-- Hero Section -->
        <section class="contact-hero">
            <div class="hero-background">
                <img src="web_images/contact/hero-bg.avif" alt="Contact Kindora" loading="lazy"
                     onerror="this.src='web_images/contact/default-hero.avif'">
                <div class="hero-overlay">
                    <div class="container">
                        <h1>Get in Touch</h1>
                        <p>We're here to help make your travel dreams come true</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="contact-container">
            <!-- Contact Methods -->
            <section class="contact-methods">
                <div class="container">
                    <div class="methods-grid">
                        <div class="method-card">
                            <div class="method-icon">📞</div>
                            <h3>Call Us</h3>
                            <p>Speak with our travel experts</p>
                            <div class="contact-details">
                                <p><strong>Global Support:</strong><br>+1-800-KINDORA (546-3672)</p>
                                <p><strong>Hours:</strong><br>24/7 Emergency Support<br>9 AM - 9 PM EST (General)</p>
                            </div>
                        </div>

                        <div class="method-card">
                            <div class="method-icon">📧</div>
                            <h3>Email Us</h3>
                            <p>Send us your questions anytime</p>
                            <div class="contact-details">
                                <p><strong>General:</strong> hello@kindora.com</p>
                                <p><strong>Bookings:</strong> bookings@kindora.com</p>
                                <p><strong>Support:</strong> support@kindora.com</p>
                                <p><strong>Response:</strong> Within 24 hours</p>
                            </div>
                        </div>

                        <div class="method-card">
                            <div class="method-icon">💬</div>
                            <h3>Live Chat</h3>
                            <p>Instant help when you need it</p>
                            <div class="contact-details">
                                <p><strong>Availability:</strong><br>9 AM - 11 PM EST</p>
                                <button class="live-chat-btn" onclick="openLiveChat()">Start Chat</button>
                            </div>
                        </div>

                        <div class="method-card">
                            <div class="method-icon">🏢</div>
                            <h3>Visit Us</h3>
                            <p>Meet us at our headquarters</p>
                            <div class="contact-details">
                                <p><strong>Address:</strong><br>
                                123 Travel Street<br>
                                San Francisco, CA 94102<br>
                                United States</p>
                                <p><strong>By appointment only</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Form -->
            <section class="contact-form-section">
                <div class="container">
                    <div class="form-header">
                        <h2>Send Us a Message</h2>
                        <p>Tell us how we can help you plan your perfect adventure</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="contact-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                       required 
                                       placeholder="Your full name">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       required 
                                       placeholder="your@email.com">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                       placeholder="+1 (555) 123-4567">
                            </div>
                            
                            <div class="form-group">
                                <label for="inquiry_type">Inquiry Type *</label>
                                <select id="inquiry_type" name="inquiry_type" required>
                                    <option value="">Select inquiry type</option>
                                    <option value="general" <?= ($_POST['inquiry_type'] ?? '') === 'general' ? 'selected' : '' ?>>General Information</option>
                                    <option value="booking" <?= ($_POST['inquiry_type'] ?? '') === 'booking' ? 'selected' : '' ?>>New Booking</option>
                                    <option value="existing" <?= ($_POST['inquiry_type'] ?? '') === 'existing' ? 'selected' : '' ?>>Existing Booking</option>
                                    <option value="support" <?= ($_POST['inquiry_type'] ?? '') === 'support' ? 'selected' : '' ?>>Technical Support</option>
                                    <option value="feedback" <?= ($_POST['inquiry_type'] ?? '') === 'feedback' ? 'selected' : '' ?>>Feedback</option>
                                    <option value="partnership" <?= ($_POST['inquiry_type'] ?? '') === 'partnership' ? 'selected' : '' ?>>Partnership</option>
                                    <option value="press" <?= ($_POST['inquiry_type'] ?? '') === 'press' ? 'selected' : '' ?>>Press/Media</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <input type="text" 
                                   id="subject" 
                                   name="subject" 
                                   value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                                   required 
                                   placeholder="Brief description of your inquiry"
                                   maxlength="200">
                        </div>

                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" 
                                      name="message" 
                                      required 
                                      rows="6" 
                                      placeholder="Please provide detailed information about your inquiry..."
                                      maxlength="2000"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            <small class="char-counter">
                                <span id="char-count">0</span>/2000 characters
                            </small>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-container">
                                <input type="checkbox" name="updates" id="updates" 
                                       <?= isset($_POST['updates']) ? 'checked' : '' ?>>
                                <span class="checkmark"></span>
                                I'd like to receive travel tips and destination updates from Kindora
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit" id="submit-btn">
                                <span class="btn-text">Send Message</span>
                                <span class="btn-loader" style="display: none;">Sending...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- FAQ Section -->
            <section class="faq-section">
                <div class="container">
                    <div class="faq-header">
                        <h2>Frequently Asked Questions</h2>
                        <p>Quick answers to common questions</p>
                    </div>
                    
                    <div class="faq-grid">
                        <div class="faq-item">
                            <button class="faq-question" onclick="toggleFAQ(this)">
                                <span>How do I cancel or modify my booking?</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div class="faq-answer">
                                <p>You can cancel or modify your booking through your account dashboard or by contacting our support team. Cancellation policies vary by destination and booking type. Most bookings can be modified up to 48 hours before departure.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" onclick="toggleFAQ(this)">
                                <span>What payment methods do you accept?</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div class="faq-answer">
                                <p>We accept all major credit cards (Visa, MasterCard, American Express), PayPal, bank transfers, and in some regions, digital wallets and cryptocurrency. All payments are processed securely.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" onclick="toggleFAQ(this)">
                                <span>Do you provide travel insurance?</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div class="faq-answer">
                                <p>We partner with leading travel insurance providers to offer comprehensive coverage options. Insurance can be added during booking and covers trip cancellation, medical emergencies, and more.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" onclick="toggleFAQ(this)">
                                <span>How far in advance should I book?</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div class="faq-answer">
                                <p>We recommend booking 3-6 months in advance for popular destinations and 6-12 months for specialized expeditions like Antarctica. However, we also offer last-minute deals for flexible travelers.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" onclick="toggleFAQ(this)">
                                <span>Can I customize my itinerary?</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div class="faq-answer">
                                <p>Absolutely! All our trips can be customized to match your preferences, interests, and budget. Our travel experts work with you to create the perfect personalized experience.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" onclick="toggleFAQ(this)">
                                <span>What if I need help during my trip?</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div class="faq-answer">
                                <p>Our 24/7 emergency support team is always available to help. You'll receive emergency contact numbers and have access to local support throughout your journey.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Office Locations -->
            <section class="office-locations">
                <div class="container">
                    <div class="locations-header">
                        <h2>Our Global Offices</h2>
                        <p>Find us around the world</p>
                    </div>
                    
                    <div class="locations-grid">
                        <div class="location-card">
                            <div class="location-header">
                                <h3>🇺🇸 San Francisco (HQ)</h3>
                                <span class="location-badge">Headquarters</span>
                            </div>
                            <div class="location-details">
                                <p>📍 123 Travel Street, San Francisco, CA 94102</p>
                                <p>📞 +1 (415) 555-0123</p>
                                <p>🕒 9 AM - 6 PM PST</p>
                            </div>
                        </div>

                        <div class="location-card">
                            <div class="location-header">
                                <h3>🇬🇧 London</h3>
                                <span class="location-badge">European Hub</span>
                            </div>
                            <div class="location-details">
                                <p>📍 456 Adventure Ave, London SW1A 1AA</p>
                                <p>📞 +44 20 7123 4567</p>
                                <p>🕒 9 AM - 5 PM GMT</p>
                            </div>
                        </div>

                        <div class="location-card">
                            <div class="location-header">
                                <h3>🇸🇬 Singapore</h3>
                                <span class="location-badge">Asia-Pacific</span>
                            </div>
                            <div class="location-details">
                                <p>📍 789 Explorer Blvd, Singapore 018989</p>
                                <p>📞 +65 6123 4567</p>
                                <p>🕒 9 AM - 6 PM SGT</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Map Section -->
            <section class="map-section">
                <div class="container">
                    <div class="map-header">
                        <h2>Find Us on the Map</h2>
                    </div>
                    <div class="map-container">
                        <div class="map-placeholder">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3152.9!2d-122.4!3d37.79!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzfCsDQ3JzI0LjAiTiAxMjLCsDI0JzAwLjAiVw!5e0!3m2!1sen!2sus!4v1234567890"
                                    width="100%" 
                                    height="400" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>
    <script>
        // Character counter for message textarea
        const messageTextarea = document.getElementById('message');
        const charCount = document.getElementById('char-count');
        
        messageTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length;
            
            if (length > 1800) {
                charCount.style.color = '#dc2626';
            } else if (length > 1500) {
                charCount.style.color = '#f59e0b';
            } else {
                charCount.style.color = '#6b7280';
            }
        });

        // Form submission
        document.querySelector('.contact-form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();
            const inquiryType = document.getElementById('inquiry_type').value;
            
            if (!name || !email || !subject || !message || !inquiryType) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (message.length < 10) {
                e.preventDefault();
                alert('Message must be at least 10 characters long.');
                return false;
            }
            
            if (!validateEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
            
            // Show loading state
            const btnText = document.querySelector('.btn-text');
            const btnLoader = document.querySelector('.btn-loader');
            const submitBtn = document.querySelector('.btn-submit');
            
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline';
            submitBtn.disabled = true;
        });

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // FAQ Toggle
        function toggleFAQ(button) {
            const faqItem = button.parentElement;
            const answer = faqItem.querySelector('.faq-answer');
            const icon = button.querySelector('.faq-icon');
            
            if (faqItem.classList.contains('active')) {
                faqItem.classList.remove('active');
                answer.style.maxHeight = '0';
                icon.textContent = '+';
            } else {
                // Close other FAQs
                document.querySelectorAll('.faq-item.active').forEach(item => {
                    item.classList.remove('active');
                    item.querySelector('.faq-answer').style.maxHeight = '0';
                    item.querySelector('.faq-icon').textContent = '+';
                });
                
                // Open this FAQ
                faqItem.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + 'px';
                icon.textContent = '−';
            }
        }

        // Live Chat
        function openLiveChat() {
            alert('Live chat feature coming soon! For immediate assistance, please call +1-800-KINDORA or send us a message using the form above.');
        }

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 8000);
            }
            
            // Initialize character counter
            if (messageTextarea.value) {
                charCount.textContent = messageTextarea.value.length;
            }
        });
    </script>
</body>
</html>
