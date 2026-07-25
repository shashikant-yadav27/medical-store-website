<?php
// auth/register.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect('profile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request. Please try again.');
        redirect('auth/register.php');
    }

    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        setFlashMessage('error', 'Please fill all required fields.');
    } elseif ($password !== $confirm_password) {
        setFlashMessage('error', 'Passwords do not match.');
    } elseif (strlen($password) < 6) {
        setFlashMessage('error', 'Password must be at least 6 characters.');
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            setFlashMessage('error', 'Email is already registered.');
        } else {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $hashedPassword])) {
                setFlashMessage('success', 'Registration successful! Please login.');
                redirect('auth/login.php');
            } else {
                setFlashMessage('error', 'Registration failed. Please try again.');
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white text-center py-3">
                    <h4 class="mb-0 fw-bold text-success">Create an Account</h4>
                </div>
                <div class="card-body p-4">
                    <form action="register.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold mt-3">Register</button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Login Here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
