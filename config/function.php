<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'dbcon.php';

// PHPMailer includes (Tama na 'to)
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


// Standard functions (Tama na 'to)
function validate($inputData)
{
    global $con;
    return trim($inputData);
}

function redirect($url, $message = null)
{
    if ($message !== null) {
        $_SESSION['status'] = $message;
    }
    header("Location: $url");
    exit(0);
}

function alertMessage()
{
    if (isset($_SESSION['status'])) {
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
         <h6>' . $_SESSION['status'] . '</h6>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        unset($_SESSION['status']);
    }
}

// ====================================================
// START NG MGA IN-UPGRADE NA FUNCTIONS
// ====================================================

// --- FIX: In-upgrade ang 'insert' para sa Prepared Statements ---
function insert($table_name, $data)
{
    global $con;

    // Basic sanitization para sa table name (pinaka-importante)
    $table = mysqli_real_escape_string($con, validate($table_name));

    $columns = array_keys($data);
    $values = array_values($data);

    $finalColumn = implode(", ", $columns);
    $placeholders = implode(', ', array_fill(0, count($values), '?')); // Gagawa ng: ?, ?, ?

    $query = "INSERT INTO $table ($finalColumn) VALUES ($placeholders)";
    $stmt = mysqli_prepare($con, $query);

    // Dito, i-a-assume natin na lahat ng data ay "string" ('s')
    // Ito ang pinakaligtas na default. Ang MySQL na ang bahalang mag-convert.
    $types = str_repeat('s', count($values));
    mysqli_stmt_bind_param($stmt, $types, ...$values);

    $result = mysqli_stmt_execute($stmt);
    return $result;
}

// --- FIX: In-upgrade ang 'update' para sa Prepared Statements ---
function update($table_name, $id, $data)
{
    global $con;
    $table = mysqli_real_escape_string($con, validate($table_name));
    $id = validate($id);

    $parts = [];
    $values = [];
    $types = ""; // Dito natin ilalagay ang types (e.g., "sssi")

    foreach ($data as $column => $value) {
        $parts[] = "$column = ?"; // Gagawa ng: name = ?, description = ?
        $values[] = $value; // Ipunin ang values
        $types .= "s"; // Idagdag ang 's' for string
    }
    $updateDataString = implode(', ', $parts);

    $query = "UPDATE $table SET $updateDataString WHERE id = ?";

    // Idagdag ang ID sa dulo ng values array
    $values[] = $id;
    $types .= "i"; // 'i' for integer (ang ID)

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    $result = mysqli_stmt_execute($stmt);
    return $result;
}

// --- FIX: In-upgrade ang 'getAll' para mas-safe ---
function getAll($tableName, $status = NULL)
{
    global $con;
    $table = mysqli_real_escape_string($con, validate($tableName));

    // Proteksyon laban sa table name injection
    // Ilagay dito lahat ng tables na *pwede* mong i-getAll
    $allowed_tables = ['categories', 'products', 'admins', 'orders'];
    if (!in_array($table, $allowed_tables)) {
        return false; // Bawal ang table na 'to
    }

    $query = "SELECT * FROM $table"; // Simula ng query

    if ($status == 'status') {
        // Gumamit ng prepared statement kahit dito
        $query .= " WHERE status = ?";
        $stmt = mysqli_prepare($con, $query);
        $active_status = 'active';
        mysqli_stmt_bind_param($stmt, "s", $active_status);
    } else {
        $stmt = mysqli_prepare($con, $query);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
}

// --- FIX: In-upgrade ang 'getByID' para sa Prepared Statements ---
function getByID($tableName, $id)
{
    global $con;
    $table = mysqli_real_escape_string($con, validate($tableName));
    $id = validate($id);

    // Proteksyon laban sa table name injection
    $allowed_tables = ['categories', 'products', 'admins', 'orders'];
    if (!in_array($table, $allowed_tables)) {
        return ['status' => 500, 'message' => 'Invalid Table Access'];
    }

    $query = "SELECT * FROM $table WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id); // 'i' for integer
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $response = [
                'status' => 200,
                'data'  => $row,
                'message' => 'Record Found'
            ];
            return $response;
        } else {
            $response = [
                'status' => 404,
                'message' => 'No Record Found'
            ];
            return $response;
        }
    } else {
        $response = [
            'status' => 500,
            'message' => 'Something Went Wrong'
        ];
        return $response;
    }
}

