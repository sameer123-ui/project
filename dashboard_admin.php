<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; // your mysqli $conn connection

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    $sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '$table' 
            AND COLUMN_NAME = '$column'";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    return false;
}

// Modified getCount function that optionally uses a custom date column
function getCount($conn, $table, $interval = null, $extraCondition = '', $dateColumn = 'created_at') {
    // Check if the date column exists; if not, ignore interval filtering
    if ($interval && !columnExists($conn, $table, $dateColumn)) {
        $interval = null; // ignore interval if date column missing
    }

    $sql = "SELECT COUNT(*) as total FROM $table";
    if ($interval) {
        $sql .= " WHERE $dateColumn >= DATE_SUB(NOW(), INTERVAL $interval)";
        if ($extraCondition) {
            $sql .= " AND $extraCondition";
        }
    } elseif ($extraCondition) {
        $sql .= " WHERE $extraCondition";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    return $total;
}

// Car counts with intervals, using 'added_date' if exists, otherwise default
$car_date_col = columnExists($conn, 'cars', 'added_date') ? 'added_date' : 'created_at';

$car_counts = [
    'all'     => getCount($conn, 'cars', null, '', $car_date_col),
    '1year'   => getCount($conn, 'cars', '1 YEAR', '', $car_date_col),
    '6months' => getCount($conn, 'cars', '6 MONTH', '', $car_date_col),
    '1month'  => getCount($conn, 'cars', '1 MONTH', '', $car_date_col),
    '1week'   => getCount($conn, 'cars', '1 WEEK', '', $car_date_col),
];

// User counts with intervals (assume users have 'created_at' column)
$user_date_col = columnExists($conn, 'users', 'created_at') ? 'created_at' : null;

$user_counts = [
    'all'     => getCount($conn, 'users', null, '', $user_date_col),
    '1year'   => getCount($conn, 'users', '1 YEAR', '', $user_date_col),
    '6months' => getCount($conn, 'users', '6 MONTH', '', $user_date_col),
    '1month'  => getCount($conn, 'users', '1 MONTH', '', $user_date_col),
    '1week'   => getCount($conn, 'users', '1 WEEK', '', $user_date_col),
];

// Booking counts with intervals (assume bookings have 'created_at' column)
$booking_date_col = columnExists($conn, 'bookings', 'created_at') ? 'created_at' : null;

$booking_counts = [
    'all'     => getCount($conn, 'bookings', null, '', $booking_date_col),
    '1year'   => getCount($conn, 'bookings', '1 YEAR', '', $booking_date_col),
    '6months' => getCount($conn, 'bookings', '6 MONTH', '', $booking_date_col),
    '1month'  => getCount($conn, 'bookings', '1 MONTH', '', $booking_date_col),
    '1week'   => getCount($conn, 'bookings', '1 WEEK', '', $booking_date_col),
];

// Booking status counts (all time)
$status_counts = [
    'booked'    => getCount($conn, 'bookings', null, "status='booked'"),
    'completed' => getCount($conn, 'bookings', null, "status='completed'"),
    'cancelled' => getCount($conn, 'bookings', null, "status='cancelled'"),
];

// Total revenue from completed bookings (sum total_amount)
$stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE status='completed'");
$stmt->execute();
$stmt->bind_result($total_revenue);
$stmt->fetch();
$stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar {
            background: #343a40;
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
            flex: 1;
            padding: 30px;
            max-width: 1000px;
            margin: auto;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .stat-box {
            background: #e9ecef;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        .stat-box h3 {
            margin-bottom: 5px;
            color: #333;
        }
        .stat-box p {
            font-size: 18px;
            color: #007bff;
        }
        footer {
            background: #343a40;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div><strong>Admin Dashboard</strong></div>
        <div>
            <a href="dashboard_admin.php">Home</a>
            <a href="add_car.php">Add Car</a>
            <a href="view_cars.php">View Cars</a>
            <a href="view_bookings.php">View Bookings</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">

        <div class="card">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 👋</h2>
            <p>Here's a quick overview of the system stats:</p>
        </div>

        <div class="card">
            <h2>📦 Car Inventory</h2>
            <div class="stats-grid">
                <div class="stat-box"><h3>All Time</h3><p><?php echo $car_counts['all']; ?> Cars</p></div>
                <div class="stat-box"><h3>1 Year</h3><p><?php echo $car_counts['1year']; ?> Cars</p></div>
                <div class="stat-box"><h3>6 Months</h3><p><?php echo $car_counts['6months']; ?> Cars</p></div>
                <div class="stat-box"><h3>1 Month</h3><p><?php echo $car_counts['1month']; ?> Cars</p></div>
                <div class="stat-box"><h3>1 Week</h3><p><?php echo $car_counts['1week']; ?> Cars</p></div>
            </div>
        </div>

        <div class="card">
            <h2>👥 Registered Users</h2>
            <div class="stats-grid">
                <div class="stat-box"><h3>All Time</h3><p><?php echo $user_counts['all']; ?> Users</p></div>
                <div class="stat-box"><h3>1 Year</h3><p><?php echo $user_counts['1year']; ?> Users</p></div>
                <div class="stat-box"><h3>6 Months</h3><p><?php echo $user_counts['6months']; ?> Users</p></div>
                <div class="stat-box"><h3>1 Month</h3><p><?php echo $user_counts['1month']; ?> Users</p></div>
                <div class="stat-box"><h3>1 Week</h3><p><?php echo $user_counts['1week']; ?> Users</p></div>
            </div>
        </div>

        <div class="card">
            <h2>📅 Bookings</h2>
            <div class="stats-grid">
                <div class="stat-box"><h3>All Time</h3><p><?php echo $booking_counts['all']; ?> Bookings</p></div>
                <div class="stat-box"><h3>1 Year</h3><p><?php echo $booking_counts['1year']; ?> Bookings</p></div>
                <div class="stat-box"><h3>6 Months</h3><p><?php echo $booking_counts['6months']; ?> Bookings</p></div>
                <div class="stat-box"><h3>1 Month</h3><p><?php echo $booking_counts['1month']; ?> Bookings</p></div>
                <div class="stat-box"><h3>1 Week</h3><p><?php echo $booking_counts['1week']; ?> Bookings</p></div>
            </div>
        </div>

        <div class="card">
            <h2>📊 Booking Status</h2>
            <div class="stats-grid">
                <div class="stat-box"><h3>Booked</h3><p><?php echo $status_counts['booked']; ?></p></div>
                <div class="stat-box"><h3>Completed</h3><p><?php echo $status_counts['completed']; ?></p></div>
                <div class="stat-box"><h3>Cancelled</h3><p><?php echo $status_counts['cancelled']; ?></p></div>
                <div class="stat-box"><h3>Total Revenue</h3><p>$<?php echo number_format($total_revenue, 2); ?></p></div>
            </div>
        </div>

    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Car Rental System | Admin Panel
    </footer>

</body>
</html>
