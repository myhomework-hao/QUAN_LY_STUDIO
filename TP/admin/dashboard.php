<?php
require_once __DIR__ . '/khung.php';

$hom_nay = date('Y-m-d');
$ngay_mai = date('Y-m-d', strtotime('+1 day'));
$dau_thang = date('Y-m-01');
$dau_thang_sau = date('Y-m-01', strtotime('first day of next month'));
$dau_thang_truoc = date('Y-m-01', strtotime('first day of last month'));

$don_cho_duyet = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich WHERE DDL_TrangThai = ?", [TRANG_THAI_CHO_DUYET]);
$don_hom_nay = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich WHERE DDL_NgayTao >= ? AND DDL_NgayTao < ?", [$hom_nay, $ngay_mai]);

$doanh_thu_thang = doanh_thu_khoang($conn, $dau_thang, $dau_thang_sau);
$doanh_thu_thang_truoc = doanh_thu_khoang($conn, $dau_thang_truoc, $dau_thang);
$ghi_chu_doanh_thu = $doanh_thu_thang_truoc > 0
    ? sprintf('%+d%% so với tháng trước', round(($doanh_thu_thang - $doanh_thu_thang_truoc) / $doanh_thu_thang_truoc * 100))
    : 'Chưa có dữ liệu tháng trước';

$khach_moi = (int) gia_tri(
    $conn,
    "SELECT COUNT(*) FROM nguoidung WHERE LND_Ma = ? AND ND_NgayTao >= ?",
    [VAI_TRO_KHACH_HANG, date('Y-m-d H:i:s', strtotime('-30 days'))]
);
$tong_khach = (int) gia_tri($conn, "SELECT COUNT(*) FROM nguoidung WHERE LND_Ma = ?", [VAI_TRO_KHACH_HANG]);

$nhan_vien_dang_lam = (int) gia_tri(
    $conn,
    "SELECT COUNT(*) FROM nhanvien nv JOIN nguoidung nd ON nd.ND_Ma = nv.ND_Ma
     WHERE nv.NV_TrangThai = 1 AND nd.ND_TrangThai = 1"
);
$tong_nhan_vien = (int) gia_tri($conn, "SELECT COUNT(*) FROM nhanvien");

$thong_ke = [
    ['nhan' => 'Đơn chờ duyệt', 'so' => $don_cho_duyet, 'ghi_chu' => $don_hom_nay . ' đơn mới hôm nay'],
    ['nhan' => 'Doanh thu tháng này', 'so' => dinh_dang_tien($doanh_thu_thang), 'ghi_chu' => $ghi_chu_doanh_thu],
    ['nhan' => 'Khách hàng mới', 'so' => $khach_moi, 'ghi_chu' => 'Trong 30 ngày gần đây, tổng ' . $tong_khach . ' khách'],
    ['nhan' => 'Nhân viên đang làm', 'so' => $nhan_vien_dang_lam, 'ghi_chu' => ($tong_nhan_vien - $nhan_vien_dang_lam) . ' tài khoản đang bị khóa'],
];

$doanh_thu_6_thang = [];
for ($i = 5; $i >= 0; $i--) {
    $dau = date('Y-m-01', strtotime("first day of -$i month"));
    $sau = date('Y-m-01', strtotime("$dau +1 month"));
    $doanh_thu_6_thang[] = [
        'thang' => 'T' . date('n', strtotime($dau)),
        'tien' => round(doanh_thu_khoang($conn, $dau, $sau) / 1000000, 1),
    ];
}
$doanh_thu_max = max(1, max(array_column($doanh_thu_6_thang, 'tien')));

$don_gan_day = lay_nhieu(
    $conn,
    "SELECT d.DDL_Ma, d.DDL_TrangThai, d.DDL_TongTien, d.DDL_NgayTao, nd.ND_Ten,
            (SELECT dv.DV_Ten FROM chitietdon c JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma
              WHERE c.DDL_Ma = d.DDL_Ma ORDER BY c.CTD_Ma LIMIT 1) AS ten_dau,
            (SELECT COUNT(*) FROM chitietdon c WHERE c.DDL_Ma = d.DDL_Ma) AS so_dich_vu
     FROM dondatlich d
     JOIN nguoidung nd ON nd.ND_Ma = d.ND_Ma
     ORDER BY d.DDL_Ma DESC LIMIT 6"
);

