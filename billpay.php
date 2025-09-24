<?php
// billpay.php - Bill Payment
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
 
// Hardcoded bills for simplicity
$bills = [
    'Electricity' => ['code' => 'ELEC', 'amount' => 1500],
    'Gas' => ['code' => 'GAS', 'amount' => 800],
    'Water' => ['code' => 'WATER', 'amount' => 500],
    'Internet' => ['code' => 'NET', 'amount' => 1200]
];
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bill_type = $_POST['bill_type'];
    $amount = floatval($_POST['amount']);
    $reference = trim($_POST['reference']);
 
    if (isset($bills[$bill_type]) && $amount > 0 && $amount <= $user['balance']) {
        $pdo->beginTransaction();
        try {
            $new_balance = $user['balance'] - $amount;
            $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$new_balance, $user_id]);
            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, reference, status) VALUES (?, 'withdraw', ?, ?, 'completed')")->execute([$user_id, $amount, $bill_type . ' Bill - ' . $reference]);
            $pdo->commit();
            $success = "Paid PKR $amount for $bill_type successfully!";
            $user['balance'] = $new_balance;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Payment failed.';
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
    <title>Pay Bill - JazzCash</title>
    <style>
        /* Internal CSS - Select-based form */
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
        <h1>Pay Bill</h1>
        <a href="dashboard.php" style="color: white; float: left; margin-top: 5px;">Back</a>
    </header>
    <div class="container">
        <?php if ($success): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Bill Type</label>
                <select name="bill_type" required>
                    <option value="">Select Bill</option>
                    <?php foreach ($bills as $type => $data): ?>
                    <option value="<?php echo $type; ?>"><?php echo $type; ?> (Suggested: PKR <?php echo $data['amount']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Amount (PKR)</label>
                <input type="number" name="amount" step="0.01" min="1" required>
            </div>
            <div class="form-group">
                <label>Reference/Bill No.</label>
                <input type="text" name="reference" required>
            </div>
            <button type="submit">Pay Bill</button>
        </form>
        <button class="back" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>
    <script>
        // Internal JS - Auto-fill amount
        document.querySelector('select[name="bill_type"]').addEventListener('change', function() {
            const selected = this.value;
            const amounts = <?php echo json_encode(array_column($bills, 'amount')); ?>;
            const index = Array.from(this.options).indexOf(this.options[this.selectedIndex]) - 1;
            if (index >= 0) document.querySelector('input[name="amount"]').value = amounts[index];
        });
    </script>
</body>
</html>
