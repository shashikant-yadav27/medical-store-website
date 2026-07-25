<?php
// auth/login.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect('profile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request. Please try again.');
        redirect('auth/login.php');
    }

    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        setFlashMessage('error', 'Please fill all fields.');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] == 1) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                
                // Also log the user into any cart session they had before logging in
                if(isset($_SESSION['cart_session_id'])){
                     $stmt = $pdo->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
                     $stmt->execute([$user['id'], $_SESSION['cart_session_id']]);
                }
                
                setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');
                redirect('profile.php');
            } else {
                setFlashMessage('error', 'Your account has been deactivated.');
            }
        } else {
            setFlashMessage('error', 'Invalid email or password.');
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white text-center py-3">
                    <h4 class="mb-0 fw-bold text-primary">Login to Your Account</h4>
                </div>
                <div class="card-body p-4">
                    <form action="login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="#" class="text-decoration-none">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Login</button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold">Register Here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
