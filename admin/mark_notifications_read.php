<?php
include('../config/dbcon.php');

// Mark all notifications as read
$update = "UPDATE notifications SET is_read = 1 WHERE is_read = 0";
mysqli_query($con, $update);

echo json_encode(['status' => 'success']);
?>