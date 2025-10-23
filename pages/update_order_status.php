<?php
include('../db_connect.php');
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Ensure order_id is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
  echo "<div class='alert alert-danger text-center mt-4'>Order ID is required.</div>";
  exit();
}

$order_id = intval($_GET['id']);
$message = "";

// Fetch order, client, and total_cost
$sql = "
  SELECT 
    o.order_id,
    o.status AS repair_status,
    o.payment_status,
    o.warranty_start,
    o.warranty_end,
    o.before_image,
    o.after_image,
    o.type_of_repair,
    o.issue,
    c.fullname,
    c.contact_number,
    c.address,
    c.email,
    d.part_cost,
    d.repair_cost,
    d.total_cost
  FROM orders o
  LEFT JOIN clients c ON o.client_id = c.client_id
  LEFT JOIN order_details d ON o.order_id = d.order_id
  WHERE o.order_id = ?
  LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
  echo "<div class='alert alert-danger text-center mt-4'>Order not found.</div>";
  exit();
}

$order = $res->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $repair_status = isset($_POST['repair_status']) ? trim($_POST['repair_status']) : $order['repair_status'];
  $payment_status = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : $order['payment_status'];
  $after_image = $order['after_image']; // keep existing by default

  if (isset($_FILES['after_image']) && $_FILES['after_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../assets/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $tmpName = $_FILES['after_image']['tmp_name'];
    $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['after_image']['name']));
    $dest = $uploadDir . $fileName;

    if (move_uploaded_file($tmpName, $dest)) {
      $after_image = $fileName;
    } else {
      $message = "Error uploading after image.";
    }
  }

  if (empty($message)) {
    $update = "UPDATE orders SET status = ?, payment_status = ?, after_image = ? WHERE order_id = ?";
    $stmt_up = $conn->prepare($update);
    if ($stmt_up) {
      $stmt_up->bind_param('sssi', $repair_status, $payment_status, $after_image, $order_id);
      if ($stmt_up->execute()) {
        $stmt_up->close();
        header("Location: orders.php?updated=1");
        exit();
      } else {
        $message = "Error updating order: " . $stmt_up->error;
      }
    } else {
      $message = "DB prepare error: " . $conn->error;
    }
  }
}
?>

<?php include('../includes/header.php'); ?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
  body {
    font-family: 'Poppins', sans-serif;
    background-color: #f5f7fa;
  }

  .main-content {
    padding: 60px 0;
    min-height: 100vh;
  }

  h3 {
    font-weight: 700;
    color: #007bff;
  }

  .card {
    background: #fff;
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    padding: 35px 40px;
  }

  .form-label {
    font-weight: 500;
    color: #333;
  }

  .form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #dcdcdc;
    transition: border-color 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.15rem rgba(0,123,255,0.25);
  }

  .btn {
    border-radius: 8px;
    font-weight: 600;
  }

  .btn-primary {
    background-color: #007bff;
    border: none;
  }

  .btn-primary:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
  }

  .btn-secondary {
    background-color: #6c757d;
    border: none;
  }

  .btn-secondary:hover {
    background-color: #5a6268;
  }

  .badge {
    padding: 6px 10px;
    font-size: 0.9rem;
    border-radius: 6px;
  }

  .img-thumbnail {
    border: none;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  }

  .card h5 {
    font-weight: 600;
    color: #007bff;
    margin-bottom: 20px;
  }

  .alert-danger {
    border-radius: 10px;
  }

  @media (max-width: 768px) {
    .card {
      padding: 25px;
    }
  }
</style>

<main class="main-content">
  <div class="container" style="max-width:850px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-pencil-square"></i> Update Order Status</h3>
      <a href="orders.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Orders
      </a>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-danger shadow-sm"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Client Information -->
    <div class="card mb-4">
      <h5><i class="bi bi-person-lines-fill"></i> Client Information</h5>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Full Name</label>
          <input class="form-control" value="<?php echo htmlspecialchars($order['fullname']); ?>" readonly>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Contact Number</label>
          <input class="form-control" value="<?php echo htmlspecialchars($order['contact_number']); ?>" readonly>
        </div>
        <div class="col-md-12 mb-3">
          <label class="form-label">Email</label>
          <input class="form-control" value="<?php echo htmlspecialchars($order['email']); ?>" readonly>
        </div>
        <div class="col-md-12 mb-2">
          <label class="form-label">Address</label>
          <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($order['address']); ?></textarea>
        </div>
      </div>
    </div>

    <!-- Order Update Form -->
    <form method="POST" enctype="multipart/form-data">
      <div class="card">
        <h5><i class="bi bi-gear"></i> Order Details</h5>

        <p><strong>Issue:</strong><br><?php echo nl2br(htmlspecialchars($order['issue'] ?? '')); ?></p>
        <p><strong>Type of Repair:</strong> <?php echo htmlspecialchars($order['type_of_repair'] ?? ''); ?></p>

        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">Part Cost (₱)</label>
            <input class="form-control" value="<?php echo number_format($order['part_cost'] ?? 0, 2); ?>" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label">Repair Cost (₱)</label>
            <input class="form-control" value="<?php echo number_format($order['repair_cost'] ?? 0, 2); ?>" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label text-success fw-semibold">Total Cost (₱)</label>
            <input class="form-control text-success fw-bold" value="₱<?php echo number_format($order['total_cost'] ?? 0, 2); ?>" readonly>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Warranty</label>
          <div>
            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($order['warranty_start'] ?? ''); ?></span>
            <span class="mx-2">→</span>
            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($order['warranty_end'] ?? ''); ?></span>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Repair Status</label>
          <select name="repair_status" class="form-select" required>
            <option value="Pending" <?php if (($order['repair_status'] ?? '') === 'Pending') echo 'selected'; ?>>Pending</option>
            <option value="On Going" <?php if (($order['repair_status'] ?? '') === 'On Going') echo 'selected'; ?>>On Going</option>
            <option value="Done" <?php if (($order['repair_status'] ?? '') === 'Done') echo 'selected'; ?>>Done</option>
            <option value="Use Warranty" <?php if (($order['repair_status'] ?? '') === 'Use Warranty') echo 'selected'; ?>>Use Warranty</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Payment Status</label>
          <select name="payment_status" class="form-select" required>
            <option value="Downpayment" <?php if (($order['payment_status'] ?? '') === 'Downpayment') echo 'selected'; ?>>Downpayment</option>
            <option value="Paid in Cash" <?php if (($order['payment_status'] ?? '') === 'Paid in Cash') echo 'selected'; ?>>Paid in Cash</option>
            <option value="Installment" <?php if (($order['payment_status'] ?? '') === 'Installment') echo 'selected'; ?>>Installment</option>
            <option value="Not Yet Paid" <?php if (($order['payment_status'] ?? '') === 'Not Yet Paid') echo 'selected'; ?>>Not Yet Paid</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">After Image (optional)</label>
          <?php if (!empty($order['after_image'])): ?>
            <div class="mb-2">
              <img src="../assets/uploads/<?php echo htmlspecialchars($order['after_image']); ?>" alt="After image" class="img-thumbnail" style="max-width:200px;">
            </div>
          <?php else: ?>
            <p class="text-muted">No after image uploaded.</p>
          <?php endif; ?>
          <input type="file" name="after_image" class="form-control" accept="image/*">
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save"></i> Update
          </button>
        </div>
      </div>
    </form>
  </div>
</main>

<?php include('../includes/footer.php'); ?>
