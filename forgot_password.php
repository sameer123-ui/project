<?php
include 'db.php';
session_start();

$step = 1; // Step 1: ask email, Step 2: reset password form
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !isset($_POST['new_password'])) {
        // Step 1: verify email
        $email = $_POST['email'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Email found, proceed to step 2
            $step = 2;
            $_SESSION['reset_email'] = $email;
        } else {
            $message = "❌ Email address not found.";
        }
    } elseif (isset($_POST['new_password'], $_POST['confirm_password'])) {
        // Step 2: process new password
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $message = "❌ Passwords do not match.";
            $step = 2;
        } elseif (strlen($new_password) < 6) {
            $message = "❌ Password must be at least 6 characters.";
            $step = 2;
        } elseif (!isset($_SESSION['reset_email'])) {
            $message = "❌ Session expired. Please try again.";
            $step = 1;
        } else {
            $email = $_SESSION['reset_email'];
            $hashed_password = hash('sha256', $new_password);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            if ($stmt->execute()) {
                $message = "✅ Password successfully reset. You can now <a href='login.php'>login</a>.";
                $step = 1;
                unset($_SESSION['reset_email']);
            } else {
                $message = "❌ Something went wrong. Please try again.";
                $step = 2;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #e9eff1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-box {
            background: #fff;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0069d9;
        }
        .msg {
            text-align: center;
            font-weight: bold;
            margin-top: 15px;
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-box">
    <?php if ($step === 1): ?>
        <h2>Forgot Password</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your registered email" required />
            <button type="submit">Next</button>
        </form>
    <?php elseif ($step === 2): ?>
        <h2>Reset Password</h2>
        <form method="POST">
            <input type="password" name="new_password" placeholder="New Password" required minlength="6" />
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required minlength="6" />
            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="msg <?php echo strpos($message, '❌') === 0 ? 'error' : ''; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div style="text-align:center; margin-top: 10px;">
        <a href="login.php">Back to Login</a>
    </div>
</div>

</body>
</html>
