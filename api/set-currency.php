<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$requestedCurrency = $payload['currency'] ?? null;

if (!$requestedCurrency) {
    echo json_encode([
        'success' => false,
        'message' => 'Currency code is required.'
    ]);
    exit;
}

$newCode = setCurrencyPreference($requestedCurrency);
$userId = getCurrentUserId();

if ($userId > 0) {
    KindoraDatabase::update(
        'users',
        ['preferred_currency' => $newCode],
        'user_id = :user_id',
        ['user_id' => $userId]
    );
}

echo json_encode([
    'success' => true,
    'currency' => $newCode,
]);

