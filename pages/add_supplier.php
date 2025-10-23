<?php
include('../db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $supplier_name = trim($_POST['supplier_name']);
  $shop_link = trim($_POST['shop_link']);
  $address_type = $_POST['address_type'] ?? 'Local';
  $full_address = trim($_POST['full_address']);
  $logo = null;

  // Ensure uploads directory exists
  $uploadDir = '../assets/uploads/suppliers/';
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }

  // Handle supplier logo upload
  if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['logo']['tmp_name'];
    $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $_FILES['logo']['name']);
    $fileDest = $uploadDir . $fileName;
    if (move_uploaded_file($fileTmp, $fileDest)) {
      $logo = $fileName;
    }
  }

  // Prevent duplicate supplier names
  $check = $conn->prepare("SELECT supplier_id FROM suppliers WHERE supplier_name = ?");
  $check->bind_param("s", $supplier_name);
  $check->execute();
  $checkResult = $check->get_result();

  if ($checkResult && $checkResult->num_rows > 0) {
    echo "<script>alert('Supplier already exists!'); window.location='suppliers.php';</script>";
    exit();
  }
  $check->close();

  // ✅ Insert new supplier (with new columns)
  $stmt = $conn->prepare("INSERT INTO suppliers 
    (supplier_name, shop_link, address_type, full_address, logo, date_added) 
    VALUES (?, ?, ?, ?, ?, NOW())");
  $stmt->bind_param("sssss", $supplier_name, $shop_link, $address_type, $full_address, $logo);

  if ($stmt->execute()) {
    header("Location: suppliers.php?added=1");
    exit();
  } else {
    echo "<script>alert('Error adding supplier: " . addslashes($stmt->error) . "');</script>";
  }

  $stmt->close();
}
?>

<?php include('../includes/header.php'); ?>

<main class="main-content py-5" style="background-color: #eef4fb; min-height: 100vh;">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold text-primary"><i class="bi bi-person-plus"></i> Add Supplier</h2>
      <a href="suppliers.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <div class="card shadow-sm border-0 p-4" style="border-radius: 15px;">
      <form method="POST" enctype="multipart/form-data">
        <!-- Supplier Name -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Supplier Name</label>
          <input type="text" class="form-control" name="supplier_name" placeholder="Enter supplier name" required>
        </div>

        <!-- Shop Link -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Shop Link</label>
          <input type="text" class="form-control" name="shop_link" placeholder="https://example.com">
        </div>

        <!-- Address Type -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Supplier Type</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="address_type" id="local" value="Local" checked>
            <label class="form-check-label" for="local">Local</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="address_type" id="international" value="International">
            <label class="form-check-label" for="international">International</label>
          </div>
        </div>

        <!-- Full Address -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Full Address</label>
          <textarea class="form-control" name="full_address" rows="3" placeholder="Enter supplier’s complete address" required></textarea>
        </div>

        <!-- Logo Upload -->
        <div class="mb-3 text-center">
          <label class="form-label fw-semibold">Supplier Photo / Logo</label>
          <input type="file" class="form-control mb-3" name="logo" id="logoInput" accept="image/*">
          <img id="previewImage" src="../assets/uploads/suppliers/default-logo.png" 
               alt="Logo Preview" 
               class="rounded-circle border"
               style="width:120px; height:120px; object-fit:cover; border:3px solid #0d6efd;">
        </div>

        <div class="text-center mt-4">
          <button type="submit" class="btn btn-primary px-5">
            <i class="bi bi-save"></i> Save Supplier
          </button>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
  // 🖼️ Live image preview
  document.getElementById('logoInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('previewImage').src = e.target.result;
      }
      reader.readAsDataURL(file);
    }
  });
</script>

<?php include('../includes/footer.php'); ?>
