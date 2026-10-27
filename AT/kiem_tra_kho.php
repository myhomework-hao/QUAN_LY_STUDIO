<?php
// AT/kiem_tra_kho.php
require_once '../config/app.php';
require_once '../config/db.php';
require_once '../includes/dich_vu.php';

header('Content-Type: application/json; charset=utf-8');

$ma_dv = (int) ($_GET['id'] ?? 0);
$bat_dau = trim($_GET['bat_dau'] ?? '');
$ket_thuc = trim($_GET['ket_thuc'] ?? '');

if ($ma_dv <= 0 || $bat_dau === '' || $ket_thuc === '') {
    echo json_encode(['con_lai' => 0, 'loi' => 'Thiếu thông tin']);
    exit;
}

// Lấy tổng kho của dịch vụ từ CSDL
$stmt = $conn->prepare("SELECT DV_SoLuong, DV_DonViTinh FROM dichvu WHERE DV_Ma = ? AND DV_DangBan = 1");
$stmt->execute([$ma_dv]);
$dv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dv) {
    echo json_encode(['con_lai' => 0, 'loi' => 'Sản phẩm không tồn tại']);
    exit;
}

$tg = thoi_gian_thue($dv['DV_DonViTinh'], $bat_dau, $ket_thuc);
if ($tg === null || strtotime($tg['ket_thuc']) <= time()) {
    echo json_encode(['con_lai' => 0, 'loi' => 'Thời gian không hợp lệ']);
    exit;
}

// Tính tổng số lượng đã bị đặt trong khoảng thời gian trùng lặp
$stmt_dat = $conn->prepare(
    "SELECT COALESCE(SUM(c.CTD_SoLuong), 0) AS da_dat
     FROM chitietdon c
     JOIN dondatlich d ON d.DDL_Ma = c.DDL_Ma
     WHERE c.DV_Ma = ? 
       AND d.DDL_TrangThai IN ('Chờ duyệt', 'Đã duyệt')
       AND NOT (c.CTD_KetThuc <= ? OR c.CTD_BatDau >= ?)"
);
$stmt_dat->execute([$ma_dv, $tg['bat_dau'], $tg['ket_thuc']]);
$da_dat = (int) $stmt_dat->fetchColumn();

$tong_kho = (int) $dv['DV_SoLuong'];
$con_lai = max(0, $tong_kho - $da_dat);

echo json_encode([
    'tong_kho' => $tong_kho,
    'da_dat' => $da_dat,
    'con_lai' => $con_lai,
    'bat_dau' => $tg['bat_dau'],
    'ket_thuc' => $tg['ket_thuc']
]);