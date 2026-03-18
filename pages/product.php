<?php
include '../includes/config.php';
include '../auth/auth_check.php';

// Handle Add Product
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $purchase_price = mysqli_real_escape_string($conn, $_POST['purchase_price']);
    $sale_price = mysqli_real_escape_string($conn, $_POST['sale_price']);

    if ($purchase_price < 1 || $sale_price < 1) {
        $_SESSION['error'] = "Price must be greater than or equal to 1.";
        header("Location: product.php");
        exit();
    }
    // Handle image upload
    $product_image = 'default-product.jpg';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "../assets/images/";
        // Create directory if not exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $product_image = time() . '_' . basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $product_image;

        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            // Image uploaded successfully
        }
    }

    $query = "INSERT INTO products (name, category_id, purchase_price, sale_price, product_image, stock_quantity) 
              VALUES ('$name', '$category_id', '$purchase_price', '$sale_price', '$product_image', 0)";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Product added successfully!";
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
    }
    header("Location: product.php");
    exit();
}

// Handle Update Product
if (isset($_POST['update_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $purchase_price = mysqli_real_escape_string($conn, $_POST['purchase_price']);
    $sale_price = mysqli_real_escape_string($conn, $_POST['sale_price']);

    if ($purchase_price < 1 || $sale_price < 1) {
        $_SESSION['error'] = "Price must be greater than or equal to 1.";
        header("Location: product.php");
        exit();
    }

    $query = "UPDATE products SET 
              name='$name', 
              category_id='$category_id', 
              purchase_price='$purchase_price', 
              sale_price='$sale_price' 
              WHERE id=$id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Product updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating: " . mysqli_error($conn);
    }
    header("Location: product.php");
    exit();
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $query = "DELETE FROM products WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting: " . mysqli_error($conn);
    }
    header("Location: product.php");
    exit();
}

// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = "WHERE p.name LIKE '%$search%'";
}

// Get products
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $where 
          ORDER BY p.id DESC";
$products = mysqli_query($conn, $query);

// Get product for view
$view_product = null;
if (isset($_GET['view'])) {
    $view_id = mysqli_real_escape_string($conn, $_GET['view']);
    $view_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.id = $view_id";
    $view_result = mysqli_query($conn, $view_query);
    if ($view_result && mysqli_num_rows($view_result) > 0) {
        $view_product = mysqli_fetch_assoc($view_result);
    }
}

