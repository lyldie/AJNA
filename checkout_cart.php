<?php
session_start();
include('config/dbcon.php');

if (empty($_SESSION['cart'])) {
    echo '<div style="display:flex;justify-content:center;align-items:center;height:100vh;flex-direction:column;font-family:sans-serif;">
            <h3 style="margin-bottom:20px;">Your cart is empty.</h3>
            <a href="index.php" style="text-decoration:none;padding:15px 30px;background:#b5975b;color:white;border-radius:50px;font-weight:bold;box-shadow: 0 4px 15px rgba(181, 151, 91, 0.3);">Go Back to Shop</a>
          </div>';
    exit;
}

$total = 0;
$product_ids = array_column($_SESSION['cart'], 'id');

if (!empty($product_ids)) {
    $id_list = implode(',', array_map('intval', $product_ids));

    // Optimized Query: Kasama na pati ang main stock (p.quantity) para sa fallback
    $query = "SELECT p.id, p.name, p.price, p.status, p.image, p.quantity as main_stock,
                     v.size, v.color, v.quantity as var_qty
              FROM products p
              INNER JOIN categories c ON p.category_id = c.id
              LEFT JOIN product_variations v ON p.id = v.product_id
              WHERE p.id IN ($id_list) 
              AND p.status='active' 
              AND c.status='active'";

    $db_products_result = mysqli_query($con, $query);

    $db_data = [];
    while ($row = mysqli_fetch_assoc($db_products_result)) {
        // I-save ang variations
        $v_key = $row['id'] . '-' . trim($row['size']) . '-' . trim($row['color']);
        $db_data['variations'][$v_key] = $row;
        // I-save ang main product info (para sa items na walang variations)
        $db_data['products'][$row['id']] = $row;
    }
}

// Re-validate session items against database
foreach ($_SESSION['cart'] as $cart_id => &$item) {
    $prod_id = $item['id'];
    $v_size = !empty($item['size']) ? trim($item['size']) : '';
    $v_color = !empty($item['color']) ? trim($item['color']) : '';
    $v_key = $prod_id . '-' . $v_size . '-' . $v_color;

    // 1. Check if the product itself exists and is active
    if (!isset($db_data['products'][$prod_id])) {
        unset($_SESSION['cart'][$cart_id]);
        continue;
    }

    $main_info = $db_data['products'][$prod_id];

    // 2. Flexible Stock Checking
    if ($v_size !== '' || $v_color !== '') {
        // Kung may variation, hanapin sa variations list
        if (isset($db_data['variations'][$v_key])) {
            $real_stock = intval($db_data['variations'][$v_key]['var_qty']);
        } else {
            // Kung may size/color pero hindi mahanap sa DB (error matching), 0 stock
            $real_stock = 0;
        }
    } else {
        // No variations - fallback sa main_stock ng product table
        $real_stock = intval($main_info['main_stock']);
    }

    $item['name'] = $main_info['name'];
    $item['price'] = $main_info['price'];
    $item['stock'] = $real_stock;

    // --- DYNAMIC IMAGE LOGIC ---
    $all_images = !empty($main_info['image']) ? array_map('trim', explode(',', $main_info['image'])) : [];
    $selected_color = strtolower($v_color);
    $final_image = !empty($all_images) ? $all_images[0] : 'assets/images/no-image.png';

    if ($selected_color && $selected_color != 'standard') {
        foreach ($all_images as $img) {
            if (str_contains(strtolower($img), $selected_color)) {
                $final_image = $img;
                break;
            }
        }
    }
    $item['image'] = $final_image;

    // --- QUANTITY ADJUSTMENT ---
    if ($item['quantity'] > $real_stock) {
        $item['quantity'] = $real_stock;
    }

    $item['subtotal'] = $item['quantity'] * $item['price'];

    // I-add sa total kung may stock pa
    if ($real_stock > 0) {
        $total += $item['subtotal'];
    }
}
unset($item);

