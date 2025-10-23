<?php
session_start();
require_once 'db_connect.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validations
    if ($username === '') $errors[] = "Username is required.";
    if ($fullname === '') $errors[] = "Full name is required.";
    if ($password === '') $errors[] = "Password is required.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

    if (empty($errors)) {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $errors[] = "Username already taken. Choose another.";
        } else {
            // Insert new user with hashed password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (username, password, fullname) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $username, $hash, $fullname);
            if ($ins->execute()) {
                // Redirect to login with success message
                header("Location: index.php?created=1");
                exit();
            } else {
                $errors[] = "Database error: could not create account.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Create Account - Sales Monitoring</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container" style="max-width:650px; margin-top:60px;">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="mb-3">Create Account</h4>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input name="fullname" class="form-control" required value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>

          <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-secondary">Back to Login</a>
            <button type="submit" class="btn btn-primary">Create Account</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</body>
</html>