// --- FIX: In-upgrade ang 'delete' para sa Prepared Statements ---
function delete($tableName, $id)
{
    global $con;
    $table = mysqli_real_escape_string($con, validate($tableName));
    $id = validate($id);

    // Proteksyon laban sa table name injection
    $allowed_tables = ['categories', 'products', 'admins', 'orders'];
    if (!in_array($table, $allowed_tables)) {
        return false;
    }

    $query = "DELETE FROM $table WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id); // 'i' for integer
    $result = mysqli_stmt_execute($stmt);
    return $result;
}

// ====================================================
// END NG MGA IN-UPGRADE NA FUNCTIONS
// ====================================================


function checkParamId($type)
{
    if (isset($_GET[$type])) {
        if ($_GET[$type] != '') {
            return $_GET[$type];
        } else {
            return '<h4>No ' . $type . ' Found</h4>';
        }
    } else {
        return  '<h5>No ' . $type . ' given in URL</h5>';
    }
}

function logoutSession()
{
    unset($_SESSION['loggedIn']);
    unset($_SESSION['loggedUser']);
}

// ====================================================
// UPGRADED DASHBOARD FUNCTIONS (Wala kang binago dito, 100% OK na 'to)
// ====================================================

function getTotalCount($table)
{
    global $con;
    // ... (walang binago)
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM $table WHERE status='active'");
    return mysqli_fetch_assoc($result)['total'] ?? 0;
}

function getLowStockCount()
{
    global $con;
    // ... (walang binago)
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM products WHERE quantity <= 5 AND status='active'");
    return mysqli_fetch_assoc($result)['total'] ?? 0;
}

function getTotalSales()
{
    global $con;
    // ... (walang binago)
    $query = "SELECT SUM(total_amount) as total FROM orders WHERE status='completed' OR status='archived'";
    $result = mysqli_query($con, $query);
    $total = mysqli_fetch_assoc($result)['total'];
    return $total ?? 0;
}

function getTodaysSales()
{
    global $con;
    // ... (walang binago)
    $query = "SELECT SUM(total_amount) as total FROM orders 
               WHERE (status='completed' OR status='archived') 
               AND DATE(created_at) = CURDATE()";
    $result = mysqli_query($con, $query);
    $total = mysqli_fetch_assoc($result)['total'];
    return $total ?? 0;
}

function getPendingCount()
{
    global $con;
    // ... (walang binago)
    $query = "SELECT COUNT(*) as total FROM orders WHERE status='pending'";
    $result = mysqli_query($con, $query);
    return mysqli_fetch_assoc($result)['total'] ?? 0;
}

