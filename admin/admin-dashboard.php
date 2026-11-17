<?php 
include('include1/header.php'); 
?>

<div class="container-fluid px-4">
    
    <div class="d-flex justify-content-between align-items-center my-4 dashboard-header">
        <div>
            <h1 class="fw-bold text-dark display-6">Dashboard</h1>
            <p class="text-muted mb-0">Overview of inventory & sales performance.</p>
        </div>
        <div>
             <a href="reports.php" class="btn btn-dark btn-lg shadow-sm rounded-pill px-4">
                <i class="fas fa-chart-line me-2"></i>View Reports
             </a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 card-stat border-start-gold">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small fw-bold mb-1 tracking-wide">Total Sales</div>
                            <div class="h2 mb-0 fw-bold text-dark">₱<?= number_format(getTotalSales(), 2); ?></div>
                        </div>
                        <div class="icon-box bg-gold-light rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-coins fa-lg text-gold"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 card-stat border-start-success">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small fw-bold mb-1 tracking-wide">Today's Sales</div>
                            <div class="h2 mb-0 fw-bold text-dark">₱<?= number_format(getTodaysSales(), 2); ?></div>
                            <div class="small text-success fw-semibold mt-1">
                                <i class="fas fa-check-circle me-1"></i><?= getTodaysOrderCount(); ?> Orders
                            </div>
                        </div>
                        <div class="icon-box bg-success-light rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-calendar-day fa-lg text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 card-stat border-start-warning">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small fw-bold mb-1 tracking-wide">Pending Orders</div>
                            <div class="h2 mb-0 fw-bold text-dark"><?= getPendingCount(); ?></div>
                        </div>
                        <div class="icon-box bg-warning-light rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-hourglass-half fa-lg text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 card-stat border-start-danger">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small fw-bold mb-1 tracking-wide">Low Stock Items</div>
                            <div class="h2 mb-0 fw-bold text-dark"><?= getLowStockCount(); ?></div>
                        </div>
                        <div class="icon-box bg-danger-light rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-exclamation-triangle fa-lg text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 h-100" style="border-top: 5px solid #b2935b !important;">
                <div class="card-header bg-white border-bottom py-3 ps-4">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-area me-2 text-gold"></i>Weekly Sales Trend</h5>
                </div>
                <div class="card-body ps-4 pe-4 pb-4">
                    <canvas id="weeklySalesChart" style="height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100" style="border-top: 5px solid #b2935b !important;">
                <div class="card-header bg-white border-bottom py-3 ps-4">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-pie me-2 text-gold"></i>Category Share</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="width: 100%; max-width: 300px;">
                        <canvas id="categoryPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 h-100" style="border-top: 5px solid #ffc107 !important;">
                <div class="card-header bg-white border-bottom py-3 ps-4 pe-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-receipt me-2 text-warning"></i>Recent Orders</h5>
                    <a href="orders.php" class="btn btn-sm btn-light border rounded-pill px-3">View All</a>
                </div>
                <div class="card-body p-0 table-responsive custom-scrollbar" style="max-height: 350px; overflow-y: auto;">
                    <?= getRecentOrdersTable(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100" style="border-top: 5px solid #6610f2 !important;">
                <div class="card-header bg-white border-bottom py-3 ps-4 pe-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2" style="color: #6610f2;"></i>Audit Trail</h5>
                    <a href="admin-logs.php" class="btn btn-sm btn-light border rounded-pill px-3">View Logs</a>
                </div>
                <div class="card-body p-0 custom-scrollbar" style="max-height: 350px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        <?php
                        // Fetch last 5 logs directly
                        $log_q = mysqli_query($con, "SELECT l.*, p.name FROM inventory_logs l JOIN products p ON l.product_id = p.id WHERE l.status='active' ORDER BY l.created_at DESC LIMIT 5");
                        if(mysqli_num_rows($log_q) > 0){
                            while($log = mysqli_fetch_assoc($log_q)){
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($log['name']); ?></div>
                                        <small class="text-muted d-block">By: <?= htmlspecialchars($log['admin_name']); ?></small>
                                        <small class="text-danger fst-italic">"<?= htmlspecialchars($log['reason']); ?>"</small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">-<?= $log['quantity']; ?></span>
                                </li>
                                <?php
                            }
                        } else {
                            echo '<div class="p-4 text-center text-muted small">No recent adjustments.</div>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card shadow-sm border-0 rounded-4 h-100" style="border-top: 5px solid #dc3545 !important;">
                <div class="card-header bg-white border-bottom py-3 ps-4 pe-4">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Low Stock</h5>
                </div>
                <div class="card-body p-0 custom-scrollbar" style="max-height: 350px; overflow-y: auto;">
                    <?= getLowStockList(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Weekly Sales Data
$chartData = getWeeklySalesData();
$chartDataJson = json_decode($chartData, true);
$chartLabels = json_encode($chartDataJson['labels']);
$chartSales = json_encode($chartDataJson['data']);

// Category Sales Data
$catChartData = getSalesByCategoryData();
$catChartDataJson = json_decode($catChartData, true);
$catChartLabels = json_encode($catChartDataJson['labels']);
$catChartSales = json_encode($catChartDataJson['data']);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // Common Options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { font: { family: "'Poppins', sans-serif", size: 12 } }
                }
            }
        };

        // 1. Weekly Sales Line Chart
        const ctxLine = document.getElementById('weeklySalesChart');
        if (ctxLine) {
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: <?php echo $chartLabels; ?>,
                    datasets: [{
                        label: 'Sales Revenue',
                        data: <?php echo $chartSales; ?>,
                        fill: true,
                        backgroundColor: 'rgba(178, 147, 91, 0.1)', 
                        borderColor: '#b2935b', 
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#b2935b',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        tension: 0.4
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: '#f0f0f0' },
                            ticks: {
                                callback: function(value) { return '₱' + value; },
                                font: { family: "'Poppins', sans-serif" }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: "'Poppins', sans-serif" } }
                        }
                    }
                }
            });
        }

        // 2. Sales by Category Pie Chart
        const ctxPie = document.getElementById('categoryPieChart');
        if (ctxPie) {
            new Chart(ctxPie, {
                type: 'doughnut', 
                data: {
                    labels: <?php echo $catChartLabels; ?>,
                    datasets: [{
                        data: <?php echo $catChartSales; ?>,
                        backgroundColor: [
                            '#b2935b', // Gold
                            '#2c3e50', // Dark Blue/Gray
                            '#d7b98e', // Light Gold
                            '#95a5a6', // Gray
                            '#e74c3c'  // Accent Red
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    ...commonOptions,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    }
                }
            });
        }
    });
