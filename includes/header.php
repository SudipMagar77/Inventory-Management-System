<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Inventory Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/validation.js" defer></script>
    <style>
        /* Logout Modal Styles */
        .logout-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        .logout-modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .logout-modal h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .logout-modal p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .logout-modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .logout-btn,
        .cancel-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(244, 67, 54, 0.4);
        }

        .cancel-btn {
            background: #e0e0e0;
            color: #333;
        }

        .cancel-btn:hover {
            background: #d0d0d0;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['admin_id'])): ?>
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-brand">GIMS</div>
                <ul class="nav-menu">
                    <li><a href="../pages/dashboard.php">Dashboard</a></li>
                    <li><a href="../pages/category.php">Category</a></li>
                    <li><a href="../pages/product.php">Product</a></li>
                    <li><a href="../pages/customer.php">Customer</a></li>
                    <li><a href="../pages/supplier.php">Supplier</a></li>
                    <li><a href="../pages/purchase.php">Purchases</a></li>
                    <li><a href="../pages/sales.php">Sales</a></li>
                    <li><a href="../pages/report.php">Report</a></li>
                    <li><a href="#" onclick="showLogoutModal(event)">Logout</a></li>
                </ul>
            </div>
        </nav>

        <!-- Logout Confirmation Modal -->
        <div id="logoutModal" class="logout-modal">
            <div class="logout-modal-content">
                <h3>⚠️ Logout</h3>
                <p>Are you sure you want to logout?</p>
                <div class="logout-modal-actions">
                    <a href="../auth/logout.php" class="logout-btn">Yes, Logout</a>
                    <button onclick="hideLogoutModal()" class="cancel-btn">Cancel</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="container">

        <script>
            // Show logout modal
            function showLogoutModal(event) {
                event.preventDefault();
                document.getElementById('logoutModal').style.display = 'block';
            }

            // Hide logout modal
            function hideLogoutModal() {
                document.getElementById('logoutModal').style.display = 'none';
            }

            // Close modal if user clicks outside
            window.onclick = function(event) {
                const modal = document.getElementById('logoutModal');
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }

            // Close modal with Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    hideLogoutModal();
                }
            });
        </script>