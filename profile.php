<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];
$message = '';
$pass_message = '';

// Fetch user info
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Profile update handler
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Additional admin fields can be fetched here, for example:
    if ($role === 'admin') {
        // e.g. $admin_note = trim($_POST['admin_note']);
    }

    if (empty($name) || empty($email)) {
        $message = "Name and email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Update query
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $user_id);
        if ($stmt->execute()) {
            $message = "Profile updated successfully.";
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $user['name'] = $name;
            $user['email'] = $email;
        } else {
            $message = "Failed to update profile.";
        }
    }
}

// Password change handler same as before
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $pass_message = "Please fill all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $pass_message = "New password and confirmation do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (hash('sha256', $current_password) !== $row['password']) {
            $pass_message = "Current password is incorrect.";
        } else {
            $new_password_hashed = hash('sha256', $new_password);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password_hashed, $user_id);
            if ($stmt->execute()) {
                $pass_message = "Password changed successfully.";
            } else {
                $pass_message = "Failed to change password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= ucfirst($role) ?> Profile</title>
    <style>
        /* Reset some default styling */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9fafb;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            align-items: center;
        }

        .container {
            background: #fff;
            width: 100%;
            max-width: 600px;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 25px;
            font-weight: 700;
            color: #1a202c;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 8px;
            letter-spacing: 0.05em;
        }

        form {
            margin-bottom: 40px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #4b5563;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1.8px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 400;
            transition: border-color 0.3s ease;
            resize: vertical;
            color: #111827;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 6px rgba(79, 70, 229, 0.5);
        }

        button {
            background-color: #4f46e5;
            color: white;
            font-weight: 700;
            padding: 14px 0;
            width: 100%;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 17px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #4338ca;
        }

        .message {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            text-align: center;
        }

        .success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1.5px solid #10b981;
        }

        .error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1.5px solid #ef4444;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .container {
                padding: 20px 25px;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <h2><?= ucfirst($role) ?> Profile</h2>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <?php if ($role === 'admin'): ?>
            <label>Admin Note:</label>
            <textarea name="admin_note" rows="4" placeholder="Add any notes here..."></textarea>
        <?php endif; ?>

        <button type="submit" name="update_profile">Update Profile</button>
    </form>

    <h2>Change Password</h2>

    <?php if ($pass_message): ?>
        <div class="message <?= strpos($pass_message, 'successfully') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($pass_message) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Current Password:</label>
        <input type="password" name="current_password" required>

        <label>New Password:</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" name="change_password">Change Password</button>
    </form>

</div>

</body>
</html>
