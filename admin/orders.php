<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="fw-bold text-dark display-6">Manage Orders</h1>
            <p class="text-muted mb-0">View and process customer orders.</p>
        </div>
        <a href="orders-archived.php" class="btn btn-outline-dark rounded-pill fw-semibold px-4 py-2 shadow-sm">
            <i class="fas fa-archive me-2"></i>Archived Orders
        </a>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" style="border-top: 5px solid #b2935b !important;">
        <div class="card-body p-0">
            
            <?php alertMessage(); ?>

            <div class="table-responsive">
                <?php
                $orders_q = "SELECT * FROM orders WHERE status != 'archived' ORDER BY id DESC";
                $orders = mysqli_query($con, $orders_q);

                if (!$orders) {
                    echo '<div class="p-5 text-center text-muted"><h4>Something went wrong.</h4></div>';
                    return;
                }

                if (mysqli_num_rows($orders) > 0):
                ?>
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4 py-3" width="8%">ID</th>
                            <th class="py-3" width="18%">Customer</th>
                            <th class="py-3" width="20%">Contact Info</th>
                            <th class="py-3" width="12%">Total</th>
                            <th class="py-3" width="10%">Payment</th>
                            <th class="py-3" width="10%">Status</th>
                            <th class="py-3" width="12%">Date</th>
                            <th class="text-center pe-4 py-3" width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                            <?php
                                $s = strtolower($o['status']);
                                
                                $badgeClass = match($s) {
                                    'pending'   => 'status-pending',
                                    'approved'  => 'status-approved',
                                    'shipped'   => 'status-shipped',
                                    'completed' => 'status-completed',
                                    'cancelled' => 'status-cancelled',
                                    'archived'  => 'status-default',
                                    default     => 'status-default'
                                };
                            ?>
                            <tr class="order-row transition-all">
                                <td class="ps-4 fw-bold text-gold">#<?= $o['id']; ?></td>
                                
                                <td>
                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($o['customer_name']); ?></span>
                                </td>
                                
                                <td>
                                    <div class="d-flex flex-column small text-muted">
                                        <span><i class="fas fa-envelope me-2 opacity-50"></i><?= htmlspecialchars($o['customer_email']); ?></span>
                                        <span class="mt-1"><i class="fas fa-phone me-2 opacity-50"></i><?= htmlspecialchars($o['customer_phone']); ?></span>
                                    </div>
                                </td>
                                
                                <td><span class="fw-bold text-dark">₱<?= number_format($o['total_amount'], 2); ?></span></td>
                                
                                <td>
                                    <span class="badge bg-white text-dark border border-secondary border-opacity-25 px-2 py-1 rounded-1 fw-normal">
                                        <?= htmlspecialchars($o['payment_method'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                
                                <td><span class="badge rounded-pill <?= $badgeClass; ?> px-3 py-2 shadow-sm"><?= ucfirst($s); ?></span></td>
                                
                                <td>
                                    <div class="small text-muted lh-sm">
                                        <div class="fw-semibold"><?= date("M d, Y", strtotime($o['created_at'])); ?></div>
                                        <div class="opacity-75"><?= date("h:i A", strtotime($o['created_at'])); ?></div>
                                    </div>
                                </td>
                                
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2 action-buttons">
                                        
                                        <a href="orders-view.php?id=<?= $o['id']; ?>" class="btn btn-view" title="View Details"><i class="fas fa-eye"></i></a>

                                        <?php if ($s === 'pending'): ?>
                                            <a href="orders-action.php?id=<?= $o['id']; ?>&action=approve" class="btn btn-approve" title="Approve"><i class="fas fa-check"></i></a>
                                            <a href="orders-action.php?id=<?= $o['id']; ?>&action=cancel" class="btn btn-cancel" title="Cancel"><i class="fas fa-times"></i></a>
                                        
                                        <?php elseif ($s === 'approved'): ?>
                                            <a href="orders-action.php?id=<?= $o['id']; ?>&action=ship" class="btn btn-ship" title="Mark as Shipped"><i class="fas fa-truck"></i></a>
                                            <a href="orders-action.php?id=<?= $o['id']; ?>&action=cancel" class="btn btn-cancel" title="Cancel"><i class="fas fa-times"></i></a>
                                        
                                        <?php elseif ($s === 'shipped'): ?>
                                            <a href="orders-action.php?id=<?= $o['id']; ?>&action=complete" class="btn btn-complete" title="Mark Completed"><i class="fas fa-flag-checkered"></i></a>
                                        
                                        <?php elseif (in_array($s, ['completed', 'cancelled'])): ?>
                                            <a href="orders-action.php?id=<?= $o['id']; ?>&action=archive" class="btn btn-archive" title="Archive"><i class="fas fa-box-archive"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="p-5 text-center">
                        <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-25"></i>
                        <h5 class="text-muted">No Active Orders Found</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- Table Styling (Dark Header) --- */
    .table thead th {
        background-color: #1e1e1e !important; /* Dark Header */
        color: #fff !important; /* White Text */
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
        border: none;
        vertical-align: middle;
    }
    
    /* Zebra Striping for contrast */
    .table-striped > tbody > tr:nth-of-type(odd) > * {
        --bs-table-accent-bg: #f8f9fa; /* Very light gray */
        color: var(--bs-table-striped-color);
    }

    .table tbody td {
        padding: 1rem 1rem;
        vertical-align: middle;
        font-size: 0.95rem;
        border-bottom: 1px solid #eee;
    }

    /* Row Hover */
    .order-row:hover {
        background-color: #fffbf0 !important; /* Gold tint on hover */
        z-index: 5;
        position: relative;
    }

    /* Colors */
    .text-gold { color: #b2935b !important; }

    /* Status Badges */
    .badge { padding: 0.6em 1em; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px; }
    .status-pending { background-color: #ffc107; color: #000; }
    .status-approved { background-color: #0d6efd; color: white; }
    .status-shipped { background-color: #6f42c1; color: white; }
    .status-completed { background-color: #198754; color: white; }
    .status-cancelled { background-color: #dc3545; color: white; }
    .status-default { background-color: #6c757d; color: white; }

    /* Buttons */
    .btn-view, .btn-approve, .btn-cancel, .btn-complete, .btn-archive, .btn-ship {
        width: 35px; height: 35px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        border: none; transition: all 0.2s ease; color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .btn-view { background-color: #fff; color: #b2935b; border: 1px solid #eee; }
    .btn-view:hover { background-color: #b2935b; color: white; }
    .btn-approve { background-color: #198754; }
    .btn-approve:hover { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(25, 135, 84, 0.3); }
    .btn-cancel { background-color: #dc3545; }
    .btn-cancel:hover { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3); }
    .btn-ship { background-color: #6f42c1; }
    .btn-ship:hover { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(111, 66, 193, 0.3); }
    .btn-complete { background-color: #0d6efd; }
    .btn-complete:hover { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3); }
    .btn-archive { background-color: #6c757d; }
    .btn-archive:hover { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3); }
</style>

<?php include('include1/footer.php'); ?>