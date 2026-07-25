<?php
// admin/product_edit.php
include 'includes/header.php';
include 'includes/sidebar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('admin/products.php');

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) redirect('admin/products.php');

$categories = $pdo->query("SELECT * FROM product_categories WHERE status = 1")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands WHERE status = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect("admin/product_edit.php?id=$id");
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
    $description = $_POST['description']; 
    $dosage_info = $_POST['dosage_info'];
    $side_effects = $_POST['side_effects'];
    $storage_instructions = $_POST['storage_instructions'];
    $prescription_required = isset($_POST['prescription_required']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Image Upload
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = $slug . '-' . time() . '.' . $ext;
            $uploadPath = '../assets/uploads/products/' . $imageName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                // Delete old image
                if ($image && file_exists('../assets/uploads/products/' . $image)) {
                    unlink('../assets/uploads/products/' . $image);
                }
                $image = $imageName;
            }
        }
    }
    
    try {
        $updateStmt = $pdo->prepare("UPDATE products SET category_id=?, brand_id=?, name=?, generic_name=?, slug=?, description=?, dosage_info=?, side_effects=?, storage_instructions=?, price=?, discount_price=?, stock_quantity=?, sku=?, image=?, prescription_required=?, is_featured=?, status=? WHERE id=?");
        $updateStmt->execute([
            $category_id, $brand_id, $name, $generic_name, $slug, $description, $dosage_info, $side_effects, $storage_instructions, $price, $discount_price, $stock, $sku, $image, $prescription_required, $is_featured, $status, $id
        ]);
        
        setFlashMessage('success', 'Product updated successfully.');
        redirect('admin/products.php');
    } catch (PDOException $e) {
        setFlashMessage('error', 'Error updating product. Slug or SKU might already exist.');
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Edit Product: <?php echo htmlspecialchars($product['name']); ?></h3>
    <a href="products.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Products</a>
</div>

<form action="product_edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="row">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Basic Information</h5>
                <div class="mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Generic Name</label>
                    <input type="text" name="generic_name" class="form-control" value="<?php echo htmlspecialchars($product['generic_name']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Product Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <h5 class="fw-bold mt-5 mb-4">Medical Details</h5>
                <div class="mb-3">
                    <label class="form-label">Dosage & Usage Information</label>
                    <textarea name="dosage_info" class="form-control" rows="3"><?php echo htmlspecialchars($product['dosage_info']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Side Effects</label>
                    <textarea name="side_effects" class="form-control" rows="3"><?php echo htmlspecialchars($product['side_effects']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Storage Instructions</label>
                    <textarea name="storage_instructions" class="form-control" rows="2"><?php echo htmlspecialchars($product['storage_instructions']); ?></textarea>
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
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Brand</label>
                    <select name="brand_id" class="form-select">
                        <option value="">No Brand</option>
                        <?php foreach($brands as $brand): ?>
                            <option value="<?php echo $brand['id']; ?>" <?php echo ($product['brand_id'] == $brand['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($brand['name']); ?></option>
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
                            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Sale Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="discount_price" class="form-control" value="<?php echo $product['discount_price']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Stock Qty *</label>
                        <input type="number" name="stock_quantity" class="form-control" value="<?php echo $product['stock_quantity']; ?>" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($product['sku']); ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Product Image</h5>
                <?php if($product['image']): ?>
                    <div class="mb-3">
                        <img src="../assets/uploads/products/<?php echo $product['image']; ?>" class="img-fluid rounded border" alt="Current Image">
                        <small class="text-muted d-block mt-1">Current Image</small>
                    </div>
                <?php endif; ?>
                <label class="form-label">Update Image (Optional)</label>
                <input type="file" name="image" class="form-control" accept="image/jpeg, image/png, image/webp">
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Settings</h5>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="prescription_required" id="prescription_required" value="1" <?php echo $product['prescription_required'] ? 'checked' : ''; ?>>
                    <label class="form-check-label text-warning fw-semibold" for="prescription_required">Requires Prescription (Rx)</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_featured">Featured Product</label>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="status" id="status" value="1" <?php echo $product['status'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="status">Active (Visible)</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Update Product</button>
            </div>
        </div>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
