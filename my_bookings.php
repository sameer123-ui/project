<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Handle cancellation
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancel_id = intval($_POST['cancel_id']);
    // Verify booking belongs to user and is cancellable
    $stmt = $conn->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cancel_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $booking = $result->fetch_assoc();
        if (in_array($booking['status'], ['booked', 'confirmed'])) {
            $update = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $update->bind_param("i", $cancel_id);
            $update->execute();
            $message = "Booking cancelled successfully.";
        } else {
            $message = "This booking cannot be cancelled.";
        }
    } else {
        $message = "Booking not found or unauthorized.";
    }
}

// Fetch bookings
$stmt = $conn->prepare("
    SELECT b.id, c.name AS car_name, b.pickup_location, b.drop_location, b.start_date, b.end_date, b.booking_date, b.status, b.total_amount
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0; padding: 0;
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
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 30px 15px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        h2 {
            margin-bottom: 25px;
            color: #0069d9;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            font-size: 16px;
            background: white;
            table-layout: fixed;
        }

        thead tr {
            background: #0d6efd;
            color: white;
            text-align: left;
            font-weight: 600;
        }

        thead th {
            padding: 15px 10px;
            user-select: none;
            text-align: center;
        }

        tbody tr {
            border-bottom: 1px solid #ddd;
            transition: background-color 0.3s ease;
            cursor: default;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #e7f0ff;
        }

        tbody td {
            padding: 14px 10px;
            vertical-align: middle;
            color: #333;
            word-wrap: break-word;
            text-align: center;
        }

        .status-booked {
            color: #0d6efd;
            font-weight: 700;
        }

        .status-confirmed {
            color: #198754;
            font-weight: 700;
        }

        .status-cancelled {
            color: #dc3545;
            font-weight: 700;
        }

        .status-completed {
            color: #6c757d;
            font-weight: 700;
        }

        .action-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.25s ease;
        }

        .action-btn:hover {
            background: #b02a37;
        }

        .message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
        }

        .success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1.5px solid #10b981;
        }

        .error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1.5px solid #ef4444;
        }

        @media (max-width: 600px) {
            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <strong>User Dashboard</strong>
        <nav>
            <a href="dashboard_user.php">Home</a>
            <a href="index.php">Browse Cars</a>
            <a href="book_car.php">Book</a>
            <a href="my_bookings.php">My Bookings</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <h2>My Bookings</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Car Name</th>
                        <th>Pickup Location</th>
                        <th>Drop Location</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Booking Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $result->fetch_assoc()): ?>
                        <?php
                        // Show "Pending" for "booked" status
                        $display_status = $booking['status'] === 'booked' ? 'Pending' : ucfirst($booking['status']);
                        ?>
                        <tr>
                            <td><?= $booking['id']; ?></td>
                            <td><?= htmlspecialchars($booking['car_name']); ?></td>
                            <td><?= htmlspecialchars($booking['pickup_location']); ?></td>
                            <td><?= htmlspecialchars($booking['drop_location']); ?></td>
                            <td><?= htmlspecialchars($booking['start_date']); ?></td>
                            <td><?= htmlspecialchars($booking['end_date']); ?></td>
                            <td><?= $booking['booking_date']; ?></td>
                            <td>Rs <?= number_format($booking['total_amount'], 2); ?></td>
                            <td class="status-<?= $booking['status']; ?>"><?= $display_status ?></td>
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
             </div>
        <?php else: ?>
            <p>You have no bookings yet.</p>
        <?php endif; ?>
    </div>

</body>
</html>
