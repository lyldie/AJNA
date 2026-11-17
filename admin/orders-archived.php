<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    <div class="card mt-4 shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Archived Orders</h4>
            <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
        </div>
        <div class="card-body">
            <?php alertMessage(); ?>
            <div class="table-responsive">
                <?php
                $q = "SELECT * FROM orders WHERE status = 'archived' ORDER BY id DESC";
                $res = mysqli_query($con, $q);
                if (!$res) {
                    echo '<h4> Something went wrong</h4>';
                    return false;
                }
                if (mysqli_num_rows($res) > 0):
                ?>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($o = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td>#<?= $o['id']; ?></td>
                            <td><?= htmlspecialchars($o['customer_name']); ?></td>
                            <td>₱<?= number_format($o['total_amount'], 2); ?></td>
                            <td><?= date("M d, Y h:i A", strtotime($o['created_at'])); ?></td>
                            <td>
                                <a href="orders-view.php?id=<?= $o['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                <a href="orders-action.php?id=<?= $o['id']; ?>&act=restore" class="btn btn-sm btn-warning">Restore</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <h5 class="mb-0">No archived orders.</h5>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('include1/footer.php'); ?>
