<?php
// contact.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request.');
        redirect('contact.php');
    }
    
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        setFlashMessage('error', 'Please fill all required fields.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $phone, $subject, $message])) {
            setFlashMessage('success', 'Your message has been sent successfully. We will get back to you soon.');
            redirect('contact.php');
        } else {
            setFlashMessage('error', 'Something went wrong. Please try again.');
        }
    }
}

$settings = getSettings($pdo);
include 'includes/header.php';
?>

<div class="bg-light py-5 border-bottom mb-5">
    <div class="container text-center">
        <h1 class="fw-bold mb-3">Contact Us</h1>
        <p class="lead text-muted mb-0">Have a question or need assistance? We're here to help.</p>
    </div>
</div>

<div class="container pb-5 mb-4">
    <div class="row g-5">
        <!-- Contact Information -->
        <div class="col-lg-4">
            <h4 class="fw-bold mb-4">Get In Touch</h4>
            <p class="text-muted mb-4">Feel free to reach out to us via email, phone, or simply drop a message using the contact form.</p>
            
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px;">
                    <i class="bi bi-geo-alt fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Our Address</h6>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($settings['address']); ?></p>
                </div>
            </div>
            
            <div class="d-flex align-items-center mb-4">
                <div class="bg-success text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px;">
                    <i class="bi bi-telephone fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Phone Number</h6>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($settings['contact_phone']); ?></p>
                </div>
            </div>
            
            <div class="d-flex align-items-center mb-4">
                <div class="bg-info text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px;">
                    <i class="bi bi-envelope fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Email Address</h6>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($settings['contact_email']); ?></p>
                </div>
            </div>
            
            <div class="mt-5">
                <h6 class="fw-bold mb-3">Follow Us</h6>
                <a href="#" class="btn btn-outline-secondary me-2"><i class="bi bi-facebook"></i></a>
                <a href="#" class="btn btn-outline-secondary me-2"><i class="bi bi-twitter-x"></i></a>
                <a href="#" class="btn btn-outline-secondary me-2"><i class="bi bi-instagram"></i></a>
                <a href="https://wa.me/1234567890" target="_blank" class="btn btn-success"><i class="bi bi-whatsapp me-1"></i> WhatsApp Us</a>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h4 class="fw-bold mb-4">Send a Message</h4>
                    <form action="contact.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control bg-light" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control bg-light" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control bg-light" name="phone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject *</label>
                                <input type="text" class="form-control bg-light" name="subject" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Message *</label>
                            <textarea class="form-control bg-light" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg px-5">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
