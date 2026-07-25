<?php
// cart.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Fetch Cart Items
$cartItems = [];
$subtotal = 0;

if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $session_id = isset($_SESSION['cart_session_id']) ? $_SESSION['cart_session_id'] : '';
    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND c.user_id IS NULL");
    $stmt->execute([$session_id]);
}

$cartItems = $stmt->fetchAll();

foreach ($cartItems as $item) {
    $price = $item['discount_price'] ?: $item['price'];
    $subtotal += ($price * $item['quantity']);
}

// Fixed shipping for example
$shipping = ($subtotal > 50 || $subtotal == 0) ? 0 : 5.00;
$total = $subtotal + $shipping;

include 'includes/header.php';
?>

<div class="bg-light py-3 border-bottom mb-4">
    <div class="container">
        <h4 class="mb-0 fw-bold">Shopping Cart</h4>
    </div>
</div>

<div class="container py-4 mb-5">
    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x fs-1 text-muted mb-3 d-block"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Looks like you haven't added anything to your cart yet.</p>
            <a href="shop.php" class="btn btn-primary mt-3">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th class="pe-4"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): 
                                        $itemPrice = $item['discount_price'] ?: $item['price'];
                                        $itemTotal = $itemPrice * $item['quantity'];
                                    ?>
                                    <tr id="cart-item-<?php echo $item['cart_id']; ?>">
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <?php if($item['image']): ?>
                                                    <img src="<?php echo SITE_URL; ?>assets/uploads/products/<?php echo $item['image']; ?>" width="60" height="60" class="rounded object-fit-cover me-3">
                                                <?php else: ?>
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center text-muted" style="width:60px; height:60px;"><i class="bi bi-image"></i></div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><a href="product.php?slug=<?php echo $item['slug']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($item['name']); ?></a></h6>
                                                    <?php if($item['prescription_required']): ?>
                                                        <span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;">Rx Required</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold"><?php echo formatCurrency($itemPrice); ?></td>
                                        <td>
                                            <div class="input-group input-group-sm" style="width: 110px;">
                                                <button class="btn btn-outline-secondary update-qty" data-action="minus" data-id="<?php echo $item['cart_id']; ?>">-</button>
                                                <input type="text" class="form-control text-center qty-val" value="<?php echo $item['quantity']; ?>" readonly>
                                                <button class="btn btn-outline-secondary update-qty" data-action="plus" data-id="<?php echo $item['cart_id']; ?>" data-max="<?php echo $item['stock_quantity']; ?>">+</button>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-primary"><?php echo formatCurrency($itemTotal); ?></td>
                                        <td class="pe-4 text-end">
                                            <button class="btn btn-sm btn-light text-danger remove-item" data-id="<?php echo $item['cart_id']; ?>"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold"><?php echo formatCurrency($subtotal); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Shipping</span>
                            <span class="fw-semibold"><?php echo $shipping == 0 ? '<span class="text-success">Free</span>' : formatCurrency($shipping); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-5 text-primary"><?php echo formatCurrency($total); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-success btn-lg w-100 fw-semibold">Proceed to Checkout</a>
                        
                        <div class="mt-4 pt-3 border-top text-center text-muted small">
                            <i class="bi bi-shield-check text-success fs-5 mb-1 d-block"></i>
                            Secure checkout guaranteed. All your data is encrypted.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Update Quantity
    $('.update-qty').on('click', function() {
        let btn = $(this);
        let cartId = btn.data('id');
        let action = btn.data('action');
        let input = btn.siblings('.qty-val');
        let currentQty = parseInt(input.val());
        let maxQty = parseInt(btn.data('max')) || 999;
        
        let newQty = currentQty;
        if (action === 'plus' && currentQty < maxQty) newQty++;
        if (action === 'minus' && currentQty > 1) newQty--;
        
        if (newQty !== currentQty) {
            $.ajax({
                url: 'ajax/cart.php',
                method: 'POST',
                data: { action: 'update', cart_id: cartId, quantity: newQty },
                success: function() {
                    location.reload(); // Simple reload to update totals
                }
            });
        }
    });
    
    // Remove Item
    $('.remove-item').on('click', function() {
        let cartId = $(this).data('id');
        if (confirm('Remove item from cart?')) {
            $.ajax({
                url: 'ajax/cart.php',
                method: 'POST',
                data: { action: 'remove', cart_id: cartId },
                success: function() {
                    location.reload();
                }
            });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
