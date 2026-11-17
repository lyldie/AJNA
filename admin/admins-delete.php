<?php  

require '../config/function.php';

$paraResultId = checkParamId('id');
$action = $_GET['action'] ?? '';   // archive or restore

if (is_numeric($paraResultId) && in_array($action, ['archive', 'restore'])) 
{
    $adminId = validate($paraResultId);

    $adminData = getById('admins', $adminId);
    if ($adminData['status'] == 200) 
    {
        // Decide new status
        $newStatus = ($action === 'archive') ? 'archived' : 'active';

        $updateRes = update('admins', $adminData['data']['id'], [
            'status' => $newStatus
        ]);

        if ($updateRes) {
            $msg = ($action === 'archive') ? 'Admin Archived Successfully' : 'Admin Restored Successfully';
            redirect('admins.php', $msg);
        } else {
            redirect('admins.php', 'Something Went Wrong');
        }
    } 
    else 
    {
        redirect('admins.php', $adminData['message']);
    }
} 
else 
{
    redirect('admins.php', 'Invalid Request');
}
