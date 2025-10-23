<?php
include '../db_connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Validate client ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Client ID not provided.");
}

$client_id = intval($_GET['id']);

// Fetch client info
$client_query = "SELECT * FROM clients WHERE client_id = $client_id LIMIT 1";
$client_result = mysqli_query($conn, $client_query);
$client = mysqli_fetch_assoc($client_result);

if (!$client) {
    die("Client not found.");
}

// Fetch client’s orders
$order_query = "
    SELECT 
        o.order_id,
        o.total_amount,
        o.status,
        o.payment_status,
        o.warranty_end,
        o.created_at,
        o.before_image,
        o.after_image,
        d.issue_description
    FROM orders o
    LEFT JOIN order_details d ON o.order_id = d.order_id
    WHERE o.client_id = $client_id
    ORDER BY o.created_at DESC
";
$order_result = mysqli_query($conn, $order_query);
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

  h3 {
    font-weight: 700;
    color: #007bff;
  }

  .card {
    background: #fff;
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  }

  .card-body {
    padding: 25px 30px;
  }

  .table {
    border-collapse: separate;
    border-spacing: 0 10px;
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
    transition: all 0.25s ease;
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

  .img-thumbnail {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
  }

  .img-thumbnail:hover {
    transform: scale(1.05);
  }

  .badge {
    padding: 8px 10px;
    font-size: 0.85rem;
    border-radius: 8px;
    font-weight: 500;
  }

  .btn {
    border-radius: 8px;
    font-weight: 600;
  }

  .btn-secondary {
    background-color: #6c757d;
    border: none;
  }

  .btn-secondary:hover {
    background-color: #5a6268;
  }

  .alert {
    border-radius: 10px;
  }

  @media (max-width: 768px) {
    .table {
      font-size: 0.9rem;
    }
  }
</style>

<main class="main-content">
  <div class="container mt-4">

    <!-- ✅ Success Alert -->
    <?php if (isset($_GET['added'])): ?>
      <div id="successAlert" class="alert alert-success alert-dismissible fade show text-center shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill"></i> 
        <strong>Success!</strong> A new order has been added for 
        <strong><?php echo htmlspecialchars($client['fullname']); ?></strong>.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($client['fullname']); ?>’s Orders</h3>
      <a href="clients.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Clients
      </a>
    </div>

    <!-- Client Info -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body">
        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-info-circle"></i> Client Details</h5>
        <div class="row">
          <div class="col-md-6">
            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($client['contact_number']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($client['email']); ?></p>
          </div>
          <div class="col-md-6">
            <p><strong>Address:</strong> <?php echo htmlspecialchars($client['address']); ?></p>
            <p><strong>Date Added:</strong> <?php echo date('M d, Y', strtotime($client['date_added'])); ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-body">
        <?php if ($order_result && mysqli_num_rows($order_result) > 0): ?>
          <table class="table table-hover align-middle text-center">
            <thead>
              <tr>
                <th>#</th>
                <th>Issue Description</th>
                <th>Total Amount</th>
                <th>Order Status</th>
                <th>Payment Status</th>
                <th>Warranty End</th>
                <th>Date Created</th>
                <th>Before</th>
                <th>After</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($order = mysqli_fetch_assoc($order_result)): ?>
                <tr>
                  <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                  <td class="text-start"><?php echo htmlspecialchars($order['issue_description'] ?? '—'); ?></td>
                  <td class="fw-semibold text-success">₱<?php echo number_format($order['total_amount'], 2); ?></td>

                  <!-- Order Status -->
                  <td>
                    <?php
                      $statusClass = match($order['status']) {
                        'Pending' => 'bg-warning text-dark',
                        'On Going' => 'bg-info text-dark',
                        'Done' => 'bg-success text-white',
                        'Finished' => 'bg-secondary text-white',
                        default => 'bg-light text-dark'
                      };
                    ?>
                    <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                  </td>

                  <!-- Payment Status -->
                  <td>
                    <?php
                      $payClass = match($order['payment_status']) {
                        'Not Yet Paid' => 'bg-danger text-white',
                        'Paid Downpayment' => 'bg-warning text-dark',
                        'Paid Full in Cash' => 'bg-success text-white',
                        default => 'bg-light text-dark'
                      };
                    ?>
                    <span class="badge <?php echo $payClass; ?>"><?php echo htmlspecialchars($order['payment_status']); ?></span>
                  </td>

                  <td><?php echo htmlspecialchars($order['warranty_end'] ?? 'N/A'); ?></td>
                  <td><small><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small></td>

                  <!-- Before Image -->
                  <td>
                    <?php if (!empty($order['before_image'])): ?>
                      <img src="../assets/uploads/<?php echo htmlspecialchars($order['before_image']); ?>" 
                           class="img-thumbnail" width="70" alt="Before">
                    <?php else: ?>
                      <span class="text-muted">No image</span>
                    <?php endif; ?>
                  </td>

                  <!-- After Image -->
                  <td>
                    <?php if (!empty($order['after_image'])): ?>
                      <img src="../assets/uploads/<?php echo htmlspecialchars($order['after_image']); ?>" 
                           class="img-thumbnail" width="70" alt="After">
                    <?php else: ?>
                      <span class="text-muted">No image</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="alert alert-info text-center mb-0">
            <i class="bi bi-info-circle"></i> No orders found for this client.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php include('../includes/footer.php'); ?>

<!-- ✅ Auto-Fade Success Alert -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const alert = document.getElementById("successAlert");
    if (alert) {
      setTimeout(() => {
        alert.classList.add("fade");
        setTimeout(() => alert.remove(), 500);
      }, 5000);
    }
  });
</script>
