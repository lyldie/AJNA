<?php
session_start();
include_once('config/dbcon.php');
include_once('config/function.php');

$product = null;
$total_amount = 0;
$variations_json = '[]';

$pre_selected_qty = intval($_GET['quantity'] ?? 1);
$pre_selected_size = $_GET['size'] ?? '';
$pre_selected_color = $_GET['color'] ?? '';

if (isset($_GET['product_id']) || isset($_GET['buy_now']) || isset($_GET['id'])) {
    $product_id = intval($_GET['product_id'] ?? $_GET['buy_now'] ?? $_GET['id']);

    $query = "SELECT products.* FROM products 
              INNER JOIN categories ON products.category_id = categories.id 
              WHERE products.id = $product_id 
              AND products.status = 'active' 
              AND categories.status = 'active'";

    $result = mysqli_query($con, $query);
    $product = mysqli_fetch_assoc($result);

    if ($product) {
        $total_amount = $product['price'];
        $images = !empty($product['image']) ? array_map('trim', explode(',', $product['image'])) : [];
        $main_image = !empty($images) ? $images[0] : 'assets/images/no-image.png';

        $v_query = mysqli_query($con, "SELECT size, color, quantity FROM product_variations WHERE product_id = '$product_id'");
        $variations = [];
        $has_real_variations = false;

        while ($v_row = mysqli_fetch_assoc($v_query)) {
            $v_size = trim($v_row['size']);
            $v_color = trim($v_row['color']);
            if ($v_size !== 'Standard' || $v_color !== 'Standard') {
                $has_real_variations = true;
            }
            $variations[] = [
                'size' => $v_size,
                'color' => $v_color,
                'quantity' => intval($v_row['quantity'])
            ];
        }
        $variations_json = json_encode($variations);
    } else {
        $_SESSION['error_message'] = "Invalid product ID or product is no longer available.";
    }
}
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
        }

        body {
            background-color: var(--color-background);
            font-family: "Poppins", sans-serif;
            color: var(--color-dark-text);
            font-size: 16px;
        }

        h2,
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

        .order-summary-wrapper {
            flex: 1;
            min-width: 350px;
            position: sticky;
            top: 40px;
        }

        .btn-place-order {
            background-color: var(--color-brand-gold);
            color: #fff;
            padding: 18px 30px;
            border-radius: 50px;
            width: 100%;
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-place-order:hover {
            background-color: #8e7343;
            transform: translateY(-3px);
        }

        .btn-place-order:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
        }

        /* GRID FIX FOR ALIGNMENT */
        .cart-item-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
            align-items: start;
            min-height: 80px;
        }

        #variation-selectors {
            display: contents;
        }

        .detail-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #666;
            margin-bottom: 4px;
        }

        .stock-indicator {
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-5px);
        }

        .stock-indicator.show {
            opacity: 1;
            transform: translateY(0);
        }

        .stock-critical {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .stock-plenty {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .stock-none {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <?php include('./include2/navbarr.php'); ?>

    <div class="checkout-page-container">
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger text-center mb-5 p-3 shadow-sm rounded-4"><?= htmlspecialchars($_SESSION['error_message']); ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="checkout-flex-container">
            <div class="checkout-form-wrapper">
                <div class="checkout-card">
                    <div class="mb-4"><a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">← Back to Shop</a></div>

                    <?php if ($product): ?>
                        <h2>Checkout Item</h2>
                        <div class="d-flex align-items-start border-bottom pb-4 mb-4 mt-4">
                            <div class="d-flex flex-column align-items-center">
                                <img id="product-image" src="<?= htmlspecialchars($main_image); ?>" class="cart-item-image mb-2" alt="Product">
                                <?php if (count($images) > 1): ?>
                                    <div class="d-flex gap-1 flex-wrap justify-content-center" style="max-width: 120px;">
                                        <?php foreach ($images as $img): ?>
                                            <img src="<?= htmlspecialchars($img); ?>" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid #ddd;" onclick="document.getElementById('product-image').src=this.src">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="ms-4 flex-grow-1">
                                <h5><?= htmlspecialchars($product['name']); ?></h5>
                                <h4 class="text-muted mb-3">₱<?= number_format($product['price'], 2); ?></h4>
                                <input type="hidden" id="unit_price" value="<?= $product['price']; ?>">

                                <div class="cart-item-details">
                                    <div id="variation-selectors"></div>

                                    <div class="d-flex flex-column" id="quantity-container">
                                        <label class="detail-label">Quantity</label>
                                        <select name="quantity" id="quantity" class="form-select" form="checkout-form" style="width: 80px;">
                                            <option value="1">1</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="dynamic-stock-badge" class="stock-indicator"></div>
                            </div>
                        </div>

                        <form action="process_order.php" method="POST" id="checkout-form">
                            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                            <input type="hidden" name="place_order" value="1">
                            <input type="hidden" name="shipping_fee" value="0">
                            <?php if (!$has_real_variations): ?>
                                <input type="hidden" name="size" value="Standard">
                                <input type="hidden" name="color" value="Standard">
                            <?php endif; ?>

                            <h4 class="mb-3">Shipping Information</h4>
                            <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="customer_name" class="form-control" required></div>
                            <div class="row mb-3">
                                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="customer_email" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text" name="customer_phone" class="form-control" required></div>
                            </div>
                            <div class="mb-3"><label class="form-label">Delivery Address</label><textarea name="customer_address" class="form-control" rows="2" required></textarea></div>

                            <h4 class="mb-3 mt-4">Payment Details</h4>
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="">-- Select Payment Method --</option>
                                    <option value="Cash on Delivery">Cash on Delivery</option>
                                    <option value="GCash">GCash</option>
                                </select>
                            </div>

                            <div class="p-4 bg-light rounded border mb-3" id="gcash-info" style="display: none;">
                                <h5 class="fw-bold" style="color: #b2935b;">Pay via GCash</h5>
                                <p class="mb-2 text-muted">Send amount to <strong>0912 345 6789</strong></p>
                                <img src="assets/images/gcash-placeholder.png" alt="GCash QR" style="max-width: 150px; border-radius: 10px;">
                                <div class="mt-3">
                                    <label class="form-label fw-bold">Enter Reference Number (Last 4 Digits)</label>
                                    <input type="text" name="pay_ref_number" id="pay_ref_number" class="form-control form-control-lg text-center fw-bold" placeholder="e.g. 1234" maxlength="4" pattern="\d{4}">
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" name="place_order" class="btn-place-order" id="placeOrderBtn">Place Order Now</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="text-center text-danger">Product not found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="order-summary-wrapper">
                <div class="checkout-card">
                    <h3>Order Summary</h3>
                    <div class="d-flex justify-content-between align-items-center fs-4 fw-bold mt-3">
                        <span>Total:</span>
                        <span>₱<span id="total-display"><?= number_format($total_amount, 2); ?></span></span>
                    </div>
                    <small class="text-muted d-block mt-2">VAT included.</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const productVariations = <?= $variations_json ?>;
        const productStockTotal = <?= $product['quantity'] ?? 0 ?>;
        const hasRealVariations = <?= $has_real_variations ? 'true' : 'false' ?>;
        const productImages = <?= json_encode($images) ?>; // For color switching

        const urlQty = <?= $pre_selected_qty ?>;
        const urlSize = "<?= $pre_selected_size ?>";
        const urlColor = "<?= $pre_selected_color ?>";

        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('variation-selectors');
            const quantitySelect = document.getElementById('quantity');
            const qtyContainer = document.getElementById('quantity-container');
            const totalDisplay = document.getElementById('total-display');
            const unitPriceElement = document.getElementById('unit_price');
            const placeOrderBtn = document.getElementById('placeOrderBtn');
            const dynamicBadge = document.getElementById('dynamic-stock-badge');
            const mainImgElement = document.getElementById('product-image');

            const normalize = (str) => str ? str.toString().trim().toLowerCase() : '';

            let allSizes = [...new Set(productVariations.map(v => v.size).filter(s => s && s !== 'Standard'))];
            let allColors = [...new Set(productVariations.map(v => v.color).filter(c => c && c !== 'Standard'))];

            let sizeSelect = null,
                colorSelect = null;

            if (allSizes.length > 0) {
                const div = document.createElement('div');
                div.className = "d-flex flex-column";
                div.innerHTML = `<label class="detail-label">Size</label><select name="size" id="sizeSelect" class="form-select" form="checkout-form" required><option value="">-- Select --</option></select>`;
                container.appendChild(div);
                sizeSelect = document.getElementById('sizeSelect');
            }

            if (allColors.length > 0) {
                const div = document.createElement('div');
                div.className = "d-flex flex-column";
                div.innerHTML = `<label class="detail-label">Color</label><select name="color" id="colorSelect" class="form-select" form="checkout-form" required><option value="">-- Select --</option></select>`;
                container.appendChild(div);
                colorSelect = document.getElementById('colorSelect');
            }

            function renderDropdowns() {
                if (!hasRealVariations) return;
                let curSize = sizeSelect ? (sizeSelect.value || urlSize) : '';
                let curColor = colorSelect ? (colorSelect.value || urlColor) : '';

                if (sizeSelect && sizeSelect.options.length <= 1) {
                    allSizes.forEach(size => {
                        let opt = document.createElement('option');
                        opt.value = size;
                        opt.textContent = size;
                        sizeSelect.appendChild(opt);
                    });
                    sizeSelect.value = curSize;
                }

                if (colorSelect) {
                    let validColors = allColors;
                    if (curSize) validColors = productVariations.filter(v => normalize(v.size) === normalize(curSize)).map(v => v.color);
                    colorSelect.innerHTML = '<option value="">-- Select --</option>';
                    allColors.forEach(color => {
                        if (validColors.some(c => normalize(c) === normalize(color))) {
                            let opt = document.createElement('option');
                            opt.value = color;
                            opt.textContent = color;
                            colorSelect.appendChild(opt);
                        }
                    });
                    colorSelect.value = curColor;
                }
            }

            function updateImageByColor(color) {
                if (!color || productImages.length === 0) return;
                const match = productImages.find(img => normalize(img).includes(normalize(color)));
                if (match) mainImgElement.src = match;
            }

            function checkStockAndReset() {
                let selSize = sizeSelect ? sizeSelect.value : (hasRealVariations ? '' : 'Standard');
                let selColor = colorSelect ? colorSelect.value : (hasRealVariations ? '' : 'Standard');

                if (selColor && selColor !== 'Standard') updateImageByColor(selColor);

                // ALIGNMENT LOGIC: Kapag Standard lang, Quantity jumps to Column 1
                if (!hasRealVariations) {
                    qtyContainer.style.gridColumn = "1";
                } else {
                    qtyContainer.style.gridColumn = "3";
                }

                let maxQty = 0;
                let isValidSelection = true;

                if (productVariations.length > 0) {
                    let v = productVariations.find(i => normalize(i.size) == normalize(selSize) && normalize(i.color) == normalize(selColor));
                    if (v) {
                        maxQty = parseInt(v.quantity);
                        if (maxQty > 5) updateBadge(`<i class="fas fa-check-circle"></i> In Stock: ${maxQty}`, 'stock-plenty');
                        else if (maxQty > 0) updateBadge(`<i class="fas fa-exclamation-circle"></i> Only ${maxQty} left!`, 'stock-critical');
                        else updateBadge(`<i class="fas fa-times-circle"></i> Out of Stock`, 'stock-none');
                    } else {
                        isValidSelection = false;
                        if (selSize && selColor) updateBadge(`Unavailable`, 'stock-none');
                        else hideBadge();
                    }
                } else {
                    maxQty = productStockTotal;
                    if (maxQty <= 5 && maxQty > 0) updateBadge(`Only ${maxQty} left!`, 'stock-critical');
                    else hideBadge();
                }

                quantitySelect.innerHTML = '';
                if (maxQty > 0 && (!hasRealVariations || (selSize && selColor))) {
                    for (let i = 1; i <= Math.min(maxQty, 10); i++) {
                        let opt = document.createElement('option');
                        opt.value = i;
                        opt.textContent = i;
                        if (i === urlQty) opt.selected = true;
                        quantitySelect.appendChild(opt);
                    }
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.textContent = "Place Order Now";
                } else {
                    let opt = document.createElement('option');
                    opt.text = "-";
                    quantitySelect.add(opt);
                    placeOrderBtn.disabled = true;
                    placeOrderBtn.textContent = (maxQty === 0 && (selSize && selColor)) ? "Out of Stock" : "Select Options";
                }
                calculateTotal();
            }

            function updateBadge(msg, type) {
                dynamicBadge.className = 'stock-indicator ' + type + ' show';
                dynamicBadge.innerHTML = msg;
            }

            function hideBadge() {
                dynamicBadge.className = 'stock-indicator';
                dynamicBadge.innerHTML = '';
            }

            function calculateTotal() {
                let price = parseFloat(unitPriceElement.value);
                let qty = parseInt(quantitySelect.value) || 0;
                totalDisplay.textContent = (price * qty).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            if (sizeSelect) sizeSelect.addEventListener('change', () => {
                renderDropdowns();
                checkStockAndReset();
            });
            if (colorSelect) colorSelect.addEventListener('change', () => {
                renderDropdowns();
                checkStockAndReset();
            });
            quantitySelect.addEventListener('change', calculateTotal);

            renderDropdowns();
            checkStockAndReset();

            // GCash Logic
            const paymentSelect = document.getElementById('payment_method');
            const gcashBox = document.getElementById('gcash-info');
            paymentSelect.addEventListener('change', function() {
                gcashBox.style.display = (this.value === 'GCash') ? 'block' : 'none';
                document.getElementById('pay_ref_number').required = (this.value === 'GCash');
            });
        });
    </script>
</body>

</html>