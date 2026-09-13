<?php
// cart.php - HORAA STORE Shopping Cart
$pageTitle = "Shopping Cart - HORAA STORE";
require_once __DIR__ . '/includes/header.php';

// Handle Cart Actions (Update / Remove / Clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        $cartId = (int)$_POST['cart_id'];
        $newQty = max(1, (int)$_POST['quantity']);
        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQty, $cartId]);
        set_flash('success', 'Cart updated.');
        header('Location: cart.php');
        exit;
    }
    if (isset($_POST['remove_item'])) {
        $cartId = (int)$_POST['cart_id'];
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cartId]);
        set_flash('success', 'Item removed from cart.');
        header('Location: cart.php');
        exit;
    }
    if (isset($_POST['remove_coupon'])) {
        unset($_SESSION['applied_coupon']);
        set_flash('info', 'Coupon removed.');
        header('Location: cart.php');
        exit;
    }
}

$cartItems = get_cart_items();
$subtotal = get_cart_subtotal();

// Discount Calculation
$discountAmount = 0.00;
if (isset($_SESSION['applied_coupon'])) {
    $coupon = $_SESSION['applied_coupon'];
    if ($coupon['type'] === 'percent') {
        $discountAmount = ($subtotal * $coupon['value']) / 100;
    } else {
        $discountAmount = min($subtotal, $coupon['value']);
    }
}

// Shipping Fee ($0 if subtotal >= 99 else $10)
$shippingFee = ($subtotal >= 99 || $subtotal == 0) ? 0.00 : 10.00;
$finalTotal = max(0, $subtotal - $discountAmount) + $shippingFee;
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <h2 class="text-white mb-0 font-monospace">
        <i class="fas fa-shopping-cart text-cyan me-2"></i> Shopping Cart 
        <small class="text-muted fs-6">(<?php echo count($cartItems); ?> items)</small>
    </h2>
    <a href="shop.php" class="btn btn-sm btn-outline-cyber"><i class="fas fa-arrow-left me-1"></i> Continue Shopping</a>
</div>

<?php if (empty($cartItems)): ?>
    <div class="text-center py-5 bg-card rounded-4 border border-secondary my-4">
        <i class="fas fa-cart-arrow-down text-muted display-1 mb-3"></i>
        <h4 class="text-white">Your Cart is Empty</h4>
        <p class="text-muted">Looks like you haven't added any gaming gear or digital keys yet.</p>
        <a href="shop.php" class="btn-cyan mt-3"><i class="fas fa-gamepad me-2"></i> Start Shopping</a>
    </div>
<?php else: ?>
    <div class="row g-4 mb-5">
        
        <!-- CART ITEMS TABLE -->
        <div class="col-lg-8">
            <div class="bg-card rounded-4 border border-cyan overflow-hidden p-3">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): 
                            $unitPrice = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                            $lineTotal = $unitPrice * $item['quantity'];
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?php echo sanitize($item['image'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo sanitize($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" class="border border-secondary">
                                        <div>
                                            <a href="product.php?id=<?php echo $item['id']; ?>" class="text-white fw-bold d-block text-decoration-none hover-cyan">
                                                <?php echo sanitize($item['name']); ?>
                                            </a>
                                            <span class="badge-cyber small"><?php echo sanitize($item['category_name']); ?></span>
                                            <?php if ($item['is_digital']): ?>
                                                <span class="badge-magenta small ms-1"><i class="fas fa-key"></i> Digital</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-monospace text-cyan fw-bold">
                                    <?php echo format_price($unitPrice); ?>
                                </td>
                                <td>
                                    <form action="cart.php" method="POST" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="form-control form-control-sm bg-dark text-white border-cyan text-center" style="width: 65px;" onchange="this.form.submit()">
                                        <input type="hidden" name="update_cart" value="1">
                                    </form>
                                </td>
                                <td class="font-monospace text-white fw-bold">
                                    <?php echo format_price($lineTotal); ?>
                                </td>
                                <td class="text-end">
                                    <form action="cart.php" method="POST" onsubmit="return confirm('Remove this item?');">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-sm btn-outline-danger border-0" title="Remove">
                                            <i class="fas fa-trash-can fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ORDER SUMMARY & COUPON SIDEBAR -->
        <div class="col-lg-4">
            
            <!-- Coupon Code Box -->
            <div class="order-summary-card mb-4">
                <h5 class="text-cyan mb-3"><i class="fas fa-ticket me-2"></i> Discount Voucher</h5>
                
                <?php if (isset($_SESSION['applied_coupon'])): ?>
                    <div class="p-3 bg-glass rounded-3 border border-magenta d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge-magenta fw-bold me-2"><?php echo sanitize($_SESSION['applied_coupon']['code']); ?></span>
                            <small class="text-muted">Applied</small>
                        </div>
                        <form action="cart.php" method="POST">
                            <button type="submit" name="remove_coupon" class="btn btn-sm btn-link text-magenta p-0"><i class="fas fa-times-circle"></i> Remove</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="input-group">
                        <input type="text" id="coupon-input" class="form-control form-control-cyber" placeholder="Enter Code (e.g. HORAA10)">
                        <button type="button" onclick="applyCouponCode()" class="btn btn-cyan">Apply</button>
                    </div>
                    <small class="text-muted d-block mt-2">Try codes: <strong class="text-cyan">HORAA10</strong> or <strong class="text-magenta">GAMER20</strong></small>
                <?php endif; ?>
            </div>

            <!-- Order Summary Card -->
            <div class="order-summary-card">
                <h5 class="text-white mb-3"><i class="fas fa-receipt text-cyan me-2"></i> Order Summary</h5>
                
                <div class="summary-row">
                    <span class="text-muted">Subtotal</span>
                    <span class="text-white font-monospace fw-bold"><?php echo format_price($subtotal); ?></span>
                </div>

                <?php if ($discountAmount > 0): ?>
                    <div class="summary-row text-magenta">
                        <span>Discount Voucher</span>
                        <span class="font-monospace fw-bold">-<?php echo format_price($discountAmount); ?></span>
                    </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span class="text-muted">Estimated Shipping</span>
                    <span class="text-white font-monospace">
                        <?php echo $shippingFee == 0 ? '<span class="text-cyan fw-bold">FREE</span>' : format_price($shippingFee); ?>
                    </span>
                </div>

                <div class="summary-row border-top border-secondary pt-3 mt-2 fs-5">
                    <span class="text-white fw-bold">Total Amount</span>
                    <span class="text-cyan font-monospace fw-bold fs-4"><?php echo format_price($finalTotal); ?></span>
                </div>

                <a href="checkout.php" class="btn-magenta w-100 mt-4 text-center justify-content-center">
                    <i class="fas fa-shield-check me-2"></i> Proceed To Checkout
                </a>
            </div>

        </div>

    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
