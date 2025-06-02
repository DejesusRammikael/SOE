<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = mysqli_real_escape_string($conn, $_POST['application_id']);
    $action = mysqli_real_escape_string($conn, $_POST['action']);

    try {
        if ($action === 'approve') {
            // Approve seller directly using id
            $query = "UPDATE seller SET status='approved' WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $application_id);
            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'Application approved successfully']);
            exit();
        } else if ($action === 'reject') {
            $query = "UPDATE seller SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $application_id);
            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'Application rejected successfully']);
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}

$stmt = $conn->query("SELECT * FROM seller WHERE status = 'pending' ORDER BY created_at DESC");
$applications = [];
while ($row = mysqli_fetch_assoc($stmt)) {
    $applications[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EZ-ORDER | Approval Management</title>
    <link rel="stylesheet" href="approval.css">
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <img src="logogo.png" alt="EZ-ORDER Logo" width="150">
            <div class="search">
                <input type="text" placeholder="Search">
            </div>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="seller.php">👤 Seller</a>
            <a href="order.php">📦 Order</a>
            <a href="approval.php"><strong>🛂 Approval</strong></a>
            <a href="reports.php">📋 Reports</a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <h1>Approval</h1>
            <input type="text" class="search-box" placeholder="Search....">
            
            <div id="alert-container"></div>

            <table class="approval-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Resume</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Approvals</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $index => $app): ?>
                    <tr data-id="<?php echo $app['id']; ?>" 
                        data-stall="<?php echo htmlspecialchars($app['first_name']); ?>"
                        data-email="<?php echo htmlspecialchars($app['email']); ?>"
                        data-password="<?php echo htmlspecialchars($app['password']); ?>">
                        <td><?php echo $index + 1; ?>.</td>
                        <td><?php echo htmlspecialchars($app['first_name']); ?></td>
                        <td>
                            <?php if (!empty($app['resume_path'])): ?>
                                <a href="<?php echo htmlspecialchars($app['resume_path']); ?>" target="_blank" download>
                                    Download Resume
                                </a>
                            <?php else: ?>
                                No Resume
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('m/d/y | H:i', strtotime($app['created_at'])); ?></td>
                        <td><span class="status-pending">Pending</span></td>
                        <td>
                            <button class="btn-approve" onclick="handleApproval(this, 'approve')">✓</button>
                            <button class="btn-reject" onclick="handleApproval(this, 'reject')">✕</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="approvalModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Approval</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this stall application?</p>
                <div class="stall-details">
                    <p><strong>Stall Name:</strong> <span id="modalStallName"></span></p>
                    <p><strong>Owner:</strong> <span id="modalOwnerName"></span></p>
                    <p><strong>Contact:</strong> <span id="modalContact"></span></p>
                    <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-confirm" onclick="confirmAction('approve')">Yes, Approve</button>
                <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Rejection</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject this stall application?</p>
                <div class="stall-details">
                    <p><strong>Stall Name:</strong> <span id="modalRejectStallName"></span></p>
                    <p><strong>Owner:</strong> <span id="modalRejectOwnerName"></span></p>
                </div>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-reject" onclick="confirmAction('reject')">Yes, Reject</button>
                <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="approveSuccessModal" class="modal" style="display: none;">
        <div class="modal-content success-modal">
            <div class="success-animation">
                <div class="checkmark-circle">
                    <div class="checkmark"></div>
                </div>
            </div>
            <h2 class="success-title">Application Approved Successfully!</h2>
            <button class="btn-ok" onclick="closeSuccessModal('approve')">OK</button>
        </div>
    </div>

    <div id="rejectSuccessModal" class="modal" style="display: none;">
        <div class="modal-content success-modal">
            <div class="success-animation">
                <div class="reject-circle">
                    <div class="cross"></div>
                </div>
            </div>
            <h2 class="success-title">Application Rejected Successfully!</h2>
            <button class="btn-ok" onclick="closeSuccessModal('reject')">OK</button>
        </div>
    </div>

    <script>
    let currentApplicationData = null;

    function showModal(action, data) {
        currentApplicationData = data;
        
        if (action === 'approve') {
            document.getElementById('modalStallName').textContent = data.stall;
            document.getElementById('modalOwnerName').textContent = data.owner;
            document.getElementById('modalContact').textContent = data.contact;
            document.getElementById('modalEmail').textContent = data.email;
            document.getElementById('approvalModal').style.display = 'flex';
        } else if (action === 'reject') {
            document.getElementById('modalRejectStallName').textContent = data.stall;
            document.getElementById('modalRejectOwnerName').textContent = data.owner;
            document.getElementById('rejectModal').style.display = 'flex';
        }
    }

    function closeModal() {
        document.getElementById('approvalModal').style.display = 'none';
        document.getElementById('rejectModal').style.display = 'none';
        currentApplicationData = null;
    }

    function handleApproval(button, action) {
        const row = button.closest('tr');
        const data = {
            application_id: row.dataset.id,
            action: action,
            stall_name: row.dataset.stall,
            owner_name: row.dataset.owner,
            contact: row.dataset.contact,
            email: row.dataset.email,
            password: row.dataset.password
        };

        fetch('approval.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                row.remove();
                showSuccessModal(action);
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('An error occurred while processing the request.', 'error');
        });
    }

    function showSuccessModal(action) {
        const modalId = action === 'approve' ? 'approveSuccessModal' : 'rejectSuccessModal';
        document.getElementById(modalId).style.display = 'flex';
    }

    function closeSuccessModal(action) {
        const modalId = action === 'approve' ? 'approveSuccessModal' : 'rejectSuccessModal';
        document.getElementById(modalId).style.display = 'none';
    }

    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 3000);
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
    </script>
</body>

</html>