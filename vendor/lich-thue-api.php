<?php
// API đã chuyển sang api/lich-thue-api.php
$query = $_GET ? '?' . http_build_query($_GET) : '';
header('Location: ../api/lich-thue-api.php' . $query);
exit;