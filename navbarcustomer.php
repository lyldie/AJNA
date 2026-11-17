
<?php
require   'config/function.php';
?>
<nav class="navbar navba
r-expand-lg navbar-dark" style="background-color: #b2935b;">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">AJ&A Divi-Things</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
      data-bs-target="#navbarNav" aria-controls="navbarNav" 
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
     <ul class="navbar-nav ms-auto">

  <!-- Search box inside navbar -->
  <li class="nav-item">
    <form class="d-flex" action="index.php" method="get">
      <input class="form-control me-1" type="search" placeholder="Search products..." name="search" aria-label="Search">
      <button class="btn btn-outline-light" type="submit">Search</button>
    </form>
  </li>

  <!-- Filter dropdown -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="filterDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      Filter
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
      <li><a class="dropdown-item" href="?filter=jewelry">Jewelry</a></li>
      <li><a class="dropdown-item" href="?filter=perfumes">Perfumes</a></li>
      <li><a class="dropdown-item" href="?filter=bags">Bags</a></li>
      <li><a class="dropdown-item" href="?filter=slides">Slides</a></li>
    </ul>
  </li>

</ul>

    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</nav>
