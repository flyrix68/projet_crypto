<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Crypto Chat</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        h1 { text-align: center; color: #333; }
        form { display: flex; flex-direction: column; }
        input { margin: 0.5rem 0; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        button { margin: 0.5rem 0; padding: 0.5rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        .error { color: red; text-align: center; margin-bottom: 1rem; }
        .link { text-align: center; margin-top: 1rem; }
        .link a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form action="register.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <p style="font-size: 0.9em; color: #666; margin-top: 1rem;">
            Note: RSA keys will be generated in the dashboard after registration.
        </p>
        <div class="link">
            <a href="login.php">Already have an account? Login</a>
        </div>
    </div>
</body>
</html>