$theo_loai = lay_nhieu(
    $conn,
    "SELECT DV_Loai, COUNT(*) AS tong, SUM(CASE WHEN DV_DangBan = 1 THEN 1 ELSE 0 END) AS hien
     FROM dichvu GROUP BY DV_Loai ORDER BY DV_Loai"
);
$luot_dat = [];
foreach (lay_nhieu(
    $conn,
    "SELECT dv.DV_Loai, COUNT(DISTINCT c.DDL_Ma) AS luot
     FROM chitietdon c
     JOIN dondatlich d ON d.DDL_Ma = c.DDL_Ma
     JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma
     WHERE d.DDL_NgayTao >= ? AND d.DDL_TrangThai NOT IN (?, ?)
     GROUP BY dv.DV_Loai",
    [date('Y-m-d', strtotime('-90 days')), TRANG_THAI_DA_HUY, TRANG_THAI_TU_CHOI]
) as $dong) {
    $luot_dat[$dong['DV_Loai']] = (int) $dong['luot'];
}

admin_dau('Quản lý Studio', 'tong-quan');
?>

        <section class="stats" id="tong-quan">
            <?php foreach ($thong_ke as $tk) : ?>
                <article class="stat-card">
                    <p class="stat-label"><?= h($tk['nhan']) ?></p>
                    <p class="stat-number"><?= h($tk['so']) ?></p>
                    <p class="stat-note"><?= h($tk['ghi_chu']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <div class="admin-grid">

            <section class="panel">
                <div class="panel-head">
                    <h2>Doanh thu 6 tháng gần nhất</h2>
                    <span class="panel-unit">Triệu đồng</span>
                </div>

                <div class="chart" role="img" aria-label="Biểu đồ cột doanh thu 6 tháng gần nhất">
                    <?php foreach ($doanh_thu_6_thang as $c) : ?>
                        <div class="chart-col">
                            <span class="chart-value"><?= h($c['tien']) ?></span>
                            <div class="chart-bar" style="height: <?= round($c['tien'] / $doanh_thu_max * 100) ?>%"></div>
                            <span class="chart-month"><?= h($c['thang']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="muted">Tính trên các đơn đã hoàn thành.</p>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Thao tác nhanh</h2>
                </div>

                <div class="quick">
                    <a href="nhan-vien.php?them=1" class="btn-solid">Thêm nhân viên</a>
                    <a href="nguoi-dung.php" class="btn-line">Quản lý người dùng</a>
                    <a href="dich-vu.php" class="btn-line">Xem dịch vụ</a>
                    <a href="thong-ke.php" class="btn-line">Thống kê và báo cáo</a>
                </div>
            </section>

        </div>

        <section class="panel" id="don-thue">
            <div class="panel-head">
                <h2>Đơn thuê gần đây</h2>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Dịch vụ</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th><span class="sr-only">Thao tác</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$don_gan_day) : ?>
                            <tr><td colspan="7" class="empty">Chưa có đơn thuê nào.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($don_gan_day as $don) : ?>
                            <tr>
                                <td><?= h(ma_don_hien_thi((int) $don['DDL_Ma'])) ?></td>
                                <td><?= h($don['ND_Ten']) ?></td>
                                <td>
                                    <?= h($don['ten_dau']) ?>
                                    <?php if ((int) $don['so_dich_vu'] > 1) : ?>
                                        <span class="muted">+<?= (int) $don['so_dich_vu'] - 1 ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= h(dinh_dang_ngay_gio($don['DDL_NgayTao'])) ?></td>
                                <td><?= h(dinh_dang_tien($don['DDL_TongTien'])) ?></td>
                                <td><span class="badge <?= lop_badge_don($don['DDL_TrangThai']) ?>"><?= h($don['DDL_TrangThai']) ?></span></td>
                                <td class="row-actions">
                                    <a href="<?= BASE_URL ?>KB/order-detail.php?id=<?= (int) $don['DDL_Ma'] ?>">Chi tiết</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel" id="dich-vu">
            <div class="panel-head">
                <h2>Dịch vụ theo loại</h2>
                <a href="dich-vu.php" class="panel-link">Xem tất cả dịch vụ</a>
            </div>

            <ul class="service-rows">
                <?php foreach ($theo_loai as $loai) : ?>
                    <li>
                        <span class="service-name"><?= h(ten_loai_dich_vu($loai['DV_Loai'])) ?></span>
                        <span class="service-count"><?= (int) ($luot_dat[$loai['DV_Loai']] ?? 0) ?> đơn trong 90 ngày</span>
                        <span class="badge badge-xac-nhan"><?= (int) $loai['hien'] ?>/<?= (int) $loai['tong'] ?> đang hiển thị</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

<?php admin_cuoi(); ?>