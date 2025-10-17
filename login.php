<?php
session_start();
require_once __DIR__ . '/controllers/UserController.php';
$userController = new UserController();

// Check if user is already logged in
if ($userController->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$userController->login();
?>