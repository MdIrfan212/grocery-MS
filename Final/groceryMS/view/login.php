<?php
// Auto-added DB bootstrap (keeps your design, replaces JSON with MySQL)
$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
foreach ($__candidates as $__p) { if (file_exists($__p)) { require_once $__p; break; } }
?>
<?php
session_start();

$errorMsg = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if ($email === '' || $pass === '') {
        $errorMsg = "Email and password are required.";
    } else {
        $file = __DIR__ . '/../users.txt';
        if (file_exists($file) && is_readable($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $found = false;
            foreach ($lines as $line) {
                $parts = explode("|", $line);
                $name = $parts[0] ?? '';
                $uEmail = $parts[1] ?? '';
                $uPass = $parts[2] ?? '';
                $uRole = $parts[3] ?? '';
                if ($email === $uEmail && $pass === $uPass) {
                    $_SESSION['user'] = $name;
                    $_SESSION['role'] = $uRole;
                    header("Location: dashboard.php");
                    exit;
                }
            }
            $errorMsg = "❌ Invalid email or password.";
        } else {
            $errorMsg = "No users found. Please register first.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GroceryMS - Login</title>
  <link rel="stylesheet" href="../css/main.css">
</head>
<body>
  <div class="auth-box">
    <img src="../images/imagelogo.webp" alt="Logo" class="logo">
    <h1>Grocery Management</h1>
    <h2>Login</h2>

    <form method="POST" id="loginForm">
      <label>Email</label>
      <input type="email" name="email" id="email" placeholder="Enter your email" required>
      <small id="loginEmailError" class="error"></small>

      <label>Password</label>
      <input type="password" name="password" id="password" placeholder="Enter your password" required>
      <small id="loginPassError" class="error"></small>

      <button type="submit">Login</button>
    </form>

    <?php if (!empty($errorMsg)): ?>
      <p style="color:red; font-size:14px; margin-top:8px;"><?php echo htmlspecialchars($errorMsg); ?></p>
    <?php endif; ?>

    <p class="switch">Don’t have an account? <a href="register.php">Register here</a></p>
  </div>

  <script src="../js/script.js"></script>
</body>
</html>
