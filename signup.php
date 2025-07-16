<?php
session_start();
require 'db.php';

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    echo "<script>alert('You are already logged in.'); window.location.href='home.php';</script>";
    exit;
}

// Check if users table exists
$sql_check_table = "SHOW TABLES LIKE 'users'";
$result_check_table = $conn->query($sql_check_table);
if ($result_check_table->num_rows == 0) {
    error_log("Table 'users' does not exist in database 'dbqpt3idyhnpqr'");
    echo "<script>alert('Database error: User table not found. Please contact support.'); window.location.href='index.php';</script>";
    $conn->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($name) || empty($email) || empty($password)) {
        error_log("Signup failed: Missing required fields");
        echo "<script>alert('Please fill in all required fields.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Signup failed: Invalid email format - $email");
        echo "<script>alert('Please enter a valid email address.');</script>";
    } elseif (strlen($password) < 6) {
        error_log("Signup failed: Password too short for email - $email");
        echo "<script>alert('Password must be at least 6 characters long.');</script>";
    } else {
        // Check if email already exists
        $sql_check = "SELECT id FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        if (!$stmt_check) {
            error_log("Prepare failed for email check: " . $conn->error);
            echo "<script>alert('Database error: " . addslashes($conn->error) . "'); window.location.href='signup.php';</script>";
            exit;
        }
        $stmt_check->bind_param('s', $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            error_log("Signup failed: Email already exists - $email");
            echo "<script>alert('Error: This email is already registered.');</script>";
            $stmt_check->close();
        } else {
            $stmt_check->close();
            // Insert new user
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed for insert: " . $conn->error);
                echo "<script>alert('Database error: " . addslashes($conn->error) . "'); window.location.href='signup.php';</script>";
                exit;
            }
            $stmt->bind_param('sss', $name, $email, $password_hash);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $name;
                error_log("Signup successful for user: $email");
                echo "<script>alert('Registration successful!'); window.location.href='home.php';</script>";
            } else {
                error_log("Execute failed: " . $stmt->error);
                echo "<script>alert('Error registering user: " . addslashes($stmt->error) . "');</script>";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sheraton Hotels - Sign Up</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            background: linear-gradient(to right, #f5f7fa, #c3cfe2);
            color: #333;
        }
        .navbar {
            background: #1a2a44;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 20px;
            font-size: 18px;
        }
        .navbar a:hover {
            color: #ffd700;
        }
        .signup-container {
            margin-top: 80px;
            padding: 20px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .signup-container h2 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }
        .signup-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .signup-container input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .signup-container button {
            padding: 12px;
            background: #ffd700;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .signup-container button:hover {
            background: #e6c200;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .signup-container {
                margin: 80px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="home.php">Home</a>
        <a href="hotels.php">Hotels</a>
        <a href="bookings.php">My Bookings</a>
        <a href="signup.php">Sign Up</a>
        <a href="login.php">Login</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>
