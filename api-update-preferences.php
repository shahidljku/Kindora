<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_SESSION['user_id']) && isset($input['currency'])) {
        KindoraDatabase::update(
            'users',
            ['preferred_currency' => $input['currency']],
            'id = ?',
            [$_SESSION['user_id']]
        );
    }
    
    echo json_encode(['success' => true]);
}
?>
