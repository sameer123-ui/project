<?php
header('Content-Type: application/json');
include 'db.php';

if (
    !isset($_GET['start_date'], $_GET['start_time'], $_GET['end_date'], $_GET['end_time'])
) {
    echo json_encode([]);
    exit;
}

$start_date = $_GET['start_date'];
$start_time = $_GET['start_time'];
$end_date   = $_GET['end_date'];
$end_time   = $_GET['end_time'];

$start_datetime = $start_date . ' ' . $start_time;
$end_datetime   = $end_date . ' ' . $end_time;

// Validate format
$datetime_regex = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/';
if (!preg_match($datetime_regex, $start_datetime) || !preg_match($datetime_regex, $end_datetime)) {
    echo json_encode([]);
    exit;
}

// Fetch all cars
$cars_result = $conn->query("SELECT * FROM cars");
$available_cars = [];

while ($car = $cars_result->fetch_assoc()) {
    $car_id = $car['id'];

    // Check for overlapping bookings for this car
    $stmt = $conn->prepare("
        SELECT * FROM bookings 
        WHERE car_id = ? 
        AND status = 'booked'
        AND (
            (? < CONCAT(end_date, ' ', end_time)) AND 
            (? > CONCAT(start_date, ' ', start_time))
        )
    ");
    $stmt->bind_param("iss", $car_id, $start_datetime, $end_datetime);
    $stmt->execute();
    $conflict_result = $stmt->get_result();

    if ($conflict_result->num_rows === 0) {
        $available_cars[] = $car;
    }

    $stmt->close();
}

echo json_encode($available_cars);
?>
