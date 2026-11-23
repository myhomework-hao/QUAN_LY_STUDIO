<?php
require_once __DIR__ . '/khung_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong('thong-ke.php');
    }

    $ma_bao_cao = (int) ($_POST['ma_bao_cao'] ?? 0);
    $ma_dich_vu = (int) ($_POST['ma_dich_vu'] ?? 0);
    $hanh_dong_bao_cao = (string) ($_POST['hanh_dong_bao_cao'] ?? '');
    if ($ma_dich_vu > 0) {
        $ds_bao_cao_can_xu_ly = lay_nhieu(
            $conn,
            "SELECT b.BCHH_Ma, b.DV_Ma, b.BCHH_TrangThai, dv.DV_Loai
             FROM baocao_huhong b
             JOIN dichvu dv ON dv.DV_Ma = b.DV_Ma
             WHERE b.DV_Ma = ? AND dv.DV_Loai = ? AND b.BCHH_TrangThai <> ?",
            [$ma_dich_vu, 'phong_chup', 'Đã xử lý']
        );
    } else {
        $bao_cao_le = lay_mot(
            $conn,
            "SELECT b.BCHH_Ma, b.DV_Ma, b.BCHH_TrangThai, dv.DV_Loai
             FROM baocao_huhong b
             JOIN dichvu dv ON dv.DV_Ma = b.DV_Ma
             WHERE b.BCHH_Ma = ?",
            [$ma_bao_cao]
        );
        $ds_bao_cao_can_xu_ly = $bao_cao_le ? [$bao_cao_le] : [];
    }

    if (!$ds_bao_cao_can_xu_ly || !in_array($hanh_dong_bao_cao, ['bao_tri', 'da_xu_ly'], true)) {
        dat_thong_bao('Báo cáo hư hỏng không hợp lệ hoặc không còn tồn tại.', 'error');
    } else {
        $bao_cao_dau = $ds_bao_cao_can_xu_ly[0];
        $co_bao_cao_cho = false;
        foreach ($ds_bao_cao_can_xu_ly as $bao_cao_item) {
            if ($bao_cao_item['BCHH_TrangThai'] === 'Chờ xử lý') {
                $co_bao_cao_cho = true;
                break;
            }
        }

        if ($hanh_dong_bao_cao === 'bao_tri' && !$co_bao_cao_cho) {
            dat_thong_bao('Các báo cáo này đã được chuyển sang bảo trì.', 'error');
        } else {
            try {
                $conn->beginTransaction();

                if ($hanh_dong_bao_cao === 'bao_tri') {
                    if ($bao_cao_dau['DV_Loai'] === 'phong_chup') {
                        $stmt = $conn->prepare(
                            'SELECT DV_Ma FROM dichvu WHERE DV_Ma = ? AND DV_Loai = ? FOR UPDATE'
                        );
                        $stmt->execute([$bao_cao_dau['DV_Ma'], 'phong_chup']);
                        if (!$stmt->fetchColumn()) {
                            throw new RuntimeException('Không tìm thấy phòng cần cập nhật.');
                        }
                    }

                    if ($ma_dich_vu > 0) {
                        $stmt = $conn->prepare(
                            "UPDATE baocao_huhong
                             SET BCHH_TrangThai = 'Đang bảo trì', BCHH_NgayCapNhat = NOW()
                             WHERE DV_Ma = ? AND BCHH_TrangThai = 'Chờ xử lý'"
                        );
                        $stmt->execute([$ma_dich_vu]);
                    } else {
                        $stmt = $conn->prepare(
                            "UPDATE baocao_huhong
                             SET BCHH_TrangThai = 'Đang bảo trì', BCHH_NgayCapNhat = NOW()
                             WHERE BCHH_Ma = ? AND BCHH_TrangThai = 'Chờ xử lý'"
                        );
                        $stmt->execute([$ma_bao_cao]);
                    }
                    if ($stmt->rowCount() < 1) {
                        throw new RuntimeException('Báo cáo đã được cập nhật bởi người khác.');
                    }

                    if ($bao_cao_dau['DV_Loai'] !== 'phong_chup') {
                        $conn->prepare("UPDATE dichvu SET DV_DangBan = 0 WHERE DV_Ma = ?")
                             ->execute([$bao_cao_dau['DV_Ma']]);
                        $bang_tinh_trang = [
                            'thiet_bi' => ['thietbi', 'TB_TinhTrang'],
                            'trang_phuc' => ['trangphuc', 'TP_TinhTrang'],
                            'phu_kien' => ['phukien', 'PK_TinhTrang'],
                        ];
                        if (!isset($bang_tinh_trang[$bao_cao_dau['DV_Loai']])) {
                            throw new RuntimeException('Loại dịch vụ không hợp lệ.');
                        }
                        [$bang, $cot] = $bang_tinh_trang[$bao_cao_dau['DV_Loai']];
                        $conn->prepare("UPDATE $bang SET $cot = 'Đang bảo trì' WHERE DV_Ma = ?")
                             ->execute([$bao_cao_dau['DV_Ma']]);
                    }

                    $phong_tu_dong_khoa = false;
                    if ($bao_cao_dau['DV_Loai'] === 'phong_chup') {
                        $so_mon_bao_tri = (int) gia_tri(
                            $conn,
                            "SELECT COUNT(DISTINCT BCHH_BoPhan)
                             FROM baocao_huhong
                             WHERE DV_Ma = ? AND BCHH_TrangThai = ?
                               AND BCHH_BoPhan IS NOT NULL AND TRIM(BCHH_BoPhan) <> ''",
                            [$bao_cao_dau['DV_Ma'], 'Đang bảo trì']
                        );
                        if ($so_mon_bao_tri > 2) {
                            $conn->prepare(
                                'UPDATE dichvu SET DV_DangBan = 0 WHERE DV_Ma = ? AND DV_Loai = ?'
                            )->execute([$bao_cao_dau['DV_Ma'], 'phong_chup']);
                            $phong_tu_dong_khoa = true;
                        }
                    }

                    $conn->commit();
                    dat_thong_bao(
                        $bao_cao_dau['DV_Loai'] === 'phong_chup'
                            ? ($phong_tu_dong_khoa
                                ? 'Đã chuyển các báo cáo sang bảo trì. Phòng tự động bị khóa vì có hơn 2 món đang bảo trì; admin có thể mở lại trong Quản lý dịch vụ.'
                                : 'Đã chuyển các báo cáo món hư trong phòng sang trạng thái bảo trì.')
                            : 'Đã khóa dịch vụ và chuyển báo cáo sang trạng thái bảo trì.'
                    );
                } else {
                    if ($ma_dich_vu > 0) {
                        $stmt = $conn->prepare(
                            "UPDATE baocao_huhong
                             SET BCHH_TrangThai = 'Đã xử lý', BCHH_NgayCapNhat = NOW()
                             WHERE DV_Ma = ? AND BCHH_TrangThai <> 'Đã xử lý'"
                        );
                        $stmt->execute([$ma_dich_vu]);
                    } else {
                        $stmt = $conn->prepare(
                            "UPDATE baocao_huhong
                             SET BCHH_TrangThai = 'Đã xử lý', BCHH_NgayCapNhat = NOW()
                             WHERE BCHH_Ma = ? AND BCHH_TrangThai <> 'Đã xử lý'"
                        );
                        $stmt->execute([$ma_bao_cao]);
                    }
                    if ($stmt->rowCount() < 1) {
                        throw new RuntimeException('Báo cáo đã được cập nhật bởi người khác.');
                    }
                    $conn->commit();
                    dat_thong_bao(
                        $ma_dich_vu > 0
                            ? 'Đã đánh dấu các món hư trong phòng là đã khắc phục.'
                            : 'Đã đánh dấu báo cáo hư hỏng là đã xử lý.'
                    );
                }
            } catch (Throwable $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                dat_thong_bao('Không thể cập nhật báo cáo hư hỏng, vui lòng thử lại.', 'error');
            }
        }
    }
    chuyen_huong('thong-ke.php');
}

