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
  'app_url' => getenv('APP_URL') ?: 'https://raceverse.it',
  'mail' => [
      'host' => 'smtp.ionos.it',
      'port' => 465,
      'username' => 'noreply@raceverse.it',
      'password' => 'Raceverse1!',
      'encryption' => 'ssl',
      'from_email' => 'noreply@raceverse.it',
      'from_name' => 'Raceverse',
  ],
];
