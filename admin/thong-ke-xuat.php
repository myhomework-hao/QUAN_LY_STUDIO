<?php
// Xuất báo cáo thống kê ra file Excel (HTML table, mở được bằng Excel và có màu/format)
require_once __DIR__ . '/khung_admin.php';

$tu = (string) ($_GET['tu'] ?? '');
$den = (string) ($_GET['den'] ?? '');
$kieu = ($_GET['kieu'] ?? 'ngay') === 'thang' ? 'thang' : 'ngay';
$loai = ($_GET['loai'] ?? 'tong_quan') === 'don' ? 'don' : 'tong_quan';

$kiem_tra = function (string $s): bool {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s;
};
if (!$kiem_tra($tu) || !$kiem_tra($den) || $tu > $den) {
    chuyen_huong('thong-ke.php');
}

$tu_gio = $tu . ' 00:00:00';
$den_gio = date('Y-m-d', strtotime($den . ' +1 day')) . ' 00:00:00';

$ten_file = 'vibe-' . ($loai === 'don' ? 'don-' : 'thong-ke-') . $tu . '_' . $den . '.xls';
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $ten_file . '"');
header('Pragma: no-cache');

echo "\xEF\xBB\xBF";
?>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; }
    h2 { color: #1f2937; }
    table { border-collapse: collapse; margin-bottom: 16px; }
    th, td { border: 1px solid #d1d5db; padding: 6px 10px; }
    th { background: #2563eb; color: #fff; font-weight: bold; }
    .tong { background: #f3f4f6; font-weight: bold; }
    .tieu-de { background: #111827; color: #fff; font-weight: bold; font-size: 14px; }
</style>
</head>
<body>
<?php
if ($loai === 'tong_quan') {
    $don_xong = lay_nhieu(
        $conn,
        "SELECT DDL_TongTien, COALESCE(DDL_ThoiDiemHoanThanh, DDL_NgayTao) AS thoi_diem FROM dondatlich
         WHERE DDL_TrangThai = ? AND COALESCE(DDL_ThoiDiemHoanThanh, DDL_NgayTao) >= ? AND COALESCE(DDL_ThoiDiemHoanThanh, DDL_NgayTao) < ?",
        [TRANG_THAI_HOAN_THANH, $tu_gio, $den_gio]
    );
    $doanh_thu = [];
    $tong_tien = 0.0;
    foreach ($don_xong as $d) {
        $khoa = date($kieu === 'ngay' ? 'Y-m-d' : 'Y-m', strtotime($d['thoi_diem']));
        $doanh_thu[$khoa] = ($doanh_thu[$khoa] ?? 0) + (float) $d['DDL_TongTien'];
        $tong_tien += (float) $d['DDL_TongTien'];
    }
    ksort($doanh_thu);
    $so_don_hoan_thanh = count($don_xong);

    $theo_trang_thai = lay_nhieu(
        $conn,
        "SELECT DDL_TrangThai, COUNT(*) AS so FROM dondatlich WHERE DDL_NgayTao >= ? AND DDL_NgayTao < ? GROUP BY DDL_TrangThai",
        [$tu_gio, $den_gio]
    );
    $tong_don = array_sum(array_column($theo_trang_thai, 'so'));

    $top_dich_vu = lay_nhieu(
        $conn,
        "SELECT dv.DV_Ma, dv.DV_Ten, dv.DV_Loai, COUNT(DISTINCT c.DDL_Ma) AS luot, COALESCE(SUM(c.CTD_ThanhTien), 0) AS tien
         FROM chitietdon c
         JOIN dondatlich d ON d.DDL_Ma = c.DDL_Ma
         JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma
         WHERE d.DDL_NgayTao >= ? AND d.DDL_NgayTao < ? AND d.DDL_TrangThai NOT IN (?, ?)
         GROUP BY dv.DV_Ma, dv.DV_Ten, dv.DV_Loai
         ORDER BY luot DESC, tien DESC LIMIT 10",
        [$tu_gio, $den_gio, TRANG_THAI_DA_HUY, TRANG_THAI_TU_CHOI]
    );
    ?>
    <h2>BÁO CÁO THỐNG KÊ - VIBE STUDIO</h2>
    <p>Từ ngày <?= h($tu) ?> đến ngày <?= h($den) ?></p>
    <table>
        <tr><th colspan="2" class="tieu-de">TỔNG QUAN</th></tr>
        <tr><td>Doanh thu (đơn hoàn thành, VND)</td><td><?= number_format($tong_tien, 0, ',', '.') ?></td></tr>
        <tr><td>Số đơn hoàn thành</td><td><?= $so_don_hoan_thanh ?></td></tr>
        <tr><td>Tổng số đơn đặt (mọi trạng thái)</td><td><?= $tong_don ?></td></tr>
        <tr><td>Giá trị trung bình / đơn (VND)</td><td><?= $so_don_hoan_thanh ? number_format(round($tong_tien / $so_don_hoan_thanh), 0, ',', '.') : 0 ?></td></tr>
    </table>

    <table>
        <tr><th colspan="2" class="tieu-de">DOANH THU THEO <?= $kieu === 'ngay' ? 'NGÀY' : 'THÁNG' ?></th></tr>
        <tr><th>Kỳ</th><th>Doanh thu (VND)</th></tr>
        <?php foreach ($doanh_thu as $khoa => $tien) : ?>
            <tr><td><?= h($khoa) ?></td><td><?= number_format($tien, 0, ',', '.') ?></td></tr>
        <?php endforeach; ?>
    </table>

    <table>
        <tr><th colspan="4" class="tieu-de">TOP DỊCH VỤ ĐƯỢC ĐẶT NHIỀU</th></tr>
        <tr><th>Dịch vụ</th><th>Loại</th><th>Số đơn</th><th>Tiền thuê (VND)</th></tr>
        <?php foreach ($top_dich_vu as $t) : ?>
            <tr><td><?= h($t['DV_Ten']) ?></td><td><?= h(ten_loai_dich_vu($t['DV_Loai'])) ?></td><td><?= (int) $t['luot'] ?></td><td><?= number_format((float) $t['tien'], 0, ',', '.') ?></td></tr>
        <?php endforeach; ?>
    </table>

    <table>
        <tr><th colspan="2" class="tieu-de">ĐƠN THEO TRẠNG THÁI</th></tr>
        <tr><th>Trạng thái</th><th>Số đơn</th></tr>
        <?php foreach ($theo_trang_thai as $t) : ?>
            <tr><td><?= h($t['DDL_TrangThai']) ?></td><td><?= (int) $t['so'] ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php
} else {
    $don = lay_nhieu(
        $conn,
        "SELECT d.DDL_Ma, kh.ND_Ten, kh.ND_Email, d.DDL_NgayTao, d.DDL_TrangThai,
                d.DDL_TongTien, d.DDL_MaGiamGia, d.DDL_SoTienGiam, nvnd.ND_Ten AS nv_xu_ly
         FROM dondatlich d
         JOIN nguoidung kh ON kh.ND_Ma = d.ND_Ma
         LEFT JOIN nhanvien nv ON nv.NV_Ma = d.NV_Ma
         LEFT JOIN nguoidung nvnd ON nvnd.ND_Ma = nv.ND_Ma
         WHERE d.DDL_NgayTao >= ? AND d.DDL_NgayTao < ?
         ORDER BY d.DDL_NgayTao DESC, d.DDL_Ma DESC",
        [$tu_gio, $den_gio]
    );

    $tong_cong = 0.0;
    foreach ($don as $d) {
        $tong_cong += (float) $d['DDL_TongTien'];
    }
    ?>
    <h2>DANH SÁCH ĐƠN THUÊ - VIBE STUDIO</h2>
    <p>Từ ngày <?= h($tu) ?> đến ngày <?= h($den) ?></p>
    <table>
        <tr>
            <th>Mã đơn</th><th>Khách hàng</th><th>Email</th><th>Ngày tạo</th><th>Trạng thái</th>
            <th>Tổng tiền (VND)</th><th>Mã giảm giá</th><th>Số tiền giảm (VND)</th><th>Nhân viên xử lý</th>
        </tr>
        <?php foreach ($don as $d) : ?>
            <tr>
                <td><?= (int) $d['DDL_Ma'] ?></td>
                <td><?= h($d['ND_Ten']) ?></td>
                <td><?= h($d['ND_Email']) ?></td>
                <td><?= h($d['DDL_NgayTao']) ?></td>
                <td><?= h($d['DDL_TrangThai']) ?></td>
                <td><?= number_format((float) $d['DDL_TongTien'], 0, ',', '.') ?></td>
                <td><?= h($d['DDL_MaGiamGia']) ?></td>
                <td><?= number_format((float) $d['DDL_SoTienGiam'], 0, ',', '.') ?></td>
                <td><?= h($d['nv_xu_ly']) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="tong">
            <td colspan="5">TỔNG</td>
            <td><?= number_format($tong_cong, 0, ',', '.') ?></td>
            <td colspan="3"></td>
        </tr>
    </table>
    <?php
}
?>
</body>
</html>