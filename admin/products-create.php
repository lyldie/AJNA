<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    <form action="code.php" method="POST" enctype="multipart/form-data">
        <div class="card mt-4 border-0 shadow-sm rounded-4">
            
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between px-3 py-3">
                <h4 class="fw-bold mb-0" style="color: #b2935b;">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </h4>
                <a href="products.php" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                </a>
            </div>

            <div class="card-body p-4">
                <?php alertMessage(); ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Main Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Product Name*</label>
                                        <input type="text" name="name" required class="form-control" placeholder="Enter product name" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Category*</label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                            <?php
                                            $categories = getAll('categories');
                                            if ($categories && mysqli_num_rows($categories) > 0) {
                                                foreach ($categories as $cateItem) {
                                                    echo '<option value="' . $cateItem['id'] . '">' . $cateItem['name'] . '</option>';
                                                }
                                            } else {
                                                echo '<option value="">No Categories Found</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Enter product details..."></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Price (₱)*</label>
                                        <input type="number" step="0.01" name="price" required class="form-control" placeholder="Enter price" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Product Variations (Inventory)</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary add-variation-btn">
                                    <i class="fas fa-plus"></i> Add Another Variation
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Specify the stock for each size and color combination.</p>
                                
                                <div id="variation-area">
                                    <div class="row g-2 align-items-center mb-2 variation-item">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Size</label>
                                            <input type="text" name="variation_size[]" class="form-control" placeholder="e.g. 41 or Small" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Color</label>
                                            <input type="text" name="variation_color[]" class="form-control" placeholder="e.g. Black" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small text-muted">Quantity</label>
                                            <input type="number" name="variation_qty[]" class="form-control" placeholder="0" min="0" required>
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <label class="small text-white">.</label><br>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                         <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Product Images</h5>
                            </div>
                            <div class="card-body">
                                <div class="col-md-12">
                                    <label class="form-label">Upload Images*</label>
                                    <input type="file" name="images[]" class="form-control" required multiple accept="image/*" />
                                    <small class="text-muted">You can upload multiple images (hold Ctrl or Cmd to select more than one).</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button class="btn btn-lg btn-dark w-100" type="submit" name="saveProduct" style="background-color: #b2935b; border:none; color:white;">
                                <i class="fas fa-save me-2"></i>Save Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.querySelector('.add-variation-btn');
    const area = document.getElementById('variation-area');

    addBtn.addEventListener('click', function() {
        const item = document.createElement('div');
        item.classList.add('row', 'g-2', 'align-items-center', 'mb-2', 'variation-item');
        item.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="variation_size[]" class="form-control" placeholder="e.g. 42 or Medium" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="variation_color[]" class="form-control" placeholder="e.g. White" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="variation_qty[]" class="form-control" placeholder="0" min="0" required>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-danger btn-sm remove-var-btn"><i class="fas fa-trash"></i></button>
            </div>
        `;
        area.appendChild(item);
    });

    area.addEventListener('click', function(e) {
        if (e.target.closest('.remove-var-btn')) {
            e.target.closest('.variation-item').remove();
        }
    });
});
</script>

<?php include('include1/footer.php'); ?>