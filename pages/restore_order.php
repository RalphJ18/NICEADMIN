<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Get archive_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_id'])) {
  $archive_id = intval($_POST['archive_id']);
} elseif (isset($_GET['id'])) {
  $archive_id = intval($_GET['id']);
} else {
  header("Location: archived_orders.php");
  exit();
}

// Fetch archive data
$stmt = $conn->prepare("SELECT * FROM archives WHERE archive_id = ? LIMIT 1");
$stmt->bind_param("i", $archive_id);
$stmt->execute();
$archive = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$archive) {
  echo "<script>alert('Archived order not found.'); window.location='archived_orders.php';</script>";
  exit();
}

// Fallbacks
$part_cost    = floatval($archive['part_cost'] ?? 0);
$repair_cost  = floatval($archive['repair_cost'] ?? 0);
$total_amount = floatval($archive['total_amount'] ?? 0);

if ($total_amount <= 0) {
  $total_amount = $part_cost + $repair_cost;
}

$client_id      = intval($archive['client_id'] ?? 0);
$issue          = $archive['issue'] ?? '';
$status         = $archive['status'] ?? 'Pending';
$payment_status = $archive['payment_status'] ?? 'Not Yet Paid';
$warranty_end   = $archive['warranty_end'] ?? null;
$before_image   = $archive['before_image'] ?? null;
$after_image    = $archive['after_image'] ?? null;
$date_created   = $archive['date_created'] ?? date('Y-m-d H:i:s');

// ✅ Insert back to orders
$insertOrder = $conn->prepare("
  INSERT INTO orders 
    (client_id, issue, total_amount, status, payment_status, warranty_end, before_image, after_image, date_created, created_at, is_archived)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)
");

if (!$insertOrder) {
  echo "<script>alert('DB error preparing order insert: " . addslashes($conn->error) . "'); window.location='archived_orders.php';</script>";
  exit();
}

// 10 placeholders → 10 types ('i' for int, 's' for string, 'd' for double)
$insertOrder->bind_param(
  "isdssssss",
  $client_id,
  $issue,
  $total_amount,
  $status,
  $payment_status,
  $warranty_end,
  $before_image,
  $after_image,
  $date_created
);

if (!$insertOrder->execute()) {
  echo "<script>alert('Error restoring order: " . addslashes($insertOrder->error) . "'); window.location='archived_orders.php';</script>";
  exit();
}

$new_order_id = $insertOrder->insert_id;
$insertOrder->close();

// ✅ Insert order details
$insertDetails = $conn->prepare("
  INSERT INTO order_details (order_id, part_cost, repair_cost, total_cost, date_added)
  VALUES (?, ?, ?, ?, NOW())
");
$insertDetails->bind_param("iddd", $new_order_id, $part_cost, $repair_cost, $total_amount);
$insertDetails->execute();
$insertDetails->close();

// ✅ Delete from archives
$delete = $conn->prepare("DELETE FROM archives WHERE archive_id = ?");
$delete->bind_param("i", $archive_id);
$delete->execute();
$delete->close();

echo "<script>alert('Order successfully restored and moved back to active orders!'); window.location='orders.php';</script>";
$conn->close();
?>
