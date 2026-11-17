<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="fw-bold text-dark display-6">Manage Categories</h1>
            <p class="text-muted mb-0">Organize your product catalog.</p>
        </div>
        <div class="btn-group shadow-sm rounded-pill" role="group">
            <a href="categories-create.php" class="btn btn-dark px-4 fw-semibold">
                <i class="fas fa-plus me-2"></i>Add Category
            </a>
            <a href="categories-archived.php" class="btn btn-outline-secondary px-4 fw-semibold">
                <i class="fas fa-archive me-2"></i>View Archived
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" style="border-top: 5px solid #b2935b !important;">
        <div class="card-body p-0">
            <?php alertMessage(); ?>

            <div class="table-responsive"> 
                <?php
                // 1. Fetch ALL active categories
                $query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
                $result = mysqli_query($con, $query);

                // 2. Prepare Data Arrays
                $parents = [];
                $children = [];

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        if($row['parent_id'] == NULL) {
                            $parents[] = $row;
                        } else {
                            $children[$row['parent_id']][] = $row;
                        }
                    }
                ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4 py-3" width="35%">Category Structure</th> <th class="py-3" width="35%">Description</th>
                            <th class="py-3" width="15%">Status</th>
                            <th class="text-center pe-4 py-3" width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // 3. Loop Parents First
                        foreach ($parents as $parent): 
                        ?>
                            <tr class="align-middle bg-light">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($parent['name']); ?></span>
                                            <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">Main</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="text-muted small"><?= htmlspecialchars($parent['description']); ?></span></td>
                                <td><span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">Active</span></td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="categories-edit.php?id=<?= $parent['id']; ?>" class="btn btn-sm btn-circle btn-approve shadow-sm"><i class="fas fa-edit"></i></a>
                                        <a href="categories-restore.php?id=<?= $parent['id']; ?>&action=archive" class="btn btn-sm btn-circle btn-cancel shadow-sm"><i class="fas fa-archive"></i></a>
                                    </div>
                                </td>
                            </tr>

                            <?php 
                            // 4. Check if Parent has Children and Loop them
                            if(isset($children[$parent['id']])): 
                                foreach($children[$parent['id']] as $child):
                            ?>
                                <tr class="align-middle hover-row">
                                    <td class="ps-4" style="padding-left: 50px !important;"> <div class="d-flex align-items-center position-relative">
                                            <div style="position: absolute; left: -25px; top: -20px; bottom: 50%; width: 2px; background: #ddd;"></div>
                                            <div style="position: absolute; left: -25px; top: 50%; width: 15px; height: 2px; background: #ddd;"></div>
                                            
                                            <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-2 ms-2"></i>
                                            <span class="fw-semibold text-gold"><?= htmlspecialchars($child['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><span class="text-muted small ms-4"><?= htmlspecialchars($child['description']); ?></span></td>
                                    <td><span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">Active</span></td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="categories-edit.php?id=<?= $child['id']; ?>" class="btn btn-sm btn-outline-success border-0"><i class="fas fa-edit"></i></a>
                                            <a href="categories-restore.php?id=<?= $child['id']; ?>&action=archive" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-archive"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endforeach; 
                            endif; 
                            ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php 
                } else {
                    echo '<div class="p-5 text-center text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                            <h5>No Active Categories Found</h5>
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Table Styling */
    .table thead th {
        background-color: #1e1e1e !important;
        color: #fff !important;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
        border: none;
    }
    .table tbody td {
        padding: 1rem 1rem;
        border-bottom: 1px solid #f0f0f0;
    }
    
    /* Hover effects specifically for Child Rows */
    .hover-row:hover {
        background-color: #fffbf0 !important; /* Gold Tint */
    }

    .text-gold { color: #b2935b; }

    /* Buttons */
    .btn-circle {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
        color: white;
        border: none;
    }
    .btn-circle:hover { transform: translateY(-3px); }
    .btn-approve { background-color: #28a745; }
    .btn-cancel { background-color: #dc3545; }
</style>

<?php include('include1/footer.php'); ?>