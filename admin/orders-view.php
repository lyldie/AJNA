<?php 
include('include1/header.php'); 

if (!isset($_GET['id'])) {
    echo '<div class="container py-5"><h4 class="text-danger">No order id provided.</h4></div>';
    include('include1/footer.php');
    exit;
}

$order_id = intval($_GET['id']);
$stmt = mysqli_prepare($con, "SELECT * FROM orders WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_res = mysqli_stmt_get_result($stmt);

if (!$order_res || mysqli_num_rows($order_res) == 0) {
    echo '<div class="container py-5"><h4 class="text-danger">Order not found.</h4></div>';
    include('include1/footer.php');
    exit;
}
$order = mysqli_fetch_assoc($order_res);

$items_q = "SELECT oi.*, p.name AS product_name, p.image AS product_image 
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
            
$items_stmt = mysqli_prepare($con, $items_q);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
$items_res = mysqli_stmt_get_result($items_stmt);
?>

<div class="container-fluid px-4">
    <div class="card mt-4 border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center px-3 py-3">
            <h4 class="fw-bold mb-0" style="color: #b2935b;">
                 <i class="fas fa-receipt me-2"></i>Order Details #<?= $order['id']; ?>
            </h4>
            <div>
                <a href="orders.php" class="btn btn-outline-secondary rounded-pill fw-semibold px-3 me-2">← Back to Orders</a>
                
                <?php if ($order['status'] === 'approved'): ?>
                    <a href="orders-action.php?id=<?= $order['id']; ?>&action=ship" class="btn btn-ship-header rounded-pill fw-semibold px-3"><i class="fas fa-truck me-2"></i>Mark as Shipped</a>
                    <a href="orders-action.php?id=<?= $order['id']; ?>&action=cancel" class="btn btn-danger rounded-pill fw-semibold px-3">Cancel</a>
                <?php elseif ($order['status'] === 'shipped'): ?>
                    <a href="orders-action.php?id=<?= $order['id']; ?>&action=complete" class="btn btn-complete-header rounded-pill fw-semibold px-3"><i class="fas fa-flag-checkered me-2"></i>Mark as Completed</a>
                <?php elseif ($order['status'] === 'completed'): ?>
                    <a href="orders-action.php?id=<?= $order['id']; ?>&action=archive" class="btn btn-archive-header rounded-pill fw-semibold px-3">Archive Order</a>
                <?php endif; ?>
                </div>
        </div>

        <div class="card-body p-4">
            <?php alertMessage(); ?>

            <div class="row">
                <div class="col-lg-7">
                    
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Customer & Order Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Customer Information</h6>
                                    <p class="mb-1"><strong><?= htmlspecialchars($order['customer_name']); ?></strong></p>
                                    <p class="mb-1"><?= htmlspecialchars($order['customer_email']); ?></p>
                                    <p class="mb-1"><?= htmlspecialchars($order['customer_phone']); ?></p>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Order Details</h6>
                                    <?php
                                        $s = strtolower($order['status']);
                                        $badgeClass = match($s) {
                                            'pending'   => 'status-pending',
                                            'approved'  => 'status-approved',
                                            'shipped'   => 'status-shipped',
                                            'completed' => 'status-completed',
                                            'cancelled' => 'status-cancelled',
                                            'archived'  => 'status-archived',
                                            default     => 'status-default'
                                        };
                                    ?>
                                    <p class="mb-1"><strong>Status:</strong> <span class="badge <?= $badgeClass; ?>"><?= ucfirst($order['status']); ?></span></p>
                                    <p class="mb-1"><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></p>
                                    
                                    <?php if($order['payment_method'] === 'GCash'): ?>
                                        <p class="mb-1"><strong>Ref No:</strong> <span class="badge bg-dark text-white"><?= htmlspecialchars($order['pay_ref_number'] ?? 'N/A'); ?></span></p>
                                    <?php endif; ?>

                                    <p class="mb-1"><strong>Total:</strong> <span class="fw-bold fs-5" style="color: #b2935b;">₱<?= number_format($order['total_amount'], 2); ?></span></p>
                                    <p class="mb-0"><strong>Date:</strong> <?= date("M d, Y h:i A", strtotime($order['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                             <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Order Items</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead style="background: #f8f9fa;">
                                        <tr>
                                            <th>Product</th>
                                            <th width="120">Price</th>
                                            <th width="120">Qty</th>
                                            <th width="150">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $grand = 0;
                                        while ($it = mysqli_fetch_assoc($items_res)):
                                            $images = explode(',', $it['product_image']);
                                            $first_image = trim($images[0]);
                                            $sub = $it['price'] * $it['quantity'];
                                            $grand += $sub;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($first_image)): ?>
                                                        <img src="../<?= htmlspecialchars($first_image); ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:6px;" class="me-3">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong class="d-block"><?= htmlspecialchars($it['product_name']); ?></strong>
                                                        <?php if(!empty($it['size'])): ?>
                                                            <small class="text-muted d-block">Size: <strong><?= htmlspecialchars($it['size']); ?></strong></small>
                                                        <?php endif; ?>
                                                        <?php if(!empty($it['color'])): ?>
                                                            <small class="text-muted d-block">Color: <strong><?= htmlspecialchars($it['color']); ?></strong></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>₱<?= number_format($it['price'], 2); ?></td>
                                            <td><?= intval($it['quantity']); ?></td>
                                            <td>₱<?= number_format($sub, 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                            <td class="fw-bold">₱<?= number_format($grand, 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <?php 
                    // ✅✅✅ NEW LOGIC FOR RIGHT PANEL ✅✅✅
                    if ($order['status'] == 'pending') : 
                    ?>
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-header bg-gold text-white rounded-top-4">
                                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Action Required</h5>
                            </div>
                            <div class="card-body text-center">
                                
                                <?php if ($order['payment_method'] === 'GCash'): ?>
                                    <div class="alert alert-light border">
                                        <h6 class="fw-bold text-dark mb-1">GCash Payment Verification</h6>
                                        <p class="mb-2 small text-muted">Please check your GCash SMS/App for Reference No:</p>
                                        <h2 class="fw-bold text-primary display-6 mb-3">
                                            <?= htmlspecialchars($order['pay_ref_number'] ?? '-------'); ?>
                                        </h2>
                                        <p class="small text-muted mb-0">Total Amount: <strong>₱<?= number_format($order['total_amount'], 2); ?></strong></p>
                                    </div>
                                    
                                    <hr class="my-3">
                                    <p class="h6">Is this reference number valid and received?</p>
                                    <div class="d-grid gap-2">
                                        <a href="orders-action.php?id=<?= $order['id']; ?>&act=approve" class="btn btn-approve btn-lg">✅ Approve Payment</a>
                                        <a href="orders-action.php?id=<?= $order['id']; ?>&act=cancel" class="btn btn-cancel btn-lg">❌ Invalid / Cancel</a>
                                    </div>
                                
                                <?php elseif ($order['payment_method'] === 'Cash on Delivery'): ?>
                                    <div class="alert alert-light border">
                                        <h6 class="fw-bold text-dark mb-1">Cash on Delivery</h6>
                                        <p class="mb-0 small text-muted">Please confirm the order details with the customer.</p>
                                    </div>
                                    <hr class="my-3">
                                    <div class="d-grid gap-2">
                                        <a href="orders-action.php?id=<?= $order['id']; ?>&act=approve" class="btn btn-approve btn-lg">✅ Confirm Order</a>
                                        <a href="orders-action.php?id=<?= $order['id']; ?>&act=cancel" class="btn btn-cancel btn-lg">❌ Cancel Order</a>
                                    </div>
                                
                                <?php else: ?>
                                    <div class="alert alert-danger">Error: Missing payment info.</div>
                                    <a href="orders-action.php?id=<?= $order['id']; ?>&act=cancel" class="btn btn-danger btn-lg w-100">Cancel Order</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                    else: 
                    ?>
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-body">
                                <h5 class="fw-semibold" style="color: #b2935b;">Order Status: <?= ucfirst($order['status']); ?></h5>
                                <p class="mb-0 text-muted">
                                    <?php 
                                        if($order['status'] == 'approved') {
                                            echo "Order confirmed. Prepare items for shipment.";
                                        } elseif ($order['status'] == 'shipped') {
                                            echo "Item is with courier. Mark as completed once delivered.";
                                        } else {
                                            echo "Transaction closed.";
                                        }
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Status badges */
.badge { padding: 0.5em 0.75em; }
.status-pending { background-color: #b2935b !important; color: white !important; }
.status-approved { background-color: #0d6efd !important; color: white !important; }
.status-shipped { background-color: #6f42c1 !important; color: white !important; }
.status-completed { background-color: #198754 !important; color: white !important; }
.status-cancelled { background-color: #dc3545 !important; color: white !important; }
.status-archived, .status-default { background-color: #6c757d !important; color: white !important; }

/* Buttons */
.btn-approve, .btn-cancel, .btn-complete-header, .btn-archive-header, .btn-ship-header {
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    transition: all 0.2s ease-in-out;
    color: white !important;
    font-weight: 600;
}
.btn-approve { background-color: #28a745; }
.btn-approve:hover { background-color: #218838; }
.btn-cancel { background-color: #dc3545; }
.btn-cancel:hover { background-color: #b02a37; }

/* Header buttons */
.btn-ship-header { background-color: #6f42c1; border-radius: 50rem !important; padding: .375rem .75rem !important; }
.btn-ship-header:hover { background-color: #5a37a0; }
.btn-complete-header { background-color: #198754; border-radius: 50rem !important; padding: .375rem .75rem !important; }
.btn-complete-header:hover { background-color: #146c43; }
.btn-archive-header { background-color: #6c757d; border-radius: 50rem !important; padding: .375rem .75rem !important; }
.btn-archive-header:hover { background-color: #5a6268; }

.bg-gold { background-color: #b2935b !important; }
</style>

<?php include('include1/footer.php'); ?>