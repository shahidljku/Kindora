<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = 'Invalid email address';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    try {
        // Check if already subscribed
        $existing = KindoraDatabase::query(
            "SELECT * FROM newsletter_subscribers WHERE email = ? LIMIT 1",
            [$email]
        );
        
        if (!empty($existing)) {
            $_SESSION['message'] = 'Email already subscribed';
            $_SESSION['message_type'] = 'info';
        } else {
            // Subscribe
            KindoraDatabase::query(
                "INSERT INTO newsletter_subscribers (email, subscribed_at, is_active) VALUES (?, NOW(), 1)",
                [$email]
            );
            $_SESSION['message'] = 'Successfully subscribed! Check your email.';
            $_SESSION['message_type'] = 'success';
        }
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error subscribing: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        error_log('Newsletter error: ' . $e->getMessage());
    }
}

header('Location: index.php#newsletter');
exit;
?>
