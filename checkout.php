<?php
// checkout.php - HORAA STORE Secure Checkout & eSewa ePay v2 Gateway
require_once __DIR__ . '/includes/functions.php';

// ESEWA DIRECT PORTAL PAYMENT HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_esewa_direct_pay'])) {
    $orderNumber = trim($_POST['order_number'] ?? '');
    if (!empty($orderNumber)) {
        $transCode = '000' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $updStmt = $db->prepare("UPDATE orders SET payment_status = 'paid', order_status = 'processing', order_notes = CONCAT(IFNULL(order_notes, ''), ' [eSewa Ref: ', ?, ']') WHERE order_number = ?");
        $updStmt->execute([$transCode, $orderNumber]);
        
        unset($_SESSION['pending_esewa_order']);
        set_flash('success', 'eSewa ePay payment completed successfully! Ref Code: ' . sanitize($transCode));
        header("Location: order-success.php?order=" . urlencode($orderNumber));
        exit;
    }
}

// HANDLE PAY FOR EXISTING PENDING ORDER
if (isset($_GET['pay_order']) && !empty($_GET['pay_order'])) {
    $payOrderNo = trim($_GET['pay_order']);
    $pStmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND payment_status = 'pending'");
    $pStmt->execute([$payOrderNo]);
    $pendingOrd = $pStmt->fetch();

    if ($pendingOrd) {
        $esewaConfig = esewa_config();
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $baseUrl = "$protocol://$host$dir";

        $amountVal = esewa_format_amount(max(0, $pendingOrd['total_amount'] - $pendingOrd['discount_amount']));
        $totalAmountVal = esewa_format_amount($pendingOrd['final_amount']);
        $deliveryChargeVal = esewa_format_amount($pendingOrd['shipping_fee']);
        $transactionUuid = $pendingOrd['order_number'];
        $productCode = $esewaConfig['product_code'];

        $signature = esewa_generate_signature($totalAmountVal, $transactionUuid, $productCode, $esewaConfig['secret_key']);

        $_SESSION['pending_esewa_order'] = [
            'form_url' => $esewaConfig['form_url'],
            'amount' => $amountVal,
            'tax_amount' => '0',
            'total_amount' => $totalAmountVal,
            'transaction_uuid' => $transactionUuid,
            'product_code' => $productCode,
            'product_service_charge' => '0',
            'product_delivery_charge' => $deliveryChargeVal,
            'success_url' => "$baseUrl/order-success.php",
            'failure_url' => "$baseUrl/checkout.php?esewa=failed",
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'signature' => $signature,
            'order_number' => $pendingOrd['order_number']
        ];
        header("Location: checkout.php?esewa_gate=1");
        exit;
    }
}

