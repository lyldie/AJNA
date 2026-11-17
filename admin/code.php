<?php
session_start();
include('../config/function.php');

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header("Location: ../loginform.php");
    exit;
}

// =============================
// ADMIN MANAGEMENT
// =============================
if (isset($_POST['saveAdmin'])) {
    $name     = validate($_POST['name']);
    $email    = validate($_POST['email']);
    $password = validate($_POST['password']);
    $phone    = validate($_POST['phone']);
    $position = validate($_POST['position']);
    $address  = validate($_POST['address']);

    if ($name != '' && $email != '' && $password != '') {
        $stmt_check = mysqli_prepare($con, "SELECT email FROM admins WHERE email = ?");
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        $emailCheck = mysqli_stmt_get_result($stmt_check);

        if ($emailCheck && mysqli_num_rows($emailCheck) > 0) {
            redirect('admins-create.php', 'Email Already Exists. Please try another one');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $data = [
            'name'     => $name,
            'email'    => $email,
            'password' => $hashedPassword,
            'phone'    => $phone,
            'position' => $position,
            'address'  => $address
        ];

        $result = insert('admins', $data);

        if ($result) {
            redirect('admins.php', 'Admin Added Successfully');
        } else {
            redirect('admins-create.php', 'Something Went Wrong');
        }
    } else {
        redirect('admins-create.php', 'Please fill required fields');
    }
}

if (isset($_POST['updateAdmin'])) {
    $adminId = validate($_POST['adminId']);
    $adminData = getById('admins', $adminId);

    if ($adminData['status'] != 200) {
        redirect('admins-edit.php?id=' . $adminId, 'No admin record found');
    }

    $name     = validate($_POST['name']);
    $email    = validate($_POST['email']);
    $password = validate($_POST['password']);
    $phone    = validate($_POST['phone']);
    $position = validate($_POST['position']);
    $address  = validate($_POST['address']);

    $stmt_check_update = mysqli_prepare($con, "SELECT email FROM admins WHERE email = ? AND id != ?");
    mysqli_stmt_bind_param($stmt_check_update, "si", $email, $adminId);
    mysqli_stmt_execute($stmt_check_update);
    $emailCheck = mysqli_stmt_get_result($stmt_check_update);

    if ($emailCheck && mysqli_num_rows($emailCheck) > 0) {
        redirect('admins-edit.php?id=' . $adminId, 'Email Already Exists. Please try another one');
    }

    if ($password != '') {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $hashedPassword = $adminData['data']['password'];
    }

    if ($name != '' && $email != '') {
        $data = [
            'name'     => $name,
            'email'    => $email,
            'password' => $hashedPassword,
            'phone'    => $phone,
            'position' => $position,
            'address'  => $address
        ];

        $result = update('admins', $adminId, $data);

        if ($result) {
            redirect('admins-edit.php?id=' . $adminId, 'Admin Updated Successfully');
        } else {
            redirect('admins-edit.php?id=' . $adminId, 'Something Went Wrong');
        }
    } else {
        redirect('admins-edit.php?id=' . $adminId, 'Please fill required fields');
    }
}


// =============================
// CATEGORY MANAGEMENT
// =============================
if (isset($_POST['saveCategory'])) {
    $name = validate($_POST['name']);
    $description = validate($_POST['description']);
    $parent_id = (isset($_POST['parent_id']) && $_POST['parent_id'] != '') ? validate($_POST['parent_id']) : NULL;
    $default_status = 'active';

    $data = [
        'parent_id' => $parent_id,
        'name' => $name,
        'description' => $description,
        'status' => $default_status
    ];
    $result = insert('categories', $data);
    if ($result) {
        redirect('categories.php', 'Category Added Successfully');
    } else {
        redirect('categories-create.php', 'Something Went Wrong');
    }
}

if (isset($_POST['updateCategory'])) {
    $categoryId = validate($_POST['categoryId']);
    $name = validate($_POST['name']);
    $description = validate($_POST['description']);
    $parent_id = (isset($_POST['parent_id']) && $_POST['parent_id'] != '') ? validate($_POST['parent_id']) : NULL;

    $data = [
        'parent_id' => $parent_id,
        'name' => $name,
        'description' => $description
    ];
    $result = update('categories', $categoryId, $data);
    if ($result) {
        redirect('categories-edit.php?id=' . $categoryId, 'Category Updated Successfully');
    } else {
        redirect('categories-edit.php?id=' . $categoryId, 'Something Went Wrong');
    }
}


// =============================
// PRODUCT MANAGEMENT
// =============================

// CREATE PRODUCT
if (isset($_POST['saveProduct'])) {

    $category_id = validate($_POST['category_id']);
    $name = validate($_POST['name']);
    $description = validate($_POST['description']);
    $price = validate($_POST['price']);
    $status = isset($_POST['status']) ? validate($_POST['status']) : 'active';

    $check_duplicate = mysqli_query($con, "SELECT name FROM products WHERE LOWER(name) = LOWER('$name') AND category_id='$category_id'");
    if (mysqli_num_rows($check_duplicate) > 0) {
        redirect('products-create.php', 'Error: Product Name already exists in this category.');
        return false;
    }

    // --- VARIATION LOGIC START ---
    $var_sizes = isset($_POST['variation_size']) ? $_POST['variation_size'] : [];
    $var_colors = isset($_POST['variation_color']) ? $_POST['variation_color'] : [];
    $var_qtys = isset($_POST['variation_qty']) ? $_POST['variation_qty'] : [];

    $total_quantity = 0;
    $final_sizes_summary = [];
    $final_colors_summary = [];

    // --- IMAGE UPLOAD ---
    $uploadDir = __DIR__ . '/../uploads/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_file_size = 2 * 1024 * 1024;
    $uploadedImages = [];
    $has_error = false;

    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0 && $_FILES['images']['name'][0] != '') {
        foreach ($_FILES['images']['name'] as $key => $imageName) {
            if ($_FILES['images']['error'][$key] === 0) {
                $file_tmp = $_FILES['images']['tmp_name'][$key];
                $file_size = $_FILES['images']['size'][$key];
                $image_ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

                if (!in_array($image_ext, $allowed_extensions)) {
                    redirect('products-create.php', "Error: Invalid file type ($imageName).");
                    $has_error = true;
                    break;
                }
                if ($file_size > $max_file_size) {
                    redirect('products-create.php', "Error: File size ($imageName) exceeds 2MB.");
                    $has_error = true;
                    break;
                }
                $filename = time() . '_' . uniqid() . '.' . $image_ext;
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($file_tmp, $targetFile)) {
                    $uploadedImages[] = 'uploads/products/' . $filename;
                }
            }
        }
    } else {
        redirect('products-create.php', 'Please upload at least one image.');
        $has_error = true;
    }

    if (!$has_error) {
        $finalImages = implode(',', $uploadedImages);

        // Loop once to get total and summaries
        foreach ($var_qtys as $index => $q) {
            $total_quantity += intval($q);
            // Handle optional fields
            $s_val = !empty($var_sizes[$index]) ? validate($var_sizes[$index]) : 'Standard';
            $c_val = !empty($var_colors[$index]) ? validate($var_colors[$index]) : 'Standard';

            $final_sizes_summary[] = $s_val;
            $final_colors_summary[] = $c_val;
        }

        $size_string = implode(',', array_unique($final_sizes_summary));
        $color_string = implode(',', array_unique($final_colors_summary));

        $data = [
            'category_id' => $category_id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'quantity' => $total_quantity,
            'color' => $color_string,
            'size' => $size_string,
            'image' => $finalImages,
            'status' => $status
        ];

        $result = insert('products', $data);

        if ($result) {
            $product_id = mysqli_insert_id($con);
            $stmt_var = mysqli_prepare($con, "INSERT INTO product_variations (product_id, size, color, quantity) VALUES (?, ?, ?, ?)");

            if ($stmt_var) {
                foreach ($var_qtys as $index => $q_val) {
                    $s_val = !empty($var_sizes[$index]) ? validate($var_sizes[$index]) : 'Standard';
                    $c_val = !empty($var_colors[$index]) ? validate($var_colors[$index]) : 'Standard';
                    mysqli_stmt_bind_param($stmt_var, "issi", $product_id, $s_val, $c_val, $q_val);
                    mysqli_stmt_execute($stmt_var);
                }
                mysqli_stmt_close($stmt_var);
            }
            redirect('products.php', 'Product Created Successfully');
        } else {
            redirect('products-create.php', 'Something Went Wrong');
        }
    }
}