$ds_bao_cao_hu_hong = lay_nhieu(
    $conn,
    "SELECT b.BCHH_Ma, b.DV_Ma, b.BCHH_BoPhan, b.BCHH_MoTa, b.BCHH_TrangThai, b.BCHH_NgayBao,
            dv.DV_Ten, dv.DV_Loai, nvnd.ND_Ten AS ten_nhan_vien
     FROM baocao_huhong b
     JOIN dichvu dv ON dv.DV_Ma = b.DV_Ma
     JOIN nhanvien nv ON nv.NV_Ma = b.NV_Ma
     JOIN nguoidung nvnd ON nvnd.ND_Ma = nv.ND_Ma
     WHERE b.BCHH_TrangThai <> ?
     ORDER BY b.BCHH_NgayBao DESC, b.BCHH_Ma DESC",
    ['Đã xử lý']
);
$so_bao_cao_hu_hong = count($ds_bao_cao_hu_hong);
$so_dich_vu_hu_hong = count(array_unique(array_column($ds_bao_cao_hu_hong, 'DV_Ma')));
$nhom_bao_cao_hu_hong = [];
foreach ($ds_bao_cao_hu_hong as $bao_cao) {
     $la_phong = $bao_cao['DV_Loai'] === 'phong_chup';
     $khoa_nhom = $la_phong ? 'phong_' . $bao_cao['DV_Ma'] : 'bao_cao_' . $bao_cao['BCHH_Ma'];
     if (!isset($nhom_bao_cao_hu_hong[$khoa_nhom])) {
         $nhom_bao_cao_hu_hong[$khoa_nhom] = [
             'la_nhom_phong' => $la_phong,
             'BCHH_Ma' => (int) $bao_cao['BCHH_Ma'],
             'DV_Ma' => (int) $bao_cao['DV_Ma'],
             'DV_Ten' => $bao_cao['DV_Ten'],
             'DV_Loai' => $bao_cao['DV_Loai'],
             'BCHH_NgayBao' => $bao_cao['BCHH_NgayBao'],
             'so_bao_cao' => 0,
             'ds_bo_phan' => [],
             'ds_mo_ta' => [],
             'ds_nhan_vien' => [],
             'ds_trang_thai' => [],
         ];
     }

     $nhom_bao_cao_hu_hong[$khoa_nhom]['so_bao_cao']++;
     foreach ([
         'ds_bo_phan' => trim((string) $bao_cao['BCHH_BoPhan']),
         'ds_mo_ta' => trim((string) $bao_cao['BCHH_MoTa']),
         'ds_nhan_vien' => trim((string) $bao_cao['ten_nhan_vien']),
         'ds_trang_thai' => trim((string) $bao_cao['BCHH_TrangThai']),
     ] as $ten_ds => $gia_tri) {
         if ($gia_tri !== '') {
             $nhom_bao_cao_hu_hong[$khoa_nhom][$ten_ds][] = $gia_tri;
         }
     }
}
foreach ($nhom_bao_cao_hu_hong as &$nhom) {
     foreach (['ds_bo_phan', 'ds_mo_ta', 'ds_nhan_vien', 'ds_trang_thai'] as $ten_ds) {
         $nhom[$ten_ds] = array_values(array_unique($nhom[$ten_ds]));
     }
}
unset($nhom);
$bao_cao_hu_hong = array_values($nhom_bao_cao_hu_hong);

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

