<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Updated query assuming start_time and end_time columns exist
$sql = "SELECT b.id, u.name AS user_name, c.name AS car_name, 
               b.start_date, b.end_date, b.start_time, b.end_time, b.booking_date,
               b.status, b.total_amount, b.pickup_location, b.drop_location
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
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: #0069d9;
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

        .container {
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            table-layout: fixed;
        }

        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        button {
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 5px;
        }

        button.cancel {
            background-color: #dc3545;
        }

        button.complete {
            background-color: #28a745;
        }

        button.confirm {
            background-color: #ffc107;
            color: black;
        }

        button:hover {
            opacity: 0.85;
        }

        form {
            margin: 0;
            display: inline;
        }

        .status-booked {
            color: #0d6efd;
            font-weight: bold;
        }
        .status-confirmed {
            color: #198754;
            font-weight: bold;
        }
        .status-cancelled {
            color: #dc3545;
            font-weight: bold;
        }
        .status-completed {
            color: #6c757d;
            font-weight: bold;
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
        <h2>All Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Car Name</th>
                    <th>Start Date & Time</th>
                    <th>End Date & Time</th>
                    <th>Booking Date</th>
                    <th>Pickup Location</th>
                    <th>Drop Location</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= htmlspecialchars($row['car_name']) ?></td>
                    <td><?= htmlspecialchars($row['start_date']) ?> <?= isset($row['start_time']) ? htmlspecialchars($row['start_time']) : '' ?></td>
                    <td><?= htmlspecialchars($row['end_date']) ?> <?= isset($row['end_time']) ? htmlspecialchars($row['end_time']) : '' ?></td>
                    <td><?= htmlspecialchars($row['booking_date']) ?></td>
                    <td><?= htmlspecialchars($row['pickup_location']) ?></td>
                    <td><?= htmlspecialchars($row['drop_location']) ?></td>
                    <td>Rs <?= number_format($row['total_amount'], 2) ?></td>
                    <td class="status-<?= htmlspecialchars($row['status']); ?>">
                        <?= htmlspecialchars(ucfirst($row['status'])) ?>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'booked'): ?>
                            <form method="post" action="admin_booking_action.php" onsubmit="return confirm('Confirm this booking?');" style="display:inline-block;">
                                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="confirm" class="confirm">Confirm</button>
                            </form>
                            <form method="post" action="admin_booking_action.php" onsubmit="return confirm('Cancel this booking?');" style="display:inline-block;">
                                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="cancel" class="cancel">Cancel</button>
                            </form>
                        <?php elseif ($row['status'] === 'confirmed'): ?>
                            <form method="post" action="admin_booking_action.php" onsubmit="return confirm('Cancel this booking?');" style="display:inline-block;">
                                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="cancel" class="cancel">Cancel</button>
                            </form>
                            <form method="post" action="admin_booking_action.php" onsubmit="return confirm('Mark booking as completed?');" style="display:inline-block;">
                                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="complete" class="complete">Complete</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
