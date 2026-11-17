<?php include ('include1/header.php'); ?>

<div class="container-fluid px-4">
    <form action="code.php" method="POST">
        
        <div class="card mt-4 border-0 shadow-sm rounded-4">
            
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between px-3 py-3">
                <h4 class="fw-bold mb-0" style="color: #b2935b;">
                    <i class="fas fa-user-plus me-2"></i>Add New Admin/Staff
                </h4>
                <a href="admins.php" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Admins
                </a>
            </div>

            <div class="card-body p-4">
                <?php alertMessage(); ?>

                <div class="row">
                    <div class="col-12">
                        
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Account Credentials</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name*</label>
                                        <input type="text" name="name" required class="form-control" placeholder="Enter full name"/>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email*</label>
                                        <input type="email" name="email" required class="form-control" placeholder="Enter email address"/>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password*</label>
                                        <input type="password" name="password" required class="form-control" placeholder="Create a strong password"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Contact Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Position*</label>
                                        <input type="text" name="position" required class="form-control" placeholder="e.g., Manager, Staff"/>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number*</label>
                                        <input type="number" name="phone" required class="form-control" placeholder="Enter phone number"/>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Address*</label>
                                        <textarea name="address" required class="form-control" rows="3" placeholder="Enter full address"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button class="btn btn-lg btn-dark" type="submit" name="saveAdmin" style="background-color: #b2935b; border:none; color:white; padding: 0.5rem 2rem;">
                                <i class="fas fa-save me-2"></i>Save Admin
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include ('include1/footer.php'); ?>