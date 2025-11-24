<?php
/**
 * KINDORA BOOKING PAGE - FINAL POLISHED DESIGN
 * - Uses a two-column form grid so labels align with fields
 * - Enlarged special requests input
 * - Clean, consistent spacing and responsive behavior
 * - Keeps all original PHP logic intact
 * - FINAL VERSION WITH PREFILL AND LOGIN REDIRECT FIXED
 */
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in - FIX: NOW HANDLES RETURN URL WITH DESTINATION
if (!isUserLoggedIn()) {
    $destination_id = isset($_GET['destination_id']) ? intval($_GET['destination_id']) : 0;
    $redirect_url = 'booking.php';
    if ($destination_id > 0) {
        $redirect_url .= '?destination_id=' . $destination_id;
    }
    header('Location: /Kindora/login.php?return=' . urlencode($redirect_url));
    exit;
}

$success = false;
$message = '';
$currencyOptions = getCurrencyOptions();
$activeCurrency = getCurrencyPreference();

// FIX: Initialize $form_data with defaults FIRST
$form_data = array(
    'destination_id' => 0,
    'travel_date' => '',
    'adults' => 1,
    'children' => 0,
    'package_type' => 'standard',
    'payment_method' => '',
    'special_requests' => ''
);

// FIX: PREFILL FROM URL if destination_id in GET and NOT POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['destination_id'])) {
    $form_data['destination_id'] = intval($_GET['destination_id']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate inputs
    $destination_id = isset($_POST['destination_id']) ? intval($_POST['destination_id']) : 0;
    $travel_date = isset($_POST['travel_date']) ? trim($_POST['travel_date']) : '';
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 0;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $package_type = isset($_POST['package_type']) ? trim($_POST['package_type']) : 'standard';
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    $special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';
    
    // Store form data for repopulation
    $form_data = array(
        'destination_id' => $destination_id,
        'travel_date' => $travel_date,
        'adults' => $adults,
        'children' => $children,
        'package_type' => $package_type,
        'payment_method' => $payment_method,
        'special_requests' => $special_requests
    );
    
    // VALIDATION
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
            // Check if destination exists and get pricing
            try {
                $destCheck = KindoraDatabase::fetchOne(
                    "SELECT destination_id FROM destinations WHERE destination_id = :id",
                    array(':id' => $destination_id)
                );
                
                if (!$destCheck) {
                    $message = "❌ Selected destination not found.";
                } else {
                    // GET PRICING FROM DATABASE
                    try {
                        $pricing = KindoraDatabase::fetchOne(
                            "SELECT * FROM destination_pricing 
                             WHERE destination_id = :id AND season = 'standard'",
                            array(':id' => $destination_id)
                        );
                        
                        if (!$pricing) {
                            $message = "❌ Pricing not available for this destination.";
                        } else {
                            // FIX: Map package type to correct database column
                            // budget → price_economy, standard → price_standard, luxury → price_luxury
                            if ($package_type === 'budget') {
                                $price_column = 'price_economy';
                            } else {
                                $price_column = 'price_' . $package_type;
                            }
                            
                            $base_price = floatval($pricing[$price_column] ?? 0);
                            
                            if ($base_price <= 0) {
                                $message = "❌ Invalid pricing for selected package. Price: " . $base_price;
                            } else {
                                // Calculate total cost
                                $adult_cost = $base_price * $adults;
                                $child_cost = ($base_price * 0.5) * $children;
                                $total_amount = $adult_cost + $child_cost;
                                
                                // Apply group discount
                                $discount_applied = false;
                                if (($adults + $children) >= 4) {
                                    $total_amount *= 0.9;
                                    $discount_applied = true;
                                }
                                
                                // Validate total
                                if ($total_amount <= 0 || $total_amount > 999999) {
                                    $message = "❌ Invalid total amount. Please try again.";
                                } else {
                                    // Create booking
                                    try {
                                        $user_id = getCurrentUserId();
                                        
                                        if (!$user_id) {
                                            $message = "❌ Session expired. Please login again.";
                                        } else {
                                            $total_guests = $adults + $children;
                                            
                                            // INSERT booking into database
                                            $insertQuery = "INSERT INTO bookings 
                                                (user_id, destination_id, guests, travel_date, total_amount, 
                                                 payment_method, special_requests, booking_date, status, 
                                                 payment_status, created_at) 
                                                VALUES 
                                                (:user_id, :destination_id, :guests, :travel_date, :total_amount, 
                                                 :payment_method, :special_requests, NOW(), 'pending', 'pending', NOW())";
                                            
                                            $executeResult = KindoraDatabase::execute($insertQuery, array(
                                                ':user_id' => $user_id,
                                                ':destination_id' => $destination_id,
                                                ':guests' => $total_guests,
                                                ':travel_date' => $travel_date,
                                                ':total_amount' => round($total_amount, 2),
                                                ':payment_method' => $payment_method,
                                                ':special_requests' => htmlspecialchars($special_requests)
                                            ));
                                            
                                            if ($executeResult) {
                                                $booking_id = KindoraDatabase::lastId();
                                                
                                                if ($booking_id) {
                                                    // Update reward points
                                                    try {
                                                        $points = floor($total_amount / 10);
                                                        $updateQuery = "UPDATE users SET reward_points = COALESCE(reward_points, 0) + :points WHERE user_id = :user_id";
                                                        KindoraDatabase::execute($updateQuery, array(
                                                            ':points' => $points,
                                                            ':user_id' => $user_id
                                                        ));
                                                    } catch (Exception $e) {
                                                        error_log("Reward points error: " . $e->getMessage());
                                                    }
                                                    
                                                   // --- Clean server-side redirect (includes payment method) ---
                                                    error_log("BOOKING OK, booking_id = " . intval($booking_id));
                                                    
                                                    // Ensure no buffered output is sent
                                                    while (ob_get_level() > 0) { ob_end_clean(); }
                                                    
                                                    // Build absolute/site-rooted URL and include chosen payment method
                                                    $url = 'payment_process.php?booking_id=' . intval($booking_id) . '&method=' . urlencode($payment_method);
                                                    
                                                    // Redirect and stop execution
                                                    header('Location: ' . $url, true, 302);
                                                    exit;
                                                    
                                                } else {
                                                    $message = "❌ Failed to create booking. Please try again.";
                                                }
                                            } else {
                                                $message = "❌ Failed to create booking. Please try again.";
                                            }
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

// Get all destinations
$destinations = array();
try {
    // --- THIS IS THE CORRECTED LINE ---
    // It now joins with destination_pricing to ensure only destinations with a valid price are shown
    $destinations = KindoraDatabase::query(
        "SELECT d.destination_id, d.name 
         FROM destinations d
         JOIN destination_pricing dp ON d.destination_id = dp.destination_id
         WHERE d.type NOT IN ('Continent') 
         AND (dp.price_economy > 0 OR dp.price_standard > 0 OR dp.price_luxury > 0)
         ORDER BY d.name ASC"
    ) ?: array();
} catch (Exception $e) {
    error_log("Destinations fetch error: " . $e->getMessage());
}

// Get selected destination name
$selected_destination_name = '';
if (!empty($form_data['destination_id'])) {
    foreach ($destinations as $dest) {
        if ($dest['destination_id'] == $form_data['destination_id']) {
            $selected_destination_name = $dest['name'];
            break;
        }
    }
}

// Include header once (navbar is in this include)
require_once 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Journey - Kindora</title>
    <?php echo linkCSS('common'); ?>
    <style>
        :root{
            --nav-height: 70px;
            --max-width: 1100px;
            --accent: #ff6b35;
            --primary: #113863;
            --muted: #6b7280;
            --card-bg: #fff;
            --surface: #f8f9fb;
            --radius: 10px;
        }
        html,body{height:100%}
        body{
            margin:0;
            padding-top:var(--nav-height);
            font-family: Inter, "Source Sans Pro", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: var(--surface);
            color: #111827;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }

        .container {
            max-width: var(--max-width);
            margin: 26px auto;
            padding: 0 20px;
        }

        .booking-header{
            background: linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);
            color:#fff;
            padding:28px;
            border-radius:12px;
            box-shadow:0 8px 30px rgba(17,56,99,0.08);
            margin-bottom:18px;
            text-align:center;
        }
        .booking-header h1{margin:0;font-size:1.7rem;letter-spacing:0.2px}
        .booking-header p{margin:6px 0 0;color:rgba(255,255,255,0.92)}

        .card{
            background:var(--card-bg);
            border-radius:var(--radius);
            padding:22px;
            box-shadow:0 10px 30px rgba(12,38,63,0.06);
        }

        /* layout: left form + right price summary */
        .grid {
            display:grid;
            grid-template-columns: 1fr 360px;
            gap:28px;
            align-items:start;
        }

        /* Aligned form: two-column grid inside left column
           first column: label, second: field
        */
        .aligned-form {
            display:grid;
            grid-template-columns: 220px 1fr;
            column-gap:20px;
            row-gap:14px;
            align-items:center;
        }

        /* For elements that should span both columns */
        .aligned-form .full {
            grid-column: 1 / -1;
        }

        label.form-label{
            display:block;
            font-weight:600;
            color:var(--primary);
            font-size:0.95rem;
        }

        /* inputs styling */
        input[type="text"], input[type="date"], input[type="number"], select, textarea, .destination-list-button {
            width:100%;
            padding:10px 12px;
            border:1px solid #e6edf3;
            border-radius:8px;
            font-size:0.95rem;
            background:#fff;
            box-sizing:border-box;
        }

        /* Destination: button & dropdown */
        .destination-list-button {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        #destinationButtonText {
            flex: 1;
            text-align: center;
        }

        .destination-list {
            position:relative;
        }
        .destination-dropdown {
            position:absolute;
            top:calc(100% + 8px);
            left:0;
            right:0;
            background:#fff;
            border:1px solid #e8eef6;
            border-radius:10px;
            box-shadow:0 18px 40px rgba(12,38,63,0.08);
            z-index:99999;
            display:none;
            max-height:420px;
            overflow:hidden;
        }
        .destination-dropdown.show { display:block; }
        .destination-search {
            padding:12px;
            border-bottom:1px solid #f1f5f9;
            background:#fbfdff;
            display:flex;
            justify-content:center;
        }
        .destination-search input{
            width:98%;
            max-width:820px;
            padding:12px 14px;
            border-radius:8px;
            border:1px solid #e6eef6;
            font-size:1rem;
        }
        .destination-list-items{
            max-height:300px;
            overflow:auto;
        }
        .destination-item{
            padding:10px 14px;
            border-bottom:1px solid #f5f7fa;
            cursor:pointer;
            display:flex;
            align-items:center;
            gap:12px;
        }
        .destination-item:hover{background:#fbfbfb;padding-left:14px}
        .destination-item.selected{background:#fff7f0;border-left:4px solid var(--accent);padding-left:10px}

        /* Traveler inputs - group inside field cell */
        .traveler-row {
            display:grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap:12px;
            align-items:end;
        }
        .traveler-row input { padding:10px }

        /* package options - span both columns */
        .package-grid {
            display:flex;
            gap:12px;
            align-items:stretch;
        }
        .package-tile {
            flex:1 1 0;
            border:1px solid #e6edf3;
            border-radius:10px;
            padding:12px;
            text-align:center;
            cursor:pointer;
            background:#fff;
            transition: all 0.16s ease;
        }
        .package-tile:hover{transform:translateY(-4px);border-color:var(--accent);background:#fff8f3}
        .package-tile input{margin-bottom:8px}
        .package-tile input[type="radio"]:checked {
            border-color: var(--accent);
        }
        .package-tile:has(input[type="radio"]:checked) {
            border-color: var(--accent);
            background-color: #fff8f3;
        }

        /* price summary */
        .summary {
            border-radius:10px;
            background:#fff;
            padding:16px;
            border:1px solid #eef3f8;
        }
        .summary .row { display:flex; justify-content:space-between; padding:8px 0; color:#111827 }
        .summary .row.total { border-top:1px dashed #e9eef6; padding-top:12px; font-weight:700; color:var(--primary); }

        /* special requests bigger */
        textarea#special_requests {
            min-height: 140px;
            resize: vertical;
            font-size:0.95rem;
            padding:12px;
        }

        /* full-width action area */
        .actions {
            margin-top:18px;
            display:block;
        }
        .btn {
            display:inline-block;
            padding:14px 18px;
            background:var(--primary);
            color:#fff;
            border-radius:10px;
            border:none;
            width:100%;
            font-weight:700;
            font-size:1rem;
            cursor:pointer;
            box-shadow:0 12px 30px rgba(17,56,99,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 8px;   
        }
        .btn:hover{background:#163c66;transform:translateY(-2px)}

        /* message */
        .message{padding:12px;border-radius:8px;margin-bottom:12px}
        .message.error{background:#fff2f2;color:#7b2626;border:1px solid #ffd6d6}

        @media (max-width: 980px){
            .grid{grid-template-columns:1fr; }
            .aligned-form{grid-template-columns: 1fr; }
            .aligned-form .full{grid-column: auto}
            .destination-search input{width:100%;max-width:none}
            .traveler-row{grid-template-columns:1fr 1fr}
            .package-grid{flex-direction:column}
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="booking-header">
            <h1>🎫 Book Your Journey</h1>
            <p>Secure your spot on an unforgettable adventure</p>
        </div>

        <div class="card">
            <?php if (!empty($message)): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="grid">
                <!-- LEFT: form -->
                <div>
                    <form method="POST" action="booking.php" id="bookingForm" novalidate>
                        <div class="aligned-form">
                            <!-- Destination (field spans) -->
                            <div class="full">
                                <label class="form-label">🌍 Select Destination *</label>
                                <div class="destination-list">
                                    <button type="button" id="destinationButton" class="destination-list-button" onclick="toggleDestinationList(event)" aria-expanded="false" aria-controls="destinationDropdown">
                                        <span id="destinationButtonText"><?php echo !empty($selected_destination_name) ? htmlspecialchars($selected_destination_name) : 'Choose a destination...'; ?></span>
                                        <span id="dropdownArrow">▼</span>
                                    </button>

                                    <div id="destinationDropdown" class="destination-dropdown" aria-hidden="true">
                                        <div class="destination-search">
                                            <input type="text" id="destinationSearchInput" placeholder="🔍 Search destinations..." onkeyup="filterDestinations()" aria-label="Search destinations">
                                        </div>
                                        <div class="destination-list-items" id="destinationItems">
                                            <?php if (count($destinations) > 0): ?>
                                                <?php foreach ($destinations as $dest): ?>
                                                    <div class="destination-item <?php echo ($form_data['destination_id'] == $dest['destination_id']) ? 'selected' : ''; ?>"
                                                         data-id="<?php echo $dest['destination_id']; ?>"
                                                         onclick="selectDestination(<?php echo $dest['destination_id']; ?>, '<?php echo htmlspecialchars($dest['name'], ENT_QUOTES); ?>')">
                                                        <span><?php echo htmlspecialchars($dest['name']); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div style="padding: 16px; text-align:center; color:#999">No destinations available</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="destination_id" id="destination_id" value="<?php echo $form_data['destination_id']; ?>" required>
                            </div>

                            <!-- Travel date -->
                            <label class="form-label">📅 Travel Date *</label>
                            <div>
                                <input type="date" name="travel_date" id="travel_date" value="<?php echo htmlspecialchars($form_data['travel_date']); ?>" required min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+2 years')); ?>">
                            </div>

                            <!-- Number of travelers -->
                            <label class="form-label">👥 Number of Travelers *</label>
                            <div>
                                <div class="traveler-row">
                                    <div>
                                        <label style="font-weight:600;font-size:0.85rem;color:var(--muted);margin-bottom:6px;display:block">Adults</label>
                                        <input type="number" name="adults" id="adults" value="<?php echo max(1, intval($form_data['adults'])); ?>" min="1" max="50" required onchange="updatePrice()">
                                    </div>
                                    <div>
                                        <label style="font-weight:600;font-size:0.85rem;color:var(--muted);margin-bottom:6px;display:block">Children (0-12)</label>
                                        <input type="number" name="children" id="children" value="<?php echo intval($form_data['children']); ?>" min="0" max="50" onchange="updatePrice()">
                                    </div>
                                    <div>
                                        <label style="font-weight:600;font-size:0.85rem;color:var(--muted);margin-bottom:6px;display:block">Total</label>
                                        <input type="number" id="total" value="<?php echo max(1, intval($form_data['adults'])) + intval($form_data['children']); ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Package type (span both columns) -->
                            <div class="full">
                                <label class="form-label">🎫 Select Package Type *</label>
                                <div class="package-grid" style="margin-top:8px">
                                    <div class="package-tile">
                                        <input type="radio" name="package_type" id="budget" value="budget" <?php echo ($form_data['package_type'] === 'budget') ? 'checked' : ''; ?> onchange="updatePrice()">
                                        <div style="font-weight:700;margin-top:6px">Budget</div>
                                        <div id="price_budget" style="color:var(--muted);margin-top:6px"><?php echo formatCurrencyAmount(0, $activeCurrency); ?></div>
                                    </div>
                                    <div class="package-tile">
                                        <input type="radio" name="package_type" id="standard" value="standard" <?php echo ($form_data['package_type'] === 'standard' || empty($form_data['package_type'])) ? 'checked' : ''; ?> onchange="updatePrice()">
                                        <div style="font-weight:700;margin-top:6px">Standard</div>
                                        <div id="price_standard" style="color:var(--muted);margin-top:6px"><?php echo formatCurrencyAmount(0, $activeCurrency); ?></div>
                                    </div>
                                    <div class="package-tile">
                                        <input type="radio" name="package_type" id="luxury" value="luxury" <?php echo ($form_data['package_type'] === 'luxury') ? 'checked' : ''; ?> onchange="updatePrice()">
                                        <div style="font-weight:700;margin-top:6px">Luxury</div>
                                        <div id="price_luxury" style="color:var(--muted);margin-top:6px"><?php echo formatCurrencyAmount(0, $activeCurrency); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment method -->
                            <label class="form-label">💳 Payment Method *</label>
                            <div>
                                <select name="payment_method" id="payment_method" required>
                                    <option value="">-- Select payment method --</option>
                                    <option value="upi" <?php echo ($form_data['payment_method'] === 'upi') ? 'selected' : ''; ?>>📱 UPI</option>
                                    <option value="card" <?php echo ($form_data['payment_method'] === 'card') ? 'selected' : ''; ?>>💳 Debit/Credit Card</option>
                                    <option value="wallet" <?php echo ($form_data['payment_method'] === 'wallet') ? 'selected' : ''; ?>>🏦 Wallet</option>
                                    <option value="bank_transfer" <?php echo ($form_data['payment_method'] === 'bank_transfer') ? 'selected' : ''; ?>>🏧 Bank Transfer</option>
                                </select>
                            </div>

                            <!-- Special requests (bigger) -->
                            <label class="form-label">📝 Special Requests (Optional)</label>
                            <div>
                                <textarea name="special_requests" id="special_requests" maxlength="500" placeholder="Any special requirements?"><?php echo htmlspecialchars($form_data['special_requests']); ?></textarea>
                                <div style="font-size:0.85rem;color:var(--muted);margin-top:6px">Max 500 characters</div>
                            </div>

                        </div> <!-- aligned-form -->

                        <!-- keep form open for JS to use -->
                    </form>
                </div>

                <!-- RIGHT: price summary -->
                <div>
                    <div style="display:flex; gap:8px; align-items:center; justify-content:flex-end; margin-bottom:10px;">
                        <label for="bookingCurrencySelect" style="font-weight:600; color:#333;">Currency:</label>
                        <select id="bookingCurrencySelect" style="padding:6px 10px; border:1px solid #d0d0d0; border-radius:6px;">
                            <?php foreach ($currencyOptions as $code => $meta): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>" <?php echo $code === $activeCurrency ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($code); ?> (<?php echo htmlspecialchars($meta['symbol']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="summary" aria-live="polite">
                        <div style="font-weight:700;margin-bottom:8px">Price Breakdown (from Database):</div>
                        <div class="row"><div>Base Price per Person:</div><div id="basePrice"><?php echo formatCurrencyAmount(0, $activeCurrency); ?></div></div>
                        <div class="row"><div>Adults (<span id="adultCount">1</span>):</div><div id="adultCost"><?php echo formatCurrencyAmount(0, $activeCurrency); ?></div></div>
                        <div class="row" id="childrenRow" style="display:none"><div>Children (<span id="childCount">0</span>):</div><div id="childCost"><?php echo formatCurrencyAmount(0, $activeCurrency); ?></div></div>
                        <div class="row" id="discountRow" style="display:none"><div>Group Discount (10%):</div><div id="discountAmount" style="color:#0a9a61">-<?php echo formatCurrencyAmount(0, $activeCurrency); ?></div></div>
                        <div class="row total"><div>Total Amount:</div><div id="totalAmount"><?php echo formatCurrencyAmount(0, $activeCurrency); ?></div></div>
                    </div>
                </div>
            </div> <!-- .grid -->

            <!-- full-width actions -->
            <div class="actions">
                <!-- We'll submit via a separate small form (cloned hidden inputs) so big button spans full width -->
                <form method="POST" action="booking.php" id="bookingFormSubmit" onsubmit="return submitClone()">
                    <input type="hidden" name="destination_id" id="destination_id_clone" value="<?php echo $form_data['destination_id']; ?>">
                    <input type="hidden" name="travel_date" id="travel_date_clone" value="<?php echo htmlspecialchars($form_data['travel_date']); ?>">
                    <input type="hidden" name="adults" id="adults_clone" value="<?php echo max(1, intval($form_data['adults'])); ?>">
                    <input type="hidden" name="children" id="children_clone" value="<?php echo intval($form_data['children']); ?>">
                    <input type="hidden" name="package_type" id="package_type_clone" value="<?php echo htmlspecialchars($form_data['package_type']); ?>">
                    <input type="hidden" name="payment_method" id="payment_method_clone" value="<?php echo htmlspecialchars($form_data['payment_method']); ?>">
                    <input type="hidden" name="special_requests" id="special_requests_clone" value="<?php echo htmlspecialchars($form_data['special_requests']); ?>">

                    <button type="submit" class="btn">💳 Proceed to Payment</button>
                </form>
            </div>
        </div> <!-- card -->
    </div> <!-- container -->

    <script>
        // pricing data - FIXED PHP SYNTAX
        const destPricing = <?php 
            try {
                $pricing_data = array();
                $all_pricing = KindoraDatabase::query("SELECT destination_id, price_economy, price_standard, price_luxury, currency FROM destination_pricing") ?: array();
                foreach ($all_pricing as $p) {
                    $pricing_data[$p['destination_id']] = array(
                        'currency' => $p['currency'] ?? 'USD',
                        'budget' => floatval($p['price_economy']),
                        'standard' => floatval($p['price_standard']),
                        'luxury' => floatval($p['price_luxury'])
                    );
                }
                echo json_encode($pricing_data);
            } catch (Exception $e) {
                echo '{}';
            }
        ?>;

        const currencyRates = <?php echo json_encode($currencyOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        let activeCurrency = '<?php echo $activeCurrency; ?>';

        function convertAmount(amount, fromCurrency, toCurrency) {
            const from = currencyRates[fromCurrency];
            const to = currencyRates[toCurrency];
            if (!from || !to) return amount;
            if (fromCurrency === toCurrency) return amount;
            let usdAmount = amount;
            if (fromCurrency !== 'USD') {
                const fromRate = parseFloat(from.rate_to_usd || 0);
                if (!fromRate) return amount;
                usdAmount = amount * fromRate;
            }
            if (toCurrency === 'USD') {
                return usdAmount;
            }
            const targetRate = parseFloat(to.rate_to_usd || 0);
            if (!targetRate) {
                return usdAmount;
            }
            return usdAmount / targetRate;
        }

        function formatAmount(amount, currency) {
            const symbol = currencyRates[currency]?.symbol || (currency + ' ');
            return symbol + Number(amount).toFixed(2);
        }

        function persistCurrency(code) {
            fetch('api/set-currency.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({currency: code})
            }).catch(()=>{});
        }

        function toggleDestinationList(e){
            e.preventDefault();
            const dd = document.getElementById('destinationDropdown');
            const btn = document.getElementById('destinationButton');
            const arrow = document.getElementById('dropdownArrow');
            const open = dd.classList.toggle('show');
            dd.setAttribute('aria-hidden', !open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            arrow.style.transform = open ? 'rotate(180deg)' : 'rotate(0)';
            if(open) document.getElementById('destinationSearchInput').focus();
        }

        function filterDestinations(){
            const q = document.getElementById('destinationSearchInput').value.toLowerCase();
            document.querySelectorAll('.destination-item').forEach(it=>{
                const name = it.textContent.toLowerCase();
                it.style.display = name.includes(q) ? 'flex' : 'none';
            });
        }

        function selectDestination(id, name){
            document.getElementById('destination_id').value = id;
            document.getElementById('destinationButtonText').textContent = name;
            document.getElementById('destinationDropdown').classList.remove('show');
            document.getElementById('destinationDropdown').setAttribute('aria-hidden','true');
            document.getElementById('destinationButton').setAttribute('aria-expanded','false');
            document.getElementById('dropdownArrow').style.transform = 'rotate(0)';
            // keep clone in sync
            if(document.getElementById('destination_id_clone')) document.getElementById('destination_id_clone').value = id;
            updatePrice();
        }

        function updatePrice(){
            const adultsEl = document.getElementById('adults');
            const childrenEl = document.getElementById('children');

            let adults = parseInt(adultsEl.value, 10) || 0;
            if(adults < 1){ adults = 1; adultsEl.value = 1; }

            const destId = parseInt(document.getElementById('destination_id').value, 10) || 0;
            const packageTypeEl = document.querySelector('input[name="package_type"]:checked');
            const packageType = packageTypeEl ? packageTypeEl.value : 'standard';
            const children = parseInt(childrenEl.value, 10) || 0;
            const total = adults + children;

            const priceMeta = destId && destPricing[destId] ? destPricing[destId] : null;
            const metaCurrency = priceMeta && priceMeta.currency ? priceMeta.currency : 'USD';
            const rawBasePrice = priceMeta && priceMeta[packageType] ? priceMeta[packageType] : 0;
            const basePrice = convertAmount(rawBasePrice, metaCurrency, activeCurrency);

            document.getElementById('adultCount').textContent = adults;
            document.getElementById('childCount').textContent = children;
            document.getElementById('basePrice').textContent = formatAmount(basePrice, activeCurrency);

            const adultCost = basePrice * adults;
            const childCost = (basePrice * 0.5) * children;
            let totalAmount = adultCost + childCost;

            document.getElementById('adultCost').textContent = formatAmount(adultCost, activeCurrency);

            if(children > 0){
                document.getElementById('childrenRow').style.display = 'flex';
                document.getElementById('childCost').textContent = formatAmount(childCost, activeCurrency);
            } else {
                document.getElementById('childrenRow').style.display = 'none';
            }

            if(total >= 4){
                const discount = totalAmount * 0.1;
                totalAmount *= 0.9;
                document.getElementById('discountRow').style.display = 'flex';
                document.getElementById('discountAmount').textContent = '-' + formatAmount(discount, activeCurrency);
            } else {
                document.getElementById('discountRow').style.display = 'none';
            }

            document.getElementById('totalAmount').textContent = formatAmount(totalAmount, activeCurrency);
            document.getElementById('total').value = total;

            const tiers = ['budget','standard','luxury'];
            if(priceMeta){
                tiers.forEach((tier) => {
                    const tierAmount = convertAmount(priceMeta[tier] || 0, metaCurrency, activeCurrency);
                    document.getElementById('price_' + tier).textContent = formatAmount(tierAmount, activeCurrency);
                });
            } else {
                tiers.forEach((tier) => {
                    document.getElementById('price_' + tier).textContent = formatAmount(0, activeCurrency);
                });
            }

            syncClones();
        }

        function syncClones(){
            if(document.getElementById('destination_id_clone')) document.getElementById('destination_id_clone').value = document.getElementById('destination_id').value;
            if(document.getElementById('travel_date_clone')) document.getElementById('travel_date_clone').value = document.getElementById('travel_date').value;
            if(document.getElementById('adults_clone')) document.getElementById('adults_clone').value = document.getElementById('adults').value;
            if(document.getElementById('children_clone')) document.getElementById('children_clone').value = document.getElementById('children').value;
            if(document.getElementById('package_type_clone')) document.getElementById('package_type_clone').value = (document.querySelector('input[name="package_type"]:checked') || {value:'standard'}).value;
            if(document.getElementById('payment_method_clone')) document.getElementById('payment_method_clone').value = document.getElementById('payment_method').value;
            if(document.getElementById('special_requests_clone')) document.getElementById('special_requests_clone').value = document.getElementById('special_requests').value;
        }

        function submitClone(){
            syncClones();
            // small client side validation to avoid empty submit
            const dest = parseInt(document.getElementById('destination_id_clone').value) || 0;
            if(dest <= 0){ alert('Please select a destination.'); return false; }
            const adults = parseInt(document.getElementById('adults_clone').value) || 0;
            if(adults < 1){ alert('Please enter at least one adult.'); return false; }
            return true;
        }

        const bookingCurrencySelect = document.getElementById('bookingCurrencySelect');
        if (bookingCurrencySelect) {
            bookingCurrencySelect.addEventListener('change', (event) => {
                const newCurrency = event.target.value;
                if (!currencyRates[newCurrency]) {
                    return;
                }
                activeCurrency = newCurrency;
                persistCurrency(newCurrency);
                updatePrice();
            });
        }

        // close dropdown when clicking outside
        document.addEventListener('click', function(e){
            if(!e.target.closest('.destination-list') && !e.target.closest('#destinationButton')){
                const dd = document.getElementById('destinationDropdown');
                if(dd && dd.classList.contains('show')){
                    dd.classList.remove('show');
                    dd.setAttribute('aria-hidden','true');
                    document.getElementById('destinationButton').setAttribute('aria-expanded','false');
                    document.getElementById('dropdownArrow').style.transform = 'rotate(0)';
                }
            }
        });

        // on load
        window.addEventListener('load', function(){
            updatePrice();
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>