<?php
session_start();
require 'db.php';
require('fpdf.php');

// Check user login & role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['booking_id'])) {
    die('Booking ID is required');
}

$booking_id = intval($_GET['booking_id']);
$user_id = $_SESSION['user']['id'];

// Fetch booking and car details for this user and booking id
$sql = "SELECT b.id, b.start_date, b.end_date, b.total_amount, b.booking_date, c.name AS car_name, c.model, c.daily_rate
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        WHERE b.id = ? AND b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Booking not found or access denied.');
}

$booking = $result->fetch_assoc();

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Car Rental Invoice', 0, 1, 'C');
$pdf->Ln(10);

// Booking info
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Booking ID:', 0, 0);
$pdf->Cell(0, 10, $booking['id'], 0, 1);

$pdf->Cell(50, 10, 'Booking Date:', 0, 0);
$pdf->Cell(0, 10, $booking['booking_date'], 0, 1);

$pdf->Cell(50, 10, 'Car Name:', 0, 0);
$pdf->Cell(0, 10, $booking['car_name'] . ' (' . $booking['model'] . ')', 0, 1);

$pdf->Cell(50, 10, 'Start Date:', 0, 0);
$pdf->Cell(0, 10, $booking['start_date'], 0, 1);

$pdf->Cell(50, 10, 'End Date:', 0, 0);
$pdf->Cell(0, 10, $booking['end_date'], 0, 1);

$start = new DateTime($booking['start_date']);
$end = new DateTime($booking['end_date']);
$diff = $end->diff($start)->days + 1; // include end date

$pdf->Cell(50, 10, 'Total Days:', 0, 0);
$pdf->Cell(0, 10, $diff, 0, 1);

$pdf->Cell(50, 10, 'Daily Rate:', 0, 0);
$pdf->Cell(0, 10, '$' . number_format($booking['daily_rate'], 2), 0, 1);

$pdf->Cell(50, 10, 'Total Amount:', 0, 0);
$pdf->Cell(0, 10, '$' . number_format($booking['total_amount'], 2), 0, 1);

$pdf->Ln(10);
$pdf->Cell(0, 10, 'Thank you for choosing our service!', 0, 1, 'C');

// Output PDF directly to browser
$pdf->Output('I', 'invoice_' . $booking['id'] . '.pdf');
exit;
?>
