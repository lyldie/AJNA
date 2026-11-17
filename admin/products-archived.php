<?php include('include1/header.php'); ?>

<div class="container-fluid px-4">
    <div class="card mt-4 border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between px-3 py-3">
            <h4 class="fw-bold mb-0" style="color: #b2935b;">
                <i class="fas fa-archive me-2"></i>Archived Products
            </h4>
            
            <a href="products.php" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">
                <i class="fas fa-shopping-bag me-2"></i>View Active Products
            </a>
        </div>
        
        <div class="card-body">
            <?php alertMessage(); ?>

            <div class="table-responsive mt-3"> 
                <?php
                // ✅ UPDATED: Query now *only* gets 'archived' (or not 'active') products
                $products = mysqli_query($con, "SELECT * FROM products WHERE status != 'active' ORDER BY id DESC");

                if (!$products) {
                    echo '<h4> Something went wrong</h4>';
                    return false;
                }
                if (mysqli_num_rows($products) > 0) {
                ?>
                <table class="table align-middle text-center shadow-sm" style="border-collapse: separate; border-spacing: 0 8px;">
                    <thead class="text-white" style="background: #1e1e1e;">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th style="min-width:200px">Action</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.95rem;">
                        <?php foreach ($products as $item): ?>
                        <tr>
                            <td><strong>#<?= $item['id']; ?></strong></td>
                            <td>
                                <img src="../<?= htmlspecialchars($item['image']); ?>" 
                                     style="width:70px;height:70px;object-fit:cover;border-radius: 8px;" 
                                     alt="<?= htmlspecialchars($item['name']); ?>">
                            </td>
                            <td><?= htmlspecialchars($item['name']); ?></td>
                            <td><?= htmlspecialchars($item['description']); ?></td>
                            <td>
                                <?php if ($item['quantity'] > 0): ?>
                                    <span class="badge status-in-stock"><?= $item['quantity']; ?> In Stock</span>
                                <?php else: ?>
                                    <span class="badge status-out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge status-archived">Archived</span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="products-edit.php?id=<?= $item['id']; ?>" class="btn btn-approve"><i class="fas fa-edit me-1"></i>Edit</a>
                                    
                                    <a href="products-status.php?id=<?= $item['id']; ?>&action=restore" class="btn btn-complete"><i class="fas fa-undo me-1"></i>Restore</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php 
                } else {
                    echo '<h5 class="mb-0 text-muted text-center py-4">No Archived Products Found</h5>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th { padding: 12px; letter-spacing: 0.3px; }
    .table tbody tr { background-color: #fdfbf7; border-radius: 8px; transition: all 0.2s ease-in-out; }
    .table tbody tr:hover { background-color: #f9f4ec !important; transform: scale(1.005); }
    tbody tr:hover td { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .status-active, .status-in-stock { background-color: #28a745 !important; color: white !important; }
    .status-archived, .status-out-of-stock { background-color: #dc3545 !important; color: white !important; }
    .btn-approve, .btn-cancel, .btn-complete { border: none; border-radius: 8px; padding: 5px 12px; transition: all 0.2s ease-in-out; color: white; }
    .btn-approve { background-color: #28a745; }
    .btn-approve:hover { background-color: #218838; }
    .btn-cancel { background-color: #dc3545; }
    .btn-cancel:hover { background-color: #b02a37; }
    .btn-complete { background-color: #0d6efd; }
    .btn-complete:hover { background-color: #0b5ed7; }
</style>

<?php include('include1/footer.php'); ?>