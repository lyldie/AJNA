<?php
session_start();
include('config/dbcon.php'); 
include('config/function.php'); // Siguraduhin na kasama 'to

if (isset($_POST['product_id']) && isset($_POST['quantity'])) {

    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // --- FIX #1: HINARANG ANG NEGATIVE/ZERO QUANTITY ---
    // Kung ang quantity ay 0 o mas mababa, itigil na agad.
    if ($product_id <= 0 || $quantity <= 0) {
        $_SESSION['cart_message'] = "Invalid product or quantity.";
        header("Location: index.php");
        exit;
    }
    // --- END NG FIX #1 ---

    $size = isset($_POST['size']) ? validate($_POST['size']) : '';
    $color = isset($_POST['color']) ? validate($_POST['color']) : '';

    // --- FIX #2: GINAMIT ANG PREPARED STATEMENTS (SQL INJECTION-PROOF) ---
    // Inalis natin ang $query = "SELECT... $product_id"
    
    // 1. Ihanda (Prepare) ang query na may '?'
    $stmt = mysqli_prepare($con, "SELECT * FROM products WHERE id = ? AND status = 'active' LIMIT 1");
    
    // 2. I-bind ang variable. "i" ibig sabihin integer.
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    
    // 3. I-execute
    mysqli_stmt_execute($stmt);
    
    // 4. Kunin ang resulta
    $result = mysqli_stmt_get_result($stmt);
    
    // --- END NG FIX #2 ---

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);

        // Ito 'yung unang stock check (kung ang bago pa lang ay sobra na)
        if ($quantity > $product['quantity']) {
            $_SESSION['cart_message'] = "Cannot add {$quantity}. Only {$product['quantity']} of {$product['name']} is available.";
            header("Location: index.php");
            exit;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $cart_id = md5($product_id . '-' . $size . '-' . $color);
        
        $firstImage = trim(explode(',', $product['image'])[0]); 

        if (isset($_SESSION['cart'][$cart_id])) {
            // Ito 'yung pangalawang stock check (kung ang total sa cart + bago ay sobra na)
            $new_quantity = $_SESSION['cart'][$cart_id]['quantity'] + $quantity;
            
            if ($new_quantity > $product['quantity']) {
                $_SESSION['cart_message'] = "Cannot add more. Only {$product['quantity']} of {$product['name']} ({$size}/{$color}) is available in total.";
            } else {
                $_SESSION['cart'][$cart_id]['quantity'] = $new_quantity;
                $_SESSION['cart_message'] = "Added more {$product['name']} to your cart!";
            }
            
        } else {
            // Kung bagong item, i-add sa cart
            $_SESSION['cart'][$cart_id] = [
                'id' => $product['id'],
                'cart_id' => $cart_id, 
                'name' => $product['name'],
                'price' => $product['price'], // Tama! Galing sa DB ang presyo
                'image' => $firstImage, 
                'quantity' => $quantity,
                'size' => $size,       
                'color' => $color       
            ];
            
            $_SESSION['cart_message'] = $product['name'] . " has been added to your cart!";
        }
        
    } else {
        $_SESSION['cart_message'] = "Product not found or unavailable.";
    }

} else {
    $_SESSION['cart_message'] = "Invalid action.";
}

header("Location: index.php");
exit;
?>