<?php
// index.php - Homepage
include 'db.php';
 
$message = '';
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard if logged in
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quick_action'])) {
    // Handle quick actions like demo login (for testing)
    if ($_POST['quick_action'] == 'demo') {
        $_SESSION['temp_email'] = 'test@example.com';
        echo "<script>window.location.href = 'login.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JazzCash - Digital Wallet & Payments</title>
    <style>
        /* Internal CSS - Modern, vibrant, JazzCash-inspired design */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #00c853; color: white; padding: 15px 0; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        header h1 { font-size: 2.5em; margin-bottom: 5px; }
        nav { display: flex; justify-content: center; gap: 20px; margin-top: 10px; }
        nav a { color: white; text-decoration: none; padding: 10px 20px; border-radius: 25px; transition: background 0.3s; }
        nav a:hover { background: rgba(255,255,255,0.2); }
        .hero { text-align: center; padding: 60px 20px; color: white; }
        .hero h2 { font-size: 3em; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .hero p { font-size: 1.2em; margin-bottom: 30px; }
        .cta-btn { background: #ffeb3b; color: #333; padding: 15px 30px; border: none; border-radius: 50px; font-size: 1.1em; cursor: pointer; transition: transform 0.3s, box-shadow 0.3s; text-decoration: none; display: inline-block; }
        .cta-btn:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .services { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; padding: 50px 0; }
        .service-card { background: white; border-radius: 15px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .service-card:hover { transform: translateY(-10px); }
        .service-card h3 { color: #00c853; font-size: 1.5em; margin-bottom: 10px; }
        .service-card p { color: #666; }
        .service-card .icon { font-size: 3em; margin-bottom: 15px; }
        .quick-actions { background: white; border-radius: 15px; padding: 40px; text-align: center; margin: 50px 0; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .quick-actions form { display: inline-block; margin: 10px; }
        .quick-actions input[type="submit"] { background: #00c853; color: white; border: none; padding: 12px 25px; border-radius: 25px; cursor: pointer; font-size: 1em; }
        footer { background: #333; color: white; text-align: center; padding: 20px; margin-top: 50px; }
        @media (max-width: 768px) { .hero h2 { font-size: 2em; } nav { flex-direction: column; } .services { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <h1>🎉 JazzCash</h1>
        <p>Your Trusted Digital Wallet</p>
        <nav>
            <a href="#services">Services</a>
            <a href="#about">About</a>
            <a href="signup.php">Sign Up</a>
            <a href="login.php">Login</a>
        </nav>
    </header>
    <div class="container">
        <section class="hero">
            <h2>Send Money, Pay Bills & More!</h2>
            <p>Fast, Secure & Convenient Digital Payments</p>
            <a href="signup.php" class="cta-btn">Get Started Now</a>
        </section>
        <section id="services" class="services">
            <div class="service-card">
                <div class="icon">💰</div>
                <h3>Money Transfer</h3>
                <p>Send & receive money instantly via phone or QR.</p>
            </div>
            <div class="service-card">
                <div class="icon">📱</div>
                <h3>Mobile Recharge</h3>
                <p>Top-up your mobile balance in seconds.</p>
            </div>
            <div class="service-card">
                <div class="icon">💡</div>
                <h3>Bill Payments</h3>
                <p>Pay electricity, gas, water & more easily.</p>
            </div>
            <div class="service-card">
                <div class="icon">🔒</div>
                <h3>Secure Wallet</h3>
                <p>Manage your funds with top-notch security.</p>
            </div>
        </section>
        <section class="quick-actions">
            <h3>Quick Demo Access</h3>
            <form method="POST">
                <input type="hidden" name="quick_action" value="demo">
                <input type="submit" value="Try Demo Login">
            </form>
        </section>
    </div>
    <footer>
        <p>&copy; 2025 JazzCash. All rights reserved. | Secure & Fast Payments</p>
    </footer>
    <script>
        // Internal JS - Simple animations
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-10px)');
            card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
        });
    </script>
</body>
</html>
