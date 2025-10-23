<?php
// pages/delete_supplier.php
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

// Get supplier name & logo
$stmt = $conn->prepare("SELECT supplier_name, logo FROM suppliers WHERE supplier_id = ?");
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$supplier = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$supplier) {
    header("Location: suppliers.php");
    exit();
}

$folder_safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $supplier['supplier_name']);
$folderPath = "../assets/uploads/suppliers/{$folder_safe}/";

// 1) delete supply image files and rows
$supplies_stmt = $conn->prepare("SELECT item_image FROM supplier_supplies WHERE supplier_id = ?");
$supplies_stmt->bind_param("i", $supplier_id);
$supplies_stmt->execute();
$res = $supplies_stmt->get_result();
while ($r = $res->fetch_assoc()) {
    if (!empty($r['item_image']) && file_exists($folderPath . $r['item_image'])) {
        @unlink($folderPath . $r['item_image']);
    }
}
$supplies_stmt->close();

// delete supplier supplies rows
$del_supplies = $conn->prepare("DELETE FROM supplier_supplies WHERE supplier_id = ?");
$del_supplies->bind_param("i", $supplier_id);
$del_supplies->execute();
$del_supplies->close();

// delete supplier logo file
if (!empty($supplier['logo'])) {
    $logoPath = "../assets/uploads/suppliers/" . $supplier['logo'];
    if (file_exists($logoPath)) @unlink($logoPath);
}

// remove supplier folder (if empty)
if (is_dir($folderPath)) {
    @rmdir($folderPath); // will only remove if empty
}

// delete supplier row
$del_supplier = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
$del_supplier->bind_param("i", $supplier_id);
$del_supplier->execute();
$del_supplier->close();

header("Location: suppliers.php?deleted=1");
exit();
