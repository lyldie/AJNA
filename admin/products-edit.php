<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    <?php
    $paramValue = checkParamId('id');
    if (!is_numeric($paramValue)) {
        echo '<div class="alert alert-danger mt-4">Invalid Product ID</div>';
        include('include1/footer.php');
        return false;
    }

    $product = getById('products', $paramValue);
    if ($product && $product['status'] == 200) {
        $p = $product['data'];

        // --- FETCH VARIATIONS ---
        $variations_query = mysqli_query($con, "SELECT * FROM product_variations WHERE product_id = '$paramValue'");
    ?>
        <form action="code.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="productId" value="<?php echo $p['id']; ?>" />

            <div class="card mt-4 border-0 shadow-sm rounded-4">

                <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between px-3 py-3">
                    <h4 class="fw-bold mb-0" style="color: #b2935b;">
                        <i class="fas fa-edit me-2"></i>Edit Product
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
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($p['name']); ?>" required class="form-control" />
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Category*</label>
                                            <select name="category_id" class="form-select" required>
                                                <?php
                                                $categories = getAll('categories');
                                                foreach ($categories as $cat) {
                                                    $sel = ($p['category_id'] == $cat['id']) ? 'selected' : '';
                                                    echo "<option value='{$cat['id']}' {$sel}>{$cat['name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($p['description']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-sm border-0 rounded-4 mb-4">
                                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Product Variations (Inventory)</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-variation-btn">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">Leave Size or Color blank for "Standard" variation. <b>Quantity is required.</b></p>

                                    <div id="variation-area">
                                        <?php
                                        if (mysqli_num_rows($variations_query) > 0) {
                                            while ($var = mysqli_fetch_assoc($variations_query)) {
                                        ?>
                                                <div class="row g-2 align-items-center mb-2 variation-item">
                                                    <div class="col-md-4">
                                                        <label class="small text-muted">Size (Optional)</label>
                                                        <input type="text" name="variation_size[]" value="<?= htmlspecialchars($var['size'] == 'Standard' ? '' : $var['size']); ?>" class="form-control" placeholder="Standard">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="small text-muted">Color (Optional)</label>
                                                        <input type="text" name="variation_color[]" value="<?= htmlspecialchars($var['color'] == 'Standard' ? '' : $var['color']); ?>" class="form-control" placeholder="Standard">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small text-muted">Quantity*</label>
                                                        <input type="number" name="variation_qty[]" value="<?= htmlspecialchars($var['quantity']); ?>" class="form-control" min="0" required>
                                                    </div>
                                                    <div class="col-md-1 text-center">
                                                        <label class="small text-white">.</label><br>
                                                        <button type="button" class="btn btn-danger btn-sm remove-var-btn"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </div>
                                        <?php
                                            }
                                        } else {
                                            echo '<p class="text-danger small">No variations found. Please add one.</p>';
                                        }
                                        ?>
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
                                    <div class="mb-3">
                                        <label class="form-label">Current Images</label><br>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php
                                            $images = !empty($p['image']) ? explode(',', $p['image']) : [];
                                            if (!empty($images) && !empty($images[0])) {
                                                foreach ($images as $img) {
                                                    echo "<img src='../$img' class='border rounded shadow-sm' style='width:60px; height:60px; object-fit:cover;' />";
                                                }
                                            } else {
                                                echo "<small class='text-muted'>No images</small>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Replace Images</label>
                                        <input type="file" name="images[]" class="form-control" multiple accept="image/*" />
                                        <small class="text-muted">Uploading new files will replace existing ones.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-sm border-0 rounded-4 mb-4">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="mb-0 fw-semibold" style="color: #b2935b;">Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Price (₱)*</label>
                                        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($p['price']); ?>" required class="form-control" />
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Total Quantity (Auto-calc)</label>
                                        <input type="text" value="<?php echo htmlspecialchars($p['quantity']); ?>" class="form-control bg-light" readonly />
                                        <small class="text-muted">Update variations to change this.</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?php echo ($p['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($p['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="updateProduct" class="btn btn-lg btn-dark w-100" style="background-color: #b2935b; border:none; color:white;">
                                    <i class="fas fa-save me-2"></i>Update Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php
    } else {
        echo '<div class="alert alert-danger mt-4">' . $product['message'] . '</div>';
    }
    ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addBtn = document.querySelector('.add-variation-btn');
        const area = document.getElementById('variation-area');

        // Add New Row
        addBtn.addEventListener('click', function() {
            const item = document.createElement('div');
            item.classList.add('row', 'g-2', 'align-items-center', 'mb-2', 'variation-item');
            item.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="variation_size[]" class="form-control" placeholder="Size (Optional)">
            </div>
            <div class="col-md-4">
                <input type="text" name="variation_color[]" class="form-control" placeholder="Color (Optional)">
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

        // Remove Row
        area.addEventListener('click', function(e) {
            if (e.target.closest('.remove-var-btn')) {
                if (document.querySelectorAll('.variation-item').length > 1) {
                    e.target.closest('.variation-item').remove();
                } else {
                    alert("You need at least one variation.");
                }
            }
        });
    });
</script>

<?php include('include1/footer.php'); ?>