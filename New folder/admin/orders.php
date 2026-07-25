<?php
// admin/orders.php
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('admin/orders.php');
    }
    
    $id = (int)$_POST['id'];
    $status = sanitizeInput($_POST['order_status']);
    $tracking = sanitizeInput($_POST['tracking_number']);
    
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, tracking_number = ? WHERE id = ?");
    $stmt->execute([$status, $tracking, $id]);
    
    setFlashMessage('success', 'Order updated successfully.');
    redirect('admin/orders.php');
}

// Fetch Orders
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$whereClause = "";
$params = [];

if ($statusFilter) {
    $whereClause = "WHERE order_status = ?";
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare("SELECT id, order_number, user_id, total_amount, payment_method, order_status, created_at, shipping_name FROM orders $whereClause ORDER BY id DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Manage Orders</h3>
    
    <form action="orders.php" method="GET" class="d-flex align-items-center">
        <label class="me-2 fw-semibold">Filter:</label>
        <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="">All Orders</option>
            <option value="Pending" <?php echo ($statusFilter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="Processing" <?php echo ($statusFilter == 'Processing') ? 'selected' : ''; ?>>Processing</option>
            <option value="Shipped" <?php echo ($statusFilter == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
            <option value="Delivered" <?php echo ($statusFilter == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
            <option value="Cancelled" <?php echo ($statusFilter == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
        </select>
    </form>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr><td colspan="7" class="text-center py-4">No orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach($orders as $o): ?>
                        <tr>
                            <td class="ps-4 fw-semibold">#<?php echo $o['order_number']; ?></td>
                            <td><?php echo htmlspecialchars($o['shipping_name']); ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($o['created_at'])); ?></td>
                            <td class="fw-bold text-primary"><?php echo formatCurrency($o['total_amount']); ?></td>
                            <td><?php echo $o['payment_method']; ?></td>
                            <td>
                                <?php
                                    $bClass = 'bg-secondary';
                                    if($o['order_status'] == 'Delivered') $bClass = 'bg-success';
                                    elseif($o['order_status'] == 'Processing') $bClass = 'bg-primary';
                                    elseif($o['order_status'] == 'Cancelled') $bClass = 'bg-danger';
                                    elseif($o['order_status'] == 'Pending') $bClass = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?php echo $bClass; ?>"><?php echo $o['order_status']; ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="order_view.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i> View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
