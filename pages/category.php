<?php
include '../includes/config.php';
include '../auth/auth_check.php';

// Handle Add Category
if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);

    if (!empty($name)) {
        $query = "INSERT INTO categories (name) VALUES ('$name')";
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = "Category added successfully!";
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

// Handle Update Category
if (isset($_POST['update_category'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);

    $query = "UPDATE categories SET name = '$name' WHERE id = $id";
    mysqli_query($conn, $query);
    $_SESSION['message'] = "Category updated successfully!";
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
        <h2>Add New Category</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label>Category Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="modal-actions">
                <button type="submit" name="add_category">Save</button>
                <button type="button" onclick="closeModal('addModal')">Close</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<?php if ($edit_category): ?>
    <div id="editModal" class="modal" style="display:block;">
        <div class="modal-content">
            <h2>Edit Category</h2>
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <div class="form-group">
                    <label>Category Name:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($edit_category['name']); ?>" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" name="update_category">Update</button>
                    <a href="category.php" class="close-btn">Cancel</a>
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