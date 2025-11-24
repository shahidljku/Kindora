<?php
require_once __DIR__ . '/../PHP/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['email' => $_POST['email'] ?? '', 'subscribed_at' => date('Y-m-d H:i:s')];
    KindoraDatabase::insert('newsletter_subscribers', $data);
    header('Location: ../home.php?subscribed=1');
    exit;
}
?>