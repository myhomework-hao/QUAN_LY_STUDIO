<?php
require_once __DIR__ . '/khung_admin.php';

$tu = (string) ($_GET['tu'] ?? date('Y-m-01'));
$den = (string) ($_GET['den'] ?? date('Y-m-d'));
$kieu = ($_GET['kieu'] ?? 'ngay') === 'thang' ? 'thang' : 'ngay';
$loi = '';

$kiem_tra = function (string $s): bool {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s;
};
if (!$kiem_tra($tu) || !$kiem_tra($den)) {
    $loi = 'Ngày không hợp lệ.';
} elseif ($tu > $den) {
    $loi = 'Ngày bắt đầu không được lớn hơn ngày kết thúc.';
} elseif ((strtotime($den) - strtotime($tu)) / 86400 > 730) {
    $loi = 'Khoảng thời gian tối đa là 2 năm.';
}

$doanh_thu = [];
$tong_tien = 0.0;
$so_don_hoan_thanh = 0;
$theo_trang_thai = [];
$top_dich_vu = [];

if (!$loi) {
    $tu_gio = $tu . ' 00:00:00';
    $den_gio = date('Y-m-d', strtotime($den . ' +1 day')) . ' 00:00:00';

    $don_xong = lay_nhieu(
        $conn,
        "SELECT DDL_TongTien, COALESCE(DDL_ThoiDiemHoanThanh, DDL_NgayTao) AS thoi_diem FROM dondatlich
         WHERE DDL_TrangThai = ? AND COALESCE(DDL_ThoiDiemHoanThanh, DDL_NgayTao) >= ? AND COALESCE(DDL_ThoiDiemHoanThanh, DDL_NgayTao) < ?",
        [TRANG_THAI_HOAN_THANH, $tu_gio, $den_gio]
    );
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
}
$tong_don = array_sum(array_column($theo_trang_thai, 'so'));
$cao_nhat = $doanh_thu ? max($doanh_thu) : 1;
$nhieu_cot = count($doanh_thu) > 14;

admin_dau('Thống kê', 'thong-ke');
?>
        <section class="panel">
            <form class="filter" method="get">
                <div class="field"><label for="tu">Từ ngày</label><input id="tu" name="tu" type="date" value="<?= h($tu) ?>"></div>
                <div class="field"><label for="den">Đến ngày</label><input id="den" name="den" type="date" value="<?= h($den) ?>"></div>
                <div class="field">
                    <label for="kieu">Nhóm theo</label>
                    <select id="kieu" name="kieu">
                        <option value="ngay" <?= $kieu === 'ngay' ? 'selected' : '' ?>>Ngày</option>
                        <option value="thang" <?= $kieu === 'thang' ? 'selected' : '' ?>>Tháng</option>
                    </select>
                </div>
                <button class="btn-solid" type="submit">Xem thống kê</button>
            </form>
            <?php if ($loi) : ?><p class="flash flash-error"><?= h($loi) ?></p><?php endif; ?>
        </section>

<?php if (!$loi) : ?>
        <?php if ($tong_don === 0 && !$doanh_thu) : ?>
            <section class="panel"><p class="empty">Không có dữ liệu thống kê trong khoảng thời gian này.</p></section>
        <?php else : ?>
        <div class="summary">
            <article class="stat-card"><p class="stat-label">Doanh thu (đơn hoàn thành)</p><p class="stat-number"><?= h(dinh_dang_tien($tong_tien)) ?></p><p class="stat-note"><?= $so_don_hoan_thanh ?> đơn hoàn thành</p></article>
            <article class="stat-card"><p class="stat-label">Tổng số đơn đặt</p><p class="stat-number"><?= (int) $tong_don ?></p><p class="stat-note">Mọi trạng thái</p></article>
            <article class="stat-card"><p class="stat-label">Giá trị trung bình / đơn</p><p class="stat-number"><?= h(dinh_dang_tien($so_don_hoan_thanh ? $tong_tien / $so_don_hoan_thanh : 0)) ?></p><p class="stat-note">Trên đơn hoàn thành</p></article>
        </div>

        <section class="panel">
            <div class="panel-head"><h2>Doanh thu theo <?= $kieu === 'ngay' ? 'ngày' : 'tháng' ?></h2><span class="panel-unit">Triệu đồng</span></div>
            <?php if (!$doanh_thu) : ?>
                <p class="empty">Chưa có đơn hoàn thành.</p>
            <?php else : ?>
            <div class="table-wrap">
            <div class="chart<?= $nhieu_cot ? ' dense' : '' ?>" style="min-width: <?= max(300, count($doanh_thu) * 28) ?>px" role="img" aria-label="Biểu đồ doanh thu">
                <?php foreach ($doanh_thu as $khoa => $tien) : ?>
                    <div class="chart-col">
                        <span class="chart-value"><?= h(round($tien / 1000000, 1)) ?></span>
                        <div class="chart-bar" style="height: <?= max(2, round($tien / $cao_nhat * 100)) ?>%"></div>
                        <span class="chart-month"><?= h($kieu === 'ngay' ? date('d/m', strtotime($khoa)) : date('m/Y', strtotime($khoa . '-01'))) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            </div>
            <?php endif; ?>
        </section>

        <div class="admin-grid">
            <section class="panel">
                <div class="panel-head"><h2>Dịch vụ được đặt nhiều</h2></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Dịch vụ</th><th>Loại</th><th>Số đơn</th><th>Tiền thuê</th></tr></thead>
                        <tbody>
                            <?php if (!$top_dich_vu) : ?><tr><td colspan="4" class="empty">Chưa có dữ liệu.</td></tr><?php endif; ?>
                            <?php foreach ($top_dich_vu as $t) : ?>
                                <tr>
                                    <td><a href="dich-vu.php?id=<?= (int) $t['DV_Ma'] ?>"><?= h($t['DV_Ten']) ?></a></td>
                                    <td><?= h(ten_loai_dich_vu($t['DV_Loai'])) ?></td>
                                    <td><?= (int) $t['luot'] ?></td>
                                    <td><?= h(dinh_dang_tien($t['tien'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Đơn theo trạng thái</h2></div>
                <ul class="service-rows">
                    <?php foreach ($theo_trang_thai as $t) : ?>
                        <li style="grid-template-columns: 1fr auto">
                            <span class="badge <?= lop_badge_don($t['DDL_TrangThai']) ?>"><?= h($t['DDL_TrangThai']) ?></span>
                            <span class="service-name"><?= (int) $t['so'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>
        <?php endif; ?>
<?php endif; ?>
<?php admin_cuoi(); ?>