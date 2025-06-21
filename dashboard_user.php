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
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>User Dashboard</title>
<style>
  /* Reset & base */
  * {
    box-sizing: border-box;
  }
  body {
    font-family: 'Poppins', sans-serif;
    background: #f0f4f8;
    margin: 0; padding: 0;
    color: #2c3e50;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  a {
    text-decoration: none;
  }

  /* Navbar */
  .navbar {
    background-color: #0d6efd;
    padding: 18px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 3px 8px rgba(13, 110, 253, 0.3);
  }
  .navbar strong {
    color: #fff;
    font-size: 1.5rem;
    letter-spacing: 1.1px;
  }
  .navbar nav a {
    color: #cce5ff;
    margin-left: 25px;
    font-weight: 600;
    transition: color 0.3s ease;
  }
  .navbar nav a:hover {
    color: #e9f0ff;
  }

  /* Container */
  .container {
    flex-grow: 1;
    max-width: 900px;
    margin: 40px auto 60px;
    padding: 0 25px;
  }

  /* Welcome Card */
  .welcome-card {
    background: #fff;
    border-radius: 15px;
    padding: 35px 45px;
    box-shadow: 0 15px 25px rgba(0, 0, 0, 0.07);
    margin-bottom: 35px;
    text-align: center;
  }
  .welcome-card h2 {
    font-weight: 700;
    color: #0d6efd;
    font-size: 2.2rem;
    margin-bottom: 12px;
  }
  .welcome-card p {
    font-size: 1.1rem;
    color: #636e72;
  }

  /* Stats Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
    gap: 25px;
  }

  .stat-box {
    background: #fff;
    border-radius: 15px;
    padding: 30px 25px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.07);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: default;
  }
  .stat-box:hover {
    transform: translateY(-7px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
  }
  .stat-box h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 12px;
  }
  .stat-box p {
    font-size: 2.6rem;
    font-weight: 700;
    user-select: none;
    color: #0d6efd;
  }

  /* Different colors for stats */
  .stat-box.total p {
    color: #007bff;
  }
  .stat-box.upcoming p {
    color: #28a745;
  }
  .stat-box.completed p {
    color: #6c757d;
  }

  /* Footer */
  footer {
    background-color: #0d6efd;
    color: #fff;
    text-align: center;
    padding: 20px 0;
    margin-top: auto;
    font-weight: 600;
    font-size: 0.9rem;
  }

  /* Responsive adjustments */
  @media (max-width: 480px) {
    .navbar {
      flex-direction: column;
      gap: 12px;
    }
    .navbar nav a {
      margin-left: 0;
      margin-right: 15px;
    }
  }
</style>
</head>
<body>

  <header class="navbar">
    <strong>User Dashboard</strong>
    <nav>
      <a href="dashboard_user.php">Home</a>
      <a href="index.php">Browse Cars</a>
      <a href="book_car.php">Book</a>
      <a href="my_bookings.php">My Bookings</a>
      <a href="profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

   <main class="container">
    <section class="welcome-card">
      <h2>Welcome back, <?php echo htmlspecialchars($user['name']); ?> 👋</h2>
      <p>Explore cars and manage your bookings with ease.</p>
    </section>

    <section class="stats-grid">
      <a href="my_bookings.php" class="stat-box total" title="View all bookings">
        <h3>Total Bookings</h3>
        <p><?php echo $totalBookings; ?></p>
      </a>
      <a href="my_bookings.php?status=upcoming" class="stat-box upcoming" title="View upcoming bookings">
        <h3>Upcoming Bookings</h3>
        <p><?php echo $upcomingBookings; ?></p>
      </a>
      <a href="my_bookings.php?status=completed" class="stat-box completed" title="View completed bookings">
        <h3>Completed Bookings</h3>
        <p><?php echo $completedBookings; ?></p>
      </a>
    </section>
  </main>

  <footer>
    &copy; <?php echo date('Y'); ?> Car Rental System | User Panel
  </footer>

</body>
</html>
