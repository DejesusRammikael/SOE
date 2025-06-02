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
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['price'] * $row['quantity'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/view_cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <div class="main-content">
            <div class="header">
                <h1>Cart Overview</h1>
                <a href="client_dashboard.php" class="back-btn">← Back to Dashboard</a>
            </div>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <svg width="120" height="120" viewBox="0 0 24 24">
                            <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" fill="currentColor"/>
                        </svg>
                        <div class="cart-face">
                            <span>:</span>
                            <span>(</span>
                        </div>
                    </div>
                    <h2>Your cart is empty</h2>
                    <p>Add some items to your cart to proceed</p>
                    <a href="client_dashboard.php" class="back-to-menu">Return to Menu</a>
                </div>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-id="<?= htmlspecialchars($item['stall_id']) ?>" data-menu-id="<?= htmlspecialchars($item['product_id']) ?>">
                        <img src="assets/images/<?= htmlspecialchars($item['product_path']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="item-image">
                        <div class="item-details">
                            <h3 class="item-name"><?= htmlspecialchars($item['product_name']) ?></h3>
                            <p class="item-stall"><?= htmlspecialchars($item['stall_name']) ?></p>
                            <p class="item-price">₱<?= number_format($item['price'], 2) ?></p>
                        </div>
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateQuantity(<?= htmlspecialchars($item['product_id']) ?>, 'decrease')">-</button>
                            <input type="number" class="qty-input" value="<?= htmlspecialchars($item['quantity']) ?>" min="1" onchange="updateQuantity(<?= htmlspecialchars($item['product_id']) ?>, 'set', this.value)">
                            <button class="qty-btn" onclick="updateQuantity(<?= htmlspecialchars($item['product_id']) ?>, 'increase')">+</button>
                        </div>
                        <button class="remove-btn" onclick="showConfirmModal(<?= htmlspecialchars($item['product_id']) ?>, '<?= htmlspecialchars(addslashes($item['product_name'])) ?>')">Remove</button>
                    </div>
                <?php endforeach; ?>

                <div class="cart-summary">
                    <div class="total">Total: ₱<?= number_format($total, 2) ?></div>
                    <button class="checkout-btn" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
        border-radius: 20px;
        margin: 60px;
        height: 400px;
        width: 90%;
    }

    .empty-cart-icon {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 30px;
        color: #333;
    }

    .cart-face {
        position: absolute;
        top: 45%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-family: monospace;
        font-size: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 15px;
    }

    .empty-cart h2 {
        color: #333;
        font-size: 30px;
        margin-bottom: 15px;
    }

    .empty-cart p {
        color: #666;
        font-size: 25px;
        margin-bottom: 30px;
    }

    .back-to-menu {
        display: inline-block;
        padding: 12px 25px;
        background-color: #1b4d4d;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 18px;
        transition: all 0.3s;
    }

    .back-to-menu:hover {
        background-color: #2c7a7a;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(27, 77, 77, 0.2);
    }

    /* Add modal styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        backdrop-filter: blur(5px);
    }

    .confirm-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    text-align: center;
    max-width: 400px;
    width: 90%;
    z-index: 1003;
}

.confirm-modal h3 {
    color:rgb(69, 74, 83);
    font-size: 24px;
    margin-bottom: 15px;
}

.confirm-modal p {
    color: #666;
    font-size: 16px;
    margin-bottom: 25px;
    line-height: 1.5;
}

.modal-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.modal-btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
}

.modal-btn:hover {
    transform: translateY(-2px);
}

.cancel-modal-btn {
    background: #f8f9fa;
    color:rgb(67, 74, 87);
}

.confirm-modal-btn {
    background: #ffd700;
    color: #333;
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

    .quantity-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin: 20px 0;
}

.quantity-controls button {
    background-color: #1b4d4d;
    color: white;
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-controls button:hover {
    background-color: #2c7a7a;
}

.quantity-input {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    min-width: 30px;
    text-align: center;

    </style>

    <!-- Add modal HTML before closing body tag -->
    <div class="modal-overlay" id="confirmModal">
        <div class="confirm-modal">
            <h3>Remove Item</h3>
            <p>Are you sure you want to remove <span id="itemName"></span> from your cart?</p>
            <div class="modal-buttons">
                <button class="modal-btn cancel-modal-btn" onclick="closeConfirmModal()">Cancel</button>
                <button class="modal-btn confirm-modal-btn" onclick="confirmRemove()">Remove</button>
            </div>
        </div>
    </div>

    <script>
        let itemToRemove = null;

        function updateQuantity(menuItemId, action, value = null) {
            let quantity;
            if (action === 'set') {
                quantity = parseInt(value);
                if (isNaN(quantity) || quantity < 1) {
                    quantity = 1;
                }
            } else {
                const input = document.querySelector(`.cart-item[data-menu-id="${menuItemId}"] .qty-input`);
                quantity = parseInt(input.value);
                if (action === 'increase') {
                    quantity++;
                } else if (action === 'decrease') {
                    quantity = Math.max(1, quantity - 1);
                }
                input.value = quantity;
            }

            // Update the total immediately for better UX
            updateCartTotal();

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `product_id=${menuItemId}&quantity=${quantity}` // <-- FIXED HERE
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartTotal(); // Update total again after server confirmation
                } else {
                    alert(data.message || 'Failed to update quantity');
                    location.reload(); // Reload to get correct state if there was an error
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Could not connect to server');
                location.reload(); // Reload to get correct state if there was an error
            });
        }

        function updateCartTotal() {
            let total = 0;
            document.querySelectorAll('.cart-item').forEach(item => {
                const price = parseFloat(item.querySelector('.item-price').textContent.replace('₱', ''));
                const quantity = parseInt(item.querySelector('.qty-input').value);
                total += price * quantity;
            });
            
            const formattedTotal = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2
            }).format(total).replace('PHP', '₱');
            
            document.querySelector('.total').textContent = `Total: ${formattedTotal}`;
        }

        function showConfirmModal(menuItemId, itemName) {
            itemToRemove = menuItemId;
            document.getElementById('itemName').textContent = itemName;
            document.getElementById('confirmModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
            itemToRemove = null;
        }

        function confirmRemove() {
            if (itemToRemove) {
                removeItem(itemToRemove);
                closeConfirmModal();
            }
        }

        function removeItem(menuItemId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `product_id=${menuItemId}&quantity=0` // <-- FIXED HERE
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to remove item');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Could not connect to server');
            });
        }

        // Update onclick handlers in cart items
        document.querySelectorAll('.remove-btn').forEach(btn => {
            const menuItemId = btn.closest('.cart-item').dataset.menuId;
            const itemName = btn.closest('.cart-item').querySelector('.item-name').textContent;
            btn.onclick = () => showConfirmModal(menuItemId, itemName);
        });

        // Close modal when clicking outside
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        // Add input validation for quantity
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('input', function() {
                let value = parseInt(this.value);
                if (isNaN(value) || value < 1) {
                    this.value = 1;
                }
            });
        });
    </script>
</body>
</html>