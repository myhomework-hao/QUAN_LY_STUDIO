<?php
require_once __DIR__ . '/config/app.php';   // ← THÊM DÒNG NÀY

header('Content-Type: text/plain; charset=utf-8');
echo 'PHP timezone: ' . date_default_timezone_get() . "\n";
echo 'PHP date():   ' . date('Y-m-d H:i:s') . "\n";