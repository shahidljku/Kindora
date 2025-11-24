<?php
// The header is included once at the top.
require_once 'includes/header.php';

/**
 * KINDORA BOOKING PAGE - FINAL CORRECTED VERSION
 * This is the primary logic block for the booking page.
 * It handles form submission (POST requests) and data fetching for the form (GET requests).
 */

// Buffer output to prevent "headers already sent" errors.
if (!ob_get_level()) {
    ob_start();
}

// Redundant includes are commented out as they are already in header.php
// require_once __DIR__ . '/config.php';
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// Check if user is logged in
if (!isUserLoggedIn()) {
    header('Location: login.php?redirect=booking.php');
    exit;
}

$success = false;
$message = '';
// Initialize form_data with default values to prevent errors on page load.
$form_data = array(
    'destination_id' => 0,
    'travel_date' => '',
    'adults' => 1,
    'children' => 0,
    'package_type' => 'standard',
    'payment_method' => '',
    'special_requests' => ''
);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // (The extensive POST handling logic from the original file is preserved here)
    // ... (all validation and database insertion logic remains untouched) ...
    $destination_id = isset($_POST['destination_id']) ? intval($_POST['destination_id']) : 0;
    $travel_date = isset($_POST['travel_date']) ? trim($_POST['travel_date']) : '';
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 0;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $package_type = isset($_POST['package_type']) ? trim($_POST['package_type']) : 'standard';
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    $special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';
    
    $form_data = array(
        'destination_id' => $destination_id,
        'travel_date' => $travel_date,
        'adults' => $adults,
        'children' => $children,
        'package_type' => $package_type,
        'payment_method' => $payment_method,
        'special_requests' => $special_requests
    );
    
    if ($destination_id <= 0) {
        $message = "❌ Please select a valid destination.";
    }
    else if ($adults < 1 || $children < 0) {
        $message = "❌ Please select at least 1 adult traveler.";
    } 
    else if (($adults + $children) > 100) {
        $message = "❌ Maximum 100 travelers allowed per booking.";
    }
    else if (empty($travel_date)) {
        $message = "❌ Please select a travel date.";
    } 
    else if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $travel_date)) {
        $message = "❌ Invalid date format.";
    } 
    else {
        $travel_timestamp = strtotime($travel_date);
        $today_timestamp = strtotime('today');
        
        if ($travel_timestamp < $today_timestamp) {
            $message = "❌ Travel date must be today or in the future.";
        } 
        else if ($travel_timestamp > strtotime('+2 years')) {
            $message = "❌ Travel date must be within 2 years.";
        }
        else if (!in_array($payment_method, array('card', 'upi', 'wallet', 'bank_transfer'))) {
            $message = "❌ Invalid payment method selected.";
        }
        else if (!in_array($package_type, array('budget', 'standard', 'luxury'))) {
            $message = "❌ Invalid package type selected.";
        } 
        else {
            try {
                $destCheck = KindoraDatabase::fetchOne("SELECT destination_id FROM destinations WHERE destination_id = :id", array(':id' => $destination_id));
                if (!$destCheck) {
                    $message = "❌ Selected destination not found.";
                } else {
                    try {
                        $pricing = KindoraDatabase::fetchOne("SELECT * FROM destination_pricing WHERE destination_id = :id AND season = 'standard'", array(':id' => $destination_id));
                        if (!$pricing) {
                            $message = "❌ Pricing not available for this destination.";
                        } else {
                            $price_column = ($package_type === 'budget') ? 'price_economy' : 'price_' . $package_type;
                            $base_price = floatval($pricing[$price_column] ?? 0);
                            
                            if ($base_price <= 0) {
                                $message = "❌ Invalid pricing for selected package. Price: " . $base_price;
                            } else {
                                $total_amount = ($base_price * $adults) + (($base_price * 0.5) * $children);
                                if (($adults + $children) >= 4) { $total_amount *= 0.9; }
                                
                                if ($total_amount <= 0 || $total_amount > 999999) {
                                    $message = "❌ Invalid total amount. Please try again.";
                                } else {
                                    try {
                                        $user_id = getCurrentUserId();
                                        if (!$user_id) {
                                            $message = "❌ Session expired. Please login again.";
                                        } else {
                                            $insertQuery = "INSERT INTO bookings (user_id, destination_id, guests, travel_date, total_amount, payment_method, special_requests, booking_date, status, payment_status, created_at) VALUES (:user_id, :destination_id, :guests, :travel_date, :total_amount, :payment_method, :special_requests, NOW(), 'pending', 'pending', NOW())";
                                            $executeResult = KindoraDatabase::execute($insertQuery, array(':user_id' => $user_id, ':destination_id' => $destination_id, ':guests' => ($adults + $children), ':travel_date' => $travel_date, ':total_amount' => round($total_amount, 2), ':payment_method' => $payment_method, ':special_requests' => htmlspecialchars($special_requests)));
                                            
                                            if ($executeResult) {
                                                $booking_id = KindoraDatabase::lastId();
                                                if ($booking_id) {
                                                    try {
                                                        $points = floor($total_amount / 10);
                                                        KindoraDatabase::execute("UPDATE users SET reward_points = COALESCE(reward_points, 0) + :points WHERE user_id = :user_id", array(':points' => $points, ':user_id' => $user_id));
                                                    } catch (Exception $e) { error_log("Reward points error: " . $e->getMessage()); }
                                                    
                                                    error_log("BOOKING OK, booking_id = " . intval($booking_id));
                                                    while (ob_get_level() > 0) { ob_end_clean(); }
                                                    $url = 'payment_process.php?booking_id=' . intval($booking_id) . '&method=' . urlencode($payment_method);
                                                    header('Location: ' . $url, true, 302);
                                                    exit;
                                                } else { $message = "❌ Failed to create booking. Please try again."; }
                                            } else { $message = "❌ Failed to create booking. Please try again."; }
                                        }
                                    } catch (Exception $e) {
                                        error_log("Booking error: " . $e->getMessage());
                                        $message = "❌ Booking error: " . htmlspecialchars($e->getMessage());
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Pricing fetch error: " . $e->getMessage());
                        $message = "❌ Error fetching pricing information.";
                    }
                }
            } catch (Exception $e) {
                error_log("Destination check error: " . $e->getMessage());
                $message = "❌ Error validating destination.";
            }
        }
    }
}

// Get all destinations for the dropdown
$destinations = array();
try {
    $destinations = KindoraDatabase::query("SELECT d.destination_id, d.name FROM destinations d JOIN destination_pricing dp ON d.destination_id = dp.destination_id WHERE d.type NOT IN ('Continent', '7 wonders', 'temp') AND (dp.price_economy > 0 OR dp.price_standard > 0 OR dp.price_luxury > 0) ORDER BY d.name ASC") ?: array();
} catch (Exception $e) {
    error_log("Destinations fetch error: " . $e->getMessage());
}

// Get selected destination name for form repopulation
$selected_destination_name = '';
if (!empty($form_data['destination_id'])) {
    foreach ($destinations as $dest) {
        if ($dest['destination_id'] == $form_data['destination_id']) {
            $selected_destination_name = $dest['name'];
            break;
        }
    }
}

/*
 *  --- CONFLICTING LOGIC BLOCK ---
 *  The following PHP code block was also found in the file.
 *  It appears to be a different, incomplete, or outdated implementation for handling bookings.
 *  As per instructions, it has been commented out to prevent fatal errors, but preserved for review.

    // booking.php
    require_once __DIR__ . '/config.php';
    session_start();

    // helper - change if your auth system differs
    function require_login() {
        if (!function_exists('isUserLoggedIn') || !isUserLoggedIn()) {
            // not logged in -- redirect to login
            header('Location: login.php?redirect=' . urlencode('packages.php'));
            exit;
        }
    }

    // CSRF: token stored in session when form was shown. If none, create fallback.
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }

    // Only accept POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo "Method not allowed";
        exit;
    }

    // Basic CSRF check (if token present in form)
    $csrf_ok = true;
    if (isset($_POST['csrf_token'])) {
        $csrf_ok = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
    if (!$csrf_ok) {
        error_log('CSRF check failed for booking attempt.');
        $_SESSION['booking_status'] = 'error_csrf';
        header('Location: packages.php');
        exit;
    }

    // Require login
    require_login();

    // get user id from your session or function - adapt if different
    $user_id = null;
    if (function_exists('currentUserId')) {
        $user_id = currentUserId();
    } else if (isset($_SESSION['user_id'])) {
        $user_id = intval($_SESSION['user_id']);
    }
    if (!$user_id) {
        // fallback: redirect to login
        header('Location: login.php?redirect=' . urlencode('packages.php'));
        exit;
    }

    // sanitize and validate inputs
    $package_id = isset($_POST['package_id']) && $_POST['package_id'] !== '' ? intval($_POST['package_id']) : null;
    $destination_id = isset($_POST['destination_id']) && $_POST['destination_id'] !== '' ? intval($_POST['destination_id']) : null;

    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $travel_date = isset($_POST['travel_date']) ? trim($_POST['travel_date']) : '';
    $guests = isset($_POST['guests']) ? intval($_POST['guests']) : 1;
    $package_option = isset($_POST['package_option']) ? trim($_POST['package_option']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // minimal validation
    $errors = [];
    if (empty($full_name)) $errors[] = "Full name required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required";
    if (empty($travel_date)) $errors[] = "Travel date required";
    if (!$package_id && !$destination_id) $errors[] = "Invalid booking target";
    if ($guests < 1) $guests = 1;

    if (!empty($errors)) {
        $_SESSION['booking_status'] = 'validation_error';
        $_SESSION['booking_errors'] = $errors;
        header('Location: packages.php');
        exit;
    }

    // compute a simple total_amount when possible
    $total_amount = 0.00;
    try {
        if ($package_id) {
            $row = KindoraDatabase::fetchOne("SELECT price FROM travel_packages WHERE package_id = ? AND is_active = 1", [$package_id]);
            if ($row && isset($row['price'])) {
                $price_single = floatval($row['price']);
                if ($package_option === 'economy') $price_single = round($price_single * 0.8, 2);
                elseif ($package_option === 'luxury') $price_single = round($price_single * 1.6, 2);
                $total_amount = $price_single * $guests;
            }
        } elseif ($destination_id) {
            $row = KindoraDatabase::fetchOne("SELECT price_standard FROM destination_pricing WHERE destination_id = ? LIMIT 1", [$destination_id]);
            if ($row && isset($row['price_standard'])) {
                $price_single = floatval($row['price_standard']);
                if ($package_option === 'economy') $price_single = round($price_single * 0.8, 2);
                elseif ($package_option === 'luxury') $price_single = round($price_single * 1.6, 2);
                $total_amount = $price_single * $guests;
            }
        }
    } catch (Exception $e) {
        error_log("Price lookup failed: " . $e->getMessage());
    }

    // Insert booking into bookings table (prepared)
    try {
        $now = date('Y-m-d H:i:s');
        $insertSql = "INSERT INTO bookings (user_id, destination_id, booking_date, travel_date, return_date, guests, total_amount, status, payment_status, created_at, updated_at, payment_method, special_requests)
                      VALUES (:user_id, :destination_id, :booking_date, :travel_date, NULL, :guests, :total_amount, 'pending', 'pending', :created_at, :updated_at, NULL, :special_requests)";
        
        $dest_to_insert = $destination_id;
        if (!$dest_to_insert && $package_id) {
            $possible = KindoraDatabase::fetchOne("SELECT destination_id FROM destination_pricing WHERE destination_id IN (SELECT destination_id FROM destination_pricing) LIMIT 1");
        }

        $params = [
            ':user_id' => $user_id,
            ':destination_id' => $dest_to_insert,
            ':booking_date' => date('Y-m-d'),
            ':travel_date' => date('Y-m-d', strtotime($travel_date)),
            ':guests' => $guests,
            ':total_amount' => number_format($total_amount, 2, '.', ''),
            ':created_at' => $now,
            ':updated_at' => $now,
            ':special_requests' => $notes
        ];

        KindoraDatabase::query($insertSql, $params);

        $_SESSION['booking_status'] = 'success';
        $_SESSION['booking_message'] = 'Booking received. Our team will contact you shortly.';
        header('Location: packages.php?booking=success');
        exit;
    } catch (Exception $e) {
        error_log('Booking insert failed: ' . $e->getMessage());
        $_SESSION['booking_status'] = 'error';
        $_SESSION['booking_message'] = 'Could not create booking. Please try again later.';
        header('Location: packages.php?booking=error');
        exit;
    }
*/
?>
<?php
/*
 * --- START OF HTML DOCUMENT ---
 * The file's HTML content has been moved here, after all PHP logic, to ensure the page renders correctly.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Journey - Kindora</title>
    <?php
    // The linkCSS function was not defined. Assuming it's in a helper file.
    // If not, this will need to be a direct <link> tag.
    if (function_exists('linkCSS')) {
        echo linkCSS('common');
    } else {
        // Fallback if the function doesn't exist.
        echo '<link href="assets/css/common_nav_footer.css" rel="stylesheet" />';
    }
    ?>
    <style>
        /* The extensive CSS block from the original file is preserved here */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Source Sans Pro', sans-serif; background: #f8f9fa; color: #333; }
        .booking-header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 40px 20px; text-align: center; }
        .booking-header h1 { font-size: 2em; margin-bottom: 10px; font-weight: 700; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .booking-card { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 40px; }
        .form-group { margin-bottom: 25px; position: relative; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #1e3c72; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 1em; font-family: inherit; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #ff6b35; box-shadow: 0 0 0 3px rgba(255,107,53,0.1); }
        .destination-list-button { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; background: white; cursor: pointer; text-align: left; font-size: 1em; transition: all 0.3s ease; display: flex; justify-content: space-between; align-items: center; }
        .destination-list-button:hover { border-color: #ff6b35; }
        .destination-list-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; max-height: 400px; overflow: hidden; z-index: 1000; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-top: -1px; }
        .destination-list-dropdown.show { display: flex; flex-direction: column; }
        .destination-search { padding: 12px 15px; border-bottom: 1px solid #ddd; background: #f9f9f9; }
        .destination-search input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.95em; }
        .destination-items { flex: 1; overflow-y: auto; padding: 8px 0; }
        .destination-item { padding: 12px 15px; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: all 0.2s ease; color: #333; display: flex; align-items: center; }
        .destination-item:hover { background: #f5f5f5; padding-left: 20px; }
        .destination-item.hidden { display: none; }
        .destination-item.selected { background: #fffbf0; border-left: 4px solid #ff6b35; padding-left: 11px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .package-select { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-top: 10px; }
        .package-option { padding: 15px; border: 2px solid #ddd; border-radius: 5px; cursor: pointer; transition: all 0.3s ease; text-align: center; }
        .package-option:hover { border-color: #ff6b35; background: #fff5f0; }
        .package-option label { cursor: pointer; margin: 0; display: block; }
        .price-summary { background: #f9f9f9; padding: 20px; border-radius: 5px; border-left: 4px solid #ff6b35; margin: 25px 0; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.95em; }
        .summary-row.total { font-size: 1.3em; font-weight: 700; color: #1e3c72; border-top: 1px solid #ddd; padding-top: 15px; margin-top: 15px; }
        .message { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: 500; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .submit-btn { width: 100%; padding: 15px; background: #1e3c72; color: white; border: none; border-radius: 5px; font-size: 1.1em; font-weight: 700; cursor: pointer; transition: all 0.3s ease; }
        .submit-btn:hover { background: #2a5298; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); }
        @media (max-width: 768px) { .booking-card { padding: 20px; } .form-row, .form-row-3, .package-select { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php //include 'includes/navbar.php'; // Commented out: Navbar is already in header.php ?>

    <div class="booking-header">
        <h1>🎫 Book Your Journey</h1>
        <p>Secure your spot on an unforgettable adventure</p>
    </div>

    <div class="container">
        <div class="booking-card">
            <?php if (!empty($message)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="booking.php" id="bookingForm">
                <!-- The entire HTML form from the original file is preserved here -->
                <div class="form-group">
                    <label>🌍 Select Destination *</label>
                    <div style="position: relative;">
                        <button type="button" class="destination-list-button" id="destinationButton" onclick="toggleDestinationList(event)">
                            <span id="destinationButtonText"><?php echo !empty($selected_destination_name) ? htmlspecialchars($selected_destination_name) : 'Choose a destination...'; ?></span>
                            <span id="dropdownArrow">▼</span>
                        </button>
                        <div class="destination-list-dropdown" id="destinationListDropdown">
                            <div class="destination-search"><input type="text" id="destinationSearchInput" placeholder="🔍 Search destinations..." onkeyup="filterDestinations()"></div>
                            <div class="destination-items" id="destinationItems">
                                <?php if (count($destinations) > 0): ?>
                                    <?php foreach ($destinations as $dest): ?>
                                        <div class="destination-item <?php echo ($form_data['destination_id'] == $dest['destination_id']) ? 'selected' : ''; ?>" data-id="<?php echo $dest['destination_id']; ?>" onclick="selectDestination(<?php echo $dest['destination_id']; ?>, '<?php echo htmlspecialchars($dest['name']); ?>')">
                                            <span><?php echo htmlspecialchars($dest['name']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div style="padding: 20px; text-align: center; color: #999;">No destinations available</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="destination_id" id="destination_id" value="<?php echo $form_data['destination_id']; ?>" required>
                    <small style="color: #666; margin-top: 5px; display: block;">Click to see all destinations, type to filter</small>
                </div>
                <div class="form-group">
                    <label for="travel_date">📅 Travel Date *</label>
                    <input type="date" name="travel_date" id="travel_date" value="<?php echo htmlspecialchars($form_data['travel_date']); ?>" required min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+2 years')); ?>">
                </div>
                <div class="form-group">
                    <label>👥 Number of Travelers *</label>
                    <div class="form-row-3">
                        <div>
                            <label for="adults">Adults</label>
                            <input type="number" name="adults" id="adults" value="<?php echo max(1, $form_data['adults']); ?>" min="1" max="50" required onchange="updatePrice()">
                        </div>
                        <div>
                            <label for="children">Children (0-12)</label>
                            <input type="number" name="children" id="children" value="<?php echo $form_data['children']; ?>" min="0" max="50" onchange="updatePrice()">
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <div style="width: 100%;"><label for="total">Total</label><input type="number" id="total" value="<?php echo max(1, $form_data['adults']) + $form_data['children']; ?>" readonly></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>🎫 Select Package Type *</label>
                    <div class="package-select">
                        <div class="package-option"><input type="radio" name="package_type" id="budget" value="budget" <?php echo ($form_data['package_type'] === 'budget') ? 'checked' : ''; ?> required onchange="updatePrice()"><label for="budget"><strong>Budget</strong><br><span id="price_budget">₹0</span></label></div>
                        <div class="package-option"><input type="radio" name="package_type" id="standard" value="standard" <?php echo ($form_data['package_type'] === 'standard' || empty($form_data['package_type'])) ? 'checked' : ''; ?> required onchange="updatePrice()"><label for="standard"><strong>Standard</strong><br><span id="price_standard">₹0</span></label></div>
                        <div class="package-option"><input type="radio" name="package_type" id="luxury" value="luxury" <?php echo ($form_data['package_type'] === 'luxury') ? 'checked' : ''; ?> required onchange="updatePrice()"><label for="luxury"><strong>Luxury</strong><br><span id="price_luxury">₹0</span></label></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="payment_method">💳 Payment Method *</label>
                    <select name="payment_method" id="payment_method" required>
                        <option value="">-- Select payment method --</option>
                        <option value="upi" <?php echo ($form_data['payment_method'] === 'upi') ? 'selected' : ''; ?>>📱 UPI</option>
                        <option value="card" <?php echo ($form_data['payment_method'] === 'card') ? 'selected' : ''; ?>>💳 Debit/Credit Card</option>
                        <option value="wallet" <?php echo ($form_data['payment_method'] === 'wallet') ? 'selected' : ''; ?>>🏦 Wallet</option>
                        <option value="bank_transfer" <?php echo ($form_data['payment_method'] === 'bank_transfer') ? 'selected' : ''; ?>>🏧 Bank Transfer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="special_requests">📝 Special Requests (Optional)</label>
                    <textarea name="special_requests" id="special_requests" rows="4" placeholder="Any special requirements?" maxlength="500"><?php echo htmlspecialchars($form_data['special_requests']); ?></textarea>
                    <small style="color: #666;">Max 500 characters</small>
                </div>
                <div class="price-summary">
                    <p><strong>Price Breakdown (from Database):</strong></p>
                    <div class="summary-row"><span>Base Price per Person:</span><span id="basePrice">₹0</span></div>
                    <div class="summary-row"><span>Adults (<span id="adultCount">1</span>):</span><span id="adultCost">₹0</span></div>
                    <div class="summary-row" id="childrenRow" style="display: none;"><span>Children (<span id="childCount">0</span>):</span><span id="childCost">₹0</span></div>
                    <div class="summary-row" id="discountRow" style="display: none;"><span>Group Discount (10%):</span><span id="discountAmount" style="color: #27ae60;">-₹0</span></div>
                    <div class="summary-row total"><span>Total Amount:</span><span id="totalAmount">₹0</span></div>
                </div>
                <button type="submit" class="submit-btn">💳 Proceed to Payment</button>
            </form>

            <script>
                // The extensive JavaScript block from the original file is preserved here
                const destPricing = <?php try { $pricing_data = array(); $all_pricing = KindoraDatabase::query("SELECT destination_id, price_economy, price_standard, price_luxury FROM destination_pricing") ?: array(); foreach ($all_pricing as $p) { $pricing_data[$p['destination_id']] = array('budget' => floatval($p['price_economy']), 'standard' => floatval($p['price_standard']), 'luxury' => floatval($p['price_luxury'])); } echo json_encode($pricing_data); } catch (Exception $e) { echo '{}'; } ?>;
                function toggleDestinationList(e) { e.preventDefault(); const dropdown = document.getElementById('destinationListDropdown'); const arrow = document.getElementById('dropdownArrow'); dropdown.classList.toggle('show'); arrow.style.transform = dropdown.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0)'; if (dropdown.classList.contains('show')) { document.getElementById('destinationSearchInput').focus(); } }
                function filterDestinations() { const searchTerm = document.getElementById('destinationSearchInput').value.toLowerCase(); const items = document.querySelectorAll('.destination-item'); items.forEach(item => { const name = item.textContent.toLowerCase(); item.classList.toggle('hidden', !name.includes(searchTerm)); }); }
                function selectDestination(id, name) { document.getElementById('destination_id').value = id; document.getElementById('destinationButtonText').textContent = name; document.getElementById('destinationListDropdown').classList.remove('show'); document.getElementById('dropdownArrow').style.transform = 'rotate(0)'; updatePrice(); }
                function updatePrice() { const adultsInput = document.getElementById('adults'); const childrenInput = document.getElementById('children'); let adults = parseInt(adultsInput.value) || 0; if (adults < 1) { adultsInput.value = 1; adults = 1; } const destId = parseInt(document.getElementById('destination_id').value); const packageType = document.querySelector('input[name="package_type"]:checked').value; const children = parseInt(childrenInput.value) || 0; const total = adults + children; let basePrice = 0; if (destId && destPricing[destId]) { basePrice = destPricing[destId][packageType] || 0; } document.getElementById('total').value = total; document.getElementById('adultCount').textContent = adults; document.getElementById('childCount').textContent = children; document.getElementById('basePrice').textContent = '₹' + basePrice.toLocaleString(); const adultCost = basePrice * adults; const childCost = (basePrice * 0.5) * children; let totalAmount = adultCost + childCost; document.getElementById('adultCost').textContent = '₹' + adultCost.toLocaleString(); if (children > 0) { document.getElementById('childrenRow').style.display = 'flex'; document.getElementById('childCost').textContent = '₹' + (basePrice * 0.5 * children).toLocaleString(); } else { document.getElementById('childrenRow').style.display = 'none'; } if (total >= 4) { const discount = totalAmount * 0.1; totalAmount *= 0.9; document.getElementById('discountRow').style.display = 'flex'; document.getElementById('discountAmount').textContent = '-₹' + Math.round(discount).toLocaleString(); } else { document.getElementById('discountRow').style.display = 'none'; } document.getElementById('totalAmount').textContent = '₹' + Math.round(totalAmount).toLocaleString(); document.getElementById('price_budget').textContent = destId && destPricing[destId] ? '₹' + destPricing[destId]['budget'].toLocaleString() : '₹0'; document.getElementById('price_standard').textContent = destId && destPricing[destId] ? '₹' + destPricing[destId]['standard'].toLocaleString() : '₹0'; document.getElementById('price_luxury').textContent = destId && destPricing[destId] ? '₹' + destPricing[destId]['luxury'].toLocaleString() : '₹0'; }
                document.addEventListener('click', function(e) { if (!e.target.closest('.form-group')) { document.getElementById('destinationListDropdown').classList.remove('show'); document.getElementById('dropdownArrow').style.transform = 'rotate(0)'; } });
                updatePrice();
            </script>
        </div>
    </div>

    <?php
    // The footer is included once at the end of the body.
    include 'includes/footer.php';
    ?>
</body>
</html>
