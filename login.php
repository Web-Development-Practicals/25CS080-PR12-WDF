<?php
session_start();
include 'db.php';

$error = '';
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = "Unauthorized access. Please log in first.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT id, password FROM admins WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $username;
            header("Location: index.php?msg=Welcome back, " . urlencode($username));
            exit();
        }
    }
    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 90vh; background: #f4f6f9; }
        .box { background: #fff; padding: 24px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.12); width: 300px; }
        .err { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 8px; margin-bottom: 12px; font-size: 13px; border-radius: 4px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin: 6px 0 14px; box-sizing: border-box; }
        button { width: 100%; padding: 9px; background: #007bff; border: none; color: white; cursor: pointer; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h3>Admin Login</h3>
        <?php if ($error): ?><div class="err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>