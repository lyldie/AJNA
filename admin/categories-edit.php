<?php include ('include1/header.php'); ?>

<div class="container-fluid px-4">
    <?php
    $parmValue = checkParamId('id');
    if (!is_numeric($parmValue)) {
        echo '<div class="alert alert-danger mt-4">' . $parmValue . '</div>';
        include('include1/footer.php');
        return false;
    }

    $category = getById('categories', $parmValue);
    if ($category['status'] == 200) {
        $cat_data = $category['data'];
    ?>
    <form action="code.php" method="POST">
        <input type="hidden" name="categoryId" value="<?php echo $cat_data['id']; ?>" />

        <div class="card mt-4 border-0 shadow-sm rounded-4">
            
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between px-3 py-3">
                <h4 class="fw-bold mb-0" style="color: #b2935b;">
                    <i class="fas fa-edit me-2"></i>Edit Category
                </h4>
                <a href="categories.php" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Categories
                </a>
            </div>

            <div class="card-body p-4">
                <?php alertMessage(); ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Category Details</h5>
                            </div>
                            <div class="card-body">
                                
                                <div class="mb-3">
                                    <label class="form-label">Parent Category (Optional)</label>
                                    <small class="text-muted d-block mb-2">Select a parent category to make this a sub-category.</small>
                                    <select name="parent_id" class="form-select">
                                        <option value="">No Parent (Main Category)</option>
                                        <?php 
                                        $current_id = $cat_data['id'];
                                        // Exclude current category para hindi siya maging parent ng sarili niya
                                        $cat_query = "SELECT * FROM categories WHERE status='active' AND id != '$current_id' ORDER BY name ASC";
                                        $cat_result = mysqli_query($con, $cat_query);
                                        
                                        if(mysqli_num_rows($cat_result) > 0){
                                            while($row = mysqli_fetch_assoc($cat_result)){
                                                // Check if this is the currently assigned parent
                                                $selected = ($cat_data['parent_id'] == $row['id']) ? 'selected' : '';
                                                echo '<option value="'.$row['id'].'" '.$selected.'>'.$row['name'].'</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Category Name*</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($cat_data['name']); ?>" required class="form-control" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description (Optional)</label>
                                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($cat_data['description']); ?></textarea>
                                </div>
                                
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-end">
                        <button class="btn btn-lg btn-dark" type="submit" name="updateCategory" style="background-color: #b2935b; border:none; color:white; padding: 0.5rem 2rem;">
                            <i class="fas fa-save me-2"></i>Update Category
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
    <?php
    } else {
        echo '<div class="alert alert-danger mt-4">' . $category['message'] . '</div>';
    }
    ?>
</div>

<style>
    .card .form-control,
    .card .form-select {
        border-radius: 8px !important;
        border: 1px solid #ced4da !important;
        padding-top: 0.65rem !important;
        padding-bottom: 0.65rem !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .card .form-control:focus,
    .card .form-select:focus {
        border-color: #b2935b !important;
        box-shadow: 0 0 0 0.25rem rgba(178, 147, 91, 0.25) !important;
    }

    .card .form-label {
        font-weight: 500 !important;
        color: #343a40;
    }
</style>

<?php include ('include1/footer.php'); ?>