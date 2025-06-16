<?php
session_start();
require 'db.php';

// Only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action'];

    if (!in_array($action, ['confirm', 'cancel', 'complete'])) {
        die('Invalid action.');
    }

    // Prepare status update based on action
    if ($action === 'confirm') {
        $new_status = 'confirmed';
        $allowed_current = ['booked']; // Only booked can be confirmed
    } elseif ($action === 'cancel') {
        $new_status = 'cancelled';
        $allowed_current = ['booked', 'confirmed']; // Can cancel booked or confirmed
    } elseif ($action === 'complete') {
        $new_status = 'completed';
        $allowed_current = ['confirmed']; // Only confirmed can be completed
    }

    // Check current status first
    $stmt = $conn->prepare("SELECT status FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        die('Booking not found.');
    }
    $row = $result->fetch_assoc();
    $current_status = $row['status'];
    $stmt->close();

    if (!in_array($current_status, $allowed_current)) {
        die('Action not allowed for current booking status.');
    }

    // Update status
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: view_bookings.php?msg=success');
        exit;
    } else {
        $stmt->close();
        die('Failed to update booking status.');
    }
} else {
    die('Invalid request method.');
}
