<?php
include('../db_connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: orders.php");
  exit();
}

$order_id = intval($_GET['id']);

// ✅ Fetch order + client + details
$query = "
  SELECT 
    o.order_id,
    o.client_id,
    c.fullname,
    c.contact_number,
    c.email,
    c.address,
    o.issue,
    o.total_amount,
    o.status,
    o.payment_status,
    o.warranty_end,
    o.warranty_start,
    o.before_image,
    o.after_image,
    o.date_created,
    d.part_cost,
    d.repair_cost
  FROM orders o
  LEFT JOIN clients c ON o.client_id = c.client_id
  LEFT JOIN order_details d ON o.order_id = d.order_id
  WHERE o.order_id = ?
  LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
  echo "<script>alert('Order not found!'); window.location='orders.php';</script>";
  exit();
}

// ✅ Insert order into archives
$insert = $conn->prepare("
  INSERT INTO archives 
    (order_id, client_id, fullname, contact_number, email, address, issue, 
     total_amount, payment_status, status, warranty_end, before_image, after_image, 
     part_cost, repair_cost, date_created, date_archived)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$insert->bind_param(
  "iissssssssssddds",
  $order['order_id'],
  $order['client_id'],
  $order['fullname'],
  $order['contact_number'],
  $order['email'],
  $order['address'],
  $order['issue'],
  $order['total_amount'],
  $order['payment_status'],
  $order['status'],
  $order['warranty_end'],
  $order['before_image'],
  $order['after_image'],
  $order['part_cost'],
  $order['repair_cost'],
  $order['date_created']
);

if ($insert->execute()) {
  // ✅ Optional: mark order as archived
  mysqli_query($conn, "UPDATE orders SET is_archived = 1 WHERE order_id = $order_id");

  echo "<script>alert('Order successfully archived!'); window.location='archived_orders.php';</script>";
} else {
  echo "<script>alert('Error archiving order: " . addslashes($insert->error) . "'); window.location='orders.php';</script>";
}

$insert->close();
$conn->close();
?>
