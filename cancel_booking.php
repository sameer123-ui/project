<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my_bookings.php');
    exit;
}

$booking_id = (int) $_GET['id'];

// Check if booking belongs to user and is currently booked
$stmt = $conn->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Booking not found or doesn't belong to user
    header('Location: my_bookings.php');
    exit;
}

$booking = $result->fetch_assoc();

if ($booking['status'] !== 'booked') {
    // Cannot cancel if already cancelled or other status
    header('Location: my_bookings.php');
    exit;
}

// Update status to cancelled
$update_stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
$update_stmt->bind_param("i", $booking_id);
if ($update_stmt->execute()) {
    $_SESSION['message'] = "Booking #$booking_id cancelled successfully.";
} else {
    $_SESSION['message'] = "Failed to cancel booking #$booking_id.";
}

header('Location: my_bookings.php');
exit;
