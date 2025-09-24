<?php
// signup.php
include 'db.php';
 
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}
 
$error = '';
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $pin = $_POST['pin'];
 
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($pin) || strlen($pin) != 4) {
        $error = 'All fields are required. PIN must be 4 digits.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email or phone exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            $error = 'Email or phone already registered.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, pin) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$full_name, $email, $phone, $hashed_password, $pin])) {
                $success = 'Account created successfully! Please login.';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Signup failed. Try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - JazzCash</title>
    <style>
        /* Internal CSS - Clean signup form */
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
        .success { color: #27ae60; text-align: center; margin-bottom: 15px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #667eea; text-decoration: none; }
        @media (max-width: 480px) { .form-container { margin: 20px; padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Sign Up</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>PIN (4 digits for 2FA)</label>
                <input type="password" name="pin" maxlength="4" pattern="\d{4}" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <div class="back-link">
            <a href="index.php">Back to Home</a>
        </div>
    </div>
    <script>
        // Internal JS - Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const pin = document.querySelector('input[name="pin"]').value;
            if (pin.length !== 4 || !/^\d+$/.test(pin)) {
                e.preventDefault();
                alert('PIN must be exactly 4 digits.');
            }
        });
    </script>
</body>
</html>
