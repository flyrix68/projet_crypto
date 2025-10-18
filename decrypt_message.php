<?php
// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Allow both AJAX and regular POST requests for now
// if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
//     http_response_code(400);
//     exit('Direct access not allowed');
// }

// Disable all error output
error_reporting(0);
ini_set('display_errors', 0);

// Clean any existing output
if (ob_get_level()) {
    ob_clean();
}

// Set JSON header
header('Content-Type: application/json');

require_once __DIR__ . '/models/RSA.php';

$response = ['success' => false, 'error' => 'Unknown error'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
        $message = trim($_POST['message'] ?? '');

        if (empty($message)) {
            $response = ['success' => false, 'error' => 'No message provided'];
        } elseif (!isset($_SESSION['private_key']) || empty($_SESSION['private_key'])) {
            $response = ['success' => false, 'error' => 'Private key not found. Please generate RSA keys first.'];
        } else {
            $rsa = new RSA();
            $privateKeyData = json_decode($_SESSION['private_key'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $response = ['success' => false, 'error' => 'Invalid private key format'];
            } else {
                // Check if message is multi-part (contains commas)
                if (strpos($message, ',') !== false) {
                    // Multi-part message: decrypt each part separately
                    $encryptedParts = array_map('trim', explode(',', $message));
                    $decryptedMessage = '';

                    foreach ($encryptedParts as $part) {
                        if (!empty($part)) {
                            $decryptedChar = $rsa->decrypt($part, $privateKeyData['d'], $privateKeyData['n']);
                            $decryptedMessage .= $decryptedChar;
                        }
                    }

                    $response = ['success' => true, 'decrypted' => $decryptedMessage];
                } else {
                    // Single-part message
                    $decrypted = $rsa->decrypt($message, $privateKeyData['d'], $privateKeyData['n']);
                    $response = ['success' => true, 'decrypted' => $decrypted];
                }
            }
        }
    } else {
        $response = ['success' => false, 'error' => 'Unauthorized'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'error' => 'Decryption failed: ' . $e->getMessage()];
}

// Send response
echo json_encode($response);
exit;
?>