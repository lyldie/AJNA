<?php
include('include1/header.php');
// include('../config/dbcon.php'); // ❌ INALIS KO NA 'TO para iwas error. Nasa header.php na siya.

// ✅ LOGIC: Clear All Button
if (isset($_POST['clear_all'])) {
    mysqli_query($con, "TRUNCATE TABLE notifications"); // Burahin lahat ng notif
    echo "<script>window.location.href='notifications.php';</script>";
}

// ✅ Auto-mark as read pag binuksan ang page na 'to
mysqli_query($con, "UPDATE notifications SET is_read = 1 WHERE is_read = 0");

// Fetch all notifications
$notifications = mysqli_query($con, "SELECT * FROM notifications ORDER BY created_at DESC");
?>

<div class="container-fluid px-4">
    
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="fw-bold text-dark display-6">Notifications</h1>
            <p class="text-muted mb-0">Recent system updates and alerts.</p>
        </div>
        
        <?php if (mysqli_num_rows($notifications) > 0): ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete ALL notifications?');">
                <button type="submit" name="clear_all" class="btn btn-outline-danger rounded-pill px-4">
                    <i class="fas fa-trash-alt me-2"></i>Clear All
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    
                    <?php if (mysqli_num_rows($notifications) > 0): ?>
                        <div class="list-group list-group-flush rounded-4">
                            <?php while($n = mysqli_fetch_assoc($notifications)): ?>
                                <?php
                                    // Icon Logic
                                    $type = $n['type'] ?? 'default';
                                    $icon = 'fa-bell';
                                    $bgClass = 'bg-secondary';
                                    $textClass = 'text-secondary';

                                    if ($type == 'order') {
                                        $icon = 'fa-shopping-bag';
                                        $bgClass = 'bg-gold'; // Using your custom CSS class
                                        $textClass = 'text-gold';
                                    } elseif ($type == 'payment_verify') {
                                        $icon = 'fa-receipt';
                                        $bgClass = 'bg-info';
                                        $textClass = 'text-info';
                                    } elseif ($type == 'stock') {
                                        $icon = 'fa-exclamation-triangle';
                                        $bgClass = 'bg-danger';
                                        $textClass = 'text-danger';
                                    }
                                    
                                    $link = !empty($n['link']) ? $n['link'] : '#';
                                ?>
                                
                                <a href="<?= $link; ?>" class="list-group-item list-group-item-action p-4 border-bottom notification-item">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box-lg rounded-circle <?= $bgClass; ?> bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                            <i class="fas <?= $icon; ?> <?= $textClass; ?> fa-lg"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-bold text-dark">
                                                    <?php 
                                                        if($type == 'stock') echo "Stock Alert";
                                                        elseif($type == 'payment_verify') echo "Payment Verification";
                                                        elseif($type == 'order') echo "New Order";
                                                        else echo "Notification";
                                                    ?>
                                                </h6>
                                                <small class="text-muted"><?= date('M d, h:i A', strtotime($n['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-0 text-secondary"><?= htmlspecialchars($n['message']); ?></p>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <h5 class="text-muted">No notifications yet.</h5>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-gold { color: #b2935b !important; }
    .bg-gold { background-color: #b2935b !important; }
    .notification-item:hover { background-color: #f9f9f9; border-left: 4px solid #b2935b; }
    .notification-item { border-left: 4px solid transparent; transition: all 0.2s; }
</style>

<?php include('include1/footer.php'); ?>