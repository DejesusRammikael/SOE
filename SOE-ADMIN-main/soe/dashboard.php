<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EZ-ORDER | Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <img src="logogo.png" alt="EZ-ORDER Logo" width="150">
            <div class="search">
                <input type="text" placeholder="Search...">
            </div>
            <a href="dashboard.php"><strong>📊 Dashboard</strong></a>
            <a href="seller.php">👤 Seller</a>
            <a href="order.php">📦 Order</a>
            <a href="approval.php">🛂 Approval</a>
            <a href="reports.php">📋 Reports</a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <div class="dashboard-section">
                <h2>Live Orders</h2>
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="icon">🛍️</div>
                        <div class="content">
                            <h3>47</h3>
                            <p>Orders</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">🕒</div>
                        <div class="content">
                            <h3>56</h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">✅</div>
                        <div class="content">
                            <h3>50</h3>
                            <p>Completed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Active Sellers</h2>
                <div class="card">
                    <div class="icon">⭐</div>
                    <div class="content">
                        <h3>30</h3>
                        <p>Total Active Sellers</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Top Stalls</h2>
                <table class="top-stalls">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Ratings</th>
                            <th>Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>01</td>
                            <td>Stall A</td>
                            <td class="stars">★★★★★</td>
                            <td><span class="badge">50%</span></td>
                        </tr>
                        <tr>
                            <td>02</td>
                            <td>Stall E</td>
                            <td class="stars">★★★★★</td>
                            <td><span class="badge">49.5%</span></td>
                        </tr>
                        <tr>
                            <td>03</td>
                            <td>Stall D</td>
                            <td class="stars">★★★★☆</td>
                            <td><span class="badge">40%</span></td>
                        </tr>
                        <tr>
                            <td>04</td>
                            <td>Stall F</td>
                            <td class="stars">★★★☆☆</td>
                            <td><span class="badge">35%</span></td>
                        </tr>
                        <tr>
                            <td>05</td>
                            <td>Stall B</td>
                            <td class="stars">★★☆☆☆</td>
                            <td><span class="badge">28%</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="dashboard-section">
                <h2>Top Products</h2>
                <table class="top-products">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Ratings</th>
                            <th>Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Siomai Rice</td>
                            <td>Stall A</td>
                            <td class="stars">★★★★★</td>
                            <td><span class="badge">50%</span></td>
                        </tr>
                        <tr>
                            <td>Tuna Sandwich</td>
                            <td>Stall E</td>
                            <td class="stars">★★★★★</td>
                            <td><span class="badge">49.5%</span></td>
                        </tr>
                        <tr>
                            <td>C. Katsu</td>
                            <td>Stall D</td>
                            <td class="stars">★★★★☆</td>
                            <td><span class="badge">40%</span></td>
                        </tr>
                        <tr>
                            <td>Finger Chicken</td>
                            <td>Stall F</td>
                            <td class="stars">★★★☆☆</td>
                            <td><span class="badge">35%</span></td>
                        </tr>
                        <tr>
                            <td>Milktea</td>
                            <td>Stall B</td>
                            <td class="stars">★★☆☆☆</td>
                            <td><span class="badge">28%</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>