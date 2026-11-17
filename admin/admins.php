<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="fw-bold text-dark display-6">Admins & Staff</h1>
            <p class="text-muted mb-0">Manage system users and access.</p>
        </div>
        <div class="btn-group shadow-sm rounded-pill" role="group">
            <a href="admins-create.php" class="btn btn-dark px-4 fw-semibold">
                <i class="fas fa-user-plus me-2"></i>Add User
            </a>
            <a href="admins-archived.php" class="btn btn-outline-secondary px-4 fw-semibold">
                <i class="fas fa-archive me-2"></i>Archived
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" style="border-top: 5px solid #b2935b;">
        <div class="card-body p-0">
            <?php alertMessage();?>

            <div class="table-responsive"> 
                <?php
                $admins = mysqli_query($con, "SELECT * FROM admins WHERE status = 'active' ORDER BY id DESC");

                if(!$admins) {
                    echo '<div class="p-5 text-center"><h4>Something went wrong.</h4></div>';
                    return false;
                }
                if(mysqli_num_rows($admins) > 0) {
                ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4 py-3" width="10%">ID</th>
                            <th class="py-3" width="30%">Name</th>
                            <th class="py-3" width="30%">Email</th>
                            <th class="py-3" width="15%">Status</th>
                            <th class="text-center pe-4 py-3" width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($admins as $adminItem): ?>
                            <tr class="transition-all hover-row">
                                <td class="ps-4 fw-bold text-gold">#<?= $adminItem['id']; ?></td>
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3 fw-bold" style="width:35px; height:35px; font-size:0.8rem;">
                                            <?= strtoupper(substr($adminItem['name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($adminItem['name']); ?></span>
                                    </div>
                                </td>
                                
                                <td class="text-muted"><?= htmlspecialchars($adminItem['email']); ?></td>
                                
                                <td><span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">Active</span></td>
                                
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="admins-edit.php?id=<?= $adminItem['id']; ?>" class="btn btn-sm btn-circle btn-approve shadow-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php
                                        // ✅ LOGIC PRESERVED: Prevent self-archive
                                        $loggedInAdminId = $_SESSION['loggedUser']['user_id']; 
                                        
                                        if ($adminItem['id'] != $loggedInAdminId) {
                                        ?>
                                            <a href="admins-archive.php?id=<?= $adminItem['id']; ?>&action=archive" class="btn btn-sm btn-circle btn-cancel shadow-sm" title="Archive">
                                                <i class="fas fa-archive"></i>
                                            </a>
                                        <?php } else { ?>
                                            <button class="btn btn-sm btn-circle btn-secondary shadow-sm disabled" title="Cannot archive yourself" disabled>
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php 
                } else {
                    echo '<div class="p-5 text-center text-muted">
                            <i class="fas fa-users-slash fa-3x mb-3 opacity-25"></i>
                            <h5>No Active Admins Found</h5>
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Consistent Styles */
    .table thead th { background-color: #1e1e1e !important; color: #fff !important; font-weight: 500; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; border: none; }
    .table tbody td { padding: 1.2rem 1rem; border-bottom: 1px solid #f0f0f0; }
    .hover-row:hover { background-color: #fffbf0 !important; transform: scale(1.002); box-shadow: 0 5px 15px rgba(0,0,0,0.05); z-index: 5; position: relative; }
    
    .btn-circle { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; color:white; border:none; }
    .btn-circle:hover { transform: translateY(-3px); }
    
    .text-gold { color: #b2935b; }
    
    .btn-approve { background-color: #28a745; }
    .btn-approve:hover { background-color: #218838; }
    .btn-cancel { background-color: #dc3545; }
    .btn-cancel:hover { background-color: #b02a37; }
</style>

<?php include ('include1/footer.php'); ?>