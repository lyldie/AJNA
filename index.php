<?php
session_start();
// --- DB CONNECTION ---
$host = "localhost";
$username = "root";
$password = "";
$database = "inventory_system";

$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// --- SEARCH & FILTER ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

// 1. FETCH CATEGORIES (Store in array for Tree View Logic)
$category_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
$categories_res = mysqli_query($conn, $category_query);
$all_categories = [];
while ($row = mysqli_fetch_assoc($categories_res)) {
    $all_categories[] = $row;
}

// 2. MAIN PRODUCT QUERY
$sql = "SELECT products.* FROM products 
        INNER JOIN categories ON products.category_id = categories.id 
        WHERE products.status = 'active' AND categories.status = 'active'";

if ($search !== '') {
    $searchSafe = mysqli_real_escape_string($conn, $search);
    $sql .= " AND products.name LIKE '%$searchSafe%'";
}

// --- UPDATED FILTER LOGIC (Parent includes Children) ---
if ($filter !== '' && $filter !== 'all') {
    $filterSafe = mysqli_real_escape_string($conn, $filter);

    // Logic: Show products belonging to this Category ID OR its Sub-categories
    $sql .= " AND (products.category_id = '$filterSafe' OR products.category_id IN (SELECT id FROM categories WHERE parent_id = '$filterSafe'))";
}

