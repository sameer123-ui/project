<?php
session_start();
require 'db.php';

// Check user login & role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Query bookings joined with cars to get car info
$sql = "SELECT b.id, c.name AS car_name, b.start_date, b.end_date, b.total_amount, b.booking_date
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f1f1f1; }
        .navbar {
            background: #0069d9; color: white; padding: 15px 20px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
        }
        .navbar a {
            color: white; text-decoration: none; margin-left: 15px; font-weight: bold;
        }
        .navbar a:hover { text-decoration: underline; }
        footer {
            text-align: center; padding: 15px; margin-top: 900px;
            background: #0069d9; color: white;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div><strong>User Dashboard</strong></div>
        <div>
            <a href="dashboard_user.php">Home</a>
            <a href="index.php">Browse Cars</a>
            <a href="my_bookings.php">My Bookings</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h2>My Bookings</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Car Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Amount</th>
                    <th>Booking Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($booking = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $booking['id']; ?></td>
                    <td><?php echo htmlspecialchars($booking['car_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($booking['end_date']); ?></td>
                    <td><?php echo number_format($booking['total_amount'], 2); ?></td>
                    <td><?php echo $booking['booking_date']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no bookings yet.</p>
    <?php endif; ?>

    <footer>
        &copy; <?php echo date('Y'); ?> Car Rental System | User Panel
    </footer>
</body>
</html>
