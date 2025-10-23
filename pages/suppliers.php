<?php
include('../db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch all suppliers
$query = "SELECT * FROM suppliers ORDER BY date_added DESC";
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

  h2 {
    font-weight: 700;
    color: #007bff;
  }

  .page-header {
    margin-bottom: 40px;
  }

  .page-header h2 {
    font-size: 1.9rem;
    margin-bottom: 8px;
  }

  .page-header p {
    color: #555;
    font-size: 0.95rem;
    margin-bottom: 15px;
  }

  .btn-primary {
    background-color: #007bff;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
  }

  .btn-primary:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
  }

  .btn-outline-primary {
    border-radius: 8px;
    font-weight: 600;
  }

  .category-title {
    font-weight: 700;
    color: #333;
    margin: 40px 0 20px;
    font-size: 1.3rem;
  }

  .supplier-card {
    border: none;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    text-align: center;
    padding: 25px 15px;
    transition: all 0.25s ease;
  }

  .supplier-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 25px rgba(0,0,0,0.08);
  }

  .supplier-logo {
    width: 110px;
    height: 110px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #007bff;
    margin-bottom: 15px;
    background-color: #f8f9fa;
  }

  .supplier-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 12px;
    font-size: 1.1rem;
  }

  .supplier-actions .btn {
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 8px;
  }

  .alert {
    border-radius: 10px;
  }

  @media (max-width: 768px) {
    .page-header {
      text-align: center;
    }
  }
</style>

<main class="main-content">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header text-center text-md-start">
      <h2><i class="bi bi-truck"></i> Suppliers</h2>
      <p>Manage your registered suppliers and view their available supplies.</p>
      <a href="add_supplier.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Supplier
      </a>
    </div>

    <!-- Success Message -->
    <?php if (isset($_GET['added'])): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle"></i> Supplier added successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Section Title -->
    <h4 class="category-title"><i class="bi bi-grid"></i> Supplier List</h4>

    <!-- Supplier Cards Grid -->
    <div class="row justify-content-start">
      <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($supplier = mysqli_fetch_assoc($result)): ?>
          <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="supplier-card">
              <img src="../assets/uploads/suppliers/<?php echo !empty($supplier['logo']) ? htmlspecialchars($supplier['logo']) : 'default-logo.png'; ?>" 
                   alt="Supplier Logo"
                   class="supplier-logo">
              
              <div class="supplier-name"><?php echo htmlspecialchars($supplier['supplier_name']); ?></div>

              <div class="supplier-actions d-grid gap-2">
                <a href="supplier_supplies.php?supplier_id=<?php echo $supplier['supplier_id']; ?>" 
                   class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-box-seam"></i> View Supplies
                </a>
                <a href="edit_supplier.php?id=<?php echo $supplier['supplier_id']; ?>" 
                   class="btn btn-outline-warning btn-sm">
                  <i class="bi bi-pencil-square"></i> Edit Supplier
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-center mt-5">
          <div class="alert alert-info py-4 shadow-sm">
            <i class="bi bi-info-circle"></i> No suppliers found. Add your first supplier above.
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<script>
  // Auto-hide alert
  setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) alert.classList.remove('show');
  }, 3000);
</script>

<?php include('../includes/footer.php'); ?>
