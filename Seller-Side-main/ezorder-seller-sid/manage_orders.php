<?php
session_start();
require_once 'database.php';

// Set default stall_id to 1 if not set
if (!isset($_SESSION['stall_id'])) {
    $_SESSION['stall_id'] = 1;
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle order status updates
function updateOrderStatus($order_id, $new_status) {
    require_once 'database.php';
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE `order` SET status = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("si", $new_status, $order_id);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Error updating order status: " . $e->getMessage());
        return false;
    }
}

// Process status updates
if (isset($_GET['accept']) && is_numeric($_GET['accept'])) {
    $order_id = intval($_GET['accept']);
    if (updateOrderStatus($order_id, 'preparing')) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=Order marked as preparing");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=Failed to update order status");
    }
    exit();
}

if (isset($_GET['ready']) && is_numeric($_GET['ready'])) {
    $order_id = intval($_GET['ready']);
    if (updateOrderStatus($order_id, 'ready')) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=Order marked as ready");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=Failed to update order status");
    }
    exit();
}

if (isset($_GET['complete']) && is_numeric($_GET['complete'])) {
    $order_id = intval($_GET['complete']);
    if (updateOrderStatus($order_id, 'completed')) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=Order marked as completed");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=Failed to update order status");
    }
    exit();
}

