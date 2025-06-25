<?php
// Your PHP logic remains the same
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
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Reset Password</title>
<style>
  /* Reset and base */
  * {
    box-sizing: border-box;
  }
  body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    color: #333;
  }
  .form-box {
    background: #fff;
    padding: 40px 35px;
    width: 100%;
    max-width: 420px;
    border-radius: 15px;
    box-shadow:
      0 10px 15px rgba(101, 71, 255, 0.3),
      0 5px 8px rgba(37, 117, 252, 0.2);
    text-align: center;
  }
  h2 {
    margin-bottom: 25px;
    font-weight: 700;
    font-size: 1.9rem;
    color: #4a00e0;
    letter-spacing: 1.5px;
  }
  input[type="email"],
  input[type="password"] {
    width: 100%;
    padding: 14px 18px;
    margin: 12px 0 20px 0;
    border: 2px solid #ddd;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
  }
  input[type="email"]:focus,
  input[type="password"]:focus {
    outline: none;
    border-color: #6a11cb;
    box-shadow: 0 0 8px #6a11cbaa;
  }
  button {
    width: 100%;
    padding: 15px;
    background: #6a11cb;
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 6px 15px #6a11cb88;
    transition: background 0.3s ease;
  }
  button:hover {
    background: #4a00e0;
    box-shadow: 0 8px 18px #4a00e0cc;
  }
  .msg {
    margin-top: 20px;
    font-weight: 600;
    font-size: 1rem;
  }
  .msg.error {
    color: #e74c3c;
  }
  .msg.success {
    color: #27ae60;
  }
  a {
    color: #6a11cb;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
  }
  a:hover {
    color: #4a00e0;
    text-decoration: underline;
  }
  /* Responsive tweaks */
  @media (max-width: 480px) {
    .form-box {
      padding: 30px 25px;
    }
  }
</style>
</head>
<body>

<div class="form-box">
    <?php if ($step === 1): ?>
        <h2>Forgot Password</h2>
        <form method="POST" novalidate>
            <input type="email" name="email" placeholder="Enter your registered email" required />
            <button type="submit">Next</button>
        </form>
    <?php elseif ($step === 2): ?>
        <h2>Reset Password</h2>
        <form method="POST" novalidate>
            <input type="password" name="new_password" placeholder="New Password" required minlength="6" />
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required minlength="6" />
            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="msg <?php echo strpos($message, '❌') === 0 ? 'error' : 'success'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div style="margin-top: 20px;">
        <a href="login.php">Back to Login</a>
    </div>
</div>

</body>
</html>
