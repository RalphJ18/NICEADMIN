<?php
include('../db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

if (!isset($_GET['supplier_id']) || !is_numeric($_GET['supplier_id'])) {
  header("Location: suppliers.php");
  exit();
}

$supplier_id = intval($_GET['supplier_id']);

// Fetch supplier info
$supplier_stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
$supplier_stmt->bind_param("i", $supplier_id);
$supplier_stmt->execute();
$supplier = $supplier_stmt->get_result()->fetch_assoc();
$supplier_stmt->close();

if (!$supplier) {
  echo "<script>alert('Supplier not found!'); window.location='suppliers.php';</script>";
  exit();
}

// Fetch supplies
$supplies_stmt = $conn->prepare("SELECT * FROM supplier_supplies WHERE supplier_id = ? ORDER BY date_added DESC");
$supplies_stmt->bind_param("i", $supplier_id);
$supplies_stmt->execute();
$supplies_result = $supplies_stmt->get_result();
$supplies_stmt->close();

// Safe folder name
$folder_safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $supplier['supplier_name']);
$folderPath = "../assets/uploads/suppliers/{$folder_safe}/";
$logoSrc = "../assets/uploads/suppliers/" . (!empty($supplier['logo']) ? htmlspecialchars($supplier['logo']) : 'default-logo.png');
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

  .supplier-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
  }

  .supplier-info {
    display: flex;
    align-items: center;
    gap: 20px;
  }

  .supplier-info img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #007bff;
  }

  .supplier-info h3 {
    font-weight: 700;
    color: #007bff;
  }

  .supplier-info p {
    color: #555;
    margin-bottom: 0;
  }

  .supplier-actions .btn {
    border-radius: 8px;
    font-weight: 600;
  }

  /* ✅ Supply Card Design */
  .supply-card {
    border: none;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    text-align: center;
    padding: 20px;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
  }

  .supply-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 25px rgba(0,0,0,0.08);
  }

  /* ✅ Make supply images square and centered */
  .supply-card img {
    width: 180px;
    height: 180px;
    object-fit: cover;
    border-radius: 12px;
    margin: 0 auto 15px auto;
    display: block;
  }

  .supply-card h6 {
    font-weight: 600;
    color: #333;
  }

  .badge {
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 6px;
  }

  .supply-price {
    font-weight: 600;
    color: #28a745;
  }

  .supply-desc {
    color: #666;
    font-size: 0.9rem;
    height: 40px;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .alert {
    border-radius: 10px;
  }

  @media (max-width: 768px) {
    .supplier-header {
      flex-direction: column;
      text-align: center;
      gap: 15px;
    }

    .supplier-info {
      flex-direction: column;
      text-align: center;
    }

    .supplier-actions {
      flex-wrap: wrap;
      justify-content: center;
      gap: 8px;
    }

    .supply-card img {
      width: 150px;
      height: 150px;
    }
  }
</style>


<main class="main-content">
  <div class="container">

    <!-- Supplier Header -->
    <div class="supplier-header">
      <div class="supplier-info">
        <img src="<?php echo $logoSrc; ?>" alt="Supplier Logo">
        <div>
          <h3><?php echo htmlspecialchars($supplier['supplier_name']); ?></h3>
          <p><?php echo htmlspecialchars($supplier['address'] ?? ''); ?></p>
        </div>
      </div>

      <div class="supplier-actions d-flex gap-2 flex-wrap">
        <a href="add_supply.php?supplier_id=<?php echo $supplier_id; ?>" class="btn btn-success">
          <i class="bi bi-plus-circle"></i> Add Supply
        </a>
        <a href="edit_supplier.php?id=<?php echo $supplier_id; ?>" class="btn btn-warning">
          <i class="bi bi-pencil-square"></i> Edit Supplier
        </a>
        <a href="delete_supplier.php?supplier_id=<?php echo $supplier_id; ?>" 
           class="btn btn-danger"
           onclick="return confirm('Are you sure you want to delete this supplier and all its supplies?');">
          <i class="bi bi-trash"></i> Delete Supplier
        </a>
        <a href="suppliers.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <!-- Section Divider -->
    <h4 class="fw-bold text-dark mb-4"><i class="bi bi-box-seam"></i> Supplies</h4>

    <!-- Supplies Grid -->
    <div class="row justify-content-start">
      <?php if ($supplies_result && mysqli_num_rows($supplies_result) > 0): ?>
        <?php while ($supply = mysqli_fetch_assoc($supplies_result)): ?>
          <?php
            $supply_image = !empty($supply['item_image']) ? htmlspecialchars($supply['item_image']) : 'no-image.png';
            $image_src = $folderPath . $supply_image;
          ?>
          <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="supply-card h-100 d-flex flex-column">
              <img src="<?php echo $image_src; ?>" alt="Supply Image">
              <h6 class="mb-1"><?php echo htmlspecialchars($supply['item_name']); ?></h6>
              <p><span class="badge bg-primary"><?php echo htmlspecialchars($supply['category']); ?></span></p>
              <p class="supply-price mb-1">₱<?php echo number_format($supply['price'], 2); ?></p>
              <p class="supply-desc mb-2"><?php echo htmlspecialchars($supply['item_description']); ?></p>

              <div class="mt-auto d-flex justify-content-center gap-2">
                <a href="edit_supply.php?id=<?php echo $supply['supply_id']; ?>&supplier_id=<?php echo $supplier_id; ?>" class="btn btn-sm btn-outline-warning">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="delete_supply.php?id=<?php echo $supply['supply_id']; ?>&supplier_id=<?php echo $supplier_id; ?>" 
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this supply?');">
                  <i class="bi bi-trash"></i> Delete
                </a>
              </div>

              <p class="text-muted small mt-2 mb-0"><i class="bi bi-calendar-event"></i> <?php echo date('M d, Y', strtotime($supply['date_added'])); ?></p>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-center mt-5">
          <div class="alert alert-info py-4 shadow-sm">
            <i class="bi bi-info-circle"></i> No supplies added yet for this supplier.
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php include('../includes/footer.php'); ?>
