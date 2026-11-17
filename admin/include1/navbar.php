<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark ps-0">
    
    <div class="d-flex align-items-center justify-content-between pe-3" style="width: 225px; background-color: #1e1e1e; height: 100%;">
        <a class="navbar-brand fw-bold ps-4" href="admin-dashboard.php" style="color: #ffffff; font-family: 'Playfair Display', serif; letter-spacing: 1px; font-size: 1.5rem;">
            AJ&A
        </a>
        <button class="btn btn-link btn-sm p-0" id="sidebarToggle" href="#!" style="color: rgba(255,255,255,0.8);">
            <i class="fas fa-bars fa-lg"></i>
        </button>
    </div>
    
    <div class="d-none d-md-inline-block ms-auto me-0 me-md-3 my-2 my-md-0"></div>
    
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4 align-items-center">
        
        <li class="nav-item dropdown me-3">
            <a class="nav-link position-relative" id="notificationsDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
                <i class="fas fa-bell fa-lg"></i>
                <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger border border-light d-none" style="padding: 0.35em 0.5em; font-size: 0.65rem;">
                    0
                </span>
            </a>
            
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-0" aria-labelledby="notificationsDropdown" style="width: 350px; max-height: 500px; overflow-y: auto; overflow-x: hidden;">
                
                <li class="dropdown-header bg-white fw-bold text-uppercase py-3 border-bottom sticky-top" style="color: #b2935b; font-size: 0.8rem; letter-spacing: 1px; z-index: 100;">
                    Recent Alerts
                </li>

                <ul id="notificationList" class="list-unstyled m-0 p-0">
                    <li class="text-center text-muted py-5 small">Loading...</li>
                </ul>

                <li class="border-top sticky-bottom bg-white">
                    <a class="dropdown-item text-center small py-2 fw-semibold text-primary" href="notifications.php">View All History</a>
                </li>
            </ul>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff;">
                <div class="bg-transparent border border-white rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 35px; height: 35px; font-size: 0.9rem;">
                    <?= strtoupper(substr($_SESSION['loggedUser']['name'] ?? 'A', 0, 1)); ?>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3" aria-labelledby="navbarDropdown">
                <li class="px-3 py-2">
                    <div class="fw-bold text-dark"><?= $_SESSION['loggedUser']['name'] ?? 'Admin'; ?></div>
                    <div class="small text-muted"><?= $_SESSION['loggedUser']['position'] ?? 'Manager'; ?></div>
                </li>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item text-danger fw-semibold" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const badge = document.getElementById("notificationBadge");
    const list = document.getElementById("notificationList");
    const dropdownLink = document.getElementById("notificationsDropdown");

    function loadNotifications() {
        fetch("fetch_notifications.php")
            .then(res => res.json())
            .then(data => {
                // 1. Update Badge (Uses Count Only)
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.classList.remove("d-none");
                } else {
                    badge.classList.add("d-none");
                }

                // 2. Populate List (Uses Orders Array)
                let htmlContent = "";

                // A. Notifications List
                if (data.orders && data.orders.length > 0) {
                    data.orders.forEach(notif => {
                        let icon = 'fa-bell';
                        let colorClass = 'text-primary';
                        let bgClass = 'bg-primary';
                        
                        if(notif.type == 'order') { 
                            icon = 'fa-shopping-bag'; colorClass = 'text-gold'; bgClass = 'bg-gold'; 
                        } else if(notif.type == 'stock') { 
                            icon = 'fa-exclamation-triangle'; colorClass = 'text-danger'; bgClass = 'bg-danger'; 
                        } else if(notif.type == 'payment_verify') {
                            icon = 'fa-receipt'; colorClass = 'text-info'; bgClass = 'bg-info';
                        }

                        // Determine background if unread (optional visual cue)
                        let itemBg = notif.is_read == 0 ? 'bg-light' : '';

                        htmlContent += `
                            <li>
                                <a class="dropdown-item py-3 px-3 d-flex align-items-start gap-3 border-bottom ${itemBg}" href="${notif.link}" style="white-space: normal;">
                                    <div class="${bgClass} bg-opacity-10 ${colorClass} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 35px; height: 35px;">
                                        <i class="fas ${icon}"></i>
                                    </div>
                                    <div style="line-height: 1.4;">
                                        <span class="d-block text-dark fw-semibold" style="font-size: 0.85rem;">${notif.message}</span>
                                        <small class="text-muted" style="font-size: 0.75rem;">${notif.formatted_date}</small>
                                    </div>
                                </a>
                            </li>`;
                    });
                }

                // B. Low Stock List
                if (data.lowStock && data.lowStock.length > 0) {
                    htmlContent += `<li class="px-3 py-2 bg-light text-muted fw-bold border-bottom" style="font-size: 0.7rem;">LOW STOCK ALERT</li>`;
                    
                    data.lowStock.forEach(prod => {
                        const color = prod.quantity == 0 ? "text-danger" : "text-warning";
                        const status = prod.quantity == 0 ? "Out of stock" : `Only ${prod.quantity} left`;
                        
                        htmlContent += `
                            <li>
                                <a class="dropdown-item py-3 px-3 d-flex align-items-start gap-3 border-bottom" href="products-edit.php?id=${prod.id}" style="white-space: normal;">
                                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 35px; height: 35px;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div style="line-height: 1.4;">
                                        <strong class="d-block text-dark" style="font-size: 0.85rem;">${prod.name}</strong>
                                        <small class="${color}" style="font-size: 0.75rem; font-weight: 600;">${status}</small>
                                    </div>
                                </a>
                            </li>`;
                    });
                }

                // C. Empty State
                if (htmlContent === "") {
                    htmlContent = `
                        <li class="text-center py-5">
                            <div class="text-muted mb-2"><i class="fas fa-bell-slash fa-2x opacity-25"></i></div>
                            <span class="text-muted small">No recent notifications.</span>
                        </li>`;
                }

                list.innerHTML = htmlContent;
            })
            .catch(err => console.error(err));
    }

    // Load immediately
    loadNotifications();
    
    // Refresh every 5 seconds
    setInterval(loadNotifications, 5000);

    // Mark as read on click (Badge disappears immediately for UX)
    dropdownLink.addEventListener("click", () => {
        if (!badge.classList.contains('d-none')) {
            // Optimistic update: Hide badge immediately
            badge.classList.add("d-none");
            badge.textContent = "0";
            
            // Send request to backend
            fetch("mark_notifications_read.php");
        }
    });
});
</script>

<style>
    .text-gold { color: #b2935b !important; }
    .bg-gold { background-color: #b2935b !important; }
    
    /* Scrollbar Styling */
    .dropdown-menu::-webkit-scrollbar { width: 6px; }
    .dropdown-menu::-webkit-scrollbar-track { background: #f1f1f1; }
    .dropdown-menu::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .dropdown-menu::-webkit-scrollbar-thumb:hover { background: #b2935b; }

    /* Links */
    .dropdown-item:active { background-color: #e9ecef; color: #000; }
</style>