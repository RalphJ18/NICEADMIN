<?php
include '../db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$order_id = $_GET['order_id'] ?? 0;

// Include header
include('../includes/header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
  body {
    background-color: #f5f7fa;
    font-family: 'Poppins', sans-serif;
  }

  .receipt-container {
    max-width: 700px;
    margin: 100px auto;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    padding: 50px 40px;
    text-align: center;
  }

  .receipt-container h2 {
    font-weight: 700;
    color: #007bff;
    margin-bottom: 15px;
  }

  .receipt-container p {
    color: #555;
    font-size: 1.05rem;
  }

  .alert-info {
    border-radius: 10px;
    font-size: 0.95rem;
    background-color: #e9f3ff;
    color: #0b5ed7;
    border: none;
  }

  .btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 10px 25px;
    transition: all 0.3s ease;
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
    background-color: #565e64;
    transform: translateY(-2px);
  }

  .icon {
    font-size: 2.3rem;
    color: #007bff;
  }

  @media (max-width: 768px) {
    .receipt-container {
      margin: 60px 20px;
      padding: 35px 25px;
    }
  }
</style>

<main>
  <div class="receipt-container">
    <div class="icon mb-3">
      <i class="bi bi-receipt-cutoff"></i>
    </div>

    <h2>Order #<?php echo htmlspecialchars($order_id); ?></h2>
    <p>✅ Your order has been successfully created and saved in the system.</p>

    <div class="alert alert-info mt-4">
      <i class="bi bi-file-earmark-pdf"></i> A downloadable PDF receipt will appear here later.
    </div>

    <div class="mt-4">
      <a href="orders.php" class="btn btn-secondary me-2">
        <i class="bi bi-arrow-left"></i> Go Back to Orders
      </a>
      <a href="add_order.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Submit Another Order
      </a>
    </div>
  </div>
</main>

<?php include('../includes/footer.php'); ?>
