<?php
session_start();
require 'config/function.php';

if (isset($_POST['loginBtn'])) {
    $email = validate($_POST['email']);
    $password = validate($_POST['password']);

    if ($email != '' && $password != '') {
        
        // Use Prepared Statement for Security
        $stmt = mysqli_prepare($con, "SELECT * FROM admins WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $hashedPassword = $row['password'];

            // 1. Check if account is archived
            if ($row['status'] === 'archived') {
                redirect('loginform.php', 'Your account has been archived. Please contact the administrator.');
                exit();
            }

            // 2. Check password
            if (!password_verify($password, $hashedPassword)) {
                redirect('loginform.php', 'Invalid Email or Password');
            } else {
                
                // ===========================================
                // ✨ NEW: SAVE LOGIN HISTORY (AUDIT TRAIL) ✨
                // ===========================================
                
                // Get User IP Address
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $role = $row['position']; 
                $user_id = $row['id'];
                $user_email = $row['email'];

                // Insert into login_logs table
                $log_query = "INSERT INTO login_logs (user_id, email, role, ip_address, status) VALUES (?, ?, ?, ?, 'Success')";
                $stmt_log = mysqli_prepare($con, $log_query);
                
                if ($stmt_log) {
                    mysqli_stmt_bind_param($stmt_log, "isss", $user_id, $user_email, $role, $ip_address);
                    mysqli_stmt_execute($stmt_log);
                }
                // ===========================================


                // Set Session Variables
                $_SESSION['loggedIn'] = true; 
                $_SESSION['loggedUser'] = [
                    'user_id' => $row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'position' => $row['position'],
                    'address' => $row['address']
                ];
                $_SESSION['login_status'] = 'Logged in Successfully';
                
                redirect('admin/admin-dashboard.php');
            }
        } else {
            redirect('loginform.php', 'Invalid Email or Password');
        }
    } else {
        redirect('loginform.php', 'Please fill required fields.');
    }
}
?>