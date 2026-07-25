<?php
// ajax/cart.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$action = isset($_POST['action']) ? sanitizeInput($_POST['action']) : '';
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Identify user or session
$user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
if (!isset($_SESSION['cart_session_id'])) {
    $_SESSION['cart_session_id'] = bin2hex(random_bytes(16));
}
$session_id = $_SESSION['cart_session_id'];

if ($action === 'add') {
    if (!$product_id) {
        echo json_encode(['status' => 'error', 'message' => 'Product ID missing']);
        exit;
    }
    
    // Check if product exists and stock
    $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product || $product['stock_quantity'] < $quantity) {
        echo json_encode(['status' => 'error', 'message' => 'Product not available or out of stock']);
        exit;
    }
    
    // Check if already in cart
    if ($user_id) {
        $checkStmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$user_id, $product_id]);
    } else {
        $checkStmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND user_id IS NULL");
        $checkStmt->execute([$session_id, $product_id]);
    }
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        if ($newQty > $product['stock_quantity']) $newQty = $product['stock_quantity'];
        
        $upStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $upStmt->execute([$newQty, $existing['id']]);
    } else {
        $insStmt = $pdo->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
        $insStmt->execute([$user_id, $session_id, $product_id, $quantity]);
    }
    
    // Get updated cart count
    if ($user_id) {
        $countStmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $countStmt->execute([$user_id]);
    } else {
        $countStmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id = ? AND user_id IS NULL");
        $countStmt->execute([$session_id]);
    }
    $cartCount = $countStmt->fetchColumn() ?: 0;
    
    echo json_encode(['status' => 'success', 'cart_count' => $cartCount]);
    exit;
}

if ($action === 'update') {
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    if ($cart_id && $quantity > 0) {
        // Verify cart belongs to user/session
        if ($user_id) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$quantity, $cart_id, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND session_id = ?");
            $stmt->execute([$quantity, $cart_id, $session_id]);
        }
        echo json_encode(['status' => 'success']);
    }
    exit;
}

if ($action === 'remove') {
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    if ($cart_id) {
        if ($user_id) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
            $stmt->execute([$cart_id, $session_id]);
        }
        echo json_encode(['status' => 'success']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
