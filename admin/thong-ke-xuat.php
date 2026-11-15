<?php
// Xuất báo cáo thống kê ra CSV (mở được bằng Excel): loai=tong_quan | loai=don
// GET + không thay đổi dữ liệu nên không cần CSRF; quyền admin do khung_admin.php chặn sẵn
require_once __DIR__ . '/khung_admin.php';

$tu = (string) ($_GET['tu'] ?? '');
$den = (string) ($_GET['den'] ?? '');
$kieu = ($_GET['kieu'] ?? 'ngay') === 'thang' ? 'thang' : 'ngay';
$loai = ($_GET['loai'] ?? 'tong_quan') === 'don' ? 'don' : 'tong_quan';

// Lọc sai tham số thì quay lại trang thống kê, không phát file rác
$kiem_tra = function (string $s): bool {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s;
};
if (!$kiem_tra($tu) || !$kiem_tra($den) || $tu > $den) {
    chuyen_huong('thong-ke.php');
}

$tu_gio = $tu . ' 00:00:00';
$den_gio = date('Y-m-d', strtotime($den . ' +1 day')) . ' 00:00:00';

$ten_file = 'vibe-' . ($loai === 'don' ? 'don-' : 'thong-ke-') . $tu . '_' . $den . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $ten_file . '"');
header('Pragma: no-cache');

// BOM UTF-8 để Excel mở tiếng Việt không lỗi font
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');
$csv = function (array $dong) use ($out): void {
    fputcsv($out, $dong);
};

if ($loai === 'tong_quan') {
    // Ba khối số liệu tính giống y trang thong-ke.php để file khớp màn hình
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

    $csv(['BÁO CÁO THỐNG KÊ - VIBE STUDIO']);
    $csv(['Từ ngày', $tu, 'Đến ngày', $den]);
    $csv([]);
    $csv(['TỔNG QUAN']);
    $csv(['Doanh thu (đơn hoàn thành, VND)', $tong_tien]);
    $csv(['Số đơn hoàn thành', $so_don_hoan_thanh]);
    $csv(['Tổng số đơn đặt (mọi trạng thái)', $tong_don]);
    $csv(['Giá trị trung bình / đơn (VND)', $so_don_hoan_thanh ? round($tong_tien / $so_don_hoan_thanh) : 0]);
    $csv([]);
    $csv(['DOANH THU THEO ' . ($kieu === 'ngay' ? 'NGÀY' : 'THÁNG')]);
    $csv(['Kỳ', 'Doanh thu (VND)']);
    foreach ($doanh_thu as $khoa => $tien) {
        $csv([$khoa, $tien]);
    }
    $csv([]);
    $csv(['TOP DỊCH VỤ ĐƯỢC ĐẶT NHIỀU']);
    $csv(['Dịch vụ', 'Loại', 'Số đơn', 'Tiền thuê (VND)']);
    foreach ($top_dich_vu as $t) {
        $csv([$t['DV_Ten'], $t['DV_Loai'], (int) $t['luot'], (float) $t['tien']]);
    }
    $csv([]);
    $csv(['ĐƠN THEO TRẠNG THÁI']);
    $csv(['Trạng thái', 'Số đơn']);
    foreach ($theo_trang_thai as $t) {
        $csv([$t['DDL_TrangThai'], (int) $t['so']]);
    }
} else {
    // Danh sách đơn chi tiết trong khoảng đã chọn, kèm dòng tổng cuối bảng
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

    $csv(['DANH SÁCH ĐƠN THUÊ - VIBE STUDIO']);
    $csv(['Từ ngày', $tu, 'Đến ngày', $den]);
    $csv([]);
    $csv(['Mã đơn', 'Khách hàng', 'Email', 'Ngày tạo', 'Trạng thái', 'Tổng tiền (VND)', 'Mã giảm giá', 'Số tiền giảm (VND)', 'Nhân viên xử lý']);
    foreach ($don as $d) {
        $csv([
            (int) $d['DDL_Ma'],
            $d['ND_Ten'],
            $d['ND_Email'],
            $d['DDL_NgayTao'],
            $d['DDL_TrangThai'],
            (float) $d['DDL_TongTien'],
            $d['DDL_MaGiamGia'],
            (float) $d['DDL_SoTienGiam'],
            $d['nv_xu_ly'],
        ]);
    }
    $csv(['TỔNG', '', '', '', '', $tong_cong]);
}

fclose($out);
exit;
