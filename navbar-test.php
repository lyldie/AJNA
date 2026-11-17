<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

  <!-- Sidebar Toggle (Topbar) -->
  <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
    <i class="fa fa-bars"></i>
  </button>

  <!-- Search Form -->
  <form class="form-inline mr-auto ml-md-3 my-2 my-md-0 navbar-search">
    <div class="input-group">
      <input type="text" class="form-control bg-light border-0 small" placeholder="Search for products..."
        aria-label="Search" aria-describedby="basic-addon2" name="search">
      <div class="input-group-append">
        <button class="btn btn-primary" type="submit">
          <i class="fas fa-search fa-sm"></i>
        </button>
      </div>
    </div>
  </form>

  <!-- Filter Dropdown -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown no-arrow mx-1">
      <a class="nav-link dropdown-toggle" href="#" id="filterDropdown" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-filter fa-fw"></i> Filter
      </a>
      <!-- Dropdown - Filter Options -->
      <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
        aria-labelledby="filterDropdown">
        <a class="dropdown-item" href="?filter=jewelry">Jewelry</a>
        <a class="dropdown-item" href="?filter=perfumes">Perfumes</a>
        <a class="dropdown-item" href="?filter=bags">Bags</a>
        <a class="dropdown-item" href="?filter=slides">Slides</a>
      </div>
    </li>
  </ul>
</nav>
<!-- End of Topbar -->

</body>
</html>