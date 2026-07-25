<?php
// admin/includes/header.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if admin is logged in, except on login page
$currentPage = basename($_SERVER['PHP_SELF']);
if ($currentPage !== 'login.php' && !isAdminLoggedIn()) {
    redirect('admin/login.php');
}

$settings = getSettings($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo htmlspecialchars($settings['site_name']); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: #fff;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #0d6efd;
            color: #fff;
        }
        .main-content {
            padding: 20px;
        }
        .navbar-admin {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
        }
    </style>
</head>
<body>
    <div class="d-flex">
