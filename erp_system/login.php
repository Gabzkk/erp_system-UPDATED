<?php
session_start();
include('db.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "User not found.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $role = $_POST['role'];


    if ($role === "admin" && !str_ends_with($new_password, "admin")) {
        $error = "Admin passwords must end with 'admin'.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Check if username exists
        $check_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$new_username'");
        if (mysqli_num_rows($check_user) > 0) {
            $error = "Username already exists.";
        } else {
            $insert_query = "INSERT INTO users (username, password, role) VALUES ('$new_username', '$hashed_password', '$role')";
            if (mysqli_query($conn, $insert_query)) {
                $success = "Account created successfully! You can now log in.";
            } else {
                $error = "Registration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(15, 15, 35, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-tabs {
            display: flex;
            margin-bottom: 30px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .tab-btn {
            flex: 1;
            padding: 12px 24px;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: rgba(0, 173, 181, 0.2);
            color: #00d4dd;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 173, 181, 0.3);
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
            animation: fadeIn 0.4s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        h2 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .input-container {
            position: relative;
        }

        input, select {
            width: 100%;
            padding: 15px 20px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        input:focus, select:focus {
            outline: none;
            border-color: #00ADB5;
            background: rgba(0, 0, 0, 0.6);
            box-shadow: 0 0 20px rgba(0, 173, 181, 0.4);
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        select {
            cursor: pointer;
        }

        select option {
            background: #1a1a2e;
            color: #ffffff;
            padding: 10px;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #00ADB5;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
            user-select: none;
        }

        .toggle-password:hover {
            color: #00d4dd;
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #00ADB5, #0891b2);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0, 173, 181, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 173, 181, 0.5);
            background: linear-gradient(45deg, #0891b2, #00ADB5);
        }

        button:active {
            transform: translateY(0);
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert.error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fecaca;
        }

        .alert.success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.4);
            color: #bbf7d0;
        }

        .admin-note {
            background: rgba(245, 158, 11, 0.2);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #fde68a;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
                margin: 20px 10px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            input, select, button {
                padding: 12px 15px;
                font-size: 14px;
            }
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
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) { width: 80px; height: 80px; top: 20%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { width: 120px; height: 120px; top: 60%; left: 80%; animation-delay: 2s; }
        .shape:nth-child(3) { width: 60px; height: 60px; top: 80%; left: 20%; animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
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
        <div class="form-tabs">
            <button class="tab-btn active" onclick="switchTab('login')">Login</button>
            <button class="tab-btn" onclick="switchTab('signup')">Sign Up</button>
        </div>

        <!-- Login Section -->
        <div id="login" class="form-section active">
            <h2>Welcome Back</h2>
            
            <?php if (isset($error)) { ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php } ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="password-login">Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password-login" placeholder="Enter your password" required>
                        <span class="toggle-password" id="password-login-toggle" onclick="togglePassword('password-login')">Show</span>
                    </div>
                </div>
                
                <button type="submit" name="login">Sign In</button>
            </form>
        </div>

        <!-- Signup Section -->
        <div id="signup" class="form-section">
            <h2>Create Account</h2>
            
            <?php if (isset($success)) { ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php } ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="new_username">Username</label>
                    <input type="text" name="new_username" id="new_username" placeholder="Choose a username" required>
                </div>
                
                <div class="form-group">
                    <label for="password-signup">Password</label>
                    <div class="password-container">
                        <input type="password" name="new_password" id="password-signup" placeholder="Create a password" required>
                        <span class="toggle-password" id="password-signup-toggle" onclick="togglePassword('password-signup')">Show</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" onchange="showAdminNote()">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                    <div id="admin-note" class="admin-note" style="display: none;">
                        <strong>Note:</strong> Admin passwords must end with "admin"
                    </div>
                </div>
                
                <button type="submit" name="register">Create Account</button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Remove active class from all tabs and sections
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.form-section').forEach(section => section.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding section
            event.target.classList.add('active');
            document.getElementById(tab).classList.add('active');
            
            // Clear any previous messages when switching tabs
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => alert.remove());
        }

        function togglePassword(id) {
            let input = document.getElementById(id);
            let toggle = document.getElementById(id + "-toggle");
            
            if (input.type === "password") {
                input.type = "text";
                toggle.textContent = "Hide";
            } else {
                input.type = "password";
                toggle.textContent = "Show";
            }
        }

        function showAdminNote() {
            const roleSelect = document.getElementById('role');
            const adminNote = document.getElementById('admin-note');
            
            if (roleSelect.value === 'admin') {
                adminNote.style.display = 'block';
            } else {
                adminNote.style.display = 'none';
            }
        }

        // Add smooth focus transitions
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            element.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>