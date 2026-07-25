<?php
// admin/advertisements.php
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle Add/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('admin/advertisements.php');
    }
    
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $title = sanitizeInput($_POST['title']);
        $type = sanitizeInput($_POST['type']);
        $link_url = sanitizeInput($_POST['link_url']);
        
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = 'ad-' . time() . '.' . $ext;
            // Should be moved to assets/uploads/ads/ (creating directory if needed)
            if(!is_dir('../assets/uploads/ads')) {
                mkdir('../assets/uploads/ads', 0777, true);
            }
            if (move_uploaded_file($_FILES['image']['tmp_name'], '../assets/uploads/ads/' . $imageName)) {
                $image = $imageName;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO advertisements (title, type, image_url, link_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $type, $image, $link_url]);
        
        setFlashMessage('success', 'Advertisement added successfully.');
        redirect('admin/advertisements.php');
        
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        $stmtImg = $pdo->prepare("SELECT image_url FROM advertisements WHERE id = ?");
        $stmtImg->execute([$id]);
        $img = $stmtImg->fetchColumn();
        if ($img && file_exists('../assets/uploads/ads/' . $img)) {
            unlink('../assets/uploads/ads/' . $img);
        }
        
        $stmt = $pdo->prepare("DELETE FROM advertisements WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('success', 'Advertisement deleted.');
        redirect('admin/advertisements.php');
    }
}

$ads = $pdo->query("SELECT * FROM advertisements ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Manage Advertisements</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdModal"><i class="bi bi-plus"></i> Add Advertisement</button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Banner</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($ads)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No advertisements found.</td></tr>
                    <?php else: ?>
                        <?php foreach($ads as $ad): ?>
                        <tr>
                            <td class="ps-4">
                                <?php if($ad['image_url']): ?>
                                    <img src="../assets/uploads/ads/<?php echo $ad['image_url']; ?>" height="40" class="rounded object-fit-cover">
                                <?php else: ?>
                                    <span class="text-muted">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($ad['title']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo $ad['type']; ?></span></td>
                            <td><a href="<?php echo htmlspecialchars($ad['link_url']); ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 150px;"><?php echo htmlspecialchars($ad['link_url']); ?></a></td>
                            <td>
                                <?php if($ad['status']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <form action="advertisements.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this advertisement?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $ad['id']; ?>">
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

<!-- Add Modal -->
<div class="modal fade" id="addAdModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="advertisements.php" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Add Advertisement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add">
                
                <div class="mb-3">
                    <label class="form-label">Ad Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ad Type</label>
                    <select name="type" class="form-select" required>
                        <option value="Homepage Slider">Homepage Slider</option>
                        <option value="Offer Banner">Offer Banner</option>
                        <option value="Popup">Popup</option>
                        <option value="Sidebar">Sidebar</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Banner Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Target Link URL (Optional)</label>
                    <input type="url" name="link_url" class="form-control" placeholder="https://...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Advertisement</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
