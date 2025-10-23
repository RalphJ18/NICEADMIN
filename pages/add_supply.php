<?php
include '../db_connect.php';
session_start();

if (!isset($_GET['supplier_id'])) {
    header("Location: suppliers.php");
    exit();
}

$supplier_id = intval($_GET['supplier_id']);
$message = "";

// Fetch supplier name
$supplier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT supplier_name FROM suppliers WHERE supplier_id = $supplier_id"));
if (!$supplier) {
    die("<div class='alert alert-danger text-center mt-4'>Supplier not found.</div>");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name']);
    $category = $_POST['category'] ?? null;
    $price = floatval($_POST['price']);
    $item_description = trim($_POST['item_description']);
    $item_image = null;

    // Folder per supplier
    $folderName = '../assets/uploads/suppliers/' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $supplier['supplier_name']) . '/';
    if (!is_dir($folderName)) mkdir($folderName, 0755, true);

    // Upload image
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['item_image']['tmp_name'];
        $name = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['item_image']['name']));
        $dest = $folderName . $name;
        if (move_uploaded_file($tmp, $dest)) {
            $item_image = $name;
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO supplier_supplies 
      (supplier_id, category, item_name, price, item_image, item_description, date_added)
      VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issdss", $supplier_id, $category, $item_name, $price, $item_image, $item_description);

    if ($stmt->execute()) {
        header("Location: supplier_supplies.php?supplier_id=$supplier_id&added=1");
        exit();
    } else {
        $message = "Error adding supply: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Supply - Sales Monitoring System</title>
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

    .btn-success {
      background-color: #28a745;
      border-radius: 10px;
      font-weight: 600;
      padding: 10px;
    }

    .btn-success:hover {
      background-color: #218838;
      transform: translateY(-2px);
    }

    .btn-outline-secondary {
      border-radius: 10px;
      font-weight: 600;
    }

    .alert {
      border-radius: 10px;
    }
  </style>
</head>
<body>

<div class="main-wrapper">
  <div class="form-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
      <h3><i class="bi bi-box-seam"></i> Add Supply for <?php echo htmlspecialchars($supplier['supplier_name']); ?></h3>
      <a href="supplier_supplies.php?supplier_id=<?php echo $supplier_id; ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

      <div class="mb-3">
        <label class="form-label">Supply Name</label>
        <input type="text" name="item_name" class="form-control" placeholder="e.g. Samsung A12 LCD Replacement" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category" class="form-select" required>
          <option value="" disabled selected>Select category</option>
          <option value="LCD Replacement">LCD Replacement</option>
          <option value="Battery Replacement">Battery Replacement</option>
          <option value="Power/Volume Button">Power/Volume Button</option>
          <option value="Middle Frame">Middle Frame</option>
          <option value="LCD Frame">LCD Frame</option>
          <option value="Back Cover">Back Cover</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Price (₱)</label>
        <input type="number" name="price" step="0.01" class="form-control" placeholder="Enter part price" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="item_description" class="form-control" rows="3" placeholder="Add supply details (optional)"></textarea>
      </div>

      <div class="mb-4">
        <label class="form-label">Part Photo</label>
        <input type="file" name="item_image" class="form-control" accept="image/*" required>
      </div>

      <button type="submit" class="btn btn-success w-100">
        <i class="bi bi-plus-circle"></i> Add Supply
      </button>
    </form>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
