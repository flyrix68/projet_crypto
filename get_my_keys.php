<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Disable error output for JSON responses
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/models/User.php';

if (isset($_SESSION['user_id'])) {
    $userModel = new User();

    $publicKey = $userModel->getPublicKey($_SESSION['user_id']);
    $privateKeyEncrypted = $userModel->getPrivateKeyEncrypted($_SESSION['user_id']);

    if ($publicKey && $privateKeyEncrypted) {
        // Decrypt private key for display
        $privateKey = $userModel->decryptPrivateKey($privateKeyEncrypted, $_SESSION['password'] ?? '');

        // For demo purposes, show the actual keys
        // In production, showing private keys is a security risk
        echo json_encode([
            'success' => true,
            'publicKey' => json_decode($publicKey, true),
            'privateKey' => json_decode($privateKey, true)
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Keys not found']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
}
exit;
?>