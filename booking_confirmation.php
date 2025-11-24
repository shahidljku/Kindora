<?php
/**
 * BOOKING CONFIRMATION PAGE - Show booking details after payment
 */

require_once __DIR__ . '/config.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id <= 0) {
    header('Location: booking.php');
    exit;
}

// Get booking details
try {
    $booking = KindoraDatabase::fetchOne(
        "SELECT b.*, d.name as destination_name, d.description as destination_description
         FROM bookings b
         LEFT JOIN destinations d ON b.destination_id = d.destination_id
         WHERE b.booking_id = :id AND b.user_id = :user_id",
        array(':id' => $booking_id, ':user_id' => getCurrentUserId())
    );
    
    if (!$booking) {
        header('Location: booking.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Booking fetch error: " . $e->getMessage());
    header('Location: booking.php');
    exit;
}

// Calculate reward points
$reward_points = floor($booking['total_amount'] / 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Kindora</title>
    <?php echo linkCSS('common'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Source Sans Pro', sans-serif; background: #f8f9fa; color: #333; }
        
        .confirmation-header {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 50px 20px;
            text-align: center;
        }
        
        .confirmation-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .confirmation-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .success-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 40px;
            margin-bottom: 30px;
            border-top: 5px solid #27ae60;
        }
        
        .confirmation-card h2 {
            color: #1e3c72;
            margin-bottom: 25px;
            font-size: 1.4em;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-column h3 {
            color: #1e3c72;
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            color: #1e3c72;
            font-weight: 600;
        }
        
        .highlight {
            background: #fffbf0;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #ff6b35;
            margin: 20px 0;
        }
        
        .highlight h4 {
            color: #1e3c72;
            margin-bottom: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1em;
            margin-bottom: 20px;
        }
        
        .status-badge.confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-size: 0.95em;
        }
        
        .btn-primary {
            background: #1e3c72;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2a5298;
        }
        
        .btn-secondary {
            background: #ddd;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #ccc;
        }
        
        .itinerary-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .itinerary-section h4 {
            color: #1e3c72;
            margin-bottom: 15px;
        }
        
        .itinerary-item {
            padding: 15px;
            background: white;
            border-radius: 4px;
            margin-bottom: 10px;
            border-left: 3px solid #ff6b35;
        }
        
        .reward-section {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }
        
        .reward-section h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.3em;
        }
        
        .reward-points {
            font-size: 2em;
            color: #ff6b35;
            font-weight: 700;
        }
        
        .payment-details {
            background: #f0f8ff;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            border-left: 4px solid #2196F3;
        }
        
        .payment-details h4 {
            color: #1e3c72;
            margin-bottom: 15px;
        }
        
        .payment-method-display {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .method-item {
            padding: 12px 20px;
            background: white;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        @media (max-width: 768px) {
            .confirmation-header h1 { font-size: 1.8em; }
            .info-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
   <?php require_once 'includes/header.php'; ?><br><br>

    <!-- Success Header -->
    <div class="confirmation-header">
        <div class="success-icon">✅</div>
        <h1>Booking Confirmed!</h1>
        <p>Your journey is all set. Below are your booking details.</p>
    </div>

    <!-- Confirmation Content -->
    <div class="container">
        <!-- Main Details -->
        <div class="confirmation-card">
            <div class="status-badge <?php echo strtolower($booking['status']); ?>">
                🎫 Booking Status: <?php echo ucfirst($booking['status']); ?>
            </div>
            
            <h2>📋 Your Booking Details</h2>
            
            <div class="info-grid">
                <!-- Left Column -->
                <div class="info-column">
                    <h3>Journey Details</h3>
                    
                    <div class="info-item">
                        <span class="info-label">Booking ID:</span>
                        <span class="info-value">#KN<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Destination:</span>
                        <span class="info-value"><?php echo htmlspecialchars($booking['destination_name']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Travel Date:</span>
                        <span class="info-value"><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Number of Travelers:</span>
                        <span class="info-value"><?php echo $booking['guests']; ?> people</span>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="info-column">
                    <h3>Payment Details</h3>
                    
                    <div class="info-item">
                        <span class="info-label">Total Amount:</span>
                        <span class="info-value" style="color: #27ae60; font-size: 1.2em;">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value"><?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($booking['payment_method'] ?? 'Not Selected'))); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Payment Status:</span>
                        <span class="info-value" style="color: #27ae60;">✓ <?php echo ucfirst($booking['payment_status']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Booking Date:</span>
                        <span class="info-value"><?php echo date('M d, Y H:i A', strtotime($booking['booking_date'])); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Highlight Box -->
            <div class="highlight">
                <h4>📝 Special Requests</h4>
                <p><?php echo !empty($booking['special_requests']) ? htmlspecialchars($booking['special_requests']) : 'No special requests provided'; ?></p>
            </div>
        </div>

        <!-- Reward Points -->
        <div class="reward-section">
            <h3>🎁 Reward Points Earned</h3>
            <div class="reward-points">+<?php echo $reward_points; ?> Points</div>
            <p style="margin-top: 10px; color: #666;">These points can be used for your next booking</p>
        </div>

        <!-- What's Next -->
        <div class="confirmation-card" style="border-top-color: #2196F3;">
            <h2>📅 What's Next?</h2>
            
            <div class="itinerary-section">
                <h4>Your Journey Timeline:</h4>
                
                <div class="itinerary-item">
                    <strong>📧 Confirmation Email</strong>
                    <p>Check your email for booking confirmation and detailed itinerary.</p>
                </div>
                
                <div class="itinerary-item">
                    <strong>📞 Pre-Journey Call</strong>
                    <p>Our travel advisor will contact you 7 days before your journey with final details.</p>
                </div>
                
                <div class="itinerary-item">
                    <strong>✈️ Departure Details</strong>
                    <p>Get transportation details and pickup point information 24 hours before departure.</p>
                </div>
                
                <div class="itinerary-item">
                    <strong>🎉 Enjoy Your Journey</strong>
                    <p>Depart on <?php echo date('M d, Y', strtotime($booking['travel_date'])); ?> and have an amazing experience!</p>
                </div>
            </div>
        </div>

        <!-- Payment Method Details -->
        <?php if (!empty($booking['payment_method'])): ?>
        <div class="confirmation-card" style="border-top-color: #2196F3;">
            <h2>💳 Payment Information</h2>
            
            <div class="payment-details">
                <h4>Payment Method: <?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'])); ?></h4>
                
                <?php if ($booking['payment_method'] === 'upi'): ?>
                    <p>You will receive a payment link via email. Scan the QR code or use any UPI app to complete payment.</p>
                <?php elseif ($booking['payment_method'] === 'card'): ?>
                    <p>A secure payment gateway will appear for you to enter your card details.</p>
                <?php elseif ($booking['payment_method'] === 'wallet'): ?>
                    <p>Payment will be deducted from your Kindora Wallet balance.</p>
                <?php else: ?>
                    <p>Bank transfer details have been sent to your registered email address.</p>
                <?php endif; ?>
                
                <p style="margin-top: 15px; color: #666; font-size: 0.9em;">
                    💡 Save your booking ID <strong>#KN<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></strong> for future reference
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="confirmation-card" style="border-top-color: #1e3c72; text-align: center;">
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">🏠 Back to Home</a>
                <a href="search.php" class="btn btn-secondary">🔍 Browse More Destinations</a>
                <a href="/Kindora/pages/mytrips.php" class="btn btn-secondary">📋 View All Bookings</a>
            </div>
        </div>
    </div>

     <?php require_once 'includes/footer.php'; ?>
</body>
</html>
