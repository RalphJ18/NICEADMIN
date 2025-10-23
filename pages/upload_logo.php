<?php
include '../db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supplier_id'])) {
    $supplier_id = intval($_POST['supplier_id']);

    // Upload directory
    $uploadDir = '../assets/uploads/suppliers/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $newLogo = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['logo']['tmp_name'];
        $name = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['logo']['name']));
        $dest = $uploadDir . $name;
        if (move_uploaded_file($tmp, $dest)) {
            $newLogo = $name;
        }
    }

    if ($newLogo) {
        $stmt = $conn->prepare("UPDATE suppliers SET logo = ? WHERE supplier_id = ?");
        $stmt->bind_param("si", $newLogo, $supplier_id);

        if ($stmt->execute()) {
            header("Location: suppliers.php?logo_updated=1");
            exit();
        } else {
            echo "<div class='alert alert-danger text-center mt-4'>Error updating logo: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='alert alert-warning text-center mt-4'>No image selected or upload failed.</div>";
    }
}
?>
