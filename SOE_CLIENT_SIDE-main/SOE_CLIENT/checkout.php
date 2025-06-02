<?php
session_start();
require_once 'db_connection.php';

$session_id = session_id();

$query = "SELECT ci.id, ci.product_id, ci.quantity, p.product_name, p.price, p.product_path, p.stall_id, s.stall_name
          FROM cart_items ci
          JOIN products p ON ci.product_id = p.product_id
          JOIN stall s ON p.stall_id = s.stall_id
          WHERE ci.session_id = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query error: " . $conn->error);
}
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

if (empty($cart_items)) {
    header('Location: view_cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/view_cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            gap: 30px;
        }

        .order-summary {
            flex: 1;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
        }

        .order-summary h2 {
            color: #1e3c72;
            font-size: 24px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary-items {
            margin-bottom: 30px;
        }

        .cart-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-details {
            flex-grow: 1;
        }

        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .item-stall {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .item-quantity {
            color: #1e3c72;
            font-size: 14px;
        }

        .item-price {
            color: #1e3c72;
            font-weight: 600;
            font-size: 16px;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            font-size: 20px;
            font-weight: 600;
            color: #1e3c72;
        }

        .user-details {
            flex: 1;
        }

        .user-details h2 {
            color: #1e3c72;
            font-size: 24px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e8f0fe;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            border-color: #1e3c72;
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .cancel-btn,
        .place-order-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .cancel-btn {
            background: #f8f9fa;
            color: #1e3c72;
        }

        .place-order-btn {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
        }

        .cancel-btn:hover,
        .place-order-btn:hover {
            transform: translateY(-2px);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #fff;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 8px;
        }

        .sidebar a i {
            font-size: 16px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-brand">
            <img src="Picture/logo1.png" alt="EZ-Order" class="header-logo">   
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logoo-wrapper">
            <img src="Picture/logo2.png" alt="EZ-Order Logo" class="sidebar-logo">
            <div class="logoo">EZ-ORDER</div>
            <div class="divider"></div> 
            <div class="tagline">"easy orders, zero hassle"</div>
        </div>
        <h2>🍽 Stalls</h2>
        <?php 
        $stall_query = "SELECT * FROM stall";
        $stall_result = $conn->query($stall_query);
        while($stall = $stall_result->fetch_assoc()): ?>
            <a href="stall_dashboard.php?stall_id=<?= $stall['stall_id'] ?>">
                <i class="fas fa-store"></i> <?= htmlspecialchars($stall['stall_name']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container">
            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="summary-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="assets/images/<?= htmlspecialchars($item['product_path']) ?>" 
                                 alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                 class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div class="item-stall"><?= htmlspecialchars($item['stall_name']) ?></div>
                                <div class="item-quantity">Quantity: <?= htmlspecialchars($item['quantity']) ?></div>
                            </div>
                            <div class="item-price">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-total">
                    <span>Total</span>
                    <span>₱<?= number_format($total, 2) ?></span>
                </div>
            </div>

            <div class="user-details">
                <h2>Your Details</h2>
                <form id="checkoutForm" action="process_order.php" method="POST">
                <div class="form-group">
                    <label for="id_number">Student ID Number</label>
                     <input type="text" id="id_number" name="id_number"
                        pattern="^(2020|2021|2022|2023|2024|2025)\d{5}$"
                        title="Enter a 9-digit ID starting with a valid school year (e.g., 202320001)"
                        placeholder="e.g. 202312345"
                        maxlength="9" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Enter Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message for Seller (Optional)</label>
                        <textarea id="message" name="message"></textarea>
                    </div>
                    
                    <input type="hidden" name="total" value="<?= htmlspecialchars($total) ?>">

                    <div class="buttons">
                        <button type="button" class="cancel-btn" onclick="window.location.href='view_cart.php'">Cancel</button>
                        <button type="submit" class="place-order-btn">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>