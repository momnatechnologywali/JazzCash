<?php
// recharge.php - Mobile Recharge
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
 
// Hardcoded providers/packages
$providers = [
    'Jazz' => [100 => 'PKR 100', 300 => 'PKR 300', 500 => 'PKR 500'],
    'Zong' => [150 => 'PKR 150', 400 => 'PKR 400'],
    'Ufone' => [200 => 'PKR 200', 600 => 'PKR 600']
];
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $provider = $_POST['provider'];
    $amount = floatval($_POST['amount']);
    $phone = trim($_POST['phone']);
 
    if (isset($providers[$provider]) && $amount > 0 && isset($providers[$provider][$amount]) && $amount <= $user['balance'] && preg_match('/^03\d{9}$/', $phone)) {
        $pdo->beginTransaction();
        try {
            $new_balance = $user['balance'] - $amount;
            $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$new_balance, $user_id]);
            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, recipient_phone, reference, status) VALUES (?, 'withdraw', ?, ?, ?, 'completed')")->execute([$user_id, $amount, $phone, $provider . ' Recharge']);
            $pdo->commit();
            $success = "Recharged PKR $amount to $phone ($provider) successfully!";
            $user['balance'] = $new_balance;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Recharge failed.';
        }
    } else {
        $error = 'Invalid selection or insufficient balance.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Recharge - JazzCash</title>
    <style>
        /* Internal CSS - Similar to billpay */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        header { background: #00c853; color: white; padding: 15px; text-align: center; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        select, input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px; }
        button { background: #00c853; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; width: 100%; }
        .message { text-align: center; padding: 10px; margin: 10px 0; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .back { background: #666; margin-top: 10px; }
    </style>
</head>
<body>
    <header>
        <h1>Mobile Recharge</h1>
        <a href="dashboard.php" style="color: white; float: left; margin-top: 5px;">Back</a>
    </header>
    <div class="container">
        <?php if ($success): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Provider</label>
                <select name="provider" id="provider" required>
                    <option value="">Select Provider</option>
                    <?php foreach ($providers as $prov => $packs): ?>
                    <option value="<?php echo $prov; ?>"><?php echo $prov; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" pattern="^03\d{9}$" required>
            </div>
            <div class="form-group">
                <label>Amount</label>
                <select name="amount" id="amount" required>
                    <option value="">Select Amount</option>
                </select>
            </div>
            <button type="submit">Recharge</button>
        </form>
        <button class="back" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>
    <script>
        // Internal JS - Dynamic amount select
        const providers = <?php echo json_encode($providers); ?>;
        document.getElementById('provider').addEventListener('change', function() {
            const prov = this.value;
            const amountSelect = document.getElementById('amount');
            amountSelect.innerHTML = '<option value="">Select Amount</option>';
            if (prov && providers[prov]) {
                Object.keys(providers[prov]).forEach(key => {
                    const opt = document.createElement('option');
                    opt.value = key;
                    opt.textContent = providers[prov][key];
                    amountSelect.appendChild(opt);
                });
            }
        });
    </script>
</body>
</html>
