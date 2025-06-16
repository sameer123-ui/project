<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['car_name'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $price_per_day = floatval($_POST['price_per_day'] ?? 0);

    if ($name && $price_per_day > 0) {
        $stmt = $conn->prepare("INSERT INTO cars (name, model, price_per_day) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $model, $price_per_day);
        if ($stmt->execute()) {
            $message = "Car added successfully.";
        } else {
            $message = "Error adding car: " . $conn->error;
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Car - Admin</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0; padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #f4f6f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar {
            background: #343a40;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-weight: 600;
            transition: color 0.3s;
        }
        .navbar a:hover {
            color: #adb5bd;
            text-decoration: underline;
        }
        .container {
            flex: 1;
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px 35px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 25px;
            color: #212529;
            text-align: center;
            font-weight: 700;
        }
        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        form input[type="text"],
        form input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1.5px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        form input[type="text"]:focus,
        form input[type="number"]:focus {
            border-color: #007bff;
            outline: none;
        }
        button[type="submit"] {
            width: 100%;
            background: #007bff;
            color: white;
            font-size: 18px;
            font-weight: 700;
            padding: 12px 0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button[type="submit"]:hover {
            background: #0056b3;
        }
        .message {
            margin-bottom: 20px;
            font-weight: 600;
            color: green;
            text-align: center;
        }
        .error {
            color: #dc3545;
        }
        p.back-link {
            margin-top: 15px;
            text-align: center;
        }
        p.back-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }
        p.back-link a:hover {
            text-decoration: underline;
        }
        footer {
            background: #343a40;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: auto;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div><strong>Admin Dashboard</strong></div>
        <div>
            <a href="dashboard_admin.php">Home</a>
            <a href="add_car.php">Add Car</a>
            <a href="view_cars.php">View Cars</a>
            <a href="view_bookings.php">View Bookings</a>
               <a href="profile.php">Profle</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="container">
        <h2>Add New Car</h2>
        <?php if ($message): ?>
            <p class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="car_name">Car Name *</label>
            <input type="text" name="car_name" id="car_name" required placeholder="e.g., Toyota Corolla" />

            <label for="model">Model</label>
            <input type="text" name="model" id="model" placeholder="e.g., 2020 XLE" />

            <label for="price_per_day">Price per Day *</label>
            <input type="number" name="price_per_day" id="price_per_day" step="0.01" min="0" required placeholder="e.g., 50.00" />

            <button type="submit">Add Car</button>
        </form>
        <p class="back-link"><a href="dashboard_admin.php">← Back to Dashboard</a></p>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> Car Rental Management System
    </footer>
</body>
</html>
