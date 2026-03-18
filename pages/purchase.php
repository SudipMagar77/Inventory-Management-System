<?php
include '../includes/config.php';
include '../auth/auth_check.php';

// Handle Add Purchase
if (isset($_POST['add_purchase'])) {
    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    $purchase_date = mysqli_real_escape_string($conn, $_POST['purchase_date']);

    // Insert purchase
    $query = "INSERT INTO purchases (supplier_id, purchase_date, total_amount) 
              VALUES ('$supplier_id', '$purchase_date', 0)";
    mysqli_query($conn, $query);
    $purchase_id = mysqli_insert_id($conn);

    $total_amount = 0;

    // Insert purchase items
    if (isset($_POST['products'])) {
        $products = $_POST['products'];
        $quantities = $_POST['quantities'];
        $prices = $_POST['prices'];

        for ($i = 0; $i < count($products); $i++) {
            if (!empty($products[$i]) && !empty($quantities[$i]) && !empty($prices[$i])) {
                $product_id = mysqli_real_escape_string($conn, $products[$i]);
                $quantity = mysqli_real_escape_string($conn, $quantities[$i]);
                $price = mysqli_real_escape_string($conn, $prices[$i]);
                $amount = $quantity * $price;

                $item_query = "INSERT INTO purchase_items (purchase_id, product_id, quantity, price, amount) 
                              VALUES ('$purchase_id', '$product_id', '$quantity', '$price', '$amount')";
                mysqli_query($conn, $item_query);

                // Update product stock
                mysqli_query($conn, "UPDATE products SET stock_quantity = stock_quantity + $quantity WHERE id = $product_id");

                $total_amount += $amount;
            }
        }
    }

    // Update total amount
    mysqli_query($conn, "UPDATE purchases SET total_amount = $total_amount WHERE id = $purchase_id");

    $_SESSION['message'] = "Purchase added successfully!";
    header("Location: purchase.php");
    exit();
}

// Handle Update Purchase
if (isset($_POST['update_purchase'])) {
    $purchase_id = mysqli_real_escape_string($conn, $_POST['purchase_id']);
    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    $purchase_date = mysqli_real_escape_string($conn, $_POST['purchase_date']);

    // First, revert old stock
    $old_items = mysqli_query($conn, "SELECT * FROM purchase_items WHERE purchase_id = $purchase_id");
    while ($old_item = mysqli_fetch_assoc($old_items)) {
        mysqli_query($conn, "UPDATE products SET stock_quantity = stock_quantity - {$old_item['quantity']} WHERE id = {$old_item['product_id']}");
    }

    // Delete old items
    mysqli_query($conn, "DELETE FROM purchase_items WHERE purchase_id = $purchase_id");

    // Update purchase main info
    mysqli_query($conn, "UPDATE purchases SET supplier_id='$supplier_id', purchase_date='$purchase_date' WHERE id=$purchase_id");

    $total_amount = 0;

    // Insert new items
    if (isset($_POST['products'])) {
        $products = $_POST['products'];
        $quantities = $_POST['quantities'];
        $prices = $_POST['prices'];

        for ($i = 0; $i < count($products); $i++) {
            if (!empty($products[$i]) && !empty($quantities[$i]) && !empty($prices[$i])) {
                $product_id = mysqli_real_escape_string($conn, $products[$i]);
                $quantity = mysqli_real_escape_string($conn, $quantities[$i]);
                $price = mysqli_real_escape_string($conn, $prices[$i]);
                $amount = $quantity * $price;

                $item_query = "INSERT INTO purchase_items (purchase_id, product_id, quantity, price, amount) 
                              VALUES ('$purchase_id', '$product_id', '$quantity', '$price', '$amount')";
                mysqli_query($conn, $item_query);

                // Update product stock with new quantity
                mysqli_query($conn, "UPDATE products SET stock_quantity = stock_quantity + $quantity WHERE id = $product_id");

                $total_amount += $amount;
            }
        }
    }

    // Update total amount
    mysqli_query($conn, "UPDATE purchases SET total_amount = $total_amount WHERE id = $purchase_id");

    $_SESSION['message'] = "Purchase updated successfully!";
    header("Location: purchase.php");
    exit();
}

