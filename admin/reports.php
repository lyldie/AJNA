<?php 
include('include1/header.php'); 

// --- BACKEND LOGIC ---
$selected_month = isset($_GET['month']) ? validate($_GET['month']) : date('m');
$selected_year = isset($_GET['year']) ? validate($_GET['year']) : date('Y');
$report_generated = false;

// Initialize variables
$total_revenue = 0;
$total_items_sold = 0;
$itemized_data = [];

if(isset($_GET['generate_report'])) {
    $report_generated = true;

    // --- MAIN QUERY: ITEM-LEVEL DETAIL (Target ni Panel) ---
    // Kumukuha ito ng data per ITEM na nabenta, hindi per order lang.
    $query = "SELECT 
                o.id AS order_id,
                o.created_at,
                p.name AS product_name,
                c.name AS category_name,
                oi.size,
                oi.color,
                oi.quantity,
                oi.price,
                (oi.quantity * oi.price) AS subtotal
              FROM order_items oi
              JOIN orders o ON oi.order_id = o.id
              JOIN products p ON oi.product_id = p.id
              JOIN categories c ON p.category_id = c.id
              WHERE (o.status = 'completed' OR o.status = 'archived')
              AND MONTH(o.created_at) = '$selected_month'
              AND YEAR(o.created_at) = '$selected_year'
              ORDER BY o.created_at DESC";

    $result = mysqli_query($con, $query);

    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $itemized_data[] = $row;
            $total_revenue += $row['subtotal'];
            $total_items_sold += $row['quantity'];
        }
    }
}
?>

