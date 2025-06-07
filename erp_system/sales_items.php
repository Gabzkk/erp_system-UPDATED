<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db.php');

// Fetch products
$product_query = "SELECT * FROM products";
$products = mysqli_query($conn, $product_query);

if (!$products) {
    die("<p class='error'>ERROR: Failed to fetch products!</p>");
}

// Handle adding sale items
if (isset($_POST['add_sale_item'])) {
    $sale_id = $_POST['sale_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    // Ensure values are valid
    if ($quantity <= 0 || $price <= 0) {
        die("<p class='error'>ERROR: Invalid quantity or price!</p>");
    }

    $sales_item_query = "INSERT INTO sales_items (sale_id, product_id, quantity, price) 
                         VALUES ('$sale_id', '$product_id', '$quantity', '$price')";
    $insert_result = mysqli_query($conn, $sales_item_query);

    if (!$insert_result) {
        die("<p class='error'>ERROR: Failed to add sale item!</p>");
    }

    // Deduct stock
    $stock_update_query = "UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE id = '$product_id'";
    $stock_result = mysqli_query($conn, $stock_update_query);

    if (!$stock_result) {
        die("<p class='error'>ERROR: Failed to update stock!</p>");
    }

    echo "<p class='success'>Sale item added successfully!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Sale Items</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #0f0f0f 100%);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(0, 173, 181, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 173, 181, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(255, 62, 62, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: rgba(37, 37, 37, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            text-align: center;
            position: relative;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #00ADB5, #007F88, #00ADB5);
            border-radius: 20px 20px 0 0;
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 30px;
            position: relative;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #00ADB5, #007F88);
            border-radius: 2px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            text-align: left;
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }

        select, input {
            padding: 15px 18px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 1rem;
            background: rgba(62, 62, 62, 0.8);
            color: #ffffff;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        select:focus, input:focus {
            outline: none;
            border-color: #00ADB5;
            box-shadow: 
                0 0 0 3px rgba(0, 173, 181, 0.2),
                0 4px 12px rgba(0, 173, 181, 0.15);
            transform: translateY(-2px);
        }

        select:hover, input:hover {
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        button {
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        button[type="submit"] {
            background: linear-gradient(135deg, #00ADB5 0%, #007F88 100%);
            color: white;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background: linear-gradient(135deg, #007F88 0%, #005F66 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 173, 181, 0.3);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        .home-button {
            margin-top: 20px;
            background: linear-gradient(135deg, #FF3E3E 0%, #C20000 100%);
            color: white;
        }

        .home-button:hover {
            background: linear-gradient(135deg, #C20000 0%, #9A0000 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 62, 62, 0.3);
        }

        .error {
            color: #FF6B6B;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 62, 62, 0.1);
            border: 1px solid rgba(255, 62, 62, 0.3);
            border-radius: 10px;
            animation: shake 0.5s ease-in-out;
        }

        .success {
            color: #4ECDC4;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 20px;
            padding: 15px;
            background: rgba(0, 173, 181, 0.1);
            border: 1px solid rgba(0, 173, 181, 0.3);
            border-radius: 10px;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            position: relative;
        }

        .form-group input::placeholder {
            color: #a0a0a0;
            transition: all 0.3s ease;
        }

        .form-group input:focus::placeholder {
            color: #00ADB5;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 30px 25px;
                margin: 10px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            select, input, button {
                padding: 14px 16px;
                font-size: 0.95rem;
            }
        }

        /* Custom scrollbar for select dropdown */
        select::-webkit-scrollbar {
            width: 8px;
        }

        select::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        select::-webkit-scrollbar-thumb {
            background: #00ADB5;
            border-radius: 4px;
        }

        select::-webkit-scrollbar-thumb:hover {
            background: #007F88;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Products to Sale</h1>

        <form method="POST">
            <input type="hidden" name="sale_id" value="<?php echo isset($_GET['sale_id']) ? htmlspecialchars($_GET['sale_id']) : ''; ?>">
            
            <div class="form-group">
                <label for="product_id">Select Product:</label>
                <select name="product_id" id="product_id" required>
                    <?php while ($row = mysqli_fetch_assoc($products)) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['product_name']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" placeholder="Enter quantity" required min="1">
            </div>

            <div class="form-group">
                <label for="price">Price Per Unit:</label>
                <input type="number" name="price" id="price" placeholder="Enter price per unit" step="0.01" required min="0.01">
            </div>

            <button type="submit" name="add_sale_item">Add to Sale</button>
        </form>

        <button onclick="window.location.href='index.php';" class="home-button">Back to Home</button>
    </div>
</body>
</html>