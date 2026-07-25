<?php
// prescription.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to upload your prescription.');
    redirect('auth/login.php?redirect=prescription.php');
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('prescription.php');
    }
    
    if (isset($_FILES['prescription']) && $_FILES['prescription']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (in_array($_FILES['prescription']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['prescription']['name'], PATHINFO_EXTENSION);
            $fileName = 'rx-' . $user_id . '-' . time() . '.' . $ext;
            $uploadPath = 'assets/uploads/prescriptions/' . $fileName;
            
            if (move_uploaded_file($_FILES['prescription']['tmp_name'], $uploadPath)) {
                $stmt = $pdo->prepare("INSERT INTO prescriptions (user_id, file_path, status) VALUES (?, ?, 'Pending')");
                $stmt->execute([$user_id, $fileName]);
                
                setFlashMessage('success', 'Prescription uploaded successfully. Our pharmacist will review it shortly.');
                redirect('profile.php?tab=prescriptions');
            } else {
                setFlashMessage('error', 'Failed to save file.');
            }
        } else {
            setFlashMessage('error', 'Invalid file type. Only JPG, PNG, and PDF are allowed.');
        }
    } else {
        setFlashMessage('error', 'Please select a valid file to upload.');
    }
}

include 'includes/header.php';
?>

<div class="bg-light py-3 border-bottom mb-5">
    <div class="container">
        <h4 class="mb-0 fw-bold">Upload Prescription</h4>
    </div>
</div>

<div class="container pb-5 mb-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 4rem;"></i>
                        <h4 class="fw-bold mt-2">Upload Valid Prescription</h4>
                        <p class="text-muted">Please attach a clear image or PDF of your valid prescription.</p>
                    </div>
                    
                    <form action="prescription.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select File (JPG, PNG, PDF)</label>
                            <input class="form-control form-control-lg" type="file" name="prescription" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        
                        <div class="alert alert-info border-0 rounded bg-light text-muted small">
                            <ul class="mb-0">
                                <li>Ensure the doctor's name, signature, and date are clearly visible.</li>
                                <li>The prescription should be valid and not expired.</li>
                                <li>Maximum file size allowed is 5MB.</li>
                            </ul>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold mt-3">Upload & Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