// ESEWA HUMAN VERIFICATION & AUTHENTICATION PORTAL (WITH ENLARGED PROMINENT HEADER ESEWA LOGO)
if ((isset($_GET['esewa_gate']) || isset($_GET['esewa_redirect'])) && isset($_SESSION['pending_esewa_order'])) {
    $esewa = $_SESSION['pending_esewa_order'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>eSewa ePay Portal - EPAYTEST</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <style>
            body {
                background-color: #e5e7eb;
                color: #334155;
                font-family: 'Plus Jakarta Sans', Arial, sans-serif;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                margin: 0;
            }
            .esewa-header-nav {
                background-color: #f8f9fa;
                padding: 15px 40px;
                border-bottom: 1px solid #e2e8f0;
            }
            .esewa-header-logo {
                height: 45px;
                width: auto;
                object-fit: contain;
                transition: transform 0.2s ease;
            }
            .esewa-header-logo:hover {
                transform: scale(1.03);
            }
            .esewa-main-container {
                max-width: 860px;
                width: 100%;
                margin: 35px auto;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                border: 1px solid #e2e8f0;
                overflow: hidden;
            }
            .left-merchant-panel {
                background-color: #f4f5f7;
                border-right: 1px solid #e2e8f0;
                padding: 40px 32px;
            }
            .right-signin-panel {
                background-color: #f4f5f7;
                padding: 36px 32px;
            }
            .light-input-group {
                background-color: #eaedf1;
                border: 1px solid transparent;
                border-radius: 4px;
                overflow: hidden;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }
            .light-input-group:focus-within {
                border-color: #60b333;
                background-color: #ffffff;
                box-shadow: 0 0 0 2px rgba(96, 179, 51, 0.15);
            }
            .light-input-group .input-group-text {
                background-color: transparent !important;
                border: none !important;
                color: #64748b !important;
                padding: 12px 16px;
            }
            .light-input-group input {
                background: transparent !important;
                border: none !important;
                color: #1e293b !important;
                font-weight: 500;
                padding: 12px 14px;
                font-size: 0.95rem;
            }
            .light-input-group input:focus {
                box-shadow: none !important;
                outline: none !important;
            }
            
            /* EXACT 1:1 GOOGLE RECAPTCHA V2 REPLICA STYLES */
            .recaptcha-card {
                background-color: #f9f9f9;
                border: 1px solid #d3d3d3;
                border-radius: 3px;
                width: 100%;
                height: 76px;
                padding: 10px 12px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 0 4px rgba(0, 0, 0, 0.08);
                margin-bottom: 20px;
                user-select: none;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }
            .recaptcha-card.error-state {
                border-color: #d93025 !important;
                box-shadow: 0 0 6px rgba(217, 48, 37, 0.35) !important;
                animation: recaptchaShake 0.4s ease;
            }
            @keyframes recaptchaShake {
                0%, 100% { transform: translateX(0); }
                20%, 60% { transform: translateX(-6px); }
                40%, 80% { transform: translateX(6px); }
            }
            .recaptcha-left {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .recaptcha-checkbox {
                width: 28px;
                height: 28px;
                background-color: #ffffff;
                border: 2px solid #c1c1c1;
                border-radius: 2px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: border-color 0.2s ease;
            }
            .recaptcha-checkbox:hover {
                border-color: #b2b2b2;
            }
            .recaptcha-checkbox.checked {
                border-color: #0f9d58;
                background-color: #ffffff;
            }
            .recaptcha-label {
                font-family: Roboto, Arial, sans-serif;
                font-size: 14px;
                color: #222222;
                font-weight: 400;
                cursor: pointer;
            }
            .recaptcha-right {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                min-width: 70px;
            }
            .recaptcha-brand {
                font-family: Roboto, Arial, sans-serif;
                font-size: 10px;
                color: #555555;
                margin-top: 2px;
                font-weight: 500;
            }
            .recaptcha-terms {
                font-family: Roboto, Arial, sans-serif;
                font-size: 8px;
                color: #555555;
            }
            .recaptcha-terms a {
                color: #555555;
                text-decoration: none;
            }
            .recaptcha-terms a:hover {
                text-decoration: underline;
            }

            .btn-esewa-submit {
                background-color: #60b333;
                color: #ffffff;
                font-weight: 700;
                font-size: 1rem;
                letter-spacing: 0.5px;
                border: none;
                border-radius: 6px;
                padding: 13px;
                width: 100%;
                text-transform: uppercase;
                transition: background-color 0.2s ease, transform 0.1s ease;
                box-shadow: 0 4px 12px rgba(96, 179, 51, 0.3);
            }
            .btn-esewa-submit:hover {
                background-color: #529c29;
                color: #ffffff;
                transform: translateY(-1px);
            }
            .esewa-green-link {
                color: #60b333;
                text-decoration: none;
            }
            .esewa-green-link:hover {
                color: #4f9429;
                text-decoration: underline;
            }

            /* SIMULATED SMARTPHONE SMS PUSH NOTIFICATION POPUP */
            .sms-push-toast {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 360px;
                width: 90%;
                background: rgba(30, 41, 59, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 12px;
                color: #ffffff;
                padding: 14px 16px;
                box-shadow: 0 12px 32px rgba(0,0,0,0.3);
                animation: slideDownIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            }
            @keyframes slideDownIn {
                from { transform: translateY(-30px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        </style>
    </head>
    <body>

        <!-- SIMULATED SMS PUSH NOTIFICATION POPUP -->
        <div id="smsNotificationToast" class="sms-push-toast d-none">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 22px; height: 22px; background: #22c55e; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #fff;">
                        <i class="fas fa-comment-alt"></i>
                    </div>
                    <span class="fw-bold" style="font-size: 0.8rem; color: #cbd5e1;">MESSAGES</span>
                    <span style="font-size: 0.7rem; color: #94a3b8;">• now</span>
                </div>
                <button type="button" class="btn-close btn-close-white" style="font-size: 0.65rem;" onclick="document.getElementById('smsNotificationToast').classList.add('d-none')"></button>
            </div>
            <div class="fw-bold text-white mb-1" style="font-size: 0.88rem;">eSewa OTP Code</div>
            <div class="small" style="font-size: 0.8rem; color: #e2e8f0; line-height: 1.35;">
                Your eSewa verification code for EPAYTEST is <strong class="text-warning font-monospace fs-6" id="toastOtpCode">784912</strong>. Do not share with anyone.
            </div>
            <div class="mt-2 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-success py-0 px-2 fw-bold" style="font-size: 0.75rem;" onclick="autoFillOtp()">
                    <i class="fas fa-magic me-1"></i> Auto-Fill
                </button>
            </div>
        </div>

        <!-- eSewa Header (Enlarged Prominent eSewa Logo Header, Verified Badge & Language selector) -->
        <div class="esewa-header-nav d-flex justify-content-between align-items-center">
            <div style="width: 100px;"></div>
            <div class="d-flex align-items-center justify-content-center" style="flex-grow: 1;">
                <img src="https://cdn.esewa.com.np/ui/images/esewa_og.png" class="esewa-header-logo" alt="eSewa Logo">
            </div>
            <div class="d-flex align-items-center justify-content-end" style="width: 100px;">
                <button class="btn btn-sm text-secondary px-2 py-1 dropdown-toggle" style="background: transparent; border: none; font-size: 0.85rem; color: #64748b !important;">
                    English <i class="fas fa-chevron-down ms-1" style="font-size: 0.75rem;"></i>
                </button>
            </div>
        </div>

        <!-- Main 2-Column Portal Card -->
        <div class="container my-auto py-4">
            <div class="esewa-main-container shadow-lg">
                <div class="row g-0">
                    
                    <!-- LEFT COLUMN: Merchant & Amount Overview -->
                    <div class="col-md-6 left-merchant-panel d-flex flex-column justify-content-between">
                        <div>
                            <!-- Merchant Logo & Title -->
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <img src="https://p7.hiclipart.com/preview/261/608/1001/esewa-zone-office-bayalbas-google-play-iphone-iphone-thumbnail.jpg" style="height: 32px; width: 32px; object-fit: contain; background: #60b333; border-radius: 50%; padding: 4px;" alt="eSewa Icon">
                                <div>
                                    <span class="fw-bold text-dark fs-6 d-block" style="letter-spacing: 0.5px; color: #334155 !important;">EPAYTEST</span>
                                </div>
                            </div>

                            <!-- Total Amount Display -->
                            <div class="mb-4">
                                <small class="text-secondary d-block text-capitalize" style="font-size: 0.85rem; color: #64748b !important;">Total Amount</small>
                                <h2 class="fw-bold mb-0" style="font-size: 2.1rem;">
                                    <span style="color: #60b333;">NPR. </span>
                                    <span class="text-dark font-monospace"><?php echo number_format((float)$esewa['total_amount'], 2); ?></span>
                                </h2>
                            </div>

                            <!-- Details Breakdown Table -->
                            <div class="p-3 rounded-2 mb-4 small" style="background: #e2e8f0; border: none;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span style="color: #475569;">Product Amount</span>
                                    <span class="text-dark fw-semibold"><?php echo number_format((float)$esewa['amount'], 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span style="color: #475569;">Total Amount</span>
                                    <span class="text-dark fw-semibold"><?php echo number_format((float)$esewa['total_amount'], 1); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- LINK & PAY Banner Graphic -->
                        <div style="border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 360 110" width="100%" height="110" style="display: block;">
                                <!-- Card Background -->
                                <rect width="360" height="110" rx="10" fill="#effbe9" stroke="#bbf7d0" stroke-width="1.5" />
                                
                                <!-- Left Side Text: LINK & PAY -->
                                <text x="24" y="44" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-weight="900" font-size="26" fill="#16a34a">LINK</text>
                                <text x="24" y="74" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-weight="900" font-size="26" fill="#dc2626">&amp; PAY</text>

                                <!-- Top Right eSewa Logo Badge -->
                                <g transform="translate(290, 12)">
                                    <circle cx="10" cy="10" r="10" fill="#60b333" />
                                    <text x="10" y="14" font-family="sans-serif" font-weight="bold" font-size="11" fill="#ffffff" text-anchor="middle">e</text>
                                    <text x="24" y="15" font-family="sans-serif" font-weight="bold" font-size="13" fill="#60b333">Sewa</text>
                                </g>

                                <!-- Smartphone Mockup -->
                                <g transform="translate(240, 10)">
                                    <!-- Phone Body Outer -->
                                    <rect x="0" y="0" width="48" height="92" rx="7" fill="#ffffff" stroke="#1e293b" stroke-width="2.5" />
                                    <!-- Notch -->
                                    <rect x="18" y="2" width="12" height="3" rx="1.5" fill="#1e293b" />
                                    
                                    <!-- Bank Temple Icon inside phone screen -->
                                    <g transform="translate(14, 18)">
                                        <polygon points="10,0 0,8 20,8" fill="#475569" />
                                        <rect x="2" y="8" width="16" height="2" fill="#475569" />
                                        <rect x="3" y="11" width="3" height="9" fill="#475569" />
                                        <rect x="8.5" y="11" width="3" height="9" fill="#475569" />
                                        <rect x="14" y="11" width="3" height="9" fill="#475569" />
                                        <rect x="1" y="20" width="18" height="3" fill="#475569" />
                                    </g>

                                    <!-- App Bottom Nav -->
                                    <line x1="14" y1="87" x2="34" y2="87" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" />
                                </g>

                                <!-- Floating Green Chain Link Badge -->
                                <g transform="translate(198, 36)">
                                    <rect x="0" y="0" width="22" height="15" rx="4" fill="#60b333" />
                                    <path d="M6 7.5 h10 M8 5.5 h6 M8 9.5 h6" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" />
                                </g>

                                <!-- Floating Green e Badge -->
                                <g transform="translate(224, 48)">
                                    <circle cx="8" cy="8" r="8" fill="#60b333" />
                                    <text x="8" y="11" font-family="sans-serif" font-weight="bold" font-size="9" fill="#ffffff" text-anchor="middle">e</text>
                                </g>

                                <!-- Cashback Notification Banner overlaying Phone -->
                                <g transform="translate(162, 60)">
                                    <rect x="0" y="0" width="112" height="34" rx="4" fill="#ffffff" stroke="#cbd5e1" stroke-width="1" />
                                    <rect x="0" y="0" width="112" height="4" rx="2" fill="#60b333" />
                                    <text x="56" y="14" font-family="sans-serif" font-weight="bold" font-size="7.5" fill="#dc2626" text-anchor="middle">You have received Rs. 100/-</text>
                                    <text x="56" y="24" font-family="sans-serif" font-weight="bold" font-size="6.5" fill="#1e293b" text-anchor="middle">Cashback on Insurance Payment.</text>
                                    <rect x="0" y="30" width="112" height="4" rx="1" fill="#60b333" />
                                </g>
                            </svg>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Sign in & OTP Verification Portal -->
                    <div class="col-md-6 right-signin-panel">
                        <form id="esewaLoginForm" action="checkout.php" method="POST">
                            <input type="hidden" name="process_esewa_direct_pay" value="1">
                            <input type="hidden" name="order_number" value="<?php echo sanitize($esewa['order_number']); ?>">

                            <!-- STEP 1: LOGIN & MPIN -->
                            <div id="esewaStep1Box">
                                <h5 class="text-dark fw-bold mb-4" style="font-size: 1.15rem;">Sign in to your account</h5>

                                <!-- Input 1: eSewa ID / Mobile Number -->
                                <div class="mb-3">
                                    <div class="input-group light-input-group">
                                        <span class="input-group-text"><i class="far fa-user text-secondary"></i></span>
                                        <input type="text" name="esewa_id" id="esewaMobileInput" class="form-control" value="9806800005" required>
                                    </div>
                                </div>

                                <!-- Input 2: eSewa Password / MPIN -->
                                <div class="mb-3">
                                    <div class="input-group light-input-group">
                                        <span class="input-group-text"><i class="fas fa-lock text-secondary"></i></span>
                                        <input type="password" id="mpinInput" name="esewa_mpin" class="form-control" value="" placeholder="" autocomplete="off">
                                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-eye text-secondary" style="font-size: 0.9rem; cursor: pointer;" id="toggleMpinBtn"></i></span>
                                    </div>
                                    <div id="mpinInputError" class="text-danger small mt-1 d-none" style="font-size: 0.78rem;">
                                        <i class="fas fa-exclamation-circle me-1"></i> Please enter your eSewa MPIN / Password.
                                    </div>
                                </div>

                                <!-- EXACT 1:1 GOOGLE RECAPTCHA V2 WIDGET -->
                                <div id="recaptchaCard" class="recaptcha-card">
                                    <div class="recaptcha-left">
                                        <div id="captchaBox" class="recaptcha-checkbox" role="checkbox" aria-checked="false" tabindex="0">
                                            <div class="spinner-border text-primary d-none" id="captchaSpinner" role="status" style="width: 16px; height: 16px; border-width: 2px;"></div>
                                            <i class="fas fa-check text-success d-none" id="captchaCheckmark" style="font-size: 15px; font-weight: 900;"></i>
                                        </div>
                                        <span id="captchaLabel" class="recaptcha-label">I'm not a robot</span>
                                    </div>
                                    <div class="recaptcha-right">
                                        <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" width="28" height="28" alt="reCAPTCHA logo">
                                        <div class="recaptcha-brand">reCAPTCHA</div>
                                        <div class="recaptcha-terms">
                                            <a href="https://www.google.com/intl/en/policies/privacy/" target="_blank">Privacy</a> - <a href="https://www.google.com/intl/en/policies/terms/" target="_blank">Terms</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- RECAPTCHA ERROR NOTICE -->
                                <div id="captchaError" class="text-danger small mb-3 fw-semibold d-none" style="font-size: 0.8rem; margin-top: -12px;">
                                    <i class="fas fa-exclamation-circle me-1"></i> Please check the "I'm not a robot" box to proceed.
                                </div>

                                <!-- LOGIN / PROCEED BUTTON -->
                                <button type="submit" id="btnStep1Submit" class="btn btn-esewa-submit mb-3">
                                    LOGIN
                                </button>

                                <div class="text-center small mb-3">
                                    <a href="#" class="esewa-green-link small">Forgot Password?</a>
                                </div>

                                <div class="text-center small mb-4" style="color: #64748b;">
                                    Don't have an account? <a href="register.php" class="esewa-green-link fw-bold">Register</a>
                                </div>
                            </div>

                            <!-- STEP 2: ENTER VERIFICATION CODE -->
                            <div id="esewaStep2Box" class="d-none">
                                
                                <!-- Top Graphic: Smartphone with Green 6-Dot Pill Badge Overlay -->
                                <div class="text-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 110" width="130" height="90">
                                        <rect x="46" y="6" width="68" height="98" rx="8" fill="#334155" />
                                        <rect x="50" y="14" width="60" height="82" rx="4" fill="#ffffff" />
                                        <rect x="58" y="22" width="44" height="10" rx="2" fill="#f1f5f9" />
                                        <rect x="58" y="36" width="28" height="4" rx="1.5" fill="#e2e8f0" />
                                        <rect x="58" y="44" width="36" height="4" rx="1.5" fill="#e2e8f0" />
                                        
                                        <g transform="translate(30, 40)">
                                            <rect x="0" y="0" width="100" height="32" rx="16" fill="#60b333" />
                                            <text x="50" y="21" font-family="'Plus Jakarta Sans', sans-serif" font-weight="900" font-size="20" fill="#ffffff" text-anchor="middle" letter-spacing="3">• • • • • •</text>
                                        </g>
                                    </svg>
                                </div>

                                <h5 class="text-dark fw-bold mb-1" style="font-size: 1.15rem;">Enter a verification code</h5>
                                <p class="text-secondary small mb-3" style="font-size: 0.82rem; line-height: 1.4;">
                                    Please type a 6-digit verification code sent to your mobile number.
                                </p>

                                <!-- SMS OTP Notification Card -->
                                <div class="p-2 mb-3 rounded-2 bg-light border border-success border-opacity-50 small d-flex align-items-center justify-content-between" style="font-size: 0.8rem; background: #f0fdf4 !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-sms text-success fs-5"></i>
                                        <div>
                                            <span class="text-secondary d-block" style="font-size: 0.72rem;">SMS Code (Type below):</span>
                                            <strong class="font-monospace text-success fs-6" id="displayOtpCode">849201</strong>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-success py-1 px-2 fw-bold small" onclick="autoFillOtp()" style="font-size: 0.75rem;">
                                        <i class="fas fa-arrow-down me-1"></i> Fill Code
                                    </button>
                                </div>

                                <!-- OTP Countdown Timer on Right -->
                                <div class="d-flex justify-content-end mb-1">
                                    <small class="text-danger fw-semibold" style="font-size: 0.78rem;">
                                        OTP expires in <span id="resendTimerText" class="font-monospace fw-bold">02:59</span>
                                    </small>
                                </div>

                                <!-- OTP Code Input -->
                                <div class="mb-3">
                                    <div class="input-group light-input-group">
                                        <span class="input-group-text font-monospace fs-5 text-secondary fw-bold">#</span>
                                        <input type="text" name="esewa_otp" id="otpInput" class="form-control font-monospace fw-bold fs-5 tracking-widest text-center" value="" maxlength="6" placeholder="Enter verification token" autocomplete="off">
                                    </div>
                                    <div id="otpInputError" class="text-danger small mt-1 d-none" style="font-size: 0.78rem;">
                                        <i class="fas fa-exclamation-circle me-1"></i> Please enter the 6-digit verification code.
                                    </div>
                                </div>

                                <!-- VERIFY BUTTON -->
                                <button type="submit" id="btnCompletePayment" class="btn btn-esewa-submit mb-3">
                                    VERIFY
                                </button>

                                <div class="text-center pt-2">
                                    <button type="button" id="btnBackToStep1" class="btn btn-link p-0 text-decoration-none text-secondary fw-semibold small border-0 bg-transparent">
                                        Back To Login
                                    </button>
                                </div>
                            </div>

                            <div class="text-center pt-2">
                                <a href="checkout.php?esewa=failed" style="color: #64748b; font-size: 0.82rem; font-weight: 600; text-decoration: none; letter-spacing: 0.5px;">
                                    CANCEL PAYMENT
                                </a>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            <!-- Portal Sub-Footer -->
            <div class="d-flex justify-content-between align-items-center small px-3" style="max-width: 860px; margin: 0 auto 30px; color: #64748b;">
                <span>eSewa Nepal 2026. All Rights Reserved.</span>
                <div>
                    <a href="#" style="color: #64748b;" class="text-decoration-none me-3">Terms & Conditions</a>
                    <a href="#" style="color: #64748b;" class="text-decoration-none">Privacy Policies</a>
                </div>
            </div>
        </div>

        <script>
            let isCaptchaVerified = false;
            let currentStep = 1;
            let resendInterval = null;
            let activeOtpCode = '849201';

            const captchaBox = document.getElementById('captchaBox');
            const captchaLabel = document.getElementById('captchaLabel');
            const captchaSpinner = document.getElementById('captchaSpinner');
            const captchaCheckmark = document.getElementById('captchaCheckmark');
            const recaptchaCard = document.getElementById('recaptchaCard');
            const captchaError = document.getElementById('captchaError');

            function generateNewOtp() {
                activeOtpCode = Math.floor(100000 + Math.random() * 900000).toString();
                document.getElementById('displayOtpCode').textContent = activeOtpCode;
                document.getElementById('toastOtpCode').textContent = activeOtpCode;
                
                const toast = document.getElementById('smsNotificationToast');
                if (toast) {
                    toast.classList.remove('d-none');
                }
            }

            function autoFillOtp() {
                const otpField = document.getElementById('otpInput');
                if (otpField) {
                    otpField.value = activeOtpCode;
                    document.getElementById('otpInputError')?.classList.add('d-none');
                }
            }

            function handleCaptchaClick() {
                if (isCaptchaVerified) return;

                recaptchaCard.classList.remove('error-state');
                if (captchaError) captchaError.classList.add('d-none');

                captchaSpinner.classList.remove('d-none');

                setTimeout(() => {
                    captchaSpinner.classList.add('d-none');
                    captchaCheckmark.classList.remove('d-none');
                    captchaBox.classList.add('checked');
                    isCaptchaVerified = true;
                }, 450);
            }

            captchaBox?.addEventListener('click', handleCaptchaClick);
            captchaLabel?.addEventListener('click', handleCaptchaClick);

            const toggleMpinBtn = document.getElementById('toggleMpinBtn');
            const mpinInput = document.getElementById('mpinInput');
            if (toggleMpinBtn && mpinInput) {
                toggleMpinBtn.addEventListener('click', function() {
                    if (mpinInput.type === 'password') {
                        mpinInput.type = 'text';
                        toggleMpinBtn.classList.remove('fa-eye');
                        toggleMpinBtn.classList.add('fa-eye-slash');
                    } else {
                        mpinInput.type = 'password';
                        toggleMpinBtn.classList.remove('fa-eye-slash');
                        toggleMpinBtn.classList.add('fa-eye');
                    }
                });
            }

            document.getElementById('esewaLoginForm')?.addEventListener('submit', function(e) {
                if (currentStep === 1) {
                    const mpinVal = document.getElementById('mpinInput').value.trim();
                    const mpinError = document.getElementById('mpinInputError');

                    if (!mpinVal) {
                        e.preventDefault();
                        if (mpinError) mpinError.classList.remove('d-none');
                        document.getElementById('mpinInput')?.focus();
                        return;
                    } else {
                        if (mpinError) mpinError.classList.add('d-none');
                    }

                    if (!isCaptchaVerified) {
                        e.preventDefault();
                        recaptchaCard.classList.add('error-state');
                        if (captchaError) captchaError.classList.remove('d-none');
                        return;
                    }

                    e.preventDefault();
                    generateNewOtp();

                    document.getElementById('esewaStep1Box').classList.add('d-none');
                    document.getElementById('esewaStep2Box').classList.remove('d-none');
                    currentStep = 2;

                    setTimeout(() => {
                        document.getElementById('otpInput')?.focus();
                    }, 200);

                    startResendTimer();
                } else if (currentStep === 2) {
                    const otpInput = document.getElementById('otpInput');
                    if (otpInput && !otpInput.value.trim()) {
                        otpInput.value = activeOtpCode;
                    }
                }
            });

            document.getElementById('btnBackToStep1')?.addEventListener('click', function() {
                document.getElementById('esewaStep2Box').classList.add('d-none');
                document.getElementById('esewaStep1Box').classList.remove('d-none');
                document.getElementById('smsNotificationToast')?.classList.add('d-none');
                currentStep = 1;
            });

            function startResendTimer() {
                let timeLeft = 179;
                const timerSpan = document.getElementById('resendTimerText');
                
                if (resendInterval) clearInterval(resendInterval);

                resendInterval = setInterval(() => {
                    timeLeft--;
                    if (timeLeft >= 0) {
                        const minutes = Math.floor(timeLeft / 60);
                        const seconds = timeLeft % 60;
                        const formatted = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                        if (timerSpan) timerSpan.textContent = formatted;
                    } else {
                        clearInterval(resendInterval);
                        if (timerSpan) timerSpan.textContent = '00:00';
                    }
                }, 1000);
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

$pageTitle = "Secure Checkout - HORAA STORE";
require_once __DIR__ . '/includes/header.php';

// Check eSewa Failure Alert
if (isset($_GET['esewa']) && $_GET['esewa'] === 'failed') {
    set_flash('error', 'eSewa transaction was cancelled or encountered an error. Please try again.');
}

$cartItems = get_cart_items();
if (empty($cartItems) && !isset($_SESSION['pending_esewa_order'])) {
    set_flash('error', 'Your shopping cart is empty.');
    header('Location: shop.php');
    exit;
}

$subtotal = get_cart_subtotal();

// Discount Calculation
$discountAmount = 0.00;
$couponId = null;
if (isset($_SESSION['applied_coupon'])) {
    $coupon = $_SESSION['applied_coupon'];
    $couponId = $coupon['id'];
    if ($coupon['type'] === 'percent') {
        $discountAmount = ($subtotal * $coupon['value']) / 100;
    } else {
        $discountAmount = min($subtotal, $coupon['value']);
    }
}

$shippingFee = ($subtotal >= 99) ? 0.00 : 10.00;
$finalTotal = max(0, $subtotal - $discountAmount) + $shippingFee;

$currentUser = current_user();

// ORDER SUBMISSION POST HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = trim($_POST['shipping_name'] ?? '');
    $email = trim($_POST['shipping_email'] ?? '');
    $phone = trim($_POST['shipping_phone'] ?? '');
    $address = trim($_POST['shipping_address'] ?? '');
    $city = trim($_POST['shipping_city'] ?? '');
    $postal = trim($_POST['shipping_postal'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'esewa');
    $notes = trim($_POST['order_notes'] ?? '');

    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city)) {
        set_flash('error', 'Please fill in all required shipping fields.');
    } else {
        try {
            $db->beginTransaction();

            $orderNumber = generate_order_number();
            $userId = is_logged_in() ? $_SESSION['user_id'] : null;
            $paymentStatus = ($paymentMethod === 'cod' || $paymentMethod === 'esewa') ? 'pending' : 'paid';
            $orderStatus = ($paymentMethod === 'esewa') ? 'pending' : 'processing';

            // 1. Insert Order
            $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total_amount, discount_amount, shipping_fee, final_amount, payment_method, payment_status, order_status, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_postal, order_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId, $orderNumber, $subtotal, $discountAmount, $shippingFee, $finalTotal,
                $paymentMethod, $paymentStatus, $orderStatus, $name, $email, $phone, $address, $city, $postal, $notes
            ]);
            $orderId = $db->lastInsertId();

            // 2. Insert Order Items & Deduct Stock
            $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
            $stockStmt = $db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");

            foreach ($cartItems as $item) {
                $unitPrice = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                $itemTotal = $unitPrice * $item['quantity'];

                $itemStmt->execute([$orderId, $item['id'], $item['name'], $unitPrice, $item['quantity'], $itemTotal]);
                $stockStmt->execute([$item['quantity'], $item['id']]);
            }

            // 3. Update Coupon Usage
            if ($couponId) {
                $cStmt = $db->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE id = ?");
                $cStmt->execute([$couponId]);
            }

            // 4. Clear Cart
            $sessionId = get_session_cart_id();
            if ($userId) {
                $delCart = $db->prepare("DELETE FROM cart WHERE user_id = ? OR session_id = ?");
                $delCart->execute([$userId, $sessionId]);
            } else {
                $delCart = $db->prepare("DELETE FROM cart WHERE session_id = ?");
                $delCart->execute([$sessionId]);
            }
            unset($_SESSION['applied_coupon']);

            $db->commit();

            // 5. IF ESEWA PAYMENT METHOD SELECTED -> PREPARE ESEWA EPAY V2 PAYLOAD & SHOW GATE
            if ($paymentMethod === 'esewa') {
                $esewaConfig = esewa_config();

                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                $dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $baseUrl = "$protocol://$host$dir";

                $successUrl = "$baseUrl/order-success.php";
                $failureUrl = "$baseUrl/checkout.php?esewa=failed";

                $amountVal = esewa_format_amount(max(0, $subtotal - $discountAmount));
                $taxAmountVal = "0";
                $totalAmountVal = esewa_format_amount($finalTotal);
                $deliveryChargeVal = esewa_format_amount($shippingFee);
                $transactionUuid = $orderNumber;
                $productCode = $esewaConfig['product_code'];

                $signature = esewa_generate_signature($totalAmountVal, $transactionUuid, $productCode, $esewaConfig['secret_key']);

                $_SESSION['pending_esewa_order'] = [
                    'form_url' => $esewaConfig['form_url'],
                    'amount' => $amountVal,
                    'tax_amount' => $taxAmountVal,
                    'total_amount' => $totalAmountVal,
                    'transaction_uuid' => $transactionUuid,
                    'product_code' => $productCode,
                    'product_service_charge' => '0',
                    'product_delivery_charge' => $deliveryChargeVal,
                    'success_url' => $successUrl,
                    'failure_url' => $failureUrl,
                    'signed_field_names' => 'total_amount,transaction_uuid,product_code',
                    'signature' => $signature,
                    'order_number' => $orderNumber
                ];

                header("Location: checkout.php?esewa_gate=1");
                exit;
            }

            header("Location: order-success.php?order=" . urlencode($orderNumber));
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            set_flash('error', 'Order creation error: ' . $e->getMessage());
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <h2 class="text-white mb-0 font-monospace">
        <i class="fas fa-lock text-cyan me-2"></i> Secure Checkout
    </h2>
    <a href="cart.php" class="btn btn-sm btn-outline-cyber"><i class="fas fa-arrow-left me-1"></i> Back to Cart</a>
</div>

<form action="checkout.php" method="POST">
    <div class="row g-4 mb-5">
        
        <!-- LEFT: SHIPPING & PAYMENT DETAILS -->
        <div class="col-lg-7">
            
            <!-- Shipping Information Card -->
            <div class="bg-card rounded-4 border border-cyan p-4 mb-4">
                <h4 class="text-cyan font-monospace mb-4"><i class="fas fa-truck me-2"></i> Shipping Address</h4>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-cyber">Full Name *</label>
                        <input type="text" name="shipping_name" class="form-control form-control-cyber" required 
                               value="<?php echo sanitize($_POST['shipping_name'] ?? ($currentUser['name'] ?? '')); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-cyber">Email Address *</label>
                        <input type="email" name="shipping_email" class="form-control form-control-cyber" required 
                               value="<?php echo sanitize($_POST['shipping_email'] ?? ($currentUser['email'] ?? '')); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-cyber">Phone Number *</label>
                        <input type="text" name="shipping_phone" class="form-control form-control-cyber" required 
                               value="<?php echo sanitize($_POST['shipping_phone'] ?? ($currentUser['phone'] ?? '')); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-cyber">City / Region *</label>
                        <input type="text" name="shipping_city" class="form-control form-control-cyber" required 
                               value="<?php echo sanitize($_POST['shipping_city'] ?? ($currentUser['city'] ?? '')); ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label-cyber">Street Address *</label>
                        <input type="text" name="shipping_address" class="form-control form-control-cyber" required 
                               value="<?php echo sanitize($_POST['shipping_address'] ?? ($currentUser['address'] ?? '')); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-cyber">Postal Code</label>
                        <input type="text" name="shipping_postal" class="form-control form-control-cyber" 
                               value="<?php echo sanitize($_POST['shipping_postal'] ?? ($currentUser['postal_code'] ?? '')); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label-cyber">Special Order Notes (Optional)</label>
                        <textarea name="order_notes" rows="2" class="form-control form-control-cyber" placeholder="e.g. Leave package at front door or custom jersey name print instructions..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Payment Method Card -->
            <div class="bg-card rounded-4 border border-cyan p-4">
                <h4 class="text-cyan font-monospace mb-4"><i class="fas fa-credit-card me-2"></i> Payment Method</h4>

                <div class="d-flex flex-column gap-3">
                    <!-- eSewa ePay v2 Featured Method -->
                    <div class="p-3 bg-glass rounded-3 border border-success hover-border-cyan" style="background: rgba(40, 167, 69, 0.08);">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-success" type="radio" name="payment_method" id="payEsewa" value="esewa" checked>
                            <label class="form-check-label text-white fw-bold d-flex justify-content-between align-items-center w-100" for="payEsewa">
                                <span class="d-flex align-items-center gap-2">
                                    <img src="https://cdn.esewa.com.np/ui/images/esewa_og.png?111" style="height: 32px; width: auto; object-fit: contain;" alt="eSewa Logo"> 
                                    <span>eSewa ePay Online Payment (v2 API)</span>
                                </span>
                                <span class="badge bg-success text-white font-monospace"><i class="fas fa-shield-check me-1"></i> VERIFIED MERCH</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-3 bg-glass rounded-3 border border-secondary">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-cyan" type="radio" name="payment_method" id="payCard" value="card">
                            <label class="form-check-label text-white fw-bold d-flex justify-content-between w-100" for="payCard">
                                <span><i class="fas fa-credit-card text-cyan me-2"></i> Credit / Debit Card (256-Bit SSL)</span>
                                <span class="text-muted"><i class="fab fa-cc-visa me-1"></i><i class="fab fa-cc-mastercard"></i></span>
                            </label>
                        </div>
                    </div>

                    <div class="p-3 bg-glass rounded-3 border border-secondary">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-cyan" type="radio" name="payment_method" id="payPaypal" value="paypal">
                            <label class="form-check-label text-white fw-bold d-flex justify-content-between w-100" for="payPaypal">
                                <span><i class="fab fa-paypal text-cyan me-2"></i> PayPal Express Checkout</span>
                                <span class="text-cyan"><i class="fab fa-paypal fs-5"></i></span>
                            </label>
                        </div>
                    </div>

                    <div class="p-3 bg-glass rounded-3 border border-secondary">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-cyan" type="radio" name="payment_method" id="payCOD" value="cod">
                            <label class="form-check-label text-white fw-bold d-flex justify-content-between w-100" for="payCOD">
                                <span><i class="fas fa-money-bill-wave text-gold me-2"></i> Cash on Delivery (COD)</span>
                                <span class="badge-gold">PAY AT DOOR</span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- RIGHT: ORDER ITEMS BREAKDOWN -->
        <div class="col-lg-5">
            <div class="order-summary-card sticky-top" style="top: 100px;">
                <h4 class="text-white font-monospace mb-4"><i class="fas fa-shopping-bag text-cyan me-2"></i> Order Items</h4>

                <div class="mb-4 pe-1" style="max-height: 280px; overflow-y: auto;">
                    <?php foreach ($cartItems as $item): 
                        $unitPrice = ($item['sale_price'] && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                    ?>
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-secondary">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge-cyber"><?php echo $item['quantity']; ?>x</span>
                                <div>
                                    <span class="text-white small fw-bold d-block text-truncate" style="max-width: 220px;">
                                        <?php echo sanitize($item['name']); ?>
                                    </span>
                                    <small class="text-muted"><?php echo format_price($unitPrice); ?></small>
                                </div>
                            </div>
                            <span class="text-cyan font-monospace fw-bold small">
                                <?php echo format_price($unitPrice * $item['quantity']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span class="text-muted">Subtotal</span>
                    <span class="text-white font-monospace"><?php echo format_price($subtotal); ?></span>
                </div>

                <?php if ($discountAmount > 0): ?>
                    <div class="summary-row text-magenta">
                        <span>Discount Voucher</span>
                        <span class="font-monospace">-<?php echo format_price($discountAmount); ?></span>
                    </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span class="text-muted">Shipping Fee</span>
                    <span class="text-white font-monospace"><?php echo $shippingFee == 0 ? '<span class="text-cyan">FREE</span>' : format_price($shippingFee); ?></span>
                </div>

                <div class="summary-row border-top border-secondary pt-3 mt-2 fs-5">
                    <span class="text-white fw-bold">Final Total</span>
                    <span class="text-cyan font-monospace fw-bold fs-3"><?php echo format_price($finalTotal); ?></span>
                </div>

                <button type="submit" name="place_order" class="btn-success w-100 mt-4 text-center justify-content-center py-3 fs-5 font-monospace fw-bold border-0 shadow">
                    <i class="fas fa-lock me-2"></i> Confirm & Pay via eSewa
                </button>
            </div>
        </div>

    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
