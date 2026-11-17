<?php  
require '../config/function.php';

$paraResultId = checkParamId('id');
if (is_numeric($paraResultId)) {
    $categoryId = validate($paraResultId);

    $category = getById('categories', $categoryId);
    if ($category['status'] == 200) {
        $updateQuery = "UPDATE categories SET status='archived' WHERE id='$categoryId' LIMIT 1";
        $result = mysqli_query($con, $updateQuery);

        if ($result) {
            redirect('categories.php', 'Category Archived Successfully');
        } else {
            redirect('categories.php', 'Something Went Wrong');
        }
    } else {  
        redirect('categories.php', $category['message']);
    }    
} else {
    redirect('categories.php', 'Something Went Wrong');
}
?>
