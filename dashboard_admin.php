<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; // Make sure this uses mysqli connection $conn

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

function getCount($conn, $table, $interval = null) {
    if ($interval) {
        $sql = "SELECT COUNT(*) as total FROM $table WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)";
    } else {
        $sql = "SELECT COUNT(*) as total FROM $table";
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

$car_counts = [
    'all'     => getCount($conn, 'cars'),
    '1year'   => getCount($conn, 'cars', '1 YEAR'),
    '6months' => getCount($conn, 'cars', '6 MONTH'),
    '1month'  => getCount($conn, 'cars', '1 MONTH'),
    '1week'   => getCount($conn, 'cars', '1 WEEK'),
];

$user_counts = [
    'all'     => getCount($conn, 'users'),
    '1year'   => getCount($conn, 'users', '1 YEAR'),
    '6months' => getCount($conn, 'users', '6 MONTH'),
    '1month'  => getCount($conn, 'users', '1 MONTH'),
    '1week'   => getCount($conn, 'users', '1 WEEK'),
];
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
                <div class="stat-box">
                    <h3>All Time</h3>
                    <p><?php echo $car_counts['all']; ?> Cars</p>
                </div>
                <div class="stat-box">
                    <h3>1 Year</h3>
                    <p><?php echo $car_counts['1year']; ?> Cars</p>
                </div>
                <div class="stat-box">
                    <h3>6 Months</h3>
                    <p><?php echo $car_counts['6months']; ?> Cars</p>
                </div>
                <div class="stat-box">
                    <h3>1 Month</h3>
                    <p><?php echo $car_counts['1month']; ?> Cars</p>
                </div>
                <div class="stat-box">
                    <h3>1 Week</h3>
                    <p><?php echo $car_counts['1week']; ?> Cars</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>👥 Registered Users</h2>
            <div class="stats-grid">
                <div class="stat-box">
                    <h3>All Time</h3>
                    <p><?php echo $user_counts['all']; ?> Users</p>
                </div>
                <div class="stat-box">
                    <h3>1 Year</h3>
                    <p><?php echo $user_counts['1year']; ?> Users</p>
                </div>
                <div class="stat-box">
                    <h3>6 Months</h3>
                    <p><?php echo $user_counts['6months']; ?> Users</p>
                </div>
                <div class="stat-box">
                    <h3>1 Month</h3>
                    <p><?php echo $user_counts['1month']; ?> Users</p>
                </div>
                <div class="stat-box">
                    <h3>1 Week</h3>
                    <p><?php echo $user_counts['1week']; ?> Users</p>
                </div>
            </div>
        </div>

    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Car Rental System | Admin Panel
    </footer>

</body>
</html>
