<?php
include('../db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$order_id = $_GET['id'] ?? 0;

// Fetch order with client + details info (including after_image)
$query = "
  SELECT 
    o.order_id,
    o.client_id,
    o.issue,
    o.type_of_repair,
    o.payment_status,
    o.warranty_start,
    o.warranty_end,
    o.date_created,
    o.before_image,
    o.after_image,
    c.fullname,
    c.contact_number,
    c.email,
    c.address,
    d.part_cost,
    d.repair_cost,
    d.total_cost
  FROM orders o
  LEFT JOIN clients c ON o.client_id = c.client_id
  LEFT JOIN order_details d ON o.order_id = d.order_id
  WHERE o.order_id = ?
  LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();
?>

<?php include('../includes/header.php'); ?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
  body {
    background-color: #f5f7fa;
    font-family: 'Poppins', sans-serif;
  }

  .main-content {
    padding: 60px 0;
    min-height: 100vh;
  }

  h2 {
    font-weight: 700;
    color: #007bff;
  }

  .card {
    background: #fff;
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    padding: 40px 45px;
  }

  .card h4 {
    font-weight: 700;
    color: #333;
  }

  .card p {
    color: #444;
    margin-bottom: 8px;
  }

  hr {
    border-top: 1px solid #eaeaea;
    margin: 25px 0;
  }

  .badge {
    font-size: 0.9rem;
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 500;
  }

  .fw-bold.text-primary {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .section-title {
    font-weight: 600;
    color: #007bff;
    margin-bottom: 15px;
    font-size: 1.1rem;
  }

  .btn-secondary {
    border-radius: 8px;
    font-weight: 600;
  }

  .img-thumbnail {
    border: none;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
  }

  .img-thumbnail:hover {
    transform: scale(1.03);
  }

  .alert-secondary {
    border-radius: 10px;
  }

  @media (max-width: 768px) {
    .card {
      padding: 25px;
    }
  }
</style>

<main class="main-content">
  <div class="container">
    <?php if ($order): ?>
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="bi bi-receipt"></i> Order Details</h2>
        <a href="orders.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
      </div>

      <div class="card">
        <!-- Client Info -->
        <div class="section-title"><i class="bi bi-person-lines-fill"></i> Client Information</div>
        <h4><?php echo htmlspecialchars($order['fullname']); ?></h4>
        <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($order['contact_number']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>

        <hr>

        <!-- Repair Info -->
        <div class="section-title"><i class="bi bi-tools"></i> Repair Details</div>
        <p><strong>Issue Description:</strong><br><?php echo nl2br(htmlspecialchars($order['issue'])); ?></p>
        <p><strong>Type of Repair:</strong> <?php echo htmlspecialchars($order['type_of_repair']); ?></p>

        <div class="row my-3">
          <div class="col-md-4">
            <label class="fw-semibold">Part Cost:</label>
            <p>₱<?php echo number_format($order['part_cost'] ?? 0, 2); ?></p>
          </div>
          <div class="col-md-4">
            <label class="fw-semibold">Repair Cost:</label>
            <p>₱<?php echo number_format($order['repair_cost'] ?? 0, 2); ?></p>
          </div>
          <div class="col-md-4">
            <label class="fw-semibold text-success">Total Amount:</label>
            <p class="text-success fw-bold">₱<?php echo number_format($order['total_cost'] ?? 0, 2); ?></p>
          </div>
        </div>

        <p><strong>Payment Status:</strong> 
          <span class="badge 
            <?php 
              echo match($order['payment_status']) {
                'Downpayment' => 'bg-warning text-dark',
                'Paid in Cash' => 'bg-success text-white',
                'Installment' => 'bg-info text-white',
                'Not Yet Paid' => 'bg-danger text-white',
                default => 'bg-light text-dark'
              };
            ?>">
            <?php echo htmlspecialchars($order['payment_status']); ?>
          </span>
        </p>

        <p><strong>Warranty:</strong> 
          <?php echo htmlspecialchars($order['warranty_start']); ?> → 
          <?php echo htmlspecialchars($order['warranty_end']); ?>
        </p>

        <p><strong>Date Created:</strong> 
          <?php echo date('M d, Y', strtotime($order['date_created'])); ?>
        </p>

        <!-- 📸 Before & After Images -->
        <hr>
        <div class="section-title"><i class="bi bi-image"></i> Repair Images</div>

        <div class="row text-center">
          <div class="col-md-6 mb-4">
            <label class="fw-semibold text-muted d-block mb-2">Before Repair</label>
            <?php if (!empty($order['before_image'])): ?>
              <img src="../assets/uploads/<?php echo htmlspecialchars($order['before_image']); ?>" 
                   alt="Before Repair"
                   class="img-thumbnail"
                   style="max-width: 100%; border-radius: 10px;">
            <?php else: ?>
              <div class="alert alert-secondary py-3">No Before Image Available</div>
            <?php endif; ?>
          </div>

          <div class="col-md-6 mb-4">
            <label class="fw-semibold text-muted d-block mb-2">After Repair</label>
            <?php if (!empty($order['after_image'])): ?>
              <img src="../assets/uploads/<?php echo htmlspecialchars($order['after_image']); ?>" 
                   alt="After Repair"
                   class="img-thumbnail"
                   style="max-width: 100%; border-radius: 10px;">
            <?php else: ?>
              <div class="alert alert-secondary py-3">No After Image Uploaded</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="alert alert-warning text-center shadow-sm mt-5 p-4 rounded">
        <i class="bi bi-exclamation-triangle"></i> Order not found.
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include('../includes/footer.php'); ?>
