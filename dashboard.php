<?php
// dashboard.php - Main Wallet Dashboard
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    session_destroy();
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
 
// Handle deposit/withdraw
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        if ($_POST['action'] == 'deposit') {
            $new_balance = $user['balance'] + $amount;
            $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$new_balance, $user_id]);
            // Log transaction
            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, reference) VALUES (?, 'deposit', ?, 'Manual Deposit')")->execute([$user_id, $amount]);
            $user['balance'] = $new_balance;
            $message = "Deposited PKR $amount successfully!";
        } elseif ($_POST['action'] == 'withdraw' && $amount <= $user['balance']) {
            $new_balance = $user['balance'] - $amount;
            $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$new_balance, $user_id]);
            // Log
            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, reference) VALUES (?, 'withdraw', ?, 'Manual Withdraw')")->execute([$user_id, $amount]);
            $user['balance'] = $new_balance;
            $message = "Withdrew PKR $amount successfully!";
        } else {
            $error = "Invalid amount or insufficient balance.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JazzCash</title>
    <style>
        /* Internal CSS - Dashboard style, vibrant and card-based */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; }
        header { background: #00c853; color: white; padding: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .balance-card { background: linear-gradient(135deg, #00c853, #00b140); color: white; text-align: center; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,200,83,0.3); }
        .balance-card h2 { font-size: 2.5em; margin-bottom: 10px; }
        .balance-card p { font-size: 1.2em; }
        .actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .action-btn { background: white; padding: 20px; border-radius: 15px; text-align: center; text-decoration: none; color: #333; font-weight: bold; transition: transform 0.3s, box-shadow 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .action-btn:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .action-btn .icon { font-size: 2em; display: block; margin-bottom: 10px; }
        form { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        button { background: #00c853; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; width: 100%; }
        .message { text-align: center; padding: 10px; margin: 10px 0; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .logout { position: absolute; top: 15px; right: 20px; color: white; text-decoration: none; }
        @media (max-width: 768px) { .actions { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <a href="logout.php" class="logout">Logout</a>
        <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
    </header>
    <div class="container">
        <?php if (isset($message)): ?><div class="message success"><?php echo $message; ?></div><?php endif; ?>
        <?php if (isset($error)): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>
        <div class="balance-card">
            <h2>PKR <?php echo number_format($user['balance'], 2); ?></h2>
            <p>Your Wallet Balance</p>
        </div>
        <div class="actions">
            <a href="transfer.php" class="action-btn"><span class="icon">💸</span>Send Money</a>
            <a href="billpay.php" class="action-btn"><span class="icon">💡</span>Pay Bill</a>
            <a href="recharge.php" class="action-btn"><span class="icon">📱</span>Mobile Recharge</a>
            <a href="history.php" class="action-btn"><span class="icon">📊</span>Transaction History</a>
            <a href="settings.php" class="action-btn"><span class="icon">⚙️</span>Settings</a>
        </div>
        <form method="POST">
            <h3>Quick Deposit/Withdraw</h3>
            <div class="form-group">
                <label>Amount (PKR)</label>
                <input type="number" name="amount" step="0.01" min="1" required>
            </div>
            <button type="submit" name="action" value="deposit">Deposit</button>
            <button type="submit" name="action" value="withdraw" style="background: #ff9800; margin-left: 10px;">Withdraw</button>
        </form>
    </div>
    <script>
        // Internal JS - Balance animation
        const balance = document.querySelector('.balance-card h2');
        balance.style.transition = 'all 0.5s ease';
        balance.addEventListener('mouseenter', () => balance.style.transform = 'scale(1.05)');
        balance.addEventListener('mouseleave', () => balance.style.transform = 'scale(1)');
    </script>
</body>
</html>
