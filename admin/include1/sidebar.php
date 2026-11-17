<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion"
         style="background: #1f1f1f; color: #ddd; box-shadow: inset -2px 0 5px rgba(0,0,0,0.3);">
        <div class="sb-sidenav-menu">
            <div class="nav">

                <div class="sb-sidenav-menu-heading text-uppercase text-muted small">Core</div>
                
                <a class="nav-link sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : ''; ?>" href="admin-dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                
                <a class="nav-link sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                    Orders
                </a>

                <a class="nav-link sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    Reports
                </a>

                <div class="sb-sidenav-menu-heading text-uppercase text-muted small">CATEGORIES/PRODUCTS</div>
                
                <a class="nav-link collapsed sidebar-link <?= in_array(basename($_SERVER['PHP_SELF']), ['categories.php', 'categories-create.php', 'categories-archived.php']) ? 'active' : ''; ?>" 
                   href="#" data-bs-toggle="collapse" data-bs-target="#collapseCategory">
                    <div class="sb-nav-link-icon"><i class="fas fa-layer-group"></i></div>
                    Categories
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['categories.php', 'categories-create.php', 'categories-edit.php', 'categories-archived.php']) ? 'show' : ''; ?>" 
                     id="collapseCategory" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link sidebar-sub <?= basename($_SERVER['PHP_SELF']) == 'categories-create.php' ? 'active-sub' : ''; ?>" href="categories-create.php">Create Category</a>
                        <a class="nav-link sidebar-sub <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active-sub' : ''; ?>" href="categories.php">View Categories</a>
                    </nav>
                </div>

                <a class="nav-link collapsed sidebar-link <?= in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'products-create.php', 'products-archived.php']) ? 'active' : ''; ?>" 
                   href="#" data-bs-toggle="collapse" data-bs-target="#collapseProduct">
                    <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div>
                    Products
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'products-create.php', 'products-edit.php', 'products-archived.php']) ? 'show' : ''; ?>" 
                     id="collapseProduct" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link sidebar-sub <?= basename($_SERVER['PHP_SELF']) == 'products-create.php' ? 'active-sub' : ''; ?>" href="products-create.php">Create Product</a>
                        <a class="nav-link sidebar-sub <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active-sub' : ''; ?>" href="products.php">View Products</a>
                    </nav>
                </div>

                <div class="sb-sidenav-menu-heading text-uppercase text-muted small">ADMINS</div>
                
                <a class="nav-link collapsed sidebar-link <?= in_array(basename($_SERVER['PHP_SELF']), ['admins.php', 'admins-create.php', 'admins-archived.php']) ? 'active' : ''; ?>" 
                   href="#" data-bs-toggle="collapse" data-bs-target="#collapseAdmins">
                    <div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div>
                    Admins/Staff
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?= in_array(basename($_SERVER['PHP_SELF']), ['admins.php', 'admins-create.php', 'admins-edit.php', 'admins-archived.php']) ? 'show' : ''; ?>" 
                     id="collapseAdmins" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link sidebar-sub <?= basename($_SERVER['PHP_SELF']) == 'admins-create.php' ? 'active-sub' : ''; ?>" href="admins-create.php">Add Admin</a>
                        <a class="nav-link sidebar-sub <?= basename($_SERVER['PHP_SELF']) == 'admins.php' ? 'active-sub' : ''; ?>" href="admins.php">View Admins</a>
                        <a class="nav-link" href="admin-logs.php">
    <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
    Inventory Logs
</a>
<a class="nav-link" href="login-history.php">
    <div class="sb-nav-link-icon"><i class="fas fa-user-clock"></i></div>
    Login History
</a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="sb-sidenav-footer text-center p-2" style="background: #2b2b2b; border-top: 1px solid #3a3a3a;">
            <div class="small text-muted mb-1" style="font-size: 0.75rem;">Logged in as:</div>
            <div class="fw-semibold text-light" style="font-size: 0.9rem;">
                <?= $_SESSION['loggedUser']['name'] ?? 'Guest'; ?>
            </div>
            <div class="text-warning small" style="font-size: 0.8rem;">
                <?= $_SESSION['loggedUser']['position'] ?? ''; ?>
            </div>
        </div>
    </nav>
</div>

<style>
/* Base link styling */
.sidebar-link {
    color: #ddd;
    font-weight: 500;
    transition: all 0.25s ease;
    border-radius: 8px;
    margin: 2px 8px;
    padding: 10px 15px !important;
    position: relative;
}
.sidebar-link::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(178,147,91,0.15), rgba(215,185,142,0.25));
    border-radius: 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.sidebar-link:hover::before {
    opacity: 1;
}
.sidebar-link:hover,
.sidebar-link.active {
    color: #fff !important;
    background: linear-gradient(90deg, #b2935b, #d7b98e);
    box-shadow: 0 2px 8px rgba(178,147,91,0.4);
}
.sidebar-sub {
    font-size: 0.9rem;
    color: #aaa;
    padding-left: 35px !important;
    margin: 2px 0;
    transition: all 0.25s ease;
    border-left: 3px solid transparent;
}
.sidebar-sub:hover {
    color: #d7b98e;
    border-left: 3px solid #b2935b;
    background-color: rgba(255, 255, 255, 0.05);
}
.active-sub,
<?= basename($_SERVER['PHP_SELF']) == 'categories-edit.php' ? 'a[href="categories.php"],' : ''; ?>
<?= basename($_SERVER['PHP_SELF']) == 'products-edit.php' ? 'a[href="products.php"],' : ''; ?>
<?= basename($_SERVER['PHP_SELF']) == 'admins-edit.php' ? 'a[href="admins.php"],' : ''; ?>
.active-sub-placeholder
{
    color: #d7b98e !important;
    border-left: 3px solid #b2935b;
    background-color: rgba(255, 255, 255, 0.05);
}
.sb-sidenav-menu-heading {
    margin-top: 12px;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}
.sb-sidenav-collapse-arrow i {
    transition: transform 0.3s ease, color 0.3s ease;
}
.sidebar-link:hover .sb-sidenav-collapse-arrow i {
    color: #d7b98e;
    transform: rotate(90deg);
}
</style>