// UPDATE PRODUCT
if (isset($_POST['updateProduct'])) {

    $productId = validate($_POST['productId']);
    $productData = getById('products', $productId);

    if ($productData['status'] != 200) {
        redirect('products.php', $productData['message']);
        return false;
    }

    $existing = $productData['data'];
    $old_image_paths_string = $existing['image'];

    $category_id = validate($_POST['category_id']);
    $name = validate($_POST['name']);
    $price = validate($_POST['price']);
    $description = validate($_POST['description']);
    $status = isset($_POST['status']) ? validate($_POST['status']) : 'active';

    $var_sizes = isset($_POST['variation_size']) ? $_POST['variation_size'] : [];
    $var_colors = isset($_POST['variation_color']) ? $_POST['variation_color'] : [];
    $var_qtys = isset($_POST['variation_qty']) ? $_POST['variation_qty'] : [];

    $total_quantity = 0;
    $final_sizes_summary = [];
    $final_colors_summary = [];

    // --- IMAGE UPLOAD LOGIC ---
    $finalImages = $old_image_paths_string;
    $new_images_uploaded = false;
    $has_error = false;

    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0 && $_FILES['images']['name'][0] != '') {
        $new_images_uploaded = true;
        $uploadDir = __DIR__ . '/../uploads/products/';
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_file_size = 2 * 1024 * 1024;
        $uploadedImages = [];

        foreach ($_FILES['images']['name'] as $key => $imageName) {
            if ($_FILES['images']['error'][$key] === 0) {
                $file_tmp = $_FILES['images']['tmp_name'][$key];
                $file_size = $_FILES['images']['size'][$key];
                $image_ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

                if (!in_array($image_ext, $allowed_extensions)) {
                    redirect('products-edit.php?id=' . $productId, "Error: Invalid file type ($imageName).");
                    $has_error = true;
                    break;
                }
                if ($file_size > $max_file_size) {
                    redirect('products-edit.php?id=' . $productId, "Error: File size ($imageName) exceeds 2MB.");
                    $has_error = true;
                    break;
                }

                $filename = time() . '_' . uniqid() . '.' . $image_ext;
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($file_tmp, $targetFile)) {
                    $uploadedImages[] = 'uploads/products/' . $filename;
                }
            }
        }
        if (!$has_error) {
            $finalImages = implode(',', $uploadedImages);
        }
    }

    if (!$has_error) {
        // Calculate totals and summaries with optional field logic
        foreach ($var_qtys as $index => $q) {
            $total_quantity += intval($q);
            $s_val = !empty($var_sizes[$index]) ? validate($var_sizes[$index]) : 'Standard';
            $c_val = !empty($var_colors[$index]) ? validate($var_colors[$index]) : 'Standard';

            $final_sizes_summary[] = $s_val;
            $final_colors_summary[] = $c_val;
        }

        $size_string = implode(',', array_unique($final_sizes_summary));
        $color_string = implode(',', array_unique($final_colors_summary));

        $data = [
            'category_id' => $category_id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'quantity' => $total_quantity,
            'color' => $color_string,
            'size' => $size_string,
            'image' => $finalImages,
            'status' => $status
        ];

        $result = update('products', $productId, $data);

        if ($result) {
            // Delete and Re-insert Variations
            mysqli_query($con, "DELETE FROM product_variations WHERE product_id = '$productId'");
            $stmt_var = mysqli_prepare($con, "INSERT INTO product_variations (product_id, size, color, quantity) VALUES (?, ?, ?, ?)");
            if ($stmt_var) {
                foreach ($var_qtys as $index => $q_val) {
                    $s_val = !empty($var_sizes[$index]) ? validate($var_sizes[$index]) : 'Standard';
                    $c_val = !empty($var_colors[$index]) ? validate($var_colors[$index]) : 'Standard';
                    mysqli_stmt_bind_param($stmt_var, "issi", $productId, $s_val, $c_val, $q_val);
                    mysqli_stmt_execute($stmt_var);
                }
                mysqli_stmt_close($stmt_var);
            }

            if ($new_images_uploaded && !empty($old_image_paths_string)) {
                $old_images = explode(',', $old_image_paths_string);
                foreach ($old_images as $old_img_path) {
                    $full_old_path = __DIR__ . '/../' . trim($old_img_path);
                    if (file_exists($full_old_path)) {
                        unlink($full_old_path);
                    }
                }
            }
            redirect('products-edit.php?id=' . $productId, 'Product Updated Successfully');
        } else {
            redirect('products-edit.php?id=' . $productId, 'Something Went Wrong');
        }
    }
}

