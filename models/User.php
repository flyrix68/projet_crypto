<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/RSA.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($username, $password) {
        // Check if user already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute(array($username));
        if ($stmt->fetch()) {
            return false; // User already exists
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user without keys (keys will be generated later in dashboard)
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, public_key, private_key_encrypted) VALUES (?, ?, NULL, NULL)");
        if ($stmt->execute(array($username, $passwordHash))) {
            return true; // Success
        } else {
            return null; // Database insert failed
        }
    }

    public function generateUserKeys($userId, $password, $p = null, $q = null) {
        // Generate RSA key pair
        $rsa = new RSA();
        if ($p && $q) {
            $rsa->generateKeys($p, $q);
        } else {
            // Generate random primes
            $p = $this->generatePrime(256);
            $q = $this->generatePrime(256);
            $rsa->generateKeys($p, $q);
        }

        $publicKey = json_encode($rsa->getPublicKey());
        $privateKey = json_encode($rsa->getPrivateKey());

        // Encrypt private key with user's password (for storage)
        $encryptedPrivateKey = $this->encryptPrivateKey($privateKey, $password);

        // Update user with keys
        $stmt = $this->db->prepare("UPDATE users SET public_key = ?, private_key_encrypted = ? WHERE id = ?");
        return $stmt->execute(array($publicKey, $encryptedPrivateKey, $userId));
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT id, password_hash, public_key, private_key_encrypted FROM users WHERE username = ?");
        $stmt->execute(array($username));
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Decrypt private key if it exists
            $privateKey = null;
            if ($user['private_key_encrypted']) {
                $privateKey = $this->decryptPrivateKey($user['private_key_encrypted'], $password);
            }
            return array(
                'id' => $user['id'],
                'username' => $username,
                'public_key' => $user['public_key'],
                'private_key' => $privateKey
            );
        }
        return false;
    }

    public function getPublicKey($userId) {
        $stmt = $this->db->prepare("SELECT public_key FROM users WHERE id = ?");
        $stmt->execute(array($userId));
        $result = $stmt->fetch();
        return $result ? $result['public_key'] : null;
    }

    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, username FROM users");
        return $stmt->fetchAll();
    }

    public function getPrivateKeyEncrypted($userId) {
        $stmt = $this->db->prepare("SELECT private_key_encrypted FROM users WHERE id = ?");
        $stmt->execute(array($userId));
        $result = $stmt->fetch();
        return $result ? $result['private_key_encrypted'] : null;
    }

    private function encryptPrivateKey($privateKey, $password) {
        $key = hash('sha256', $password, true);
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($privateKey, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public function decryptPrivateKey($encryptedPrivateKey, $password) {
        $key = hash('sha256', $password, true);
        $data = base64_decode($encryptedPrivateKey);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    private function generatePrime($bits = 256) {
        do {
            $num = gmp_random_bits($bits);
            $num = gmp_setbit($num, 0); // Make odd
        } while (!gmp_prob_prime($num, 10));
        return gmp_strval($num);
    }
}
?>