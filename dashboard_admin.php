<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

function columnExists($conn, $table, $column) {
    $sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '$table' 
            AND COLUMN_NAME = '$column'";
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc()['count'] > 0 : false;
}

function getCount($conn, $table, $interval = null, $extraCondition = '', $dateColumn = 'created_at') {
    if ($interval && !columnExists($conn, $table, $dateColumn)) {
        $interval = null;
    }

    $sql = "SELECT COUNT(*) as total FROM $table";
    if ($interval) {
        $sql .= " WHERE $dateColumn >= DATE_SUB(NOW(), INTERVAL $interval)";
        if ($extraCondition) $sql .= " AND $extraCondition";
    } elseif ($extraCondition) {
        $sql .= " WHERE $extraCondition";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    return $total;
}

$car_date_col = columnExists($conn, 'cars', 'added_date') ? 'added_date' : 'created_at';
$user_date_col = columnExists($conn, 'users', 'created_at') ? 'created_at' : null;
$booking_date_col = columnExists($conn, 'bookings', 'created_at') ? 'created_at' : null;

$carCount = getCount($conn, 'cars', null, '', $car_date_col);
$userCount = getCount($conn, 'users', null, '', $user_date_col);
$bookingCount = getCount($conn, 'bookings', null, '', $booking_date_col);
$completedCount = getCount($conn, 'bookings', null, "status='completed'");
$cancelledCount = getCount($conn, 'bookings', null, "status='cancelled'");
$bookedCount = getCount($conn, 'bookings', null, "status='booked'");

$stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE status='completed'");
$stmt->execute();
$stmt->bind_result($revenue);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0f2f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
        }

        .navbar {
            background-color: #0069d9;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar a {
            color: #ecf0f1;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .navbar a:hover {
            color: #1abc9c;
        }

        .container {
            flex: 1;
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px 40px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }

        .card h2 {
            margin-bottom: 20px;
            color: #34495e;
            font-weight: 700;
        }

        .card p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #666;
        }

        .stats-grid {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .stat-box {
            flex: 1 1 30%;
            background-color: #f7fafc;
            border-radius: 12px;
            padding: 25px 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: default;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .stat-box h3 {
            font-size: 1.3rem;
            margin-bottom: 12px;
            color: #2c3e50;
        }

        .stat-box p {
            font-size: 2.2rem;
            font-weight: 700;
            user-select: none;
        }

        .stat-box.total p { color: #3498db; }
        .stat-box.users p { color: #9b59b6; }
        .stat-box.bookings p { color: #1abc9c; }
        .stat-box.completed p { color: #2ecc71; }
        .stat-box.cancelled p { color: #e74c3c; }
        .stat-box.revenue p { color: #f39c12; }

        footer {
            background-color: #2c3e50;
            color: #ecf0f1;
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
            font-size: 0.9rem;
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
            <a href="view_users.php">View Users</a>
                    <a href="view_revenue.php">View Revenue</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 👋</h2>
            <p>Monitor and manage the platform's activity including cars, users, bookings, and revenue.</p>
        </div>
<div class="card">
    <h2>System Overview</h2>
    <div class="stats-grid">
        <a href="view_cars.php" class="stat-box total" title="View all cars">
            <h3>Total Cars</h3>
            <p><?php echo $carCount; ?></p>
        </a>
        <a href="view_users.php" class="stat-box users" title="View all users">
            <h3>Total Users</h3>
            <p><?php echo $userCount; ?></p>
        </a>
        <a href="view_bookings.php" class="stat-box bookings" title="View all bookings">
            <h3>Total Bookings</h3>
            <p><?php echo $bookingCount; ?></p>
        </a>
        <a href="view_bookings.php?status=completed" class="stat-box completed" title="View completed bookings">
            <h3>Completed</h3>
            <p><?php echo $completedCount; ?></p>
        </a>
        <a href="view_bookings.php?status=cancelled" class="stat-box cancelled" title="View cancelled bookings">
            <h3>Cancelled</h3>
            <p><?php echo $cancelledCount; ?></p>
        </a>
        <a href="view_revenue.php" class="stat-box revenue" title="View revenue details">
            <h3>Total Revenue</h3>
            <p>Rs <?php echo number_format($revenue, 2); ?></p>
        </a>
    </div>
</div>

    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Car Rental System | Admin Panel
    </footer>

</body>
</html>
