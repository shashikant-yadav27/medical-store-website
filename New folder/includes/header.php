<?php
require_once 'config.php';
require_once 'functions.php';

$settings = getSettings($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name']); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body>
    <!-- Topbar -->
    <div class="bg-primary text-white py-1">
        <div class="container d-flex justify-content-between align-items-center">
            <small><i class="bi bi-telephone-fill"></i> <?php echo htmlspecialchars($settings['contact_phone']); ?></small>
            <div>
                <?php if(isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>profile.php" class="text-white text-decoration-none me-3"><i class="bi bi-person-circle"></i> My Account</a>
                    <a href="<?php echo SITE_URL; ?>auth/logout.php" class="text-white text-decoration-none"><i class="bi bi-box-arrow-right"></i> Logout</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>auth/login.php" class="text-white text-decoration-none me-3"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                    <a href="<?php echo SITE_URL; ?>auth/register.php" class="text-white text-decoration-none"><i class="bi bi-person-plus-fill"></i> Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand text-success fw-bold fs-3" href="<?php echo SITE_URL; ?>">
                <i class="bi bi-capsule"></i> <?php echo htmlspecialchars($settings['site_name']); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <form class="d-flex mx-auto w-50 position-relative" action="<?php echo SITE_URL; ?>shop.php" method="GET">
                    <input class="form-control rounded-pill pe-5" type="search" name="q" placeholder="Search for medicines, health products..." aria-label="Search" id="searchInput">
                    <button class="btn position-absolute end-0 top-50 translate-middle-y text-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    <!-- Search Suggestions Container -->
                    <div id="searchSuggestions" class="position-absolute w-100 bg-white border rounded mt-1 shadow-sm" style="top:100%; display:none; z-index:1000;"></div>
                </form>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="<?php echo SITE_URL; ?>prescription.php">
                            <i class="bi bi-file-medical text-primary"></i> Upload Prescription
                        </a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="nav-link position-relative cart-icon" href="<?php echo SITE_URL; ?>cart.php">
                            <i class="bi bi-cart3 fs-4 text-dark"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">
                                <?php echo getCartCount($pdo); ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Secondary Navbar (Categories) -->
    <div class="bg-light border-bottom">
        <div class="container">
            <ul class="nav justify-content-center py-2 secondary-nav">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?php echo SITE_URL; ?>shop.php?category=prescription-medicines">Prescription</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?php echo SITE_URL; ?>shop.php?category=otc-medicines">OTC Medicines</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?php echo SITE_URL; ?>shop.php?category=vitamins-supplements">Vitamins & Supplements</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?php echo SITE_URL; ?>shop.php?category=personal-care">Personal Care</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?php echo SITE_URL; ?>shop.php?category=diabetes-care">Diabetes Care</a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="container mt-3">
        <?php displayFlashMessage(); ?>
    </div>
