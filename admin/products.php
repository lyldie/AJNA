<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="fw-bold text-dark display-6">Manage Products</h1>
            <p class="text-muted mb-0">Inventory and product catalog.</p>
        </div>
        <div class="btn-group shadow-sm rounded-pill" role="group">
            <a href="products-create.php" class="btn btn-dark px-4 fw-semibold">
                <i class="fas fa-plus me-2"></i>Add Product
            </a>
            <a href="products-archived.php" class="btn btn-outline-secondary px-4 fw-semibold">
                <i class="fas fa-archive me-2"></i>View Archived
            </a>
        </div>
    </div>
    
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" style="border-top: 5px solid #b2935b;">
        <div class="card-body p-0">
            <?php alertMessage(); ?>

            <div class="table-responsive"> 
                <?php
                $products = mysqli_query($con, "SELECT * FROM products WHERE status = 'active' ORDER BY id DESC");

                if (!$products) {
                    echo '<div class="p-5 text-center"><h4>Something went wrong.</h4></div>';
                    return false;
                }
                if (mysqli_num_rows($products) > 0) {
                ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4 py-3">Product</th>
                            <th class="py-3">Category / Desc</th>
                            <th class="py-3 text-center">Inventory</th> 
                            <th class="py-3 text-center">Status</th>
                            <th class="text-center pe-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $item): ?>
                            <?php
                                $image_string = $item['image'];
                                $image_list = explode(',', $image_string);
                                $first_image = trim($image_list[0]);

                                // --- FETCH VARIATIONS DATA FOR MODALS ---
                                $pid = $item['id'];
                                $var_query = mysqli_query($con, "SELECT * FROM product_variations WHERE product_id = '$pid'");
                                $variations = [];
                                while($v = mysqli_fetch_assoc($var_query)){
                                    $variations[] = $v;
                                }
                                $variations_json = htmlspecialchars(json_encode($variations), ENT_QUOTES, 'UTF-8');
                                // ---------------------------------------
                            ?>
                            <tr class="transition-all hover-row">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            <img src="../<?= htmlspecialchars($first_image); ?>" 
                                                 style="width:50px; height:50px; object-fit:cover; border-radius: 8px; border: 1px solid #eee;" 
                                                 alt="Img">
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold text-dark"><?= htmlspecialchars($item['name']); ?></span>
                                            <small class="text-muted">₱<?= number_format($item['price'], 2); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td width="25%" class="text-muted small">
                                    <?= htmlspecialchars(substr($item['description'], 0, 40)) . '...'; ?>
                                </td>
                                
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <h5 class="mb-1 fw-bold <?= $item['quantity'] == 0 ? 'text-danger' : 'text-dark' ?>">
                                            <?= $item['quantity']; ?>
                                        </h5>
                                        <small class="text-muted mb-2">Total Items</small>
                                        
                                        <?php if(!empty($variations)): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                                    style="font-size: 0.75rem;"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#stockModal"
                                                    data-prodname="<?= htmlspecialchars($item['name']) ?>"
                                                    data-variations="<?= $variations_json ?>">
                                                <i class="fas fa-list-ul me-1"></i> View Breakdown
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">No Variations</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success px-3">
                                        Active
                                    </span>
                                </td>
                                
                                <td class="text-center pe-4">
                                    <div class="btn-group">
                                        <a href="products-edit.php?id=<?= $item['id']; ?>" class="btn btn-sm btn-light border text-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <button type="button" class="btn btn-sm btn-light border text-warning" 
                                                title="Adjust Stock (Logs)" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#adjustStockModal"
                                                data-prod-id="<?= $item['id']; ?>"
                                                data-prod-name="<?= htmlspecialchars($item['name']); ?>"
                                                data-vars="<?= $variations_json; ?>">
                                            <i class="fas fa-clipboard-list"></i>
                                        </button>

                                        <a href="products-status.php?id=<?= $item['id']; ?>&action=archive" class="btn btn-sm btn-light border text-danger" title="Archive" onclick="return confirm('Archive this product?');">
                                            <i class="fas fa-archive"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php 
                } else {
                    echo '<div class="p-5 text-center text-muted"><h5>No Active Products Found</h5></div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold" id="stockModalLabel">Stock Breakdown</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0">
        <p class="text-muted mb-3">Product: <strong id="modalProdName" class="text-dark"></strong></p>
        <div class="table-responsive border rounded-3">
            <table class="table table-sm table-striped mb-0 text-center">
                <thead class="bg-light">
                    <tr>
                        <th class="py-2">Size</th>
                        <th class="py-2">Color</th>
                        <th class="py-2">Quantity</th>
                        <th class="py-2">Status</th>
                    </tr>
                </thead>
                <tbody id="modalTableBody"></tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0">
      <div class="modal-header bg-warning bg-opacity-10">
        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-boxes me-2"></i>Adjust Stock (Deduct)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="code.php" method="POST">
          <div class="modal-body p-4">
            <input type="hidden" name="product_id" id="adj_prod_id">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Product:</label>
                <input type="text" id="adj_prod_name" class="form-control-plaintext fw-bold text-dark ps-2" style="font-size: 1.1em;" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase">Target Inventory</label>
                
                <select name="variation_id" id="adj_var_select" class="form-select">
                    <option value="">-- Choose Size/Color --</option>
                </select>

                <div id="adj_no_var_msg" class="alert alert-light border d-none mt-2">
                    <small class="text-muted fw-bold">
                        <i class="fas fa-info-circle me-1"></i> No variations found. Deducting from Main Inventory.
                    </small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Quantity to Remove <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="1" required placeholder="e.g. 1">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase">Reason for Deduction <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="2" required placeholder="e.g. Damaged during delivery, Expired, Lost item"></textarea>
            </div>
            
            <div class="alert alert-warning small border-0 bg-warning bg-opacity-10 text-dark">
                <i class="fas fa-info-circle me-1"></i> This will be recorded in <strong>Inventory Logs</strong> and will <strong class="text-danger">NOT</strong> count as a Sale.
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="saveAdjustment" class="btn btn-warning rounded-pill px-4 fw-bold">Confirm Deduction</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Existing View Breakdown Modal Logic ---
    const stockModal = document.getElementById('stockModal');
    if (stockModal) {
        stockModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const prodName = button.getAttribute('data-prodname');
            let variations = [];
            try { variations = JSON.parse(button.getAttribute('data-variations')); } catch (e) { console.error("Parse error"); }

            document.getElementById('modalProdName').textContent = prodName;
            const tbody = document.getElementById('modalTableBody');
            tbody.innerHTML = '';

            if(variations.length > 0) {
                variations.forEach(v => {
                    let qty = parseInt(v.quantity);
                    let statusBadge = qty > 0 ? '<span class="badge bg-success bg-opacity-10 text-success">In Stock</span>' : '<span class="badge bg-danger bg-opacity-10 text-danger">Out of Stock</span>';
                    tbody.innerHTML += `<tr><td class="align-middle fw-semibold">${v.size}</td><td class="align-middle">${v.color}</td><td class="align-middle fw-bold ${qty < 5 ? 'text-danger' : ''}">${qty}</td><td class="align-middle">${statusBadge}</td></tr>`;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-muted py-3">No variations found.</td></tr>';
            }
        });
    }

    // --- ✨ NEW: Adjust Stock Modal Logic (SMART) ✨ ---
    const adjustModal = document.getElementById('adjustStockModal');
    if (adjustModal) {
        adjustModal.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            const prodId = btn.getAttribute('data-prod-id');
            const prodName = btn.getAttribute('data-prod-name');
            let variations = [];
            try { variations = JSON.parse(btn.getAttribute('data-vars')); } catch (e) { }

            // Populate Fields
            adjustModal.querySelector('#adj_prod_id').value = prodId;
            adjustModal.querySelector('#adj_prod_name').value = prodName;

            const select = adjustModal.querySelector('#adj_var_select');
            const msg = adjustModal.querySelector('#adj_no_var_msg');

            // Reset UI
            select.innerHTML = '<option value="">-- Choose Size/Color --</option>';
            
            // Logic: May Variations ba o Wala?
            if(variations.length > 0){
                // Show Dropdown, Hide Message
                select.classList.remove('d-none');
                msg.classList.add('d-none');
                select.setAttribute('required', 'required'); // Require selection

                variations.forEach(v => {
                    let label = `Size: ${v.size} | Color: ${v.color} (Stock: ${v.quantity})`;
                    let opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = label;
                    select.appendChild(opt);
                });
            } else {
                // Hide Dropdown, Show Message
                select.classList.add('d-none');
                msg.classList.remove('d-none');
                select.removeAttribute('required'); // NOT Required (kasi wala naman pipiliin)
            }
        });
    }
});
</script>

<style>
    .table thead th { font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase; }
    .btn-group .btn { margin: 0 2px; border-radius: 6px !important; }
    .hover-row:hover { background-color: #fcfcfc; }
    
    /* Optional: Custom scrollbar for modals if needed */
    .modal-body { overflow-y: auto; }
</style>

<?php include('include1/footer.php'); ?>