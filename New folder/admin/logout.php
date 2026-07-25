<?php
// admin/logout.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);

setFlashMessage('success', 'Logged out successfully.');
redirect('admin/login.php');
?>
