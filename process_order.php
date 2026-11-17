<?php
session_start();
include_once('config/dbcon.php');
include_once('config/function.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['product_id'])) {
    header("Location: index.php");
    exit;
}

$product_id = validate($_POST['product_id']);
$quantity   = validate($_POST['quantity']);
$size       = isset($_POST['size']) ? validate($_POST['size']) : '';
$color      = isset($_POST['color']) ? validate($_POST['color']) : '';

$product_data = getById('products', $product_id);
if ($product_data['status'] != 200 || $product_data['data']['status'] != 'active') {
    $_SESSION['error_message'] = "Product is not available.";
    header("Location: index.php");
    exit();
}
$product = $product_data['data'];

if ($quantity > $product['quantity']) {
    $_SESSION['error_message'] = "Sorry, only " . $product['quantity'] . " of " . $product['name'] . " are in stock.";
    header("Location: checkout.php?id=" . $product_id);
    exit();
}

$unit_price = $product['price'];
$total_price = $unit_price * $quantity;

// ✅ PAYMENT METHOD & REF NUMBER
$payment_method = validate($_POST['payment_method']);
$pay_ref_number = null;

if ($payment_method === 'GCash') {
    if (!isset($_POST['pay_ref_number']) || empty($_POST['pay_ref_number'])) {
        $_SESSION['error_message'] = "Reference number is required for GCash.";
        header("Location: checkout.php?id=" . $product_id);
        exit();
    }
    $pay_ref_number = validate($_POST['pay_ref_number']);
    if (strlen($pay_ref_number) > 4) $pay_ref_number = substr($pay_ref_number, -4);
}

// ❌ Removed File Upload Logic

$customer_name = validate($_POST['customer_name']);
$customer_email = validate($_POST['customer_email']);
$customer_phone = validate($_POST['customer_phone']);
$customer_address = validate($_POST['customer_address']);

$status = 'pending';

// ✅ INSERT ORDER (with ref number, null proof)
$ref_sql = ($pay_ref_number) ? "'$pay_ref_number'" : "NULL";

$order_query = "INSERT INTO orders 
    (customer_name, customer_email, customer_phone, customer_address, payment_method, pay_ref_number, payment_proof, total_amount, status, created_at) 
    VALUES 
    ('$customer_name', '$customer_email', '$customer_phone', '$customer_address', '$payment_method', $ref_sql, NULL, '$total_price', '$status', NOW())";

if (mysqli_query($con, $order_query)) {
    $order_id = mysqli_insert_id($con);

    $size_sql = ($size !== '') ? "'$size'" : "NULL";
    $color_sql = ($color !== '') ? "'$color'" : "NULL";
    $subtotal = $total_price;

    $query_items = "INSERT INTO order_items (order_id, product_id, quantity, price, size, color, subtotal) 
                    VALUES 
                    ('$order_id', '$product_id', '$quantity', '$unit_price', $size_sql, $color_sql, '$subtotal')";

    mysqli_query($con, $query_items);

    $notif_message = "New order #$order_id placed by $customer_name.";
    $notif_link = "orders-view.php?id=$order_id";
    $safe_msg = mysqli_real_escape_string($con, $notif_message);

    mysqli_query($con, "INSERT INTO notifications (message, type, link, is_read, created_at) 
                          VALUES ('$safe_msg', 'order', '$notif_link', 0, NOW())");

    header("Location: thankyou.php?order_id=" . $order_id);
    exit;
} else {
    $_SESSION['error_message'] = "Database error: " . mysqli_error($con);
    header("Location: checkout.php?id=" . $product_id);
    exit();
}
