<?php
ob_start(); // Prevent headers error
session_start();
include('../config/function.php'); 

$action = null;
if (isset($_GET['action'])) $action = $_GET['action'];
elseif (isset($_GET['act'])) $action = $_GET['act'];

$order_id_raw = null; 
if (isset($_GET['id'])) $order_id_raw = $_GET['id'];
elseif (isset($_GET['order_id'])) $order_id_raw = $_GET['order_id'];

$order_id = validate($order_id_raw);
$action   = validate($action);

if (!is_numeric($order_id) || empty($action)) {
    redirect('orders.php', 'Invalid request.');
    exit;
}

$validActions = ['approve', 'ship', 'complete', 'cancel', 'archive', 'restore'];
if (!in_array($action, $validActions)) {
    redirect('orders.php', 'Invalid action.');
    exit;
}

$redirect = 'orders.php';
$msg = '';

/* -------------------------------------------------------
    EMAIL TEMPLATE HELPER
------------------------------------------------------- */
function getEmailTemplate($content) {
    return "
    <div style='background-color: #f4f6f9; padding: 40px 0; font-family: Arial, sans-serif; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);'>
            
            <div style='background-color: #1e1e1e; padding: 30px; text-align: center; border-bottom: 5px solid #b2935b;'>
                <h1 style='margin: 0; color: #ffffff; font-family: \"Times New Roman\", serif; letter-spacing: 2px; font-size: 28px;'>AJ&A</h1>
                <p style='margin: 5px 0 0; color: #b2935b; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>Luxury & Quality</p>
            </div>

            <div style='padding: 40px 30px;'>
                $content
            </div>

            <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eee; color: #999; font-size: 12px;'>
                <p style='margin: 0;'>Thank you for shopping with AJ&A.</p>
                <p style='margin: 5px 0 0;'>This is an automated message. Please do not reply.</p>
            </div>
        </div>
    </div>
    ";
}

