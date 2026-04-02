<?php
include '../includes/config.php';
include '../auth/auth_check.php';

$report_type = isset($_GET['type']) ? $_GET['type'] : 'product_list';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$date_error = '';

// Validate dates
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    if (strtotime($end_date) < strtotime($start_date)) {
        $date_error = "End date cannot be earlier than start date!";
        // Reset to default dates
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
    }
}
?>
<?php include '../includes/header.php'; ?>

<style>
    .report-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .tab {
        padding: 10px 20px;
        background: #f0f0f0;
        border-radius: 5px;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
    }

    .tab:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
    }

    .tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .date-filter {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .date-filter h3 {
        margin-bottom: 20px;
        color: #333;
    }

    .date-inputs {
        display: flex;
        gap: 20px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .date-group {
        flex: 1;
        min-width: 200px;
    }

    .date-group label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: bold;
    }

    .date-group input {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        font-size: 14px;
    }

    .date-group input:focus {
        outline: none;
        border-color: #667eea;
    }

    .date-group input.error {
        border-color: #f44336;
    }

    .date-error {
        color: #f44336;
        font-size: 13px;
        margin-top: 5px;
        display: block;
    }

    .btn-generate {
        padding: 12px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        min-width: 150px;
    }

    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-generate:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .report-content {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .report-content h2 {
        color: #333;
        margin-bottom: 20px;
    }

    .report-content h3 {
        color: #555;
        margin: 20px 0;
    }

    /* Search Container Styles */
    .search-container {
        margin-bottom: 20px;
        display: flex;
        justify-content: flex-start;
    }

    .search-form {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .search-form input {
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        width: 300px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .search-form input:focus {
        outline: none;
        border-color: #667eea;
    }

    .btn-search {
        padding: 10px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: all 0.3s;
    }

    .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-clear {
        padding: 10px 20px;
        background: #e0e0e0;
        color: #333;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        font-weight: bold;
        transition: all 0.3s;
    }

    .btn-clear:hover {
        background: #d0d0d0;
        transform: translateY(-2px);
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .data-table th {
        background: #f5f5f5;
        padding: 12px;
        text-align: left;
        font-weight: bold;
        color: #333;
        border-bottom: 2px solid #e0e0e0;
    }

    .data-table td {
        padding: 12px;
        border-bottom: 1px solid #e0e0e0;
    }

    .data-table tbody tr:hover {
        background: #f9f9f9;
    }

    .total-row {
        background: #f0f0f0;
        font-weight: bold;
    }

    .profit-loss-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }

    .profit-loss-cards .card {
        background: #f9f9f9;
        padding: 25px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .profit-loss-cards .card h3 {
        margin: 0 0 15px 0;
        color: #555;
    }

    .revenue {
        color: #2196F3;
        font-size: 28px;
        font-weight: bold;
    }

    .cost {
        color: #f44336;
        font-size: 28px;
        font-weight: bold;
    }

    .profit {
        color: #4CAF50;
        font-size: 28px;
        font-weight: bold;
    }

    .loss {
        color: #f44336;
        font-size: 28px;
        font-weight: bold;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }

    .status-in {
        background: #4CAF50;
        color: white;
    }

    .status-low {
        background: #FFC107;
        color: black;
    }

    .status-out {
        background: #f44336;
        color: white;
    }

    .no-data {
        text-align: center;
        padding: 50px;
        color: #666;
        font-style: italic;
    }
</style>

<h1>Reports</h1>

<div class="report-tabs">
    <a href="?type=product_list" class="tab <?php echo $report_type == 'product_list' ? 'active' : ''; ?>">Product List</a>
    <a href="?type=supplier_list" class="tab <?php echo $report_type == 'supplier_list' ? 'active' : ''; ?>">Supplier List</a>
    <a href="?type=customer_list" class="tab <?php echo $report_type == 'customer_list' ? 'active' : ''; ?>">Customer List</a>
    <a href="?type=purchase_report" class="tab <?php echo $report_type == 'purchase_report' ? 'active' : ''; ?>">Purchase Report</a>
    <a href="?type=sales_report" class="tab <?php echo $report_type == 'sales_report' ? 'active' : ''; ?>">Sales Report</a>
    <a href="?type=profit_loss" class="tab <?php echo $report_type == 'profit_loss' ? 'active' : ''; ?>">Profit/Loss</a>
    <a href="?type=stock_balance" class="tab <?php echo $report_type == 'stock_balance' ? 'active' : ''; ?>">Stock Balance</a>
</div>

<div class="report-content">
    <?php if ($report_type == 'product_list'): ?>
        <h2>Product List</h2>

        <!-- Search Form -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="hidden" name="type" value="product_list">
                <input type="text" name="search" placeholder="Search by product name..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn-search">Search</button>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <a href="?type=product_list" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        $where = '';
        if (!empty($search)) {
            $where = "WHERE p.name LIKE '%$search%'";
        }

        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  $where
                  ORDER BY p.name";
        $result = mysqli_query($conn, $query);
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Purchase Price</th>
                    <th>Sales Price</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></td>
                            <td>Rs. <?php echo number_format($row['purchase_price'], 2); ?></td>
                            <td>Rs. <?php echo number_format($row['sale_price'], 2); ?></td>
                            <td>
                                <?php
                                if ($row['stock_quantity'] == 0) {
                                    echo '<span class="status-badge status-out">Out of Stock</span>';
                                } elseif ($row['stock_quantity'] < 10) {
                                    echo '<span class="status-badge status-low">' . $row['stock_quantity'] . ' (Low)</span>';
                                } else {
                                    echo '<span class="status-badge status-in">' . $row['stock_quantity'] . '</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">No products found<?php echo !empty($search) ? ' matching "' . htmlspecialchars($search) . '"' : ''; ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($report_type == 'supplier_list'): ?>
        <h2>Supplier List</h2>

        <!-- Search Form -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="hidden" name="type" value="supplier_list">
                <input type="text" name="search" placeholder="Search by name, phone or email..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn-search">Search</button>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <a href="?type=supplier_list" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        $where = '';
        if (!empty($search)) {
            $where = "WHERE name LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%'";
        }

        $result = mysqli_query($conn, "SELECT * FROM suppliers $where ORDER BY name");
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">No suppliers found<?php echo !empty($search) ? ' matching "' . htmlspecialchars($search) . '"' : ''; ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($report_type == 'customer_list'): ?>
        <h2>Customer List</h2>

        <!-- Search Form -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="hidden" name="type" value="customer_list">
                <input type="text" name="search" placeholder="Search by name, phone or email..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn-search">Search</button>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <a href="?type=customer_list" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        $where = '';
        if (!empty($search)) {
            $where = "WHERE name LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%'";
        }

        $result = mysqli_query($conn, "SELECT * FROM customers $where ORDER BY name");
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">No customers found<?php echo !empty($search) ? ' matching "' . htmlspecialchars($search) . '"' : ''; ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($report_type == 'purchase_report'): ?>
        <h2>Purchase Report</h2>

        <div class="date-filter">
            <h3>Select Date Range</h3>
            <form method="GET" action="" id="purchaseReportForm">
                <input type="hidden" name="type" value="purchase_report">
                <div class="date-inputs">
                    <div class="date-group">
                        <label>Start Date:</label>
                        <input type="date" name="start_date" id="start_date"
                            value="<?php echo $start_date; ?>" required
                            onchange="validateDates()">
                    </div>
                    <div class="date-group">
                        <label>End Date:</label>
                        <input type="date" name="end_date" id="end_date"
                            value="<?php echo $end_date; ?>" required
                            onchange="validateDates()">
                    </div>
                    <button type="submit" class="btn-generate" id="generateBtn">Generate Report</button>
                </div>
                <div id="dateError" class="date-error"></div>
                <?php if ($date_error): ?>
                    <div class="date-error"><?php echo $date_error; ?></div>
                <?php endif; ?>
            </form>
        </div>

        <?php
        if (!$date_error) {
            $query = "SELECT pi.*, p.name as product_name, s.name as supplier_name, 
                             pr.purchase_date
                      FROM purchase_items pi
                      JOIN purchases pr ON pi.purchase_id = pr.id
                      JOIN products p ON pi.product_id = p.id
                      LEFT JOIN suppliers s ON pr.supplier_id = s.id
                      WHERE pr.purchase_date BETWEEN '$start_date' AND '$end_date'
                      ORDER BY pr.purchase_date DESC";
            $result = mysqli_query($conn, $query);

            $total_qty = 0;
            $total_amount = 0;
        ?>

            <h3>Purchase Report From <?php echo date('d-m-Y', strtotime($start_date)); ?> to <?php echo date('d-m-Y', strtotime($end_date)); ?></h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product Name</th>
                        <th>Supplier</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)):
                            $total_qty += $row['quantity'];
                            $total_amount += $row['amount'];
                        ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($row['purchase_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['supplier_name'] ?? 'N/A'); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                                <td>Rs. <?php echo number_format($row['amount'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong><?php echo $total_qty; ?></strong></td>
                            <td></td>
                            <td><strong>Rs. <?php echo number_format($total_amount, 2); ?></strong></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-data">No purchases found for this period</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php } ?>

    <?php elseif ($report_type == 'sales_report'): ?>
        <h2>Sales Report</h2>

        <div class="date-filter">
            <h3>Select Date Range</h3>
            <form method="GET" action="" id="salesReportForm">
                <input type="hidden" name="type" value="sales_report">
                <div class="date-inputs">
                    <div class="date-group">
                        <label>Start Date:</label>
                        <input type="date" name="start_date" id="start_date"
                            value="<?php echo $start_date; ?>" required
                            onchange="validateDates()">
                    </div>
                    <div class="date-group">
                        <label>End Date:</label>
                        <input type="date" name="end_date" id="end_date"
                            value="<?php echo $end_date; ?>" required
                            onchange="validateDates()">
                    </div>
                    <button type="submit" class="btn-generate" id="generateBtn">Generate Report</button>
                </div>
                <div id="dateError" class="date-error"></div>
                <?php if ($date_error): ?>
                    <div class="date-error"><?php echo $date_error; ?></div>
                <?php endif; ?>
            </form>
        </div>

        <?php
        if (!$date_error) {
            $query = "SELECT si.*, p.name as product_name, c.name as customer_name, 
                             s.sale_date
                      FROM sale_items si
                      JOIN sales s ON si.sale_id = s.id
                      JOIN products p ON si.product_id = p.id
                      LEFT JOIN customers c ON s.customer_id = c.id
                      WHERE s.sale_date BETWEEN '$start_date' AND '$end_date'
                      ORDER BY s.sale_date DESC";
            $result = mysqli_query($conn, $query);

            $total_qty = 0;
            $total_amount = 0;
        ?>

            <h3>Sales Report From <?php echo date('d-m-Y', strtotime($start_date)); ?> to <?php echo date('d-m-Y', strtotime($end_date)); ?></h3>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product Name</th>
                        <th>Customer</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)):
                            $total_qty += $row['quantity'];
                            $total_amount += $row['amount'];
                        ?>
                            <tr>
                                <td><?php echo date('d-m-Y', strtotime($row['sale_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Walk-in'); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                                <td>Rs. <?php echo number_format($row['amount'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong><?php echo $total_qty; ?></strong></td>
                            <td></td>
                            <td><strong>Rs. <?php echo number_format($total_amount, 2); ?></strong></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-data">No sales found for this period</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php } ?>

    <?php elseif ($report_type == 'profit_loss'): ?>
        <h2>Profit/Loss Report</h2>

        <div class="date-filter">
            <h3>Select Date Range</h3>
            <form method="GET" action="" id="profitLossForm">
                <input type="hidden" name="type" value="profit_loss">
                <div class="date-inputs">
                    <div class="date-group">
                        <label>Start Date:</label>
                        <input type="date" name="start_date" id="start_date"
                            value="<?php echo $start_date; ?>" required
                            onchange="validateDates()">
                    </div>
                    <div class="date-group">
                        <label>End Date:</label>
                        <input type="date" name="end_date" id="end_date"
                            value="<?php echo $end_date; ?>" required
                            onchange="validateDates()">
                    </div>
                    <button type="submit" class="btn-generate" id="generateBtn">Generate Report</button>
                </div>
                <div id="dateError" class="date-error"></div>
                <?php if ($date_error): ?>
                    <div class="date-error"><?php echo $date_error; ?></div>
                <?php endif; ?>
            </form>
        </div>

        <?php
        if (!$date_error) {
            // Calculate total revenue (sales)
            $revenue_query = "SELECT SUM(total_amount) as total FROM sales 
                              WHERE sale_date BETWEEN '$start_date' AND '$end_date'";
            $revenue_result = mysqli_fetch_assoc(mysqli_query($conn, $revenue_query));
            $revenue = $revenue_result['total'] ?? 0;

            // Calculate total cost (purchases)
            $cost_query = "SELECT SUM(total_amount) as total FROM purchases 
                           WHERE purchase_date BETWEEN '$start_date' AND '$end_date'";
            $cost_result = mysqli_fetch_assoc(mysqli_query($conn, $cost_query));
            $cost = $cost_result['total'] ?? 0;

            $profit_loss = $revenue - $cost;
        ?>

            <h3>Profit/Loss Report From <?php echo date('d-m-Y', strtotime($start_date)); ?> to <?php echo date('d-m-Y', strtotime($end_date)); ?></h3>

            <div class="profit-loss-cards">
                <div class="card">
                    <h3>Total Revenue</h3>
                    <p class="revenue">Rs. <?php echo number_format($revenue, 2); ?></p>
                </div>
                <div class="card">
                    <h3>Total Cost</h3>
                    <p class="cost">Rs. <?php echo number_format($cost, 2); ?></p>
                </div>
                <div class="card">
                    <h3>Net <?php echo $profit_loss >= 0 ? 'Profit' : 'Loss'; ?></h3>
                    <p class="<?php echo $profit_loss >= 0 ? 'profit' : 'loss'; ?>">
                        Rs. <?php echo number_format(abs($profit_loss), 2); ?>
                    </p>
                </div>
            </div>
        <?php } ?>

    <?php elseif ($report_type == 'stock_balance'): ?>
        <h2>Stock Balance</h2>

        <!-- Search Form -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="hidden" name="type" value="stock_balance">
                <input type="text" name="search" placeholder="Search by product name..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn-search">Search</button>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <a href="?type=stock_balance" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        $where = '';
        if (!empty($search)) {
            $where = "WHERE p.name LIKE '%$search%'";
        }

        $query = "SELECT p.name, p.stock_quantity, 
                         p.purchase_price, p.sale_price,
                         (p.stock_quantity * p.purchase_price) as stock_value
                  FROM products p
                  $where
                  ORDER BY p.name";
        $result = mysqli_query($conn, $query);

        $total_value = 0;
        $total_products = 0;
        ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Stock Quantity</th>
                    <th>Purchase Price</th>
                    <th>Stock Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        $total_value += $row['stock_value'];
                        $total_products++;

                        if ($row['stock_quantity'] == 0) {
                            $status = '<span class="status-badge status-out">Out of Stock</span>';
                        } elseif ($row['stock_quantity'] < 10) {
                            $status = '<span class="status-badge status-low">Low Stock</span>';
                        } else {
                            $status = '<span class="status-badge status-in">In Stock</span>';
                        }
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo $row['stock_quantity']; ?></td>
                            <td>Rs. <?php echo number_format($row['purchase_price'], 2); ?></td>
                            <td>Rs. <?php echo number_format($row['stock_value'], 2); ?></td>
                            <td><?php echo $status; ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <tr class="total-row">
                        <td colspan="3"><strong>Total Stock Value (<?php echo $total_products; ?> products)</strong></td>
                        <td><strong>Rs. <?php echo number_format($total_value, 2); ?></strong></td>
                        <td></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">No products found<?php echo !empty($search) ? ' matching "' . htmlspecialchars($search) . '"' : ''; ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
    function validateDates() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const errorElement = document.getElementById('dateError');
        const generateBtn = document.getElementById('generateBtn');

        if (startDate && endDate) {
            if (endDate < startDate) {
                errorElement.textContent = '❌ End date cannot be earlier than start date!';
                if (generateBtn) {
                    generateBtn.disabled = true;
                    generateBtn.style.opacity = '0.5';
                    generateBtn.style.cursor = 'not-allowed';
                }
                return false;
            } else {
                errorElement.textContent = '';
                if (generateBtn) {
                    generateBtn.disabled = false;
                    generateBtn.style.opacity = '1';
                    generateBtn.style.cursor = 'pointer';
                }
                return true;
            }
        }
        return true;
    }

    // Auto-hide alerts after 3 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            if (alert) alert.style.display = 'none';
        });
    }, 3000);

    // Run validation on page load
    document.addEventListener('DOMContentLoaded', function() {
        validateDates();
    });
</script>

<?php include '../includes/footer.php'; ?>