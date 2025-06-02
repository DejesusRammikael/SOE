<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "password", "ezorderdb");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$session_id = session_id();
$stmt = $conn->prepare("DELETE FROM cart_items WHERE session_id = ?");
$stmt->bind_param("s", $session_id);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
?>
