<?php
// Session will be started by the calling script
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $userModel;
    private $db;

    public function __construct() {
        $this->userModel = new User();
        $this->db = Database::getInstance()->getConnection();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $user = $this->userModel->login($username, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['public_key'] = $user['public_key'];
                $_SESSION['private_key'] = $user['private_key'];
                // Debug: check if session is set
                error_log("Login successful for user: " . $username . ", session user_id: " . $_SESSION['user_id']);
                // Force redirect with full URL
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $redirectUrl = $protocol . '://' . $host . '/projet_crypto/dashboard.php';
                error_log("Redirecting to: " . $redirectUrl);

                // Force flush any buffered output
                if (ob_get_level()) {
                    ob_end_clean();
                }

                // Send redirect header
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $error = "Invalid username or password";
                error_log("Login failed for user: " . $username);
                include __DIR__ . '/../views/login.php';
            }
        } else {
            include __DIR__ . '/../views/login.php';
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $result = $this->userModel->register($username, $password);
            if ($result === true) {
                header('Location: login.php?success=1');
                exit;
            } elseif ($result === false) {
                $error = "Username already exists";
                include __DIR__ . '/../views/register.php';
            } elseif ($result === null) {
                $error = "Registration failed due to database error";
                include __DIR__ . '/../views/register.php';
            }
        } else {
            include __DIR__ . '/../views/register.php';
        }
    }

    public function generateKeys() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $password = $_POST['password'];
            $p = isset($_POST['prime_p']) ? $_POST['prime_p'] : null;
            $q = isset($_POST['prime_q']) ? $_POST['prime_q'] : null;

            // Store password in session for later use
            $_SESSION['password'] = $password;

            // Verify password first
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute(array($_SESSION['user_id']));
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $result = $this->userModel->generateUserKeys($_SESSION['user_id'], $password, $p, $q);
                if ($result) {
                    // Update session with new keys
                    $privateKeyEncrypted = $this->userModel->getPrivateKeyEncrypted($_SESSION['user_id']);
                    $privateKey = $this->userModel->decryptPrivateKey($privateKeyEncrypted, $password);
                    $publicKey = $this->userModel->getPublicKey($_SESSION['user_id']);
                    $_SESSION['private_key'] = $privateKey;
                    $_SESSION['public_key'] = $publicKey;

                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Key generation failed']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid password']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        }
        exit;
    }

    private function getPrivateKey($userId, $password) {
        $stmt = $this->db->prepare("SELECT private_key_encrypted FROM users WHERE id = ?");
        $stmt->execute(array($userId));
        $result = $stmt->fetch();
        if ($result) {
            return $this->userModel->decryptPrivateKey($result['private_key_encrypted'], $password);
        }
        return null;
    }

    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        return array(
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'public_key' => $_SESSION['public_key'],
            'private_key' => $_SESSION['private_key']
        );
    }
}
?>