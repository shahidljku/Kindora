<?php
require_once __DIR__ . '/PHP/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $_SESSION['user_id'] ?? null,
        'package_id' => $_POST['package_id'] ?? null,
        'start_date' => $_POST['start_date'] ?? null,
        'end_date' => $_POST['end_date'] ?? null,
        'people' => $_POST['people'] ?? 1,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    $bookingId = KindoraDatabase::insert('bookings', $data);
    header('Location: confirmation.php?id=' . $bookingId);
    exit;
}

readfile(__DIR__ . '/confirmation.php');
?>