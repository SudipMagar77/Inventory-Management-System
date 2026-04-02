<?php
include '../includes/config.php';
include '../auth/auth_check.php';

// Handle Add Customer
if (isset($_POST['add_customer'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // ✅ ADD THIS - Check if phone already exists
    $phone_check = mysqli_query($conn, "SELECT id FROM customers WHERE phone = '$phone'");
    if (mysqli_num_rows($phone_check) > 0) {
        $_SESSION['error'] = " Phone number already registered!";
        header("Location: customer.php");
        exit();
    }

    // ✅ ADD THIS - Check if email already exists
    $email_check = mysqli_query($conn, "SELECT id FROM customers WHERE email = '$email'");
    if (mysqli_num_rows($email_check) > 0) {
        $_SESSION['error'] = " Email address already registered!";
        header("Location: customer.php");
        exit();
    }

    // Only proceed if no duplicates found
    $query = "INSERT INTO customers (name, phone, email, address) 
              VALUES ('$name', '$phone', '$email', '$address')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Customer added successfully!";
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
    }
    header("Location: customer.php");
    exit();
}


// Handle Update Customer
if (isset($_POST['update_customer'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // ✅ Check if phone already exists for OTHER customers (excluding current)
    $phone_check = mysqli_query($conn, "SELECT id FROM customers WHERE phone = '$phone' AND id != $id");
    if (mysqli_num_rows($phone_check) > 0) {
        $_SESSION['error'] = " Phone number already registered to another customer!";
        header("Location: customer.php");
        exit();
    }

    // ✅ Check if email already exists for OTHER customers (excluding current)
    $email_check = mysqli_query($conn, "SELECT id FROM customers WHERE email = '$email' AND id != $id");
    if (mysqli_num_rows($email_check) > 0) {
        $_SESSION['error'] = " Email address already registered to another customer!";
        header("Location: customer.php");
        exit();
    }

    $query = "UPDATE customers SET name='$name', phone='$phone', email='$email', address='$address' WHERE id=$id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Customer updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating: " . mysqli_error($conn);
    }
    header("Location: customer.php");
    exit();
}
// Handle Delete Customer
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);

    // Check if customer has sales
    $check = mysqli_query($conn, "SELECT id FROM sales WHERE customer_id = $id LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Cannot delete customer with existing sales records!";
    } else {
        $query = "DELETE FROM customers WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = "Customer deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting: " . mysqli_error($conn);
        }
    }
    header("Location: customer.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = "WHERE name LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%'";
}

// Get customers
$query = "SELECT * FROM customers $where ORDER BY id DESC";
$customers = mysqli_query($conn, $query);

// Get customer for view
$view_customer = null;
if (isset($_GET['view'])) {
    $view_id = mysqli_real_escape_string($conn, $_GET['view']);
    $view_result = mysqli_query($conn, "SELECT * FROM customers WHERE id = $view_id");
    if ($view_result && mysqli_num_rows($view_result) > 0) {
        $view_customer = mysqli_fetch_assoc($view_result);
    }
}

// Get customer for edit
$edit_customer = null;
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM customers WHERE id = $edit_id");
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_customer = mysqli_fetch_assoc($edit_result);
    }
}
?>
<?php include '../includes/header.php'; ?>

