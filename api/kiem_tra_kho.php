<?php
// api/kiem_tra_kho.php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/dich_vu.php';
require_once '../includes/cart_helper.php';

header('Content-Type: application/json; charset=utf-8');

$ma_dv = (int) ($_GET['id'] ?? 0);
$bat_dau = trim($_GET['bat_dau'] ?? '');
$ket_thuc = trim($_GET['ket_thuc'] ?? '');
$kich_co = trim((string) ($_GET['kich_co'] ?? ''));

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
if ($tg === null || strtotime($tg['bat_dau']) <= time() || strtotime($tg['ket_thuc']) <= time()) {
    echo json_encode(['con_lai' => 0, 'loi' => 'Thời gian không hợp lệ']);
    exit;
}

// Tính tổng số lượng đã bị đặt trong khoảng thời gian trùng lặp
// (trang phục chọn size thì đếm riêng theo cột CTD_KichCo)
$sql_dat = "SELECT COALESCE(SUM(c.CTD_SoLuong), 0) AS da_dat
     FROM chitietdon c
     JOIN dondatlich d ON d.DDL_Ma = c.DDL_Ma
     WHERE c.DV_Ma = ?
       AND d.DDL_TrangThai IN ('Chờ duyệt', 'Đã duyệt')
       AND NOT (c.CTD_KetThuc <= ? OR c.CTD_BatDau >= ?)";
$tham_so_dat = [$ma_dv, $tg['ket_thuc'], $tg['bat_dau']];

$tong_kho = (int) $dv['DV_SoLuong'];

// Trang phục có size: tồn kho theo dichvu_kichco của đúng size khách chọn;
// số đã đặt cũng đếm riêng theo size (cột CTD_KichCo).
if ($kich_co !== '') {
    $stmt_size = $conn->prepare(
        "SELECT DVK_SoLuong FROM dichvu_kichco
         WHERE DV_Ma = ? AND DVK_Ten = ? AND DVK_DangHoatDong = 1"
    );
    $stmt_size->execute([$ma_dv, $kich_co]);
    $kho_size = $stmt_size->fetchColumn();

    if ($kho_size === false) {
        echo json_encode([
            'tong_kho' => 0, 'da_dat' => 0, 'con_lai' => 0,
            'bat_dau' => $tg['bat_dau'], 'ket_thuc' => $tg['ket_thuc']
        ]);
        exit;
    }

    $sql_dat .= ' AND c.CTD_KichCo = ?';
    $tham_so_dat[] = $kich_co;
    $tong_kho = (int) $kho_size;
}

$stmt_dat = $conn->prepare($sql_dat);
$stmt_dat->execute($tham_so_dat);
$da_dat = (int) $stmt_dat->fetchColumn();

$con_lai = max(0, $tong_kho - $da_dat);

echo json_encode([
    'tong_kho' => $tong_kho,
    'da_dat' => $da_dat,
    'con_lai' => $con_lai,
    'bat_dau' => $tg['bat_dau'],
    'ket_thuc' => $tg['ket_thuc']
]);