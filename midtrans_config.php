<?php
// midtrans_config.php

require_once __DIR__ . '/vendor/autoload.php';

\Midtrans\Config::$serverKey = 'YOUR_SERVER_KEY';
\Midtrans\Config::$isProduction = false; // true jika production
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
?>