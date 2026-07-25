<?php
// admin/includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
        <!-- Sidebar -->
        <div class="sidebar p-3" style="width: 250px;">
            <h4 class="text-center text-white mb-4"><i class="bi bi-capsule text-primary"></i> Admin Panel</h4>
            
            <a href="index.php" class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            
            <p class="text-muted small text-uppercase mt-4 mb-2 px-2">Catalog</p>
            <a href="categories.php" class="<?php echo ($currentPage == 'categories.php') ? 'active' : ''; ?>">
                <i class="bi bi-tags me-2"></i> Categories
            </a>
            <a href="brands.php" class="<?php echo ($currentPage == 'brands.php') ? 'active' : ''; ?>">
                <i class="bi bi-award me-2"></i> Brands
            </a>
            <a href="products.php" class="<?php echo ($currentPage == 'products.php' || $currentPage == 'product_add.php') ? 'active' : ''; ?>">
                <i class="bi bi-box-seam me-2"></i> Products
            </a>
            
            <p class="text-muted small text-uppercase mt-4 mb-2 px-2">Sales</p>
            <a href="orders.php" class="<?php echo ($currentPage == 'orders.php') ? 'active' : ''; ?>">
                <i class="bi bi-cart-check me-2"></i> Orders
            </a>
            <a href="prescriptions.php" class="<?php echo ($currentPage == 'prescriptions.php') ? 'active' : ''; ?>">
                <i class="bi bi-file-medical me-2"></i> Prescriptions
            </a>
            
            <p class="text-muted small text-uppercase mt-4 mb-2 px-2">Users</p>
            <a href="customers.php" class="<?php echo ($currentPage == 'customers.php') ? 'active' : ''; ?>">
                <i class="bi bi-people me-2"></i> Customers
            </a>
            
            <p class="text-muted small text-uppercase mt-4 mb-2 px-2">Marketing</p>
            <a href="advertisements.php" class="<?php echo ($currentPage == 'advertisements.php') ? 'active' : ''; ?>">
                <i class="bi bi-megaphone me-2"></i> Advertisements
            </a>
            
            <div class="mt-auto pt-4">
                <a href="logout.php" class="text-danger">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content Area Wrapper -->
        <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
            <!-- Top Navbar inside main content -->
            <nav class="navbar navbar-expand-lg navbar-admin px-4 py-2">
                <div class="container-fluid">
                    <button class="btn btn-light d-lg-none" type="button" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <a href="../" target="_blank" class="btn btn-outline-primary btn-sm me-3">
                            <i class="bi bi-globe"></i> View Website
                        </a>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <div class="main-content flex-grow-1">
                <div class="container-fluid">
                    <?php displayFlashMessage(); ?>
