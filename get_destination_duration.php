<?php
/**
 * get_destination_duration.php
 * API endpoint to fetch destination duration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/Database.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['destination_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing destination_id']);
        exit();
    }

    $destination_id = intval($data['destination_id']);

    $database = new Database();
    $db = $database->connect();

    $query = "SELECT destination_id, name, suggested_duration_days 
              FROM destinations 
              WHERE destination_id = ? AND is_active = 1";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Destination not found']);
        exit();
    }

    $destination = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'destination_id' => $destination['destination_id'],
        'destination_name' => $destination['name'],
        'suggested_duration_days' => (int)$destination['suggested_duration_days']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
