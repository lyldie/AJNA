<?php
require '../config/function.php';

// 1. Get the ID and action from URL
$paraResultId = checkParamId('id');
$action = isset($_GET['action']) ? validate($_GET['action']) : '';

// ✅ Set a default redirect page for any errors
$error_redirect_page = "products.php";

if (is_numeric($paraResultId) && !empty($action)) {
    $productId = validate($paraResultId);

    // 2. Check if the product exists
    $productData = getById('products', $productId);

    if ($productData['status'] == 200) {
        $current_status = $productData['data']['status'];
        $status_value = '';
        $success_message = '';
        $success_redirect_page = ''; // ✅ This will be set by our logic

        // --- ARCHIVE LOGIC ---
        if ($action === 'archive') {
            // ✅ Cleaned up logic: Only archive if it's 'active'
            if ($current_status == 'active') {
                $status_value = 'archived';
                $success_message = "Product Archived Successfully.";
                $success_redirect_page = "products.php"; // Redirect back to the active list
            } else {
                redirect($error_redirect_page, "Product is already archived.");
                exit;
            }
        }
        // --- RESTORE LOGIC ---
        elseif ($action === 'restore') {
            // ✅ Cleaned up logic: Restore if it's NOT 'active'
            if ($current_status != 'active') {
                $status_value = 'active';
                $success_message = "Product Restored Successfully.";
                $success_redirect_page = "products-archived.php"; // Redirect back to the archived list
            } else {
                redirect($error_redirect_page, "Product is already active.");
                exit;
            }
        }
        // --- INVALID ACTION ---
        else {
            redirect($error_redirect_page, "Invalid Status Action Provided.");
            exit;
        }

        // 3. Update the status if valid
        if (!empty($status_value)) {
            $query = "UPDATE products SET status='$status_value' WHERE id='$productId' LIMIT 1";
            $result = mysqli_query($con, $query);

            if ($result) {
                // ✅ Redirect to the correct success page
                redirect($success_redirect_page, $success_message);
            } else {
                redirect($error_redirect_page, "Something Went Wrong During Update.");
            }
        }

    } else {
        redirect($error_redirect_page, $productData['message']);
    }

} else {
    redirect($error_redirect_page, "Invalid Product Request (Missing ID or Action).");
}
?>