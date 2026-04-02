<?php
include '../includes/config.php';
include '../auth/auth_check.php';

// Handle Add Category with PROPER VALIDATION AND ERROR DISPLAY
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']); // Don't use mysqli_real_escape_string yet

    // VALIDATION 1: Check if empty after trimming
    if (empty($name)) {
        $_SESSION['error'] = " Category name cannot be empty!";
    }
    // VALIDATION 2: Check if it's just quotes
    elseif ($name == '""' || $name == "''") {
        $_SESSION['error'] = " Please enter a valid category name (quotes not allowed)!";
    }
    // VALIDATION 3: Check minimum length
    elseif (strlen($name) < 2) {
        $_SESSION['error'] = " Category name must be at least 2 characters!";
    }
    // VALIDATION 4: Check if it contains only numbers
    elseif (is_numeric($name)) {
        $_SESSION['error'] = " Category name cannot be only numbers!";
    }
    // VALIDATION 5: Check for special characters (optional)
    elseif (!preg_match("/^[a-zA-Z0-9\s\-]+$/", $name)) {
        $_SESSION['error'] = " Category name can only contain letters, numbers, spaces and hyphens!";
    } else {
        // Now escape for database
        $name = mysqli_real_escape_string($conn, $name);

        // Check if already exists
        $check_query = "SELECT id FROM categories WHERE name = '$name'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = " Category '$name' already exists!";
        } else {
            $query = "INSERT INTO categories (name) VALUES ('$name')";
            if (mysqli_query($conn, $query)) {
                $_SESSION['message'] = " Category added successfully!";
            } else {
                $_SESSION['error'] = " Database error: " . mysqli_error($conn);
            }
        }
    }
    header("Location: category.php");
    exit();
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $query = "DELETE FROM categories WHERE id = $id";
    mysqli_query($conn, $query);
    $_SESSION['message'] = "Category deleted successfully!";
    header("Location: category.php");
    exit();
}

// Handle Update Category with validation
if (isset($_POST['update_category'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = trim($_POST['name']);

    // VALIDATION
    if (empty($name)) {
        $_SESSION['error'] = " Category name cannot be empty!";
    } elseif ($name == '""' || $name == "''") {
        $_SESSION['error'] = " Please enter a valid category name (quotes not allowed)!";
    } elseif (strlen($name) < 2) {
        $_SESSION['error'] = " Category name must be at least 2 characters!";
    } elseif (is_numeric($name)) {
        $_SESSION['error'] = " Category name cannot be only numbers!";
    } else {
        $name = mysqli_real_escape_string($conn, $name);

        // Check if name already exists (excluding current category)
        $check_query = "SELECT id FROM categories WHERE name = '$name' AND id != $id";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = " Category '$name' already exists!";
        } else {
            $query = "UPDATE categories SET name = '$name' WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $_SESSION['message'] = " Category updated successfully!";
            } else {
                $_SESSION['error'] = " Error updating: " . mysqli_error($conn);
            }
        }
    }
    header("Location: category.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = "WHERE name LIKE '%$search%'";
}

// Get categories
$query = "SELECT * FROM categories $where ORDER BY id DESC";
$categories = mysqli_query($conn, $query);

// Get category for edit
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM categories WHERE id = $edit_id");
    $edit_category = mysqli_fetch_assoc($edit_result);
}
?>
<?php include '../includes/header.php'; ?>

<h1>Category Management</h1>

<?php if (isset($_SESSION['error'])): ?>
    <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffcdd2; font-weight: bold;">
        <?php
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['message'])): ?>
    <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c8e6c9; font-weight: bold;">
        <?php
        echo $_SESSION['message'];
        unset($_SESSION['message']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert success"><?php echo $_SESSION['message'];
                                unset($_SESSION['message']); ?></div>
<?php endif; ?>

<div class="page-header">
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search category..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="add-btn">
        <button onclick="openModal('addModal')">Add New Category</button>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Category</h2>
        </div>
        <form method="POST" action="" id="addCategoryForm" onsubmit="return validateCategoryForm()">
            <div class="modal-body">
                <div class="form-group">
                    <label>Category Name:</label>
                    <input type="text" name="name" id="categoryName" placeholder="Enter category name" required>
                    <small id="categoryError" class="error-message"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" name="add_category" id="saveCategoryBtn" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<?php if ($edit_category): ?>
    <div id="editModal" class="modal" style="display:block;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Category</h2>
            </div>
            <form method="POST" action="" id="editCategoryForm" onsubmit="return validateEditCategoryForm()">
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category Name:</label>
                        <input type="text" name="name" id="editCategoryName"
                            value="<?php echo htmlspecialchars($edit_category['name']); ?>"
                            placeholder="Enter category name" required>
                        <small id="editCategoryError" class="error-message"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="category.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="update_category" id="updateCategoryBtn" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<h2>Category List</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>S.N.</th>
            <th>Category Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($categories) > 0): ?>
            <?php $sn = 1; ?>
            <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td class="actions">
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>"
                            class="btn-delete"
                            onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No categories found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
<?php
include '../includes/config.php';
include '../auth/auth_check.php';

// ... your existing PHP code ...

// Display error messages
if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error" style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffcdd2;">
        <?php
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success" style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c8e6c9;">
        <?php
        echo $_SESSION['message'];
        unset($_SESSION['message']);
        ?>
    </div>
<?php endif; ?>