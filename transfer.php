<?php
// transfer.php - Money Transfer
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$success = $error = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $recipient_phone = trim($_POST['recipient_phone']);
    $reference = trim($_POST['reference']);
 
    if ($amount > 0 && $amount <= $user['balance'] && preg_match('/^03\d{9}$/', $recipient_phone)) {
        // Check recipient exists
        $stmt = $pdo->prepare("SELECT id, phone FROM users WHERE phone = ?");
        $stmt->execute([$recipient_phone]);
        $recipient = $stmt->fetch();
        if ($recipient && $recipient['id'] != $user_id) {
            // Transfer
            $pdo->beginTransaction();
            try {
                // Sender
                $new_sender_balance = $user['balance'] - $amount;
                $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$new_sender_balance, $user_id]);
                // Recipient
                $new_rec_balance = $recipient['balance'] + $amount;
                $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$new_rec_balance, $recipient['id']]);
                // Log sender
                $pdo->prepare("INSERT INTO transactions (user_id, type, amount, recipient_phone, reference, status) VALUES (?, 'send', ?, ?, ?, 'completed')")->execute([$user_id, $amount, $recipient_phone, $reference]);
                // Log recipient
                $pdo->prepare("INSERT INTO transactions (user_id, type, amount, recipient_phone, reference, status) VALUES (?, 'receive', ?, ?, ?, 'completed')")->execute([$recipient['id'], $amount, $user['phone'] ?? '', $reference]);
                $pdo->commit();
                $success = "Transferred PKR $amount to $recipient_phone successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Transfer failed. Try again.';
            }
        } else {
            $error = 'Recipient phone not found or invalid.';
        }
    } else {
        $error = 'Invalid amount or insufficient balance.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Money - JazzCash</title>
    <style>
        /* Internal CSS - Form-focused */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        header { background: #00c853; color: white; padding: 15px; text-align: center; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
        button { background: #00c853; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; width: 100%; }
        .message { text-align: center; padding: 10px; margin: 10px 0; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .back { background: #666; margin-top: 10px; }
        @media (max-width: 480px) { .container { margin: 10px; padding: 15px; } }
    </style>
</head>
<body>
    <header>
        <h1>Send Money</h1>
        <a href="dashboard.php" style="color: white; float: left; margin-top: 5px;">Back</a>
    </header>
    <div class="container">
        <?php if ($success): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Recipient Phone (e.g., 03001234567)</label>
                <input type="tel" name="recipient_phone" pattern="^03\d{9}$" required>
            </div>
            <div class="form-group">
                <label>Amount (PKR)</label>
                <input type="number" name="amount" step="0.01" min="1" required>
            </div>
            <div class="form-group">
                <label>Reference (Optional)</label>
                <input type="text" name="reference">
            </div>
            <button type="submit">Send Money</button>
        </form>
        <button class="back" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>
    <script>
        // Internal JS - Phone validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const phone = document.querySelector('input[name="recipient_phone"]').value;
            if (!/^03\d{9}$/.test(phone)) {
                e.preventDefault();
                alert('Phone must start with 03 followed by 9 digits.');
            }
        });
    </script>
</body>
</html>
