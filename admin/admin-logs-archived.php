<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="fw-bold text-secondary display-6">Archived Logs</h1>
            <p class="text-muted mb-0">History of hidden adjustments.</p>
        </div>
        <div>
            <a href="admin-logs.php" class="btn btn-dark rounded-pill px-4 fw-semibold shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to Active Logs
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" style="border-top: 5px solid #6c757d !important;">
        <div class="card-body p-0">
            <?php alertMessage(); ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-secondary text-white">
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
                        // Filter by status = 'archived'
                        $query = "SELECT l.*, p.name as product_name, v.size, v.color 
                                  FROM inventory_logs l
                                  LEFT JOIN products p ON l.product_id = p.id
                                  LEFT JOIN product_variations v ON l.variation_id = v.id
                                  WHERE l.status = 'archived'
                                  ORDER BY l.created_at DESC";
                        
                        $logs = mysqli_query($con, $query);

                        if(mysqli_num_rows($logs) > 0) {
                            foreach($logs as $log) {
                                ?>
                                <tr class="text-muted"> <td class="ps-4 small">
                                        <?= date('M d, Y h:i A', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td><?= htmlspecialchars($log['admin_name']); ?></td>
                                    <td>
                                        <span class="d-block fw-bold">
                                            <?= htmlspecialchars($log['product_name']); ?>
                                        </span>
                                        <?php if($log['size'] || $log['color']): ?>
                                            <small class="border px-2 rounded-pill">
                                                <?= $log['size'] ?? '-'; ?> | <?= $log['color'] ?? '-'; ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        -<?= $log['quantity']; ?>
                                    </td>
                                    <td class="fst-italic">
                                        "<?= htmlspecialchars($log['reason']); ?>"
                                    </td>
                                    <td class="text-center pe-4">
                                        <form action="code.php" method="POST" onsubmit="return confirm('Restore this log to active list?');">
                                            <input type="hidden" name="log_id" value="<?= $log['id']; ?>">
                                            <button type="submit" name="restoreLog" class="btn btn-sm btn-success text-white shadow-sm" title="Restore">
                                                <i class="fas fa-undo me-1"></i> Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center py-5 text-muted">No archived logs found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('include1/footer.php'); ?>