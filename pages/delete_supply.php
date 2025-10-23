<?php
// pages/delete_supply.php
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

// get supply info (image + supplier name)
$stmt = $conn->prepare("SELECT s.item_image, sp.supplier_name
                        FROM supplier_supplies s
                        LEFT JOIN suppliers sp ON s.supplier_id = sp.supplier_id
                        WHERE s.supply_id = ?");
$stmt->bind_param("i", $supply_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    // delete image file if present
    $folder_safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $row['supplier_name']);
    $imagePath = "../assets/uploads/suppliers/{$folder_safe}/" . $row['item_image'];
    if (!empty($row['item_image']) && file_exists($imagePath)) {
        @unlink($imagePath);
    }

    // delete db row
    $del = $conn->prepare("DELETE FROM supplier_supplies WHERE supply_id = ?");
    $del->bind_param("i", $supply_id);
    $del->execute();
    $del->close();
}

// redirect back to supplier page
if ($supplier_id) {
    header("Location: supplier_supplies.php?supplier_id={$supplier_id}&deleted=1");
} else {
    header("Location: suppliers.php?deleted=1");
}
exit();
