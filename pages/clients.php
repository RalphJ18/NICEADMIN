<?php
session_start();
include('../db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch unique clients
$query = "SELECT MIN(client_id) AS client_id, fullname, email, contact_number, address, MIN(date_added) AS date_added 
          FROM clients 
          GROUP BY fullname, email, contact_number, address 
          ORDER BY fullname ASC";
$result = mysqli_query($conn, $query);
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

  .btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .btn-info {
    background-color: #17a2b8;
    border: none;
  }

  .btn-info:hover {
    background-color: #138496;
    transform: translateY(-2px);
  }

  .text-muted {
    color: #777 !important;
  }

  @media (max-width: 768px) {
    .table {
      font-size: 0.9rem;
    }

    h2 {
      font-size: 1.4rem;
    }
  }
</style>

<main class="main-content">
  <div class="container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold text-primary">
        <i class="bi bi-people"></i> Clients
      </h2>
    </div>

    <!-- Clients Table -->
    <div class="card">
      <div class="card-body table-responsive">
        <table class="table align-middle text-center mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Contact Number</th>
              <th>Address</th>
              <th>Date Registered</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><strong>#<?php echo $row['client_id']; ?></strong></td>
                  <td class="fw-semibold text-dark"><?php echo htmlspecialchars($row['fullname']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                  <td><?php echo htmlspecialchars($row['address']); ?></td>
                  <td><small><?php echo date('M d, Y', strtotime($row['date_added'])); ?></small></td>
                  <td>
                    <a href="view_client_orders.php?id=<?php echo $row['client_id']; ?>" 
                       class="btn btn-sm btn-info text-white">
                      <i class="bi bi-list-ul"></i> View Orders
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-muted py-4">No clients found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<?php include('../includes/footer.php'); ?>
