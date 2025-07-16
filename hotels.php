<?php
session_start();
require 'db.php';

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get search parameters
$destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$checkin = isset($_GET['checkin']) ? trim($_GET['checkin']) : '';
$checkout = isset($_GET['checkout']) ? trim($_GET['checkout']) : '';

// Validate dates
$checkin_date = !empty($checkin) ? DateTime::createFromFormat('Y-m-d', $checkin) : false;
$checkout_date = !empty($checkout) ? DateTime::createFromFormat('Y-m-d', $checkout) : false;
$today = new DateTime();
if ($checkin && (!$checkin_date || $checkin_date < $today)) {
    $checkin = '';
}
if ($checkout && (!$checkout_date || $checkout_date <= $checkin_date)) {
    $checkout = '';
}

// Check if hotels table exists
$sql_check_table = "SHOW TABLES LIKE 'hotels'";
$result_check_table = $conn->query($sql_check_table);
if ($result_check_table->num_rows == 0) {
    error_log("Table 'hotels' does not exist in database 'dbqpt3idyhnpqr'");
    echo "<p class='error'>Database error: Hotels table not found. Please contact support.</p>";
    $conn->close();
    exit;
}

// Fetch hotels with case-insensitive search
$sql = $destination ? "SELECT * FROM hotels WHERE LOWER(location) LIKE LOWER(?)" : "SELECT * FROM hotels";
$search_term = $destination ? '%' . $destination . '%' : '%';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo "<p class='error'>Database error: " . htmlspecialchars($conn->error) . "</p>";
    $conn->close();
    exit;
}
if ($destination) {
    $stmt->bind_param('s', $search_term);
}
$stmt->execute();
$result = $stmt->get_result();

// Get available locations for error message
$locations_sql = "SELECT DISTINCT location FROM hotels";
$locations_result = $conn->query($locations_sql);
$available_locations = [];
while ($row = $locations_result->fetch_assoc()) {
    $available_locations[] = $row['location'];
}
$locations_list = implode(", ", $available_locations) ?: "No locations available";

// Log if no hotels found
if ($result->num_rows == 0) {
    error_log("No hotels found for destination: $destination. Available locations: $locations_list");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sheraton Hotels - Listings</title>
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
        .container {
            margin-top: 80px;
            padding: 20px;
        }
        .filters {
            margin: 20px;
            display: flex;
            gap: 10px;
        }
        .filters select, .filters input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .hotel-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }
        .hotel-card {
            background: white;
            width: 300px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .hotel-card:hover {
            transform: scale(1.05);
        }
        .hotel-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .hotel-card h3 {
            padding: 15px;
            font-size: 22px;
        }
        .hotel-card p {
            padding: 0 15px;
            color: #666;
        }
        .hotel-card button {
            margin: 15px;
            padding: 10px;
            background: #ffd700;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: calc(100% - 30px);
        }
        .hotel-card button:hover {
            background: #e6c200;
        }
        .error {
            color: red;
            text-align: center;
            margin: 20px;
        }
        @media (max-width: 768px) {
            .hotel-card {
                width: 100%;
            }
            .filters {
                flex-direction: column;
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
    <div class="container">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <p class="error">Please log in to book hotels.</p>
        <?php endif; ?>
        <?php if ($result->num_rows == 0): ?>
            <p class="error">No hotels found for your search. Try these locations: <?php echo htmlspecialchars($locations_list); ?>.</p>
        <?php endif; ?>
        <div class="filters">
            <select id="sort">
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="rating-desc">Best Rated</option>
            </select>
            <input type="number" id="price-min" placeholder="Min Price">
            <input type="number" id="price-max" placeholder="Max Price">
        </div>
        <div class="hotel-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="hotel-card">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p>Price: $<?php echo htmlspecialchars($row['price']); ?>/night</p>
                    <p>Rating: <?php echo htmlspecialchars($row['rating']); ?>/5</p>
                    <button onclick="bookHotel(<?php echo (int)$row['id']; ?>, '<?php echo htmlspecialchars($checkin); ?>', '<?php echo htmlspecialchars($checkout); ?>')">Book Now</button>
                </div>
            <?php endwhile; ?>
            <?php $stmt->close(); ?>
        </div>
    </div>
    <script>
        function bookHotel(id, checkin, checkout) {
            if (!id || id <= 0) {
                alert('Error: Invalid hotel selection. Please try again.');
                return;
            }
            if (!checkin || !checkout) {
                alert('Please select valid check-in and check-out dates before booking.');
                return;
            }
            <?php if (isset($_SESSION['user_id'])): ?>
                window.location.href = `booking.php?hotel_id=${id}&checkin=${encodeURIComponent(checkin)}&checkout=${encodeURIComponent(checkout)}`;
            <?php else: ?>
                alert('Please log in to book a hotel.');
                window.location.href = 'login.php';
            <?php endif; ?>
        }
        document.getElementById('sort').addEventListener('change', function() {
            alert('Sorting feature to be implemented');
        });
    </script>
</body>
</html>
