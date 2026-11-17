<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "inventory_system";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// --- Get product ID ---
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM products WHERE id = '$product_id' AND status = 'active'";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<p class='text-center mt-5'>Product not found.</p>";
    exit;
}

// Handle multiple images
$imageList = array_filter(array_map('trim', explode(',', $product['image'])));
$firstImage = !empty($imageList) ? $imageList[0] : 'assets/images/no-image.png';

// --- FETCH VARIATIONS DATA ---
$v_query = mysqli_query($conn, "SELECT size, color, quantity FROM product_variations WHERE product_id = '$product_id'");
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']); ?> | AJ&A Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Playfair Display', serif;
        }

        .product-container {
            margin-top: 50px;
            margin-bottom: 50px;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .product-image-wrapper {
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 15px;
            background: #fff;
        }

        .product-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.02);
        }

        .thumbnail-container {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            opacity: 0.7;
        }

        .thumbnail:hover,
        .thumbnail.active {
            border-color: #b2935b;
            opacity: 1;
            transform: translateY(-2px);
        }

        .product-title {
            font-size: 2.5rem;
            color: #222;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 2rem;
            color: #b2935b;
            font-weight: 700;
        }

        .product-desc {
            color: #666;
            line-height: 1.8;
            font-size: 1rem;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #444;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-select,
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px;
        }

        .btn-brand {
            background-color: #b2935b;
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            padding: 15px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .btn-brand:hover {
            background-color: #8e7343;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(178, 147, 91, 0.3);
        }

        .btn-dark {
            background-color: #222;
            border-radius: 50px;
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .btn-dark:hover {
            background-color: #000;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .badge-in-stock {
            background-color: #d4edda;
            color: #155724;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 30px;
        }

        .stock-indicator {
            margin-top: 15px;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
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

        .navbar {
            background: #b2935b !important;
            padding: 15px 0;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: white !important;
        }
    </style>
</head>

<body>
    <?php include('include2/navbarr.php'); ?>

    <?php if (isset($_SESSION['cart_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show text-center mb-0" role="alert" style="position: fixed; top: 0; left: 0; width: 100%; z-index: 2000; border-radius: 0;">
            <?= htmlspecialchars($_SESSION['cart_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['cart_message']); ?>
    <?php endif; ?>

    <div class="container product-container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 text-center">
                <div class="product-image-wrapper">
                    <img id="main-image" src="<?= htmlspecialchars($firstImage); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="product-image">
                </div>
                <?php if (count($imageList) > 1): ?>
                    <div class="thumbnail-container">
                        <?php foreach ($imageList as $index => $img): ?>
                            <img src="<?= htmlspecialchars($img); ?>" class="thumbnail <?= $index === 0 ? 'active' : ''; ?>" alt="Product thumbnail" onclick="changeImage(this)">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <h1 class="product-title"><?= htmlspecialchars($product['name']); ?></h1>
                    <p class="product-price">₱<?= number_format($product['price'], 2); ?></p>
                    <div class="mb-4">
                        <span id="main-stock-badge" class="badge-in-stock">
                            <i class="fas fa-check-circle me-1"></i> Total Stock: <?= $product['quantity']; ?>
                        </span>
                    </div>
                    <div class="product-desc"><?= nl2br(htmlspecialchars($product['description'])); ?></div>
                    <hr class="my-4" style="opacity: 0.1;">

                    <form action="add_to_cart.php" method="POST" id="main-product-form">
                        <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                        <div class="row">
                            <div id="variation-container" class="col-12" <?= !$has_real_variations ? 'style="display:none;"' : ''; ?>></div>
                            <div class="col-12 mb-3">
                                <div id="dynamic-stock-badge" class="stock-indicator"></div>
                            </div>
                            <div class="mb-4 col-md-6">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-control text-center" value="1" min="1" max="<?= $product['quantity']; ?>" required>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mt-2">
                            <button type="submit" name="add_to_cart" id="add-to-cart-btn" class="btn btn-brand btn-lg flex-fill">Add to Cart</button>
                            <button type="submit" name="buy_now" id="buy-now-btn" class="btn btn-dark btn-lg flex-fill" formaction="checkout.php" formmethod="GET">
                                Buy Now
                                <input type="hidden" name="buy_now" value="<?= $product['id']; ?>" disabled>
                            </button>
                        </div>
                    </form>
                    <div class="mt-5 pt-3 text-center"><a href="index.php" class="btn btn-outline-back">← Continue Shopping</a></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- Image Switching Logic ---
        function changeImage(thumb) {
            const mainImage = document.getElementById('main-image');
            mainImage.src = thumb.src;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const productVariations = <?= $variations_json ?>;
            const productTotalStock = <?= intval($product['quantity']) ?>;
            const hasRealVariations = <?= $has_real_variations ? 'true' : 'false' ?>;

            const container = document.getElementById('variation-container');
            const quantityInput = document.getElementById('quantity');
            const addToCartBtn = document.getElementById('add-to-cart-btn');
            const buyNowBtn = document.getElementById('buy-now-btn');
            const dynamicBadge = document.getElementById('dynamic-stock-badge');
            const mainImage = document.getElementById('main-image');
            const thumbnails = document.querySelectorAll('.thumbnail');

            const normalize = (str) => str ? str.toString().trim().toLowerCase() : '';

            let allSizes = [],
                allColors = [];
            if (productVariations.length > 0) {
                allSizes = [...new Set(productVariations.map(v => v.size).filter(s => s && s !== 'Standard'))];
                allColors = [...new Set(productVariations.map(v => v.color).filter(c => c && c !== 'Standard'))];
            }

            let sizeSelect = null,
                colorSelect = null;

            if (allSizes.length > 0) {
                const wrapper = document.createElement('div');
                wrapper.className = "mb-3";
                wrapper.innerHTML = `<label class="form-label">Select Size</label><select id="size" name="size" class="form-select" required><option value="">-- Choose Option --</option></select>`;
                container.appendChild(wrapper);
                sizeSelect = document.getElementById('size');
            }

            if (allColors.length > 0) {
                const wrapper = document.createElement('div');
                wrapper.className = "mb-3";
                wrapper.innerHTML = `<label class="form-label">Select Color</label><select id="color" name="color" class="form-select" required><option value="">-- Choose Option --</option></select>`;
                container.appendChild(wrapper);
                colorSelect = document.getElementById('color');
            }

            // Logic: Auto switch image when color is picked
            function autoSwitchImage(selectedColor) {
                if (!selectedColor) return;
                const target = normalize(selectedColor);
                let found = false;
                thumbnails.forEach(thumb => {
                    if (normalize(thumb.src).includes(target) && !found) {
                        mainImage.src = thumb.src;
                        thumbnails.forEach(t => t.classList.remove('active'));
                        thumb.classList.add('active');
                        found = true;
                    }
                });
            }

            function renderDropdowns() {
                if (!hasRealVariations) return;
                let curSize = sizeSelect ? sizeSelect.value : '';
                let curColor = colorSelect ? colorSelect.value : '';

                if (sizeSelect) {
                    let tempSize = curSize;
                    sizeSelect.innerHTML = '<option value="">-- Choose Option --</option>';
                    allSizes.forEach(size => {
                        let isDisabled = false;
                        if (curColor) {
                            let v = productVariations.find(i => normalize(i.size) == normalize(size) && normalize(i.color) == normalize(curColor));
                            if (!v || parseInt(v.quantity) <= 0) isDisabled = true;
                        }
                        let opt = document.createElement('option');
                        opt.value = size;
                        opt.textContent = size + (isDisabled ? " (Unavailable)" : "");
                        if (isDisabled) opt.disabled = true;
                        sizeSelect.appendChild(opt);
                    });
                    sizeSelect.value = tempSize;
                }

                if (colorSelect) {
                    let tempColor = curColor;
                    colorSelect.innerHTML = '<option value="">-- Choose Option --</option>';
                    allColors.forEach(color => {
                        let isDisabled = false;
                        if (curSize) {
                            let v = productVariations.find(i => normalize(i.size) == normalize(curSize) && normalize(i.color) == normalize(color));
                            if (!v || parseInt(v.quantity) <= 0) isDisabled = true;
                        }
                        let opt = document.createElement('option');
                        opt.value = color;
                        opt.textContent = color + (isDisabled ? " (Unavailable)" : "");
                        if (isDisabled) opt.disabled = true;
                        colorSelect.appendChild(opt);
                    });
                    colorSelect.value = tempColor;
                }
            }

            function checkStock() {
                let selSize = sizeSelect ? sizeSelect.value : 'Standard';
                let selColor = colorSelect ? colorSelect.value : 'Standard';
                let maxQty = productTotalStock;
                let isValid = true;

                if (productVariations.length > 0) {
                    let v = productVariations.find(i => normalize(i.size) == normalize(selSize) && normalize(i.color) == normalize(selColor));
                    if (hasRealVariations && ((sizeSelect && !sizeSelect.value) || (colorSelect && !colorSelect.value))) {
                        isValid = false;
                        dynamicBadge.className = 'stock-indicator';
                    } else if (v) {
                        maxQty = parseInt(v.quantity);
                        let msg = maxQty > 10 ? `In Stock: ${maxQty} available` : (maxQty > 0 ? `Hurry! Only ${maxQty} left!` : `Out of Stock`);
                        let type = maxQty > 10 ? 'stock-plenty' : (maxQty > 0 ? 'stock-critical' : 'stock-none');
                        dynamicBadge.innerHTML = `<i class="fas fa-info-circle"></i> ` + msg;
                        dynamicBadge.className = 'stock-indicator ' + type + ' show';
                    } else {
                        maxQty = 0;
                        if (hasRealVariations) {
                            dynamicBadge.innerHTML = `Unavailable Combination`;
                            dynamicBadge.className = 'stock-indicator stock-none show';
                        }
                    }
                }

                quantityInput.max = maxQty > 0 ? maxQty : 1;
                if (maxQty > 0 && isValid) {
                    addToCartBtn.disabled = false;
                    buyNowBtn.disabled = false;
                    addToCartBtn.textContent = "Add to Cart";
                } else {
                    addToCartBtn.disabled = true;
                    buyNowBtn.disabled = true;
                    addToCartBtn.textContent = maxQty <= 0 && isValid ? "Out of Stock" : "Select Options";
                }
            }

            if (sizeSelect) sizeSelect.addEventListener('change', () => {
                renderDropdowns();
                checkStock();
            });
            if (colorSelect) colorSelect.addEventListener('change', function() {
                renderDropdowns();
                checkStock();
                autoSwitchImage(this.value);
            });

            renderDropdowns();
            checkStock();

            document.getElementById('main-product-form').addEventListener('submit', function(e) {
                if (e.submitter && e.submitter.name === 'buy_now') {
                    const h = e.submitter.querySelector('input[name="buy_now"]');
                    if (h) h.disabled = false;
                }
                if (!hasRealVariations) {
                    const s = document.createElement('input');
                    s.type = 'hidden';
                    s.name = 'size';
                    s.value = 'Standard';
                    this.appendChild(s);
                    const c = document.createElement('input');
                    c.type = 'hidden';
                    c.name = 'color';
                    c.value = 'Standard';
                    this.appendChild(c);
                }
            });
        });
    </script>
</body>

</html>
<?php mysqli_close($conn); ?>