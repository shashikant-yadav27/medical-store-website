<?php
// admin/products.php
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('admin/products.php');
    }
    
    $id = (int)$_POST['id'];
    
    // Get image to delete
    $stmtImg = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmtImg->execute([$id]);
    $img = $stmtImg->fetchColumn();
    
    if ($img && file_exists('../assets/uploads/products/' . $img)) {
        unlink('../assets/uploads/products/' . $img);
    }
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    setFlashMessage('success', 'Product deleted successfully.');
    redirect('admin/products.php');
}

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search setup
$search = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$whereClause = "";
$params = [];

if ($search) {
    $whereClause = "WHERE p.name LIKE ? OR p.sku LIKE ?";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam];
}

// Fetch total records for pagination
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM products p $whereClause");
$stmtTotal->execute($params);
$totalRecords = $stmtTotal->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Fetch products
$sql = "SELECT p.id, p.name, p.price, p.stock_quantity, p.status, p.image, c.name as category_name 
        FROM products p 
        JOIN product_categories c ON p.category_id = c.id 
        $whereClause 
        ORDER BY p.id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Manage Products</h3>
    <a href="product_add.php" class="btn btn-primary"><i class="bi bi-plus"></i> Add Product</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="products.php" method="GET" class="d-flex w-50">
            <input type="text" name="q" class="form-control me-2" placeholder="Search by name or SKU..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-outline-primary">Search</button>
            <?php if($search): ?>
                <a href="products.php" class="btn btn-outline-secondary ms-2">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr><td colspan="7" class="text-center py-4">No products found.</td></tr>
                    <?php else: ?>
                        <?php foreach($products as $p): ?>
                        <tr>
                            <td class="ps-4">
                                <?php if($p['image']): ?>
                                    <img src="../assets/uploads/products/<?php echo $p['image']; ?>" class="rounded" width="50" height="50" style="object-fit:cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width:50px; height:50px;"><i class="bi bi-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                            <td><?php echo formatCurrency($p['price']); ?></td>
                            <td>
                                <?php if($p['stock_quantity'] > 10): ?>
                                    <span class="text-success"><?php echo $p['stock_quantity']; ?></span>
                                <?php else: ?>
                                    <span class="text-danger fw-bold"><?php echo $p['stock_quantity']; ?> (Low)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($p['status']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="product_edit.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></a>
                                <form action="products.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this product permanently?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if($totalPages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $search ? '&q='.$search : ''; ?>">Previous</a>
        </li>
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&q='.$search : ''; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $search ? '&q='.$search : ''; ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
