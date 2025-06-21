<?php
session_start();
require 'db.php';

// Only admin access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all users
$sql = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>View Users</title>
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
        margin-bottom: 30px;
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
    h2 {
        color: #333;
        margin-bottom: 20px;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
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

<h2>All Users</h2>

<?php if ($result->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered On</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
            <td><?= htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?>

</body>
</html>
