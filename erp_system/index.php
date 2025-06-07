<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP System Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
            animation: fadeInDown 0.8s ease-out;
        }

        h1 {
            font-size: 3.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #00ADB5, #0891b2, #00d4dd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            text-shadow: 0 4px 20px rgba(0, 173, 181, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 20px rgba(0, 173, 181, 0.5)); }
            to { filter: drop-shadow(0 0 30px rgba(0, 173, 181, 0.8)); }
        }

        .subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 300;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1200px;
            width: 100%;
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .nav-card {
            background: rgba(15, 15, 35, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .nav-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 173, 181, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .nav-card:hover:before {
            left: 100%;
        }

        .nav-card:hover {
            transform: translateY(-10px);
            border-color: rgba(0, 173, 181, 0.4);
            box-shadow: 0 25px 50px rgba(0, 173, 181, 0.2);
        }

        .nav-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: linear-gradient(45deg, #00ADB5, #0891b2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            transition: all 0.3s ease;
        }

        .nav-card:hover .nav-icon {
            transform: scale(1.1) rotate(360deg);
            box-shadow: 0 10px 30px rgba(0, 173, 181, 0.4);
        }

        .nav-card h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: #ffffff;
            font-weight: 600;
        }

        .nav-card p {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .nav-link {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(45deg, #00ADB5, #0891b2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 173, 181, 0.4);
        }

        .logout-section {
            margin-top: 30px;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .logout-card {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 15px;
            padding: 20px 40px;
            text-align: center;
        }

        .logout-button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(0, 173, 181, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .shape:nth-child(1) { 
            width: 100px; 
            height: 100px; 
            top: 10%; 
            left: 10%; 
            animation-delay: 0s; 
        }
        .shape:nth-child(2) { 
            width: 150px; 
            height: 150px; 
            top: 70%; 
            left: 80%; 
            animation-delay: 2s; 
        }
        .shape:nth-child(3) { 
            width: 80px; 
            height: 80px; 
            top: 40%; 
            left: 5%; 
            animation-delay: 4s; 
        }
        .shape:nth-child(4) { 
            width: 120px; 
            height: 120px; 
            top: 20%; 
            left: 85%; 
            animation-delay: 6s; 
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg); 
                opacity: 0.3;
            }
            50% { 
                transform: translateY(-30px) rotate(180deg); 
                opacity: 0.6;
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 0 10px;
            }
            
            .nav-card {
                padding: 25px 20px;
            }
            
            .container {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 2rem;
            }
            
            .nav-card {
                padding: 20px 15px;
            }
            
            .nav-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
        }

        /* Add pulse animation for important cards */
        .nav-card:nth-child(3) {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3); }
            50% { box-shadow: 0 15px 35px rgba(0, 173, 181, 0.2); }
            100% { box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3); }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1>ERP Dashboard</h1>
            <p class="subtitle">Enterprise Resource Planning System</p>
        </div>

        <div class="dashboard-grid">
            <div class="nav-card">
                <div class="nav-icon">📦</div>
                <h3>Product Management</h3>
                <p>Manage your inventory, add new products, and track stock levels efficiently.</p>
                <a href="products.php" class="nav-link">Manage Products</a>
            </div>

            <div class="nav-card">
                <div class="nav-icon">👥</div>
                <h3>Customer Management</h3>
                <p>Handle customer information, contact details, and relationship management.</p>
                <a href="customers.php" class="nav-link">Manage Customers</a>
            </div>

            <div class="nav-card">
                <div class="nav-icon">💰</div>
                <h3>Sales Processing</h3>
                <p>Process new sales transactions, create invoices, and manage orders.</p>
                <a href="sales.php" class="nav-link">Process Sales</a>
            </div>

            <div class="nav-card">
                <div class="nav-icon">🔍</div>
                <h3>Item Inspector</h3>
                <p>Check and verify item details, availability, and specifications.</p>
                <a href="sales_items.php" class="nav-link">Check Items</a>
            </div>

            <div class="nav-card">
                <div class="nav-icon">📊</div>
                <h3>Sales Analytics</h3>
                <p>View comprehensive sales reports, analytics, and performance metrics.</p>
                <a href="sales_report.php" class="nav-link">View Reports</a>
            </div>
        </div>

        <div class="logout-section">
            <div class="logout-card">
                <a href="logout.php" class="logout-button">🚪 Logout</a>
            </div>
        </div>
    </div>

    <script>
        // Add interactive effects
        document.querySelectorAll('.nav-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add click ripple effect
        document.querySelectorAll('.nav-link, .logout-button').forEach(button => {
            button.addEventListener('click', function(e) {
                let ripple = document.createElement('span');
                let rect = this.getBoundingClientRect();
                let size = Math.max(rect.width, rect.height);
                let x = e.clientX - rect.left - size / 2;
                let y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.3);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                `;
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
            .nav-link, .logout-button {
                position: relative;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>