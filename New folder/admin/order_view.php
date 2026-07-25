<?php
// admin/order_view.php
include 'includes/header.php';
include 'includes/sidebar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('admin/orders.php');

// Fetch Order
$stmt = $pdo->prepare("SELECT o.*, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) redirect('admin/orders.php');

// Fetch Order Items
$stmtItems = $pdo->prepare("SELECT oi.*, p.name, p.sku, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Order Details <span class="text-primary">#<?php echo $order['order_number']; ?></span></h3>
    <a href="orders.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Orders</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Items Purchased</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th class="pe-4 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <?php if($item['image']): ?>
                                            <img src="../assets/uploads/products/<?php echo $item['image']; ?>" width="50" class="rounded me-3">
                                        <?php else: ?>
                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center text-muted" style="width:50px; height:50px;"><i class="bi bi-image"></i></div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                    <?php if($item['prescription_id']): ?>
                                        <div class="mt-1"><a href="prescriptions.php" class="badge bg-info text-decoration-none">View Linked Rx #<?php echo $item['prescription_id']; ?></a></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['sku'] ?: 'N/A'); ?></td>
                                <td><?php echo formatCurrency($item['price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="pe-4 text-end fw-bold"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-end text-muted">Subtotal:</td>
                                <td class="pe-4 text-end fw-semibold"><?php echo formatCurrency($order['subtotal']); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end text-muted">Shipping:</td>
                                <td class="pe-4 text-end fw-semibold"><?php echo formatCurrency($order['shipping_charge']); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold fs-5">Total:</td>
                                <td class="pe-4 text-end fw-bold fs-5 text-primary"><?php echo formatCurrency($order['total_amount']); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Order Status Update -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Update Order Status</h5>
                <form action="orders.php" method="POST" class="row align-items-end">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                    
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="order_status" class="form-select">
                            <option value="Pending" <?php echo ($order['order_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Processing" <?php echo ($order['order_status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                            <option value="Shipped" <?php echo ($order['order_status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                            <option value="Delivered" <?php echo ($order['order_status'] == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="Cancelled" <?php echo ($order['order_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="Returned" <?php echo ($order['order_status'] == 'Returned') ? 'selected' : ''; ?>>Returned</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <label class="form-label fw-semibold">Tracking Number (Optional)</label>
                        <input type="text" name="tracking_number" class="form-control" value="<?php echo htmlspecialchars($order['tracking_number']); ?>">
                    </div>
                    <div class="col-md-3 mt-3 mt-md-0">
                        <button type="submit" class="btn btn-primary w-100">Update Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Customer & Shipping Info -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Customer Information</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-light text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 45px; height: 45px; font-weight: bold;">
                        <?php echo strtoupper(substr($order['shipping_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($order['shipping_name']); ?></h6>
                        <small class="text-muted">Customer ID: #<?php echo $order['user_id']; ?></small>
                    </div>
                </div>
                <hr>
                <h6 class="fw-bold mt-3 mb-2">Contact Details</h6>
                <p class="mb-1"><i class="bi bi-envelope text-muted me-2"></i> <?php echo htmlspecialchars($order['shipping_email']); ?></p>
                <p class="mb-3"><i class="bi bi-telephone text-muted me-2"></i> <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                
                <h6 class="fw-bold mt-4 mb-2">Shipping Address</h6>
                <p class="mb-1"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                <p class="mb-0"><?php echo htmlspecialchars($order['shipping_city']) . ', ' . htmlspecialchars($order['shipping_state']) . ' ' . htmlspecialchars($order['shipping_zip']); ?></p>
            </div>
        </div>
        
        <!-- Payment Info -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Payment Information</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Method:</span>
                    <span class="fw-semibold"><?php echo $order['payment_method']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment Status:</span>
                    <?php
                        $pClass = 'text-warning';
                        if($order['payment_status'] == 'Completed') $pClass = 'text-success';
                        elseif($order['payment_status'] == 'Failed') $pClass = 'text-danger';
                    ?>
                    <span class="fw-bold <?php echo $pClass; ?>"><?php echo $order['payment_status']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-muted">Order Date:</span>
                    <span class="fw-semibold text-end"><?php echo date('M d, Y', strtotime($order['created_at'])); ?><br><small><?php echo date('h:i A', strtotime($order['created_at'])); ?></small></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
