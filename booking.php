<?php
session_start();
require 'db.php';

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and user_id exists in users table
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    error_log("Booking failed: No valid user_id in session");
    echo "<script>alert('Please log in to book a hotel.'); window.location.href='login.php';</script>";
    exit;
}

// Validate user_id exists in users table
$user_id = (int)$_SESSION['user_id'];
$sql = "SELECT id FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed for user check: " . $conn->error);
    echo "<script>alert('Database error: " . addslashes($conn->error) . "'); window.location.href='hotels.php';</script>";
    exit;
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    error_log("Booking failed: User not found (ID: $user_id)");
    session_destroy(); // Clear invalid session
    echo "<script>alert('Error: User account not found. Please log in again.'); window.location.href='login.php';</script>";
    exit;
}
$stmt->close();

// Get and validate input parameters
$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
$checkin = isset($_GET['checkin']) ? trim($_GET['checkin']) : '';
$checkout = isset($_GET['checkout']) ? trim($_GET['checkout']) : '';

// Validate hotel_id
if ($hotel_id <= 0) {
    error_log("Booking failed: Invalid hotel_id ($hotel_id)");
    echo "<script>alert('Error: Invalid hotel ID. Please select a valid hotel from the list.'); window.location.href='hotels.php';</script>";
    exit;
}

// Validate dates
if (empty($checkin)) {
    error_log("Booking failed: Check-in date missing");
    echo "<script>alert('Error: Check-in date is required.'); window.location.href='hotels.php';</script>";
    exit;
}
if (empty($checkout)) {
    error_log("Booking failed: Check-out date missing");
    echo "<script>alert('Error: Check-out date is required.'); window.location.href='hotels.php';</script>";
    exit;
}

// Validate date format and logic
$checkin_date = DateTime::createFromFormat('Y-m-d', $checkin);
$checkout_date = DateTime::createFromFormat('Y-m-d', $checkout);
$today = new DateTime();
if (!$checkin_date || $checkin_date < $today) {
    error_log("Booking failed: Invalid or past check-in date ($checkin)");
    echo "<script>alert('Error: Check-in date must be today or in the future and in YYYY-MM-DD format.'); window.location.href='hotels.php';</script>";
    exit;
}
if (!$checkout_date || $checkout_date <= $checkin_date) {
    error_log("Booking failed: Invalid or same-day check-out date ($checkout)");
    echo "<script>alert('Error: Check-out date must be after check-in and in YYYY-MM-DD format.'); window.location.href='hotels.php';</script>";
    exit;
}

// Check if hotel exists
$sql = "SELECT * FROM hotels WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo "<script>alert('Database error: " . addslashes($conn->error) . "'); window.location.href='hotels.php';</script>";
    exit;
}
$stmt->bind_param('i', $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$hotel) {
    error_log("Booking failed: Hotel not found (ID: $hotel_id)");
    $sql_available = "SELECT id, name FROM hotels";
    $result_available = $conn->query($sql_available);
    $available_ids = [];
    while ($row = $result_available->fetch_assoc()) {
        $available_ids[] = "ID: {$row['id']}, Name: {$row['name']}";
    }
    $ids_list = implode("; ", $available_ids) ?: "No hotels available";
    error_log("Available hotel IDs: $ids_list");
    echo "<script>alert('Error: Selected hotel (ID: $hotel_id) not found. Available hotels: $ids_list'); window.location.href='hotels.php';</script>";
    exit;
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO bookings (hotel_id, user_id, checkin, checkout) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for booking insert: " . $conn->error);
        echo "<script>alert('Database error: " . addslashes($conn->error) . "'); window.location.href='hotels.php';</script>";
        exit;
    }
    $stmt->bind_param('iiss', $hotel_id, $user_id, $checkin, $checkout);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo "<script>alert('Booking confirmed!'); window.location.href='bookings.php';</script>";
    } else {
        error_log("Booking insert failed: " . $stmt->error);
        echo "<script>alert('Error confirming booking: " . addslashes($stmt->error) . "'); window.location.href='hotels.php';</script>";
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sheraton Hotels - Booking</title>
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
        .booking-container {
            margin-top: 80px;
            padding: 20px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .booking-container h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }
        .booking-container img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .booking-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .booking-container input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .booking-container button {
            padding: 12px;
            background: #ffd700;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .booking-container button:hover {
            background: #e6c200;
        }
        @media (max-width: 768px) {
            .booking-container {
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
    <div class="booking-container">
        <h2>Book <?php echo htmlspecialchars($hotel['name']); ?></h2>
        <img src="<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
        <p>Price: $<?php echo htmlspecialchars($hotel['price']); ?>/night</p>
        <p>Check-in: <?php echo htmlspecialchars($checkin); ?></p>
        <p>Check-out: <?php echo htmlspecialchars($checkout); ?></p>
        <form method="POST">
            <button type="submit">Confirm Booking</button>
        </form>
    </div>
</body>
</html>
