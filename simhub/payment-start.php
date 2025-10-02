<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/SubscriptionManager.php';

Auth::start();
$user = Auth::user();
if (!$user) {
    redirect_to('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('payment.php');
}

$planCode = $_POST['plan'] ?? '';
$provider = strtolower($_POST['provider'] ?? '');
$plan = SubscriptionManager::getPlan($planCode);
if (!$plan) {
    redirect_to('payment.php?status=error');
}

$config = app_config();
$currency = strtolower($config['payments']['currency'] ?? 'eur');

if ($provider === 'stripe') {
    $stripeConfig = $config['payments']['stripe'] ?? [];
    $secretKey = $stripeConfig['secret_key'] ?? '';
    if (!$secretKey || $secretKey === 'sk_test_replace_me') {
        redirect_to('payment.php?status=error');
    }
    $successUrl = absolute_url('payment-complete.php?provider=stripe&plan=' . urlencode($planCode) . '&session_id={CHECKOUT_SESSION_ID}');
    $cancelUrl = absolute_url('payment.php?status=cancelled');
    $payload = http_build_query([
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'mode' => 'payment',
        'metadata[user_id]' => $user['id'],
        'metadata[plan_code]' => $planCode,
        'line_items[0][price_data][currency]' => $currency,
        'line_items[0][price_data][product_data][name]' => $plan['name'],
        'line_items[0][price_data][unit_amount]' => (int)round($plan['price_eur'] * 100),
        'line_items[0][quantity]' => 1,
    ]);
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => $secretKey . ':',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    if ($statusCode === 200 && $response) {
        $data = json_decode($response, true);
        if (!empty($data['url'])) {
            header('Location: ' . $data['url']);
            exit;
        }
    }
    error_log('Stripe checkout error: ' . $curlError . ' response:' . $response);
    redirect_to('payment.php?status=error');
}

if ($provider === 'paypal') {
    $paypalConfig = $config['payments']['paypal'] ?? [];
    $clientId = $paypalConfig['client_id'] ?? '';
    $clientSecret = $paypalConfig['client_secret'] ?? '';
    if (!$clientId || !$clientSecret || $clientId === 'paypal_client_id') {
        redirect_to('payment.php?status=error');
    }
    $environment = strtolower($paypalConfig['environment'] ?? 'sandbox');
    $apiBase = $environment === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

    $tokenCh = curl_init($apiBase . '/v1/oauth2/token');
    curl_setopt_array($tokenCh, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => $clientId . ':' . $clientSecret,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Accept-Language: en_US',
        ],
    ]);
    $tokenResponse = curl_exec($tokenCh);
    $tokenStatus = curl_getinfo($tokenCh, CURLINFO_RESPONSE_CODE);
    $tokenError = curl_error($tokenCh);
    curl_close($tokenCh);
    if ($tokenStatus !== 200 || !$tokenResponse) {
        error_log('PayPal token error: ' . $tokenError . ' response:' . $tokenResponse);
        redirect_to('payment.php?status=error');
    }
    $tokenData = json_decode($tokenResponse, true);
    $accessToken = $tokenData['access_token'] ?? '';
    if (!$accessToken) {
        redirect_to('payment.php?status=error');
    }

    $orderPayload = json_encode([
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'amount' => [
                'currency_code' => strtoupper($currency),
                'value' => number_format($plan['price_eur'], 2, '.', ''),
            ],
            'description' => $plan['name'],
        ]],
        'application_context' => [
            'brand_name' => 'RaceVerse',
            'landing_page' => 'LOGIN',
            'user_action' => 'PAY_NOW',
            'return_url' => absolute_url('payment-complete.php?provider=paypal&plan=' . urlencode($planCode) . '&token={token}'),
            'cancel_url' => absolute_url('payment.php?status=cancelled'),
        ],
    ]);

    $orderCh = curl_init($apiBase . '/v2/checkout/orders');
    curl_setopt_array($orderCh, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ],
        CURLOPT_POSTFIELDS => $orderPayload,
    ]);
    $orderResponse = curl_exec($orderCh);
    $orderStatus = curl_getinfo($orderCh, CURLINFO_RESPONSE_CODE);
    $orderError = curl_error($orderCh);
    curl_close($orderCh);
    if ($orderStatus === 201 && $orderResponse) {
        $orderData = json_decode($orderResponse, true);
        if (!empty($orderData['links'])) {
            foreach ($orderData['links'] as $link) {
                if (($link['rel'] ?? '') === 'approve' && !empty($link['href'])) {
                    header('Location: ' . $link['href']);
                    exit;
                }
            }
        }
    }
    error_log('PayPal order error: ' . $orderError . ' response:' . $orderResponse);
    redirect_to('payment.php?status=error');
}

redirect_to('payment.php?status=error');
