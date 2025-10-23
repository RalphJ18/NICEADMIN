<?php
// Detect base path automatically for root or /pages/
$basePath = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : '';
?>

<!-- ✅ Bootstrap Bundle -->
<script src="<?php echo $basePath; ?>assets/js/bootstrap.bundle.min.js"></script>

<!-- ✅ Top Navbar Mobile Menu Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');

  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', function () {
      navLinks.classList.toggle('show');
    });

    // Close the mobile menu when a link is clicked (for better UX)
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 992) {
          navLinks.classList.remove('show');
        }
      });
    });
  }

  // Optional: Close the menu if you resize to desktop view
  window.addEventListener('resize', function () {
    if (window.innerWidth > 992) {
      navLinks.classList.remove('show');
    }
  });
});
</script>

<!-- ✅ Optional Styling for Smooth Mobile Dropdown -->
<style>
  .nav-links {
    transition: all 0.3s ease-in-out;
  }
  .nav-links.show {
    display: flex !important;
    opacity: 1;
  }
</style>

</body>
</html>
