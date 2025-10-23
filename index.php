<?php
session_start();
include('db_connect.php');

$error = '';
$success = '';

// --- LOGIN HANDLER ---
if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM users WHERE username = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['fullname'] = $user['fullname'];
      header("Location: dashboard.php");
      exit();
    } else {
      $error = "Invalid password!";
    }
  } else {
    $error = "User not found!";
  }
}

// --- REGISTER HANDLER ---
if (isset($_POST['register'])) {
  $fullname = $_POST['fullname'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // check duplicate username
  $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $check->bind_param("s", $username);
  $check->execute();
  $result = $check->get_result();

  if ($result->num_rows > 0) {
    $error = "Username already exists!";
  } else {
    $stmt = $conn->prepare("INSERT INTO users (username, password, fullname) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $fullname);
    if ($stmt->execute()) {
      $success = "Account created successfully! You can now log in.";
    } else {
      $error = "Error creating account.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Monitoring System - Login</title>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background: url('assets/bg/bg.jpg') center/cover no-repeat fixed;
      height: 100vh;
      overflow: hidden;
    }

    .overlay {
      background: rgba(0, 0, 0, 0.55);
      position: absolute;
      inset: 0;
      z-index: 0;
    }

    .auth-container {
      position: relative;
      z-index: 1;
      display: flex;
      height: 100vh;
      justify-content: center;
      align-items: center;
      padding: 0 20px;
    }

    .auth-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(6px);
      border-radius: 15px;
      padding: 40px;
      width: 380px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }

    .auth-card:hover {
      transform: translateY(-5px);
    }

    .auth-title {
      text-align: center;
      font-weight: 700;
      color: #007bff;
      margin-bottom: 20px;
    }

    .form-label {
      font-weight: 600;
      color: #333;
    }

    .btn-primary, .btn-success {
      font-weight: 600;
      border-radius: 8px;
      padding: 10px;
    }

    .toggle-link {
      text-decoration: none;
      color: #007bff;
      font-weight: 500;
    }

    .toggle-link:hover {
      text-decoration: underline;
    }

    .alert {
      border-radius: 8px;
      font-size: 0.9rem;
      padding: 10px;
    }

    /* Left Section - Branding */
    .brand-section {
      flex: 1;
      text-align: center;
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 30px;
    }

    .brand-section h1 {
      font-weight: 700;
      font-size: 2.5rem;
      text-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
    }

    .brand-section p {
      font-size: 1rem;
      color: #ddd;
      max-width: 500px;
    }

    @media (max-width: 992px) {
      .brand-section {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="overlay"></div>

  <div class="auth-container">
    <!-- Branding Section -->
    <div class="brand-section">
      <h1><i class="bi bi-graph-up"></i> Sales Monitoring System</h1>
      <p>Manage your clients, orders, and performance reports — all in one place. Track sales, profits, and progress easily.</p>
    </div>

    <!-- Login/Register Card -->
    <div class="auth-card">
      <h3 class="auth-title">Welcome Back</h3>

      <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success text-center"><?php echo $success; ?></div>
      <?php endif; ?>

      <!-- LOGIN FORM -->
      <form method="POST" id="loginForm">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100 mb-2">Login</button>
        <div class="text-center">
          <a href="#" class="toggle-link" onclick="toggleForms()">Create an account</a>
        </div>
      </form>

      <!-- REGISTER FORM -->
      <form method="POST" id="registerForm" style="display:none;">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="fullname" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-success w-100 mb-2">Create Account</button>
        <div class="text-center">
          <a href="#" class="toggle-link" onclick="toggleForms()">Back to login</a>
        </div>
      </form>
    </div>
  </div>

  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleForms() {
      const loginForm = document.getElementById('loginForm');
      const registerForm = document.getElementById('registerForm');
      const title = document.querySelector('.auth-title');
      if (loginForm.style.display === 'none') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
        title.textContent = 'Welcome Back';
      } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        title.textContent = 'Create Account';
      }
    }
  </script>
</body>
</html>
