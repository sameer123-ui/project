<?php
include 'db.php';
session_start();

// Access control using the proper session keys
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    echo "Access denied.";
    exit;
}

$user_id = $_SESSION['user']['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Validate date input
    if ($end_date < $start_date) {
        $message = "End date cannot be before start date.";
    } else {
        // Check for overlapping bookings
        $stmt = $conn->prepare("SELECT * FROM bookings 
            WHERE car_id = ? AND status = 'booked' 
            AND start_date <= ? AND end_date >= ?");
        $stmt->bind_param("iss", $car_id, $end_date, $start_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This car is already booked for the selected dates.";
        } else {
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, car_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'booked')");
            $stmt->bind_param("iiss", $user_id, $car_id, $start_date, $end_date);
            if ($stmt->execute()) {
                $message = "Car booked successfully!";
            } else {
                $message = "Error booking the car. Please try again.";
            }
        }
    }
}

// Fetch all cars for selection
$cars = $conn->query("SELECT * FROM cars");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Car</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 0px; }
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
        .message { color: red; margin-bottom: 20px; }
        form { margin-top: 20px; }
        select, input { margin: 5px 0; padding: 8px; width: 200px; }
        button { padding: 8px 16px; }
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

    <h2>Book a Car</h2>

    <?php if (!empty($message)) echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; ?>

    <form method="post" onsubmit="return validateDates()">
        <label>Choose Car:</label><br>
        <select name="car_id" required>
            <?php while ($car = $cars->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($car['id']) ?>">
                    <?= htmlspecialchars($car['car_name']) ?> - <?= htmlspecialchars($car['model']) ?>
                </option>
            <?php endwhile; ?>
        </select><br>

        <label>Start Date:</label><br>
        <input type="date" id="start_date" name="start_date" required min="<?= date('Y-m-d') ?>"><br>

        <label>End Date:</label><br>
        <input type="date" id="end_date" name="end_date" required min="<?= date('Y-m-d') ?>"><br>

        <button type="submit">Book Car</button>
    </form>

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
