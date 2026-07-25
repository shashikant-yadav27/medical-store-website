<?php
// auth/logout.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Unset all of the session variables for user (keep admin if separate, but here we just unset user)
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);

// If we wanted to destroy the whole session (including cart and admin)
// session_destroy();

setFlashMessage('success', 'You have been successfully logged out.');
redirect('auth/login.php');
?>
