<?php
// includes/footer.php
?>
    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3 mt-5">
        <div class="container">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h4 class="text-success fw-bold mb-3"><i class="bi bi-capsule"></i> <?php echo htmlspecialchars($settings['site_name']); ?></h4>
                    <p class="text-muted">Your trusted online pharmacy for authentic medicines and healthcare products delivered to your doorstep.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-geo-alt text-success me-2"></i> <?php echo htmlspecialchars($settings['address']); ?></li>
                        <li class="mb-2"><i class="bi bi-telephone text-success me-2"></i> <?php echo htmlspecialchars($settings['contact_phone']); ?></li>
                        <li class="mb-2"><i class="bi bi-envelope text-success me-2"></i> <?php echo htmlspecialchars($settings['contact_email']); ?></li>
                    </ul>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>shop.php" class="text-muted text-decoration-none">Shop</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>prescription.php" class="text-muted text-decoration-none">Upload Prescription</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>blog.php" class="text-muted text-decoration-none">Health Blog</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>contact.php" class="text-muted text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                
                <!-- Customer Service -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-3">Customer Service</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>profile.php" class="text-muted text-decoration-none">My Account</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>profile.php?tab=orders" class="text-muted text-decoration-none">Order History</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Returns Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Terms & Conditions</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="mb-3">Subscribe to Newsletter</h5>
                    <p class="text-muted">Get updates on new products and special offers.</p>
                    <form action="#" method="POST" class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Your Email Address" required>
                        <button type="submit" class="btn btn-success">Subscribe</button>
                    </form>
                    <div class="mt-4">
                        <a href="#" class="text-white fs-4 me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white fs-4 me-3"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-white fs-4 me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <hr class="border-secondary mt-4 mb-3">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name']); ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <img src="https://via.placeholder.com/300x40?text=Payment+Methods" alt="Payment Methods" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
</body>
</html>