</script>

<style>
/* --- Global --- */
body {
    background-color: #f8f9fa !important;
    font-family: 'Poppins', sans-serif;
}
.tracking-wide { letter-spacing: 0.05em; }

/* --- Stat Cards --- */
.card-stat {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: #fff;
}
.card-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}

.border-start-gold { border-left: 4px solid #b2935b !important; }
.border-start-success { border-left: 4px solid #198754 !important; }
.border-start-warning { border-left: 4px solid #ffc107 !important; }
.border-start-danger { border-left: 4px solid #dc3545 !important; }

.icon-box {
    width: 50px; height: 50px;
}
.bg-gold-light { background-color: rgba(178, 147, 91, 0.15) !important; }
.bg-success-light { background-color: rgba(25, 135, 84, 0.15) !important; }
.bg-warning-light { background-color: rgba(255, 193, 7, 0.15) !important; }
.bg-danger-light { background-color: rgba(220, 53, 69, 0.15) !important; }

.text-gold { color: #b2935b !important; }

/* --- Tables & Lists --- */
.table { margin-bottom: 0; }
.table thead th {
    background-color: #fff !important;
    color: #666 !important;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
    border-bottom: 2px solid #eee;
}
.table tbody td {
    padding: 1rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f8f9fa;
    font-size: 0.9rem;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #f0f0f0;
    padding: 15px;
    background: transparent;
}
.list-group-item:last-child { border-bottom: none; }

/* --- Scrollbar --- */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #b2935b; }
</style>

<?php include('include1/footer.php'); ?>