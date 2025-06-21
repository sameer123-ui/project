<?php
include 'db.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'];
    $password = hash('sha256', $_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        $_SESSION['user']    = $user;
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['user_id'] = $user['id'];

        header("Location: " . ($user['role'] === 'admin' ? "dashboard_admin.php" : "dashboard_user.php"));
        exit;
    } else {
        $message = "❌ Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Car Rental System</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(120deg, #2980b9, #6dd5fa);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-box {
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            max-width: 400px;
            width: 100%;
            animation: fadeIn 0.6s ease;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        input {
            width: 100%;
            padding: 14px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            outline: none;
            background-color: #f9f9f9;
            transition: 0.3s;
        }

        input:focus {
            border-color: #007bff;
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.1);
        }

        button {
            width: 100%;
            background-color: #007bff;
            border: none;
            padding: 14px;
            font-weight: bold;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #0062cc;
        }

        .msg {
            text-align: center;
            color: #dc3545;
            font-weight: bold;
            margin-top: 10px;
        }

        .link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .link a {
            color: #007bff;
            text-decoration: none;
            margin: 0 5px;
        }

        .link a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 500px) {
            .form-box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="form-box">
        <h2>Login</h2>
        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required />
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required />
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="msg"><?php echo $message; ?></div>
        <div class="link">
            <a href="register.php">Create a new account</a> |
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>

</body>
</html>
