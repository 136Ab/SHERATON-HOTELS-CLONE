<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to view bookings.'); window.location.href='login.php';</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT b.*, h.name AS hotel_name FROM bookings b JOIN hotels h ON b.hotel_id = h.id WHERE b.user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo "<script>alert('Database error. Please try again later.'); window.location.href='home.php';</script>";
    exit;
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sheraton Hotels - My Bookings</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            background: #f5f7fa;
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
        .bookings-container {
            margin-top: 80px;
            padding: 20px;
        }
        .booking-card {
            background: white;
            padding: 20px;
            margin: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .booking-card h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .booking-card p {
            color: #666;
            margin: 5px 0;
        }
        @media (max-width: 768px) {
            .booking-card {
                margin: 10px;
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
    <div class="bookings-container">
        <h2>My Bookings</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="booking-card">
                <h3><?php echo htmlspecialchars($row['hotel_name']); ?></h3>
                <p>Check-in: <?php echo htmlspecialchars($row['checkin']); ?></p>
                <p>Check-out: <?php echo htmlspecialchars($row['checkout']); ?></p>
            </div>
        <?php endwhile; ?>
        <?php $stmt->close(); ?>
    </div>
</body>
</html>
