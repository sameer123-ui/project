<?php
session_start();
require 'db.php';

// Only admin access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Calculate total revenue from completed bookings
$sqlRevenue = "SELECT SUM(total_amount) AS total_revenue FROM bookings WHERE status = 'completed'";
$resultRevenue = $conn->query($sqlRevenue);
$totalRevenue = 0;
if ($resultRevenue && $row = $resultRevenue->fetch_assoc()) {
    $totalRevenue = $row['total_revenue'] ?? 0;
}

// Fetch all completed bookings with user and car info
$sqlBookings = "
    SELECT b.id, u.name AS user_name, c.name AS car_name, b.start_date, b.end_date, b.booking_date, b.total_amount
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    WHERE b.status = 'completed'
    ORDER BY b.booking_date DESC
";
$resultBookings = $conn->query($sqlBookings);

if (!$resultBookings) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>View Revenue</title>
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
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
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
    h2 {
        color: #333;
        margin-bottom: 15px;
    }
    .revenue-summary {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        font-size: 1.5rem;
        font-weight: bold;
        color: #28a745;
        text-align: center;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    th {
        background-color: #007bff;
        color: white;
    }
    tr:hover {
        background-color: #f1f1f1;
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

<h2>Total Revenue from Completed Bookings</h2>
<div class="revenue-summary">
    Rs <?= number_format($totalRevenue, 2) ?>
</div>

<h2>Completed Bookings Details</h2>

<?php if ($resultBookings->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>Booking ID</th>
            <th>User Name</th>
            <th>Car Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Booking Date</th>
            <th>Total Amount (Rs)</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($booking = $resultBookings->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($booking['id']) ?></td>
            <td><?= htmlspecialchars($booking['user_name']) ?></td>
            <td><?= htmlspecialchars($booking['car_name']) ?></td>
            <td><?= htmlspecialchars($booking['start_date']) ?></td>
            <td><?= htmlspecialchars($booking['end_date']) ?></td>
            <td><?= htmlspecialchars($booking['booking_date']) ?></td>
            <td><?= number_format($booking['total_amount'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
    <p>No completed bookings found.</p>
<?php endif; ?>

</body>
</html>
