<?php
// profile.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to access your profile.');
    redirect('auth/login.php');
}

$user_id = $_SESSION['user_id'];
$tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'dashboard';

// Fetch User
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

// Fetch Orders
$stmtOrders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmtOrders->execute([$user_id]);
$orders = $stmtOrders->fetchAll();

// Fetch Prescriptions
$stmtPresc = $pdo->prepare("SELECT * FROM prescriptions WHERE user_id = ? ORDER BY id DESC");
$stmtPresc->execute([$user_id]);
$prescriptions = $stmtPresc->fetchAll();

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('profile.php');
    }
    
    $name = sanitizeInput($_POST['name']);
    $phone = sanitizeInput($_POST['phone']);
    
    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $user_id]);
    
    $_SESSION['user_name'] = $name;
    setFlashMessage('success', 'Profile updated successfully.');
    redirect('profile.php?tab=settings');
}

include 'includes/header.php';
?>

<div class="bg-primary text-white py-4 mb-4">
    <div class="container">
        <div class="d-flex align-items-center">
            <div class="bg-white text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <div>
                <h3 class="mb-0 fw-bold">Hello, <?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="mb-0 text-white-50"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container py-4 mb-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="list-group list-group-flush">
                    <a href="profile.php?tab=dashboard" class="list-group-item list-group-item-action py-3 <?php echo ($tab == 'dashboard') ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-grid me-2"></i> Dashboard
                    </a>
                    <a href="profile.php?tab=orders" class="list-group-item list-group-item-action py-3 <?php echo ($tab == 'orders') ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-box-seam me-2"></i> My Orders
                    </a>
                    <a href="profile.php?tab=prescriptions" class="list-group-item list-group-item-action py-3 <?php echo ($tab == 'prescriptions') ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-file-medical me-2"></i> My Prescriptions
                    </a>
                    <a href="profile.php?tab=settings" class="list-group-item list-group-item-action py-3 <?php echo ($tab == 'settings') ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-person-gear me-2"></i> Account Settings
                    </a>
                    <a href="auth/logout.php" class="list-group-item list-group-item-action py-3 text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            
            <?php if($tab == 'dashboard'): ?>
                <h4 class="fw-bold mb-4">Account Dashboard</h4>
                <div class="row g-4 mb-4">
                    <div class="col-sm-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-bag-check fs-1 text-primary mb-2"></i>
                                <h3 class="fw-bold mb-1"><?php echo count($orders); ?></h3>
                                <p class="text-muted mb-0">Total Orders</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-file-earmark-medical fs-1 text-success mb-2"></i>
                                <h3 class="fw-bold mb-1"><?php echo count($prescriptions); ?></h3>
                                <p class="text-muted mb-0">Prescriptions</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between">
                        <h6 class="mb-0 fw-bold">Recent Orders</h6>
                        <a href="profile.php?tab=orders" class="text-decoration-none">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $recentOrders = array_slice($orders, 0, 3);
                                    if(empty($recentOrders)): ?>
                                        <tr><td colspan="4" class="text-center py-3 text-muted">No recent orders.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($recentOrders as $o): ?>
                                        <tr>
                                            <td class="ps-3 fw-semibold">#<?php echo $o['order_number']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                                            <td><?php echo formatCurrency($o['total_amount']); ?></td>
                                            <td>
                                                <?php
                                                    $bClass = 'bg-secondary';
                                                    if($o['order_status'] == 'Delivered') $bClass = 'bg-success';
                                                    elseif($o['order_status'] == 'Processing') $bClass = 'bg-primary';
                                                    elseif($o['order_status'] == 'Cancelled') $bClass = 'bg-danger';
                                                    elseif($o['order_status'] == 'Pending') $bClass = 'bg-warning text-dark';
                                                ?>
                                                <span class="badge <?php echo $bClass; ?>"><?php echo $o['order_status']; ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            
            <?php elseif($tab == 'orders'): ?>
                <h4 class="fw-bold mb-4">My Orders</h4>
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Order #</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($orders)): ?>
                                        <tr><td colspan="5" class="text-center py-5 text-muted">You haven't placed any orders yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($orders as $o): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">#<?php echo $o['order_number']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                                            <td class="fw-semibold text-primary"><?php echo formatCurrency($o['total_amount']); ?></td>
                                            <td>
                                                <?php
                                                    $bClass = 'bg-secondary';
                                                    if($o['order_status'] == 'Delivered') $bClass = 'bg-success';
                                                    elseif($o['order_status'] == 'Processing') $bClass = 'bg-primary';
                                                    elseif($o['order_status'] == 'Cancelled') $bClass = 'bg-danger';
                                                    elseif($o['order_status'] == 'Pending') $bClass = 'bg-warning text-dark';
                                                ?>
                                                <span class="badge <?php echo $bClass; ?>"><?php echo $o['order_status']; ?></span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-outline-secondary" onclick="alert('Order details view not fully implemented in frontend yet.')">View Details</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            
            <?php elseif($tab == 'prescriptions'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">My Prescriptions</h4>
                    <a href="prescription.php" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Upload New</a>
                </div>
                <div class="row g-4">
                    <?php if(empty($prescriptions)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-file-earmark-x fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted">No prescriptions uploaded yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($prescriptions as $p): ?>
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-3">
                                        <h6 class="fw-bold mb-0">Prescription #<?php echo $p['id']; ?></h6>
                                        <?php
                                            $bClass = 'bg-warning text-dark';
                                            if ($p['status'] == 'Approved') $bClass = 'bg-success';
                                            elseif ($p['status'] == 'Rejected') $bClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $bClass; ?>"><?php echo $p['status']; ?></span>
                                    </div>
                                    <p class="text-muted small mb-3"><i class="bi bi-calendar-check me-1"></i> Uploaded: <?php echo date('M d, Y g:i A', strtotime($p['created_at'])); ?></p>
                                    
                                    <?php if($p['admin_notes']): ?>
                                        <div class="alert alert-light border p-2 small mb-3">
                                            <strong>Note:</strong> <?php echo htmlspecialchars($p['admin_notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo SITE_URL; ?>assets/uploads/prescriptions/<?php echo $p['file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-eye"></i> View File</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php elseif($tab == 'settings'): ?>
                <h4 class="fw-bold mb-4">Account Settings</h4>
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="profile.php?tab=settings" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address (Cannot be changed)</label>
                                    <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        </form>
                        
                        <hr class="my-5">
                        
                        <h5 class="fw-bold mb-4">Change Password</h5>
                        <form action="auth/change_password.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-outline-danger px-4" disabled>Update Password</button>
                            <small class="text-muted ms-2">(Feature placeholder)</small>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
