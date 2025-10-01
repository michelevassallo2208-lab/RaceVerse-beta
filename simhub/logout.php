<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/helpers.php';
Auth::logout();
redirect_to('index.php');
