<?php
include('../config/dbcon.php');

// 1. Get Count of UNREAD Only (Para sa Red Badge)
$countQuery = "SELECT COUNT(*) as unread_count FROM notifications WHERE is_read = 0";
$countResult = mysqli_query($con, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$count = $countRow['unread_count'];

// 2. Get Recent Notifications (READ or UNREAD) - Limit 5 para sa dropdown
// Ito ang fix para hindi "nawawala" ang items pagka-click mo.
$listQuery = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5";
$listResult = mysqli_query($con, $listQuery);

$notifications = [];
if (mysqli_num_rows($listResult) > 0) {
    while ($row = mysqli_fetch_assoc($listResult)) {
        
        // Ensure Link is Valid
        if (empty($row['link']) || $row['link'] == '#') {
            if ($row['type'] == 'order') $row['link'] = 'orders.php';
            elseif ($row['type'] == 'stock') $row['link'] = 'products.php';
            elseif ($row['type'] == 'payment_verify') $row['link'] = 'orders.php';
            else $row['link'] = 'notifications.php';
        }

        $row['formatted_date'] = date('M d, h:i A', strtotime($row['created_at']));
        $notifications[] = $row;
    }
}

// 3. Get Low Stock (Same logic)
$lowStockQuery = "SELECT * FROM products WHERE quantity <= 5 AND status = 'active' LIMIT 5";
$lowStockResult = mysqli_query($con, $lowStockQuery);
$lowStock = [];
while ($row = mysqli_fetch_assoc($lowStockResult)) {
    $lowStock[] = $row;
}

// 4. Return Data
header('Content-Type: application/json');
echo json_encode([
    'count' => $count, // Unread Count lang
    'orders' => $notifications, // Recent 5 items (Mixed read/unread)
    'lowStock' => $lowStock
]);
?>