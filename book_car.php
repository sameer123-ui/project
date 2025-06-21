<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include 'db.php';
session_start();

// Access control
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    echo "Access denied.";
    exit;
}

$user_id = $_SESSION['user']['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id          = $_POST['car_id'];
    $pickup_location = trim($_POST['pickup_location']);
    $drop_location   = trim($_POST['drop_location']);
    $start_date      = $_POST['start_date'];
    $end_date        = $_POST['end_date'];

    $today = date('Y-m-d');

    // Validate inputs
    if (empty($car_id) || empty($pickup_location) || empty($drop_location) || empty($start_date) || empty($end_date)) {
        $message = "Please fill in all fields.";
    } elseif ($start_date > $end_date) {
        $message = "End date cannot be before start date.";
    } elseif ($start_date < $today || $end_date < $today) {
        $message = "Booking dates cannot be in the past.";
    } else {
        // Check for overlapping bookings
        $stmt = $conn->prepare("
            SELECT * FROM bookings 
            WHERE car_id = ? 
              AND status = 'booked' 
              AND start_date <= ? 
              AND end_date >= ?
        ");
        $stmt->bind_param("iss", $car_id, $end_date, $start_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This car is already booked for the selected dates.";
        } else {
            // Calculate total_amount based on car price and days
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $diff = $start->diff($end);
            $days = $diff->days;
            if ($days == 0) $days = 1; // minimum 1 day

            // Get car price_per_day
            $stmtPrice = $conn->prepare("SELECT price_per_day FROM cars WHERE id = ?");
            $stmtPrice->bind_param("i", $car_id);
            $stmtPrice->execute();
            $stmtPrice->bind_result($price_per_day);
            $stmtPrice->fetch();
            $stmtPrice->close();

            $total_amount = $price_per_day * $days;

            // Insert booking
            $stmt = $conn->prepare("
                INSERT INTO bookings (user_id, car_id, start_date, end_date, pickup_location, drop_location, status, total_amount)
                VALUES (?, ?, ?, ?, ?, ?, 'booked', ?)
            ");
            $stmt->bind_param("iissssd", $user_id, $car_id, $start_date, $end_date, $pickup_location, $drop_location, $total_amount);

            if ($stmt->execute()) {
                $message = "Car booked successfully!";
            } else {
                $message = "Error booking the car. Please try again.";
            }
        }
    }
}

// Fetch cars for selection
$cars = $conn->query("SELECT * FROM cars");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Car</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background-color: #f2f2f2;
        }
        .navbar {
            background: #0069d9;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .container {
            padding: 30px;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            font-weight: bold;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        form {
            margin-top: 20px;
            background: #fff;
            padding: 20px;
            max-width: 450px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        select, input[type="text"], input[type="date"], button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            background-color: #0069d9;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div><strong>User Dashboard</strong></div>
        <div>
            <a href="dashboard_user.php">Home</a>
            <a href="index.php">Browse Cars</a>
            <a href="book_car.php">Book</a>
            <a href="my_bookings.php">My Bookings</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Book a Car</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" onsubmit="return validateDates()">
            <label for="car_id">Choose Car:</label>
            <select name="car_id" id="car_id" required>
                <option value="">-- Select a car --</option>
                <?php while ($car = $cars->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($car['id']) ?>">
                        <?= htmlspecialchars($car['name']) ?> - <?= htmlspecialchars($car['model']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="pickup_location">Pickup Location:</label>
            <input type="text" name="pickup_location" id="pickup_location" required placeholder="Enter pickup location">

            <label for="drop_location">Drop Location:</label>
            <input type="text" name="drop_location" id="drop_location" required placeholder="Enter drop location">

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required min="<?= date('Y-m-d') ?>">

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required min="<?= date('Y-m-d') ?>">

            <button type="submit">Book Car</button>
        </form>
    </div>

    <script>
        function validateDates() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            if (endDate < startDate) {
                alert("End date cannot be before start date.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
