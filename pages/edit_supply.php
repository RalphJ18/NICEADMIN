<?php
// pages/edit_supply.php
include('../db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: suppliers.php");
    exit();
}

$supply_id = intval($_GET['id']);
$supplier_id = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;

// fetch supply and supplier name
$stmt = $conn->prepare("SELECT s.*, sp.supplier_name FROM supplier_supplies s LEFT JOIN suppliers sp ON s.supplier_id = sp.supplier_id WHERE s.supply_id = ?");
$stmt->bind_param("i", $supply_id);
$stmt->execute();
$supply = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$supply) {
    echo "<div class='alert alert-danger'>Supply not found.</div>";
    exit();
}

$folder_safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $supply['supplier_name']);
$folderPath = "../assets/uploads/suppliers/{$folder_safe}/";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name']);
    $category = $_POST['category'] ?? null;
    $price = floatval($_POST['price']);
    $desc = trim($_POST['item_description']);
    $new_image = $supply['item_image'];

    // handle new image
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($folderPath)) mkdir($folderPath, 0755, true);
        $tmp  = $_FILES['item_image']['tmp_name'];
        $name = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['item_image']['name']));
        $dest = $folderPath . $name;
        if (move_uploaded_file($tmp, $dest)) {
            // delete old
            if (!empty($supply['item_image']) && file_exists($folderPath . $supply['item_image'])) {
                @unlink($folderPath . $supply['item_image']);
            }
            $new_image = $name;
        }
    }

    // update
    $up = $conn->prepare("UPDATE supplier_supplies SET category = ?, item_name = ?, price = ?, item_image = ?, item_description = ? WHERE supply_id = ?");
    $up->bind_param("ssdssi", $category, $item_name, $price, $new_image, $desc, $supply_id);
    if ($up->execute()) {
        $up->close();
        header("Location: supplier_supplies.php?supplier_id={$supply['supplier_id']}&updated=1");
        exit();
    } else {
        $message = "Error updating: " . $up->error;
        $up->close();
    }
}
?>

<?php include('../includes/header.php'); ?>

<main class="main-content py-5" style="min-height: 100vh; background:#eef4fb;">
  <div class="container" style="max-width:760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="fw-bold text-primary"><i class="bi bi-pencil-square"></i> Edit Supply</h3>
      <a href="supplier_supplies.php?supplier_id=<?php echo $supply['supplier_id']; ?>" class="btn btn-outline-secondary">Back</a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm">
      <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Supply Name</label>
          <input type="text" name="item_name" class="form-control" required value="<?php echo htmlspecialchars($supply['item_name']); ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Category</label>
          <select name="category" class="form-select" required>
            <?php
            $cats = ['LCD Replacement','Battery Replacement','Power/Volume Button','Middle Frame','LCD Frame','Back Cover'];
            foreach($cats as $c){
              $sel = ($supply['category'] === $c) ? 'selected' : '';
              echo "<option value=\"{$c}\" {$sel}>{$c}</option>";
            }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Price (₱)</label>
          <input type="number" name="price" class="form-control" step="0.01" required value="<?php echo number_format($supply['price'], 2, '.', ''); ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="item_description" class="form-control" rows="3"><?php echo htmlspecialchars($supply['item_description']); ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Replace Image (optional)</label><br>
          <?php if (!empty($supply['item_image'])): ?>
            <img src="<?php echo $folderPath . htmlspecialchars($supply['item_image']); ?>" style="height:120px; object-fit:cover; border-radius:8px;" class="mb-2">
          <?php endif; ?>
          <input type="file" name="item_image" class="form-control" accept="image/*">
        </div>

        <button class="btn btn-primary w-100">Save Changes</button>
      </form>
    </div>
  </div>
</main>

<?php include('../includes/footer.php'); ?>
