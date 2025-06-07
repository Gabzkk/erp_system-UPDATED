<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db.php');

// Fetch products
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("ERROR: QUERY FAILED! " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - ERP System</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            animation: fadeIn 0.8s ease-out;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.8s ease-out;
        }

        h1 {
            font-size: 3rem;
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
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 300;
            margin-bottom: 10px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #00ADB5;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .breadcrumb a:hover {
            color: #00d4dd;
            text-shadow: 0 0 10px rgba(0, 173, 181, 0.5);
        }

        .form-card {
            background: rgba(15, 15, 35, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .form-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 173, 181, 0.05), transparent);
            transition: left 0.6s ease;
        }

        .form-card:hover:before {
            left: 100%;
        }

        .form-title {
            font-size: 1.5rem;
            color: #ffffff;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #00ADB5, #0891b2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .input-group {
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(62, 62, 62, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .input-group input:focus {
            outline: none;
            border-color: #00ADB5;
            box-shadow: 0 0 20px rgba(0, 173, 181, 0.3);
            transform: translateY(-2px);
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .submit-btn {
            width: 100%;
            padding: 15px 30px;
            background: linear-gradient(45deg, #00ADB5, #0891b2);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 173, 181, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .table-card {
            background: rgba(15, 15, 35, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-title {
            font-size: 1.5rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-box {
            padding: 10px 15px;
            background: rgba(62, 62, 62, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            color: #ffffff;
            font-size: 0.9rem;
            min-width: 200px;
        }

        .search-box::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(25, 25, 45, 0.8);
            backdrop-filter: blur(10px);
        }

        th, td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: linear-gradient(45deg, #00ADB5, #0891b2);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        tr:hover td {
            background: rgba(0, 173, 181, 0.1);
            transform: scale(1.01);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-edit, .btn-delete {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: linear-gradient(45deg, #10b981, #059669);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-delete {
            background: linear-gradient(45deg, #ef4444, #dc2626);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .back-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 15px 25px;
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.5);
        }

        .stock-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            min-width: 60px;
        }

        .stock-high {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .stock-medium {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .stock-low {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .price-tag {
            font-weight: 600;
            color: #00d4dd;
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
            background: rgba(0, 173, 181, 0.05);
            border-radius: 50%;
            animation: float 12s ease-in-out infinite;
        }

        .shape:nth-child(1) { width: 120px; height: 120px; top: 15%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { width: 80px; height: 80px; top: 60%; left: 85%; animation-delay: 4s; }
        .shape:nth-child(3) { width: 100px; height: 100px; top: 80%; left: 15%; animation-delay: 8s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
            50% { transform: translateY(-40px) rotate(180deg); opacity: 0.6; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container { padding: 0 10px; }
            h1 { font-size: 2.2rem; }
            .form-grid { grid-template-columns: 1fr; }
            .table-header { flex-direction: column; align-items: stretch; }
            .search-box { min-width: auto; }
            .back-button { bottom: 20px; right: 20px; padding: 12px 20px; }
            th, td { padding: 12px 15px; }
            .action-buttons { flex-direction: column; gap: 5px; }
        }

        @media (max-width: 480px) {
            h1 { font-size: 1.8rem; }
            .form-card, .table-card { padding: 20px; margin-bottom: 20px; }
            th, td { padding: 10px 12px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Product Management</h1>
            <p class="subtitle">Manage your inventory and product catalog</p>
            <div class="breadcrumb">
                <a href="index.php">🏠 Dashboard</a>
                <span>›</span>
                <span>Product Management</span>
            </div>
        </div>

        <!-- Add Product Form -->
        <div class="form-card">
            <div class="form-title">
                <div class="form-icon">📦</div>
                Add New Product
            </div>
            <form action="product_controller.php" method="POST">
                <div class="form-grid">
                    <div class="input-group">
                        <input type="text" name="product_name" placeholder="Product Name" required>
                    </div>
                    <div class="input-group">
                        <input type="text" name="category" placeholder="Category" required>
                    </div>
                    <div class="input-group">
                        <input type="number" name="price" placeholder="Price ($)" step="0.01" required>
                    </div>
                    <div class="input-group">
                        <input type="number" name="stock_quantity" placeholder="Stock Quantity" required>
                    </div>
                </div>
                <button type="submit" name="add_product" class="submit-btn">
                    ✨ Add Product
                </button>
            </form>
        </div>

        <!-- Products Table -->
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">
                    <div class="form-icon">📋</div>
                    Product Inventory
                </div>
                <input type="text" class="search-box" placeholder="🔍 Search products..." id="searchInput">
            </div>
            
            <div class="table-wrapper">
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { 
                            $stock = (int)$row['stock_quantity'];
                            $stockClass = $stock > 50 ? 'stock-high' : ($stock > 20 ? 'stock-medium' : 'stock-low');
                            $stockText = $stock > 50 ? 'In Stock' : ($stock > 20 ? 'Low Stock' : 'Critical');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td class="price-tag">$<?php echo number_format($row['price'], 2); ?></td>
                            <td>
                                <div class="stock-badge <?php echo $stockClass; ?>">
                                    <?php echo $stock; ?> - <?php echo $stockText; ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                                        ✏️ Edit
                                    </a>
                                    <a href="delete_product.php?id=<?php echo $row['id']; ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('⚠️ Are you sure you want to delete this product?');">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <button onclick="window.location.href='index.php';" class="back-button">
        🏠 Back to Dashboard
    </button>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('productsTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length - 1; j++) {
                    if (cells[j].textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        });

        // Add ripple effect to buttons
        document.querySelectorAll('.submit-btn, .btn-edit, .btn-delete, .back-button').forEach(button => {
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
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
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
        `;
        document.head.appendChild(style);

        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>