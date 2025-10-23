<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$basePath = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Monitoring System</title>

  <link href="<?php echo $basePath; ?>assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo $basePath; ?>assets/css/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
    }

    /* ===== Top Navigation Bar ===== */
    .topnav {
      background-color: rgba(17, 22, 26, 0.95);
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.7rem 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.4);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 999;
      backdrop-filter: blur(10px);
    }

    .topnav .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1.25rem;
      font-weight: 600;
      color: white;
      text-decoration: none;
    }

    .topnav .brand img {
      width: 42px;
      height: 42px;
      border-radius: 8px;
      object-fit: cover;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .nav-links a {
      color: #eaeaea;
      font-weight: 500;
      text-decoration: none;
      transition: color 0.2s ease;
    }

    .nav-links a:hover,
    .nav-links a.active {
      color: #0d6efd;
    }

    .nav-links a.text-danger {
      color: #ff5c5c !important;
    }

    .menu-toggle {
      display: none;
      font-size: 1.8rem;
      background: none;
      border: none;
      color: white;
      cursor: pointer;
    }

    @media (max-width: 992px) {
      .nav-links {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 65px;
        left: 0;
        width: 100%;
        background-color: rgba(17, 22, 26, 0.97);
        padding: 1rem 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.4);
      }

      .nav-links.show {
        display: flex;
      }

      .menu-toggle {
        display: block;
      }
    }

    /* Padding fix for fixed header */
    main, .dashboard-container {
      padding-top: 85px !important;
    }
  </style>
</head>
<body>

<!-- ✅ Top Navigation -->
<nav class="topnav">
  <a href="<?php echo $basePath; ?>dashboard.php" class="brand">
    <!-- ✅ Updated logo source -->
    <img src="<?php echo $basePath; ?>assets/bg/logo.jpg" alt="Logo">
    <span>Sales Monitoring</span>
  </a>

  <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i></button>

  <div class="nav-links" id="navLinks">
    <a href="<?php echo $basePath; ?>dashboard.php" class="<?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
    <a href="<?php echo $basePath; ?>pages/orders.php" class="<?php echo $currentPage == 'orders.php' ? 'active' : ''; ?>">Orders</a>
    <a href="<?php echo $basePath; ?>pages/clients.php" class="<?php echo $currentPage == 'clients.php' ? 'active' : ''; ?>">Clients</a>
    <a href="<?php echo $basePath; ?>pages/suppliers.php" class="<?php echo $currentPage == 'suppliers.php' ? 'active' : ''; ?>">Suppliers</a>
    <a href="<?php echo $basePath; ?>pages/archived_orders.php" class="<?php echo $currentPage == 'archived_orders.php' ? 'active' : ''; ?>">Archives</a>
    <a href="<?php echo $basePath; ?>pages/sales_report.php" class="<?php echo $currentPage == 'sales_report.php' ? 'active' : ''; ?>">Sales Report</a>
    <a href="<?php echo $basePath; ?>logout.php" class="text-danger fw-bold">Logout</a>
  </div>
</nav>

<script>
  document.getElementById('menuToggle').addEventListener('click', function() {
    document.getElementById('navLinks').classList.toggle('show');
  });
</script>
