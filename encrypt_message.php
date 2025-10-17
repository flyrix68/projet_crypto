<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Disable error output for JSON responses
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/controllers/MessageController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $receiverId = $_POST['receiver_id'];
    $message = $_POST['message'];

    $messageController = new MessageController();
    $userModel = $messageController->getUserModel();

    $receiverPublicKey = $userModel->getPublicKey($receiverId);
    if ($receiverPublicKey) {
        try {
            $rsa = new RSA();
            $publicKeyData = json_decode($receiverPublicKey, true);
            $encrypted = $rsa->encrypt($message, $publicKeyData['e'], $publicKeyData['n']);
            echo json_encode(['success' => true, 'encrypted' => $encrypted]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Encryption failed: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Receiver not found or has no public key']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
}
exit;
?>