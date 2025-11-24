<?php
require_once 'connection.php';

if (isset($_GET['id'])) {
    $dest_id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
    $stmt->execute([$dest_id]);
    $destination = $stmt->fetch();
    
    if ($destination) {
        header('Content-Type: application/json');
        echo json_encode($destination);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Destination not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing destination ID']);
}
?>
