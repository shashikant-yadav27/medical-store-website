<?php
// admin/prescriptions.php
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('admin/prescriptions.php');
    }
    
    $id = (int)$_POST['id'];
    $status = sanitizeInput($_POST['status']);
    $notes = sanitizeInput($_POST['admin_notes']);
    
    $stmt = $pdo->prepare("UPDATE prescriptions SET status = ?, admin_notes = ? WHERE id = ?");
    $stmt->execute([$status, $notes, $id]);
    
    setFlashMessage('success', 'Prescription status updated successfully.');
    redirect('admin/prescriptions.php');
}

// Fetch Prescriptions
$stmt = $pdo->query("SELECT p.*, u.name as user_name, u.email as user_email FROM prescriptions p JOIN users u ON p.user_id = u.id ORDER BY p.id DESC");
$prescriptions = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Manage Prescriptions</h3>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>User</th>
                        <th>Date Uploaded</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($prescriptions)): ?>
                        <tr><td colspan="5" class="text-center py-4">No prescriptions found.</td></tr>
                    <?php else: ?>
                        <?php foreach($prescriptions as $p): ?>
                        <tr>
                            <td class="ps-4 fw-semibold">#<?php echo $p['id']; ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($p['user_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($p['user_email']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($p['created_at'])); ?></td>
                            <td>
                                <?php
                                    $badgeClass = 'bg-warning text-dark';
                                    if ($p['status'] == 'Approved') $badgeClass = 'bg-success';
                                    elseif ($p['status'] == 'Rejected') $badgeClass = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $p['status']; ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary view-btn" 
                                    data-id="<?php echo $p['id']; ?>"
                                    data-file="<?php echo $p['file_path']; ?>"
                                    data-status="<?php echo $p['status']; ?>"
                                    data-notes="<?php echo htmlspecialchars($p['admin_notes']); ?>"
                                    data-bs-toggle="modal" data-bs-target="#viewModal">
                                    Review
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" action="prescriptions.php" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Review Prescription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="id" id="presc_id">
                
                <div class="mb-4 text-center bg-light p-3 rounded">
                    <a id="presc_file_link" href="#" target="_blank" class="btn btn-outline-primary mb-2">Open File in New Tab <i class="bi bi-box-arrow-up-right"></i></a>
                    <div id="presc_preview" class="mt-3"></div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Update Status</label>
                        <select name="status" id="presc_status" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approve</option>
                            <option value="Rejected">Reject</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" id="presc_notes" class="form-control" rows="2" placeholder="Reason for rejection, etc."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewBtns = document.querySelectorAll('.view-btn');
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('presc_id').value = this.dataset.id;
            document.getElementById('presc_status').value = this.dataset.status;
            document.getElementById('presc_notes').value = this.dataset.notes;
            
            let filePath = '../assets/uploads/prescriptions/' + this.dataset.file;
            let ext = this.dataset.file.split('.').pop().toLowerCase();
            
            document.getElementById('presc_file_link').href = filePath;
            
            let previewArea = document.getElementById('presc_preview');
            if (ext === 'pdf') {
                previewArea.innerHTML = '<iframe src="'+filePath+'" width="100%" height="400px" style="border:1px solid #ddd;"></iframe>';
            } else {
                previewArea.innerHTML = '<img src="'+filePath+'" class="img-fluid rounded border" style="max-height: 400px;">';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
