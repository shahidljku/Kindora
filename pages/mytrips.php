<?php
/**
 * My Trips / Bookings - Complete Management System
 * View, Update, and Cancel bookings
 * Minimal fixes only: robust booking fetch fallback and small HTML/JS safety fixes.
 */

require_once '../config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$userBookings = [];
$message = '';
$messageType = 'info';

// simple sanitize helper used by your file
if (!function_exists('sanitize')) {
    function sanitize($v) {
        return is_string($v) ? trim($v) : $v;
    }
}

// Handle update booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_booking') {
    $bookingId = intval($_POST['booking_id']);
    $guests = intval($_POST['guests']);
    $travelDate = sanitize($_POST['travel_date'] ?? '');
    $returnDate = sanitize($_POST['return_date'] ?? '');
    
    try {
        // Verify booking belongs to user and can be updated
        $booking = KindoraDatabase::query(
            "SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?",
            [$bookingId, $userId]
        );

        // normalize if object is returned (some DB wrappers return PDOStatement)
        if (is_object($booking) && method_exists($booking, 'fetchAll')) {
            $booking = $booking->fetchAll(PDO::FETCH_ASSOC);
        }

        if (empty($booking)) {
            $message = 'Booking not found.';
            $messageType = 'error';
        } else {
            $booking = is_array($booking) ? $booking[0] : $booking;

            if (($booking['status'] ?? '') === 'completed') {
                $message = 'Cannot update a completed booking.';
                $messageType = 'error';
            } elseif (($booking['status'] ?? '') === 'cancelled') {
                $message = 'Cannot update a cancelled booking.';
                $messageType = 'error';
            } elseif ($guests < 1) {
                $message = 'Guests must be at least 1.';
                $messageType = 'error';
            } elseif (empty($travelDate)) {
                $message = 'Travel date is required.';
                $messageType = 'error';
            } elseif (strtotime($travelDate) <= time()) {
                $message = 'Travel date must be in the future.';
                $messageType = 'error';
            } else {
                // Validate return date if provided
                if (!empty($returnDate) && strtotime($returnDate) <= strtotime($travelDate)) {
                    $message = 'Return date must be after travel date.';
                    $messageType = 'error';
                } else {
                    // Update booking - use correct column names
                    $updateData = [
                        'guests' => $guests,
                        'travel_date' => $travelDate,
                        'return_date' => !empty($returnDate) ? $returnDate : null
                    ];

                    KindoraDatabase::update(
                        'bookings',
                        $updateData,
                        'booking_id = :booking_id',
                        ['booking_id' => $bookingId]
                    );

                    $message = 'Booking updated successfully!';
                    $messageType = 'success';
                }
            }
        }
    } catch (Exception $e) {
        error_log("UPDATE BOOKING ERROR: " . $e->getMessage());
        $message = 'Error updating booking: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle cancel booking
// -----------------------
// Handle cancel booking
// -----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    // Basic input validation
    $bookingId = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

    if ($bookingId <= 0) {
        $message = 'Invalid booking ID.';
        $messageType = 'error';
    } else {
        try {
            // Verify booking belongs to user
            // Use explicit columns to avoid unexpected returned types
            $booking = KindoraDatabase::query(
                "SELECT booking_id, user_id, status FROM bookings WHERE booking_id = ? AND user_id = ? LIMIT 1",
                [$bookingId, $userId]
            );

            // Normalize result: handle PDOStatement or array returns
            if ($booking === false) {
                // DB layer returned false -> treat as DB error
                error_log("CANCEL BOOKING: DB query returned false for booking_id={$bookingId}, user_id={$userId}");
                throw new Exception("Database error while verifying booking.");
            }

            if (is_object($booking) && method_exists($booking, 'fetchAll')) {
                $rows = $booking->fetchAll(PDO::FETCH_ASSOC);
            } elseif (is_array($booking)) {
                $rows = $booking;
            } else {
                // unexpected return type
                error_log("CANCEL BOOKING: unexpected KindoraDatabase::query return type: " . gettype($booking));
                $rows = [];
            }

            if (empty($rows)) {
                $message = 'Booking not found or does not belong to you.';
                $messageType = 'error';
            } else {
                $b = $rows[0];

                // protect from cancelling completed/cancelled
                if (isset($b['status']) && $b['status'] === 'completed') {
                    $message = 'Cannot cancel a completed booking.';
                    $messageType = 'error';
                } elseif (isset($b['status']) && $b['status'] === 'cancelled') {
                    $message = 'Booking is already cancelled.'; 
                    $messageType = 'info';
                } else {
                    // Perform update in a safe try/catch
                    $updated = KindoraDatabase::update(
                        'bookings',
                        ['status' => 'cancelled'],
                        'booking_id = :booking_id AND user_id = :user_id',
                        ['booking_id' => $bookingId, 'user_id' => $userId]
                    );

                    // Some DB wrappers return number of affected rows, some return true/false.
                    if ($updated === false) {
                        error_log("CANCEL BOOKING: KindoraDatabase::update returned false for booking_id={$bookingId}");
                        throw new Exception("Failed to cancel booking due to database error.");
                    }

                    // If update returns 0 (no rows affected) we should warn
                    if ($updated === 0) {
                        // Could be race condition or already cancelled by another process
                        error_log("CANCEL BOOKING: update affected 0 rows for booking_id={$bookingId}, user_id={$userId}");
                        $message = 'Unable to cancel booking (no rows updated). It may already be cancelled or changed.';
                        $messageType = 'error';
                    } else {
                        $message = 'Booking cancelled successfully.';
                        $messageType = 'success';
                    }
                }
            }
        } catch (Exception $e) {
            // Prevent raw exception from turning into HTTP 500 response body
            error_log("CANCEL BOOKING ERROR: " . $e->getMessage() . " -- Trace: " . $e->getTraceAsString());
            $message = 'Error cancelling booking. Try again later.';
            $messageType = 'error';
        }
    }
}


