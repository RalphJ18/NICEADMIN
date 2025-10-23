<?php
session_start();
include('../db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Get selected month (default to current)
$month = $_GET['month'] ?? date('Y-m');
$monthStart = $month . '-01';
$monthEnd = date('Y-m-t', strtotime($monthStart));

// --- Monthly Income and Profit ---
$incomeQuery = $conn->query("
  SELECT 
    SUM(d.total_cost) AS total_sales, 
    SUM(d.repair_cost) AS total_profit 
  FROM order_details d
  INNER JOIN orders o ON o.order_id = d.order_id 
  WHERE DATE(o.created_at) BETWEEN '$monthStart' AND '$monthEnd'
");
$income = $incomeQuery->fetch_assoc();
$total_sales = $income['total_sales'] ?? 0;
$total_profit = $income['total_profit'] ?? 0;

// --- Top Customers ---
$topCustomersQuery = $conn->query("
  SELECT 
    c.fullname, 
    COUNT(o.order_id) AS total_orders, 
    SUM(d.total_cost) AS total_spent
  FROM orders o
  INNER JOIN clients c ON c.client_id = o.client_id
  INNER JOIN order_details d ON d.order_id = o.order_id
  WHERE DATE(o.created_at) BETWEEN '$monthStart' AND '$monthEnd'
  GROUP BY c.client_id
  ORDER BY total_spent DESC
  LIMIT 5
");
$topCustomers = $topCustomersQuery->fetch_all(MYSQLI_ASSOC);
?>

<?php include('../includes/header.php'); ?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
  body {
    font-family: 'Poppins', sans-serif;
    background-color: #f5f7fa;
  }

  /* Hero Section */
  .hero {
    position: relative;
    background: url('../assets/bg/bg.jpg') center/cover no-repeat;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #fff;
  }

  .hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
  }

  .hero-content {
    position: relative;
    z-index: 1;
    max-width: 700px;
  }

  .hero h1 {
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 10px;
  }

  .hero p {
    font-size: 1.1rem;
    color: #e1e1e1;
  }

  /* Filter Form */
  .filter-form {
    margin-top: -60px;
    background: #fff;
    padding: 25px 30px;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
  }

  .filter-form label {
    font-weight: 600;
    color: #555;
  }

  .filter-form input {
    border-radius: 10px;
    padding: 8px 12px;
  }

  /* Summary Cards */
  .summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-top: 40px;
  }

  .summary-card {
    background: #fff;
    border: none;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.25s ease;
  }

  .summary-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 25px rgba(0,0,0,0.1);
  }

  .summary-card h5 {
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 8px;
  }

  .summary-card h3 {
    font-weight: 700;
  }

  .summary-card.sales h3 {
    color: #28a745;
  }

  .summary-card.profit h3 {
    color: #007bff;
  }

  /* Table Section */
  .report-section {
    background: #fff;
    margin-top: 50px;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.06);
  }

  .report-section h4 {
    font-weight: 700;
    color: #333;
    margin-bottom: 20px;
  }

  .table {
    border-radius: 10px;
    overflow: hidden;
  }

  .table thead {
    background-color: #007bff;
    color: #fff;
  }

  .table tbody tr:hover {
    background-color: #f9f9f9;
  }

  @media (max-width: 768px) {
    .hero h1 {
      font-size: 2.2rem;
    }
  }
</style>

<main>
  <!-- Hero -->
  <section class="hero">
    <div class="hero-content">
      <h1><i class="bi bi-graph-up"></i> Explore a World of Sales</h1>
      <p>Get detailed monthly reports on your business performance and top customers.</p>
    </div>
  </section>

  <!-- Filter & Content -->
  <div class="container py-5">
    <!-- Filter -->
    <form method="GET" class="filter-form">
      <div>
        <label for="month">Select Month:</label>
        <input type="month" name="month" id="month" class="form-control"
               value="<?php echo htmlspecialchars($month); ?>" onchange="this.form.submit()">
      </div>
      <div>
        <span class="fw-semibold text-muted">
          Showing results for <strong><?php echo date('F Y', strtotime($monthStart)); ?></strong>
        </span>
      </div>
    </form>

    <!-- Summary Cards -->
    <div class="summary">
      <div class="summary-card sales">
        <h5>Total Monthly Sales</h5>
        <h3>₱<?php echo number_format($total_sales, 2); ?></h3>
      </div>
      <div class="summary-card profit">
        <h5>Total Monthly Profit</h5>
        <h3>₱<?php echo number_format($total_profit, 2); ?></h3>
      </div>
    </div>

    <!-- Top Customers -->
    <div class="report-section">
      <h4><i class="bi bi-person-badge"></i> Top 5 Customers</h4>
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Orders</th>
              <th>Total Spent</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($topCustomers): foreach ($topCustomers as $cust): ?>
              <tr>
                <td><?php echo htmlspecialchars($cust['fullname']); ?></td>
                <td><?php echo $cust['total_orders']; ?></td>
                <td>₱<?php echo number_format($cust['total_spent'], 2); ?></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="3" class="text-muted py-3">No customer data for this month.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<?php include('../includes/footer.php'); ?>
