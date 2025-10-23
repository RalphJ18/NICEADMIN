<?php
session_start();
include(__DIR__ . '/db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

// Fetch stats
$total_orders   = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'] ?? 0;
$total_pending  = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE payment_status='Not Yet Paid'")->fetch_assoc()['total'] ?? 0;
$total_ongoing  = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='On Going'")->fetch_assoc()['total'] ?? 0;
$total_done     = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Done'")->fetch_assoc()['total'] ?? 0;

// Include header
include('includes/header.php');
?>

<!-- Modern Dashboard Style -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
  body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background: url('assets/bg/bg.jpg') no-repeat center center/cover;
    height: 100vh;
    overflow-x: hidden;
    color: #fff;
  }

  .overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.55);
    z-index: -1;
  }

  .dashboard-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 40px 20px;
  }

  .dashboard-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
  }

  .dashboard-header img {
    width: 65px;
    height: 65px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.3);
  }

  .dashboard-header h1 {
    font-weight: 700;
    font-size: 2.8rem;
    color: #fff;
    margin: 0;
    text-shadow: 0 2px 10px rgba(0,0,0,0.4);
  }

  .create-order-btn {
    background-color: #007bff;
    border: none;
    padding: 14px 40px;
    font-size: 1.1rem;
    border-radius: 50px;
    color: #fff;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.4);
    margin-bottom: 40px;
  }

  .create-order-btn:hover {
    background-color: #0056b3;
    transform: translateY(-3px);
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    width: 100%;
    max-width: 1000px;
  }

  .stat-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 30px 20px;
    color: #fff;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.2);
  }

  .stat-card h5 {
    font-weight: 500;
    font-size: 1.1rem;
    margin-bottom: 10px;
  }

  .stat-card h2 {
    font-weight: 700;
    font-size: 2.4rem;
    margin: 0;
  }

  @media (max-width: 768px) {
    .dashboard-header h1 {
      font-size: 2rem;
    }
    .dashboard-header img {
      width: 55px;
      height: 55px;
    }
    .create-order-btn {
      padding: 12px 30px;
      font-size: 1rem;
    }
  }
</style>

<div class="overlay"></div>

<main class="dashboard-container">
  <!-- Header with logo -->
  <div class="dashboard-header">
    <img src="assets/bg/logo.jpg" alt="Sales Monitoring Logo">
    <h1>Sales Monitoring</h1>
  </div>

  <!-- Create Order Button -->
  <a href="pages/add_order.php" class="create-order-btn">
    <i class="bi bi-plus-circle"></i> Create an Order
  </a>

  <!-- Stats Section -->
  <div class="stats-grid">
    <div class="stat-card">
      <h5>Total Orders</h5>
      <h2><?php echo $total_orders; ?></h2>
    </div>

    <div class="stat-card">
      <h5>Pending Payments</h5>
      <h2><?php echo $total_pending; ?></h2>
    </div>

    <div class="stat-card">
      <h5>Ongoing Repairs</h5>
      <h2><?php echo $total_ongoing; ?></h2>
    </div>

    <div class="stat-card">
      <h5>Completed Repairs</h5>
      <h2><?php echo $total_done; ?></h2>
    </div>
  </div>
</main>

<?php include('includes/footer.php'); ?>
