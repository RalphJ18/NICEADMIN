<?php
session_start();
include('../db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch archived orders
$query = "
  SELECT 
    a.archive_id,
    a.order_id,
    a.client_id,
    COALESCE(a.fullname, c.fullname) AS client_name,
    a.total_amount,
    a.status,
    a.payment_status,
    a.warranty_end,
    a.date_archived
  FROM archives a
  LEFT JOIN clients c ON a.client_id = c.client_id
  ORDER BY a.date_archived DESC
";
$result = mysqli_query($conn, $query);
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

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 30px;
  }

  .page-header h2 {
    font-weight: 700;
    color: #6c757d;
  }

  .btn-primary {
    border-radius: 8px;
    font-weight: 600;
  }

  .alert {
    border-radius: 10px;
    font-weight: 500;
  }

  .archive-card {
    background: #fff;
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    transition: all 0.25s ease;
    padding: 20px;
    height: 100%;
  }

  .archive-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 25px rgba(0,0,0,0.08);
  }

  .archive-card h5 {
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
  }

  .archive-card .badge {
    font-size: 0.8rem;
    border-radius: 6px;
  }

  .archive-meta {
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 5px;
  }

  .archive-amount {
    font-size: 1.1rem;
    font-weight: 600;
    color: #28a745;
  }

  .archive-actions .btn {
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 8px;
  }

  @media (max-width: 768px) {
    .page-header {
      text-align: center;
      justify-content: center;
      gap: 15px;
    }
  }
</style>

<main class="main-content">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header">
      <h2><i class="bi bi-archive-fill"></i> Archived Orders</h2>
      <a href="orders.php" class="btn btn-primary shadow-sm">
        <i class="bi bi-arrow-left"></i> Back to Orders
      </a>
    </div>

    <!-- Alerts -->
    <?php if (isset($_GET['restored'])): ?>
      <div class="alert alert-success shadow-sm">
        <i class="bi bi-check-circle"></i> Order restored successfully!
      </div>
    <?php elseif (isset($_GET['deleted'])): ?>
      <div class="alert alert-danger shadow-sm">
        <i class="bi bi-trash"></i> Archived order permanently deleted!
      </div>
    <?php endif; ?>

    <!-- Archived Cards Grid -->
    <div class="row mt-4">
      <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="archive-card d-flex flex-column">
              <h5><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($row['client_name'] ?? 'Unknown'); ?></h5>
              <p class="archive-meta mb-1"><i class="bi bi-hash"></i> Order ID: <?php echo $row['order_id']; ?></p>
              <p class="archive-amount mb-1">₱<?php echo number_format($row['total_amount'] ?? 0, 2); ?></p>

              <!-- Status Badges -->
              <div class="mb-2">
                <?php
                  $status = $row['status'] ?? 'Unknown';
                  $statusBadge = match($status) {
                    'Pending' => 'bg-warning text-dark',
                    'On Going' => 'bg-info text-dark',
                    'Done', 'Finished' => 'bg-success text-white',
                    default => 'bg-light text-dark'
                  };
                  $payStatus = $row['payment_status'] ?? 'Unknown';
                  $payBadge = match($payStatus) {
                    'Not Yet Paid' => 'bg-danger text-white',
                    'Paid Downpayment' => 'bg-warning text-dark',
                    'Paid Full in Cash' => 'bg-success text-white',
                    default => 'bg-light text-dark'
                  };
                ?>
                <span class="badge <?php echo $statusBadge; ?>"><?php echo htmlspecialchars($status); ?></span>
                <span class="badge <?php echo $payBadge; ?>"><?php echo htmlspecialchars($payStatus); ?></span>
              </div>

              <p class="archive-meta mb-0"><i class="bi bi-calendar-event"></i> Archived: <?php echo date('M d, Y', strtotime($row['date_archived'])); ?></p>
              <p class="archive-meta mb-3"><i class="bi bi-hourglass-split"></i> Warranty Ends: <?php echo htmlspecialchars($row['warranty_end'] ?? 'N/A'); ?></p>

              <div class="archive-actions mt-auto d-flex justify-content-center gap-2">
                <a href="archived_order_details.php?id=<?php echo $row['archive_id']; ?>" class="btn btn-sm btn-outline-info">
                  <i class="bi bi-eye"></i> View
                </a>
                <a href="restore_order.php?id=<?php echo $row['archive_id']; ?>" 
                   class="btn btn-sm btn-outline-success"
                   onclick="return confirm('Restore this order to active orders?');">
                  <i class="bi bi-arrow-counterclockwise"></i> Restore
                </a>
                <a href="delete_archive.php?id=<?php echo $row['archive_id']; ?>" 
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Permanently delete this archived order?');">
                  <i class="bi bi-trash"></i> Delete
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-center mt-5">
          <div class="alert alert-info py-4 shadow-sm">
            <i class="bi bi-info-circle"></i> No archived orders found.
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include('../includes/footer.php'); ?>
