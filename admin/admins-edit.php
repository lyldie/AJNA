<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    <?php
    // Ginamit natin 'yung mas magandang validation galing sa products-edit
    $paramValue = checkParamId('id');
    if (!is_numeric($paramValue)) {
        echo '<div class="alert alert-danger mt-4">' . $paramValue . '</div>';
        include('include1/footer.php');
        return false;
    }

    // Kukunin natin 'yung data ng admin
    $admin = getById('admins', $paramValue);
    if ($admin && $admin['status'] == 200) {
        $a = $admin['data']; // '$a' for Admin
    ?>
    
    <form action="code.php" method="POST">
        <input type="hidden" name="adminId" value="<?php echo $a['id']; ?>" />
        
        <div class="card mt-4 border-0 shadow-sm rounded-4">
            
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between px-3 py-3">
                <h4 class="fw-bold mb-0" style="color: #b2935b;">
                    <i class="fas fa-user-edit me-2"></i> Edit Admin/Staff </h4>
                <a href="admins.php" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Admins </a>
            </div>

            <div class="card-body p-4">
                <?php alertMessage(); ?>

                <div class="row">
                    
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Account Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name*</label>
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($a['name']); ?>" required class="form-control" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email*</label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($a['email']); ?>" required class="form-control" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" />
                                        <small class="text-muted">Leave blank to keep current password.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" value="<?php echo htmlspecialchars($a['phone']); ?>" class="form-control"/>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" value="<?php echo htmlspecialchars($a['address']); ?>" class="form-control"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        </div>

                    <div class="col-lg-4">
                        
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Role & Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Position / Role*</label>
                                    <input type="text" name="position" value="<?php echo htmlspecialchars($a['position']); ?>" required class="form-control" placeholder="e.g., Manager, Staff" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?php echo ($a['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($a['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="updateAdmin" class="btn btn-lg w-100" style="background-color: #b2935b; border:none; color:white;">
                                <i class="fas fa-save me-2"></i>Update Admin
                            </button>
                        </div>
                    </div>

                </div> </div> </div> </form>
    
    <?php
    } else {
        // Error message galing sa getById()
        echo '<div class="alert alert-danger mt-4">' . $admin['message'] . '</div>';
    }
    ?>
</div>

<?php include('include1/footer.php'); ?>