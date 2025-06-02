<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$preparing_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'preparing'")->fetch_assoc()['count'];
$completed_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'")->fetch_assoc()['count'];
$pending_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];

$orders_result = $conn->query("SELECT *, (price * item_quantity) as total_price FROM orders ORDER BY id DESC");
$orders = [];
while($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EZ-ORDER | Order Management</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="order.css">
    <script>
        function filterOrders(status) {
            const rows = document.querySelectorAll('.order-table tbody tr');
            const preparingBtn = document.querySelector('.filter-btn.preparing');
            const completedBtn = document.querySelector('.filter-btn.completed');
            const pendingBtn = document.querySelector('.filter-btn.pending');
            
            preparingBtn.classList.remove('selected');
            completedBtn.classList.remove('selected');
            pendingBtn.classList.remove('selected');

            if (status === 'preparing') {
                preparingBtn.classList.add('selected');
            } else if (status === 'completed') {
                completedBtn.classList.add('selected');
            } else if (status === 'pending') {
                pendingBtn.classList.add('selected');
            }

            rows.forEach(row => {
                const statusCell = row.querySelector('.status-badge');
                if (status === 'all' || statusCell.classList.contains(status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function searchOrders() {
            const searchInput = document.querySelector('.search-box input');
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('.order-table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function viewDetails(orderId) {
            const modal = document.getElementById('orderModal');
            const orderData = <?php echo json_encode($orders); ?>;
            const order = orderData.find(o => o.id == orderId);
            
            if (order) {
                document.getElementById('orderImage').src = `images/${order.order_name.toLowerCase().replace(' ', '_')}.png`;
                document.getElementById('orderProduct').textContent = order.order_name;
                document.getElementById('orderQuantity').textContent = order.item_quantity;
                document.getElementById('orderPrice').textContent = order.price;
                document.getElementById('orderBy').textContent = order.ordered_by;
                document.getElementById('orderTime').textContent = order.order_time;
                document.getElementById('orderDate').textContent = new Date(order.order_date).toLocaleDateString();
                document.getElementById('paymentStatus').textContent = order.payment_status;
                document.getElementById('totalPrice').textContent = order.total_price;
                
                modal.style.display = 'flex';
            }
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <img src="logogo.png" alt="EZ-ORDER Logo" width="150">
            <div class="search">
                <input type="text" placeholder="Search...">
            </div>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="seller.php">👤 Seller</a>
            <a href="order.php"><strong>📦 Order</strong></a>
            <a href="approval.php">🛂 Approval</a>
            <a href="reports.php">📋 Reports</a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <div class="dashboard-section">
                <h2>Orders</h2>

                <div class="order-controls">
                    <div class="filter-buttons">
                        <button class="filter-btn preparing" onclick="filterOrders('preparing')">Preparing</button>
                        <button class="filter-btn completed" onclick="filterOrders('completed')">Completed</button>
                        <button class="filter-btn pending" onclick="filterOrders('pending')">Pending</button>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Search....." oninput="searchOrders()">
                    </div>
                </div>

                <div class="order-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Orders</th>
                                <th>Item No.</th>
                                <th>Stall Name</th>
                                <th>Order Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['item_quantity']) . 'x'; ?></td>
                                <td><?php echo htmlspecialchars($order['stall_name']); ?></td>
                                <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><button class="details-btn" onclick="viewDetails(<?php echo $order['id']; ?>)">ℹ️</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="order-info">
                    <table class="details-table">
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                        <tr>
                            <td><img id="orderImage" src="" alt="Product" width="50" height="50"></td>
                            <td id="orderProduct"></td>
                            <td id="orderQuantity"></td>
                            <td id="orderPrice"></td>
                        </tr>
                    </table>
                    
                    <div class="order-details">
                        <p>Ordered by: <span id="orderBy"></span></p>
                        <p>Time: <span id="orderTime"></span></p>
                        <p>Date: <span id="orderDate"></span></p>
                        <p>Payment Status: <span id="paymentStatus"></span></p>
                        <p class="total-price">Total Price: ₱<span id="totalPrice"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html> 