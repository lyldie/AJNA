<?php
session_start();
include_once('config/dbcon.php');
include_once('config/function.php'); 

if (!isset($_POST['place_order']) || empty($_SESSION['cart'])) {
    header("Location: index.php"); 
    exit;
}

$payment_method = validate($_POST['payment_method']);
$pay_ref_number = null; // Default null

// ✅ VALIDATE REF NUMBER FOR GCASH
if ($payment_method === 'GCash') {
    if (!isset($_POST['pay_ref_number']) || empty($_POST['pay_ref_number'])) {
        $_SESSION['error_message'] = "Reference number is required for GCash.";
        header("Location: checkout_cart.php"); 
        exit(); 
    }
    // Sanitized Text (Ensure only 4 chars)
    $pay_ref_number = validate($_POST['pay_ref_number']);
    if(strlen($pay_ref_number) > 4) $pay_ref_number = substr($pay_ref_number, -4); // Safe fallback
}

$customer_name = validate($_POST['name']);
$customer_email = validate($_POST['customer_email']); 
$customer_phone = validate($_POST['phone']);
$customer_address = validate($_POST['address']);

// Calculate Total
$total_price = 0;
foreach ($_SESSION['cart'] as $item) { 
    $total_price += $item['price'] * $item['quantity']; 
}

$status = 'pending'; 

// ✅ INSERT ORDER (Added pay_ref_number, Removed payment_proof upload)
$ref_sql = ($pay_ref_number) ? "'$pay_ref_number'" : "NULL";

$order_query = "INSERT INTO orders 
    (customer_name, customer_email, customer_phone, customer_address, payment_method, pay_ref_number, payment_proof, total_amount, status, created_at) 
    VALUES 
    ('$customer_name', '$customer_email', '$customer_phone', '$customer_address', '$payment_method', $ref_sql, NULL, '$total_price', '$status', NOW())";

if (mysqli_query($con, $order_query)) {
    $order_id = mysqli_insert_id($con); 

    foreach ($_SESSION['cart'] as $cart_id => $item) 
    {
        $pid = $item['id']; 
        $qty = $item['quantity']; 
        $price = $item['price']; 
        
        $size = isset($item['size']) ? validate($item['size']) : '';
        $color = isset($item['color']) ? validate($item['color']) : '';
        
        $subtotal = $price * $qty;
        
        $size_sql = ($size !== '') ? "'$size'" : "NULL";
        $color_sql = ($color !== '') ? "'$color'" : "NULL";

        $query_items = "INSERT INTO order_items (order_id, product_id, quantity, price, size, color, subtotal) 
                        VALUES 
                        ('$order_id', '$pid', '$qty', '$price', $size_sql, $color_sql, '$subtotal')";
        
        mysqli_query($con, $query_items);
    }
    
    $notif_message = "New order #$order_id placed by $customer_name.";
    $notif_link = "orders-view.php?id=$order_id";
    $safe_msg = mysqli_real_escape_string($con, $notif_message);
    
    mysqli_query($con, "INSERT INTO notifications (message, type, link, is_read, created_at) 
                          VALUES ('$safe_msg', 'order', '$notif_link', 0, NOW())");

    unset($_SESSION['cart']);
    
    header("Location: thankyou.php?order_id=" . $order_id);
    exit;
    
} else {
    $_SESSION['error_message'] = "Database error: " . mysqli_error($con);
    header("Location: checkout_cart.php"); 
    exit();
}
?>