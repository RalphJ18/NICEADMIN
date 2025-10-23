<?php
// pages/delete_archive.php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: archived_orders.php");
  exit();
}

$archive_id = intval($_GET['id']);

// fetch archive row
$stmt = $conn->prepare("SELECT * FROM archives WHERE archive_id = ? LIMIT 1");
$stmt->bind_param('i', $archive_id);
$stmt->execute();
$archive = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$archive) {
  header("Location: archived_orders.php");
  exit();
}

// try to unlink images if present
if (!empty($archive['before_image'])) {
  $path = "../assets/uploads/" . $archive['before_image'];
  if (file_exists($path)) @unlink($path);
}
if (!empty($archive['after_image'])) {
  $path = "../assets/uploads/" . $archive['after_image'];
  if (file_exists($path)) @unlink($path);
}

// delete archive row
$del = $conn->prepare("DELETE FROM archives WHERE archive_id = ?");
$del->bind_param('i', $archive_id);
$del->execute();
$del->close();

header("Location: archived_orders.php?deleted=1");
exit();
