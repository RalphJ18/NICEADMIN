<?php
// Detect base path (handles both root and /pages/ directories)
$basePath = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : '';
?>

<!-- Sidebar Toggle Button -->
<button id="sidebarToggle"><i class="bi bi-list"></i></button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Sales Monitor</h4>
    <i id="closeSidebar" class="bi bi-x-lg"></i>
  </div>

  <a href="<?php echo $basePath; ?>dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
    <i class="bi bi-house"></i> Dashboard
  </a>

  <a href="<?php echo $basePath; ?>pages/orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
    <i class="bi bi-bag"></i> Orders
  </a>

  <a href="<?php echo $basePath; ?>pages/clients.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>">
    <i class="bi bi-people"></i> Clients
  </a>

  <a href="<?php echo $basePath; ?>pages/suppliers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'suppliers.php' ? 'active' : ''; ?>">
    <i class="bi bi-truck"></i> Suppliers
  </a>

  <a href="<?php echo $basePath; ?>pages/archived_orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'archived_orders.php' ? 'active' : ''; ?>">
    <i class="bi bi-archive"></i> Archives
  </a>

  <a href="<?php echo $basePath; ?>logout.php" class="mt-5 text-danger fw-bold">
    <i class="bi bi-box-arrow-right"></i> Logout
  </a>
</div>

<!-- Sidebar JS -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const sidebarToggle = document.getElementById("sidebarToggle");
  const closeSidebar = document.getElementById("closeSidebar");

  sidebarToggle.addEventListener("click", () => {
    sidebar.classList.toggle("show");
  });

  closeSidebar.addEventListener("click", () => {
    sidebar.classList.remove("show");
  });

  // Auto-hide sidebar on mobile after link click
  document.querySelectorAll(".sidebar a").forEach(link => {
    link.addEventListener("click", () => {
      if (window.innerWidth <= 992) {
        sidebar.classList.remove("show");
      }
    });
  });
});
</script>

<!-- Sidebar Style -->
<style>
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 250px;
  height: 100vh;
  background: linear-gradient(180deg, #0d6efd, #0044cc);
  color: white;
  padding: 20px;
  box-shadow: 3px 0 15px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s ease;
  z-index: 1040;
  transform: translateX(0);
}
.sidebar.hide {
  transform: translateX(-260px);
}
.sidebar a {
  display: block;
  color: #ffffffcc;
  text-decoration: none;
  padding: 12px 15px;
  margin: 5px 10px;
  border-radius: 8px;
  transition: all 0.2s ease;
}
.sidebar a:hover,
.sidebar a.active {
  background-color: #ffffff33;
  color: #fff;
}
#sidebarToggle {
  position: fixed;
  top: 15px;
  left: 15px;
  background: white;
  border: none;
  border-radius: 8px;
  padding: 8px 10px;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
  z-index: 1051;
}
#sidebarToggle i {
  font-size: 1.5rem;
  color: #0d6efd;
}
#closeSidebar {
  cursor: pointer;
  font-size: 1.2rem;
  color: #fff;
  display: none;
}
@media (max-width: 992px) {
  .sidebar {
    transform: translateX(-260px);
  }
  .sidebar.show {
    transform: translateX(0);
  }
  #closeSidebar {
    display: inline;
  }
}
</style>
