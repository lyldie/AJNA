<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="fw-bold text-dark display-6">Login History</h1>
            <p class="text-muted mb-0">Monitor user access logs.</p>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" style="border-top: 5px solid #b2935b !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3">Timestamp</th>
                            <th class="py-3">User Details</th>
                            <th class="py-3">Role</th>
                            <th class="py-3 text-center pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM login_logs ORDER BY login_time DESC LIMIT 100";
                        $logs = mysqli_query($con, $query);

                        if(mysqli_num_rows($logs) > 0) {
                            foreach($logs as $log) {
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted small">
                                        <i class="far fa-clock me-1"></i>
                                        <?= date('M d, Y h:i A', strtotime($log['login_time'])); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold text-dark"><?= htmlspecialchars($log['email']); ?></span>
                                                <small class="text-muted" style="font-size: 0.75rem;">ID: #<?= $log['user_id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary text-uppercase" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars($log['role']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 border border-success">
                                            Success
                                        </span>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            // Updated colspan from 5 to 4
                            echo '<tr><td colspan="4" class="text-center py-5 text-muted"><i class="fas fa-shield-alt fa-2x mb-3 opacity-25"></i><br>No login records found yet.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th { 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 1px; 
        font-weight: 700;
    }
</style>

<?php include('include1/footer.php'); ?>