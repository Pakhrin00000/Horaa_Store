        </div> <!-- /.container -->
    </main>

    <?php if (basename($_SERVER['PHP_SELF']) !== 'order-success.php'): ?>
    <!-- FOOTER SECTION -->
    <footer class="horaa-footer">
        <div class="container">
            <div class="row g-4 mb-5">
                
                <!-- Brand Info -->
                <div class="col-lg-4 col-md-6">
                    <a href="<?php echo url('index.php'); ?>" class="brand-logo d-inline-block mb-3 p-2 px-3 bg-white rounded-3 shadow-sm">
                        <img src="https://horaaesports.com.np/images/HORAA-ESPORTSBlack.png" onerror="this.onerror=null;this.src='<?php echo url('assets/images/horaa-logo.png'); ?>';" alt="HORAA Esports" style="height: 38px; width: auto; object-fit: contain; display: block;">
                    </a>
                    <p class="text-secondary small pe-lg-4 mb-3" style="color: #cbd5e1 !important;">
                        <strong>HORAA STORE</strong> — “Gaming • Tech • Digital — Everything in One Place”.
                        The ultimate destination for competitive gamers, tech enthusiasts, and digital creators.
                    </p>
                    <div class="d-flex gap-3 fs-5">
                        <a href="#" class="text-purple"><i class="fab fa-discord"></i></a>
                        <a href="#" class="text-purple"><i class="fab fa-twitch"></i></a>
                        <a href="#" class="text-purple"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="text-purple"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-purple"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <!-- Categories Column -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Top Categories</h5>
                    <ul class="footer-links">
                        <?php
                        global $db;
                        $footerCats = [];
                        if (isset($db)) {
                            try {
                                $fStmt = $db->query("SELECT id, name, icon FROM categories ORDER BY id ASC LIMIT 5");
                                if ($fStmt) $footerCats = $fStmt->fetchAll();
                            } catch (Exception $e) {}
                        }
                        ?>
                        <?php if (!empty($footerCats)): ?>
                            <?php foreach ($footerCats as $fCat): ?>
                                <li>
                                    <a href="<?php echo url('shop.php?cat=' . $fCat['id']); ?>">
                                        <i class="<?php echo sanitize($fCat['icon'] ?: 'fas fa-tag'); ?> text-purple me-2"></i>
                                        <?php echo sanitize($fCat['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a href="<?php echo url('shop.php?cat=1'); ?>"><i class="fas fa-tshirt text-purple me-2"></i> Esports Jerseys & Merch</a></li>
                            <li><a href="<?php echo url('shop.php?cat=2'); ?>"><i class="fas fa-gamepad text-purple me-2"></i> Gaming Gear</a></li>
                            <li><a href="<?php echo url('shop.php?cat=3'); ?>"><i class="fas fa-desktop text-purple me-2"></i> PCs & Laptops</a></li>
                            <li><a href="<?php echo url('shop.php?cat=5'); ?>"><i class="fas fa-keyboard text-purple me-2"></i> Keyboards & Mice</a></li>
                            <li><a href="<?php echo url('shop.php?cat=11'); ?>"><i class="fas fa-key text-purple me-2"></i> Digital Products</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Customer Care Column -->
                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo url('index.php'); ?>"><i class="fas fa-house text-purple me-2"></i> Home</a></li>
                        <li><a href="<?php echo url('shop.php'); ?>"><i class="fas fa-store text-purple me-2"></i> All Products</a></li>
                        <li><a href="<?php echo url('cart.php'); ?>"><i class="fas fa-shopping-cart text-purple me-2"></i> Shopping Cart</a></li>
                        <li><a href="<?php echo url('wishlist.php'); ?>"><i class="fas fa-heart text-purple me-2"></i> Saved Wishlist</a></li>
                        <li><a href="<?php echo url('profile.php'); ?>"><i class="fas fa-user-circle text-purple me-2"></i> My Orders</a></li>
                        <li><a href="<?php echo url('admin/index.php'); ?>"><i class="fas fa-gauge-high text-purple me-2"></i> Admin Panel</a></li>
                    </ul>
                </div>

                <!-- Contact & Payments -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Store Info & Support</h5>
                    <ul class="list-unstyled small mb-3" style="color: #cbd5e1;">
                        <li class="mb-2"><i class="fas fa-map-marker-alt text-purple me-2"></i> Esports HQ Towers, Neo Tokyo</li>
                        <li class="mb-2"><i class="fas fa-envelope text-purple me-2"></i> support@horaa.store</li>
                        <li class="mb-2"><i class="fas fa-phone text-purple me-2"></i> +1 (555) 019-2831 (24/7 Support)</li>
                    </ul>
                    <div class="border-top border-secondary pt-3 mt-3">
                        <span class="small d-block mb-2 text-white">Accepted Payment Methods:</span>
                        <div class="d-flex gap-2 fs-4 text-purple">
                            <i class="fab fa-cc-visa" title="Visa"></i>
                            <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                            <i class="fab fa-cc-paypal" title="PayPal"></i>
                            <i class="fab fa-bitcoin text-gold" title="Crypto / Esports Pay"></i>
                        </div>
                    </div>
                </div>

            </div>

            <!-- COPYRIGHT BAR -->
            <div class="border-top border-secondary pt-3 d-flex flex-column flex-md-row align-items-center justify-content-between text-muted small">
                <div>&copy; <?php echo date('Y'); ?> <strong>HORAA STORE</strong>. All Rights Reserved.</div>
                <div class="mt-2 mt-md-0">
                    <span class="me-3"><i class="fas fa-shield-halved text-cyan me-1"></i> SSL 256-Bit Encrypted</span>
                    <span><i class="fas fa-truck-fast text-cyan me-1"></i> Global Express Shipping</span>
                </div>
            </div>

        </div>
    </footer>
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom HORAA Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
