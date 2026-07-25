<?php
// checkout_success.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['order_success'])) {
    redirect('index.php');
}

$order_number = $_SESSION['order_success'];
unset($_SESSION['order_success']);

include 'includes/header.php';
?>

<div class="container py-5 mt-4 text-center mb-5">
    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
    <h2 class="fw-bold mt-4">Order Placed Successfully!</h2>
    <p class="lead text-muted">Thank you for your purchase. Your order number is <span class="fw-bold text-dark"><?php echo htmlspecialchars($order_number); ?></span></p>
    <p class="mb-4 text-muted">We have received your order and are currently processing it. You will receive an email confirmation shortly.</p>
    
    <div>
        <a href="profile.php?tab=orders" class="btn btn-outline-primary me-2">View Order Details</a>
        <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
