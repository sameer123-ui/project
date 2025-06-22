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
    $start_time      = $_POST['start_time'];
    $end_date        = $_POST['end_date'];
    $end_time        = $_POST['end_time'];

    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');

    // Validate inputs
    if (empty($car_id) || empty($pickup_location) || empty($drop_location) || empty($start_date) || empty($start_time) || empty($end_date) || empty($end_time)) {
        $message = "Please fill in all fields.";
    } elseif ($start_date > $end_date || ($start_date == $end_date && $start_time >= $end_time)) {
        $message = "End date/time must be after start date/time.";
    } elseif ($start_date < $today || $end_date < $today) {
        $message = "Booking dates cannot be in the past.";
    } else {
        // Combine dates and times to datetime strings
        $startDateTime = $start_date . ' ' . $start_time;
        $endDateTime = $end_date . ' ' . $end_time;

        // Check overlapping bookings considering date and time
        $stmt = $conn->prepare("
            SELECT * FROM bookings 
            WHERE car_id = ?
              AND status = 'booked'
              AND (
                    (start_date < ? AND end_date > ?) OR
                    (start_date = ? AND start_time < ?) OR
                    (end_date = ? AND end_time > ?) OR
                    (start_date > ? AND end_date < ?)
              )
        ");

        // We need to check overlaps based on combined datetime ranges
        // To simplify, fetch bookings for the car and check overlap in PHP below

        // So we fetch all bookings for this car and then filter overlaps in PHP:

        $stmt2 = $conn->prepare("SELECT start_date, start_time, end_date, end_time FROM bookings WHERE car_id = ? AND status = 'booked' ORDER BY start_date, start_time");
        $stmt2->bind_param("i", $car_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        $overlap = false;
        while ($row = $result2->fetch_assoc()) {
            $bookingStart = new DateTime($row['start_date'] . ' ' . $row['start_time']);
            $bookingEnd = new DateTime($row['end_date'] . ' ' . $row['end_time']);
            $requestedStart = new DateTime($startDateTime);
            $requestedEnd = new DateTime($endDateTime);

            // Check for overlap condition
            if ($requestedStart < $bookingEnd && $requestedEnd > $bookingStart) {
                $overlap = true;
                break;
            }
        }
        $stmt2->close();

        if ($overlap) {
            $message = "This car is already booked for the selected date and time range.";
        } else {
            // Calculate total duration in hours (including partial days)
            $startDT = new DateTime($startDateTime);
            $endDT = new DateTime($endDateTime);
            $interval = $startDT->diff($endDT);
            $hours = ($interval->days * 24) + $interval->h + ($interval->i > 0 ? 1 : 0); // round up partial hour

            if ($hours == 0) $hours = 1; // minimum 1 hour charge

            // Get price per day (you may want to also have price per hour)
            $stmtPrice = $conn->prepare("SELECT price_per_day FROM cars WHERE id = ?");
            $stmtPrice->bind_param("i", $car_id);
            $stmtPrice->execute();
            $stmtPrice->bind_result($price_per_day);
            $stmtPrice->fetch();
            $stmtPrice->close();

            // Calculate total amount based on hourly price (assuming price_per_day/24)
            $price_per_hour = $price_per_day / 24;
            $total_amount = $price_per_hour * $hours;

            // Insert booking with times
            $stmtInsert = $conn->prepare("
                INSERT INTO bookings (user_id, car_id, start_date, start_time, end_date, end_time, pickup_location, drop_location, status, total_amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'booked', ?)
            ");
            $stmtInsert->bind_param("iissssssd", $user_id, $car_id, $start_date, $start_time, $end_date, $end_time, $pickup_location, $drop_location, $total_amount);

            if ($stmtInsert->execute()) {
                $message = "Car booked successfully!";
            } else {
                $message = "Error booking the car. Please try again.";
            }
            $stmtInsert->close();
        }
    }
}
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
        margin-left: 600px;
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
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            margin-left:-10px;
            font-weight: 600;
        }
        select, input[type="text"], input[type="date"], input[type="time"], button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            margin-left: -10px;
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
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post" onsubmit="return validateForm()">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required min="<?= date('Y-m-d') ?>">

            <label for="start_time">Start Time:</label>
            <input type="time" id="start_time" name="start_time" required value="09:00">

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required min="<?= date('Y-m-d') ?>">

            <label for="end_time">End Time:</label>
            <input type="time" id="end_time" name="end_time" required value="18:00">

            <label for="car_id">Choose Car:</label>
            <select name="car_id" id="car_id" required>
                <option value="">-- Select a car --</option>
                <!-- Options filled dynamically -->
            </select>

            <label for="pickup_location">Pickup Location:</label>
            <input type="text" name="pickup_location" id="pickup_location" required placeholder="Enter pickup location">

            <label for="drop_location">Drop Location:</label>
            <input type="text" name="drop_location" id="drop_location" required placeholder="Enter drop location">

            <button type="submit">Book Car</button>
        </form>
    </div>

    <script>
        function validateForm() {
            const startDate = document.getElementById('start_date').value;
            const startTime = document.getElementById('start_time').value;
            const endDate = document.getElementById('end_date').value;
            const endTime = document.getElementById('end_time').value;

            if (!startDate || !startTime || !endDate || !endTime) {
                alert("Please fill all date and time fields.");
                return false;
            }

            const startDateTime = new Date(startDate + 'T' + startTime);
            const endDateTime = new Date(endDate + 'T' + endTime);

            if (endDateTime <= startDateTime) {
                alert("End date/time must be after start date/time.");
                return false;
            }
            return true;
        }

        async function fetchAvailableCars() {
            const startDate = document.getElementById('start_date').value;
            const startTime = document.getElementById('start_time').value;
            const endDate = document.getElementById('end_date').value;
            const endTime = document.getElementById('end_time').value;

            const carSelect = document.getElementById('car_id');

            if (!startDate || !startTime || !endDate || !endTime) {
                carSelect.innerHTML = '<option value="">-- Select a car --</option>';
                return;
            }

            const startDateTime = new Date(startDate + 'T' + startTime);
            const endDateTime = new Date(endDate + 'T' + endTime);

            if (endDateTime <= startDateTime) {
                alert('End date/time must be after start date/time.');
                carSelect.innerHTML = '<option value="">-- Select a car --</option>';
                return;
            }

            try {
                const response = await fetch(`get_available_cars.php?start_date=${startDate}&start_time=${startTime}&end_date=${endDate}&end_time=${endTime}`);
                const cars = await response.json();

                carSelect.innerHTML = '<option value="">-- Select a car --</option>';

                if (cars.length === 0) {
                    carSelect.innerHTML = '<option value="">No cars available for selected date/time range</option>';
                    return;
                }

                cars.forEach(car => {
                    const option = document.createElement('option');
                    option.value = car.id;
                    option.textContent = `${car.name} - ${car.model}`;
                    carSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error fetching available cars:', error);
            }
        }

        document.getElementById('start_date').addEventListener('change', fetchAvailableCars);
        document.getElementById('start_time').addEventListener('change', fetchAvailableCars);
        document.getElementById('end_date').addEventListener('change', fetchAvailableCars);
        document.getElementById('end_time').addEventListener('change', fetchAvailableCars);

        window.addEventListener('DOMContentLoaded', fetchAvailableCars);
    </script>
</body>
</html>