// Link tải báo cáo CSV giữ nguyên bộ lọc hiện tại
$truy_van_xuat = ['tu' => $tu, 'den' => $den, 'kieu' => $kieu];
$lien_ket_xuat_tq = 'thong-ke-xuat.php?' . http_build_query($truy_van_xuat) . '&loai=tong_quan';
$lien_ket_xuat_don = 'thong-ke-xuat.php?' . http_build_query($truy_van_xuat) . '&loai=don';

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
                <?php if (!$loi) : ?>
                    <a class="btn-line" href="<?= h($lien_ket_xuat_tq) ?>">Tải báo cáo Excel (.xls)</a>
                    <a class="btn-line" href="<?= h($lien_ket_xuat_don) ?>">Tải danh sách đơn Excel (.xls)</a>
                <?php endif; ?>
            </form>
            <?php if ($loi) : ?><p class="flash flash-error"><?= h($loi) ?></p><?php endif; ?>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2>Thống kê hư hỏng</h2>
                <span><?= $so_bao_cao_hu_hong ?> báo cáo · <?= $so_dich_vu_hu_hong ?> dịch vụ</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Dịch vụ</th><th>Bộ phận</th><th>Mô tả hư hỏng</th><th>Nhân viên báo cáo</th><th>Ngày báo</th><th>Trạng thái</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$bao_cao_hu_hong) : ?>
                            <tr><td colspan="7" class="empty">Chưa có báo cáo hư hỏng đang mở.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($bao_cao_hu_hong as $bao_cao) : ?>
                            <tr>
                                <td><?= h($bao_cao['DV_Ten']) ?><br><small><?= h(ten_loai_dich_vu($bao_cao['DV_Loai'])) ?></small></td>
                                <td>
                                    <?php if ($bao_cao['ds_bo_phan']) : ?>
                                        <?php foreach ($bao_cao['ds_bo_phan'] as $bo_phan) : ?><div><?= h($bo_phan) ?></div><?php endforeach; ?>
                                    <?php else : ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <?php foreach ($bao_cao['ds_mo_ta'] as $mo_ta) : ?>
                                        <div style="margin-bottom: 4px;"><?= nl2br(h($mo_ta)) ?></div>
                                    <?php endforeach; ?>
                                </td>
                                <td><?= h(implode(', ', $bao_cao['ds_nhan_vien'])) ?></td>
                                <td>
                                    <?= h(date('d/m/Y H:i', strtotime($bao_cao['BCHH_NgayBao']))) ?>
                                    <?php if ($bao_cao['so_bao_cao'] > 1) : ?><br><small><?= (int) $bao_cao['so_bao_cao'] ?> báo cáo</small><?php endif; ?>
                                </td>
                                <td><?= h(implode(', ', $bao_cao['ds_trang_thai'])) ?></td>
                                <td>
                                    <?php if (in_array('Chờ xử lý', $bao_cao['ds_trang_thai'], true)) : ?>
                                        <form method="post" style="margin-bottom:6px;">
                                            <?= csrf_input() ?>
                                            <?php if ($bao_cao['la_nhom_phong']) : ?>
                                                <input type="hidden" name="ma_dich_vu" value="<?= (int) $bao_cao['DV_Ma'] ?>">
                                            <?php else : ?>
                                                <input type="hidden" name="ma_bao_cao" value="<?= (int) $bao_cao['BCHH_Ma'] ?>">
                                            <?php endif; ?>
                                            <input type="hidden" name="hanh_dong_bao_cao" value="bao_tri">
                                            <button type="submit"><?= $bao_cao['la_nhom_phong'] ? 'Chuyển nhóm sang bảo trì' : 'Đưa bảo trì / khóa' ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post">
                                        <?= csrf_input() ?>
                                        <?php if ($bao_cao['la_nhom_phong']) : ?>
                                            <input type="hidden" name="ma_dich_vu" value="<?= (int) $bao_cao['DV_Ma'] ?>">
                                        <?php else : ?>
                                            <input type="hidden" name="ma_bao_cao" value="<?= (int) $bao_cao['BCHH_Ma'] ?>">
                                        <?php endif; ?>
                                        <input type="hidden" name="hanh_dong_bao_cao" value="da_xu_ly">
                                        <button type="submit"><?= $bao_cao['la_nhom_phong'] ? 'Đã khắc phục các món' : 'Đã khắc phục' ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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