<?php
session_start();
include_once('config/dbcon.php');
include_once('config/function.php');

$search_result = null;
$history_result = null;
$error_msg = "";
$success_msg = "";

// --- LOGIC 1: TRACK SPECIFIC ORDER (FIXED: Calculates Total) ---
if (isset($_POST['btn_track'])) {
    $order_id = mysqli_real_escape_string($con, $_POST['order_id']);
    $email    = mysqli_real_escape_string($con, $_POST['email']);

    // Gumamit tayo ng JOIN at SUM para makuha ang total kahit walang 'total_price' column
    $check_query = "
        SELECT o.*, SUM(oi.price * oi.quantity) as total_bill
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id='$order_id' AND o.customer_email='$email'
        GROUP BY o.id
        LIMIT 1
    ";
    $check_run = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_run) > 0) {
        $search_result = mysqli_fetch_assoc($check_run);
    } else {
        $error_msg = "No order found with that ID and Email combination.";
    }
}

// --- LOGIC 2: RECOVER ORDER IDs (FIXED: Calculates Total) ---
if (isset($_POST['btn_recover'])) {
    $recover_email = mysqli_real_escape_string($con, $_POST['recover_email']);

    // Updated Query: JOIN order_items to compute total price on the fly
    $history_query = "
        SELECT o.*, SUM(oi.price * oi.quantity) as total_bill 
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.customer_email='$recover_email'
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ";
    $history_run = mysqli_query($con, $history_query);

    if (mysqli_num_rows($history_run) > 0) {
        $history_result = $history_run; 
        $success_msg = "We found orders linked to your email.";
    } else {
        $error_msg = "No order history found for this email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Your Order | AJ&A</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --color-brand-gold: #b2935b;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        h1, h2, h3, h5 { font-family: 'Playfair Display', serif; }
        
        .navbar { background: var(--color-brand-gold) !important; padding: 15px 0; }
        .navbar-brand { font-family: 'Playfair Display', serif; font-weight: bold; font-size: 1.5rem; color: white !important; }

        .track-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            background: white;
        }
        .track-header {
            background-color: #222;
            color: var(--color-brand-gold);
            padding: 30px;
            text-align: center;
        }
        .btn-brand {
            background-color: var(--color-brand-gold);
            color: white;
            border-radius: 50px;
            padding: 10px 30px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }
        .btn-brand:hover { background-color: #8e7343; color: white; }

        /* STATUS STEPS CSS */
        .status-box {
            background: #fdfdfd;
            border: 1px solid #eee;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 5px 15px;
            border-radius: 50px;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-shipped { background: #cff4fc; color: #055160; } 

        /* RECOVER SECTION */
        .recover-section {
            border-top: 2px dashed #eee;
            margin-top: 30px;
            padding-top: 30px;
        }
        .history-item {
            border-left: 4px solid var(--color-brand-gold);
            background: #fffcf5;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 5px;
            transition: 0.2s;
        }
        .history-item:hover {
            background: #f0eadd;
            transform: translateX(5px);
        }
    </style>
</head>
<body>

<?php include('include2/navbarr.php'); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <div class="track-card mb-4">
                <div class="track-header">
                    <h2 class="mb-0 fw-bold"><i class="fas fa-shipping-fast me-2"></i>Track Order</h2>
                    <p class="small text-white-50 mb-0">Enter your details to check status</p>
                </div>
                <div class="card-body p-4">
                    
                    <?php if($error_msg): ?>
                        <div class="alert alert-danger text-center"><?= $error_msg; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Order ID</label>
                            <input type="number" name="order_id" class="form-control form-control-lg" placeholder="e.g. 104" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="customer@email.com" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="btn_track" class="btn btn-brand btn-lg">Check Status</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="#recoverCollapse" data-bs-toggle="collapse" class="text-muted small text-decoration-none">
                            Forgot your Order ID? <span style="color: var(--color-brand-gold); font-weight: bold;">Click here</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="collapse <?= isset($_POST['btn_recover']) ? 'show' : '' ?>" id="recoverCollapse">
                <div class="track-card">
                    <div class="card-body p-4 bg-light">
                        <h5 class="fw-bold mb-3"><i class="fas fa-search me-2"></i>Find My Orders</h5>
                        <p class="small text-muted">Enter your email address to see your recent order history.</p>
                        
                        <form action="" method="POST">
                            <div class="input-group mb-3">
                                <input type="email" name="recover_email" class="form-control" placeholder="Enter your email" required>
                                <button class="btn btn-dark" type="submit" name="btn_recover">Find</button>
                            </div>
                        </form>

                        <?php if($history_result): ?>
                            <div class="mt-3">
                                <h6 class="fw-bold text-muted text-uppercase small">Orders linked to this email:</h6>
                                <?php while($hist = mysqli_fetch_assoc($history_result)): ?>
                                    <div class="history-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="d-block fw-bold text-dark">Order #<?= $hist['id'] ?></span>
                                            <small class="text-muted"><?= date('M j, Y', strtotime($hist['created_at'])) ?></small>
                                        </div>
                                        <div class="text-end">
                                            <?php 
                                                $h_status = strtolower($hist['status']);
                                                $h_badge = 'bg-secondary';
                                                if(strpos($h_status, 'pending') !== false) $h_badge = 'status-pending';
                                                if(strpos($h_status, 'complete') !== false) $h_badge = 'status-completed';
                                                if(strpos($h_status, 'ship') !== false) $h_badge = 'status-shipped';
                                            ?>
                                            <span class="badge rounded-pill <?= $h_badge ?>"><?= ucfirst($hist['status']) ?></span>
                                            
                                            <div class="small fw-bold mt-1 text-dark">₱<?= number_format($hist['total_bill'], 2) ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <?php if($search_result): ?>
        <div class="col-md-6">
            <div class="track-card h-100">
                <div class="card-body p-5">
                    <div class="text-center">
                        <i class="fas fa-box-open fa-3x mb-3" style="color: var(--color-brand-gold);"></i>
                        <h3 class="fw-bold">Order #<?= $search_result['id']; ?></h3>
                        <p class="text-muted">Current Status</p>
                        
                        <?php 
                            $status = strtolower($search_result['status']);
                            $badgeClass = 'bg-secondary';
                            if(strpos($status, 'pending') !== false) $badgeClass = 'status-pending';
                            if(strpos($status, 'complete') !== false) $badgeClass = 'status-completed';
                            if(strpos($status, 'cancel') !== false) $badgeClass = 'status-cancelled';
                            if(strpos($status, 'ship') !== false) $badgeClass = 'status-shipped';
                        ?>
                        <span class="status-badge <?= $badgeClass ?> fs-5 px-4 py-2">
                            <?= strtoupper($status); ?>
                        </span>
                    </div>

                    <div class="status-box">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Customer:</span>
                            <span class="fw-bold"><?= htmlspecialchars($search_result['customer_name']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Date Ordered:</span>
                            <span class="fw-bold"><?= date('F j, Y', strtotime($search_result['created_at'])); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Payment:</span>
                            <span class="fw-bold"><?= $search_result['payment_method']; ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-dark">TOTAL AMOUNT</span>
                            <span class="fw-bold fs-4" style="color: var(--color-brand-gold);">₱<?= number_format($search_result['total_bill'], 2); ?></span>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 small border-0 bg-opacity-10">
                        <i class="fas fa-info-circle me-1"></i>
                        Need to change something? Please contact our support team immediately.
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>