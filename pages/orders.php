<?php
session_start();
include('../db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// ✅ Fetch all NON-ARCHIVED orders with total_cost + client info
$query = "
  SELECT 
    o.order_id,
    o.status AS repair_status,
    o.payment_status,
    o.warranty_end,
    o.date_created,
    o.after_image,
    c.fullname AS client_name,
    c.contact_number,
    d.total_cost
  FROM orders o
  LEFT JOIN clients c ON o.client_id = c.client_id
  LEFT JOIN order_details d ON o.order_id = d.order_id
  WHERE o.is_archived = 0
  ORDER BY o.date_created DESC
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

  h2 {
    font-weight: 700;
    color: #007bff;
  }

  .main-content {
    padding-top: 60px;
    padding-bottom: 60px;
    min-height: 100vh;
  }

  .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  }

  .card-body {
    padding: 25px 30px;
  }

  .table {
    border-collapse: separate;
    border-spacing: 0 10px;
  }

  .table thead {
    border-radius: 12px;
  }

  .table thead th {
    background-color: #007bff;
    color: #fff;
    border: none;
    font-weight: 500;
    padding: 14px;
    text-align: center;
  }

  .table tbody tr {
    background: #fff;
    border-radius: 12px;
    transition: all 0.2s ease;
  }

  .table tbody tr:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }

  .table tbody td {
    vertical-align: middle;
    border-top: none;
    padding: 16px 12px;
    text-align: center;
  }

  .badge {
    font-size: 0.85rem;
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 500;
  }

  .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 6px 12px;
  }

  .btn-sm i {
    margin-right: 4px;
  }

  .alert {
    border-radius: 10px;
    font-weight: 500;
  }

  .fw-bold.text-primary {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .btn-primary {
    background-color: #007bff;
    border: none;
  }

  .btn-primary:hover {
    background-color: #0056b3;
  }

  .btn-warning {
    color: #212529;
  }

  .btn-info {
    background-color: #17a2b8;
    border: none;
  }

  .btn-info:hover {
    background-color: #138496;
  }

  .btn-secondary:hover {
    background-color: #5c636a;
  }

  @media (max-width: 768px) {
    .table {
      font-size: 0.9rem;
    }
  }
</style>

<main class="main-content">
  <div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold text-primary"><i class="bi bi-bag"></i> Orders</h2>
      <a href="add_order.php" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle"></i> Add Order
      </a>
    </div>

    <!-- ✅ Alerts -->
    <?php if (isset($_GET['updated'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> Order updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php elseif (isset($_GET['archived'])): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-archive"></i> Order archived successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Orders Table -->
    <div class="card">
      <div class="card-body table-responsive">
        <table class="table align-middle text-center mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Client</th>
              <th>Total Cost</th>
              <th>Repair Status</th>
              <th>Payment Status</th>
              <th>Warranty End</th>
              <th>Date Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><strong>#<?php echo $row['order_id']; ?></strong></td>
                  <td>
                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['client_name'] ?? 'Unknown'); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($row['contact_number'] ?? ''); ?></small>
                  </td>
                  <td class="fw-semibold text-success">₱<?php echo number_format($row['total_cost'] ?? 0, 2); ?></td>

                  <!-- 🧰 Repair Status -->
                  <td>
                    <?php
                      $status = $row['repair_status'] ?? 'Pending';
                      $statusBadge = match($status) {
                        'Pending' => 'bg-warning text-dark',
                        'On Going' => 'bg-info text-dark',
                        'Done' => 'bg-success text-white',
                        'Use Warranty' => 'bg-secondary text-white',
                        default => 'bg-light text-dark'
                      };
                    ?>
                    <span class="badge <?php echo $statusBadge; ?>">
                      <?php echo htmlspecialchars($status); ?>
                    </span>
                  </td>

                  <!-- 💵 Payment Status -->
                  <td>
                    <?php
                      $payStatus = $row['payment_status'] ?? 'Not Yet Paid';
                      $payBadge = match($payStatus) {
                        'Downpayment' => 'bg-warning text-dark',
                        'Paid in Cash' => 'bg-success text-white',
                        'Installment' => 'bg-info text-white',
                        'Not Yet Paid' => 'bg-danger text-white',
                        default => 'bg-light text-dark'
                      };
                    ?>
                    <span class="badge <?php echo $payBadge; ?>">
                      <?php echo htmlspecialchars($payStatus); ?>
                    </span>
                  </td>

                  <td><small><?php echo htmlspecialchars($row['warranty_end'] ?? 'N/A'); ?></small></td>
                  <td><small><?php echo date('M d, Y', strtotime($row['date_created'])); ?></small></td>

                  <td>
                    <a href="order_details.php?id=<?php echo $row['order_id']; ?>" 
                       class="btn btn-sm btn-info text-white mb-1">
                      <i class="bi bi-eye"></i> View
                    </a>
                    <a href="update_order_status.php?id=<?php echo $row['order_id']; ?>" 
                       class="btn btn-sm btn-warning text-dark mb-1">
                      <i class="bi bi-pencil-square"></i> Update
                    </a>
                    <a href="archive_order.php?id=<?php echo $row['order_id']; ?>" 
                       class="btn btn-sm btn-secondary text-white mb-1"
                       onclick="return confirm('Archive this order?');">
                      <i class="bi bi-archive-fill"></i> Archive
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-muted py-4">No active orders found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<?php include('../includes/footer.php'); ?>
