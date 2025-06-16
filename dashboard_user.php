<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

function getUserBookingCount($conn, $user_id, $status = null) {
    if ($status) {
        $sql = "SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $status);
    } else {
        $sql = "SELECT COUNT(*) FROM bookings WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    }

    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count;
}

$totalBookings = getUserBookingCount($conn, $user_id);
$upcomingBookings = getUserBookingCount($conn, $user_id, 'upcoming');
$completedBookings = getUserBookingCount($conn, $user_id, 'completed');
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <style>
        /* your existing CSS here */
        * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f0f2f5;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    color: #333;
}

.navbar {
    background-color: #0069d9;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.navbar a {
    color: #ecf0f1;
    text-decoration: none;
    margin-left: 20px;
    font-weight: 600;
    transition: color 0.3s ease;
}

.navbar a:hover {
    color: #1abc9c;
}

.container {
    flex: 1;
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.card {
    background: #ffffff;
    border-radius: 12px;
    padding: 30px 40px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
}

.card h2 {
    margin-bottom: 20px;
    color: #34495e;
    font-weight: 700;
}

.card p {
    font-size: 1.1rem;
    line-height: 1.6;
    color: #666;
}

.stats-grid {
    display: flex;
    gap: 25px;
    justify-content: space-between;
}

.stat-box {
    flex: 1;
    background-color: #f7fafc;
    border-radius: 12px;
    padding: 25px 20px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: default;
}

.stat-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.stat-box h3 {
    font-size: 1.3rem;
    margin-bottom: 12px;
    color: #2c3e50;
}

.stat-box p {
    font-size: 2.2rem;
    font-weight: 700;
    color: #1abc9c;
    user-select: none;
}

.stat-box.total p {
    color: #3498db;
}

.stat-box.upcoming p {
    color: #27ae60;
}

.stat-box.completed p {
    color: #95a5a6;
}

footer {
    background-color: #2c3e50;
    color: #ecf0f1;
    text-align: center;
    padding: 20px 0;
    margin-top: auto;
    font-size: 0.9rem;
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
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?> 👋</h2>
            <p>Browse available cars and manage your bookings easily.</p>
        </div>

        <div class="card">
            <h2>Your Booking Summary</h2>
            <div style="display:flex; gap:20px;">
                <div style="flex:1; background:#e9ecef; padding:15px; border-radius:8px; text-align:center;">
                    <h3>Total Bookings</h3>
                    <p style="font-size:24px; color:#007bff;"><?php echo $totalBookings; ?></p>
                </div>
                <div style="flex:1; background:#e9ecef; padding:15px; border-radius:8px; text-align:center;">
                    <h3>Upcoming</h3>
                    <p style="font-size:24px; color:#28a745;"><?php echo $upcomingBookings; ?></p>
                </div>
                <div style="flex:1; background:#e9ecef; padding:15px; border-radius:8px; text-align:center;">
                    <h3>Completed</h3>
                    <p style="font-size:24px; color:#6c757d;"><?php echo $completedBookings; ?></p>
                </div>
            </div>
        </div>

    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Car Rental System | User Panel
    </footer>

</body>
</html>
