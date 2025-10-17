<?php
session_start();
require_once 'controllers/UserController.php';
$userController = new UserController();
if (!$userController->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
include 'views/dashboard.php';
?>