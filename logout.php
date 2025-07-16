<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sheraton Hotels - Logout</title>
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
        .logout-container {
            margin-top: 80px;
            padding: 20px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .logout-container h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }
        .logout-container p {
            color: #666;
            margin-bottom: 20px;
        }
        .logout-container a {
            padding: 12px;
            background: #ffd700;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-size: 16px;
        }
        .logout-container a:hover {
            background: #e6c200;
        }
        @media (max-width: 768px) {
            .logout-container {
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
    <div class="logout-container">
        <h2>Logged Out</h2>
        <p>You have been successfully logged out.</p>
        <a href="home.php">Return to Home</a>
    </div>
</body>
</html>