<div class="container-fluid px-4">
    
    <div class="d-flex justify-content-between align-items-center my-4 d-print-none">
        <div>
            <h1 class="fw-bold text-dark display-6">Sales & Inventory Report</h1>
            <p class="text-muted mb-0">Detailed breakdown of sold items and product variations.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none" style="background: #fff;">
        <div class="card-body p-4">
            <form action="reports.php" method="GET">
                <h5 class="fw-bold text-gold mb-3"><i class="fas fa-filter me-2"></i>Select Period</h5>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Month</label>
                        <select name="month" class="form-select form-select-lg shadow-none bg-light text-dark">
                            <?php
                            for ($m = 1; $m <= 12; $m++) {
                                $month_name = date('F', mktime(0, 0, 0, $m, 10));
                                $month_value = str_pad($m, 2, '0', STR_PAD_LEFT);
                                $is_selected = ($month_value == $selected_month) ? 'selected' : '';
                                echo "<option value='$month_value' $is_selected>$month_name</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Year</label>
                        <select name="year" class="form-select form-select-lg shadow-none bg-light text-dark">
                            <?php 
                            $current_year = date('Y');
                            for ($y = $current_year; $y >= $current_year - 5; $y--) {
                                $is_selected = ($y == $selected_year) ? 'selected' : '';
                                echo "<option value='$y' $is_selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="generate_report" class="btn btn-primary w-100 btn-lg shadow-sm">
                            <i class="fas fa-file-invoice me-2"></i>Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($report_generated): ?>
        
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5" id="report-content" style="border-top: 5px solid #b2935b;">
            
            <div class="card-header bg-white border-bottom py-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold text-dark mb-1" style="font-family: 'Playfair Display', serif;">Monthly Sales Breakdown</h3>
                    <p class="text-muted mb-0">Period: <span class="fw-bold text-gold"><?= date('F', mktime(0, 0, 0, $selected_month, 10)) . " " . $selected_year; ?></span></p>
                </div>
                <div class="d-print-none">
                    <button onclick="window.print()" class="btn btn-outline-dark rounded-pill fw-semibold px-4">
                        <i class="fas fa-print me-2"></i>Print Report
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-gold text-white shadow-sm d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small fw-bold opacity-75 mb-1">Total Revenue</div>
                                <h2 class="fw-bold mb-0">₱<?= number_format($total_revenue, 2); ?></h2>
                            </div>
                            <i class="fas fa-coins fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-dark text-white shadow-sm d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small fw-bold opacity-75 mb-1">Total Items Sold</div>
                                <h2 class="fw-bold mb-0"><?= number_format($total_items_sold); ?> <small class="fs-6 fw-normal">units</small></h2>
                            </div>
                            <i class="fas fa-boxes fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Detailed Product Sales</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 table-striped">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th class="py-3 ps-3" width="10%">Sales ID</th>
                                <th class="py-3" width="15%">Date Sold</th>
                                <th class="py-3" width="15%">Category</th>
                                <th class="py-3" width="25%">Product Name</th>
                                <th class="py-3" width="20%">Specific Details (Var)</th>
                                <th class="py-3 text-center" width="5%">Qty</th>
                                <th class="py-3 pe-3 text-end" width="10%">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($itemized_data)): ?>
                                <?php foreach($itemized_data as $row): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-gold">#<?= $row['order_id']; ?></td>
                                        <td><?= date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td><span class="badge bg-light text-dark border fw-normal"><?= htmlspecialchars($row['category_name']); ?></span></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['product_name']); ?></td>
                                        <td>
                                            <?php if($row['size']): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary" style="font-size:0.75rem">Size: <?= $row['size']; ?></span>
                                            <?php endif; ?>
                                            <?php if($row['color']): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary" style="font-size:0.75rem">Color: <?= $row['color']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center fw-bold"><?= $row['quantity']; ?></td>
                                        <td class="pe-3 text-end">₱<?= number_format($row['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No sales records found for this period.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if(!empty($itemized_data)): ?>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="5" class="text-end py-3">GRAND TOTAL:</td>
                                <td class="text-center py-3"><?= number_format($total_items_sold); ?></td>
                                <td class="text-end py-3 pe-3 text-gold">₱<?= number_format($total_revenue, 2); ?></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>

                <div class="d-none d-print-block mt-5 pt-5">
                    <div class="row mt-5">
                        <div class="col-5">
                            <div class="border-top border-dark border-2 pt-2 text-center">
                                <p class="mb-0 fw-bold"><?= $_SESSION['loggedUser']['name'] ?? 'Administrator'; ?></p>
                                <small class="text-muted">Prepared By</small>
                            </div>
                        </div>
                        <div class="col-2"></div>
                        <div class="col-5">
                            <div class="border-top border-dark border-2 pt-2 text-center">
                                <p class="mb-0 fw-bold">&nbsp;</p>
                                <small class="text-muted">Noted By</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>

</div>

<style>
    /* --- Colors --- */
    .text-gold { color: #b2935b !important; }
    .bg-gold { background-color: #b2935b !important; }
    
    /* --- Table Styling (IMPROVED) --- */
    .table {
        border-collapse: separate; 
        border-spacing: 0; 
    }

    .table thead th { 
        background-color: #f8f9fa !important; /* Light Gray Header */
        color: #444 !important; 
        text-transform: uppercase; 
        font-size: 0.85rem; /* Increased Header Size */
        letter-spacing: 1px; 
        font-weight: 700;
        padding: 1.2rem 1rem; /* Taller Header */
        border-bottom: 2px solid #ddd !important;
    }

    .table tbody td { 
        padding: 1.2rem 1rem; /* Mas malaking padding para "huminga" ang rows */
        vertical-align: middle; 
        font-size: 1.05rem; /* Increased Body Text Size (from 0.9rem to 1.05rem) */
        color: #333;
        border-bottom: 1px solid #eee;
    }

    /* Hover Effect */
    .table-hover tbody tr:hover { 
        background-color: #fffbf0 !important; /* Gold tint on hover */
        transform: scale(1.01); /* Slight Zoom effect */
        transition: all 0.2s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        position: relative;
        z-index: 10;
    }

    /* Badges */
    .badge {
        padding: 0.5em 0.8em;
        font-size: 0.85rem; /* Larger Badges */
    }

    /* Form Elements */
    .form-select, .btn { border-radius: 10px !important; }
    .btn-primary { background-color: #b2935b !important; border-color: #b2935b !important; }
    .btn-primary:hover { background-color: #8e7343 !important; }

    /* Print Styles */
    @media print {
        .sb-topnav, #layoutSidenav_nav, .d-print-none, .btn { display: none !important; }
        body, #layoutSidenav_content, .container-fluid { margin: 0 !important; padding: 0 !important; background-color: #fff !important; }
        .card { box-shadow: none !important; border: none !important; }
        .text-white { color: #000 !important; }
        .bg-dark { background-color: #eee !important; color: #000 !important; border: 1px solid #ccc !important; }
        .bg-gold { background-color: #eee !important; color: #000 !important; border: 1px solid #ccc !important; }
        .table { width: 100% !important; border-collapse: collapse !important; }
        .table th, .table td { border: 1px solid #ddd !important; padding: 10px !important; font-size: 12pt !important; }
        .d-print-block { display: block !important; }
        .badge { border: 1px solid #000 !important; color: #000 !important; font-weight: bold; }
    }
</style>

<?php include('include1/footer.php'); ?>