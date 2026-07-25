<?php
// admin/index.php
include 'includes/header.php';
include 'includes/sidebar.php';

// Fetch Dashboard Stats
$stats = [
    'sales' => $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Completed'")->fetchColumn() ?: 0,
    'orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'customers' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'low_stock' => $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 10")->fetchColumn()
];

// Fetch Recent Orders
$recentOrders = $pdo->query("SELECT id, order_number, total_amount, order_status, created_at FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<div class="row mb-4 mt-2">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Sales</h6>
                        <h3 class="mb-0"><?php echo formatCurrency($stats['sales']); ?></h3>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Orders</h6>
                        <h3 class="mb-0"><?php echo $stats['orders']; ?></h3>
                    </div>
                    <i class="bi bi-cart-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Customers</h6>
                        <h3 class="mb-0"><?php echo $stats['customers']; ?></h3>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Low Stock Items</h6>
                        <h3 class="mb-0"><?php echo $stats['low_stock']; ?></h3>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Order #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recentOrders)): ?>
                                <tr><td colspan="5" class="text-center py-3">No orders found.</td></tr>
                            <?php else: ?>
                                <?php foreach($recentOrders as $order): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold">#<?php echo $order['order_number']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <?php
                                            $badgeClass = 'bg-secondary';
                                            if($order['order_status'] == 'Delivered') $badgeClass = 'bg-success';
                                            elseif($order['order_status'] == 'Processing') $badgeClass = 'bg-primary';
                                            elseif($order['order_status'] == 'Cancelled') $badgeClass = 'bg-danger';
                                            elseif($order['order_status'] == 'Pending') $badgeClass = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $order['order_status']; ?></span>
                                    </td>
                                    <td>
                                        <a href="order_view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <!-- We can add a pie chart here or just some quick links -->
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="product_add.php" class="btn btn-outline-primary text-start"><i class="bi bi-plus-circle me-2"></i> Add New Product</a>
                    <a href="prescriptions.php" class="btn btn-outline-success text-start"><i class="bi bi-file-medical me-2"></i> View Prescriptions</a>
                    <a href="categories.php" class="btn btn-outline-info text-start"><i class="bi bi-tags me-2"></i> Manage Categories</a>
                    <a href="advertisements.php" class="btn btn-outline-warning text-start"><i class="bi bi-megaphone me-2"></i> Manage Ads</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
