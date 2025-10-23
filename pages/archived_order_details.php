<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Validate archive ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: archived_orders.php");
  exit();
}

$archive_id = intval($_GET['id']);

// Fetch archived order with client info
$query = "
  SELECT 
    a.archive_id,
    a.order_id,
    a.client_id,
    COALESCE(a.fullname, c.fullname) AS fullname,
    COALESCE(a.contact_number, c.contact_number) AS contact_number,
    COALESCE(a.email, c.email) AS email,
    COALESCE(a.address, c.address) AS address,
    a.issue,
    a.total_amount,
    a.part_cost,
    a.repair_cost,
    a.status,
    a.payment_status,
    a.warranty_end,
    a.before_image,
    a.after_image,
    a.date_archived,
    a.date_created
  FROM archives a
  LEFT JOIN clients c ON a.client_id = c.client_id
  WHERE a.archive_id = ?
  LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $archive_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
  echo "<div class='alert alert-warning text-center mt-5'>Archived order not found.</div>";
  exit();
}

include('../includes/header.php');
?>

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

  .container {
    max-width: 950px;
  }

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 30px;
  }

  .page-header h3 {
    font-weight: 700;
    color: #6c757d;
    margin-bottom: 0;
  }

  .page-header .btn {
    font-weight: 600;
    border-radius: 8px;
  }

  .card {
    background: #fff;
    border-radius: 15px;
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    padding: 30px;
  }

  .card h4 {
    font-weight: 700;
    color: #007bff;
  }

  .card p {
    margin-bottom: 8px;
  }

  .badge {
    font-size: 0.85rem;
    border-radius: 6px;
    padding: 6px 10px;
  }

  hr {
    margin: 1.5rem 0;
  }

  .bg-light {
    background-color: #f1f3f5 !important;
  }

  .img-thumbnail {
    width: 100%;
    max-height: 260px;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  }

  .btn-outline-primary, .btn-outline-secondary, .btn-success {
    border-radius: 10px;
  }

  .btn-outline-primary:hover {
    background-color: #007bff;
    color: #fff;
  }

  .btn-outline-secondary:hover {
    background-color: #6c757d;
    color: #fff;
  }

  .alert-secondary {
    font-size: 0.9rem;
    color: #555;
  }

  @media (max-width: 768px) {
    .page-header {
      flex-direction: column;
      align-items: stretch;
      text-align: center;
    }

    .page-header .d-flex {
      justify-content: center;
      flex-wrap: wrap;
      gap: 10px;
    }

    .card {
      padding: 20px;
    }
  }
</style>

<main class="main-content">
  <div class="container">

    <!-- Header Section -->
    <div class="page-header">
      <h3><i class="bi bi-archive-fill"></i> Archived Order Details</h3>
      <div class="d-flex flex-wrap gap-2">
        <!-- Download Receipt -->
        <a href="download_archive_receipt.php?id=<?php echo $archive_id; ?>" class="btn btn-outline-primary">
          <i class="bi bi-download"></i> Download Receipt
        </a>

        <!-- Claim Warranty (Restore) -->
        <form method="POST" action="restore_order.php" style="display:inline;">
          <input type="hidden" name="archive_id" value="<?php echo $archive_id; ?>">
          <button type="submit" class="btn btn-success"
                  onclick="return confirm('Claim warranty and restore this order to active orders?');">
            <i class="bi bi-arrow-counterclockwise"></i> Claim Warranty
          </button>
        </form>

        <!-- Back -->
        <a href="archived_orders.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <!-- Order Card -->
    <div class="card">
      <h4 class="mb-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($order['fullname'] ?? 'Unknown'); ?></h4>

      <!-- Client Info -->
      <div class="row mb-3">
        <div class="col-md-6">
          <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($order['contact_number'] ?? 'N/A'); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></p>
          <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></p>
        </div>
        <div class="col-md-6">
          <p><strong>Status:</strong> 
            <span class="badge bg-secondary"><?php echo htmlspecialchars($order['status'] ?? 'N/A'); ?></span>
          </p>
          <p><strong>Payment Status:</strong> 
            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($order['payment_status'] ?? 'N/A'); ?></span>
          </p>
          <p><strong>Warranty End:</strong> <?php echo htmlspecialchars($order['warranty_end'] ?? 'N/A'); ?></p>
        </div>
      </div>

      <hr>

      <!-- Costs -->
      <div class="row text-center">
        <div class="col-md-4 mb-3 mb-md-0">
          <p><strong>Part Cost:</strong></p>
          <p>₱<?php echo number_format($order['part_cost'] ?? 0, 2); ?></p>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
          <p><strong>Repair Cost:</strong></p>
          <p>₱<?php echo number_format($order['repair_cost'] ?? 0, 2); ?></p>
        </div>
        <div class="col-md-4">
          <p><strong>Total Cost:</strong></p>
          <p class="fw-bold text-success">₱<?php echo number_format($order['total_amount'] ?? 0, 2); ?></p>
        </div>
      </div>

      <hr>

      <!-- Issue -->
      <div class="mb-3">
        <p><strong>Issue / Description:</strong></p>
        <div class="p-3 bg-light rounded">
          <?php echo nl2br(htmlspecialchars($order['issue'] ?? 'No issue recorded.')); ?>
        </div>
      </div>

      <!-- Images -->
      <div class="row mt-4 text-center">
        <div class="col-md-6 mb-4 mb-md-0">
          <p class="fw-bold text-muted"><i class="bi bi-image"></i> Before Repair</p>
          <?php if (!empty($order['before_image']) && file_exists("../assets/uploads/" . $order['before_image'])): ?>
            <img src="../assets/uploads/<?php echo htmlspecialchars($order['before_image']); ?>" 
                 alt="Before Image" class="img-thumbnail">
          <?php else: ?>
            <div class="alert alert-secondary py-2">No image available</div>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <p class="fw-bold text-muted"><i class="bi bi-image"></i> After Repair</p>
          <?php if (!empty($order['after_image']) && file_exists("../assets/uploads/" . $order['after_image'])): ?>
            <img src="../assets/uploads/<?php echo htmlspecialchars($order['after_image']); ?>" 
                 alt="After Image" class="img-thumbnail">
          <?php else: ?>
            <div class="alert alert-secondary py-2">No image available</div>
          <?php endif; ?>
        </div>
      </div>

      <hr>

      <div class="text-muted small text-center text-md-start mt-3">
        <p><strong>Date Created:</strong> <?php echo date('M d, Y', strtotime($order['date_created'] ?? 'now')); ?></p>
        <p><strong>Date Archived:</strong> <?php echo date('M d, Y', strtotime($order['date_archived'] ?? 'now')); ?></p>
      </div>
    </div>
  </div>
</main>

<?php include('../includes/footer.php'); ?>
