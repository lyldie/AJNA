<?php
require   'config/function.php';
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
?>

<!-- ✅ NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background-color: #b2935b; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
  <div class="container d-flex align-items-center gap-3" style="font-family: 'Playfair Display', serif; font-weight: bold; font-size: 20px;">
    <a class="navbar-brand text-white" href="index.php">
      <img src="logo.jpeg" alt="AJ&A Logo" style="height: 35px;">
      AJ&A DIVI-things
    </a>

    <div class="d-flex align-items-center gap-3 ms-auto">
     

      <div class="position-relative">
        <a href="cart.php" class="btn btn-outline-light position-relative">
          🛒 Cart
          <?php if ($cart_count > 0): ?>
            <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $cart_count; ?></span>
          <?php endif; ?>
        </a>
      </div>

      <a href="loginform.php" class="btn btn-light">Login</a>
    </div>
  </div>
</nav>

<!-- ✅ Extra navbar styling -->
<style>
  .cart-count {
    font-size: 12px;
    padding: 2px 6px;
  }
  .navbar .btn, .navbar .form-control {
    font-family: 'Open Sans', sans-serif;
  }
  .navbar-dark .form-control::placeholder {
    color: #dee2e6;
    opacity: 1;
  }
  .navbar-dark .form-control {
    color: white;
    background-color: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
  }
</style>
