<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="fw-bold text-dark display-6">Inventory Logs</h1>
            <p class="text-muted mb-0">Track history of stock adjustments.</p>
        </div>
        <div>
            <a href="admin-logs-archived.php" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold shadow-sm">
                <i class="fas fa-archive me-2"></i>View Archived Logs
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" style="border-top: 5px solid #b2935b !important;">
        <div class="card-body p-0">
            <?php alertMessage(); ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4 py-3">Date</th>
                            <th class="py-3">Admin</th>
                            <th class="py-3">Product</th>
                            <th class="py-3 text-center">Qty</th>
                            <th class="py-3" width="30%">Reason</th>
                            <th class="py-3 text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Filter by status = 'active'
                        $query = "SELECT l.*, p.name as product_name, v.size, v.color 
                                  FROM inventory_logs l
                                  LEFT JOIN products p ON l.product_id = p.id
                                  LEFT JOIN product_variations v ON l.variation_id = v.id
                                  WHERE l.status = 'active'
                                  ORDER BY l.created_at DESC";
                        
                        $logs = mysqli_query($con, $query);

                        if(mysqli_num_rows($logs) > 0) {
                            foreach($logs as $log) {
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted small">
                                        <?= date('M d, Y h:i A', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="fw-bold"><?= htmlspecialchars($log['admin_name']); ?></td>
                                    <td>
                                        <span class="d-block fw-bold text-primary" style="color: #b2935b !important;">
                                            <?= htmlspecialchars($log['product_name']); ?>
                                        </span>
                                        <?php if($log['size'] || $log['color']): ?>
                                            <small class="text-muted border px-2 rounded-pill bg-light">
                                                <?= $log['size'] ?? '-'; ?> | <?= $log['color'] ?? '-'; ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 rounded-pill border border-danger">
                                            -<?= $log['quantity']; ?>
                                        </span>
                                    </td>
                                    <td class="text-secondary fst-italic">
                                        "<?= htmlspecialchars($log['reason']); ?>"
                                    </td>
                                    <td class="text-center pe-4">
                                        <form action="code.php" method="POST" onsubmit="return confirm('Archive this log entry? It will be hidden from this list.');">
                                            <input type="hidden" name="log_id" value="<?= $log['id']; ?>">
                                            <button type="submit" name="archiveLog" class="btn btn-sm btn-light border text-secondary shadow-sm" title="Archive">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center py-5 text-muted">No active logs found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('include1/footer.php'); ?>