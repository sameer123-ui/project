
<?php
session_start();
require 'db.php';

// Optional: check if user is logged in, or just show cars
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
//    header('Location: login.php');
//    exit;
//}

$result = $conn->query("SELECT * FROM cars WHERE status = 'available' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Cars</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0; padding: 0px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        a.button {
            padding: 8px 15px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a.button:hover {
            background: #218838;
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
        <a href="logout.php">Logout</a>
    </div>
    </div>
    <h2>Available Cars for Rent</h2>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Model</th>
                <th>Price Per Day</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($car = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($car['name']); ?></td>
                    <td><?php echo htmlspecialchars($car['model']); ?></td>
                    <td>$<?php echo number_format($car['price_per_day'], 2); ?></td>
                    <td><a class="button" href="book_car.php?id=<?php echo $car['id']; ?>">Book Now</a></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">No cars available at the moment.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
