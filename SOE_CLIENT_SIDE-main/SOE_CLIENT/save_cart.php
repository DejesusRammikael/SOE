<?php
$conn = new mysqli("localhost", "root", "password", "soe_clientside");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');


$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['cart']) || !isset($data['order_number'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$cart = $data['cart'];
$order_number = $conn->real_escape_string($data['order_number']);


foreach ($cart as $id => $item) {
    $item_id = intval($id);
    $name = $conn->real_escape_string($item['name']);
    $quantity = intval($item['quantity']);
    $price = floatval($item['price']);

    $sql = "INSERT INTO orders (item_id, item_name, quantity, price, order_number) VALUES ($item_id, '$name', $quantity, $price, '$order_number')";
    if (!$conn->query($sql)) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
}

echo json_encode(['success' => true, 'message' => 'Cart saved successfully']);
?>
