<?php
require '../config/function.php';

// 1. Get the ID and action from URL
$paraResultId = checkParamId('id');
$action = isset($_GET['action']) ? validate($_GET['action']) : '';
$error_redirect_page = "admins.php"; // Default page for errors

if (is_numeric($paraResultId) && !empty($action)) {
    $adminId = validate($paraResultId);

    // 2. Check if the admin exists
    $adminData = getById('admins', $adminId);

    if ($adminData['status'] == 200) {
        $current_status = $adminData['data']['status'];
        $status_value = '';
        $success_message = '';
        $success_redirect_page = ''; // Ito ang magsasabi kung saan babalik

        // --- ARCHIVE LOGIC ---
        if ($action === 'archive') {
            // Check kung active bago i-archive
            if ($current_status == 'active') {
                $status_value = 'archived';
                $success_message = "Admin Archived Successfully";
                $success_redirect_page = "admins.php"; // Balik sa active list
            } else {
                redirect($error_redirect_page, "Admin is already archived.");
                exit;
            }
        }
        // --- RESTORE LOGIC ---
        elseif ($action === 'restore') {
            // Check kung HINDI active bago i-restore
            if ($current_status != 'active') {
                $status_value = 'active';
                $success_message = "Admin Restored Successfully";
                $success_redirect_page = "admins-archived.php"; // Balik sa archived list
            } else {
                redirect($error_redirect_page, "Admin is already active.");
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
            $query = "UPDATE admins SET status='$status_value' WHERE id='$adminId' LIMIT 1";
            $result = mysqli_query($con, $query);

            if ($result) {
                redirect($success_redirect_page, $success_message); // Redirect sa tamang page
            } else {
                redirect($error_redirect_page, "Something Went Wrong During Update.");
            }
        }

    } else {
        redirect($error_redirect_page, $adminData['message']);
    }

} else {
    redirect($error_redirect_page, "Invalid Request");
}
?>