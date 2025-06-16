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
    $car_id     = $_POST['car_id'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];

    $today = date('Y-m-d');

    // Basic date validation
    if (empty($car_id) || empty($start_date) || empty($end_date)) {
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
            $stmt = $conn->prepare("
                INSERT INTO bookings (user_id, car_id, start_date, end_date, status)
                VALUES (?, ?, ?, ?, 'booked')
            ");
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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0px;
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
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        select, input[type="date"], button {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        button {
            background-color: #0069d9;
            color: white;
            border: none;
            cursor: pointer;
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
               <a href="profile.php">Profle</a>
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
            <select name="car_id" required>
                <option value="">-- Select a car --</option>
                <?php while ($car = $cars->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($car['id']) ?>">
                        <?= htmlspecialchars($car['name']) ?> - <?= htmlspecialchars($car['model']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

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
