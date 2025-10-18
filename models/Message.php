<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/RSA.php';

class Message {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function sendMessage($senderId, $receiverId, $message, $receiverPublicKey) {
        // For now, store message as plain text - encryption happens in frontend
        // Insert message
        $stmt = $this->db->prepare("INSERT INTO messages (sender_id, receiver_id, encrypted_message) VALUES (?, ?, ?)");
        return $stmt->execute(array($senderId, $receiverId, $message));
    }

    public function sendEncryptedMessage($senderId, $receiverId, $encryptedMessage) {
        // Insert already encrypted message
        $stmt = $this->db->prepare("INSERT INTO messages (sender_id, receiver_id, encrypted_message) VALUES (?, ?, ?)");
        return $stmt->execute(array($senderId, $receiverId, $encryptedMessage));
    }

    public function getMessages($userId, $otherUserId) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username as sender_username
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.sent_at ASC
        ");
        $stmt->execute(array($userId, $otherUserId, $otherUserId, $userId));
        return $stmt->fetchAll();
    }

    public function decryptMessage($encryptedMessage, $privateKey) {
        $privateKeyData = json_decode($privateKey, true);
        $rsa = new RSA();
        return $rsa->decrypt($encryptedMessage, $privateKeyData['d'], $privateKeyData['n']);
    }

    public function markAsRead($messageId, $userId) {
        $stmt = $this->db->prepare("UPDATE messages SET status = 'read', read_at = NOW() WHERE id = ? AND receiver_id = ?");
        return $stmt->execute(array($messageId, $userId));
    }

    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND status != 'read'");
        $stmt->execute(array($userId));
        $result = $stmt->fetch();
        return $result['count'];
    }

    private function encryptMessage($message, $publicKey) {
        $publicKeyData = json_decode($publicKey, true);
        $rsa = new RSA();
        return $rsa->encrypt($message, $publicKeyData['e'], $publicKeyData['n']);
    }
}
?>