// Load user data and bookings
try {
    // fetch user (simple check)
    $userData = KindoraDatabase::query(
        "SELECT * FROM users WHERE user_id = ?",
        [$userId]
    );
    if (is_object($userData) && method_exists($userData, 'fetchAll')) {
        $userData = $userData->fetchAll(PDO::FETCH_ASSOC);
    }

    if (empty($userData)) {
        header('Location: logout.php');
        exit;
    }
    
    $userData = is_array($userData) ? $userData[0] : $userData;

    // Primary attempt: bookings with destination join (this mirrors your original)
   $userBookings = KindoraDatabase::query(
    "SELECT b.booking_id, b.user_id, b.destination_id, b.booking_date, b.travel_date, b.return_date,
            b.guests, b.total_amount, b.status, b.payment_status, b.created_at, b.updated_at,
            d.name, d.type, d.description, d.image_url, d.best_season, d.location
     FROM bookings b
     LEFT JOIN destinations d ON b.destination_id = d.destination_id
     WHERE b.user_id = ?
     ORDER BY b.travel_date DESC",
    [$userId]
);

    // If result is a PDOStatement-like object, fetchAll
    if (is_object($userBookings) && method_exists($userBookings, 'fetchAll')) {
        $userBookings = $userBookings->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fallback: if join returned empty but bookings exist in DB, fetch bookings first then destinations one-by-one
    if (empty($userBookings)) {
        // Check count quickly (non-intrusive)
        $countRes = KindoraDatabase::query("SELECT COUNT(*) as cnt FROM bookings WHERE user_id = ?", [$userId]);
        if (is_object($countRes) && method_exists($countRes, 'fetchAll')) {
            $countRes = $countRes->fetchAll(PDO::FETCH_ASSOC);
        }
        $cnt = 0;
        if (is_array($countRes) && isset($countRes[0]['cnt'])) {
            $cnt = (int)$countRes[0]['cnt'];
        }

        if ($cnt > 0) {
            // fetch bookings without join
            $bookingsOnly = KindoraDatabase::query(
                "SELECT * FROM bookings WHERE user_id = ? ORDER BY travel_date DESC",
                [$userId]
            );
            if (is_object($bookingsOnly) && method_exists($bookingsOnly, 'fetchAll')) {
                $bookingsOnly = $bookingsOnly->fetchAll(PDO::FETCH_ASSOC);
            }
            if (is_array($bookingsOnly) && count($bookingsOnly) > 0) {
                // for each booking attempt to fetch destination record separately
                $userBookings = [];
                foreach ($bookingsOnly as $b) {
                    $dest = null;
                    if (!empty($b['destination_id'])) {
                        $destQuery = KindoraDatabase::query("SELECT * FROM destinations WHERE destination_id = ?", [$b['destination_id']]);
                        if (is_object($destQuery) && method_exists($destQuery, 'fetchAll')) {
                            $destQuery = $destQuery->fetchAll(PDO::FETCH_ASSOC);
                        }
                        if (!empty($destQuery) && is_array($destQuery)) {
                            $dest = $destQuery[0];
                        }
                    }

                    // map destination fields (if available)
                    if ($dest) {
                        $b['name'] = $dest['name'] ?? $b['name'] ?? null;
                        $b['image_url'] = $dest['image_url'] ?? $b['image_url'] ?? null;
                        $b['description'] = $dest['description'] ?? $b['description'] ?? null;
                        $b['location'] = $dest['location'] ?? $b['location'] ?? null;
                    } else {
                        // ensure some fields are present for frontend
                        $b['name'] = $b['name'] ?? 'Unknown Destination';
                        $b['image_url'] = $b['image_url'] ?? 'placeholder.jpg';
                    }
                    $userBookings[] = $b;
                }
            }
        }
    }

    // Final defensive normalization: ensure $userBookings is an array
    if (!is_array($userBookings)) $userBookings = [];

    error_log("USER BOOKINGS LOADED: " . count($userBookings) . " bookings found for user_id={$userId}");

} catch (Exception $e) {
    error_log("LOAD BOOKINGS ERROR: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $message = 'Error loading bookings: ' . $e->getMessage();
    $messageType = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Trips - Kindora</title>
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 30px;
            transition: opacity 0.3s;
        }

        nav a:hover {
            opacity: 0.8;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 32px;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .filter-btn.active {
            background: #667eea;
            color: white;
        }

        .filter-btn:hover {
            background: #667eea;
            color: white;
        }

        .trips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .trip-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .trip-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #ddd;
        }

        .trip-info {
            padding: 20px;
        }

        .trip-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .trip-detail {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 14px;
            color: #666;
        }

        .trip-detail-label {
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .empty-state a:hover {
            background-color: #5568d3;
        }

        .trip-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-view {
            background-color: #667eea;
            color: white;
        }

        .btn-view:hover {
            background-color: #5568d3;
        }

        .btn-edit {
            background-color: #28a745;
            color: white;
        }

        .btn-edit:hover {
            background-color: #218838;
        }

        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #c82333;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal.show {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 20px 0;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            max-width: 700px;
            width: 95%;
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 24px;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }

        .detail-label {
            font-weight: 600;
            color: #667eea;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #333;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-modal {
            flex: 1;
            min-width: 120px;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-modal-save {
            background-color: #28a745;
            color: white;
        }

        .btn-modal-save:hover {
            background-color: #218838;
        }

        .btn-modal-close {
            background-color: #6c757d;
            color: white;
        }

        .btn-modal-close:hover {
            background-color: #5a6268;
        }

        .btn-modal-cancel {
            background-color: #dc3545;
            color: white;
        }

        .btn-modal-cancel:hover {
            background-color: #c82333;
        }

        .cancel-confirmation {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            color: #721c24;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                gap: 10px;
            }

            nav a {
                margin-left: 0;
            }

            .trips-grid {
                grid-template-columns: 1fr;
            }

            .detail-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Kindora</div>
            <div style="display: flex; gap: 20px;">
                <a href="../index.php">Home</a>
                <a href="mytrips.php">My Trips</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>My Trips</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($userBookings)): ?>
            <div class="filters">
                <button class="filter-btn active" onclick="filterTrips('all', this)">All Trips</button>
                <button class="filter-btn" onclick="filterTrips('pending', this)">Pending</button>
                <button class="filter-btn" onclick="filterTrips('confirmed', this)">Confirmed</button>
                <button class="filter-btn" onclick="filterTrips('completed', this)">Completed</button>
                <button class="filter-btn" onclick="filterTrips('cancelled', this)">Cancelled</button>
            </div>

            <div class="trips-grid">
                <?php foreach ($userBookings as $booking): ?>
                    <div class="trip-card" data-status="<?php echo htmlspecialchars($booking['status']); ?>">
                        <img src="<?php echo htmlspecialchars($booking['image_url'] ?? 'placeholder.jpg'); ?>" 
     alt="<?php echo htmlspecialchars($booking['name'] ?? 'Destination'); ?>" 
     class="trip-image">

<div class="trip-title">
    <?php echo htmlspecialchars($booking['name'] ?? 'Unknown Destination'); ?>
</div>

                        
                        <div class="trip-info">
                            <div class="trip-title"><?php echo htmlspecialchars($booking['name'] ?? 'Unknown Destination'); ?></div>
                            
                            <div class="trip-detail">
                                <span class="trip-detail-label">Booking ID:</span>
                                <span>#<?php echo htmlspecialchars($booking['booking_id']); ?></span>
                            </div>

                            <div class="trip-detail">
                                <span class="trip-detail-label">Travel Date:</span>
                                <span><?php echo (empty($booking['travel_date']) ? 'N/A' : date('M d, Y', strtotime($booking['travel_date']))); ?></span>
                            </div>

                            <?php if (!empty($booking['return_date'])): ?>
                            <div class="trip-detail">
                                <span class="trip-detail-label">Return:</span>
                                <span><?php echo date('M d, Y', strtotime($booking['return_date'])); ?></span>
                            </div>
                            <?php endif; ?>

                            <div class="trip-detail">
                                <span class="trip-detail-label">Guests:</span>
                                <span><?php echo htmlspecialchars($booking['guests']); ?></span>
                            </div>

                            <div class="trip-detail">
                                <span class="trip-detail-label">Amount:</span>
                                <span>$<?php echo number_format($booking['total_amount'] ?? 0, 2); ?></span>
                            </div>

                            <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>">
                                <?php echo strtoupper(htmlspecialchars($booking['status'] ?? 'pending')); ?>
                            </span>

                            <div class="trip-actions">
                                <?php 
                                    // robust JSON encoding for data attribute
                                    $bookingJsonAttr = json_encode($booking, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);
                                ?>
                                <button class="btn-small btn-view" data-booking='<?php echo htmlspecialchars($bookingJsonAttr, ENT_QUOTES, 'UTF-8'); ?>' onclick="openDetailsFromDataset(this)">
                                    View Details
                                </button>
                                <?php if (($booking['status'] ?? '') !== 'completed' && ($booking['status'] ?? '') !== 'cancelled'): ?>
                                    <button class="btn-small btn-edit" data-booking='<?php echo htmlspecialchars($bookingJsonAttr, ENT_QUOTES, 'UTF-8'); ?>' onclick="openEditFromDataset(this)">
                                        Edit
                                    </button>
                                    <button class="btn-small btn-cancel" data-booking='<?php echo htmlspecialchars($bookingJsonAttr, ENT_QUOTES, 'UTF-8'); ?>' onclick="openCancelFromDataset(this)">
                                        Cancel
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>No Trips Yet</h2>
                <p>You haven't booked any trips yet. Start your adventure today!</p>
                <a href="index.php">Browse Destinations</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- View Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Booking Details</h2>
                <button class="close-btn" onclick="closeModal('detailsModal')">&times;</button>
            </div>
            <div class="modal-body" id="detailsModalBody"></div>
        </div>
    </div>

    <!-- Edit Booking Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Booking</h2>
                <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editForm">
                    <input type="hidden" name="action" value="update_booking">
                    <input type="hidden" name="booking_id" id="editBookingId">

                    <div id="editDestinationDisplay" style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 5px;">
                        <strong>Destination:</strong> <span id="editDestinationName"></span>
                    </div>

                    <div class="form-group">
                        <label for="editTravelDate">Travel Date *</label>
                        <input type="date" id="editTravelDate" name="travel_date" required>
                    </div>

                    <div class="form-group">
                        <label for="editReturnDate">Return Date</label>
                        <input type="date" id="editReturnDate" name="return_date">
                    </div>

                    <div class="form-group">
                        <label for="editGuests">Number of Guests *</label>
                        <input type="number" id="editGuests" name="guests" min="1" max="20" required>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-modal btn-modal-save">Save Changes</button>
                        <button type="button" class="btn-modal btn-modal-close" onclick="closeModal('editModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Booking Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Cancel Booking</h2>
                <button class="close-btn" onclick="closeModal('cancelModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="cancel-confirmation">
                    <strong>⚠️ Are you sure?</strong>
                    <p>You are about to cancel your booking for <span id="cancelDestinationName"></span>. This action cannot be undone.</p>
                </div>

                <form method="POST" id="cancelForm">
                    <input type="hidden" name="action" value="cancel_booking">
                    <input type="hidden" name="booking_id" id="cancelBookingId">

                    <div class="modal-actions">
                        <button type="submit" class="btn-modal btn-modal-cancel">Yes, Cancel Booking</button>
                        <button type="button" class="btn-modal btn-modal-close" onclick="closeModal('cancelModal')">No, Keep Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Kindora. All rights reserved.</p>
    </footer>

    <script>
        function filterTrips(status, btn) {
            const cards = document.querySelectorAll('.trip-card');
            const buttons = document.querySelectorAll('.filter-btn');

            // Safely set active class using provided button reference
            if (btn) {
                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }

            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function openDetailsModal(booking) {
            const modal = document.getElementById('detailsModal');
            const body = document.getElementById('detailsModalBody');

            const html = `
                <img src="${booking.image_url || 'placeholder.jpg'}" alt="${booking.name}" class="modal-image">

                <div class="detail-row">
                    <div class="detail-item">
                        <div class="detail-label">Destination</div>
                        <div class="detail-value">${booking.name}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Booking ID</div>
                        <div class="detail-value">#${booking.booking_id}</div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-item">
                        <div class="detail-label">Travel Date</div>
                        <div class="detail-value">${booking.travel_date ? new Date(booking.travel_date).toLocaleDateString() : 'N/A'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Return Date</div>
                        <div class="detail-value">${booking.return_date ? new Date(booking.return_date).toLocaleDateString() : 'N/A'}</div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-item">
                        <div class="detail-label">Guests</div>
                        <div class="detail-value">${booking.guests}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Location</div>
                        <div class="detail-value">${booking.location || 'N/A'}</div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-item">
                        <div class="detail-label">Amount</div>
                        <div class="detail-value" style="font-size: 20px; color: #667eea;">$${parseFloat(booking.total_amount || 0).toFixed(2)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value"><span class="status-badge status-${booking.status || 'pending'}">${(booking.status || 'PENDING').toUpperCase()}</span></div>
                    </div>
                </div>

                ${booking.description ? `<div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><strong>About:</strong> ${booking.description}</div>` : ''}

                <div class="modal-actions">
                    <button type="button" class="btn-modal btn-modal-close" onclick="closeModal('detailsModal')">Close</button>
                </div>
            `;

            body.innerHTML = html;
            modal.classList.add('show');
        }

        function openDetailsFromDataset(el) {
            try {
                const booking = JSON.parse(el.getAttribute('data-booking'));
                openDetailsModal(booking);
            } catch (err) {
                console.error("Failed to parse booking data:", err);
                alert("Failed to open booking details. Check console for errors.");
            }
        }

        function openEditModal(booking) {
            document.getElementById('editBookingId').value = booking.booking_id;
            document.getElementById('editDestinationName').textContent = booking.name;
            document.getElementById('editTravelDate').value = booking.travel_date || '';
            document.getElementById('editReturnDate').value = booking.return_date || '';
            document.getElementById('editGuests').value = booking.guests || 1;

            document.getElementById('editModal').classList.add('show');
        }

        function openEditFromDataset(el) {
            try {
                const booking = JSON.parse(el.getAttribute('data-booking'));
                openEditModal(booking);
            } catch (err) {
                console.error("Failed to parse booking data for edit:", err);
                alert("Failed to open edit form. Check console for errors.");
            }
        }

        // New helper: open cancel modal from the JSON data-booking attribute (avoids inline JS quoting issues)
        function openCancelFromDataset(el) {
            try {
                const booking = JSON.parse(el.getAttribute('data-booking'));
                // booking.booking_id should be present, booking.name may be null
                openCancelModal(booking.booking_id, booking.name || '');
            } catch (err) {
                console.error("openCancelFromDataset parse error:", err);
                alert("Failed to open cancel dialog. See console for details.");
            }
        }

        function openCancelModal(bookingId, destinationName) {
            document.getElementById('cancelBookingId').value = bookingId;
            document.getElementById('cancelDestinationName').textContent = destinationName;
            document.getElementById('cancelModal').classList.add('show');
        }

        // Disable the cancel form submit button on submit to avoid double submits
        (function attachCancelFormHandler() {
            const cancelForm = document.getElementById('cancelForm');
            if (cancelForm) {
                cancelForm.addEventListener('submit', function (e) {
                    // find the submit button inside the modal and disable it
                    const submitBtn = cancelForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Cancelling...';
                    }
                    // allow the form to submit normally (no preventDefault) so server-side handles cancellation
                });
            }
        })();

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.remove('show');
                }
            });
        }
    </script>
</body>
</html>
