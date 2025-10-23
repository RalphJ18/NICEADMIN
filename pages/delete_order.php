<?php
session_start();
include('../db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Check if ID is passed
if (isset($_GET['id'])) {
  $order_id = intval($_GET['id']);

  // Prepare and execute delete query
  $query = "DELETE FROM orders WHERE order_id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $order_id);

  if ($stmt->execute()) {
    // Redirect back to orders page with success message
    $_SESSION['success_message'] = "Order deleted successfully!";
    header("Location: orders.php");
    exit();
  } else {
    // Redirect back with error message
    $_SESSION['error_message'] = "Failed to delete order. Please try again.";
    header("Location: orders.php");
    exit();
  }

  $stmt->close();
} else {
  // No ID passed, redirect back safely
  header("Location: orders.php");
  exit();
}

$conn->close();
?>
