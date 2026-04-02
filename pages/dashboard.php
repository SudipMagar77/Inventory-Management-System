<?php
include '../includes/config.php';
include '../auth/auth_check.php';

// Get counts
$products_count = 0;
$categories_count = 0;
$customers_count = 0;
$suppliers_count = 0;

// Get products count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
if ($result) {
    $products_count = mysqli_fetch_assoc($result)['count'];
}

// Get categories count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories");
if ($result) {
    $categories_count = mysqli_fetch_assoc($result)['count'];
}

// Get customers count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM customers");
if ($result) {
    $customers_count = mysqli_fetch_assoc($result)['count'];
}

// Get suppliers count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM suppliers");
if ($result) {
    $suppliers_count = mysqli_fetch_assoc($result)['count'];
}

// Get stock data for chart
$in_stock = 0;
$low_stock = 0;
$out_of_stock = 0;

$stock_query = "SELECT 
    SUM(CASE WHEN stock_quantity > 10 THEN 1 ELSE 0 END) as in_stock,
    SUM(CASE WHEN stock_quantity <= 10 AND stock_quantity > 0 THEN 1 ELSE 0 END) as low_stock,
    SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM products";

$stock_result = mysqli_query($conn, $stock_query);
if ($stock_result && mysqli_num_rows($stock_result) > 0) {
    $stock_data = mysqli_fetch_assoc($stock_result);
    $in_stock = $stock_data['in_stock'] ?? 0;
    $low_stock = $stock_data['low_stock'] ?? 0;
    $out_of_stock = $stock_data['out_of_stock'] ?? 0;
}
?>
<?php include '../includes/header.php'; ?>

<style>
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: all 0.3s ease;
    }

    .card h3 {
        color: #666;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .card p {
        color: #333;
        font-size: 32px;
        font-weight: bold;
    }

    /* Clickable card links */
    .card-link {
        text-decoration: none;
        display: block;
        cursor: pointer;
    }

    .card-link:hover .card {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .chart-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        margin: 0 auto;
    }

    .chart-container h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }

    .stock-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }

    .legend-color.green {
        background: #4CAF50;
    }

    .legend-color.yellow {
        background: #FFC107;
    }

    .legend-color.red {
        background: #F44336;
    }

    .no-data {
        text-align: center;
        color: #666;
        padding: 20px;
        font-style: italic;
    }
</style>

<h1>Dashboard</h1>

<div class="dashboard-cards">
    <a href="product.php" class="card-link">
        <div class="card">
            <h3>Total Products</h3>
            <p><?php echo $products_count; ?></p>
        </div>
    </a>
    <a href="category.php" class="card-link">
        <div class="card">
            <h3>Total Categories</h3>
            <p><?php echo $categories_count; ?></p>
        </div>
    </a>
    <a href="customer.php" class="card-link">
        <div class="card">
            <h3>Total Customers</h3>
            <p><?php echo $customers_count; ?></p>
        </div>
    </a>
    <a href="supplier.php" class="card-link">
        <div class="card">
            <h3>Total Suppliers</h3>
            <p><?php echo $suppliers_count; ?></p>
        </div>
    </a>
</div>

<div class="chart-container">
    <h2>Stock Status</h2>

    <?php if ($products_count > 0): ?>
        <canvas id="stockChart" style="max-height: 300px;"></canvas>

        <div class="stock-legend">
            <div class="legend-item">
                <div class="legend-color green"></div>
                <span>In Stock: <?php echo $in_stock; ?></span>
            </div>
            <div class="legend-item">
                <div class="legend-color yellow"></div>
                <span>Low Stock: <?php echo $low_stock; ?></span>
            </div>
            <div class="legend-item">
                <div class="legend-color red"></div>
                <span>Out of Stock: <?php echo $out_of_stock; ?></span>
            </div>
        </div>
    <?php else: ?>
        <div class="no-data">
            <p>No products added yet. Add products to see stock status chart.</p>
            <p><a href="product.php" style="color: #667eea;">Add your first product →</a></p>
        </div>
    <?php endif; ?>
</div>

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<?php if ($products_count > 0): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('stockChart').getContext('2d');

            const inStock = <?php echo $in_stock; ?>;
            const lowStock = <?php echo $low_stock; ?>;
            const outOfStock = <?php echo $out_of_stock; ?>;

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                    datasets: [{
                        data: [inStock, lowStock, outOfStock],
                        backgroundColor: ['#4CAF50', '#FFC107', '#F44336'],
                        borderColor: ['#388E3C', '#FFA000', '#D32F2F'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = inStock + lowStock + outOfStock;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>