<?php
/**
 * KINDORA PAYMENT PAGE - Process payment and update booking status
 * Handles UPI, Card, Wallet, Bank Transfer
 */

// Temporary debug: show errors during local testing (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!ob_get_level()) { ob_start(); }

require_once __DIR__ . '/config.php';

if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$payment_method = isset($_GET['method']) ? trim($_GET['method']) : '';
// Allow method via GET (redirect) or via POST (user chooses on page)
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$payment_method = '';
if (isset($_GET['method'])) {
    $payment_method = trim($_GET['method']);
} elseif (isset($_POST['payment_method'])) {
    $payment_method = trim($_POST['payment_method']);
}

// booking id is required — if missing, go back to booking page
if ($booking_id <= 0) {
    header('Location: booking.php');
    exit;
}

// Later, when handling form submission ($_SERVER['REQUEST_METHOD'] === 'POST'),
// validate $payment_method and perform payment processing.

// Get booking details
try {
    $booking = KindoraDatabase::fetchOne(
        "SELECT b.*, d.name as destination_name 
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

$payment_status = 'pending';
$message = '';
$success = false;

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method_post = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    
    // Validate payment method
    if (!in_array($payment_method_post, array('upi', 'card', 'wallet', 'bank_transfer'))) {
        $message = "❌ Invalid payment method.";
    } else {
        // Simulate payment processing
        $payment_processing = true;
        $payment_success = true; // In real app, call payment gateway API here
        
        if ($payment_success) {
            // Update booking status to confirmed
            try {
                $updateQuery = "UPDATE bookings SET 
                    status = 'confirmed',
                    payment_status = 'completed',
                    payment_method = :payment_method,
                    updated_at = NOW()
                    WHERE booking_id = :id";
                
                $result = KindoraDatabase::execute($updateQuery, array(
                    ':payment_method' => $payment_method_post,
                    ':id' => $booking_id
                ));
                
                if ($result) {
                    $success = true;
                    $payment_status = 'completed';
                    $message = "✅ Payment successful! Booking confirmed.";
                    
                    // Refresh booking data
                    $booking = KindoraDatabase::fetchOne(
                        "SELECT b.*, d.name as destination_name 
                         FROM bookings b
                         LEFT JOIN destinations d ON b.destination_id = d.destination_id
                         WHERE b.booking_id = :id",
                        array(':id' => $booking_id)
                    );
                } else {
                    $message = "❌ Failed to update booking. Please try again.";
                }
            } catch (Exception $e) {
                error_log("Payment update error: " . $e->getMessage());
                $message = "❌ Payment processing error.";
            }
        } else {
            $message = "❌ Payment failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - Kindora</title>
    <?php echo linkCSS('common'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Source Sans Pro', sans-serif; background: #f8f9fa; color: #333; }
        
        .payment-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .payment-header h1 { font-size: 1.8em; margin-bottom: 5px; font-weight: 700; }
        
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .payment-card, .booking-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .booking-summary {
            grid-column: 1;
        }
        
        .payment-card {
            grid-column: 2;
        }
        
        .booking-summary h2,
        .payment-card h2 {
            color: #1e3c72;
            margin-bottom: 20px;
            font-size: 1.3em;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            color: #666;
            font-weight: 500;
        }
        
        .summary-value {
            color: #1e3c72;
            font-weight: 600;
        }
        
        .total-amount {
            background: #fffbf0;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            border-left: 4px solid #ff6b35;
        }
        
        .total-amount .summary-item {
            border-bottom: none;
            padding: 0;
            font-size: 1.4em;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .payment-method-select {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .payment-option {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .payment-option:hover {
            border-color: #ff6b35;
            background: #fff5f0;
        }
        
        .payment-option input[type="radio"] {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }
        
        .payment-option label {
            cursor: pointer;
            flex: 1;
            margin: 0;
        }
        
        .payment-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
        
        .payment-details.show {
            display: block;
        }
        
        .payment-input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .payment-btn {
            width: 100%;
            padding: 12px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1em;
        }
        
        .payment-btn:hover {
            background: #2a5298;
        }
        
        .payment-btn:active {
            transform: scale(0.98);
        }
        
        .payment-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .success-screen {
            text-align: center;
            padding: 40px;
        }
        
        .success-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        .success-screen h2 {
            color: #27ae60;
            font-size: 1.8em;
            margin-bottom: 15px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #1e3c72;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: #2a5298;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85em;
            margin-bottom: 15px;
        }
        
        .status-badge.completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            .payment-card { grid-column: 1; }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="payment-header">
        <h1>💳 Complete Payment</h1>
        <p>Secure your booking with payment</p>
    </div>

    <div class="container">
        <!-- Booking Summary -->
        <div class="booking-summary">
            <h2>📋 Booking Summary</h2>
            
            <div class="status-badge <?php echo $booking['payment_status']; ?>">
                Status: <?php echo ucfirst($booking['payment_status']); ?>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Booking ID:</span>
                <span class="summary-value">#KN<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Destination:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking['destination_name']); ?></span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Travel Date:</span>
                <span class="summary-value"><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Travelers:</span>
                <span class="summary-value"><?php echo $booking['guests']; ?> people</span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Payment Method:</span>
                <span class="summary-value"><?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($booking['payment_method'] ?? 'Pending'))); ?></span>
            </div>
            
            <div class="total-amount">
                <div class="summary-item">
                    <span class="summary-label">Total Amount:</span>
                    <span class="summary-value">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Card -->
        <div class="payment-card">
            <?php if (!$success): ?>
                <h2>💰 Payment Method</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="payment_process.php?booking_id=<?php echo $booking_id; ?>">
                    <div class="payment-method-select">
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="upi" value="upi" checked onchange="showPaymentDetails()">
                            <label for="upi">
                                <strong>📱 UPI</strong>
                                <small style="color: #666; display: block;">Google Pay, PhonePe, Paytm</small>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="card" value="card" onchange="showPaymentDetails()">
                            <label for="card">
                                <strong>💳 Debit/Credit Card</strong>
                                <small style="color: #666; display: block;">Visa, Mastercard, Rupay</small>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="wallet" value="wallet" onchange="showPaymentDetails()">
                            <label for="wallet">
                                <strong>🏦 Wallet</strong>
                                <small style="color: #666; display: block;">Kindora Wallet Balance</small>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" name="payment_method" id="bank" value="bank_transfer" onchange="showPaymentDetails()">
                            <label for="bank">
                                <strong>🏧 Bank Transfer</strong>
                                <small style="color: #666; display: block;">Direct bank transfer (2-3 hours)</small>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Payment Details (varies by method) -->
                    <div id="upiDetails" class="payment-details show">
                        <h4>Enter UPI ID:</h4>
                        <input type="text" class="payment-input" placeholder="yourname@upi" required>
                        <small style="color: #666;">Example: yourname@googlepay</small>
                    </div>
                    
                    <div id="cardDetails" class="payment-details">
                        <h4>Card Details:</h4>
                        <input type="text" class="payment-input" placeholder="Card Number" maxlength="16" pattern="[0-9]{16}">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="text" class="payment-input" placeholder="MM/YY" maxlength="5">
                            <input type="text" class="payment-input" placeholder="CVV" maxlength="4" pattern="[0-9]{3,4}">
                        </div>
                        <input type="text" class="payment-input" placeholder="Cardholder Name">
                    </div>
                    
                    <div id="walletDetails" class="payment-details">
                        <h4>Wallet Payment</h4>
                        <p style="color: #666; margin-bottom: 10px;">Your current wallet balance will be used for payment.</p>
                        <div class="summary-item" style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
                            <span>Available Balance:</span>
                            <span style="color: #27ae60; font-weight: 600;">₹5,000</span>
                        </div>
                    </div>
                    
                    <div id="bankDetails" class="payment-details">
                        <h4>Bank Details:</h4>
                        <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                            <p><strong>Bank Name:</strong> KINDORA BANK LTD</p>
                            <p><strong>Account Number:</strong> 1234567890</p>
                            <p><strong>IFSC Code:</strong> KINDORA001</p>
                            <p><strong>Amount to Transfer:</strong> ₹<?php echo number_format($booking['total_amount'], 2); ?></p>
                            <p style="color: #666; margin-top: 10px; font-size: 0.9em;">Use your booking ID as payment reference</p>
                        </div>
                    </div>
                    
                    <button type="submit" class="payment-btn">Pay ₹<?php echo number_format($booking['total_amount'], 2); ?></button>
                    
                    <p style="text-align: center; margin-top: 15px; color: #666; font-size: 0.85em;">
                        🔒 Your payment is secure and encrypted
                    </p>
                </form>
            <?php else: ?>
                <div class="success-screen">
                    <div class="success-icon">✅</div>
                    <h2>Payment Successful!</h2>
                    <p style="color: #666; margin-bottom: 20px;">
                        Your booking has been confirmed and payment received.
                    </p>
                    <div class="booking-summary" style="box-shadow: none; background: #f9f9f9;">
                        <div class="summary-item">
                            <span class="summary-label">Booking ID:</span>
                            <span class="summary-value">#KN<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Amount Paid:</span>
                            <span class="summary-value">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Status:</span>
                            <span class="summary-value" style="color: #27ae60;">Confirmed</span>
                        </div>
                    </div>
                    <a href="booking_confirmation.php?booking_id=<?php echo $booking_id; ?>" class="back-link">View Confirmation Details</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function showPaymentDetails() {
            // Hide all details
            document.getElementById('upiDetails').classList.remove('show');
            document.getElementById('cardDetails').classList.remove('show');
            document.getElementById('walletDetails').classList.remove('show');
            document.getElementById('bankDetails').classList.remove('show');
            
            // Show selected method details
            const selected = document.querySelector('input[name="payment_method"]:checked').value;
            document.getElementById(selected + 'Details').classList.add('show');
        }
    </script>
</body>
</html>