function getRecentOrdersTable()
{
    global $con;
    // ... (walang binago, 100% OK)
    $query = mysqli_query($con, "SELECT * FROM orders 
                                  WHERE status='pending' 
                                  ORDER BY created_at DESC LIMIT 5");

    if (mysqli_num_rows($query) == 0) return "<p class='text-muted text-center py-3'>No orders needing action. Good job!</p>";

    $html = "<table class='table table-sm align-middle mb-0'><thead><tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead><tbody>";
    while ($row = mysqli_fetch_assoc($query)) {

        $status_text = $row['status'];
        $badge_class = 'bg-secondary';
        if ($status_text == 'pending') {
            $badge_class = 'status-pending text-white';
        }

        $html .= "<tr>
            <td>#{$row['id']}</td>
            <td>" . htmlspecialchars($row['customer_name']) . "</td>
            <td>₱" . number_format($row['total_amount'], 2) . "</td>
            <td><span class='badge {$badge_class}'>{$status_text}</span></td>
        </tr>";
    }
    return $html . "</tbody></table>";
}

function getLowStockList()
{
    global $con;
    // ... (walang binago, 100% OK)
    $query = mysqli_query($con, "SELECT * FROM products 
                                  WHERE quantity <= 5 AND status='active' 
                                  ORDER BY quantity ASC LIMIT 5");

    if (mysqli_num_rows($query) == 0) return "<p class='text-muted text-center py-3'>All stocks are healthy.</p>";

    $html = "<ul class='list-group list-group-flush'>";
    while ($row = mysqli_fetch_assoc($query)) {
        $status = $row['quantity'] == 0 ? "Out of Stock" : "{$row['quantity']} left";
        $color = $row['quantity'] == 0 ? "text-danger" : "text-warning";
        $html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>
            <span>" . htmlspecialchars($row['name']) . "</span><span class='fw-bold $color small'>$status</span>
        </li>";
    }
    return $html . "</ul>";
}

function getWeeklySalesData()
{
    global $con;
    // ... (walang binago, 100% OK)
    $dates = [];
    for ($i = 6; $i >= 0; $i--) {
        $dates[date('Y-m-d', strtotime("-$i days"))] = 0;
    }
    $query = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m-%d') AS sales_date, 
                SUM(total_amount) AS daily_total
              FROM orders
              WHERE 
                (status = 'completed' OR status = 'archived')
                AND created_at >= CURDATE() - INTERVAL 7 DAY
              GROUP BY DATE(created_at)
              ORDER BY sales_date ASC";
    $result = mysqli_query($con, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($dates[$row['sales_date']])) {
                $dates[$row['sales_date']] = $row['daily_total'];
            }
        }
    }
    $labels = [];
    $data = [];
    foreach ($dates as $date => $total) {
        $labels[] = date('M d', strtotime($date));
        $data[] = $total;
    }
    return json_encode(['labels' => $labels, 'data' => $data]);
}

function getTopSellingProducts()
{
    global $con;
    // ... (walang binago, 100% OK)
    $query = "SELECT p.name, SUM(oi.quantity) AS total_sold
              FROM order_items oi
              JOIN products p ON oi.product_id = p.id
              JOIN orders o ON oi.order_id = o.id
              WHERE 
                  (o.status = 'completed' OR o.status = 'archived')
                  AND MONTH(o.created_at) = MONTH(CURDATE())
                  AND YEAR(o.created_at) = YEAR(CURDATE())
              GROUP BY oi.product_id
              ORDER BY total_sold DESC
              LIMIT 5";
    $result = mysqli_query($con, $query);
    if (!$result || mysqli_num_rows($result) == 0) {
        return "<p class='text-muted text-center py-3'>No sales data for this month yet.</p>";
    }
    $html = "<ul class='list-group list-group-flush'>";
    while ($row = mysqli_fetch_assoc($result)) {
        $html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>
            <span>" . htmlspecialchars($row['name']) . "</span>
            <span class='badge bg-gold text-white rounded-pill'>{$row['total_sold']} sold</span>
        </li>";
    }
    return $html . "</ul>";
}

function getSalesByCategoryData()
{
    global $con;
    // ... (walang binago, 100% OK)
    $query = "SELECT c.name AS category_name, SUM(o.total_amount) AS category_sales
              FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              JOIN products p ON oi.product_id = p.id
              JOIN categories c ON p.category_id = c.id
              WHERE 
                  (o.status = 'completed' OR o.status = 'archived')
                  AND MONTH(o.created_at) = MONTH(CURDATE())
                  AND YEAR(o.created_at) = YEAR(CURDATE())
              GROUP BY c.id
              ORDER BY category_sales DESC";
    $result = mysqli_query($con, $query);
    $labels = [];
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['category_name'];
            $data[] = $row['category_sales'];
        }
    }
    return json_encode(['labels' => $labels, 'data' => $data]);
}


// Email function (Tama na 'to)
function sendEmail($to, $customer_name, $subject, $body)
{
    $email_username = 'your-email@gmail.com';
    $email_password = 'your-app-password';
    $email_sender_name = 'AJ&A Shop';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_username;
        $mail->Password   = $email_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom($email_username, $email_sender_name);
        $mail->addAddress($to, $customer_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
// ✅ Idagdag mo 'to sa dulo ng function.php para gumana 'yung dashboard
function getTodaysOrderCount()
{
    global $con;
    $query = "SELECT COUNT(*) as total FROM orders 
              WHERE (status='completed' OR status='archived') 
              AND DATE(created_at) = CURDATE()";
    $result = mysqli_query($con, $query);
    return mysqli_fetch_assoc($result)['total'] ?? 0;
}
