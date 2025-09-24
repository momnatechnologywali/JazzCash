<?php
// settings.php - Account Management
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$success = $error = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'reset_pin') {
        $new_pin = $_POST['new_pin'];
        if (strlen($new_pin) == 4 && ctype_digit($new_pin)) {
            $stmt = $pdo->prepare("UPDATE users SET pin = ? WHERE id = ?");
            if ($stmt->execute([$new_pin, $user_id])) {
                $success = 'PIN reset successfully!';
            } else {
                $error = 'Failed to reset PIN.';
            }
        } else {
            $error = 'PIN must be 4 digits.';
        }
    }
    // Add more actions like update profile if needed
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - JazzCash</title>
    <style>
        /* Internal CSS - Settings form */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        header { background: #00c853; color: white; padding: 15px; text-align: center; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .info { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
        button { background: #00c853; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; width: 100%; margin-bottom: 10px; }
        .message { text-align: center; padding: 10px; margin: 10px 0; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .back { background: #666; }
    </style>
</head>
<body>
    <header>
        <h1>Account Settings</h1>
        <a href="dashboard.php" style="color: white; float: left; margin-top: 5px;">Back</a>
    </header>
    <div class="container">
        <?php if ($success): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>
        <div class="info">
            <h3>Account Info</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="reset_pin">
            <div class="form-group">
                <label>New PIN (4 digits)</label>
                <input type="password" name="new_pin" maxlength="4" pattern="\d{4}" required>
            </div>
            <button type="submit">Reset PIN</button>
        </form>
        <button class="back" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>
    <script>
        // Internal JS - PIN validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const pin = document.querySelector('input[name="new_pin"]').value;
            if (pin.length !== 4 || !/^\d+$/.test(pin)) {
                e.preventDefault();
                alert('PIN must be 4 digits.');
            }
        });
    </script>
</body>
</html>
