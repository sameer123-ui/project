<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$result = $conn->query("SELECT * FROM cars ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>View Cars - Admin</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 0; /* Remove padding here to avoid gap around navbar */
}

/* Navbar stretches full width without gaps */
.navbar {
    background: #0069d9;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
}

/* Container for page content with padding */
.container {
    padding: 20px; /* Padding for content only, below navbar */
}

/* Navbar links */
.navbar a {
    color: white;
    text-decoration: none;
    margin-left: 15px;
    font-weight: bold;
}

.navbar a:hover {
    text-decoration: underline;
}

/* Table styles */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #007bff;
    color: white;
}

tr:hover {
    background: #f1f1f1;
}

/* Buttons */
a.button {
    display: inline-block;
    padding: 8px 16px;
    background: #007bff;
    color: white;
    border-radius: 4px;
    text-decoration: none;
    margin-bottom: 15px;
}

a.button:hover {
    background: #0056b3;
}

.actions a {
    margin-right: 10px;
    color: #007bff;
    text-decoration: none;
}

.actions a:hover {
    text-decoration: underline;
}

footer {
    text-align: center;
    padding: 15px;
    margin-top: 40px;
    background: #343a40;
    color: white;
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
               <a href="profile.php">Profle</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h2>Cars List</h2>
    <a href="add_car.php" class="button">Add New Car</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Model</th>
                <th>Price per Day</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($car = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $car['id']; ?></td>
                    <td><?php echo htmlspecialchars($car['name']); ?></td>
                    <td><?php echo htmlspecialchars($car['model']); ?></td>
                    <td><?php echo number_format($car['price_per_day'], 2); ?></td>
                    <td><?php echo htmlspecialchars($car['status']); ?></td>
                    <td class="actions">
                        <a href="edit_car.php?id=<?php echo $car['id']; ?>">Edit</a> |
                        <a href="delete_car.php?id=<?php echo $car['id']; ?>" onclick="return confirm('Are you sure you want to delete this car?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No cars found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <footer>
        &copy; <?php echo date('Y'); ?> Car Rental System - Admin Panel
    </footer>
</body>
</html>
