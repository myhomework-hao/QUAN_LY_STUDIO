<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/donhang.php';
require_once __DIR__ . '/../includes/security.php';

header('Content-Type: application/json; charset=utf-8');

if (vai_tro_hien_tai() !== 'nhanvien') {
    http_response_code(403);
    echo json_encode(['error' => 'Chưa đăng nhập nhân viên.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$start = trim((string) ($_GET['start'] ?? ''));
$end = trim((string) ($_GET['end'] ?? ''));
$locLoai = trim((string) ($_GET['loai'] ?? ''));
$locTrangThai = trim((string) ($_GET['trang_thai'] ?? ''));

if ($start === '' || $end === '') {
    echo json_encode([]);
    exit;
}

$tu = date('Y-m-d H:i:s', strtotime($start));
$den = date('Y-m-d H:i:s', strtotime($end));

$dieuKien = ['c.CTD_BatDau < ?', 'c.CTD_KetThuc > ?', "d.DDL_TrangThai NOT IN ('Đã từ chối', 'Đã hủy')"];
$thamSo = [$den, $tu];

if ($locLoai !== '' && in_array($locLoai, ['phong_chup', 'thiet_bi', 'trang_phuc', 'phu_kien', 'dich_vu', 'goi_chup'], true)) {
    $dieuKien[] = 'dv.DV_Loai = ?';
    $thamSo[] = $locLoai;
}
if ($locTrangThai !== '' && $locTrangThai !== 'Đã từ chối' && $locTrangThai !== 'Đã hủy') {
    $dieuKien[] = 'd.DDL_TrangThai = ?';
    $thamSo[] = $locTrangThai;
}

$where = implode(' AND ', $dieuKien);
$stmt = $conn->prepare(
    "SELECT d.DDL_Ma, d.DDL_TenNguoiThue, d.DDL_TrangThai,
            c.DV_Ma, dv.DV_Ten, c.CTD_BatDau, c.CTD_KetThuc
     FROM dondatlich d
     JOIN chitietdon c ON c.DDL_Ma = d.DDL_Ma
     JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma
     WHERE $where
     ORDER BY c.CTD_BatDau ASC"
);
$stmt->execute($thamSo);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($rows as $row) {
    $trangThai = (string) $row['DDL_TrangThai'];
    $mau = '#777777';
    if ($trangThai === 'Chờ duyệt') {
        $mau = '#b08d57';
    } elseif ($trangThai === 'Đã duyệt') {
        $mau = '#49352b';
    } elseif ($trangThai === 'Đang thuê') {
        $mau = '#2e6b3f';
    }

    $batDauTs = strtotime((string) $row['CTD_BatDau']);
    $ketThucTs = strtotime((string) $row['CTD_KetThuc']);
    if ($ketThucTs <= $batDauTs) {
        $ketThucTs = $batDauTs + 3600;
    }

    $events[] = [
        'id' => (int) $row['DDL_Ma'],
        'title' => $row['DV_Ten'] . ' - ' . $row['DDL_TenNguoiThue'],
        'start' => date('Y-m-d\TH:i:s', $batDauTs),
        'end' => date('Y-m-d\TH:i:s', $ketThucTs),
        'color' => $mau,
        'extendedProps' => [
            'maDon' => (int) $row['DDL_Ma'],
            'trangThai' => $trangThai,
            'khach' => (string) $row['DDL_TenNguoiThue'],
            'thietBi' => (string) $row['DV_Ten'],
            'batDau' => (string) $row['CTD_BatDau'],
            'ketThuc' => (string) $row['CTD_KetThuc'],
        ],
    ];
}

echo json_encode($events, JSON_UNESCAPED_UNICODE);
exit;