<?php
// history.php - Transaction History
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Fetch transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - JazzCash</title>
    <style>
        /* Internal CSS - Table-based history */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        header { background: #00c853; color: white; padding: 15px; text-align: center; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .type-send { color: #e74c3c; }
        .type-receive { color: #27ae60; }
        .type-deposit { color: #27ae60; }
        .type-withdraw { color: #e74c3c; }
        .back { background: #00c853; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; margin-top: 20px; display: block; width: 100%; }
    </style>
</head>
<body>
    <header>
        <h1>Transaction History</h1>
        <a href="dashboard.php" style="color: white; float: left; margin-top: 5px;">Back</a>
    </header>
    <div class="container">
        <?php if (empty($transactions)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">No transactions yet.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Reference/Phone</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td><?php echo date('Y-m-d H:i', strtotime($tx['created_at'])); ?></td>
                    <td class="type-<?php echo $tx['type']; ?>"><?php echo ucfirst($tx['type']); ?></td>
                    <td>PKR <?php echo number_format($tx['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($tx['reference'] ?? $tx['recipient_phone'] ?? 'N/A'); ?></td>
                    <td><?php echo ucfirst($tx['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <button class="back" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>
    <script>
        // Internal JS - Filter (simple, client-side)
        // Omitted for brevity, can add search input if needed
    </script>
</body>
</html>
