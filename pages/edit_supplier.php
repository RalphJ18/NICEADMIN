<?php
include '../db_connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Check supplier ID
if (!isset($_GET['id'])) {
  header("Location: suppliers.php");
  exit();
}

$supplier_id = intval($_GET['id']);
$message = "";

// Fetch supplier data
$query = "SELECT * FROM suppliers WHERE supplier_id = $supplier_id";
$result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) == 0) {
  die("<div class='alert alert-danger text-center mt-4'>Supplier not found.</div>");
}
$supplier = mysqli_fetch_assoc($result);

// Update info
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $supplier_name = trim($_POST['supplier_name']);
  $shop_link     = trim($_POST['shop_link']);
  $address       = trim($_POST['address']);

  $update = "UPDATE suppliers SET supplier_name=?, shop_link=?, address=? WHERE supplier_id=?";
  $stmt = $conn->prepare($update);
  $stmt->bind_param("sssi", $supplier_name, $shop_link, $address, $supplier_id);

  if ($stmt->execute()) {
    header("Location: suppliers.php?updated=1");
    exit();
  } else {
    $message = "Error updating supplier: " . $stmt->error;
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Supplier - Sales Monitoring System</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fa;
    }

    .main-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 15px;
    }

    .form-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 25px rgba(0,0,0,0.08);
      padding: 40px 35px;
      width: 100%;
      max-width: 700px;
      transition: all 0.25s ease;
    }

    .form-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 30px rgba(0,0,0,0.1);
    }

    h3 {
      font-weight: 700;
      color: #007bff;
    }

    .form-label {
      font-weight: 600;
      color: #333;
    }

    .form-control {
      border-radius: 8px;
      padding: 10px;
    }

    .btn-primary {
      background-color: #007bff;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      padding: 10px 20px;
    }

    .btn-primary:hover {
      background-color: #0056b3;
      transform: translateY(-2px);
    }

    .btn-secondary {
      border-radius: 10px;
      font-weight: 600;
      padding: 10px 20px;
    }

    .alert {
      border-radius: 10px;
    }

    .text-end {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
  </style>
</head>
<body>

<div class="main-wrapper">
  <div class="form-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
      <h3><i class="bi bi-pencil-square"></i> Edit Supplier Info</h3>
      <a href="suppliers.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Supplier Name</label>
        <input type="text" name="supplier_name" class="form-control" 
               value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Shop Link</label>
        <input type="text" name="shop_link" class="form-control" 
               value="<?php echo htmlspecialchars($supplier['shop_link']); ?>" placeholder="Optional">
      </div>

      <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($supplier['address']); ?></textarea>
      </div>

      <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
