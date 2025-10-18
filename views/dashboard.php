<?php
// Session already started in dashboard.php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../models/User.php';
$userModel = new User();
$users = $userModel->getAllUsers();
$currentUser = $_SESSION['username'];

// Check if user has RSA keys
$userKeys = $userModel->getPublicKey($_SESSION['user_id']);
$hasKeys = !empty($userKeys);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Crypto Chat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; height: 100vh; }
        .sidebar { width: 250px; background: #343a40; color: white; padding: 1rem; }
        .sidebar h2 { margin-top: 0; }
        .user-list { list-style: none; padding: 0; }
        .user-list li { padding: 0.5rem; cursor: pointer; border-bottom: 1px solid #495057; }
        .user-list li:hover { background: #495057; }
        .user-list li.selected { background: #007bff; }
        .chat-area { flex: 1; display: flex; flex-direction: column; }
        .chat-header { background: #007bff; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .messages { flex: 1; padding: 0.3rem; overflow-y: auto; background: #f8f9fa; max-height: 250px; }
        .message { margin-bottom: 0.3rem; padding: 0.3rem; border-radius: 6px; max-width: 70%; cursor: pointer; user-select: text; word-wrap: break-word; overflow-wrap: break-word; white-space: pre-wrap; font-size: 0.9em; }
        .message.sent { background: #007bff; color: white; margin-left: auto; }
        .message.received { background: white; border: 1px solid #ddd; }
        .message:hover { opacity: 0.8; }
        .message.selected { border: 2px solid #28a745; box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); }
        .message-input { display: flex; padding: 1rem; background: white; border-top: 1px solid #ddd; }
        .message-input input { flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .message-input button { padding: 0.5rem 1rem; background: #28a745; color: white; border: none; border-radius: 4px; margin-left: 0.5rem; cursor: pointer; }
        .logout { background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Welcome, <?php echo htmlspecialchars($currentUser); ?></h2>

        <!-- Key Generation Section -->
        <?php if (!$hasKeys): ?>
        <div id="keySection" style="margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; border: 2px solid #ffc107;">
            <h3 style="margin-top: 0; color: #856404;">⚠️ RSA Key Generation Required</h3>
            <p style="margin: 0.5rem 0; font-size: 0.9em; color: #856404;">You need to generate RSA keys to encrypt/decrypt messages.</p>
            <button id="showKeyFormBtn" style="width: 100%; padding: 0.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 0.5rem;">Choose Prime Numbers</button>
            <form id="keyForm" style="display: none;">
                <input type="password" id="keyPassword" placeholder="Your password" required style="width: 100%; margin-bottom: 0.5rem; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                <input type="text" id="primeP" placeholder="Prime P (ex: 100003)" value="100003" required style="width: 100%; margin-bottom: 0.5rem; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                <input type="text" id="primeQ" placeholder="Prime Q (ex: 200003)" value="200003" required style="width: 100%; margin-bottom: 0.5rem; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                <button type="submit" id="generateKeysBtn" style="width: 100%; padding: 0.5rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Generate RSA Keys</button>
            </form>
            <div id="keyStatus" style="margin-top: 0.5rem; font-size: 0.9em;"></div>
        </div>
        <?php else: ?>
        <div style="margin-bottom: 1rem; padding: 1rem; background: #d4edda; border-radius: 8px; border: 1px solid #c3e6cb;">
            <h3 style="margin-top: 0; color: #155724;">✅ RSA Keys Generated</h3>
            <p style="margin: 0; font-size: 0.9em; color: #155724;">Your RSA keys are ready for encryption/decryption.</p>
            <button id="showKeysBtn" style="margin-top: 0.5rem; padding: 0.5rem; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">Show My Keys</button>
            <button id="regenerateKeysBtn" style="margin-top: 0.5rem; padding: 0.5rem; background: #ffc107; color: black; border: none; border-radius: 4px; cursor: pointer; width: 100%;">🔄 Regenerate Keys</button>
            <div id="keysDisplay" style="display: none; margin-top: 0.5rem; padding: 0.5rem; background: white; border-radius: 4px; border: 1px solid #ddd;">
                <h4 style="margin-top: 0; color: #333;">Your RSA Keys</h4>
                <p><strong>Public Key:</strong></p>
                <textarea id="publicKeyDisplay" readonly style="width: 100%; height: 60px; font-family: monospace; font-size: 0.8em; margin-bottom: 0.5rem;"></textarea>
                <p><strong>Private Key:</strong></p>
                <textarea id="privateKeyDisplay" readonly style="width: 100%; height: 60px; font-family: monospace; font-size: 0.8em; margin-bottom: 0.5rem;"></textarea>
                <div style="display: flex; gap: 0.5rem;">
                    <button id="sharePublicKeyBtn" style="padding: 0.3rem 0.6rem; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 0.8em;">Copy Public Key</button>
                    <button id="sharePrivateKeyBtn" style="padding: 0.3rem 0.6rem; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 0.8em;">⚠️ Copy Private Key</button>
                </div>
            </div>
            <div id="regenerateSection" style="display: none; margin-top: 0.5rem; padding: 0.5rem; background: #fff3cd; border-radius: 4px; border: 1px solid #ffeaa7;">
                <h4 style="margin-top: 0; color: #856404;">Regenerate RSA Keys</h4>
                <form id="regenerateKeyForm">
                    <input type="password" id="regeneratePassword" placeholder="Your password" required style="width: 100%; margin-bottom: 0.5rem; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                    <input type="text" id="regeneratePrimeP" placeholder="Prime P (ex: 100003)" value="100003" required style="width: 100%; margin-bottom: 0.5rem; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                    <input type="text" id="regeneratePrimeQ" placeholder="Prime Q (ex: 200003)" value="200003" required style="width: 100%; margin-bottom: 0.5rem; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                    <button type="submit" id="regenerateKeysSubmitBtn" style="width: 100%; padding: 0.5rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Generate New Keys</button>
                </form>
                <div id="regenerateStatus" style="margin-top: 0.5rem; font-size: 0.9em;"></div>
            </div>
        </div>
        <?php endif; ?>

        <ul class="user-list" id="userList">
            <?php foreach ($users as $user): ?>
                <?php if ($user['username'] !== $currentUser): ?>
                    <li data-user-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="chat-area">
        <div class="chat-header">
            <h3 id="chatTitle">Select a user to start chatting</h3>
            <form action="logout.php" method="post" style="display: inline;">
                <button type="submit" class="logout">Logout</button>
            </form>
        </div>
        <div class="messages" id="messages"></div>
        <div class="message-input">
            <input type="text" id="messageInput" placeholder="Type your message..." disabled>
            <button id="encryptButton" disabled title="Encrypt message with recipient's public key">Encrypt</button>
            <button id="decryptButton" disabled title="Decrypt message with your private key">Decrypt</button>
            <button id="sendButton" disabled>Send</button>
        </div>
        <div id="statusMessage" style="padding: 0.5rem; color: #666; font-size: 0.9em;"></div>
    </div>

    <script>
        let currentChatUserId = null;
        let currentChatUsername = null;

        const userList = document.getElementById('userList');
        if (userList) {
            userList.addEventListener('click', function(e) {
                if (e.target.tagName === 'LI') {
                    // Remove selected class from all users
                    document.querySelectorAll('#userList li').forEach(li => li.classList.remove('selected'));
                    // Add selected class to clicked user
                    e.target.classList.add('selected');

                    currentChatUserId = e.target.dataset.userId;
                    currentChatUsername = e.target.dataset.username;
                    const chatTitle = document.getElementById('chatTitle');
                    if (chatTitle) chatTitle.textContent = 'Chat with ' + currentChatUsername;

                    const messageInput = document.getElementById('messageInput');
                    const encryptButton = document.getElementById('encryptButton');
                    const decryptButton = document.getElementById('decryptButton');
                    const sendButton = document.getElementById('sendButton');

                    if (messageInput) messageInput.disabled = false;
                    if (encryptButton) encryptButton.disabled = <?php echo $hasKeys ? 'false' : 'true'; ?>;
                    if (decryptButton) decryptButton.disabled = <?php echo $hasKeys ? 'false' : 'true'; ?>;
                    if (sendButton) sendButton.disabled = false;
                    loadMessages();
                }
            });
        }

        // Check if elements exist before adding event listeners
        const sendButton = document.getElementById('sendButton');
        const encryptButton = document.getElementById('encryptButton');
        const decryptButton = document.getElementById('decryptButton');
        const showKeyFormBtn = document.getElementById('showKeyFormBtn');

        if (sendButton) sendButton.addEventListener('click', sendMessage);
        if (encryptButton) encryptButton.addEventListener('click', encryptMessage);
        if (decryptButton) decryptButton.addEventListener('click', decryptMessage);
        if (showKeyFormBtn) showKeyFormBtn.addEventListener('click', function() {
            const keyForm = document.getElementById('keyForm');
            if (keyForm) {
                keyForm.style.display = 'block';
                showKeyFormBtn.style.display = 'none';
            }
        });

        const keyForm = document.getElementById('keyForm');
        if (keyForm) {
            keyForm.addEventListener('submit', generateKeys);
        }

        <?php if ($hasKeys): ?>
        const showKeysBtn = document.getElementById('showKeysBtn');
        if (showKeysBtn) {
            showKeysBtn.addEventListener('click', function() {
                const keysDisplay = document.getElementById('keysDisplay');
                if (keysDisplay) {
                    if (keysDisplay.style.display === 'none') {
                        // Load and display keys
                        fetch('get_my_keys.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const publicKeyDisplay = document.getElementById('publicKeyDisplay');
                                const privateKeyDisplay = document.getElementById('privateKeyDisplay');
                                if (publicKeyDisplay) publicKeyDisplay.value = JSON.stringify(data.publicKey, null, 2);
                                if (privateKeyDisplay) privateKeyDisplay.value = JSON.stringify(data.privateKey, null, 2);
                                keysDisplay.style.display = 'block';
                            }
                        });
                    } else {
                        keysDisplay.style.display = 'none';
                    }
                }
            });
        }

        const sharePublicKeyBtn = document.getElementById('sharePublicKeyBtn');
        if (sharePublicKeyBtn) {
            sharePublicKeyBtn.addEventListener('click', function() {
                const publicKeyDisplay = document.getElementById('publicKeyDisplay');
                if (publicKeyDisplay) {
                    navigator.clipboard.writeText(publicKeyDisplay.value).then(function() {
                        alert('Public key copied to clipboard! Share it with your contacts.');
                    });
                }
            });
        }

        const sharePrivateKeyBtn = document.getElementById('sharePrivateKeyBtn');
        if (sharePrivateKeyBtn) {
            sharePrivateKeyBtn.addEventListener('click', function() {
                if (confirm('WARNING: Never share your private key with anyone! This is for demonstration purposes only. Continue?')) {
                    const privateKeyDisplay = document.getElementById('privateKeyDisplay');
                    if (privateKeyDisplay) {
                        navigator.clipboard.writeText(privateKeyDisplay.value).then(function() {
                            alert('Private key copied to clipboard. Remember: NEVER share this with anyone!');
                        });
                    }
                }
            });
        }

        const regenerateKeysBtn = document.getElementById('regenerateKeysBtn');
        if (regenerateKeysBtn) {
            regenerateKeysBtn.addEventListener('click', function() {
                const regenerateSection = document.getElementById('regenerateSection');
                if (regenerateSection) {
                    regenerateSection.style.display = regenerateSection.style.display === 'none' ? 'block' : 'none';
                }
            });
        }

        const regenerateKeyForm = document.getElementById('regenerateKeyForm');
        if (regenerateKeyForm) {
            regenerateKeyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                regenerateKeys();
            });
        }
        <?php endif; ?>
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') sendMessage();
            });
        }

        function sendMessage() {
            const message = document.getElementById('messageInput').value.trim();
            if (message && currentChatUserId) {
                const statusDiv = document.getElementById('statusMessage');
                statusDiv.textContent = 'Sending message...';
                statusDiv.style.color = '#666';

                fetch('send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `receiver_id=${currentChatUserId}&message=${encodeURIComponent(message)}&encrypted=false`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('messageInput').value = '';
                        statusDiv.textContent = 'Message sent successfully!';
                        statusDiv.style.color = '#28a745';
                        setTimeout(() => { statusDiv.textContent = ''; }, 3000);
                        loadMessages();
                    } else {
                        statusDiv.textContent = 'Failed to send message: ' + (data.error || 'Unknown error');
                        statusDiv.style.color = '#dc3545';
                        setTimeout(() => { statusDiv.textContent = ''; }, 5000);
                    }
                })
                .catch(error => {
                    statusDiv.textContent = 'Send error: ' + error.message;
                    statusDiv.style.color = '#dc3545';
                    setTimeout(() => { statusDiv.textContent = ''; }, 5000);
                });
            }
        }

        function encryptMessage() {
            const message = document.getElementById('messageInput').value.trim();
            if (message && currentChatUserId) {
                const statusDiv = document.getElementById('statusMessage');
                statusDiv.textContent = 'Encrypting message with recipient\'s public key...';
                statusDiv.style.color = '#666';

                // Encrypt with receiver's public key
                fetch('encrypt_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `receiver_id=${currentChatUserId}&message=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('messageInput').value = data.encrypted;
                        statusDiv.textContent = 'Message encrypted! Now you can send it securely.';
                        statusDiv.style.color = '#28a745';
                        setTimeout(() => { statusDiv.textContent = ''; }, 3000);
                    } else {
                        statusDiv.textContent = 'Encryption failed: ' + (data.error || 'Unknown error');
                        statusDiv.style.color = '#dc3545';
                        setTimeout(() => { statusDiv.textContent = ''; }, 5000);
                    }
                })
                .catch(error => {
                    statusDiv.textContent = 'Encryption error: ' + error.message;
                    statusDiv.style.color = '#dc3545';
                    setTimeout(() => { statusDiv.textContent = ''; }, 5000);
                });
            } else if (!currentChatUserId) {
                alert('Please select a contact first!');
            }
        }

        function decryptMessage() {
            const message = document.getElementById('messageInput').value.trim();
            if (message) {
                const statusDiv = document.getElementById('statusMessage');
                statusDiv.textContent = 'Decrypting message with your private key...';
                statusDiv.style.color = '#666';

                // Decrypt with user's private key
                fetch('decrypt_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `message=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('messageInput').value = data.decrypted;
                        statusDiv.textContent = 'Message decrypted! You can now read it.';
                        statusDiv.style.color = '#28a745';
                        setTimeout(() => { statusDiv.textContent = ''; }, 3000);
                    } else {
                        statusDiv.textContent = 'Decryption failed: ' + (data.error || 'Unknown error');
                        statusDiv.style.color = '#dc3545';
                        setTimeout(() => { statusDiv.textContent = ''; }, 5000);
                    }
                })
                .catch(error => {
                    statusDiv.textContent = 'Decryption error: ' + error.message;
                    statusDiv.style.color = '#dc3545';
                    setTimeout(() => { statusDiv.textContent = ''; }, 5000);
                });
            }
        }

        function generateKeys(e) {
            e.preventDefault();
            const password = document.getElementById('keyPassword').value;
            const primeP = document.getElementById('primeP').value.trim();
            const primeQ = document.getElementById('primeQ').value.trim();
            const statusDiv = document.getElementById('keyStatus');

            if (!password) {
                statusDiv.textContent = 'Password is required';
                statusDiv.style.color = '#dc3545';
                return;
            }

            statusDiv.textContent = 'Generating RSA keys...';
            statusDiv.style.color = '#666';

            let body = `password=${encodeURIComponent(password)}`;
            if (primeP) body += `&prime_p=${encodeURIComponent(primeP)}`;
            if (primeQ) body += `&prime_q=${encodeURIComponent(primeQ)}`;

            fetch('generate_keys.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.textContent = 'RSA keys generated successfully!';
                    statusDiv.style.color = '#28a745';
                    location.reload(); // Reload page to update UI
                } else {
                    statusDiv.textContent = 'Key generation failed: ' + (data.error || 'Unknown error');
                    statusDiv.style.color = '#dc3545';
                }
            })
            .catch(error => {
                statusDiv.textContent = 'Key generation error: ' + error.message;
                statusDiv.style.color = '#dc3545';
            });
        }

        function regenerateKeys() {
            const password = document.getElementById('regeneratePassword').value;
            const primeP = document.getElementById('regeneratePrimeP').value.trim();
            const primeQ = document.getElementById('regeneratePrimeQ').value.trim();
            const statusDiv = document.getElementById('regenerateStatus');

            if (!password) {
                statusDiv.textContent = 'Password is required';
                statusDiv.style.color = '#dc3545';
                return;
            }

            statusDiv.textContent = 'Regenerating RSA keys...';
            statusDiv.style.color = '#666';

            let body = `password=${encodeURIComponent(password)}`;
            if (primeP) body += `&prime_p=${encodeURIComponent(primeP)}`;
            if (primeQ) body += `&prime_q=${encodeURIComponent(primeQ)}`;

            fetch('generate_keys.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.textContent = 'RSA keys regenerated successfully!';
                    statusDiv.style.color = '#28a745';
                    setTimeout(() => location.reload(), 2000); // Reload page after 2 seconds
                } else {
                    statusDiv.textContent = 'Key regeneration failed: ' + (data.error || 'Unknown error');
                    statusDiv.style.color = '#dc3545';
                }
            })
            .catch(error => {
                statusDiv.textContent = 'Key regeneration error: ' + error.message;
                statusDiv.style.color = '#dc3545';
            });
        }

        function loadMessages() {
            if (currentChatUserId) {
                fetch(`get_messages.php?other_user_id=${currentChatUserId}`)
                .then(response => response.json())
                .then(messages => {
                    console.log('Loaded messages:', messages);
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = '';
                    messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'message ' + (msg.sender_id == <?php echo $_SESSION['user_id']; ?> ? 'sent' : 'received');
                        messageDiv.textContent = msg.decrypted_message || msg.encrypted_message;
                        messageDiv.title = `From: ${msg.sender_username}, Time: ${msg.sent_at}`;

                        // Add click handler for message selection
                        messageDiv.addEventListener('click', function() {
                            // Remove selected class from all messages
                            document.querySelectorAll('.message').forEach(m => m.classList.remove('selected'));
                            // Add selected class to clicked message
                            this.classList.add('selected');

                            // Copy message content to input field
                            const messageInput = document.getElementById('messageInput');
                            if (messageInput) {
                                messageInput.value = this.textContent;
                                messageInput.focus();
                                messageInput.select();
                            }
                        });

                        // Add double-click handler for instant copy
                        messageDiv.addEventListener('dblclick', function() {
                            navigator.clipboard.writeText(this.textContent).then(function() {
                                // Visual feedback
                                const originalClass = messageDiv.className;
                                messageDiv.className += ' selected';
                                setTimeout(() => {
                                    messageDiv.className = originalClass;
                                }, 200);
                            }).catch(function(err) {
                                console.error('Failed to copy: ', err);
                            });
                        });

                        messagesDiv.appendChild(messageDiv);
                    });
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                });
            }
        }

        // Auto-refresh messages every 5 seconds
        setInterval(loadMessages, 5000);
    </script>
</body>
</html>