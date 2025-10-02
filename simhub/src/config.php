<?php
return [
  'db' => [
      'host' => 'localhost',
      'dbname' => 'simhub',
      'user' => 'root',
      'pass' => '',
      'charset' => 'utf8mb4',
  ],
  'app_key' => 'change_me_please_32chars_min',
  'app_url' => 'https://raceverse.it',
  'mail' => [
      'host' => 'smtp.ionos.it',
      'port' => 465,
      'encryption' => 'ssl',
      'username' => 'noreply@raceverse.it',
      'password' => 'Raceverse1!',
      'from_email' => 'noreply@raceverse.it',
      'from_name' => 'RaceVerse',
  ],
  'payments' => [
      'currency' => 'EUR',
      'stripe' => [
          'secret_key' => 'sk_test_replace_me',
          'publishable_key' => 'pk_test_replace_me',
          'webhook_secret' => 'whsec_replace_me',
      ],
      'paypal' => [
          'client_id' => 'paypal_client_id',
          'client_secret' => 'paypal_client_secret',
          'environment' => 'sandbox',
      ],
  ],
];
