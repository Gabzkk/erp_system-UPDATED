<?php
session_start();
// Security: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Security: Optional - Restrict access based on role if only certain users can edit products
// For example, if only admins can edit products:
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     die("Access denied: You do not have permission to edit products.");
// }

error_reporting(E_ALL);
ini_set('display_errors', 1);
include('db.php'); // Make sure db.php contains mysqli_connect and appropriate error handling

// --- Get product ID from URL (using prepared statement for safety) ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<p class='error-message'>ERROR: No product ID provided.</p>");
}

$product_id = $_GET['id'];

// Fetch product details using a prepared statement (SECURITY FIX)
$product_query_sql = "SELECT id, product_name, category, price, stock_quantity FROM products WHERE id = ?";
$stmt_product = mysqli_prepare($conn, $product_query_sql);

if ($stmt_product === false) {
    die("<p class='error-message'>ERROR: Failed to prepare product fetch query - " . mysqli_error($conn) . "</p>");
}

mysqli_stmt_bind_param($stmt_product, "i", $product_id); // "i" for integer
mysqli_stmt_execute($stmt_product);
$product_result = mysqli_stmt_get_result($stmt_product);

if (!$product_result || mysqli_num_rows($product_result) == 0) {
    die("<p class='error-message'>ERROR: Product not found.</p>");
}

$product = mysqli_fetch_assoc($product_result);
mysqli_stmt_close($stmt_product); // Close the statement after fetching

// --- Handle product update (using prepared statement for safety) ---
if (isset($_POST['update_product'])) {
    $name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock_quantity'];

    // Update query using a prepared statement (SECURITY FIX)
    $update_query_sql = "UPDATE products SET product_name=?, category=?, price=?, stock_quantity=? WHERE id=?";
    $stmt_update = mysqli_prepare($conn, $update_query_sql);

    if ($stmt_update === false) {
        die("<p class='error-message'>ERROR: Failed to prepare update query - " . mysqli_error($conn) . "</p>");
    }

    // "s" for string, "s" for string, "d" for double (float), "i" for integer, "i" for integer
    mysqli_stmt_bind_param($stmt_update, "ssdii", $name, $category, $price, $stock, $product_id);

    if (mysqli_stmt_execute($stmt_update)) {
        mysqli_stmt_close($stmt_update); // Close statement before redirect
        header("Location: products.php?status=updated"); // Redirect to product list after update
        exit();
    } else {
        die("<p class='error-message'>ERROR: Failed to update product: " . mysqli_error($conn) . "</p>");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - ERP System</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative; /* For floating shapes */
        }

        .container {
            width: 90%;
            max-width: 500px; /* Adjusted max-width for forms */
            padding: 40px;
            background: rgba(15, 15, 35, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        h1 {
            font-size: 2.8rem;
            font-weight: 700;
            background: linear-gradient(45deg, #00ADB5, #0891b2, #00d4dd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 30px;
            text-shadow: 0 4px 20px rgba(0, 173, 181, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 20px rgba(0, 173, 181, 0.5)); }
            to { filter: drop-shadow(0 0 30px rgba(0, 173, 181, 0.8)); }
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px; /* Spacing between form elements */
        }

        input[type="text"],
        input[type="number"] {
            padding: 15px 20px;
            border: 1px solid rgba(0, 173, 181, 0.3); /* Subtle accent border */
            border-radius: 10px; /* More rounded inputs */
            background-color: rgba(26, 26, 46, 0.7); /* Darker input background */
            color: #ffffff;
            font-size: 1.1rem;
            width: 100%;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #00ADB5;
            box-shadow: 0 0 15px rgba(0, 173, 181, 0.4);
            outline: none; /* Remove default outline */
        }

        button[type="submit"] {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(45deg, #00ADB5, #0891b2);
            color: white;
            text-decoration: none;
            border-radius: 10px; /* Slightly less rounded than dashboard buttons */
            font-weight: 600;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 173, 181, 0.2);
            margin-top: 10px; /* Space above button */
        }

        button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 173, 181, 0.4);
        }

        .error-message {
            color: #ef4444; /* Red for errors */
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 1.1em;
        }

        /* Animations from first code */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Ripple effect for buttons */
        button[type="submit"] span.ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple 0.6s linear;
        }
        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }

        /* Floating Shapes - Copied directly */
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 30px;
            }
            h1 {
                font-size: 2.2rem;
            }
            input, button {
                font-size: 1rem;
                padding: 12px 15px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
                width: 95%;
            }
            h1 {
                font-size: 1.8rem;
            }
            input, button {
                padding: 10px 12px;
            }
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
        <h1>Edit Product</h1>
        <form method="POST">
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required placeholder="Product Name">
            <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required placeholder="Category">
            <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" required placeholder="Price">
            <input type="number" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required placeholder="Stock Quantity">
            <button type="submit" name="update_product">Update Product</button>
        </form>
    </div>

    <script>
        // Ripple effect for the update button
        document.querySelector('button[type="submit"]').addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.classList.add('ripple');
            let rect = this.getBoundingClientRect();
            let size = Math.max(rect.width, rect.height);
            let x = e.clientX - rect.left - size / 2;
            let y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
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
    </script>
</body>
</html>