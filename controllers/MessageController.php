<?php
// Session will be started by the calling script
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/User.php';

class MessageController {
    private $messageModel;
    private $userModel;

    public function __construct() {
        $this->messageModel = new Message();
        $this->userModel = new User();
    }

    public function sendMessage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $receiverId = $_POST['receiver_id'];
            $message = $_POST['message'];
            $encrypted = isset($_POST['encrypted']) ? $_POST['encrypted'] : 'false';

            if ($encrypted === 'true') {
                // Message is already encrypted
                $this->messageModel->sendEncryptedMessage($_SESSION['user_id'], $receiverId, $message);
                echo json_encode(['success' => true]);
            } else {
                // Check if receiver exists and has public key
                $receiver = $this->userModel->getPublicKey($receiverId);
                if ($receiver !== null) {
                    $this->messageModel->sendMessage($_SESSION['user_id'], $receiverId, $message, $receiver);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Receiver not found or has no public key']);
                }
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        }
        exit;
    }

    public function getMessages() {
        if (isset($_SESSION['user_id']) && isset($_GET['other_user_id'])) {
            $otherUserId = $_GET['other_user_id'];
            $messages = $this->messageModel->getMessages($_SESSION['user_id'], $otherUserId);

            // Decrypt messages for display
            foreach ($messages as &$msg) {
                if ($msg['sender_id'] == $_SESSION['user_id']) {
                    // Sent message - show as encrypted (we can't decrypt it since it's encrypted with receiver's public key)
                    $msg['decrypted_message'] = $msg['encrypted_message']; // Show encrypted version for sent messages
                } else {
                    // Received message - show encrypted version initially, user can decrypt manually
                    $msg['decrypted_message'] = $msg['encrypted_message']; // Show encrypted version, user decrypts manually
                }
            }

            header('Content-Type: application/json');
            echo json_encode($messages);
        } else {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
        exit;
    }

    public function markAsRead() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['message_id'])) {
            $this->messageModel->markAsRead($_POST['message_id'], $_SESSION['user_id']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    public function getUnreadCount() {
        if (isset($_SESSION['user_id'])) {
            $count = $this->messageModel->getUnreadCount($_SESSION['user_id']);
            echo json_encode(['count' => $count]);
        } else {
            echo json_encode(['count' => 0]);
        }
        exit;
    }

    public function getUserModel() {
        return $this->userModel;
    }
}
?>