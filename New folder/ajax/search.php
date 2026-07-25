<?php
// ajax/search.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (isset($_GET['q'])) {
    $q = sanitizeInput($_GET['q']);
    $search = "%$q%";
    
    $stmt = $pdo->prepare("SELECT id, name, slug, image, price, discount_price FROM products WHERE (name LIKE ? OR generic_name LIKE ?) AND status = 1 LIMIT 5");
    $stmt->execute([$search, $search]);
    $products = $stmt->fetchAll();
    
    if (count($products) > 0) {
        echo '<div class="list-group list-group-flush">';
        foreach($products as $p) {
            $img = $p['image'] ? SITE_URL.'assets/uploads/products/'.$p['image'] : 'https://via.placeholder.com/50';
            $price = $p['discount_price'] ? formatCurrency($p['discount_price']) : formatCurrency($p['price']);
            
            echo '
            <a href="'.SITE_URL.'product.php?slug='.$p['slug'].'" class="list-group-item list-group-item-action d-flex align-items-center">
                <img src="'.$img.'" class="rounded me-3" width="40" height="40" style="object-fit:cover;">
                <div class="flex-grow-1">
                    <h6 class="mb-0 text-dark fw-semibold">'.htmlspecialchars($p['name']).'</h6>
                    <small class="text-success fw-bold">'.$price.'</small>
                </div>
            </a>';
        }
        echo '<a href="'.SITE_URL.'shop.php?q='.urlencode($q).'" class="list-group-item list-group-item-action text-center text-primary fw-bold bg-light">View All Results</a>';
        echo '</div>';
    } else {
        echo '<div class="p-3 text-center text-muted">No products found.</div>';
    }
}
