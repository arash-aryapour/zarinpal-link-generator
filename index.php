<?php
/**
 * ZarinPal Payment Link Generator
 * A simple script to generate ZarinPal payment links without the need for tax codes or eNamad.
 * GitHub: https://github.com/arash-aryapour/zarinpal-link-generator
 * 
 * @license MIT
 * @version 1.0
 */

// --- Configuration ---
require_once __DIR__ . '/config.php';

// --- Functions ---
function generateRandomName() {
    $firstNames = ['محمد', 'علی', 'رضا', 'حسین', 'فاطمه', 'زهرا', 'مریم'];
    $lastNames = ['محمدی', 'رضایی', 'حسینی', 'کریمی', 'موسوی'];
    return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
}

function generateRandomMobile() {
    return '09' . mt_rand(100000000, 999999999);
}

function generateUserAgent() {
    $agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0'
    ];
    return $agents[array_rand($agents)];
}

// --- Main Logic ---
header('Content-Type: text/html; charset=utf-8');

// Get amount from query parameter (default: 20,000 Tomans)
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 20000;
$amountRial = $amount * 10; // Convert to Rials

// Prepare request data
$requestData = [
    'name' => generateRandomName(),
    'mobile' => generateRandomMobile(),
    'email' => '',
    'description' => '',
    'amount' => (string)$amountRial,
    'coupon' => ''
];

// Send request to ZarinPal
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://zarinp.al/api/v4/zarinLink/checkout/" . ZARINPAL_LINK_ID . ".json",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestData),
    CURLOPT_HTTPHEADER => [
    'accept: application/json, text/plain, */*',
    'accept-encoding: gzip, deflate, br, zstd',
    'accept-language: en-US,en;q=0.9',
    'content-type: application/json',
    'origin: https://zarinp.al',
    'referer: https://zarinp.al/' . ZARINPAL_LINK_ID,
    'user-agent: ' . generateUserAgent()

    ]
]);

$response = curl_exec($ch);
curl_close($ch);

// Process response
$responseData = json_decode($response, true);

if (isset($responseData['data']['authority'])) {
    $paymentLink = 'https://www.zarinpal.com/pg/StartPay/' . $responseData['data']['authority'];
    echo "<h2>لینک پرداخت زرین‌پال:</h2>";
    echo "<p><a href='{$paymentLink}' target='_blank'>{$paymentLink}</a></p>";
} else {
    echo "<h2>خطا در ایجاد لینک پرداخت</h2>";
    echo "<pre>" . htmlspecialchars(print_r($responseData, true)) . "</pre>";
}
