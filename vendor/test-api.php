<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/donhang.php';
header('Content-Type: application/json; charset=utf-8');
$start = '2026-10-26T00:00:00+07:00';
$end = '2026-12-07T00:00:00+07:00';
$tu = date('Y-m-d H:i:s', strtotime($start));
$den = date('Y-m-d H:i:s', strtotime($end));
$stmt = $conn->prepare("SELECT d.DDL_Ma, d.DDL_TenNguoiThue, d.DDL_TrangThai, c.DV_Ma, dv.DV_Ten, c.CTD_BatDau, c.CTD_KetThuc FROM dondatlich d JOIN chitietdon c ON c.DDL_Ma=d.DDL_Ma JOIN dichvu dv ON dv.DV_Ma=c.DV_Ma WHERE c.CTD_BatDau < ? AND c.CTD_KetThuc > ? AND d.DDL_TrangThai NOT IN ('Đã từ chối','Đã hủy') ORDER BY c.CTD_BatDau");
$stmt->execute([$den,$tu]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
exit;