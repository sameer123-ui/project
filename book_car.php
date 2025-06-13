<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Check if car ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Car ID.");
}

$car_id = (int)$_GET['id'];

// Fetch car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ? AND status = 'available'");
$stmt->bind_param('i', $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Car not found or not available.");
}

$car = $result->fetch_assoc();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    // Validate dates
    if (!$start_date || !$end_date) {
        $error = "Please select both start and end dates.";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be after end date.";
    } elseif ($start_date < date('Y-m-d')) {
        $error = "Start date cannot be in the past.";
    } else {
        // Calculate number of days
        $diff = (strtotime($end_date) - strtotime($start_date)) / (60*60*24) + 1;
        $total_amount = $diff * $car['price_per_day'];

        // Check if car is already booked for given dates
        $stmt2 = $conn->prepare("SELECT * FROM bookings WHERE car_id = ? AND NOT (end_date < ? OR start_date > ?)");
        $stmt2->bind_param('iss', $car_id, $start_date, $end_date);
        $stmt2->execute();
        $conflicts = $stmt2->get_result();

        if ($conflicts->num_rows > 0) {
            $error = "Sorry, this car is already booked for the selected dates.";
        } else {
            // Insert booking
            $stmt3 = $conn->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, total_amount) VALUES (?, ?, ?, ?, ?)");
            $stmt3->bind_param('iissd', $user['id'], $car_id, $start_date, $end_date, $total_amount);

            if ($stmt3->execute()) {
                $message = "Booking successful! Total amount: $" . number_format($total_amount, 2);
            } else {
                $error = "Failed to book the car. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Car - <?php echo htmlspecialchars($car['name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0; padding: 0px;
        }
        .navbar {
            background: #0069d9;
            color: white;
            padding: 15px 20px;
            display: flex; justify-content: space-between; align-items: center;
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
            max-width: 600px;
            background: white;
            padding: 25px;
            margin: 30px auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type=date] {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            padding: 10px 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .message {
            margin-top: 20px;
            color: green;
        }
        .error {
            margin-top: 20px;
            color: red;
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
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Book Car: <?php echo htmlspecialchars($car['name']); ?> (<?php echo htmlspecialchars($car['model']); ?>)</h2>
    <p>Price per day: $<?php echo number_format($car['price_per_day'], 2); ?></p>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
        <p><a href="my_bookings.php">View My Bookings</a></p>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" required>

            <button type="submit">Book Now</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
