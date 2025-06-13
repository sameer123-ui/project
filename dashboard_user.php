<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
            flex: 1;
            padding: 30px;
            max-width: 1000px;
            margin: auto;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .card h2 {
            margin-bottom: 15px;
        }
        .card a {
            display: inline-block;
            padding: 10px 15px;
            background: #28a745;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 10px;
        }
        .card a:hover {
            background: #218838;
        }
        footer {
            background: #0069d9;
            color: white;
            text-align: center;
            padding: 15px;
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

    <div class="container">
        <div class="card">
            <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?> 👋</h2>
            <p>Browse available cars and manage your bookings easily.</p>
        </div>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Car Rental System | User Panel
    </footer>

</body>
</html>
