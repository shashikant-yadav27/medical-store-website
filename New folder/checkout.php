<?php
// checkout.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to proceed to checkout.');
    redirect('auth/login.php?redirect=checkout.php');
}

$user_id = $_SESSION['user_id'];

// Fetch Cart
$stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    redirect('cart.php');
}

$subtotal = 0;
$requires_prescription = false;
foreach ($cartItems as $item) {
    $price = $item['discount_price'] ?: $item['price'];
    $subtotal += ($price * $item['quantity']);
    if ($item['prescription_required']) $requires_prescription = true;
}

$shipping = ($subtotal > 50 || $subtotal == 0) ? 0 : 5.00;
$total = $subtotal + $shipping;

// Check if prescription is uploaded if required
$has_valid_prescription = false;
if ($requires_prescription) {
    $stmtPresc = $pdo->prepare("SELECT id FROM prescriptions WHERE user_id = ? AND status = 'Approved' ORDER BY id DESC LIMIT 1");
    $stmtPresc->execute([$user_id]);
    $valid_prescription = $stmtPresc->fetchColumn();
    if ($valid_prescription) {
        $has_valid_prescription = true;
    }
}

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('checkout.php');
    }
    
    if ($requires_prescription && !$has_valid_prescription) {
        setFlashMessage('error', 'You must have an approved prescription to order these items.');
        redirect('checkout.php');
    }
    
    $shipping_name = sanitizeInput($_POST['shipping_name']);
    $shipping_email = sanitizeInput($_POST['shipping_email']);
    $shipping_phone = sanitizeInput($_POST['shipping_phone']);
    $shipping_address = sanitizeInput($_POST['shipping_address']);
    $shipping_city = sanitizeInput($_POST['shipping_city']);
    $shipping_state = sanitizeInput($_POST['shipping_state']);
    $shipping_zip = sanitizeInput($_POST['shipping_zip']);
    $payment_method = sanitizeInput($_POST['payment_method']); // COD, Stripe, Razorpay
    
    $order_number = 'ORD-' . strtoupper(uniqid());
    
    try {
        $pdo->beginTransaction();
        
        // Insert Order
        $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, order_number, subtotal, shipping_charge, total_amount, payment_method, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtOrder->execute([
            $user_id, $order_number, $subtotal, $shipping, $total, $payment_method, $shipping_name, $shipping_email, $shipping_phone, $shipping_address, $shipping_city, $shipping_state, $shipping_zip
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insert Order Items and Update Stock
        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, prescription_id) VALUES (?, ?, ?, ?, ?)");
        $stmtStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        
        $prescription_id = $valid_prescription ?: null;
        
        foreach ($cartItems as $item) {
            $price = $item['discount_price'] ?: $item['price'];
            $stmtItem->execute([$order_id, $item['id'], $item['quantity'], $price, $item['prescription_required'] ? $prescription_id : null]);
            $stmtStock->execute([$item['quantity'], $item['id']]);
        }
        
        // Clear Cart
        $stmtClearCart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmtClearCart->execute([$user_id]);
        
        $pdo->commit();
        
        // In a real app, integrate Stripe/Razorpay here if selected
        // For COD, just redirect to success page
        
        $_SESSION['order_success'] = $order_number;
        redirect('checkout_success.php');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlashMessage('error', 'Something went wrong while placing your order. Please try again.');
    }
}

// Fetch User Info
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

include 'includes/header.php';
?>

<div class="bg-light py-3 border-bottom mb-4">
    <div class="container">
        <h4 class="mb-0 fw-bold">Checkout</h4>
    </div>
</div>

<div class="container py-4 mb-5">
    
    <?php if ($requires_prescription && !$has_valid_prescription): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Prescription Required!</strong> Your cart contains medicines that require an approved prescription. 
                Please <a href="prescription.php" class="alert-link">upload your prescription</a> and wait for admin approval before checking out.
            </div>
        </div>
    <?php endif; ?>

    <form action="checkout.php" method="POST" class="row">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Shipping Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="shipping_name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="shipping_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number *</label>
                            <input type="text" class="form-control" name="shipping_phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Complete Address *</label>
                            <textarea class="form-control" name="shipping_address" rows="3" required></textarea>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">City *</label>
                            <input type="text" class="form-control" name="shipping_city" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State *</label>
                            <input type="text" class="form-control" name="shipping_state" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Zip Code *</label>
                            <input type="text" class="form-control" name="shipping_zip" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Payment Method</h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-check mb-3 border p-3 rounded">
                        <input class="form-check-input mt-2" type="radio" name="payment_method" id="cod" value="COD" checked>
                        <label class="form-check-label d-block ms-2" for="cod">
                            <span class="fw-bold d-block">Cash on Delivery (COD)</span>
                            <span class="text-muted small">Pay with cash upon delivery.</span>
                        </label>
                    </div>
                    <!-- Placeholder for Gateways -->
                    <div class="form-check mb-3 border p-3 rounded opacity-50">
                        <input class="form-check-input mt-2" type="radio" name="payment_method" id="card" value="Stripe" disabled>
                        <label class="form-check-label d-block ms-2" for="card">
                            <span class="fw-bold d-block">Credit / Debit Card (Stripe)</span>
                            <span class="text-muted small">Temporarily unavailable.</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <?php foreach($cartItems as $item): 
                            $price = $item['discount_price'] ?: $item['price'];
                        ?>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <div class="me-2 text-truncate">
                                <span class="text-muted"><?php echo $item['quantity']; ?>x</span>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </div>
                            <span class="fw-semibold text-nowrap"><?php echo formatCurrency($price * $item['quantity']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold"><?php echo formatCurrency($subtotal); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping</span>
                        <span class="fw-semibold"><?php echo $shipping == 0 ? 'Free' : formatCurrency($shipping); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-5 text-primary"><?php echo formatCurrency($total); ?></span>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100 fw-semibold" <?php echo ($requires_prescription && !$has_valid_prescription) ? 'disabled' : ''; ?>>
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
