<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: view_cars.php');
    exit;
}

$message = '';

$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();
$stmt->close();

if (!$car) {
    header('Location: view_cars.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $model = $_POST['model'] ?? '';
    $price_per_day = floatval($_POST['price_per_day'] ?? 0);
    $status = $_POST['status'] ?? 'available';

    if ($name && $price_per_day > 0 && in_array($status, ['available','booked'])) {
        $stmt = $conn->prepare("UPDATE cars SET name = ?, model = ?, price_per_day = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $name, $model, $price_per_day, $status, $id);
        if ($stmt->execute()) {
            $message = "Car updated successfully.";
            // Refresh the car data
            $car['name'] = $name;
            $car['model'] = $model;
            $car['price_per_day'] = $price_per_day;
            $car['status'] = $status;
        } else {
            $message = "Error updating car: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill in the required fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Car - Admin</title>
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

.container {
    max-width: 600px;          /* limit form width */
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    margin: auto;              /* center horizontally */
    box-sizing: border-box;
}

label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}

input[type=text],
input[type=number],
select {
    width: 100%;
    padding: 8px;
    margin-top: 6px;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

button {
    margin-top: 20px;
    padding: 10px 15px;
    background: #007bff;
    border: none;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background: #0056b3;
}

.message {
    margin-top: 15px;
    color: green;
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
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Edit Car #<?php echo $car['id']; ?></h2>
        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="name">Name *</label>
            <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($car['name']); ?>">

            <label for="model">Model</label>
            <input type="text" name="model" id="model" value="<?php echo htmlspecialchars($car['model']); ?>">

            <label for="price_per_day">Price per Day *</label>
            <input type="number" step="0.01" name="price_per_day" id="price_per_day" required value="<?php echo htmlspecialchars($car['price_per_day']); ?>">

            <label for="status">Status *</label>
            <select name="status" id="status" required>
                <option value="available" <?php if($car['status'] === 'available') echo 'selected'; ?>>Available</option>
                <option value="booked" <?php if($car['status'] === 'booked') echo 'selected'; ?>>Booked</option>
            </select>

            <button type="submit">Update Car</button>
        </form>
        <p><a href="view_cars.php">Back to Cars List</a></p>
    </div>
</body>
</html>
