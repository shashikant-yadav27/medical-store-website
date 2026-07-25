<?php
// admin/login.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (isAdminLoggedIn()) {
    redirect('admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('admin/login.php');
    }

    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        setFlashMessage('error', 'Please fill all fields.');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            if ($admin['status'] == 1) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'];
                
                redirect('admin/index.php');
            } else {
                setFlashMessage('error', 'Account is deactivated.');
            }
        } else {
            setFlashMessage('error', 'Invalid email or password.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Medical Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; display: flex; align-items: center; height: 100vh; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <h2 class="text-primary fw-bold"><i class="bi bi-capsule"></i> Admin Panel</h2>
                </div>
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <?php displayFlashMessage(); ?>
                        <form action="login.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Login to Dashboard</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="../" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Back to Website</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
