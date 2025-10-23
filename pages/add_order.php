<?php
include '../db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Client info
    $fullname       = trim($_POST['fullname']);
    $contact_number = trim($_POST['contact_number']);
    $address        = trim($_POST['address']);
    $email          = trim($_POST['email']);
    $issue          = trim($_POST['issue']);

    // Order info
    $type_of_repair = isset($_POST['type_of_repair']) ? implode(', ', $_POST['type_of_repair']) : '';
    $payment_status = trim($_POST['payment_status']);
    $part_cost      = floatval($_POST['part_cost']);
    $repair_cost    = floatval($_POST['repair_cost']);
    $total_cost     = $part_cost + $repair_cost;

    // Dates
    $date_created   = date('Y-m-d');
    $warranty_start = $date_created;
    $warranty_end   = date('Y-m-d', strtotime('+1 month', strtotime($warranty_start)));
    $created_at     = date('Y-m-d H:i:s');

    // Upload before image
    $uploadDir = '../assets/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $before_image = null;
    if (isset($_FILES['before_image']) && $_FILES['before_image']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['before_image']['tmp_name'];
        $name = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['before_image']['name']));
        $dest = $uploadDir . $name;
        if (move_uploaded_file($tmp, $dest)) {
            $before_image = $name;
        }
    }

    // Find or create client
    $client_id = null;
    $sql = "SELECT client_id FROM clients WHERE fullname=? AND (contact_number=? OR email=?) LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $fullname, $contact_number, $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $client_id = $res->fetch_assoc()['client_id'];
    } else {
        $insert_client = "INSERT INTO clients (fullname, contact_number, address, email, date_added) VALUES (?, ?, ?, ?, NOW())";
        $stmt2 = $conn->prepare($insert_client);
        $stmt2->bind_param('ssss', $fullname, $contact_number, $address, $email);
        $stmt2->execute();
        $client_id = $stmt2->insert_id;
        $stmt2->close();
    }
    $stmt->close();

    // Insert order
    $insert_order = "INSERT INTO orders (client_id, issue, type_of_repair, payment_status, warranty_start, warranty_end, date_created, created_at, before_image)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt3 = $conn->prepare($insert_order);
    $stmt3->bind_param(
        'issssssss',
        $client_id, $issue, $type_of_repair, $payment_status,
        $warranty_start, $warranty_end, $date_created, $created_at, $before_image
    );

    if ($stmt3->execute()) {
        $order_id = $stmt3->insert_id;
        $stmt3->close();

        // Insert into order_details
        $insert_detail = "INSERT INTO order_details (order_id, issue_description, part_cost, repair_cost, total_cost, date_added)
                          VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt4 = $conn->prepare($insert_detail);
        $stmt4->bind_param('isddd', $order_id, $issue, $part_cost, $repair_cost, $total_cost);
        $stmt4->execute();
        $stmt4->close();

        header("Location: generate_receipt.php?order_id=$order_id");
        exit();
    } else {
        $message = "Error creating order: " . $stmt3->error;
    }
}

// Include header
include('../includes/header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  body {
    background-color: #f5f7fa;
    font-family: 'Poppins', sans-serif;
  }
  .page-container {
    max-width: 1100px;
    margin: 50px auto;
    display: flex;
    gap: 30px;
    padding: 0 20px;
  }
  .form-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    padding: 30px 40px;
    flex: 2;
  }
  .sidebar-summary {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    padding: 25px;
    flex: 1;
    height: fit-content;
  }
  h3.page-title {
    color: #007bff;
    font-weight: 700;
    margin-bottom: 25px;
  }
  .section-title {
    font-weight: 600;
    font-size: 1.1rem;
    color: #333;
    border-bottom: 2px solid #eaeaea;
    padding-bottom: 5px;
    margin-top: 20px;
    margin-bottom: 15px;
  }
  .form-label {
    font-weight: 500;
    color: #555;
  }
  .form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #dcdcdc;
    box-shadow: none;
  }
  .form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.15rem rgba(0,123,255,0.25);
  }
  .btn-primary {
    background-color: #007bff;
    border: none;
    font-weight: 600;
    padding: 12px;
    border-radius: 8px;
    transition: all 0.3s ease;
  }
  .btn-primary:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
  }
  .form-check-label {
    color: #444;
  }
</style>

<script>
  function calculateTotal() {
    const part = parseFloat(document.getElementById('part_cost').value) || 0;
    const repair = parseFloat(document.getElementById('repair_cost').value) || 0;
    document.getElementById('total_cost').value = (part + repair).toFixed(2);
  }
</script>

<main class="page-container">
  <div class="form-section">
    <h3 class="page-title"><i class="bi bi-plus-circle"></i> Add New Order</h3>

    <?php if (!empty($message)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="section-title"><i class="bi bi-person-lines-fill"></i> Client Information</div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input type="text" name="fullname" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Contact Number</label>
          <input type="text" name="contact_number" class="form-control" required>
        </div>
        <div class="col-md-12 mt-3">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control" required>
        </div>
        <div class="col-md-12 mt-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-12 mt-3">
          <label class="form-label">Issue Description</label>
          <textarea name="issue" class="form-control" rows="3" required></textarea>
        </div>
      </div>

      <div class="section-title"><i class="bi bi-tools"></i> Order Information</div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Type of Repair</label>
        <?php
        $services = [
          "LCD Replacement", "Battery Replacement", "Back Cover Replacement",
          "Middle Frame Replacement", "LCD Frame Replacement",
          "Power/Volume Replacement", "Bootloop Repair", "Forgotten Password / Remove FRP"
        ];
        foreach ($services as $service) {
            echo "<div class='form-check'>
                    <input class='form-check-input' type='checkbox' name='type_of_repair[]' value=\"$service\">
                    <label class='form-check-label'>$service</label>
                  </div>";
        }
        ?>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Payment Status</label>
          <select name="payment_status" class="form-select" required>
            <option value="Downpayment">Downpayment</option>
            <option value="Paid in Cash">Paid in Cash</option>
            <option value="Installment">Installment</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Part Cost (₱)</label>
          <input type="number" step="0.01" id="part_cost" name="part_cost" class="form-control" oninput="calculateTotal()" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Repair Cost (₱)</label>
          <input type="number" step="0.01" id="repair_cost" name="repair_cost" class="form-control" oninput="calculateTotal()" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Total Cost (₱)</label>
        <input type="number" step="0.01" id="total_cost" name="total_cost" class="form-control bg-light" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">Before Image</label>
        <input type="file" name="before_image" class="form-control" accept="image/*">
      </div>

      <button type="submit" class="btn btn-primary w-100 mt-2">
        <i class="bi bi-save"></i> Submit Order & Generate Receipt
      </button>
    </form>
  </div>

  <div class="sidebar-summary">
    <h5 class="fw-bold text-primary"><i class="bi bi-card-checklist"></i> Quick Notes</h5>
    <p class="text-muted small mb-2">
      Ensure all client details and repair types are accurate before submitting.
    </p>
    <hr>
    <p class="small text-secondary mb-0">
      Uploaded images will be stored securely in the system. A receipt will automatically generate after successful order creation.
    </p>
  </div>
</main>

<?php include('../includes/footer.php'); ?>
