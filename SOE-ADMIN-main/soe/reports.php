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
    <title>EZ-ORDER | Reports</title>
    <link rel="stylesheet" href="reports.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <a href="order.php">📦 Order</a>
            <a href="approval.php">🛂 Approval</a>
            <a href="reports.php"><strong>📋 Reports</strong></a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <h1>Reports</h1>
            
            <div class="header-actions">
                <button class="complaints-btn" onclick="window.location.href='complaints.php'">
                    <i class="fas fa-flag"></i> Complaints
                </button>
            </div>

            <div class="reports-grid">
                <div class="report-card" onclick="showReport('user')">
                    <div class="report-header">
                        <div class="report-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h2 class="report-title">User</h2>
                    </div>
                    <div class="report-content">
                        <p class="report-description">View and analyze user activity, registration trends, and engagement metrics.</p>
                    </div>
                </div>

                <div class="report-card" onclick="showReport('seller')">
                    <div class="report-header">
                        <div class="report-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h2 class="report-title">Seller</h2>
                    </div>
                    <div class="report-content">
                        <p class="report-description">Track seller performance, sales analytics, and stall management statistics.</p>
                    </div>
                </div>
            </div>

            <div id="reportDetails" class="report-details" style="display: none;">
                <div class="report-filters">
                    <div class="date-filter">
                        <label>From:</label>
                        <input type="date" id="dateFrom">
                        <label>To:</label>
                        <input type="date" id="dateTo">
                    </div>
                    <button class="filter-btn" onclick="applyFilters()">Apply Filters</button>
                </div>

                <div class="report-table-container">
                    <table class="report-table">
                        <thead>
                            <tr id="tableHeaders">
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
                    </table>
                </div>

                <button class="export-btn" onclick="exportReport()">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
        </div>
    </div>

    <script>
        function showReport(type) {
            const reportDetails = document.getElementById('reportDetails');
            reportDetails.style.display = 'block';
            
            const headers = type === 'user' 
                ? ['User ID', 'Name', 'Email', 'Registration Date', 'Orders Made']
                : ['Seller ID', 'Stall Name', 'Owner', 'Status', 'Total Sales'];
            
            setTableHeaders(headers);
            loadReportData(type);
        }

        function setTableHeaders(headers) {
            const headerRow = document.getElementById('tableHeaders');
            headerRow.innerHTML = headers.map(header => `<th>${header}</th>`).join('');
        }

        function loadReportData(type) {
            const data = type === 'user' 
                ? [
                    ['001', 'John Doe', 'john@example.com', '2024-01-15', '25'],
                    ['002', 'Jane Smith', 'jane@example.com', '2024-01-16', '18'],
                    ['003', 'Mike Johnson', 'mike@example.com', '2024-01-17', '32']
                ]
                : [
                    ['S001', 'Food Corner A', 'John Smith', 'Active', '₱15,000'],
                    ['S002', 'Snack Haven', 'Mary Johnson', 'Active', '₱12,500'],
                    ['S003', 'Drinks Paradise', 'Robert Wilson', 'Inactive', '₱8,000']
                ];

            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = data.map(row => 
                `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`
            ).join('');
        }

        function applyFilters() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            console.log('Applying filters:', { dateFrom, dateTo });
        }

        function exportReport() {
            console.log('Exporting report...');
            alert('Report exported successfully!');
        }
    </script>
</body>

</html> 