if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $order_id = intval($_GET['reject']);
    if (updateOrderStatus($order_id, 'cancelled')) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=Order has been cancelled");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=Failed to cancel order");
    }
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders - Ezorder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/manage_orders.css">
</head>
<body>
    <div class="dashboard-container">
    <div class="sidebar">
            <div class="sidebar-header">
                <img src="Picture/logo2.png" alt="EZ-ORDER" class="sidebar-logo">
                <h2>EZ-ORDER</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_orders.php" class="nav-item active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Manage Transactions</span>
                </a>
                <a href="manage_products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Manage Products</span>
                </a>
                <a href="feedback.php" class="nav-item">
                    <i class="fas fa-comments"></i>
                    <span>Feedback</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <main class="main-content">
            <div class="top-bar">
                <h1>Manage Orders</h1>
                <div class="order-filters">
                    <button class="filter-btn active">All Orders</button>
                    <button class="filter-btn">Pending</button>
                    <button class="filter-btn">Preparing</button>
                    <button class="filter-btn">Completed</button>
                </div>
    </div>

        <div class="orders-container">
            <?php
            require_once 'database.php';
            
            // Show success/error messages
            if (isset($_GET['success'])) {
                echo "<div class='alert alert-success'>" . htmlspecialchars($_GET['success']) . "</div>";
            }
            if (isset($_GET['error'])) {
                echo "<div class='alert alert-error'>" . htmlspecialchars($_GET['error']) . "</div>";
            }
            
            // Get status filter if set
            $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
            
            $sql = "SELECT id, order_name, item_quantity, stall_name, price, ordered_by, 
                           order_time, order_date, status, payment_status 
                    FROM `order`
                    WHERE 1=1";
                    
            if ($status_filter !== 'all') {
                $sql .= " AND status = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("s", $status_filter);
                } else {
                    echo "<div class='alert alert-error'>Error preparing statement: " . $conn->error . "</div>";
                    exit();
                }
            } else {
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    echo "<div class='alert alert-error'>Error preparing statement: " . $conn->error . "</div>";
                    exit();
                }
            }
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows === 0) {
                    echo "<div class='no-orders'>No orders found.</div>";
                }
            } else {
                echo "<div class='alert alert-error'>Error executing query: " . $stmt->error . "</div>";
                exit();
            }

            while ($row = $result->fetch_assoc()) {
                $order_id = "A" . $row['id'];
                $time = $row['order_time'];
                    $total = number_format($row['price'] * $row['item_quantity'], 2);
                
                // Prepare order data for modal
                $orderData = array(
                    'time' => $row['order_time'],
                    'date' => $row['order_date'],
                    'itemName' => $row['order_name'],
                    'price' => number_format($row['price'], 2),
                    'quantity' => $row['item_quantity'],
                    'orderNumber' => date('Y-') . $row['id'],
                    'customerName' => $row['ordered_by'],
                        'totalPayment' => $total
                );
                $orderDataJson = htmlspecialchars(json_encode($orderData), ENT_QUOTES, 'UTF-8');
                
                echo "<div class='order-card' id='order-card-" . $row['id'] . "'>";
                    echo "<div class='order-header'>";
                echo "<div class='order-id'>{$order_id}</div>";
                    echo "<div class='order-time'><i class='far fa-clock'></i> {$time}</div>";
                    echo "</div>";
                    
                echo "<div class='order-details'>";
                    echo "<div class='customer-info'>";
                    echo "<i class='fas fa-user'></i>";
                    echo "<span class='customer-name'>" . htmlspecialchars($row['ordered_by']) . "</span>";
                    echo "</div>";
                    
                    echo "<div class='order-items'>";
                    echo "<div class='item-name'>" . htmlspecialchars($row['order_name']) . "</div>";
                    echo "<div class='item-meta'>";
                echo "<span class='quantity'>×" . htmlspecialchars($row['item_quantity']) . "</span>";
                    echo "<span class='price'>₱" . $total . "</span>";
                    echo "</div>";
                echo "</div>";
                echo "</div>";
                    
                    echo "<div class='order-actions'>";
                    if ($row['status'] === 'pending') {
                        echo "<button onclick='updateStatus(" . $row['id'] . ", \"accept\")' class='action-btn accept'>";
                        echo "<i class='fas fa-check'></i> Accept";
                        echo "</button>";
                        echo "<button onclick='updateStatus(" . $row['id'] . ", \"reject\")' class='action-btn reject'>";
                        echo "<i class='fas fa-times'></i> Reject";
                        echo "</button>";
                    } elseif ($row['status'] === 'preparing') {
                        echo "<button onclick='updateStatus(" . $row['id'] . ", \"ready\")' class='action-btn ready'>";
                        echo "<i class='fas fa-check-double'></i> Mark as Ready";
                        echo "</button>";
                    } elseif ($row['status'] === 'ready') {
                        echo "<button onclick='updateStatus(" . $row['id'] . ", \"complete\")' class='action-btn complete'>";
                        echo "<i class='fas fa-check-circle'></i> Mark as Completed";
                        echo "</button>";
                    } else {
                        echo "<span class='status-badge'>" . ucfirst($row['status']) . "</span>";
                    }
                    echo "</div>";
                echo "</div>";
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details</h2>
            </div>
            
            <div class="modal-body">
                <div class="order-info">
                    <div class="info-group">
                        <label>Order ID</label>
                        <div id="modalOrderId" class="info-value"></div>
                    </div>
                    <div class="info-group">
                        <label>Date & Time</label>
                        <div class="info-value">
                            <div id="modalOrderDate"></div>
                            <div id="modalOrderTime"></div>
                        </div>
                    </div>
                </div>

                <div class="order-items-list">
                    <div class="item">
                        <div class="item-details">
                            <h3 id="modalItemName"></h3>
                            <div class="item-meta">
                                <span id="modalItemQuantity"></span>
                                <span id="modalItemPrice"></span>
                            </div>
                        </div>
                </div>
            </div>

                <div class="customer-details">
                    <div class="info-group">
                        <label>Customer</label>
                        <div id="modalCustomerName" class="info-value"></div>
                    </div>
                    <div class="info-group">
                        <label>Total Payment</label>
                        <div id="modalTotalPayment" class="info-value total"></div>
                    </div>
            </div>
            </div>

            <div class="modal-footer">
                <button class="action-btn reject" onclick="closeModal()">Cancel</button>
                <button class="action-btn accept" onclick="acceptOrder()">Accept Order</button>
            </div>
        </div>
    </div>

    <!-- Rejection Confirmation Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content reject-modal">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirm Rejection</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject this order? This action cannot be undone.</p>
                </div>
            <div class="modal-footer">
                <button class="action-btn" onclick="closeRejectModal()">Cancel</button>
                <button class="action-btn reject" onclick="confirmReject()">Yes, Reject Order</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="toast-content">
            <i class="fas fa-check-circle"></i>
            <span id="toastMessage"></span>
        </div>
    </div>

    <style>
    .reject-modal {
        max-width: 400px;
    }
    .reject-modal .modal-header {
        background: #fff3f3;
        color: #dc3545;
    }
    .reject-modal .modal-header h2 {
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .reject-modal .modal-body {
        padding: 1.5rem;
        text-align: center;
    }
    .reject-modal .modal-footer {
        padding: 1rem;
        display: flex;
        justify-content: center;
        gap: 1rem;
    }
    .reject-modal .action-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
    }
    .reject-modal .action-btn:not(.reject) {
        background: #e9ecef;
        color: #495057;
    }
    .reject-modal .action-btn:not(.reject):hover {
        background: #dee2e6;
    }
    .reject-modal .action-btn.reject {
        background: #dc3545;
        color: white;
    }
    .reject-modal .action-btn.reject:hover {
        background: #c82333;
    }

    /* Alert Messages */
    .alert {
        padding: 12px 20px;
        border-radius: 4px;
        margin-bottom: 20px;
        font-weight: 500;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Order Cards */
    .order-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 15px;
        margin-bottom: 15px;
    }

    /* Toast Notification Styles */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #fff;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        display: none;
        z-index: 1100;
        animation: slideIn 0.3s ease;
    }

    .toast-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .toast i {
        color: #28a745;
        font-size: 1.4rem;
    }

    .toast span {
        color: #333;
        font-weight: 500;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    </style>

    <script>
    let currentOrderId = null;
    let pendingRejectOrderId = null;

        function showModal(orderId, orderData) {
        currentOrderId = orderId;
            const modal = document.getElementById('orderModal');
            
        // Populate modal with order data
            document.getElementById('modalOrderId').textContent = 'A' + orderId;
        document.getElementById('modalOrderDate').textContent = orderData.date;
        document.getElementById('modalOrderTime').textContent = orderData.time;
            document.getElementById('modalItemName').textContent = orderData.itemName;
        document.getElementById('modalItemQuantity').textContent = '×' + orderData.quantity;
            document.getElementById('modalItemPrice').textContent = '₱' + orderData.price;
            document.getElementById('modalCustomerName').textContent = orderData.customerName;
            document.getElementById('modalTotalPayment').textContent = '₱' + orderData.totalPayment;
            
            modal.style.display = 'flex';
        }

    function closeModal() {
        const modal = document.getElementById('orderModal');
        modal.style.display = 'none';
        currentOrderId = null;
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
            <span>${message}</span>
        `;
        const container = document.getElementById('toast-container');
        container.appendChild(toast);
        void toast.offsetWidth;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => { toast.remove(); }, 300);
        }, 3000);
    }
    
    function updateStatus(orderId, action) {
        let message = '';
        let url = 'manage_orders.php?' + action + '=' + orderId;
        
        switch(action) {
            case 'accept':
                message = 'Are you sure you want to mark this order as Preparing?';
                break;
            case 'ready':
                message = 'Mark this order as Ready for pickup?';
                break;
            case 'complete':
                message = 'Mark this order as Completed?';
                break;
            case 'reject':
                message = 'Are you sure you want to reject this order?';
                break;
        }
        
        if (confirm(message)) {
            window.location.href = url;
        }
    }

    function acceptOrder() {
        if (currentOrderId) {
            // Send acceptance to server
            window.location.href = 'manage_orders.php?accept=' + currentOrderId;
            
            // Remove the order card from UI
            const orderCard = document.getElementById('order-card-' + currentOrderId);
            if (orderCard) {
                    orderCard.remove();
            }
            closeModal();
            
            // Show success message
            showToast('Order successfully accepted! Processing will begin shortly.');
        }
        }

        function rejectOrder(orderId) {
        pendingRejectOrderId = orderId;
        const rejectModal = document.getElementById('rejectModal');
        rejectModal.style.display = 'flex';
    }

    function closeRejectModal() {
        const rejectModal = document.getElementById('rejectModal');
        rejectModal.style.display = 'none';
        pendingRejectOrderId = null;
    }

    function confirmReject() {
        if (pendingRejectOrderId) {
            // Send rejection to server
            window.location.href = 'manage_orders.php?reject=' + pendingRejectOrderId;
            
            // Remove the order card from UI
            const orderCard = document.getElementById('order-card-' + pendingRejectOrderId);
            if (orderCard) {
                    orderCard.remove();
            }
            closeRejectModal();
        }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const orderModal = document.getElementById('orderModal');
        const rejectModal = document.getElementById('rejectModal');
        if (event.target === orderModal) {
                closeModal();
            }
        if (event.target === rejectModal) {
            closeRejectModal();
        }
        }
    </script>
</body>
</html>