/* -------------------------------------------------------
    ACTION HANDLING
------------------------------------------------------- */
switch ($action) {

    // STEP 1: APPROVE (DEDUCT STOCKS HERE)
    case 'approve':
        $orderData = getById('orders', $order_id); 

        if ($orderData['status'] == 200 && $orderData['data']['status'] == 'pending') {
            $order = $orderData['data'];
            
            // 1. Get Items
            $stmt_get_items = mysqli_prepare($con, "SELECT * FROM order_items WHERE order_id=?");
            mysqli_stmt_bind_param($stmt_get_items, "i", $order_id);
            mysqli_stmt_execute($stmt_get_items);
            $items_q = mysqli_stmt_get_result($stmt_get_items);
            mysqli_stmt_close($stmt_get_items);

            if(!$items_q) {
                redirect($redirect, 'Failed to fetch items.');
                exit;
            }
            
            $items = mysqli_fetch_all($items_q, MYSQLI_ASSOC);
            $stock_ok = true;
            $error_product = '';

            // 2. CHECK STOCK AVAILABILITY (Total & Variation)
            foreach ($items as $item) {
                // Check Main Product Stock
                $product_data = getById('products', $item['product_id']); 
                if ($item['quantity'] > $product_data['data']['quantity']) {
                    $stock_ok = false; 
                    $error_product = $product_data['data']['name'] . " (Total Stock)";
                    break; 
                }

                // Check Specific Variation Stock (If size/color exists)
                if(!empty($item['size']) || !empty($item['color'])) {
                    $check_size = $item['size'];
                    $check_color = $item['color'];
                    
                    // Note: Use prepared statement for safety
                    $v_query = mysqli_query($con, "SELECT quantity FROM product_variations WHERE product_id='{$item['product_id']}' AND size='$check_size' AND color='$check_color'");
                    
                    if(mysqli_num_rows($v_query) > 0) {
                        $v_row = mysqli_fetch_assoc($v_query);
                        if($item['quantity'] > $v_row['quantity']) {
                            $stock_ok = false;
                            $error_product = $product_data['data']['name'] . " (" . $check_size . " - " . $check_color . ")";
                            break;
                        }
                    } else {
                        // Variation not found (baka dinelete na)
                        $stock_ok = false;
                        $error_product = $product_data['data']['name'] . " (Variation Unavailable)";
                        break;
                    }
                }
            }
            
            if ($stock_ok) {
                // 3. DEDUCT STOCKS
                
                // A. Deduct Main Product Total
                $stmt_deduct = mysqli_prepare($con, "UPDATE products SET quantity = quantity - ? WHERE id = ?");
                
                // B. Deduct Variation (Prepare statement once)
                $stmt_deduct_var = mysqli_prepare($con, "UPDATE product_variations SET quantity = quantity - ? WHERE product_id = ? AND size = ? AND color = ?");

                foreach ($items as $item) {
                    // Update Main
                    mysqli_stmt_bind_param($stmt_deduct, "ii", $item['quantity'], $item['product_id']);
                    mysqli_stmt_execute($stmt_deduct);

                    // Update Variation (If applicable)
                    if(!empty($item['size']) || !empty($item['color'])) {
                        mysqli_stmt_bind_param($stmt_deduct_var, "iiss", $item['quantity'], $item['product_id'], $item['size'], $item['color']);
                        mysqli_stmt_execute($stmt_deduct_var);
                    }
                }
                mysqli_stmt_close($stmt_deduct); 
                mysqli_stmt_close($stmt_deduct_var);

                // 4. Update Status
                $stmt_approve = mysqli_prepare($con, "UPDATE orders SET status='approved' WHERE id=?");
                mysqli_stmt_bind_param($stmt_approve, "i", $order_id);
                
                if (mysqli_stmt_execute($stmt_approve)) {
                    $msg = 'Order approved successfully.';

                    // EMAIL LOGIC
                    $customer_name  = $order['customer_name'];
                    $customer_email = $order['customer_email'];
                    $total_amount   = $order['total_amount'];

                    // Invoice Table
                    $invoiceHTML = '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px;">';
                    $invoiceHTML .= '<tr style="background-color: #f8f9fa; color: #555;">
                                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">PRODUCT</th>
                                        <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">QTY</th>
                                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">TOTAL</th>
                                     </tr>';

                    $stmt_inv = mysqli_prepare($con, "SELECT oi.*, p.name as p_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE order_id=?");
                    mysqli_stmt_bind_param($stmt_inv, "i", $order_id);
                    mysqli_stmt_execute($stmt_inv);
                    $res_inv = mysqli_stmt_get_result($stmt_inv);

                    while($row = mysqli_fetch_assoc($res_inv)){
                        $sub = $row['price'] * $row['quantity'];
                        $details = '<strong>' . htmlspecialchars($row['p_name']) . '</strong>';
                        if(!empty($row['size'])) $details .= "<br><span style='color:#888; font-size:12px;'>Size: ".htmlspecialchars($row['size'])."</span>";
                        if(!empty($row['color'])) $details .= "<br><span style='color:#888; font-size:12px;'>Color: ".htmlspecialchars($row['color'])."</span>";

                        $invoiceHTML .= '<tr>
                                            <td style="padding: 12px; border-bottom: 1px solid #eee;">'.$details.'</td>
                                            <td style="padding: 12px; border-bottom: 1px solid #eee; text-align: center;">'.$row['quantity'].'</td>
                                            <td style="padding: 12px; border-bottom: 1px solid #eee; text-align: right;">₱'.number_format($sub, 2).'</td>
                                         </tr>';
                    }
                    mysqli_stmt_close($stmt_inv);

                    $invoiceHTML .= '<tr>
                                        <td colspan="2" style="padding: 15px 12px; text-align: right; font-weight: bold; font-size: 16px;">GRAND TOTAL</td>
                                        <td style="padding: 15px 12px; text-align: right; font-weight: bold; color: #b2935b; font-size: 18px;">₱'.number_format($total_amount, 2).'</td>
                                     </tr>';
                    $invoiceHTML .= '</table>';

                    $content = "
                        <h2 style='color: #1e1e1e; margin-top: 0;'>Order Confirmed!</h2>
                        <p style='font-size: 16px; line-height: 1.6; color: #555;'>
                            Hi <strong>$customer_name</strong>,<br><br>
                            Great news! Your order <strong>#$order_id</strong> has been successfully confirmed. We are now preparing your items for shipment.
                        </p>
                        <div style='background-color: #fffbf0; border-left: 4px solid #b2935b; padding: 15px; margin: 20px 0;'>
                            <p style='margin: 0; color: #b2935b; font-weight: bold;'>Order Summary</p>
                        </div>
                        $invoiceHTML
                        <br>
                    ";
                    
                    if($order['payment_method'] == 'Cash on Delivery') {
                        $content .= "
                        <div style='background-color: #e8f5e9; color: #1b5e20; padding: 15px; border-radius: 8px; font-size: 14px; text-align: center;'>
                            <strong>Payment Note:</strong> Please prepare <strong>₱" . number_format($total_amount, 2) . "</strong> for our courier upon delivery.
                        </div>";
                    }

                    $content .= "<p style='text-align: center; margin-top: 30px; color: #888;'>We will notify you again once your parcel is out for delivery.</p>";
                    $finalBody = getEmailTemplate($content);
                    sendEmail($customer_email, $customer_name, "Order Confirmed #$order_id", $finalBody);

                } else {
                    $msg = 'Database error: ' . mysqli_stmt_error($stmt_approve);
                }
                mysqli_stmt_close($stmt_approve);
                
            } else {
                $msg = "Order cannot be approved. Not enough stock for: $error_product";
                $redirect = 'orders-view.php?id=' . $order_id; 
            }
            
        } else {
            $msg = "Order is no longer pending.";
        }
        break;


    // STEP 2: SHIP
    case 'ship':
        $orderData = getById('orders', $order_id);

        if ($orderData['status'] == 200 && $orderData['data']['status'] == 'approved') {
            $order = $orderData['data'];
            
            $stmt_ship = mysqli_prepare($con, "UPDATE orders SET status='shipped' WHERE id=?");
            mysqli_stmt_bind_param($stmt_ship, "i", $order_id);
            
            if (mysqli_stmt_execute($stmt_ship)) {
                $msg = 'Order marked as shipped.';
                
                $customer_name  = $order['customer_name'];
                $customer_email = $order['customer_email'];
                $total_amount   = number_format($order['total_amount'], 2);
                $payment_method = $order['payment_method'];
                $order_display_id = str_pad($order_id, 6, '0', STR_PAD_LEFT); 

                // PREMIUM SHIPPED EMAIL CONTENT
                $content = "
                    <div style='text-align: center; padding: 10px 0;'>
                        <img src='https://cdn-icons-png.flaticon.com/512/2830/2830305.png' style='width: 80px; margin-bottom: 20px; opacity: 0.9;' alt='On the way'>
                        
                        <h2 style='color: #1e1e1e; margin: 0 0 10px 0; font-family: \"Times New Roman\", serif; letter-spacing: 0.5px;'>Your Order is On The Way!</h2>
                        
                        <p style='font-size: 16px; line-height: 1.6; color: #555; margin-bottom: 30px;'>
                            Hello <strong>$customer_name</strong>,<br>
                            Great news! Your package has been handed over to our courier and is making its way to your delivery address.
                        </p>

                        <div style='background-color: #f8f9fa; border: 1px solid #eee; border-radius: 12px; padding: 20px; text-align: left; max-width: 350px; margin: 0 auto; box-shadow: 0 2px 5px rgba(0,0,0,0.03);'>
                            <table style='width: 100%; font-size: 14px;'>
                                <tr>
                                    <td style='color: #777; padding-bottom: 8px;'>Order No:</td>
                                    <td style='text-align: right; font-weight: bold; color: #333;'>#$order_display_id</td>
                                </tr>
                                <tr>
                                    <td style='color: #777; padding-bottom: 8px;'>Payment:</td>
                                    <td style='text-align: right; font-weight: bold; color: #333;'>$payment_method</td>
                                </tr>
                                <tr style='border-top: 1px solid #ddd;'>
                                    <td style='color: #777; padding-top: 8px;'>Amount Due:</td>
                                    <td style='text-align: right; font-weight: bold; color: #b2935b; font-size: 16px; padding-top: 8px;'>₱$total_amount</td>
                                </tr>
                            </table>
                        </div>

                        <div style='margin-top: 30px; color: #666; font-size: 14px;'>
                            <p style='margin: 5px 0;'>🚚 Our courier will contact you shortly.</p>
                            <p style='margin: 5px 0;'>📱 Please keep your phone lines open.</p>
                        </div>
                    </div>
                ";
                
                $finalBody = getEmailTemplate($content);
                sendEmail($customer_email, $customer_name, "On The Way: Order #$order_display_id", $finalBody);

            } else {
                $msg = 'Database error: ' . mysqli_stmt_error($stmt_ship);
            }
            mysqli_stmt_close($stmt_ship);

        } else {
            $msg = "Order must be approved first.";
        }
        break;  

    // STEP 3: COMPLETE
    case 'complete':
        $orderData = getById('orders', $order_id);
        if ($orderData['status'] == 200) {
            if (strtolower(trim($orderData['data']['status'])) == 'shipped') {
                $stmt_complete = mysqli_prepare($con, "UPDATE orders SET status='completed' WHERE id=?");
                mysqli_stmt_bind_param($stmt_complete, "i", $order_id);
                if (mysqli_stmt_execute($stmt_complete)) $msg = 'Order completed.';
                else $msg = 'Database error.';
                mysqli_stmt_close($stmt_complete);
            } else $msg = "Order must be shipped first.";
        } else $msg = "Order not found.";
        break;

    // CANCEL (RESTORE STOCKS HERE)
    case 'cancel':
        $orderData = getById('orders', $order_id); 
        if ($orderData['status'] == 200 && in_array($orderData['data']['status'], ['approved', 'shipped'])) {
            $stmt_items = mysqli_prepare($con, "SELECT * FROM order_items WHERE order_id=?");
            mysqli_stmt_bind_param($stmt_items, "i", $order_id);
            mysqli_stmt_execute($stmt_items);
            $items = mysqli_fetch_all(mysqli_stmt_get_result($stmt_items), MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_items);
            
            // A. Restore Main Product Stock
            $stmt_restock = mysqli_prepare($con, "UPDATE products SET quantity = quantity + ? WHERE id = ?");
            // B. Restore Variation Stock
            $stmt_restock_var = mysqli_prepare($con, "UPDATE product_variations SET quantity = quantity + ? WHERE product_id = ? AND size = ? AND color = ?");

            foreach ($items as $item) {
                // Restore Main
                mysqli_stmt_bind_param($stmt_restock, "ii", $item['quantity'], $item['product_id']);
                mysqli_stmt_execute($stmt_restock);

                // Restore Variation
                if(!empty($item['size']) || !empty($item['color'])) {
                    mysqli_stmt_bind_param($stmt_restock_var, "iiss", $item['quantity'], $item['product_id'], $item['size'], $item['color']);
                    mysqli_stmt_execute($stmt_restock_var);
                }
            }
            mysqli_stmt_close($stmt_restock);
            mysqli_stmt_close($stmt_restock_var);

            $msg = 'Order cancelled. Stock restored (Total & Variations).';
        } else $msg = 'Order cancelled.';
        
        $stmt_cancel = mysqli_prepare($con, "UPDATE orders SET status='cancelled' WHERE id=?");
        mysqli_stmt_bind_param($stmt_cancel, "i", $order_id);
        mysqli_stmt_execute($stmt_cancel);
        mysqli_stmt_close($stmt_cancel);
        break;

    // ARCHIVE
    case 'archive':
        $stmt_archive = mysqli_prepare($con, "UPDATE orders SET status='archived' WHERE id=?");
        mysqli_stmt_bind_param($stmt_archive, "i", $order_id);
        $msg = mysqli_stmt_execute($stmt_archive) ? 'Order archived.' : 'Database error.';
        mysqli_stmt_close($stmt_archive);
        break;

    // RESTORE (From Archive to Pending - NO STOCK CHANGE NEEDED usually unless it was approved before)
    // Assuming simple restore to pending flow
    case 'restore':
        $stmt_restore = mysqli_prepare($con, "UPDATE orders SET status='pending' WHERE id=?");
        mysqli_stmt_bind_param($stmt_restore, "i", $order_id);
        $msg = mysqli_stmt_execute($stmt_restore) ? 'Order restored.' : 'Database error.';
        mysqli_stmt_close($stmt_restore);
        $redirect = 'orders-archived.php';
        break;

    default: $msg = 'Invalid action.'; break;
}

redirect($redirect, $msg);
exit;
ob_end_flush();
?>