// =============================
// STOCK ADJUSTMENT (LOGS)
// =============================
if (isset($_POST['saveAdjustment'])) {
    $product_id = validate($_POST['product_id']);
    $variation_id = (isset($_POST['variation_id']) && $_POST['variation_id'] != '') ? validate($_POST['variation_id']) : NULL;
    $quantity = validate($_POST['quantity']);
    $reason = validate($_POST['reason']);
    $admin_name = $_SESSION['loggedUser']['name'] ?? 'Admin';

    if ($quantity <= 0 || empty($reason)) {
        redirect('products.php', 'Error: Please enter a valid quantity and reason.');
        return false;
    }

    $updateSuccess = false;

    if ($variation_id != NULL) {
        $varData = mysqli_fetch_assoc(mysqli_query($con, "SELECT quantity FROM product_variations WHERE id='$variation_id'"));
        $currentQty = intval($varData['quantity']);
        if ($currentQty < $quantity) {
            redirect('products.php', "Error: Not enough stock in variation.");
            return false;
        }
        $newQty = $currentQty - $quantity;
        $updateVar = mysqli_query($con, "UPDATE product_variations SET quantity='$newQty' WHERE id='$variation_id'");
        $updateMain = mysqli_query($con, "UPDATE products SET quantity = (SELECT SUM(quantity) FROM product_variations WHERE product_id='$product_id') WHERE id='$product_id'");
        if ($updateVar && $updateMain) $updateSuccess = true;
    } else {
        $prodData = mysqli_fetch_assoc(mysqli_query($con, "SELECT quantity FROM products WHERE id='$product_id'"));
        $currentQty = intval($prodData['quantity']);
        if ($currentQty < $quantity) {
            redirect('products.php', "Error: Not enough stock.");
            return false;
        }
        $newQty = $currentQty - $quantity;
        $updateMain = mysqli_query($con, "UPDATE products SET quantity='$newQty' WHERE id='$product_id'");
        if ($updateMain) $updateSuccess = true;
    }

    if ($updateSuccess) {
        $varSql = ($variation_id == NULL) ? "NULL" : "'$variation_id'";
        $logQuery = "INSERT INTO inventory_logs (product_id, variation_id, admin_name, adjustment_type, quantity, reason) 
                     VALUES ('$product_id', $varSql, '$admin_name', 'Deduct (Manual)', '$quantity', '$reason')";
        mysqli_query($con, $logQuery);
        redirect('products.php', 'Stock Adjusted Successfully!');
    } else {
        redirect('products.php', 'Something went wrong.');
    }
}

// =============================
// LOGS ARCHIVING LOGIC
// =============================
if (isset($_POST['archiveLog'])) {
    $logId = validate($_POST['log_id']);
    $query = "UPDATE inventory_logs SET status='archived' WHERE id='$logId'";
    if (mysqli_query($con, $query)) redirect('admin-logs.php', 'Log Moved to Archive');
}

if (isset($_POST['restoreLog'])) {
    $logId = validate($_POST['log_id']);
    $query = "UPDATE inventory_logs SET status='active' WHERE id='$logId'";
    if (mysqli_query($con, $query)) redirect('admin-logs-archived.php', 'Log Restored Successfully');
}
