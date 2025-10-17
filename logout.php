<?php
session_start();
require_once __DIR__ . '/controllers/UserController.php';
$userController = new UserController();
$userController->logout();
?>