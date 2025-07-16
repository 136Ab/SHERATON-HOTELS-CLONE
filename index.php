<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sheraton Hotels - Welcome</title>
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
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: url('https://images.unsplash.com/photo-1542314831-8d7f3d9e7b7c') no-repeat center center/cover;
        }
        .hero-content {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .hero-content h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .hero-content p {
            font-size: 20px;
            margin-bottom: 30px;
        }
        .search-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .search-form input {
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            width: 200px;
        }
        .search-form button {
            padding: 12px 20px;
            background: #ffd700;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .search-form button:hover {
            background: #e6c200;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .hero-content {
                padding: 20px;
            }
            .hero-content h1 {
                font-size: 32px;
            }
            .search-form input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="hotels.php">Hotels</a>
        <a href="bookings.php">My Bookings</a>
        <a href="signup.php">Sign Up</a>
        <a href="login.php">Login</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="hero">
        <div class="hero-content">
            <h1>Welcome to Sheraton Hotels</h1>
            <p>Book your dream vacation today!</p>
            <form id="searchForm" action="hotels.php" method="GET" onsubmit="return validateForm()">
                <div class="search-form">
                    <input type="text" name="destination" placeholder="Destination (e.g., New York)" value="">
                    <input type="date" name="checkin" id="checkin" required>
                    <input type="date" name="checkout" id="checkout" required>
                    <button type="submit">Search Hotels</button>
                </div>
                <p id="error" class="error" style="display: none;"></p>
            </form>
        </div>
    </div>
    <script>
        function validateForm() {
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const error = document.getElementById('error');
            const today = new Date().toISOString().split('T')[0];

            if (!checkin || !checkout) {
                error.textContent = 'Please select both check-in and check-out dates.';
                error.style.display = 'block';
                return false;
            }

            if (checkin < today) {
                error.textContent = 'Check-in date must be today or in the future.';
                error.style.display = 'block';
                return false;
            }

            if (checkout <= checkin) {
                error.textContent = 'Check-out date must be after check-in date.';
                error.style.display = 'block';
                return false;
            }

            error.style.display = 'none';
            return true;
        }
    </script>
</body>
</html>
