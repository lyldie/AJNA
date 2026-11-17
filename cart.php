<?php
session_start();
include_once('config/dbcon.php');
include_once('config/function.php');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Remove Item Logic
if (isset($_GET['remove'])) {
    $remove_cart_id = validate($_GET['remove']);
    if (isset($_SESSION['cart'][$remove_cart_id])) {
        unset($_SESSION['cart'][$remove_cart_id]);
    }
    $_SESSION['cart_message'] = "Item removed from cart.";
    header("Location: cart.php");
    exit;
}

$total = 0;
$cart_warnings = [];

if (!empty($_SESSION['cart'])) {
    $product_ids = array_column($_SESSION['cart'], 'id');

    if (!empty($product_ids)) {
        $id_list = implode(',', array_map('intval', $product_ids));

        // QUERY: Kinukuha ang Product Details + Variations
        $query = "SELECT p.id, p.name, p.price, p.status, p.image, p.quantity as main_stock,
                         v.size, v.color, v.quantity as var_qty
                  FROM products p
                  INNER JOIN categories c ON p.category_id = c.id
                  LEFT JOIN product_variations v ON p.id = v.product_id
                  WHERE p.id IN ($id_list) 
                  AND p.status = 'active' 
                  AND c.status = 'active'";

        $db_products_result = mysqli_query($con, $query);

        $db_data = [];
        if ($db_products_result) {
            while ($row = mysqli_fetch_assoc($db_products_result)) {
                // Key for variations
                $v_key = $row['id'] . '-' . trim($row['size']) . '-' . trim($row['color']);
                $db_data['variations'][$v_key] = $row;
                // Key for main product info (fallback)
                $db_data['products'][$row['id']] = $row;
            }
        }
    }

    // Update Cart Logic
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $cart_id => $qty) {
            if (isset($_SESSION['cart'][$cart_id])) {
                $item = &$_SESSION['cart'][$cart_id];
                $requested_qty = max(1, intval($qty));

                $v_size = isset($item['size']) ? trim($item['size']) : '';
                $v_color = isset($item['color']) ? trim($item['color']) : '';
                $v_key = $item['id'] . '-' . $v_size . '-' . $v_color;

                // Determine stock source
                if (!empty($v_size) || !empty($v_color)) {
                    $max_stock = isset($db_data['variations'][$v_key]) ? (int)$db_data['variations'][$v_key]['var_qty'] : 0;
                } else {
                    $max_stock = isset($db_data['products'][$item['id']]) ? (int)$db_data['products'][$item['id']]['main_stock'] : 0;
                }

                $item['quantity'] = min($requested_qty, $max_stock);
                if ($requested_qty > $max_stock) {
                    $cart_warnings[] = "Adjusted: Only {$max_stock} available for <b>" . htmlspecialchars($item['name']) . "</b>";
                }
            }
        }
        unset($item);
        $_SESSION['cart_message'] = "Cart updated successfully.";
        header("Location: cart.php");
        exit;
    }

    // Validation Loop
    $items_to_remove = [];
    foreach ($_SESSION['cart'] as $cart_id => &$item) {
        $item_id = $item['id'];
        $v_size = !empty($item['size']) ? trim($item['size']) : '';
        $v_color = !empty($item['color']) ? trim($item['color']) : '';
        $v_key = $item_id . '-' . $v_size . '-' . $v_color;

        // 1. Check if product exists/active
        if (!isset($db_data['products'][$item_id])) {
            $cart_warnings[] = "<b>" . htmlspecialchars($item['name']) . "</b> is no longer available.";
            $items_to_remove[] = $cart_id;
            continue;
        }

        // 2. Stock Checking Logic (Flexible)
        if (!empty($v_size) || !empty($v_color)) {
            // May variation ang item
            $real_stock = isset($db_data['variations'][$v_key]) ? (int)$db_data['variations'][$v_key]['var_qty'] : 0;
        } else {
            // Walang variation (Standard Product)
            $real_stock = (int)$db_data['products'][$item_id]['main_stock'];
        }

        if ($real_stock <= 0) {
            $cart_warnings[] = "<b>" . htmlspecialchars($item['name']) . "</b> is now out of stock.";
            $items_to_remove[] = $cart_id;
            continue;
        }

        if ($item['quantity'] > $real_stock) {
            $item['quantity'] = $real_stock;
        }

        // Sync Latest Data
        $item['name'] = $db_data['products'][$item_id]['name'];
        $item['price'] = $db_data['products'][$item_id]['price'];

        // Image Logic
        $all_images = explode(',', $db_data['products'][$item_id]['image']);
        $item['image'] = trim($all_images[0]);

        $total += $item['price'] * $item['quantity'];
    }
    unset($item);

    foreach ($items_to_remove as $id) unset($_SESSION['cart'][$id]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>🛒 Your Cart | AJnA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-brand-gold: #b5975b;
            --color-dark-text: #222222;
            --color-background: #f9f9f9;
            --color-white: #ffffff;
        }

        body {
            background-color: var(--color-background);
            font-family: "Poppins", sans-serif;
            color: var(--color-dark-text);
        }

        h2 {
            font-family: 'Playfair Display', serif;
        }

        .navbar {
            background-color: var(--color-brand-gold) !important;
            padding: 15px 0;
        }

        .cart-container {
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background-color: var(--color-white);
        }

        .table img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
        }

        .quantity-input {
            width: 70px;
            text-align: center;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .btn-remove {
            color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
            border: none;
            border-radius: 50px;
            padding: 5px 15px;
        }

        .btn-checkout {
            background-color: var(--color-brand-gold);
            color: white;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            text-decoration: none;
        }

        .total-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-brand-gold);
        }
    </style>
</head>

<body>

    <?php include('./include2/navbarr.php'); ?>

    <div class="container cart-container">
        <?php if (!empty($cart_warnings)): ?>
            <div class="alert alert-danger mb-4">
                <h6 class="fw-bold"><i class="fas fa-exclamation-circle me-2"></i> Notice</h6>
                <?php foreach ($cart_warnings as $warning): ?>
                    <p class="mb-1 small"><?= $warning; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="card p-5 text-center">
                <h4>Your cart is empty.</h4>
                <a href="index.php" class="btn btn-checkout mt-3 d-inline-block">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="card overflow-hidden">
                <form method="POST">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light text-uppercase">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['cart'] as $cart_id => $item): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= htmlspecialchars($item['image']); ?>">
                                                <div class="ms-3">
                                                    <span class="d-block fw-bold"><?= htmlspecialchars($item['name']); ?></span>
                                                    <small class="text-muted">
                                                        Size: <?= htmlspecialchars($item['size'] ?? '/'); ?> |
                                                        Color: <?= htmlspecialchars($item['color'] ?? '/'); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">₱<?= number_format($item['price'], 2); ?></td>
                                        <td class="text-center">
                                            <input type="number" name="quantity[<?= $cart_id; ?>]" value="<?= $item['quantity']; ?>" class="quantity-input" min="1">
                                        </td>
                                        <td class="text-center fw-bold">₱<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        <td class="text-center pe-4">
                                            <a href="cart.php?remove=<?= $cart_id; ?>" class="btn-remove"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-white border-top">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <button type="submit" name="update_cart" class="btn btn-dark rounded-pill px-4">Update Cart</button>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="me-3 text-muted">Grand Total:</span>
                                <span class="total-price">₱<?= number_format($total, 2); ?></span>
                                <div class="mt-3">
                                    <a href="checkout_cart.php" class="btn btn-checkout">Proceed to Checkout →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>

</html>