$result = mysqli_query($conn, $sql);

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJ & A | Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* --- GLOBAL STYLES --- */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        .navbar-brand {
            font-family: 'Playfair Display', serif;
        }

        /* --- NAVBAR UPGRADE --- */
        .navbar {
            background: #b2935b !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding-top: 15px;
            padding-bottom: 15px;
        }

        .navbar-brand {
            font-size: 1.5rem;
            letter-spacing: 1px;
            font-weight: bold;
            color: white !important;
        }

        .nav-link {
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* --- HERO SECTION --- */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('kends.jpeg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 20px;
            text-align: center;
            border-radius: 0 0 30px 30px;
            margin-bottom: 50px;
            box-shadow: 0 10px 30px rgba(178, 147, 91, 0.3);
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .hero .btn {
            padding: 12px 35px;
            border-radius: 50px;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        .hero .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
        }

        /* --- PRODUCT CARD UPGRADE --- */
        .product-card {
            border: none;
            border-radius: 15px;
            background: #fff;
            transition: all 0.3s ease-in-out;
            overflow: hidden;
            height: 100%;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .product-card img {
            height: 250px;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover img {
            transform: scale(1.05);
        }

        .card-body {
            padding: 20px;
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-text.text-muted {
            color: #b2935b !important;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        /* --- BADGES & BUTTONS --- */
        .btn-dark {
            background-color: #222;
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
        }

        .btn-outline-dark {
            border-radius: 50px;
            font-weight: 600;
            border: 2px solid #222;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: bold;
            padding: 2px 6px;
            border: 2px solid #b2935b;
        }

        /* --- MODAL UPGRADE --- */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: #f8f9fa;
            border-bottom: none;
            padding: 20px 30px;
            border-radius: 20px 20px 0 0;
        }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-weight: bold;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-product-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-brand {
            background-color: #b2935b;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-brand:hover {
            background-color: #8e7343;
            color: white;
            transform: translateY(-2px);
        }

        .stock-display-modal {
            font-size: 0.9rem;
            margin-top: 5px;
            display: block;
        }
    </style>
</head>

<body>

    <?php if (isset($_SESSION['cart_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show text-center mb-0" role="alert" style="position: fixed; top: 0; left: 0; width: 100%; z-index: 2000; border-radius: 0;">
            <?= htmlspecialchars($_SESSION['cart_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['cart_message']); ?>
    <?php endif; ?>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="logo.jpeg" alt="AJ&A Logo" style="height: 40px; border-radius:5px; margin-right: 10px;">
                AJ&A DIVI-things
            </a>
            <div class="d-flex align-items-center gap-3">
                <form class="d-flex" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search products" value="<?= htmlspecialchars($search) ?>" style="border-radius: 20px;">
                    <button class="btn btn-outline-light" type="submit" style="border-radius: 20px;">Search</button>
                </form>
                <div class="position-relative">
                    <a href="cart.php" class="btn btn-outline-light position-relative" style="border-radius: 20px; padding-left: 20px; padding-right: 20px;">
                        🛒 Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?= $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <a href="track-order.php" class="btn btn-outline-light" style="border-radius: 20px; padding-left: 20px; padding-right: 20px;">
                    📦 Track Order
                </a>

                <a href="loginform.php" class="btn btn-light" style="border-radius: 20px; padding-left: 20px; padding-right: 20px; font-weight: 600;">Login</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <h1 class="fw-bold">Welcome to AJ&A Shop</h1>
        <p>Find your favorite products at the best prices</p>
        <a href="#products" class="btn btn-light mt-3 fw-bold">Shop Now</a>
    </section>

    <div class="container mb-5 text-center">
        <form method="GET">
            <select name="filter" class="form-select w-auto d-inline-block" style="border-radius: 20px; padding: 10px 40px 10px 20px; border: 2px solid #eee;">
                <option value="all">All Categories</option>
                <?php
                // 1. Separate Parents from array
                $parents = array_filter($all_categories, function ($c) {
                    return $c['parent_id'] == NULL;
                });

                foreach ($parents as $parent):
                    $isSelected = ($filter == $parent['id']) ? 'selected' : '';
                ?>
                    <option value="<?= $parent['id'] ?>" <?= $isSelected ?> style="font-weight: bold; color: #b2935b;">
                        <?= htmlspecialchars($parent['name']) ?>
                    </option>

                    <?php
                    // 2. Find Children for this parent
                    $children = array_filter($all_categories, function ($c) use ($parent) {
                        return $c['parent_id'] == $parent['id'];
                    });

                    foreach ($children as $child):
                        $isChildSelected = ($filter == $child['id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $child['id'] ?>" <?= $isChildSelected ?>>
                            &nbsp;&nbsp;&nbsp;↳ <?= htmlspecialchars($child['name']) ?>
                        </option>
                    <?php endforeach; ?>

                <?php endforeach; ?>
            </select>
            <button class="btn btn-dark ms-2" type="submit" style="border-radius: 20px; padding: 10px 25px;">Filter</button>
        </form>
    </div>

    <div class="container" id="products">
        <div class="row g-4">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    $imageList = explode(',', $row['image']);
                    $firstImage = trim($imageList[0]);

                    // --- FETCH VARIATIONS (Needed for Modal Logic, but not display) ---
                    $v_res = mysqli_query($conn, "SELECT size, color, quantity FROM product_variations WHERE product_id='" . $row['id'] . "'");
                    $p_vars = [];
                    while ($vr = mysqli_fetch_assoc($v_res)) {
                        $p_vars[] = $vr;
                    }
                    $json_vars = htmlspecialchars(json_encode($p_vars), ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="col-md-3">
                        <div class="card product-card h-100">
                            <a href="view-product.php?id=<?= $row['id'] ?>">
                                <img src="<?= htmlspecialchars($firstImage); ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text text-muted mb-2">₱<?= number_format($row['price'], 2); ?></p>

                                <div class="mb-3">
                                    <?php if ($row['quantity'] > 0): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i> In Stock
                                        </span>
                                        <div class="small text-muted mt-1"><?= $row['quantity'] ?> items available</div>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill">
                                            <i class="fas fa-times-circle me-1"></i> Out of Stock
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-auto">
                                    <?php if ($row['quantity'] > 0): ?>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="checkout.php?product_id=<?= $row['id']; ?>" class="btn btn-dark flex-fill btn-sm">Buy Now</a>

                                            <button type="button"
                                                class="btn btn-outline-dark flex-fill btn-sm add-to-cart-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addToCartModal"
                                                data-product-id="<?= $row['id'] ?>"
                                                data-product-name="<?= htmlspecialchars($row['name']) ?>"
                                                data-product-price="<?= $row['price'] ?>"
                                                data-product-image="<?= htmlspecialchars($firstImage) ?>"
                                                data-product-stock="<?= $row['quantity'] ?>"
                                                data-variations="<?= $json_vars ?>">
                                                🛒
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-grid">
                                            <span class="btn btn-danger disabled rounded-pill">Out of Stock</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted py-5">No products found.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addToCartModalLabel">Select Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="add_to_cart.php" method="POST" id="modalCartForm">
                        <input type="hidden" name="product_id" id="modalProductId">

                        <div class="d-flex align-items-center mb-4">
                            <img src="" id="modalProductImage" class="modal-product-image me-4" alt="Product">
                            <div>
                                <h5 id="modalProductName" class="fw-bold mb-1">Product Name</h5>
                                <h4 id="modalProductPrice" style="color: #b2935b; font-weight: 700;">₱0.00</h4>
                            </div>
                        </div>

                        <div id="modalVariationSelectors"></div>

                        <div class="mb-4">
                            <label for="modalQuantity" class="form-label fw-semibold">
                                Quantity <span id="modalStockDisplay" class="stock-display-modal text-muted"></span>
                            </label>
                            <input type="number" name="quantity" id="modalQuantity" class="form-control" value="1" min="1" max="1" required style="border-radius: 10px; padding: 10px;">
                        </div>

                        <button type="submit" id="modalAddToCartBtn" class="btn btn-brand w-100">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Detect Page Type
            const isModal = document.getElementById('addToCartModal') !== null;

            function initProductLogic(container, variationsJson, initialStock) {
                let productVariations = [];
                try {
                    productVariations = typeof variationsJson === 'string' ? JSON.parse(variationsJson) : variationsJson;
                } catch (e) {}

                const variationSelectorsDiv = container.querySelector(isModal ? '#modalVariationSelectors' : '#variation-container');
                const stockDisplay = container.querySelector(isModal ? '#modalStockDisplay' : '#dynamic-stock-badge');
                const addToCartBtn = container.querySelector(isModal ? '#modalAddToCartBtn' : '#add-to-cart-btn');
                const buyNowBtn = container.querySelector('#buy-now-btn');
                const quantityInput = container.querySelector(isModal ? '#modalQuantity' : '#quantity');

                // Reset UI if Modal
                if (isModal) {
                    variationSelectorsDiv.innerHTML = '';
                    if (stockDisplay) stockDisplay.textContent = '';
                    if (quantityInput) quantityInput.value = 1;
                }

                const normalize = (str) => str ? str.toString().trim().toLowerCase() : '';

                // Extract Unique Attributes
                let allSizes = [],
                    allColors = [];
                if (productVariations.length > 0) {
                    allSizes = [...new Set(productVariations.map(v => v.size).filter(s => s))];
                    allColors = [...new Set(productVariations.map(v => v.color).filter(c => c))];
                }

                let sizeSelect = null,
                    colorSelect = null;

                // Create Dropdowns Structure (Only once)
                if (allSizes.length > 0) {
                    const div = document.createElement('div');
                    div.className = "mb-3";
                    div.innerHTML = `<label class="form-label fw-semibold">Size</label>
            <select name="size" class="form-select size-select" required><option value="">-- Select Size --</option></select>`;
                    variationSelectorsDiv.appendChild(div);
                    sizeSelect = div.querySelector('select');
                }
                if (allColors.length > 0) {
                    const div = document.createElement('div');
                    div.className = "mb-3";
                    div.innerHTML = `<label class="form-label fw-semibold">Color</label>
            <select name="color" class="form-select color-select" required><option value="">-- Select Color --</option></select>`;
                    variationSelectorsDiv.appendChild(div);
                    colorSelect = div.querySelector('select');
                }

                // --- CORE LOGIC: RENDER WITH DISABLED OPTIONS ---
                function updateDropdownOptions() {
                    let curSize = sizeSelect ? sizeSelect.value : '';
                    let curColor = colorSelect ? colorSelect.value : '';

                    // Update SIZE Options
                    if (sizeSelect) {
                        // Save current selection to restore if valid, else reset
                        let tempSize = curSize;
                        sizeSelect.innerHTML = '<option value="">-- Select Size --</option>';

                        allSizes.forEach(size => {
                            let isDisabled = false;
                            let label = size;

                            // Kung may nakapili na na Color, check kung available itong Size na 'to para sa color na yun
                            if (curColor && productVariations.length > 0) {
                                const variant = productVariations.find(v => normalize(v.size) === normalize(size) && normalize(v.color) === normalize(curColor));
                                if (!variant || parseInt(variant.quantity) <= 0) {
                                    isDisabled = true;
                                    label += " (Unavailable)";
                                }
                            }

                            let opt = document.createElement('option');
                            opt.value = size;
                            opt.textContent = label;
                            if (isDisabled) opt.disabled = true; // 🔒 GRAYED OUT
                            sizeSelect.appendChild(opt);
                        });
                        sizeSelect.value = tempSize; // Restore selection
                    }

                    // Update COLOR Options
                    if (colorSelect) {
                        let tempColor = curColor;
                        colorSelect.innerHTML = '<option value="">-- Select Color --</option>';

                        allColors.forEach(color => {
                            let isDisabled = false;
                            let label = color;

                            // Kung may nakapili na na Size, check kung available itong Color na 'to para sa size na yun
                            if (curSize && productVariations.length > 0) {
                                const variant = productVariations.find(v => normalize(v.size) === normalize(curSize) && normalize(v.color) === normalize(color));
                                if (!variant || parseInt(variant.quantity) <= 0) {
                                    isDisabled = true;
                                    label += " (Unavailable)";
                                }
                            }

                            let opt = document.createElement('option');
                            opt.value = color;
                            opt.textContent = label;
                            if (isDisabled) opt.disabled = true; // 🔒 GRAYED OUT
                            colorSelect.appendChild(opt);
                        });
                        colorSelect.value = tempColor; // Restore selection
                    }
                }

                function checkStock() {
                    let selSize = sizeSelect ? sizeSelect.value : null;
                    let selColor = colorSelect ? colorSelect.value : null;
                    let maxQty = initialStock;
                    let foundVariant = null;

                    if (productVariations.length > 0) {
                        foundVariant = productVariations.find(v => normalize(v.size) === normalize(selSize) && normalize(v.color) === normalize(selColor));

                        if (foundVariant) {
                            maxQty = parseInt(foundVariant.quantity);
                            let msg = maxQty > 0 ? `(Stock: ${maxQty})` : `(Out of Stock)`;
                            let cls = maxQty > 0 ? "text-success fw-bold ms-2" : "text-danger fw-bold ms-2";

                            if (isModal) {
                                stockDisplay.textContent = msg;
                                stockDisplay.className = cls;
                            } else {
                                stockDisplay.className = 'stock-indicator ' + (maxQty > 0 ? 'stock-plenty' : 'stock-none') + ' show';
                                stockDisplay.innerHTML = maxQty > 0 ? `<i class="fas fa-check-circle"></i> In Stock: ${maxQty}` : `<i class="fas fa-times-circle"></i> Out of Stock`;
                            }
                        } else {
                            if (isModal) stockDisplay.textContent = "";
                            else if (stockDisplay) stockDisplay.classList.remove('show');
                        }
                    }

                    if (quantityInput) {
                        quantityInput.max = (maxQty > 0) ? maxQty : 1;
                        if (parseInt(quantityInput.value) > maxQty && maxQty > 0) quantityInput.value = maxQty;
                    }

                    const isSelectionComplete = (productVariations.length === 0) || (productVariations.length > 0 && selSize && selColor);

                    if (maxQty <= 0 && isSelectionComplete) {
                        if (addToCartBtn) {
                            addToCartBtn.disabled = true;
                            addToCartBtn.textContent = "Out of Stock";
                        }
                        if (buyNowBtn) {
                            buyNowBtn.disabled = true;
                            buyNowBtn.textContent = "Out of Stock";
                        }
                        if (quantityInput) quantityInput.value = 0;
                    } else {
                        if (!isSelectionComplete) {
                            if (addToCartBtn) {
                                addToCartBtn.disabled = true;
                                addToCartBtn.textContent = "Select Options";
                            }
                            if (buyNowBtn) {
                                buyNowBtn.disabled = true;
                                buyNowBtn.textContent = "Select Options";
                            }
                        } else {
                            if (addToCartBtn) {
                                addToCartBtn.disabled = false;
                                addToCartBtn.textContent = "Add to Cart";
                            }
                            if (buyNowBtn) {
                                buyNowBtn.disabled = false;
                                buyNowBtn.innerHTML = 'Buy Now <input type="hidden" name="buy_now" value="true">';
                            }
                        }
                    }
                }

                // Event Listeners (Trigger Update on Change)
                if (sizeSelect) sizeSelect.addEventListener('change', () => {
                    updateDropdownOptions();
                    checkStock();
                });
                if (colorSelect) colorSelect.addEventListener('change', () => {
                    updateDropdownOptions();
                    checkStock();
                });

                // Initial Render
                updateDropdownOptions();
                checkStock();
            }

            // --- HOOK INTO MODAL (Index.php) ---
            if (isModal) {
                const addToCartModal = document.getElementById('addToCartModal');
                addToCartModal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;
                    const modal = addToCartModal;

                    modal.querySelector('#modalProductId').value = button.getAttribute('data-product-id');
                    modal.querySelector('#modalProductImage').src = button.getAttribute('data-product-image');
                    modal.querySelector('#modalProductName').textContent = button.getAttribute('data-product-name');
                    modal.querySelector('#modalProductPrice').textContent = `₱${parseFloat(button.getAttribute('data-product-price')).toFixed(2)}`;

                    initProductLogic(modal, button.getAttribute('data-variations'), parseInt(button.getAttribute('data-product-stock')));
                });
            }
            // --- HOOK INTO VIEW PRODUCT PAGE ---
            else {
                const container = document.getElementById('main-product-form').parentElement;
                if (typeof productVariations !== 'undefined') {
                    initProductLogic(document, productVariations, productTotalStock);
                }
            }
        });
    </script>
</body>

</html>
<?php mysqli_close($conn); ?>