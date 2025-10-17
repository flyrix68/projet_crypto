<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Disable error output for JSON responses
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/controllers/UserController.php';
$userController = new UserController();
$userController->generateKeys();
?>