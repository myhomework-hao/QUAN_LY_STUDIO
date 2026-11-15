<?php
// Quản lý đơn hàng (admin): chỉ xem + lọc + phân trang + chi tiết đầy đủ.
// Mọi thay đổi trạng thái vẫn thuộc luồng nhân viên (vendor-orders.php) — trang này không ghi dữ liệu.
require_once __DIR__ . '/khung_admin.php';

$ma_xem = (int) ($_GET['id'] ?? 0);

if ($ma_xem > 0) {
    $don = lay_mot(
        $conn,
        "SELECT d.*, kh.ND_Ten, kh.ND_Email, kh.ND_SDT,
                nvnd.ND_Ten AS nv_xu_ly, nvht.ND_Ten AS nv_hoan_thanh
         FROM dondatlich d
         JOIN nguoidung kh ON kh.ND_Ma = d.ND_Ma
         LEFT JOIN nhanvien nv ON nv.NV_Ma = d.NV_Ma
         LEFT JOIN nguoidung nvnd ON nvnd.ND_Ma = nv.ND_Ma
         LEFT JOIN nhanvien nvh ON nvh.NV_Ma = d.NV_MaHoanThanh
         LEFT JOIN nguoidung nvht ON nvht.ND_Ma = nvh.ND_Ma
         WHERE d.DDL_Ma = ?",
        [$ma_xem]
    );
    if (!$don) {
        dat_thong_bao('Không tìm thấy đơn thuê.', 'error');
        chuyen_huong('don-hang.php');
    }

    $don_chi_tiet = lay_nhieu(
        $conn,
        "SELECT c.CTD_SoLuong, c.CTD_KichCo, c.CTD_BatDau, c.CTD_KetThuc, c.CTD_DonGia, c.CTD_ThanhTien,
                dv.DV_Ten, dv.DV_Loai, dv.DV_DonViTinh
         FROM chitietdon c JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma
         WHERE c.DDL_Ma = ?
         ORDER BY c.CTD_Ma",
        [$ma_xem]
    );
    $don_thanh_toan = lay_nhieu(
        $conn,
        "SELECT TT_Loai, TT_SoTien, TT_PhuongThuc, TT_MaThamChieu, TT_TrangThai, TT_ThoiDiem
         FROM thanhtoan WHERE DDL_Ma = ? ORDER BY TT_Ma",
        [$ma_xem]
    );

    admin_dau('Chi tiết đơn ' . ma_don_hien_thi($ma_xem), 'don-hang');
    ?>
    <section class="panel">
        <div class="panel-head">
            <h2>Đơn <?= h(ma_don_hien_thi($ma_xem)) ?></h2>
            <a href="don-hang.php<?= ($_SERVER['QUERY_STRING'] ?? '') ? '?' . h($_SERVER['QUERY_STRING']) : '' ?>" class="panel-link">← Danh sách</a>
        </div>
        <dl class="detail-grid">
            <dt>Khách hàng</dt><dd><?= h($don['ND_Ten']) ?> · <?= h($don['ND_Email']) ?></dd>
            <dt>Điện thoại khách</dt><dd><?= h($don['ND_SDT'] ?: '—') ?></dd>
            <dt>Người thuê</dt><dd><?= h($don['DDL_TenNguoiThue'] ?: '—') ?> · <?= h($don['DDL_SDTNguoiThue'] ?: '—') ?></dd>
            <dt>CCCD</dt><dd><?= h($don['DDL_CCCD'] ?: '—') ?></dd>
            <dt>Địa chỉ</dt><dd><?= h($don['DDL_DiaChi'] ?: '—') ?></dd>
            <dt>Ngày tạo</dt><dd><?= h(dinh_dang_ngay_gio($don['DDL_NgayTao'])) ?></dd>
            <dt>Tổng tiền (đã trừ giảm)</dt><dd><?= h(dinh_dang_tien($don['DDL_TongTien'])) ?></dd>
            <dt>Tiền cọc</dt><dd><?= (float) $don['DDL_TienCoc'] > 0 ? h(dinh_dang_tien($don['DDL_TienCoc'])) : 'Không đặt cọc' ?></dd>
            <dt>Mã giảm giá</dt>
            <dd><?= $don['DDL_MaGiamGia'] ? h($don['DDL_MaGiamGia']) . ' (giảm ' . h(dinh_dang_tien($don['DDL_SoTienGiam'])) . ')' : '—' ?></dd>
            <dt>Ghi chú</dt><dd><?= h($don['DDL_GhiChu'] ?: '—') ?></dd>
            <dt>Trạng thái</dt>
            <dd><span class="badge <?= lop_badge_don($don['DDL_TrangThai']) ?>"><?= h($don['DDL_TrangThai']) ?></span></dd>
            <?php if ($don['DDL_LyDoTuChoi']) : ?>
                <dt>Lý do từ chối</dt><dd><?= h($don['DDL_LyDoTuChoi']) ?><?= $don['DDL_ThoiDiemXuLy'] ? ' (' . h(dinh_dang_ngay_gio($don['DDL_ThoiDiemXuLy'])) . ')' : '' ?></dd>
            <?php endif; ?>
            <?php if ($don['DDL_ThoiDiemHuy']) : ?>
                <dt>Hủy lúc</dt><dd><?= h(dinh_dang_ngay_gio($don['DDL_ThoiDiemHuy'])) ?><?= $don['DDL_LyDoHuy'] ? ' · ' . h($don['DDL_LyDoHuy']) : '' ?></dd>
            <?php endif; ?>
            <?php if ($don['nv_xu_ly']) : ?>
                <dt>Nhân viên duyệt</dt><dd><?= h($don['nv_xu_ly']) ?><?= $don['DDL_ThoiDiemXuLy'] ? ' · ' . h(dinh_dang_ngay_gio($don['DDL_ThoiDiemXuLy'])) : '' ?></dd>
            <?php endif; ?>
            <?php if ($don['nv_hoan_thanh']) : ?>
                <dt>Hoàn thành lúc</dt><dd><?= h(dinh_dang_ngay_gio($don['DDL_ThoiDiemHoanThanh'])) ?> · NV <?= h($don['nv_hoan_thanh']) ?></dd>
            <?php endif; ?>
            <dt>Hoàn tiền</dt>
            <dd><?= h((string) ($don['DDL_TrangThaiHoanTien'] ?? HOAN_TIEN_KHONG)) ?><?= (float) ($don['DDL_SoTienHoan'] ?? 0) > 0 ? ' (' . h(dinh_dang_tien($don['DDL_SoTienHoan'])) . ' · ' . h($don['DDL_NganHangHoan'] ?: '—') . ' · TK ' . h($don['DDL_SoTKHoan'] ?: '—') . ')' : '' ?></dd>
        </dl>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Sản phẩm trong đơn</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Dịch vụ</th><th>Loại</th><th>Đơn vị</th><th>Số lượng</th><th>Size</th><th>Bắt đầu</th><th>Kết thúc</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
                <tbody>
                    <?php foreach ($don_chi_tiet as $c) : ?>
                        <tr>
                            <td><?= h($c['DV_Ten']) ?></td>
                            <td><?= h(ten_loai_dich_vu($c['DV_Loai'])) ?></td>
                            <td><?= h($c['DV_DonViTinh']) ?></td>
                            <td><?= (int) $c['CTD_SoLuong'] ?></td>
                            <td><?= h($c['CTD_KichCo'] ?: '—') ?></td>
                            <td><?= h(dinh_dang_ngay_gio($c['CTD_BatDau'])) ?></td>
                            <td><?= h(dinh_dang_ngay_gio($c['CTD_KetThuc'])) ?></td>
                            <td><?= h(dinh_dang_tien($c['CTD_DonGia'])) ?></td>
                            <td><?= h(dinh_dang_tien($c['CTD_ThanhTien'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Thanh toán</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Loại</th><th>Số tiền</th><th>Phương thức</th><th>Mã tham chiếu</th><th>Trạng thái</th><th>Thời điểm</th></tr></thead>
                <tbody>
                    <?php if (!$don_thanh_toan) : ?><tr><td colspan="6" class="empty">Chưa có bản ghi thanh toán.</td></tr><?php endif; ?>
                    <?php foreach ($don_thanh_toan as $t) : ?>
                        <tr>
                            <td><?= h($t['TT_Loai']) ?></td>
                            <td><?= h(dinh_dang_tien($t['TT_SoTien'])) ?></td>
                            <td><?= h($t['TT_PhuongThuc']) ?></td>
                            <td><?= h($t['TT_MaThamChieu'] ?: '—') ?></td>
                            <td><span class="badge <?= $t['TT_TrangThai'] === 'Thành công' ? 'badge-hoan-thanh' : 'badge-cho' ?>"><?= h($t['TT_TrangThai']) ?></span></td>
                            <td><?= h(dinh_dang_ngay_gio($t['TT_ThoiDiem'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
    admin_cuoi();
    exit;
}

// ---- DANH SÁCH ĐƠN ----
$tu_khoa = trim((string) ($_GET['q'] ?? ''));
$trang_thai = trim((string) ($_GET['tt'] ?? ''));
$tu = (string) ($_GET['tu'] ?? '');
$den = (string) ($_GET['den'] ?? '');
$kiem_tra = function (string $s): bool {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s;
};
if (!$kiem_tra($tu)) { $tu = ''; }
if (!$kiem_tra($den)) { $den = ''; }

// Trạng thái chỉ nhận giá trị chuẩn từ includes/donhang.php, tránh gõ sai dấu qua URL
$cac_trang_thai = [TRANG_THAI_CHO_DUYET, TRANG_THAI_DA_DUYET, TRANG_THAI_HOAN_THANH, TRANG_THAI_TU_CHOI, TRANG_THAI_DA_HUY];

$dieu_kien = ["1=1"];
$tham_so = [];
if ($tu_khoa !== '') {
    $dieu_kien[] = "(d.DDL_Ma = ? OR kh.ND_Ten LIKE ? OR kh.ND_Email LIKE ? OR d.DDL_TenNguoiThue LIKE ?)";
    $like = '%' . $tu_khoa . '%';
    array_push($tham_so, (int) $tu_khoa, $like, $like, $like);
}
if ($trang_thai !== '' && in_array($trang_thai, $cac_trang_thai, true)) {
    $dieu_kien[] = "d.DDL_TrangThai = ?";
    $tham_so[] = $trang_thai;
}
if ($tu !== '') {
    $dieu_kien[] = "d.DDL_NgayTao >= ?";
    $tham_so[] = $tu . ' 00:00:00';
}
if ($den !== '') {
    $dieu_kien[] = "d.DDL_NgayTao < ?";
    $tham_so[] = date('Y-m-d', strtotime($den . ' +1 day')) . ' 00:00:00';
}
$where = implode(' AND ', $dieu_kien);

$tong = (int) gia_tri(
    $conn,
    "SELECT COUNT(*) FROM dondatlich d JOIN nguoidung kh ON kh.ND_Ma = d.ND_Ma WHERE $where",
    $tham_so
);
$tong_trang = 1;
$trang = trang_hien_tai($tong, $tong_trang);
$lech = ($trang - 1) * SO_DONG_MOI_TRANG;

$ds = lay_nhieu(
    $conn,
    "SELECT d.DDL_Ma, d.DDL_TongTien, d.DDL_TrangThai, d.DDL_NgayTao, d.DDL_MaGiamGia, d.DDL_TienCoc,
            d.DDL_TrangThaiHoanTien, d.DDL_SoTienHoan,
            kh.ND_Ten, kh.ND_Email, nvnd.ND_Ten AS nv_xu_ly
     FROM dondatlich d
     JOIN nguoidung kh ON kh.ND_Ma = d.ND_Ma
     LEFT JOIN nhanvien nv ON nv.NV_Ma = d.NV_Ma
     LEFT JOIN nguoidung nvnd ON nvnd.ND_Ma = nv.ND_Ma
     WHERE $where
     ORDER BY d.DDL_Ma DESC LIMIT " . SO_DONG_MOI_TRANG . " OFFSET $lech",
    $tham_so
);

admin_dau('Đơn hàng', 'don-hang');
?>
        <section class="panel">
            <div class="panel-head">
                <h2>Đơn đặt lịch (<?= $tong ?>)</h2>
            </div>

            <form class="filter" method="get">
                <div class="field wide">
                    <label for="q">Tìm theo mã đơn, tên, email khách</label>
                    <input id="q" name="q" value="<?= h($tu_khoa) ?>">
                </div>
                <div class="field">
                    <label for="tt">Trạng thái</label>
                    <select id="tt" name="tt">
                        <option value="">Tất cả</option>
                        <?php foreach ($cac_trang_thai as $tt) : ?>
                            <option value="<?= h($tt) ?>" <?= $trang_thai === $tt ? 'selected' : '' ?>><?= h($tt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field"><label for="tu">Từ ngày</label><input id="tu" name="tu" type="date" value="<?= h($tu) ?>"></div>
                <div class="field"><label for="den">Đến ngày</label><input id="den" name="den" type="date" value="<?= h($den) ?>"></div>
                <button class="btn-solid" type="submit">Lọc</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Cọc</th><th>Giảm giá</th><th>NV xử lý</th><th>Trạng thái</th><th><span class="sr-only">Thao tác</span></th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$ds) : ?><tr><td colspan="9" class="empty">Không tìm thấy đơn nào.</td></tr><?php endif; ?>
                        <?php foreach ($ds as $d) : ?>
                            <tr>
                                <td><?= h(ma_don_hien_thi((int) $d['DDL_Ma'])) ?></td>
                                <td><?= h($d['ND_Ten']) ?><br><span class="muted"><?= h($d['ND_Email']) ?></span></td>
                                <td><?= h(dinh_dang_ngay_gio($d['DDL_NgayTao'])) ?></td>
                                <td><?= h(dinh_dang_tien($d['DDL_TongTien'])) ?></td>
                                <td><?= (float) $d['DDL_TienCoc'] > 0 ? h(dinh_dang_tien($d['DDL_TienCoc'])) : '—' ?></td>
                                <td><?= $d['DDL_MaGiamGia'] ? h($d['DDL_MaGiamGia']) : '—' ?></td>
                                <td><?= h($d['nv_xu_ly'] ?: '—') ?></td>
                                <td>
                                    <span class="badge <?= lop_badge_don($d['DDL_TrangThai']) ?>"><?= h($d['DDL_TrangThai']) ?></span>
                                    <?php $tt_hoan = (string) ($d['DDL_TrangThaiHoanTien'] ?? HOAN_TIEN_KHONG); ?>
                                    <?php if ($tt_hoan === HOAN_TIEN_CHO || $tt_hoan === HOAN_TIEN_DA) : ?>
                                        <br><span class="muted"><?= h($tt_hoan) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="row-actions"><a href="?id=<?= (int) $d['DDL_Ma'] ?>">Chi tiết</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php hien_phan_trang($tong_trang, $trang); ?>
        </section>
<?php admin_cuoi(); ?>