// Handle Delete Purchase
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);

    // Get purchase items to revert stock
    $items = mysqli_query($conn, "SELECT * FROM purchase_items WHERE purchase_id = $id");
    while ($item = mysqli_fetch_assoc($items)) {
        // Revert stock
        mysqli_query($conn, "UPDATE products SET stock_quantity = stock_quantity - {$item['quantity']} WHERE id = {$item['product_id']}");
    }

    // Delete purchase (cascade will delete items)
    $query = "DELETE FROM purchases WHERE id = $id";
    mysqli_query($conn, $query);

    $_SESSION['message'] = "Purchase deleted successfully!";
    header("Location: purchase.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = "WHERE s.name LIKE '%$search%' OR p.purchase_date LIKE '%$search%'";
}

// Get purchases
$query = "SELECT p.*, s.name as supplier_name 
          FROM purchases p 
          LEFT JOIN suppliers s ON p.supplier_id = s.id 
          $where 
          ORDER BY p.id DESC";
$purchases = mysqli_query($conn, $query);

// Get suppliers for dropdown
$suppliers = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY name");

// Get products for dropdown
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY name");

// Get purchase for view
$view_purchase = null;
$view_items = null;
if (isset($_GET['view'])) {
    $view_id = mysqli_real_escape_string($conn, $_GET['view']);
    $view_result = mysqli_query($conn, "SELECT p.*, s.name as supplier_name FROM purchases p LEFT JOIN suppliers s ON p.supplier_id = s.id WHERE p.id = $view_id");
    $view_purchase = mysqli_fetch_assoc($view_result);

    $items_query = "SELECT pi.*, pr.name as product_name 
                   FROM purchase_items pi 
                   LEFT JOIN products pr ON pi.product_id = pr.id 
                   WHERE pi.purchase_id = $view_id";
    $view_items = mysqli_query($conn, $items_query);
}

