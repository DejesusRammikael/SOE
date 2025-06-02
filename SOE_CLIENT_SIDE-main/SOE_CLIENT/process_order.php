<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect if not a POST request
    header('Location: view_cart.php');
    exit;
}

$session_id = session_id();

// Get user details from POST data
$id_number = isset($_POST['id_number']) ? htmlspecialchars($_POST['id_number']) : '';
$name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
$message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';
$total = isset($_POST['total']) ? floatval($_POST['total']) : 0;

// Log received POST data
error_log('Process Order POST Data: ' . print_r($_POST, true));
error_log('Validated Data: ID=' . $id_number . ', Name=' . $name . ', Total=' . $total);

// Validate required fields
if (empty($id_number) || empty($name) || $total <= 0) {
    error_log('Validation Failed: ID or Name empty or total <= 0');
    $_SESSION['error_message'] = 'Please fill in all required fields.'; // More user-friendly message
    header('Location: checkout.php');
    exit;
}

// Fetch cart items
$query = "SELECT ci.product_id, ci.quantity, p.price, p.product_name 
          FROM cart_items ci 
          JOIN products p ON ci.product_id = p.product_id 
          WHERE ci.session_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$cart_result = $stmt->get_result();

$cart_items = [];
while ($row = $cart_result->fetch_assoc()) {
    $cart_items[] = $row;
}

if (empty($cart_items)) {
    // Redirect if cart is empty
    $_SESSION['error_message'] = 'Your cart is empty.';
    header('Location: view_cart.php');
    exit;
}

// Start database transaction
$conn->begin_transaction();

try {
    // 1. Insert into orders table
    // Assuming an 'orders' table with columns: id, session_id, id_number, customer_name, message, total_amount, created_at
    $insert_order_query = "INSERT INTO orders (session_id, id_number, customer_name, message, total_amount, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_order_query);
    $stmt->bind_param("ssssd", $session_id, $id_number, $name, $message, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;

    if ($order_id) {
        error_log('Order successfully inserted into orders table. Order ID: ' . $order_id);
        // We can add a temporary redirect here for testing if needed, but let's keep trying order_items first
    } else {
         error_log('Failed to insert order into orders table.');
         throw new Exception('Failed to save main order details.');
    }

    // 2. Insert into order_items table
    // Assuming an 'order_items' table with columns: id, order_id, menu_item_id, quantity, price_at_order, item_name_at_order
    $insert_item_query = "INSERT INTO order_items (order_id, product_id, quantity, price_at_order, item_name_at_order) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_item_query);
    if (!$stmt) {
        throw new Exception('Prepare failed for order_items: ' . $conn->error);
    }
    foreach ($cart_items as $item) {
        $stmt->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item['product_name']);
        $stmt->execute();
    }

    // 3. Clear the cart
    $delete_cart_query = "DELETE FROM cart_items WHERE session_id = ?";
    $stmt = $conn->prepare($delete_cart_query);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Redirect to a confirmation page
    $_SESSION['success_message'] = 'Your order has been placed successfully!';
    header('Location: order_confirmation.php?order_id=' . $order_id); // Create this page next
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Log error (in a real application)
    error_log('Order processing error: ' . $e->getMessage());

    // Redirect to an error page or show error message
    $_SESSION['error_message'] = 'There was an error processing your order. Please try again.';
    header('Location: checkout.php');
    exit;
}

$conn->close();
?>