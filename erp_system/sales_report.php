<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check for admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied: Admins only.");
}

// Include database connection
include('db.php'); // Make sure db.php contains mysqli_connect and appropriate error handling

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Fetch sales records using prepared statement (SECURITY FIX) ---
$sales_query_sql = "SELECT sales.id, customers.name AS customer_name, sales.total_amount, sales.sales_date
                    FROM sales
                    JOIN customers ON sales.customer_id = customers.id
                    ORDER BY sales.sales_date DESC";

$stmt_sales = mysqli_prepare($conn, $sales_query_sql);
if ($stmt_sales === false) {
    die("<p class='error'>ERROR: Failed to prepare sales query - " . mysqli_error($conn) . "</p>");
}
mysqli_stmt_execute($stmt_sales);
$sales_result = mysqli_stmt_get_result($stmt_sales);

if (!$sales_result) {
    die("<p class='error'>ERROR: Failed to fetch sales transactions - " . mysqli_error($conn) . "</p>");
}

// --- Fetch products for each sale using prepared statement (SECURITY FIX) ---
function getSaleItems($sale_id, $conn) {
    // Prepare the statement
    $stmt_items = mysqli_prepare($conn, "SELECT products.product_name, sales_items.quantity, sales_items.price
                                         FROM sales_items
                                         JOIN products ON sales_items.product_id = products.id
                                         WHERE sales_items.sale_id = ?");

    // Check if the statement was prepared successfully
    if ($stmt_items === false) {
        // Log the error instead of dying directly in a function for better error handling flow
        error_log("Failed to prepare items query for sale_id $sale_id: " . mysqli_error($conn));
        return false; // Return false to indicate failure
    }

    // Bind parameters: "i" for integer
    mysqli_stmt_bind_param($stmt_items, "i", $sale_id);

    // Execute the statement
    mysqli_stmt_execute($stmt_items);

    // Get the result set
    $result_items = mysqli_stmt_get_result($stmt_items);

    // Close the statement
    mysqli_stmt_close($stmt_items);

    return $result_items;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Sales Report</title>
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
            display: flex; /* Use flexbox for centering */
            justify-content: center;
            align-items: center;
            padding: 20px; /* Add padding for smaller screens */
        }

        .container {
            width: 90%; /* Adjusted for better responsiveness */
            max-width: 1200px; /* Max width to prevent it from getting too wide on large screens */
            margin: auto;
            padding: 40px; /* Increased padding */
            background: rgba(15, 15, 35, 0.9); /* Same as nav-card background */
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px; /* Rounded corners */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: fadeIn 0.8s ease-out; /* Fade-in animation */
        }

        h1 {
            font-size: 3rem; /* Slightly smaller than dashboard h1 */
            font-weight: 700;
            background: linear-gradient(45deg, #00ADB5, #0891b2, #00d4dd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 30px; /* Increased margin */
            text-shadow: 0 4px 20px rgba(0, 173, 181, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 20px rgba(0, 173, 181, 0.5)); }
            to { filter: drop-shadow(0 0 30px rgba(0, 173, 181, 0.8)); }
        }

        table {
            width: 100%;
            border-collapse: separate; /* Use separate for border-radius on cells */
            border-spacing: 0;
            background: #252525; /* Darker base for table */
            border-radius: 15px; /* Rounded corners for the table */
            overflow: hidden; /* Ensures border-radius is applied to content */
            margin-top: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        th, td {
            padding: 15px 20px; /* Increased padding */
            text-align: left; /* Align text to left for better readability */
            border-bottom: 1px solid rgba(255, 255, 255, 0.08); /* Subtle separator */
            color: #ffffff;
        }

        th {
            background-color: rgba(0, 173, 181, 0.8); /* Slightly transparent for depth */
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:last-child td {
            border-bottom: none; /* No border for the last row */
        }

        td {
            background-color: rgba(26, 26, 46, 0.7); /* Darker background for cells */
            transition: background-color 0.3s ease;
        }

        tbody tr:hover td {
            background-color: rgba(0, 173, 181, 0.1); /* Subtle highlight on row hover */
        }

        ul {
            list-style: none; /* Remove bullet points */
            padding: 0;
            margin: 0;
        }

        li {
            padding: 5px 0;
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.8);
        }

        .home-button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(45deg, #ef4444, #dc2626); /* Red gradient for consistency */
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none; /* Ensure no default button border */
            cursor: pointer;
        }

        .home-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        /* Animations from first code */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Ripple effect for buttons */
        .home-button {
            position: relative;
            overflow: hidden;
        }
        .home-button span.ripple {
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
                padding: 20px;
                width: 95%;
            }
            h1 {
                font-size: 2.5rem;
                margin-bottom: 20px;
            }
            th, td {
                padding: 10px 15px;
                font-size: 0.9em;
            }
            li {
                font-size: 0.8em;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            h1 {
                font-size: 2rem;
                margin-bottom: 15px;
            }
            table {
                font-size: 0.8em;
            }
            th, td {
                padding: 8px 10px;
                display: block; /* Stack cells on very small screens */
                width: 100%;
                text-align: center; /* Center text when stacked */
            }
            thead {
                display: none; /* Hide table headers on small screens */
            }
            tr {
                margin-bottom: 15px;
                display: block;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 10px;
                padding: 10px;
            }
            tr td:first-child {
                background-color: rgba(0, 173, 181, 0.3); /* Highlight sale ID */
                border-radius: 8px 8px 0 0;
            }
            tr td:last-child {
                border-bottom: none;
                border-radius: 0 0 8px 8px;
            }
            li {
                text-align: left; /* Keep list items left-aligned */
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
        <h1>Sales Report</h1>

        <?php if (mysqli_num_rows($sales_result) > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Sale Date</th>
                        <th>Products</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($sale = mysqli_fetch_assoc($sales_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['id']); ?></td>
                        <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                        <td>$<?php echo number_format(htmlspecialchars($sale['total_amount']), 2); ?></td>
                        <td><?php echo htmlspecialchars($sale['sales_date']); ?></td>
                        <td>
                            <ul>
                                <?php
                                $items_result = getSaleItems($sale['id'], $conn);
                                if ($items_result) { // Check if getSaleItems returned a valid result
                                    while ($item = mysqli_fetch_assoc($items_result)) {
                                        echo "<li>" . htmlspecialchars($item['product_name']) . " (" . htmlspecialchars($item['quantity']) . " @ $" . number_format(htmlspecialchars($item['price']), 2) . ")</li>";
                                    }
                                } else {
                                    echo "<li>Error fetching items.</li>";
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p style="color: rgba(255, 255, 255, 0.7); font-size: 1.1em; margin-top: 30px;">No sales records found.</p>
        <?php } ?>

        <button onclick="window.location.href='index.php';" class="home-button">Back to Home</button>
    </div>

    <script>
        // Ripple effect for buttons (copied from first code)
        document.querySelector('.home-button').addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.classList.add('ripple'); // Add a class for specific ripple styling
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

        // No need for dynamically adding @keyframes ripple CSS as it's now in the <style> block
    </script>
</body>
</html>