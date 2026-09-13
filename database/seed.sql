-- HORAA STORE Seed Data

USE `horaa_store`;

-- Clear existing data (in correct FK order)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `cart`;
TRUNCATE TABLE `wishlist`;
TRUNCATE TABLE `reviews`;
TRUNCATE TABLE `coupons`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS (Admin password: admin123, User password: user123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role`) VALUES
(1, 'HORAA Admin', 'admin@horaa.com', '$2y$10$p4syZfKscZiN4MCS3B1bZuSFS5y.XSI/fQ17O6A1DjP0C3tq13Yky', '+1 (555) 019-2831', 'Esports HQ Towers, Cyber Plaza', 'Neo Tokyo', '10001', 'admin'),
(2, 'Alex Mercer (Gamer)', 'gamer@horaa.com', '$2y$10$Y5hoBXXzxbyb8KmEUhQTq../EQk.yTf/vV1RlGTpAIO9Nx8TKKoxS', '+1 (555) 012-9988', '404 Cyber Stream Way', 'Los Angeles', '90001', 'user');

-- 2. CATEGORIES
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `image`) VALUES
(1, 'Esports Jerseys & Merch', 'esports-jerseys', 'Official HORAA Pro Esports Apparel, Hoodies, and Merchandise', 'fas fa-tshirt', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR-mwlNRs8Z21M5ysZ--Bg4UuoaGDFPDGSO2h0GE7zGeRMGfGWSzFI_tG8&s=10'),
(2, 'Gaming Gear', 'gaming-gear', 'Pro Esports Grade Accessories, Controllers & Mousepads', 'fas fa-gamepad', 'assets/images/category-gear.jpg'),
(3, 'PCs & Laptops', 'pcs-laptops', 'High Performance Custom Rig PCs and Gaming Laptops', 'fas fa-laptop', 'assets/images/category-pcs.jpg'),
(4, 'PC Components', 'pc-components', 'GPUs, Processors, RAM, Motherboards & Cooling Systems', 'fas fa-microchip', 'assets/images/category-components.jpg'),
(5, 'Keyboards & Mice', 'keyboards-mice', 'Mechanical Keyboards, Ultra-Lightweight Mice & Switches', 'fas fa-keyboard', 'assets/images/category-keyboards.jpg'),
(6, 'Headsets & Audio', 'headsets-audio', '7.1 Surround Headsets, Studio Mics & Wireless Audio', 'fas fa-headset', 'assets/images/category-audio.jpg'),
(7, 'Cables & Hubs', 'cables-hubs', 'Braided High-Speed USB-C Cables, Splitters & Docking Stations', 'fas fa-plug', 'assets/images/category-cables.jpg'),
(8, 'Chargers & Power', 'chargers-power', 'GaN Fast Chargers, Power Banks & Wireless Charging Pads', 'fas fa-bolt', 'assets/images/category-chargers.jpg'),
(9, 'Electronics & Gadgets', 'electronics-gadgets', 'Smart Streamer Gadgets, LED Lighting & Capture Cards', 'fas fa-tv', 'assets/images/category-electronics.jpg'),
(10, 'Accessories', 'accessories', 'Backpacks, Cable Ties, Headset Stands & Cleaning Kits', 'fas fa-toolbox', 'assets/images/category-accessories.jpg'),
(11, 'Digital Products', 'digital-products', 'Instant Delivery Game Keys, Gift Cards & Subscriptions', 'fas fa-key', 'assets/images/category-digital.jpg');

-- 3. PRODUCTS
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `short_description`, `description`, `specifications`, `price`, `sale_price`, `stock`, `sku`, `image`, `additional_images`, `is_featured`, `is_trending`, `is_digital`, `digital_file_url`) VALUES
(1, 1, 'HORAA Pro Esports Official Jersey 2026 Edition', 'horaa-pro-esports-official-jersey-2026', 'Breathable pro-fit athletic jersey with dynamic RGB cyan accents & custom sponsor sleeve patches.', 'Dominate the arena with the official 2026 HORAA Pro Esports Jersey. Built from ultra-light moisture-wicking micro-mesh polymer, designed for competitive gamers who demand max ventilation during intense tournaments.', '{"Material": "100% Breathable Micro-Mesh", "Fit": "Athletic Pro-Fit", "Sizes": "S, M, L, XL, XXL", "Feature": "Anti-Odor & Quick Dry"}', 69.99, 54.99, 45, 'HOR-JRS-2026', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR-mwlNRs8Z21M5ysZ--Bg4UuoaGDFPDGSO2h0GE7zGeRMGfGWSzFI_tG8&s=10', '["https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR-mwlNRs8Z21M5ysZ--Bg4UuoaGDFPDGSO2h0GE7zGeRMGfGWSzFI_tG8&s=10"]', 1, 1, 0, NULL),

(2, 5, 'HORAA Apex Pro RGB Mechanical Keyboard', 'horaa-apex-pro-rgb-mechanical-keyboard', 'Hot-swappable optical magnetic switches with 0.1mm actuation & per-key RGB lighting.', 'Experience instant speed with HORAA Apex Pro. Custom magnetic switches allow adjustable actuation from 0.1mm to 4.0mm, wrapped in an aircraft-grade aluminum top plate with dynamic neon backlighting.', '{"Switches": "HORAA Optical Magnetic", "Actuation": "0.1mm to 4.0mm Adjustable", "Backlight": "Per-key RGB 16.8M Colors", "Connection": "Detachable Type-C"}', 149.99, 129.99, 30, 'HOR-KB-APEX', 'assets/images/product-keyboard-1.jpg', '["assets/images/product-keyboard-1.jpg"]', 1, 1, 0, NULL),

(3, 5, 'HORAA VaporLight 8K Wireless Gaming Mouse', 'horaa-vaporlight-8k-wireless-mouse', 'Ultra-lightweight 49g competitive mouse with 8000Hz polling rate & 30,000 DPI sensor.', 'Engineered for pixel-perfect aim tracking in FPS titles. Weighing just 49 grams with zero lag 8K Hz wireless transmission and optical microswitches rated for 90M clicks.', '{"Weight": "49g Ultra-Light", "Polling Rate": "8000Hz Wireless", "Sensor": "HORAA PAW3395 30K DPI", "Battery Life": "Up to 80 Hours"}', 99.99, 84.99, 60, 'HOR-MS-VAPOR', 'assets/images/product-mouse-1.jpg', '["assets/images/product-mouse-1.jpg"]', 1, 1, 0, NULL),

(4, 6, 'HORAA Quantum 7.1 Wireless Spatial Gaming Headset', 'horaa-quantum-71-wireless-headset', 'Lossless 2.4GHz + Bluetooth 5.3 headset with active noise cancelling mic & memory foam earcups.', 'Hear every footstep before your enemies arrive. Features custom 50mm titanium drivers, spatial audio position engine, and detachable broadcast-quality AI microphone.', '{"Driver Size": "50mm Titanium Neodymium", "Frequency": "20Hz - 40,000Hz", "Connectivity": "2.4GHz Low Latency + BT 5.3", "Battery": "50 Hours"}', 129.99, 109.99, 25, 'HOR-HS-QUANT', 'assets/images/product-headset-1.jpg', '["assets/images/product-headset-1.jpg"]', 1, 0, 0, NULL),

(5, 3, 'HORAA Titan Cyber Desktop PC (RTX 4090 / i9-14900K)', 'horaa-titan-cyber-desktop-pc', 'Ultimate flagship gaming PC with liquid cooling, 64GB DDR5 RGB RAM, & dual 2TB NVMe SSDs.', 'Unleash uncompromised 4K 240FPS gaming and streaming power. Custom liquid loop cooling in a dual-chamber tempered glass chassis with customizable neon RGB illumination.', '{"CPU": "Intel Core i9-14900K 24-Core", "GPU": "NVIDIA GeForce RTX 4090 24GB", "RAM": "64GB DDR5 6000MHz RGB", "Storage": "4TB NVMe M.2 Gen4 SSD"}', 3899.99, 3599.99, 8, 'HOR-PC-TITAN', 'assets/images/product-pc-1.jpg', '["assets/images/product-pc-1.jpg"]', 1, 1, 0, NULL),

(6, 3, 'HORAA Blade Pro 16" OLED Gaming Laptop', 'horaa-blade-pro-16-oled-laptop', '240Hz QHD+ OLED display, RTX 4080 GPU, Intel i9 CPU, vapor chamber cooling.', 'Ultra-thin cyberpunk laptop chassis packed with Desktop-tier power. Thunderbolt 4, per-key RGB keyboard, and stunning 0.2ms response OLED screen.', '{"Display": "16 inch QHD+ 240Hz 0.2ms OLED", "CPU": "Intel Core i9-14900HX", "GPU": "RTX 4080 12GB GDDR6", "Weight": "2.3 kg"}', 2499.99, 2299.99, 12, 'HOR-LAP-BLADE', 'assets/images/product-laptop-1.jpg', '["assets/images/product-laptop-1.jpg"]', 1, 0, 0, NULL),

(7, 7, 'HORAA 10-in-1 Aluminum USB-C Hub & Docking Station', 'horaa-10-in-1-usb-c-hub', '4K@60Hz HDMI, 100W Power Delivery, Gigabit Ethernet, SD/TF Card Reader, 3x USB 3.2 Ports.', 'Turn a single USB-C port into a full workstation. Precision CNC anodized aluminum shell with thermal management and high-speed data transfer up to 10Gbps.', '{"Ports": "4K HDMI, 100W PD, 1Gbps LAN, 3x USB 3.2, SD/TF, 3.5mm AUX", "Material": "CNC Aluminum Alloy", "Cable Length": "25cm Braided"}', 49.99, 39.99, 100, 'HOR-HUB-10IN1', 'assets/images/product-hub-1.jpg', '["assets/images/product-hub-1.jpg"]', 0, 1, 0, NULL),

(8, 7, 'HORAA Braided Ultra-Fast 240W USB-C to USB-C Cable (2m)', 'horaa-240w-braided-usbc-cable-2m', 'Heavy duty nylon braided 240W PD3.1 fast charging & 20Gbps data cable with LED wattage display.', 'Bulletproof Kevlar reinforced cable with real-time digital power display. Charges laptops, tablets, and smartphones at maximum rated speed.', '{"Power Output": "240W Max (48V/5A PD3.1)", "Length": "2 Meters / 6.6ft", "Data Transfer": "20Gbps", "Display": "OLED Power Wattmeter"}', 24.99, 18.99, 150, 'HOR-CBL-240W', 'assets/images/product-cable-1.jpg', '["assets/images/product-cable-1.jpg"]', 0, 1, 0, NULL),

(9, 8, 'HORAA 140W GaN V Fast Charger (Dual USB-C + USB-A)', 'horaa-140w-gan-fast-charger', 'Next-gen GaN V tech for ultra-compact dual laptop & smartphone fast charging.', 'Power your gaming laptop and phone simultaneously from one wall outlet. Operates 20% cooler with advanced dynamic power distribution.', '{"Total Power": "140W Max", "Ports": "2x USB-C PD 3.1 + 1x USB-A QC 4.0", "Technology": "GaN Fast V Semiconductor"}', 59.99, 49.99, 80, 'HOR-CHG-140W', 'assets/images/product-charger-1.jpg', '["assets/images/product-charger-1.jpg"]', 0, 0, 0, NULL),

(10, 2, 'HORAA Cyber XL RGB Extended Mousepad (900x400mm)', 'horaa-cyber-xl-rgb-mousepad', 'Waterproof micro-woven surface with 14 RGB lighting modes & non-slip rubber base.', 'Low-friction surface tuned for optical mouse sensors. Embedded neon light strip around stitched edges with quick button color switching.', '{"Dimensions": "900 x 400 x 4mm", "Surface": "Water-Resistant Speed Mesh", "Lighting": "14 Modes RGB Spectrum"}', 29.99, 22.99, 90, 'HOR-PAD-XL', 'assets/images/product-mousepad-1.jpg', '["assets/images/product-mousepad-1.jpg"]', 0, 1, 0, NULL),

(11, 11, 'Steam Wallet $50 Gift Card [Digital Code Instant Delivery]', 'steam-wallet-50-gift-card-digital', 'Instant digital key delivered straight to your email & order dashboard upon payment.', 'Add $50 directly to your Steam Wallet balance to purchase games, DLCs, and in-game items instantly with zero delivery fee.', '{"Delivery": "Instant Digital Key", "Region": "Global / Multi-Currency Auto Conversion", "Validity": "No Expiration"}', 50.00, NULL, 999, 'HOR-DIG-STM50', 'assets/images/product-steam-1.jpg', '["assets/images/product-steam-1.jpg"]', 1, 1, 1, 'https://horaa.store/downloads/steam-key-sample.pdf'),

(12, 11, 'Xbox Game Pass Ultimate 12 Months Membership Code', 'xbox-game-pass-ultimate-12-months', 'Access hundreds of high-quality PC and console games plus EA Play & Cloud Gaming.', 'Enjoy 12 full months of Xbox Game Pass Ultimate. Play day-one releases on PC, Xbox, and mobile devices via Cloud Gaming.', '{"Duration": "12 Months", "Platforms": "PC, Xbox Series X/S, Cloud", "Included": "EA Play & Xbox Live Gold"}', 149.99, 119.99, 500, 'HOR-DIG-XGP12', 'assets/images/product-xbox-1.jpg', '["assets/images/product-xbox-1.jpg"]', 1, 0, 1, 'https://horaa.store/downloads/xgp-key-sample.pdf');

-- 4. COUPONS
INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `usage_limit`, `times_used`, `expiry_date`, `is_active`) VALUES
(1, 'HORAA10', 'percent', 10.00, 50.00, 500, 12, '2026-12-31', 1),
(2, 'GAMER20', 'fixed', 20.00, 100.00, 200, 5, '2026-12-31', 1),
(3, 'WELCOME15', 'percent', 15.00, 30.00, 1000, 48, '2026-12-31', 1);

-- 5. REVIEWS
INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `user_name`, `rating`, `comment`, `status`) VALUES
(1, 1, 2, 'Alex Mercer', 5, 'The jersey quality is unbelievable! Perfect fit, super cool material during long gaming sessions.', 'approved'),
(2, 2, 2, 'Alex Mercer', 5, 'Best mechanical keyboard I have ever owned. Rapid trigger actuation gives an insane edge in Valorant.', 'approved'),
(3, 3, 1, 'HORAA Admin', 5, '49 grams feels completely weightless. The 8K Hz polling rate is ultra smooth.', 'approved');
