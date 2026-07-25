<?php
// admin/product_add.php
include 'includes/header.php';
include 'includes/sidebar.php';

$categories = $pdo->query("SELECT * FROM product_categories WHERE status = 1")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands WHERE status = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('admin/product_add.php');
    }
    
    $name = sanitizeInput($_POST['name']);
    $slug = generateSlug($name);
    $category_id = (int)$_POST['category_id'];
    $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $generic_name = sanitizeInput($_POST['generic_name']);
    $price = (float)$_POST['price'];
    $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $stock = (int)$_POST['stock_quantity'];
    $sku = sanitizeInput($_POST['sku']);
    $description = $_POST['description']; // basic sanitization might be needed depending on editor
    $dosage_info = $_POST['dosage_info'];
    $side_effects = $_POST['side_effects'];
    $storage_instructions = $_POST['storage_instructions'];
    $prescription_required = isset($_POST['prescription_required']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Image Upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = $slug . '-' . time() . '.' . $ext;
            $uploadPath = '../assets/uploads/products/' . $imageName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $image = $imageName;
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (category_id, brand_id, name, generic_name, slug, description, dosage_info, side_effects, storage_instructions, price, discount_price, stock_quantity, sku, image, prescription_required, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $category_id, $brand_id, $name, $generic_name, $slug, $description, $dosage_info, $side_effects, $storage_instructions, $price, $discount_price, $stock, $sku, $image, $prescription_required, $is_featured, $status
        ]);
        
        setFlashMessage('success', 'Product added successfully.');
        redirect('admin/products.php');
    } catch (PDOException $e) {
        setFlashMessage('error', 'Error adding product. Slug or SKU might already exist.');
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Add New Product</h3>
    <a href="products.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Products</a>
</div>

<form action="product_add.php" method="POST" enctype="multipart/form-data" class="row">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Basic Information</h5>
                <div class="mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Generic Name</label>
                    <input type="text" name="generic_name" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Product Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>
                
                <h5 class="fw-bold mt-5 mb-4">Medical Details</h5>
                <div class="mb-3">
                    <label class="form-label">Dosage & Usage Information</label>
                    <textarea name="dosage_info" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Side Effects</label>
                    <textarea name="side_effects" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Storage Instructions</label>
                    <textarea name="storage_instructions" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Organization</h5>
                <div class="mb-3">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Brand</label>
                    <select name="brand_id" class="form-select">
                        <option value="">No Brand</option>
                        <?php foreach($brands as $brand): ?>
                            <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Pricing & Inventory</h5>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Regular Price *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Sale Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="discount_price" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Stock Qty *</label>
                        <input type="number" name="stock_quantity" class="form-control" value="10" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Product Image</h5>
                <input type="file" name="image" class="form-control" accept="image/jpeg, image/png, image/webp">
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Settings</h5>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="prescription_required" id="prescription_required" value="1">
                    <label class="form-check-label text-warning fw-semibold" for="prescription_required">Requires Prescription (Rx)</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1">
                    <label class="form-check-label" for="is_featured">Featured Product</label>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                    <label class="form-check-label" for="status">Active (Visible)</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Save Product</button>
            </div>
        </div>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
