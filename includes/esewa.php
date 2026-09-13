<?php
// includes/esewa.php - eSewa ePay v2 Gateway Integration Helper

/**
 * Get eSewa Configuration Credentials & Endpoints
 */
function esewa_config() {
    $isTest = true; // Set to false for live production merchant credentials

    return [
        'is_test' => $isTest,
        'product_code' => 'EPAYTEST', // Default UAT Product Code
        'secret_key' => '8g2hAkAVN259dZw8', // Standard eSewa UAT Secret Key
        'form_url' => $isTest 
            ? 'https://rc-epay.esewa.com.np/api/epay/main/v2/form' 
            : 'https://epay.esewa.com.np/api/epay/main/v2/form',
        'status_url' => $isTest 
            ? 'https://rc.esewa.com.np/api/epay/transaction/status/' 
            : 'https://esewa.com.np/api/epay/transaction/status/'
    ];
}

/**
 * Format amount string for eSewa payload matching
 * Removes extra trailing zeros for whole integers (e.g. 100 instead of 100.00)
 */
function esewa_format_amount($amount) {
    $val = (float)$amount;
    if (floor($val) == $val) {
        return (string)intval($val);
    }
    return number_format($val, 2, '.', '');
}

/**
 * Generate HMAC-SHA256 Base64 Signature for eSewa v2 Form Request
 * Message format: total_amount=VAL,transaction_uuid=VAL,product_code=VAL
 */
function esewa_generate_signature($totalAmount, $transactionUuid, $productCode, $secretKey) {
    $totalAmount = trim((string)$totalAmount);
    $transactionUuid = trim((string)$transactionUuid);
    $productCode = trim((string)$productCode);
    $secretKey = trim((string)$secretKey);

    $signedFieldsStr = "total_amount=$totalAmount,transaction_uuid=$transactionUuid,product_code=$productCode";
    $rawHmac = hash_hmac('sha256', $signedFieldsStr, $secretKey, true);
    return base64_encode($rawHmac);
}

/**
 * Verify eSewa v2 Response Callback Signature
 * Response message format: transaction_code=VAL,status=VAL,total_amount=VAL,transaction_uuid=VAL,product_code=VAL,signed_field_names=VAL
 */
function esewa_verify_response_signature($data) {
    $config = esewa_config();
    $signedFieldNames = explode(',', $data['signed_field_names'] ?? '');
    
    $parts = [];
    foreach ($signedFieldNames as $field) {
        $field = trim($field);
        if (isset($data[$field])) {
            $parts[] = "$field={$data[$field]}";
        }
    }
    
    $message = implode(',', $parts);
    $calculatedSig = base64_encode(hash_hmac('sha256', $message, $config['secret_key'], true));
    
    return isset($data['signature']) && hash_equals($calculatedSig, $data['signature']);
}

/**
 * Call eSewa Status Check API v2
 * API URL: https://rc.esewa.com.np/api/epay/transaction/status/?product_code=EPAYTEST&total_amount=100&transaction_uuid=123
 */
function esewa_check_status($totalAmount, $transactionUuid) {
    $config = esewa_config();
    $formattedTotal = esewa_format_amount($totalAmount);
    
    $url = $config['status_url'] . '?product_code=' . urlencode($config['product_code']) 
         . '&total_amount=' . urlencode($formattedTotal) 
         . '&transaction_uuid=' . urlencode($transactionUuid);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $json = json_decode($response, true);
        if (is_array($json)) {
            return $json;
        }
    }
    
    // Fallback using file_get_contents if curl fails
    $context = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
    $fallbackRes = @file_get_contents($url, false, $context);
    if ($fallbackRes) {
        $json = json_decode($fallbackRes, true);
        if (is_array($json)) {
            return $json;
        }
    }
    
    return null;
}
