<?php
// login.php
include 'db.php';
 
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}
 
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == 'pin') {
        // Verify PIN
        $pin = $_POST['pin'];
        $email = $_SESSION['temp_email'] ?? '';
        $stmt = $pdo->prepare("SELECT id, pin FROM users WHERE email = ? AND is_active = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && $user['pin'] === $pin) {
            $_SESSION['user_id'] = $user['id'];
            unset($_SESSION['temp_email']);
            echo "<script>window.location.href = 'dashboard.php';</script>";
            exit;
        } else {
            $error = 'Invalid PIN.';
        }
    } else {
        // First step: email/password
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ? AND is_active = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['temp_email'] = $email;
            // Show PIN form (via JS redirect or inline)
            $show_pin = true;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
 
$show_pin = isset($show_pin);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JazzCash</title>
    <style>
        /* Internal CSS - Similar to signup */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .form-container { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 30px; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
        input:focus { outline: none; border-color: #00c853; }
        button { width: 100%; padding: 12px; background: #00c853; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #00b140; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 15px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #667eea; text-decoration: none; }
        .pin-form { display: <?php echo $show_pin ? 'block' : 'none'; ?>; }
        @media (max-width: 480px) { .form-container { margin: 20px; padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
 
        <!-- Email/Password Form -->
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
 
        <!-- PIN Form -->
        <form method="POST" id="pinForm" class="pin-form">
            <input type="hidden" name="step" value="pin">
            <div class="form-group">
                <label>Enter your 4-digit PIN (2FA)</label>
                <input type="password" name="pin" maxlength="4" pattern="\d{4}" required>
            </div>
            <button type="submit">Verify PIN</button>
        </form>
 
        <div class="back-link">
            <a href="index.php">Back to Home</a> | <a href="signup.php">Sign Up</a>
        </div>
    </div>
    <script>
        // Internal JS - Handle form switching
        document.getElementById('loginForm').addEventListener('submit', function() {
            // Simulate show pin after submit (server handles)
        });
        <?php if ($show_pin): ?>
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('pinForm').style.display = 'block';
        <?php endif; ?>
 
        // PIN validation
        document.getElementById('pinForm').addEventListener('submit', function(e) {
            const pin = document.querySelector('input[name="pin"]').value;
            if (pin.length !== 4 || !/^\d+$/.test(pin)) {
                e.preventDefault();
                alert('PIN must be exactly 4 digits.');
            }
        });
    </script>
</body>
</html>
