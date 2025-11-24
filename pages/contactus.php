<?php
require_once __DIR__ . '/PHP/config.php';
require_once 'paths.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'message' => $_POST['message'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];
    KindoraDatabase::insert('contact_messages', $data);
    header('Location: contactus.php?sent=1');
    exit;
}

readfile(__DIR__ . '/contactus.php');
?>