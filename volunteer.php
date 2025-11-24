<?php
require_once __DIR__ . '/PHP/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'message' => $_POST['message'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];
    KindoraDatabase::insert('volunteer_applications', $data);
    header('Location: volunteer.html?submitted=1');
    exit;
}

readfile(__DIR__ . '/volunteer.html');
?>