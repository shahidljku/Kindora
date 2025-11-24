<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check login
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['message'] = 'Please login to submit a review';
        $_SESSION['message_type'] = 'error';
        header('Location: pages/login.php');
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $destination_id = (int)($_POST['destination_id'] ?? 0);
    $destination_name = trim($_POST['destination_name'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $text = trim($_POST['text'] ?? '');

    if ($destination_id < 1 && $destination_name !== '') {
        try {
            $destinationRow = KindoraDatabase::query(
                "SELECT destination_id FROM destinations WHERE name = ? LIMIT 1",
                [$destination_name]
            );
            if (!empty($destinationRow)) {
                $destination_id = (int)$destinationRow[0]['destination_id'];
            }
        } catch (Exception $e) {
            error_log('Destination lookup error: ' . $e->getMessage());
        }
    }
    
    // Validate
    $validationErrors = [];
    if ($destination_id < 1) {
        $validationErrors[] = 'Please select a valid destination.';
    }
    if ($rating < 1 || $rating > 5) {
        $validationErrors[] = 'Please choose a star rating.';
    }
    if (strlen($text) < 10) {
        $validationErrors[] = 'Review must be at least 10 characters long.';
    }

    if (!empty($validationErrors)) {
        $_SESSION['message'] = implode(' ', $validationErrors);
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    try {
        // Check if user has completed booking for this destination
        $booking = KindoraDatabase::query(
            "SELECT booking_id FROM bookings 
             WHERE user_id = ? 
               AND destination_id = ? 
               AND status IN ('completed', 'pending', 'confirmed', 'approved')
             LIMIT 1",
            [$user_id, $destination_id]
        );
        
        if (empty($booking)) {
            $_SESSION['message'] = 'You can only review destinations you have visited';
            $_SESSION['message_type'] = 'error';
            header('Location: index.php');
            exit;
        }
        
        // Submit review
        KindoraDatabase::query(
            "INSERT INTO reviews (user_id, destination_id, rating, review_text, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())",
            [$user_id, $destination_id, $rating, $text]
        );
        
        $_SESSION['message'] = 'Review submitted for approval!';
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error submitting review: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        error_log('Review error: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
?>
