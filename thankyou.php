<?php
include_once('config/dbcon.php');
include_once('config/function.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = intval($_GET['order_id']);

// Fetch order details
$order_query = mysqli_query($con, "SELECT * FROM orders WHERE id = '$order_id' LIMIT 1");
$order = mysqli_fetch_assoc($order_query);

if (!$order) {
    echo "<h3>Order not found.</h3>";
    exit;
}

// Fetch order items
$item_query = mysqli_query($con, "
    SELECT oi.*, p.name AS product_name, p.image AS product_image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = '$order_id'
");

// ✅ Shipping fee is 0
$shipping_fee = 0.00;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Confirmed | AJnA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-brand-gold: #b5975b;
            --color-dark-text: #222;
            --color-light-text: #555;
            --color-background: #f9f9f9;
            --color-white: #fff;
            --color-border: #eee;
        }

        body {
            background-color: var(--color-background);
            font-family: "Poppins", sans-serif;
            color: var(--color-dark-text);
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Playfair Display', serif;
        }

        /* Navbar Fix */
        .navbar {
            background-color: var(--color-brand-gold) !important;
            padding: 15px 0;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
        }

        /* Main Layout */
        .thankyou-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 20px;
        }

        /* Card Styling */
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
            background-color: var(--color-white);
            overflow: hidden;
        }

        /* Info Section */
        .info-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--color-dark-text);
            margin-bottom: 20px;
        }

        /* Table Styling */
        .table thead th {
            background-color: #f8f9fa;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-bottom: 2px solid #eee;
            padding: 15px;
        }

        .table tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .product-name {
            font-weight: 600;
            color: #333;
            display: block;
        }

        .product-meta {
            font-size: 0.85rem;
            color: #777;
        }

        /* Total Section */
        .total-row td {
            border-top: 2px solid #eee;
            padding-top: 15px;
        }

        .grand-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-brand-gold);
            font-family: 'Poppins', sans-serif;
        }

        /* Buttons */
        .btn-home {
            background-color: var(--color-brand-gold);
            color: white;
            border-radius: 50px;
            padding: 12px 35px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            display: inline-block;
            text-decoration: none;
            border: none;
        }

        .btn-home:hover {
            background-color: #8e7343;
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 5px 15px rgba(181, 151, 91, 0.3);
        }

        .btn-track {
            background-color: transparent;
            color: var(--color-brand-gold);
            border: 2px solid var(--color-brand-gold);
            border-radius: 50px;
            padding: 12px 35px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            display: inline-block;
            text-decoration: none;
        }

        .btn-track:hover {
            background-color: var(--color-brand-gold);
            color: white;
        }

        .status-badge {
            background-color: #fff3cd;
            color: #856404;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid #ffeeba;
        }

        /* --- NEW TRACKING BOX STYLE --- */
        .tracking-box {
            background-color: #fffcf5;
            /* Very light gold tint */
            border: 2px dashed var(--color-brand-gold);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 40px;
            text-align: center;
        }

        .tracking-id-display {
            font-size: 2rem;
            font-weight: 800;
            color: var(--color-brand-gold);
            letter-spacing: 2px;
            margin: 5px 0;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body>
    <?php include('include2/navbarr.php'); ?>

    <div class="container thankyou-container">

        <div class="card mb-5">
            <div class="text-center pt-5 pb-4 px-4">
                <div class="mb-3">
                    <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" alt="Success" style="width: 80px; opacity: 0.9;">
                </div>
                <h1 class="fw-bold mb-2" style="color: var(--color-brand-gold);">Order Confirmed!</h1>
                <p class="text-muted fs-5">Thank you for shopping with AJ&A.</p>
            </div>

            <hr style="margin: 0 40px; opacity: 0.1;">

            <div class="card-body p-4 p-md-5">

                <div class="tracking-box">
                    <p class="mb-1 text-uppercase fw-bold text-muted small" style="letter-spacing: 1px;">Important: Save for Tracking</p>

                    <div class="tracking-id-display">
                        #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                    </div>

                    <p class="mb-3 text-muted">
                        Linked Email: <strong class="text-dark"><?= htmlspecialchars($order['customer_email']); ?></strong>
                    </p>

                    <div class="alert alert-warning d-inline-block py-2 px-3 mb-0 border-0" style="background-color: #fff3cd; color: #856404; font-size: 0.85rem; border-radius: 10px;">
                        <i class="fas fa-info-circle me-1"></i>
                        Please screenshot or copy this <b>Order ID</b> to track your delivery status.
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-label">Order Number</div>
                        <div class="info-value">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>

                        <div class="info-label">Status</div>
                        <div class="info-value"><span class="status-badge"><?= ucfirst($order['status']); ?></span></div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="info-label">Date</div>
                        <div class="info-value"><?= date("F j, Y, g:i a", strtotime($order['created_at'])); ?></div>

                        <div class="info-label">Payment Method</div>
                        <div class="info-value">
                            <?= htmlspecialchars($order['payment_method']); ?>
                            <?php if ($order['payment_method'] === 'GCash'): ?>
                                <br><small class="text-muted" style="font-size: 0.8rem;">Ref No: <?= htmlspecialchars($order['pay_ref_number'] ?? 'N/A'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row mb-5 p-3 rounded-3" style="background-color: #f8f9fa;">
                    <div class="col-md-6">
                        <div class="info-label">Shipping Address</div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($order['customer_name']); ?></div>
                        <div class="text-muted"><?= nl2br(htmlspecialchars($order['customer_address'])); ?></div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="info-label">Contact Info</div>
                        <div class="text-dark"><?= htmlspecialchars($order['customer_email']); ?></div>
                        <div class="text-dark"><?= htmlspecialchars($order['customer_phone']); ?></div>
                    </div>
                </div>

                <h4 class="mb-3 fw-bold">Order Summary</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="ps-0">Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $subtotal = 0;
                            while ($item = mysqli_fetch_assoc($item_query)):
                                $line_total = $item['price'] * $item['quantity'];
                                $subtotal += $line_total;

                                $images = explode(',', $item['product_image']);
                                $first_image = trim($images[0]);
                            ?>
                                <tr>
                                    <td class="ps-0">
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($first_image)): ?>
                                                <img src="<?= htmlspecialchars($first_image); ?>" class="product-img me-3">
                                            <?php endif; ?>
                                            <div>
                                                <span class="product-name"><?= htmlspecialchars($item['product_name']); ?></span>
                                                <div class="product-meta">
                                                    <?php if (!empty($item['size'])) echo "Size: " . htmlspecialchars($item['size']) . " | "; ?>
                                                    <?php if (!empty($item['color'])) echo "Color: " . htmlspecialchars($item['color']); ?>
                                                </div>
                                                <div class="product-meta mt-1">
                                                    ₱<?= number_format($item['price'], 2); ?> each
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= $item['quantity']; ?></td>
                                    <td class="text-end fw-bold">₱<?= number_format($line_total, 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2" class="text-end border-0 pt-3 text-dark fw-bold fs-5">Total Amount</td>
                                <td class="text-end border-0 pt-3 grand-total">₱<?= number_format($subtotal, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if ($order['payment_method'] === 'GCash'): ?>
                    <div class="mt-5 p-4 border rounded-3 text-center bg-light">
                        <h5 class="fw-bold text-success mb-2"><i class="fas fa-check-circle me-2"></i>Payment Verification Pending</h5>
                        <p class="text-muted mb-0">
                            We have received your Reference Number: <strong><?= htmlspecialchars($order['pay_ref_number'] ?? '-------'); ?></strong>.
                            <br>We will process your order once the payment is verified.
                        </p>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-5 d-flex flex-column flex-md-row justify-content-center gap-3">
                    <a href="track-order.php" class="btn-track">📦 Track Order</a>
                    <a href="index.php" class="btn-home">Continue Shopping</a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>

</html>