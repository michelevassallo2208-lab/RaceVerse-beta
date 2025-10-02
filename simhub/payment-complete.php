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

$provider = strtolower($_GET['provider'] ?? '');
$planCode = $_GET['plan'] ?? '';
$plan = SubscriptionManager::getPlan($planCode);
if (!$plan) {
    redirect_to('payment.php?status=error');
}

$config = app_config();
$activated = false;

if ($provider === 'stripe') {
    $sessionId = $_GET['session_id'] ?? '';
    $stripeConfig = $config['payments']['stripe'] ?? [];
    $secretKey = $stripeConfig['secret_key'] ?? '';
    if ($sessionId && $secretKey && $secretKey !== 'sk_test_replace_me') {
        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . urlencode($sessionId));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $secretKey . ':',
        ]);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($statusCode === 200 && $response) {
            $data = json_decode($response, true);
            if (($data['payment_status'] ?? '') === 'paid' || ($data['status'] ?? '') === 'complete') {
                SubscriptionManager::activate($user['id'], $planCode, 'Stripe Checkout - ' . $plan['label']);
                $activated = true;
            }
        } else {
            error_log('Stripe verification error: ' . $curlError . ' response:' . $response);
        }
    }
}

if ($provider === 'paypal') {
    $orderId = $_GET['token'] ?? '';
    $paypalConfig = $config['payments']['paypal'] ?? [];
    $clientId = $paypalConfig['client_id'] ?? '';
    $clientSecret = $paypalConfig['client_secret'] ?? '';
    if ($orderId && $clientId && $clientSecret && $clientId !== 'paypal_client_id') {
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
        if ($tokenStatus === 200 && $tokenResponse) {
            $tokenData = json_decode($tokenResponse, true);
            $accessToken = $tokenData['access_token'] ?? '';
            if ($accessToken) {
                $captureCh = curl_init($apiBase . '/v2/checkout/orders/' . urlencode($orderId) . '/capture');
                curl_setopt_array($captureCh, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $accessToken,
                    ],
                    CURLOPT_POSTFIELDS => '{}',
                ]);
                $captureResponse = curl_exec($captureCh);
                $captureStatus = curl_getinfo($captureCh, CURLINFO_RESPONSE_CODE);
                $captureError = curl_error($captureCh);
                curl_close($captureCh);
                if (($captureStatus === 200 || $captureStatus === 201) && $captureResponse) {
                    $captureData = json_decode($captureResponse, true);
                    if (($captureData['status'] ?? '') === 'COMPLETED') {
                        SubscriptionManager::activate($user['id'], $planCode, 'PayPal - ' . $plan['label']);
                        $activated = true;
                    }
                } else {
                    error_log('PayPal capture error: ' . $captureError . ' response:' . $captureResponse);
                }
            }
        } else {
            error_log('PayPal capture token error: ' . $tokenError . ' response:' . $tokenResponse);
        }
    }
}

if ($activated) {
    $freshUser = SubscriptionManager::fetchUser($user['id']);
    if ($freshUser) {
        $_SESSION['user'] = array_merge($_SESSION['user'], [
            'subscription_plan' => $freshUser['subscription_plan'],
            'subscription_active' => (bool)$freshUser['subscription_active'],
            'subscription_started_at' => $freshUser['subscription_started_at'],
            'subscription_renews_at' => $freshUser['subscription_renews_at'],
            'subscription_expires_at' => $freshUser['subscription_expires_at'] ?? $freshUser['subscription_renews_at'],
            'subscription_payment_method' => $freshUser['subscription_payment_method'],
            'subscription_cancel_at_period_end' => (bool)$freshUser['subscription_cancel_at_period_end'],
        ]);
    }
    redirect_to('payment.php?status=success');
}

redirect_to('payment.php?status=error');
