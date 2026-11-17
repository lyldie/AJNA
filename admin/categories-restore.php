<?php
require '../config/function.php';

// 1. Get the ID
$paraResultId = checkParamId('id');

// 2. Get the ACTION (archive or restore)
$action = isset($_GET['action']) ? validate($_GET['action']) : ''; 

if (is_numeric($paraResultId) && !empty($action)) {
    $categoryId = validate($paraResultId);
    $categoryData = getById('categories', $categoryId);

    if ($categoryData['status'] == 200) {
        
        $current_status = $categoryData['data']['status'];
        $status_value = '';
        $success_message = '';
        $redirect_page = ''; // ✅ NEW: Variable to control redirect
        
        // --- ARCHIVE LOGIC ---
        if ($action === 'archive') {
            if ($current_status == 'active') { 
                $status_value = 'archived';
                $success_message = "Category Archived Successfully";
                $redirect_page = "categories.php"; // Go back to the active list
            } else {
                redirect("categories.php", "Category is already archived.");
                return false;
            }
        } 
        
        // --- RESTORE LOGIC ---
        elseif ($action === 'restore') {
            if ($current_status != 'active') { 
                $status_value = 'active';
                $success_message = "Category Restored Successfully";
                $redirect_page = "categories-archived.php"; // Go back to the archived list
            } else {
                redirect("categories.php", "Category is already active.");
                return false;
            }
        } 
        
        // --- UNKNOWN ACTION ---
        else {
            redirect("categories.php", "Invalid Status Action Provided.");
            return false;
        }

        // Execute the database update
        if (!empty($status_value)) {
            $query = "UPDATE categories SET status='$status_value' WHERE id='$categoryId' LIMIT 1";
            $result = mysqli_query($con, $query);

            if ($result) {
                redirect($redirect_page, $success_message); // ✅ Use the new redirect
            } else {
                redirect("categories.php", "Something Went Wrong.");
            }
        }

    } else {
        redirect("categories.php", $categoryData['message']);
    }
} else {
    redirect("categories.php", "Invalid Category Request (Missing ID or Action).");
}
?>