<style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        overflow-y: auto;
    }

    .modal-content {
        background-color: white;
        margin: 30px auto;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
        position: relative;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .modal-header h2 {
        margin: 0;
        color: #333;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
        text-decoration: none;
    }

    .close-btn:hover {
        color: #333;
    }

    .modal-body {
        margin-bottom: 20px;
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #555;
        font-weight: bold;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        font-size: 14px;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: #e0e0e0;
        color: #333;
    }

    .btn-secondary:hover {
        background: #d0d0d0;
    }

    .btn-danger {
        background: #f44336;
        color: white;
    }

    .customer-info {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
    }

    .customer-info p {
        margin: 12px 0;
        font-size: 16px;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 8px;
    }

    .customer-info p:last-child {
        border-bottom: none;
    }

    .customer-info strong {
        color: #555;
        min-width: 100px;
        display: inline-block;
    }

    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    .alert-error {
        background: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .search-box form {
        display: flex;
        gap: 10px;
    }

    .search-box input {
        padding: 10px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        width: 250px;
    }

    .data-table {
        width: 100%;
        background: white;
        border-collapse: collapse;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .data-table thead {
        background:black;
        color: white;
    }

    .data-table th,
    .data-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    .data-table tbody tr:hover {
        background: #f5f5f5;
    }

    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-view,
    .btn-edit,
    .btn-delete {
        padding: 5px 10px;
        border-radius: 3px;
        text-decoration: none;
        font-size: 12px;
        font-weight: bold;
    }

    .btn-view {
        background: #4CAF50;
        color: white;
    }

    .btn-edit {
        background: #2196F3;
        color: white;
    }

    .btn-delete {
        background: #f44336;
        color: white;
    }
</style>

<h1>Customer Management</h1>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['message'];
                                        unset($_SESSION['message']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><?php echo $_SESSION['error'];
                                    unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="page-header">
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by name, phone or email..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?>
                <a href="customer.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="add-btn">
        <button onclick="openModal('addModal')" class="btn btn-primary">Add New Customer</button>
    </div>
</div>

<!-- Add Customer Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Customer</h2>
            <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" required placeholder="Enter customer name">
                </div>

                <div class="form-group">
                    <label>Phone:</label>
                    <input type="tel" name="phone" required placeholder="Enter 10 digit phone number"
                        pattern="[0-9]{10}" maxlength="10"
                        onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required placeholder="Enter email address">
                </div>

                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" rows="3" required placeholder="Enter address"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" name="add_customer" class="btn btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<!-- View Customer Modal -->
<?php if ($view_customer): ?>
    <div id="viewModal" class="modal" style="display:block;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Customer Details</h2>
                <a href="customer.php" class="close-btn">&times;</a>
            </div>
            <div class="modal-body">
                <div class="customer-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($view_customer['name']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($view_customer['phone']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($view_customer['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($view_customer['address'])); ?></p>
                    <p><strong>Registered on:</strong> <?php echo date('d-m-Y', strtotime($view_customer['created_at'])); ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="customer.php" class="btn btn-secondary">Close</a>
                <a href="?edit=<?php echo $view_customer['id']; ?>" class="btn btn-primary">Edit Customer</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Edit Customer Modal -->
<?php if ($edit_customer): ?>
    <div id="editModal" class="modal" style="display:block;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Customer</h2>
                <a href="customer.php" class="close-btn">&times;</a>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $edit_customer['id']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($edit_customer['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($edit_customer['phone']); ?>"
                            required pattern="[0-9]{10}" maxlength="10"
                            onkeyup="this.value=this.value.replace(/[^0-9]/g,'')">
                    </div>

                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($edit_customer['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Address:</label>
                        <textarea name="address" rows="3" required><?php echo htmlspecialchars($edit_customer['address']); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="customer.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="update_customer" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<h2>Customer List</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>S.N.</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($customers) > 0): ?>
            <?php $sn = 1; ?>
            <?php while ($row = mysqli_fetch_assoc($customers)): ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars(substr($row['address'], 0, 30)) . (strlen($row['address']) > 30 ? '...' : ''); ?></td>
                    <td class="actions">
                        <a href="?view=<?php echo $row['id']; ?>" class="btn-view">View</a>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>"
                            class="btn-delete"
                            onclick="return confirm('Are you sure you want to delete this customer?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px;">
                    No customers found.
                    <?php if ($search): ?>
                        <br><a href="customer.php" class="btn btn-secondary" style="margin-top: 10px;">Clear Search</a>
                    <?php else: ?>
                        <br><button onclick="openModal('addModal')" class="btn btn-primary" style="margin-top: 10px;">Add Your First Customer</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // Modal functions
    function openModal(id) {
        document.getElementById(id).style.display = 'block';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }

    // Phone number validation (only numbers)
    document.querySelectorAll('input[type="tel"]').forEach(function(input) {
        input.addEventListener('keyup', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
</script>

<?php include '../includes/footer.php'; ?>