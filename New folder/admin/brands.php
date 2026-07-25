<?php
// admin/brands.php
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('admin/brands.php');
    }
    
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $name = sanitizeInput($_POST['name']);
        $slug = generateSlug($name);
        
        $stmt = $pdo->prepare("INSERT INTO brands (name, slug) VALUES (?, ?)");
        try {
            $stmt->execute([$name, $slug]);
            setFlashMessage('success', 'Brand added successfully.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Brand already exists or database error.');
        }
        redirect('admin/brands.php');
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name = sanitizeInput($_POST['name']);
        $slug = generateSlug($name);
        $status = isset($_POST['status']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE brands SET name = ?, slug = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $status, $id]);
        setFlashMessage('success', 'Brand updated successfully.');
        redirect('admin/brands.php');
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('success', 'Brand deleted successfully.');
        redirect('admin/brands.php');
    }
}

// Fetch all brands
$brands = $pdo->query("SELECT * FROM brands ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Manage Brands</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus"></i> Add Brand</button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($brands as $b): ?>
                    <tr>
                        <td class="ps-4"><?php echo $b['id']; ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($b['name']); ?></td>
                        <td><?php echo htmlspecialchars($b['slug']); ?></td>
                        <td>
                            <?php if($b['status']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-light border edit-btn" 
                                data-id="<?php echo $b['id']; ?>"
                                data-name="<?php echo htmlspecialchars($b['name']); ?>"
                                data-status="<?php echo $b['status']; ?>"
                                data-bs-toggle="modal" data-bs-target="#editModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="brands.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="brands.php" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Add Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Brand Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Brand</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="brands.php" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Edit Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Brand Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="status" id="edit_status" value="1">
                    <label class="form-check-label" for="edit_status">Active Status</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update Brand</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_status').checked = this.dataset.status == 1;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