// Get product for edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM products WHERE id = $edit_id");
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_product = mysqli_fetch_assoc($edit_result);
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
        max-width: 600px;
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
    .form-group select,
    .form-group textarea {
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

    .product-info {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .product-info p {
        margin: 10px 0;
        font-size: 16px;
    }

    .product-info strong {
        color: #555;
        min-width: 120px;
        display: inline-block;
    }

    .image-preview {
        text-align: center;
        margin: 20px 0;
    }

    .image-preview img {
        max-width: 200px;
        max-height: 200px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        padding: 5px;
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
</style>

<h1>Product Management</h1>

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
            <input type="text" name="search" placeholder="Search product..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
    <div class="add-btn">
        <button onclick="openModal('addModal')" class="btn btn-primary">Add New Product</button>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Product</h2>
            <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-group">
                    <label>Product Name:</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Category:</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php
                        mysqli_data_seek($categories, 0);
                        while ($cat = mysqli_fetch_assoc($categories)):
                        ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Purchase Price (Rs.):</label>
                    <input type="number" min="1" step="1" name="purchase_price" oninput="this.value = Math.abs(this.value)" onkeydown="return event.key !== '-'" required>
                </div>

                <div class="form-group">
                    <label>Sale Price (Rs.):</label>
                    <input type="number" min="1" step="1" name="sale_price" oninput="this.value = Math.abs(this.value)" onkeydown="return event.key !== '-'" required>
                </div>

                <div class="form-group">
                    <label>Product Image:</label>
                    <div class="image-preview">
                        <img src="../assets/images/default-product.jpg" alt="Preview" id="preview" style="max-width: 200px;">
                    </div>
                    <input type="file" name="product_image" accept="image/*" onchange="previewImage(this)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" name="add_product" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- View Product Modal -->
<?php if ($view_product): ?>
    <div id="viewModal" class="modal" style="display:block;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Product Details</h2>
                <a href="product.php" class="close-btn">&times;</a>
            </div>
            <div class="modal-body">
                <div class="image-preview">
                    <img src="../assets/images/<?php echo $view_product['product_image']; ?>" alt="<?php echo $view_product['name']; ?>" onerror="this.src='../assets/images/default-product.jpg'">
                </div>
                <div class="product-info">
                    <p><strong>Product Name:</strong> <?php echo htmlspecialchars($view_product['name']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($view_product['category_name'] ?? 'N/A'); ?></p>
                    <p><strong>Purchase Price:</strong> Rs. <?php echo number_format($view_product['purchase_price'], 2); ?></p>
                    <p><strong>Sale Price:</strong> Rs. <?php echo number_format($view_product['sale_price'], 2); ?></p>
                    <p><strong>Current Stock:</strong> <?php echo $view_product['stock_quantity']; ?> units</p>
                    <p><strong>Added on:</strong> <?php echo date('d-m-Y', strtotime($view_product['created_at'])); ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="product.php" class="btn btn-secondary">Close</a>
                <a href="?edit=<?php echo $view_product['id']; ?>" class="btn btn-primary">Edit Product</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Edit Product Modal -->
<?php if ($edit_product): ?>
    <div id="editModal" class="modal" style="display:block;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <a href="product.php" class="close-btn">&times;</a>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                <div class="modal-body">
                    <div class="image-preview">
                        <img src="../assets/images/<?php echo $edit_product['product_image']; ?>" alt="<?php echo $edit_product['name']; ?>" style="max-width: 200px;" onerror="this.src='../assets/images/default-product.jpg'">
                    </div>

                    <div class="form-group">
                        <label>Product Name:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Category:</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            mysqli_data_seek($categories, 0);
                            while ($cat = mysqli_fetch_assoc($categories)):
                            ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo ($cat['id'] == $edit_product['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Purchase Price (Rs.):</label>
                        <input type="number" min="1" step="1" name="purchase_price" oninput="this.value = Math.abs(this.value)" onkeydown="return event.key !== '-'" value="<?php echo $edit_product['purchase_price']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Sale Price (Rs.):</label>
                        <input type="number" min="1" step="1" name="sale_price" oninput="this.value = Math.abs(this.value)" onkeydown="return event.key !== '-'" value="<?php echo $edit_product['sale_price']; ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="product.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<h2>Product List</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>S.N.</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Purchase Price</th>
            <th>Sale Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($products) > 0): ?>
            <?php $sn = 1; ?>
            <?php while ($row = mysqli_fetch_assoc($products)): ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></td>
                    <td>Rs. <?php echo number_format($row['purchase_price'], 2); ?></td>
                    <td>Rs. <?php echo number_format($row['sale_price'], 2); ?></td>
                    <td>
                        <?php
                        if ($row['stock_quantity'] == 0) {
                            echo '<span style="color: #f44336; font-weight: bold;">Out of Stock</span>';
                        } elseif ($row['stock_quantity'] < 10) {
                            echo '<span style="color: #FFC107; font-weight: bold;">' . $row['stock_quantity'] . ' (Low)</span>';
                        } else {
                            echo '<span style="color: #4CAF50; font-weight: bold;">' . $row['stock_quantity'] . '</span>';
                        }
                        ?>
                    </td>
                    <td class="actions">
                        <a href="?view=<?php echo $row['id']; ?>" class="btn-view">View</a>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>"
                            class="btn-delete"
                            onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center;">No products found. Click "Add New Product" to create one.</td>
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

    // Image preview
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>

<?php include '../includes/footer.php'; ?>