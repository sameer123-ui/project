<?php
session_start();
require 'db.php';

// Check user login & role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Handle cancel request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancel_id = intval($_POST['cancel_id']);
    // Allow cancellation if status is booked or confirmed
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('booked', 'confirmed')");
    $stmt->bind_param("ii", $cancel_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Query bookings
$sql = "SELECT b.id, c.name AS car_name, b.start_date, b.end_date, b.booking_date, b.status
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
        .action-btn {
            background-color: #dc3545; color: white; border: none;
            padding: 5px 10px; border-radius: 5px; cursor: pointer;
        }
        .action-btn:hover { background-color: #c82333; }
        .status-booked { color: green; font-weight: bold; }
        .status-confirmed { color: orange; font-weight: bold; }
        .status-cancelled { color: red; font-weight: bold; }
        .status-completed { color: gray; font-weight: bold; }
        footer {
            text-align: center; padding: 15px; margin-top: 100px;
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
            <a href="book_car.php">Book</a>
            <a href="my_bookings.php">My Bookings</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div style="padding: 20px;">
        <h2>My Bookings</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Car Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $booking['id']; ?></td>
                        <td><?= htmlspecialchars($booking['car_name']); ?></td>
                        <td><?= htmlspecialchars($booking['start_date']); ?></td>
                        <td><?= htmlspecialchars($booking['end_date']); ?></td>
                        <td><?= $booking['booking_date']; ?></td>
                        <td class="status-<?= $booking['status']; ?>">
                            <?= ucfirst($booking['status']); ?>
                        </td>
                        <td>
                            <?php if (in_array($booking['status'], ['booked', 'confirmed'])): ?>
                                <form method="post" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="cancel_id" value="<?= $booking['id']; ?>">
                                    <button type="submit" class="action-btn">Cancel</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no bookings yet.</p>
        <?php endif; ?>
    </div>

    <footer>
        &copy; <?= date('Y'); ?> Car Rental System | User Panel
    </footer>
</body>
</html>
