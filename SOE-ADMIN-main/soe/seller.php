<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

// Seller registration logic (if you want to add sellers from this page)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Handle resume upload (PDF only)
    $resume_path = "";
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['resume']['tmp_name'];
        $fileName = $_FILES['resume']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($fileExt === 'pdf') {
            $uploadDir = __DIR__ . '/uploads/resume/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFileName = uniqid('resume_', true) . '.pdf';
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $resume_path = 'uploads/resume/' . $newFileName;
            }
        }
    }

    // Handle stall photo upload (image only)
    $stall_filepath = "";
    if (isset($_FILES['stall_photo']) && $_FILES['stall_photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['stall_photo']['tmp_name'];
        $fileName = $_FILES['stall_photo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileExt, $allowedExts)) {
            $uploadDir = __DIR__ . '/uploads/stall/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFileName = uniqid('stall_', true) . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $stall_filepath = 'uploads/stall/' . $newFileName;
            }
        }
    }

    // Stall info
    $stall_name = mysqli_real_escape_string($conn, $_POST['stall_name']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);

    // Insert seller
    $query = "INSERT INTO seller (first_name, middle_name, last_name, email, password, resume_path, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssss", $first_name, $middle_name, $last_name, $email, $password, $resume_path);

    if (mysqli_stmt_execute($stmt)) {
        $seller_id = $conn->insert_id;
        // Insert stall and link to seller
        $query2 = "INSERT INTO stall (stall_name, stall_filepath, phone_number, id) VALUES (?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $query2);
        mysqli_stmt_bind_param($stmt2, "sssi", $stall_name, $stall_filepath, $phone_number, $seller_id);
        mysqli_stmt_execute($stmt2);

        header("Location: seller.php?success=1");
        exit();
    } else {
        $error = "Error submitting application: " . mysqli_error($conn);
    }
}

// Counts
$total_count = $conn->query("SELECT COUNT(*) as count FROM seller")->fetch_assoc()['count'];
$active_count = $conn->query("SELECT COUNT(*) as count FROM seller WHERE status = 'approved'")->fetch_assoc()['count'];
$new_count = $conn->query("SELECT COUNT(*) as count FROM seller WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];

// Fetch sellers
$sellers_result = $conn->query("SELECT * FROM seller ORDER BY created_at DESC");
$sellers = [];
while($row = $sellers_result->fetch_assoc()) {
    $sellers[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EZ-ORDER | Seller Management</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="seller.css">
    <script>
        function filterSellers(status) {
            const rows = document.querySelectorAll('.seller-table tbody tr');
            const activeBtn = document.querySelector('.filter-btn.active');
            const inactiveBtn = document.querySelector('.filter-btn.inactive');
            
            if (status === 'active') {
                activeBtn.classList.add('selected');
                inactiveBtn.classList.remove('selected');
            } else if (status === 'inactive') {
                inactiveBtn.classList.add('selected');
                activeBtn.classList.remove('selected');
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

        function searchSellers() {
            const searchInput = document.querySelector('.search-box input');
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('.seller-table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function showAddModal() {
            document.getElementById('addSellerModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addSellerModal').style.display = 'none';
            document.getElementById('addSellerForm').reset();
        }

        function handleSubmit(event) {
            event.preventDefault();
            const form = document.getElementById('addSellerForm');
            const formData = new FormData(form);

            fetch('seller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                closeAddModal();
                showSuccessModal();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function showSuccessModal() {
            document.getElementById('successModal').style.display = 'flex';
        }

        function closeSuccessModal() {
            document.getElementById('successModal').style.display = 'none';
            window.location.reload();
        }

        window.onclick = function(event) {
            const addModal = document.getElementById('addSellerModal');
            const successModal = document.getElementById('successModal');
            
            if (event.target == addModal) {
                closeAddModal();
            }
            if (event.target == successModal) {
                closeSuccessModal();
            }
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                showSuccessModal();
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
            <a href="seller.php"><strong>👤 Seller</strong></a>
            <a href="order.php">📦 Order</a>
            <a href="approval.php">🛂 Approval</a>
            <a href="reports.php">📋 Reports</a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <div class="dashboard-section">
                <div id="successMessage" class="success-message">
                    Seller added successfully!
                </div>

                <h2>Accounts</h2>
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="icon">
                            <img src="icons/total-sellers.png" alt="Total Sellers">
                        </div>
                        <div class="content">
                            <h3><?php echo $total_count; ?></h3>
                            <p>Total Sellers</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">
                            <img src="icons/new-sellers.png" alt="New Sellers">
                        </div>
                        <div class="content">
                            <h3><?php echo $new_count; ?></h3>
                            <p>New Sellers</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="icon">
                            <img src="icons/active-sellers.png" alt="Active Sellers">
                        </div>
                        <div class="content">
                            <h3><?php echo $active_count; ?></h3>
                            <p>Active Sellers</p>
                        </div>
                    </div>
                </div>

                <div class="seller-controls">
                    <div class="filter-buttons">
                        <button class="filter-btn active" onclick="filterSellers('active')">Active</button>
                        <button class="filter-btn inactive" onclick="filterSellers('inactive')">Inactive</button>
                        <button class="filter-btn add" onclick="showAddModal()">Add</button>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Search....." oninput="searchSellers()">
                    </div>
                </div>

                <div class="seller-table">
                    <table>
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sellers as $seller): ?>
                            <tr>   
                                <td><?php echo htmlspecialchars($seller['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($seller['middle_name']); ?></td>
                                <td><?php echo htmlspecialchars($seller['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($seller['email']); ?></td>
                                <td><span class="status-badge <?php echo $seller['status']; ?>"><?php echo ucfirst($seller['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($seller['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="addSellerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2>New Seller & Stall</h2>
            <form method="POST" action="seller.php" id="addSellerForm" enctype="multipart/form-data" onsubmit="handleSubmit(event)">
                <h3>Seller Information</h3>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="resume">Resume (optional)</label>
                    <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                </div>
                <hr>
                <h3>Stall Information</h3>
                <div class="form-group">
                    <label for="stall_name">Stall Name</label>
                    <input type="text" id="stall_name" name="stall_name" required>
                </div>
                <div class="form-group">
                    <label for="stall_photo">Stall Photo (optional)</label>
                    <input type="file" id="stall_photo" name="stall_photo" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-save">SAVE</button>
                    <button type="button" class="btn-cancel" onclick="closeAddModal()">CANCEL</button>
                </div>
            </form>
        </div>
    </div>

    <div id="successModal" class="modal">
        <div class="modal-content success-modal">
            <div class="success-animation">
                <div class="checkmark-circle">
                    <div class="checkmark"></div>
                </div>
            </div>
            <h2 class="success-title">Add Seller Successful!</h2>
            <button class="btn-ok" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
</body>

</html>