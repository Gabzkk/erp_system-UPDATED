<?php
include('db.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure database connection is valid
if (!$conn) {
    die("<p class='error'>ERROR: Database connection failed.</p>");
}

// Fetch customers
$customer_query = "SELECT * FROM customers";
$customers = mysqli_query($conn, $customer_query);

// Fetch products
$product_query = "SELECT * FROM products WHERE stock_quantity > 0";
$products = mysqli_query($conn, $product_query);

if (!$customers || !$products) {
    die("<p class='error'>ERROR: Failed to fetch customers or products!</p>");
}

// Handle sale submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['process_sale'])) {
    if (!isset($_POST['customer_id']) || !isset($_POST['total_amount']) || empty($_POST['selected_products'])) {
        die("<p class='error'>ERROR: Missing required sale details.</p>");
    }

    $customer_id = $_POST['customer_id'];
    $total_amount = $_POST['total_amount'];
    $selected_products = $_POST['selected_products'];

    // Insert sale into database
    $sales_query = "INSERT INTO sales (customer_id, total_amount) VALUES ('$customer_id', '$total_amount')";
    $sales_result = mysqli_query($conn, $sales_query);

    if (!$sales_result) {
        die("<p class='error'>ERROR: Failed to insert sale - " . mysqli_error($conn) . "</p>");
    }

    $sale_id = mysqli_insert_id($conn);

    foreach ($selected_products as $product_id) {
        $quantity = $_POST['quantity'][$product_id];
        $price = $_POST['price'][$product_id];

        // Validate quantity & stock
        $stock_check = mysqli_query($conn, "SELECT stock_quantity FROM products WHERE id = '$product_id'");
        $stock_data = mysqli_fetch_assoc($stock_check);

        if ($quantity <= 0 || $quantity > $stock_data['stock_quantity']) {
            die("<p class='error'>ERROR: Invalid quantity or insufficient stock for Product ID {$product_id}.</p>");
        }

        // Insert sale item
        $sales_item_query = "INSERT INTO sales_items (sale_id, product_id, quantity, price) 
                             VALUES ('$sale_id', '$product_id', '$quantity', '$price')";
        mysqli_query($conn, $sales_item_query);

        // Deduct stock
        $stock_update_query = "UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE id = '$product_id'";
        mysqli_query($conn, $stock_update_query);
    }

    header("Location: sales_items.php?sale_id=" . $sale_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Process Sale - ERP System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --dark-lighter: #334155;
            --glass: rgba(15, 23, 42, 0.7);
            --glass-light: rgba(30, 41, 59, 0.8);
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --border: rgba(148, 163, 184, 0.2);
            --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            --shadow-lg: 0 35px 60px -12px rgba(0, 0, 0, 0.7);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 35%, #334155 100%);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(6, 182, 212, 0.1) 0%, transparent 50%);
            z-index: -2;
            animation: backgroundShift 20s ease-in-out infinite;
        }

        @keyframes backgroundShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            border-radius: 50%;
            opacity: 0.1;
            animation: float 15s infinite linear;
        }

        .particle:nth-child(1) { width: 4px; height: 4px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 6px; height: 6px; left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 3px; height: 3px; left: 30%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 5px; height: 5px; left: 40%; animation-delay: 6s; }
        .particle:nth-child(5) { width: 4px; height: 4px; left: 50%; animation-delay: 8s; }
        .particle:nth-child(6) { width: 6px; height: 6px; left: 60%; animation-delay: 10s; }
        .particle:nth-child(7) { width: 3px; height: 3px; left: 70%; animation-delay: 12s; }
        .particle:nth-child(8) { width: 5px; height: 5px; left: 80%; animation-delay: 14s; }

        @keyframes float {
            0% { 
                transform: translateY(100vh) rotate(0deg); 
                opacity: 0; 
            }
            10% { 
                opacity: 0.1; 
            }
            90% { 
                opacity: 0.1; 
            }
            100% { 
                transform: translateY(-100px) rotate(360deg); 
                opacity: 0; 
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            animation: slideInDown 0.8s ease-out;
        }

        .header h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 4px 20px rgba(99, 102, 241, 0.3));
            animation: textGlow 3s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            from { filter: drop-shadow(0 4px 20px rgba(99, 102, 241, 0.3)); }
            to { filter: drop-shadow(0 6px 30px rgba(99, 102, 241, 0.6)); }
        }

        .subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .main-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out 0.2s both;
        }

        .main-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            transition: left 0.8s ease;
        }

        .main-card:hover::before {
            left: 100%;
        }

        .form-section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 1rem;
            background: var(--glass-light);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }

        .products-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--glass-light);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .products-table th {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.2rem;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
        }

        .products-table th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--accent), var(--primary));
        }

        .products-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            background: var(--glass);
            transition: all 0.3s ease;
        }

        .product-row:hover td {
            background: rgba(99, 102, 241, 0.1);
            transform: scale(1.01);
        }

        .product-row.selected td {
            background: rgba(99, 102, 241, 0.2);
            border-color: var(--primary);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            appearance: none;
            border: 2px solid var(--border);
            border-radius: 6px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-checkbox:checked {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-color: var(--primary);
        }

        .custom-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .quantity-input {
            width: 80px;
            padding: 0.5rem;
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            text-align: center;
            transition: all 0.3s ease;
        }

        .quantity-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        .total-section {
            background: linear-gradient(135deg, var(--success), #059669);
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .total-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: translateX(-100%);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .total-label {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .total-amount {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .button-group {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 160px;
            justify-content: center;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(239, 68, 68, 0.4);
        }

        .error {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .main-card {
                padding: 1.5rem;
            }
            
            .products-table {
                font-size: 0.9rem;
            }
            
            .products-table th,
            .products-table td {
                padding: 0.8rem 0.5rem;
            }
            
            .button-group {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
            }
            
            .total-amount {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .products-table th,
            .products-table td {
                padding: 0.6rem 0.3rem;
                font-size: 0.8rem;
            }
            
            .quantity-input {
                width: 60px;
            }
        }

        /* Loading animation for buttons */
        .btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Process Sale</h1>
            <p class="subtitle">Create and manage sales transactions</p>
        </div>

        <div class="main-card">
            <form method="POST" id="salesForm">
                <div class="form-section">
                    <h2 class="section-title">
                        <span class="section-icon">👤</span>
                        Customer Selection
                    </h2>
                    <div class="form-group">
                        <label for="customer_id" class="form-label">Select Customer</label>
                        <select name="customer_id" id="customer_id" class="form-select" required>
                            <option value="">Choose a customer...</option>
                            <?php while ($row = mysqli_fetch_assoc($customers)) { ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">
                        <span class="section-icon">🛍️</span>
                        Product Selection
                    </h2>
                    <div class="table-container">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($products)) { ?>
                                <tr class="product-row">
                                    <td>
                                        <div class="checkbox-wrapper">
                                            <input type="checkbox" class="custom-checkbox product-checkbox" name="selected_products[]" value="<?php echo $row['id']; ?>">
                                        </div>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></td>
                                    <td class="price" data-price="<?php echo $row['price']; ?>">$<?php echo number_format($row['price'], 2); ?></td>
                                    <td><span class="stock-badge"><?php echo $row['stock_quantity']; ?> units</span></td>
                                    <td>
                                        <input type="number" name="quantity[<?php echo $row['id']; ?>]" class="quantity-input quantity" min="1" max="<?php echo $row['stock_quantity']; ?>" value="1">
                                        <input type="hidden" name="price[<?php echo $row['id']; ?>]" value="<?php echo $row['price']; ?>">
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="total-section">
                    <div class="total-label">Total Amount</div>
                    <div class="total-amount" id="displayTotal">$0.00</div>
                    <input type="hidden" id="total_amount" name="total_amount" value="0.00">
                </div>

                <div class="button-group">
                    <button type="submit" name="process_sale" class="btn btn-primary" id="submitBtn">
                        💳 Process Sale
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        🏠 Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateTotal() {
            let total = 0;
            const rows = document.querySelectorAll(".product-row");
            
            rows.forEach(row => {
                const checkbox = row.querySelector(".product-checkbox");
                const quantityInput = row.querySelector(".quantity");
                
                if (checkbox.checked) {
                    row.classList.add('selected');
                    const quantity = parseInt(quantityInput.value) || 0;
                    const price = parseFloat(row.querySelector(".price").dataset.price) || 0;
                    total += quantity * price;
                } else {
                    row.classList.remove('selected');
                }
            });
            
            document.getElementById("total_amount").value = total.toFixed(2);
            document.getElementById("displayTotal").textContent = '$' + total.toFixed(2);
            
            // Add visual feedback for total changes
            const totalSection = document.querySelector('.total-section');
            totalSection.style.transform = 'scale(1.02)';
            setTimeout(() => {
                totalSection.style.transform = 'scale(1)';
            }, 200);
        }

        function addInteractivity() {
            // Add event listeners for real-time updates
            document.querySelectorAll(".product-checkbox, .quantity").forEach(input => {
                input.addEventListener("input", updateTotal);
                input.addEventListener("change", updateTotal);
            });

            // Form validation and loading state
            const form = document.getElementById('salesForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function(e) {
                const selectedProducts = document.querySelectorAll('.product-checkbox:checked');
                const customerId = document.getElementById('customer_id').value;

                if (!customerId) {
                    e.preventDefault();
                    alert('Please select a customer');
                    return;
                }

                if (selectedProducts.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one product');
                    return;
                }

                // Add loading state
                submitBtn.classList.add('loading');
                submitBtn.textContent = 'Processing...';
            });

            // Add ripple effect to buttons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
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
                        pointer-events: none;
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Initialize total calculation
            updateTotal();
        }

        // Initialize when DOM is loaded
        document.addEventListener("DOMContentLoaded", addInteractivity);

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>