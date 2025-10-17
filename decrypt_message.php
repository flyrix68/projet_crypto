<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Disable error output for JSON responses
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/models/RSA.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $message = $_POST['message'];

    if (isset($_SESSION['private_key']) && $_SESSION['private_key']) {
        try {
            $rsa = new RSA();
            $privateKeyData = json_decode($_SESSION['private_key'], true);
            $decrypted = $rsa->decrypt($message, $privateKeyData['d'], $privateKeyData['n']);
            echo json_encode(['success' => true, 'decrypted' => $decrypted]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Decryption failed: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Private key not found. Please generate RSA keys first.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
}
exit;
?>