$total_with_shipping = $total;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout | AJnA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --color-brand-gold: #b5975b;
            --color-dark-text: #222;
            --color-background: #f9f9f9;
            --color-white: #fff;
            --color-border: #eee;
        }

        body {
            background-color: var(--color-background);
            font-family: "Poppins", sans-serif;
            color: var(--color-dark-text);
            font-size: 16px;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Playfair Display', serif;
            color: var(--color-dark-text);
        }

        .checkout-page-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .checkout-flex-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            align-items: flex-start;
        }

        .checkout-card {
            background-color: var(--color-white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: none;
        }

        .checkout-form-wrapper {
            flex: 2;
            min-width: 320px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--color-dark-text);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control,
        .form-select {
            padding: 12px 18px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }

        .cart-item-price {
            font-weight: 700;
            color: var(--color-brand-gold);
            margin-left: auto;
        }

        .gcash-box {
            background: #f0f7ff;
            padding: 25px;
            border-radius: 15px;
            border: 2px dashed #007bff;
            text-align: center;
            margin-top: 20px;
        }

        .order-summary-box {
            position: sticky;
            top: 40px;
            border: 1px solid #eee;
        }

        .btn-place-order {
            background-color: var(--color-brand-gold);
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            text-transform: uppercase;
            padding: 15px;
            border: none;
            border-radius: 50px;
            width: 100%;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(181, 151, 91, 0.4);
        }

        .btn-place-order:hover {
            background-color: #8e7343;
            transform: translateY(-2px);
        }

        .btn-outline-back {
            border: 2px solid #ddd;
            color: #666;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .navbar {
            background-color: var(--color-brand-gold) !important;
            padding: 15px 0;
        }
    </style>
</head>

<body>

    <?php include('./include2/navbarr.php'); ?>

    <div class="checkout-page-container">
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger text-center mb-5 rounded-4">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="checkout-flex-container">
            <div class="checkout-form-wrapper">
                <div class="checkout-card">
                    <div class="mb-4"><a href="cart.php" class="btn btn-outline-back">← Back to Cart</a></div>
                    <h2>Checkout</h2>

                    <ul class="cart-item-list mb-5 p-0">
                        <?php
                        $can_proceed = true;
                        foreach ($_SESSION['cart'] as $cart_id => $item):
                            $is_out_of_stock = ($item['stock'] <= 0);
                            if ($is_out_of_stock) $can_proceed = false;
                        ?>
                            <li class="cart-item">
                                <img src="<?= htmlspecialchars($item['image']); ?>" class="cart-item-image">
                                <div class="cart-item-info">
                                    <h5 class="mb-1"><?= htmlspecialchars($item['name']); ?></h5>
                                    <div class="small text-muted">
                                        Size: <strong><?= !empty($item['size']) ? htmlspecialchars($item['size']) : 'Standard'; ?></strong> |
                                        Color: <strong><?= !empty($item['color']) ? htmlspecialchars($item['color']) : 'Standard'; ?></strong>
                                    </div>
                                    <div class="mt-2">
                                        <?php if ($is_out_of_stock): ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border">Qty: <?= $item['quantity']; ?></span>
                                            <?php if ($item['stock'] <= 5): ?>
                                                <span class="text-danger small ms-2 fw-bold">Only <?= $item['stock']; ?> left!</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="cart-item-price">₱<?= number_format($item['subtotal'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <form action="place_order.php" method="POST" id="checkout-form">
                        <input type="hidden" name="place_order" value="1">
                        <h4>Shipping Information</h4>
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Juan Dela Cruz" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="customer_email" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="text" name="phone" class="form-control" placeholder="09xxxxxxxxx" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Delivery Address</label>
                            <textarea name="address" class="form-control" rows="3" required></textarea>
                        </div>

                        <h4>Payment Details</h4>
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="">-- Select Payment Method --</option>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="GCash">GCash</option>
                            </select>
                        </div>

                        <div class="gcash-box" id="gcash-info" style="display:none;">
                            <h5 class="fw-bold" style="color: #007bff;">Pay via GCash</h5>
                            <p class="mb-2">Send payment to: <strong>0912 345 6789</strong></p>
                            <img src="assets/images/gcash-placeholder.png" alt="GCash QR" style="max-width: 150px; border-radius: 10px;">
                            <div class="mt-3 text-start">
                                <label class="form-label fw-bold">GCash Reference No. (Last 4 Digits)</label>
                                <input type="text" name="pay_ref_number" id="pay_ref_number" class="form-control text-center fw-bold" placeholder="1234" maxlength="4">
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn-place-order" id="placeOrderBtn" <?= !$can_proceed ? 'disabled' : '' ?>>
                                <?= $can_proceed ? 'Place Order Now' : 'Some items are unavailable' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="order-summary-wrapper">
                <div class="checkout-card order-summary-box">
                    <h3 class="h4">Order Summary</h3>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>₱<?= number_format($total, 2); ?></span></div>
                    <div class="d-flex justify-content-between mb-4"><span>Shipping</span><span class="text-success">FREE</span></div>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <span class="fw-bold h5 mb-0">Total</span>
                        <span class="fw-bold h4 mb-0" style="color: var(--color-brand-gold);">₱<?= number_format($total_with_shipping, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const paymentSelect = document.getElementById('payment_method');
            const gcashBox = document.getElementById('gcash-info');
            const refInput = document.getElementById('pay_ref_number');
            const placeBtn = document.getElementById('placeOrderBtn');

            paymentSelect.addEventListener('change', function() {
                if (this.value === 'GCash') {
                    gcashBox.style.display = 'block';
                    refInput.required = true;
                } else {
                    gcashBox.style.display = 'none';
                    refInput.required = false;
                }
            });

            document.getElementById('checkout-form').addEventListener('submit', function(e) {
                if (paymentSelect.value === 'GCash') {
                    const refVal = refInput.value.trim();
                    if (refVal.length !== 4 || isNaN(refVal)) {
                        e.preventDefault();
                        alert('Please enter exactly the LAST 4 DIGITS of the GCash Reference Number.');
                        refInput.focus();
                        return;
                    }
                }
                placeBtn.disabled = true;
                placeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Placing Order...';
            });
        });
    </script>
</body>

</html>