<?php
require_once __DIR__ . '/../PHP/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $data = ['user_id' => $_SESSION['user_id'] ?? null, 'action' => $input['action'] ?? 'unknown', 'element' => $input['element'] ?? '', 'created_at' => date('Y-m-d H:i:s')];
    KindoraDatabase::insert('user_interactions', $data);
    echo json_encode(['success' => true]);
}
?>