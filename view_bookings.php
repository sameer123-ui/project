<?php
session_start();
require 'db.php';

// Only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$sql = "SELECT b.id, u.name AS user_name, c.name AS car_name, b.start_date, b.end_date, b.total_amount, b.booking_date
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN cars c ON b.car_id = c.id
        ORDER BY b.booking_date DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Bookings</title>
    <style>
       body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;          /* Remove default margin to avoid gaps */
    padding: 0;         /* Remove body padding so navbar touches edges */
}

.navbar {
    background: #343a40;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    box-sizing: border-box;
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

/* Wrap your page content in a container div for padding */
.container {
    padding: 20px;
}

/* Table styles */
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
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h2>All Bookings</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User Name</th>
                <th>Car Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Amount</th>
                <th>Booking Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
