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
    <title>EZ-ORDER | Complaints</title>
    <link rel="stylesheet" href="reports.css">
    <link rel="stylesheet" href="complaints.css">
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
            <a href="reports.php">📋 Reports</a>
            <div class="logout">
                <a href="logout.php">↩ Logout</a>
            </div>
        </div>

        <div class="main">
            <div class="back-button" onclick="window.location.href='reports.php'">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </div>

            <section class="complaints-section">
                <h2><i class="fas fa-store"></i> Reported Stalls</h2>
                <div class="complaints-grid">
                    <div class="complaint-card" onclick="openStallModal('Stall B', 'Too salty', '10:30 am', '01/09/2024', 'Poor food quality', 'food1.jpg', 'John Doe')">
                        <div class="complaint-icon">
                            <img src="stall-icon.png" alt="Stall Icon">
                        </div>
                        <div class="complaint-info">
                            <h3>Stall B</h3>
                            <p>Poor food quality complaint from customer</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <div class="complaint-card" onclick="openStallModal('Stall C', 'Food was served cold and presentation was poor', '11:45 am', '01/09/2024', 'Service quality', 'food2.jpg', 'Mary Smith')">
                        <div class="complaint-icon">
                            <img src="stall-icon.png" alt="Stall Icon">
                        </div>
                        <div class="complaint-info">
                            <h3>Stall C</h3>
                            <p>Service quality issues reported</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <div class="complaint-card" onclick="openStallModal('Stall D', 'Portion size is too small for the price charged', '2:15 pm', '01/09/2024', 'Price concern', 'food3.jpg', 'James Wilson')">
                        <div class="complaint-icon">
                            <img src="stall-icon.png" alt="Stall Icon">
                        </div>
                        <div class="complaint-info">
                            <h3>Stall D</h3>
                            <p>Price and portion size complaint</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <div class="complaint-card" onclick="openStallModal('Stall E', 'Long waiting time and unfriendly service', '3:30 pm', '01/09/2024', 'Poor service', 'food4.jpg', 'Sarah Johnson')">
                        <div class="complaint-icon">
                            <img src="stall-icon.png" alt="Stall Icon">
                        </div>
                        <div class="complaint-info">
                            <h3>Stall E</h3>
                            <p>Customer service complaint</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="complaints-section">
                <h2><i class="fas fa-user"></i> Reported Users</h2>
                <div class="complaints-grid">
                    <div class="complaint-card" onclick="openUserModal('John Doe', 'I would like to report a suspected scam that I encountered.', '10:30 am', '01/09/2024', 'Scam', 'Stall A')">
                        <div class="complaint-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="complaint-info">
                            <h3>John Doe</h3>
                            <p>Reported for suspicious activity</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <div class="complaint-card" onclick="openUserModal('Jeon Wonwoo', 'User has been making multiple fake orders and not showing up.', '11:20 am', '01/09/2024', 'Fake Orders', 'Stall B')">
                        <div class="complaint-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="complaint-info">
                            <h3>Jeon Wonwoo</h3>
                            <p>Multiple fake order reports</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <div class="complaint-card" onclick="openUserModal('Mingyu Kim', 'Inappropriate behavior and harassment towards staff.', '1:45 pm', '01/09/2024', 'Harassment', 'Stall C')">
                        <div class="complaint-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="complaint-info">
                            <h3>Mingyu Kim</h3>
                            <p>Harassment report</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

                    <div class="complaint-card" onclick="openUserModal('John Smith', 'Payment issues and refusal to pay for ordered items.', '4:15 pm', '01/09/2024', 'Payment Issues', 'Stall D')">
                        <div class="complaint-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="complaint-info">
                            <h3>John Smith</h3>
                            <p>Payment dispute reported</p>
                        </div>
                        <div class="complaint-actions">
                            <button class="dismiss-btn" onclick="event.stopPropagation()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div id="stallModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reported by <span id="stallReporter"></span></h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="complaint-details">
                    <div class="food-image">
                        <img id="foodImage" src="" alt="Food Image">
                    </div>
                    <div class="details-grid">
                        <div class="detail-row">
                            <label>Full Name</label>
                            <span id="stallName"></span>
                        </div>
                        <div class="detail-row">
                            <label>Time</label>
                            <span id="stallTime"></span>
                        </div>
                        <div class="detail-row">
                            <label>Date</label>
                            <span id="stallDate"></span>
                        </div>
                        <div class="detail-row">
                            <label>Selected Reason</label>
                            <span id="stallReason"></span>
                        </div>
                        <div class="detail-row">
                            <label>Description</label>
                            <div class="description-box">
                                <span id="stallDescription"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="reply-btn">Reply</button>
            </div>
        </div>
    </div>

    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reported by <span id="userReporter"></span></h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="complaint-details">
                    <div class="user-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="details-grid">
                        <div class="detail-row">
                            <label>Full Name</label>
                            <span id="userName"></span>
                        </div>
                        <div class="detail-row">
                            <label>Time</label>
                            <span id="userTime"></span>
                        </div>
                        <div class="detail-row">
                            <label>Date</label>
                            <span id="userDate"></span>
                        </div>
                        <div class="detail-row">
                            <label>Selected Reason</label>
                            <span id="userReason"></span>
                        </div>
                        <div class="detail-row">
                            <label>Description</label>
                            <div class="description-box">
                                <span id="userDescription"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="reply-btn">Reply</button>
            </div>
        </div>
    </div>

    <div id="stallReplyModal" class="modal">
        <div class="modal-content reply-modal">
            <div class="modal-header">
                <h2>To <span id="stallReplyName"></span></h2>
                <span class="close-modal" onclick="closeReplyModal('stallReplyModal')">&times;</span>
            </div>
            <div class="modal-body">
                <textarea class="reply-textarea" placeholder="Type your reply here...">Hello our dear customer were sorry about the inconvenience</textarea>
                <button class="send-btn">Send</button>
            </div>
        </div>
    </div>

    <div id="userReplyModal" class="modal">
        <div class="modal-content reply-modal">
            <div class="modal-header">
                <h2>To <span id="userReplyName"></span></h2>
                <span class="close-modal" onclick="closeReplyModal('userReplyModal')">&times;</span>
            </div>
            <div class="modal-body">
                <textarea class="reply-textarea" placeholder="Type your reply here...">Hello our dear customer were sorry about the inconvenience</textarea>
                <button class="send-btn">Send</button>
            </div>
        </div>
    </div>

    <div id="successModal" class="modal success-modal">
        <div class="modal-content success-content">
            <div class="success-message">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2>Reply Sent</h2>
            </div>
        </div>
    </div>

    <script>
        const stallModal = document.getElementById('stallModal');
        const userModal = document.getElementById('userModal');
        const stallReplyModal = document.getElementById('stallReplyModal');
        const userReplyModal = document.getElementById('userReplyModal');
        const successModal = document.getElementById('successModal');
        const allModals = [stallModal, userModal, stallReplyModal, userReplyModal, successModal];

        function closeAllModals() {
            allModals.forEach(modal => {
                if (modal) {
                    modal.style.display = 'none';
                }
            });
        }

        function closeModal(modal) {
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function openStallModal(name, description, time, date, reason, imageUrl, reporter) {
            closeAllModals();
            document.getElementById('stallName').textContent = name;
            document.getElementById('stallDescription').textContent = description;
            document.getElementById('stallTime').textContent = time;
            document.getElementById('stallDate').textContent = date;
            document.getElementById('stallReason').textContent = reason;
            document.getElementById('stallReporter').textContent = reporter;
            document.getElementById('foodImage').src = imageUrl;
            stallModal.style.display = 'block';

            const replyBtn = stallModal.querySelector('.reply-btn');
            replyBtn.onclick = () => openStallReplyModal(name);
        }

        function openUserModal(name, description, time, date, reason, reporter) {
            closeAllModals(); 
            document.getElementById('userName').textContent = name;
            document.getElementById('userDescription').textContent = description;
            document.getElementById('userTime').textContent = time;
            document.getElementById('userDate').textContent = date;
            document.getElementById('userReason').textContent = reason;
            document.getElementById('userReporter').textContent = reporter;
            userModal.style.display = 'block';
n
            const replyBtn = userModal.querySelector('.reply-btn');
            replyBtn.onclick = () => openUserReplyModal(name);
        }

        function openStallReplyModal(name) {
            closeAllModals();
            document.getElementById('stallReplyName').textContent = name;
            stallReplyModal.style.display = 'block';
        }

        function openUserReplyModal(name) {
            closeAllModals(); 
            document.getElementById('userReplyName').textContent = name;
            userReplyModal.style.display = 'block';
        }

        function showSuccessModal() {
            closeAllModals(); 
            
            successModal.style.display = 'block';
            
            setTimeout(() => {
                closeModal(successModal);
            }, 2000);
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                if (event.target === successModal) {
                    closeModal(successModal);
                } else {
                    closeModal(event.target);
                }
            }
        }

        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            });
        });

        document.querySelectorAll('.send-btn').forEach(button => {
            button.addEventListener('click', showSuccessModal);
        });

        document.querySelectorAll('.dismiss-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const card = this.closest('.complaint-card');
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                }, 300);
            });
        });
    </script>
</body>

</html> 