// Get purchase for edit
$edit_purchase = null;
$edit_items = null;
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM purchases WHERE id = $edit_id");
    $edit_purchase = mysqli_fetch_assoc($edit_result);

    $items_query = "SELECT pi.*, pr.name as product_name 
                   FROM purchase_items pi 
                   LEFT JOIN products pr ON pi.product_id = pr.id 
                   WHERE pi.purchase_id = $edit_id";
    $edit_items = mysqli_query($conn, $items_query);
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
        max-width: 800px;
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
        max-height: 60vh;
        overflow-y: auto;
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
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        font-size: 14px;
    }

    .form-group input:focus,
    .form-group select:focus {
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

    .btn-success {
        background: #4CAF50;
        color: white;
    }

    .btn-danger {
        background: #f44336;
        color: white;
    }

    .product-row {
        display: grid;
        grid-template-columns: 3fr 1fr 1fr auto;
        gap: 10px;
        margin-bottom: 10px;
        align-items: center;
        background: #f9f9f9;
        padding: 10px;
        border-radius: 5px;
    }

    .purchase-info {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .purchase-info p {
        margin: 10px 0;
        font-size: 16px;
    }

    .purchase-info strong {
        color: #555;
        min-width: 120px;
        display: inline-block;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .items-table th {
        background: #f0f0f0;
        padding: 10px;
        text-align: left;
    }

    .items-table td {
        padding: 10px;
        border-bottom: 1px solid #e0e0e0;
    }

    .items-table tr:last-child td {
        border-bottom: none;
    }

    .total-row {
        font-weight: bold;
        background: #f0f0f0;
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
        background: black;
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

    .add-row-btn {
        margin: 15px 0;
        background: #4CAF50;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
    }

    .remove-row {
        background: #f44336;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
    }
</style>

<h1>Purchase Management</h1>

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
            <input type="text" name="search" placeholder="Search by supplier or date..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?>
                <a href="purchase.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="add-btn">
        <button onclick="openModal('addModal')" class="btn btn-primary">Add New Purchase</button>
    </div>
</div>

<!-- Add Purchase Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Purchase</h2>
            <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" action="" id="purchaseForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Supplier:</label>
                    <select name="supplier_id" required>
                        <option value="">Select Supplier</option>
                        <?php
                        mysqli_data_seek($suppliers, 0);
                        while ($sup = mysqli_fetch_assoc($suppliers)):
                        ?>
                            <option value="<?php echo $sup['id']; ?>">
                                <?php echo htmlspecialchars($sup['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Purchase Date:</label>
                    <input type="date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <h3>Products</h3>
                <div id="product-rows">
                    <div class="product-row">
                        <select name="products[]" required>
                            <option value="">Select Product</option>
                            <?php
                            mysqli_data_seek($products, 0);
                            while ($prod = mysqli_fetch_assoc($products)):
                            ?>
                                <option value="<?php echo $prod['id']; ?>">
                                    <?php echo htmlspecialchars($prod['name']); ?> (Stock: <?php echo $prod['stock_quantity']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <input type="number" name="quantities[]" placeholder="Quantity" min="1" onkeydown="return event.key !== '-'" required>
                        <input type="number" name="prices[]" placeholder="Price" step="1" min="0" onkeydown="return event.key !== '-'" required>
                        <button type="button" onclick="removeRow(this)" class="remove-row">Remove</button>
                    </div>
                </div>

                <button type="button" onclick="addProductRow()" class="add-row-btn">+ Add More Product</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" name="add_purchase" class="btn btn-primary">Save Purchase</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Purchase Modal -->
<?php if ($edit_purchase): ?>
    <div id="editModal" class="modal" style="display:block;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Purchase #<?php echo $edit_purchase['id']; ?></h2>
                <a href="purchase.php" class="close-btn">&times;</a>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="purchase_id" value="<?php echo $edit_purchase['id']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Supplier:</label>
                        <select name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            <?php
                            mysqli_data_seek($suppliers, 0);
                            while ($sup = mysqli_fetch_assoc($suppliers)):
                                $selected = ($sup['id'] == $edit_purchase['supplier_id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $sup['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($sup['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Purchase Date:</label>
                        <input type="date" name="purchase_date" value="<?php echo $edit_purchase['purchase_date']; ?>" required>
                    </div>

                    <h3>Products</h3>
                    <div id="edit-product-rows">
                        <?php if ($edit_items && mysqli_num_rows($edit_items) > 0):
                            while ($item = mysqli_fetch_assoc($edit_items)):
                        ?>
                                <div class="product-row">
                                    <select name="products[]" required>
                                        <option value="">Select Product</option>
                                        <?php
                                        mysqli_data_seek($products, 0);
                                        while ($prod = mysqli_fetch_assoc($products)):
                                            $selected = ($prod['id'] == $item['product_id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $prod['id']; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($prod['name']); ?> (Stock: <?php echo $prod['stock_quantity']; ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <input type="number" name="quantities[]" value="<?php echo $item['quantity']; ?>" placeholder="Quantity" min="1" onkeydown="return event.key !== '-'" required>
                                    <input type="number" name="prices[]" value="<?php echo $item['price']; ?>" placeholder="Price" step="1" min="0" onkeydown="return event.key !== '-'" required>
                                    <button type="button" onclick="removeRow(this)" class="remove-row">Remove</button>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="product-row">
                                <select name="products[]" required>
                                    <option value="">Select Product</option>
                                    <?php
                                    mysqli_data_seek($products, 0);
                                    while ($prod = mysqli_fetch_assoc($products)):
                                    ?>
                                        <option value="<?php echo $prod['id']; ?>">
                                            <?php echo htmlspecialchars($prod['name']); ?> (Stock: <?php echo $prod['stock_quantity']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <input type="number" name="quantities[]" placeholder="Quantity" min="1" onkeydown="return event.key !== '-'" required>
                                <input type="number" name="prices[]" placeholder="Price" step="0.01" min="0" onkeydown="return event.key !== '-'" required>
                                <button type="button" onclick="removeRow(this)" class="remove-row">Remove</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" onclick="addEditProductRow()" class="add-row-btn">+ Add More Product</button>
                </div>
                <div class="modal-footer">
                    <a href="purchase.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="update_purchase" class="btn btn-primary">Update Purchase</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- View Purchase Modal -->
<?php if ($view_purchase): ?>
    <div id="viewModal" class="modal" style="display:block;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Purchase Details #<?php echo $view_purchase['id']; ?></h2>
                <a href="purchase.php" class="close-btn">&times;</a>
            </div>
            <div class="modal-body">
                <div class="purchase-info">
                    <p><strong>Supplier:</strong> <?php echo htmlspecialchars($view_purchase['supplier_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('d-m-Y', strtotime($view_purchase['purchase_date'])); ?></p>
                    <p><strong>Total Amount:</strong> Rs. <?php echo number_format($view_purchase['total_amount'], 2); ?></p>
                </div>

                <h3>Products Purchased</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_qty = 0;
                        $total_amount = 0;
                        if ($view_items && mysqli_num_rows($view_items) > 0):
                            while ($item = mysqli_fetch_assoc($view_items)):
                                $total_qty += $item['quantity'];
                                $total_amount += $item['amount'];
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                    <td>Rs. <?php echo number_format($item['amount'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <tr class="total-row">
                                <td colspan="2"><strong>Total</strong></td>
                                <td><strong><?php echo $total_qty; ?></strong></td>
                                <td><strong>Rs. <?php echo number_format($total_amount, 2); ?></strong></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <a href="purchase.php" class="btn btn-secondary">Close</a>
                <a href="?edit=<?php echo $view_purchase['id']; ?>" class="btn btn-primary">Edit Purchase</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<h2>Purchases List</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>S.N.</th>
            <th>Date</th>
            <th>Supplier</th>
            <th>Total Amount</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($purchases) > 0): ?>
            <?php $sn = 1; ?>
            <?php while ($row = mysqli_fetch_assoc($purchases)): ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($row['purchase_date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_name'] ?? 'N/A'); ?></td>
                    <td>Rs. <?php echo number_format($row['total_amount'], 2); ?></td>
                    <td class="actions">
                        <a href="?view=<?php echo $row['id']; ?>" class="btn-view">View</a>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>"
                            class="btn-delete"
                            onclick="return confirm('Are you sure? This will revert stock changes.')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px;">
                    No purchases found.
                    <?php if ($search): ?>
                        <br><a href="purchase.php" class="btn btn-secondary" style="margin-top: 10px;">Clear Search</a>
                    <?php else: ?>
                        <br><button onclick="openModal('addModal')" class="btn btn-primary" style="margin-top: 10px;">Add Your First Purchase</button>
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

    // Add product row for add modal
    function addProductRow() {
        const container = document.getElementById('product-rows');
        const newRow = document.createElement('div');
        newRow.className = 'product-row';
        newRow.innerHTML = `
        <select name="products[]" required>
            <option value="">Select Product</option>
            <?php
            mysqli_data_seek($products, 0);
            while ($prod = mysqli_fetch_assoc($products)):
            ?>
                <option value="<?php echo $prod['id']; ?>">
                    <?php echo htmlspecialchars($prod['name']); ?> (Stock: <?php echo $prod['stock_quantity']; ?>)
                </option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="quantities[]" placeholder="Quantity" min="1" onkeydown="return event.key !== '-'" required>
        <input type="number" name="prices[]" placeholder="Price" step="1" min="0" onkeydown="return event.key !== '-'" required>
        <button type="button" onclick="removeRow(this)" class="remove-row">Remove</button>
    `;
        container.appendChild(newRow);
    }

    // Add product row for edit modal
    function addEditProductRow() {
        const container = document.getElementById('edit-product-rows');
        const newRow = document.createElement('div');
        newRow.className = 'product-row';
        newRow.innerHTML = `
        <select name="products[]" required>
            <option value="">Select Product</option>
            <?php
            mysqli_data_seek($products, 0);
            while ($prod = mysqli_fetch_assoc($products)):
            ?>
                <option value="<?php echo $prod['id']; ?>">
                    <?php echo htmlspecialchars($prod['name']); ?> (Stock: <?php echo $prod['stock_quantity']; ?>)
                </option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="quantities[]" placeholder="Quantity" min="1" onkeydown="return event.key !== '-'" required>
        <input type="number" name="prices[]" placeholder="Price" step="1" min="0" onkeydown="return event.key !== '-'" required>
        <button type="button" onclick="removeRow(this)" class="remove-row">Remove</button>
    `;
        container.appendChild(newRow);
    }

    function removeRow(button) {
        const rows = document.querySelectorAll('.product-row');
        if (rows.length > 1) {
            button.parentElement.remove();
        } else {
            alert('At least one product row is required!');
        }
    }

    // Auto-hide alerts after 3 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            alert.style.display = 'none';
        });
    }, 3000);
</script>

<?php include '../includes/footer.php'; ?>