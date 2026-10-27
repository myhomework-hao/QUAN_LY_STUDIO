<?php
require_once __DIR__ . '/khung_admin.php';

$ma_xem = (int) ($_GET['id'] ?? 0);

if ($ma_xem > 0) {
    $dv = lay_mot($conn, "SELECT * FROM dichvu WHERE DV_Ma = ?", [$ma_xem]);
    if (!$dv) {
        dat_thong_bao('Không tìm thấy dịch vụ.', 'error');
        chuyen_huong('dich-vu.php');
    }
    $thuoc_tinh = [];
    if ($dv['DV_Loai'] === 'phong_chup') {
        $r = lay_mot($conn, "SELECT PC_SucChua, PC_TrangThai FROM phongchup WHERE DV_Ma = ?", [$ma_xem]);
        if ($r) $thuoc_tinh = ['Sức chứa' => $r['PC_SucChua'] . ' người', 'Tình trạng' => $r['PC_TrangThai']];
    } elseif ($dv['DV_Loai'] === 'thiet_bi') {
        $r = lay_mot($conn, "SELECT TB_Loai, TB_TinhTrang FROM thietbi WHERE DV_Ma = ?", [$ma_xem]);
        if ($r) $thuoc_tinh = ['Loại thiết bị' => $r['TB_Loai'], 'Tình trạng' => $r['TB_TinhTrang']];
    }
    $anh = lay_nhieu($conn, "SELECT HA_DuongDan FROM hinhanh WHERE DV_Ma = ? ORDER BY HA_Ma", [$ma_xem]);
    $danh_gia = lay_nhieu(
        $conn,
        "SELECT g.DG_SoSao, g.DG_NoiDung, g.DG_NgayTao, nd.ND_Ten FROM danhgia g
         JOIN nguoidung nd ON nd.ND_Ma = g.ND_Ma WHERE g.DV_Ma = ? ORDER BY g.DG_Ma DESC LIMIT 20",
        [$ma_xem]
    );
    $tong_dg = lay_mot($conn, "SELECT COUNT(*) AS so, AVG(DG_SoSao) AS tb FROM danhgia WHERE DV_Ma = ?", [$ma_xem]);

    admin_dau('Chi tiết dịch vụ', 'dich-vu');
    ?>
    <section class="panel">
        <div class="panel-head"><h2><?= h($dv['DV_Ten']) ?></h2><a href="dich-vu.php" class="panel-link">← Danh sách</a></div>
        <dl class="detail-grid">
            <dt>Loại</dt><dd><?= h(ten_loai_dich_vu($dv['DV_Loai'])) ?></dd>
            <dt>Giá thuê</dt><dd><?= h(gia_hien_thi($dv['DV_GiaThue'], $dv['DV_DonViTinh'])) ?></dd>
            <dt>Số lượng</dt><dd><?= (int) $dv['DV_SoLuong'] ?></dd>
            <dt>Hiển thị</dt><dd><span class="badge <?= (int) $dv['DV_DangBan'] === 1 ? 'badge-xac-nhan' : 'badge-khoa' ?>"><?= (int) $dv['DV_DangBan'] === 1 ? 'Đang hiển thị' : 'Đang ẩn' ?></span></dd>
            <?php foreach ($thuoc_tinh as $ten => $gt) : ?><dt><?= h($ten) ?></dt><dd><?= h($gt) ?></dd><?php endforeach; ?>
            <dt>Mô tả</dt><dd><?= nl2br(h($dv['DV_MoTa'] ?: 'Chưa có mô tả')) ?></dd>
            <dt>Mô tả chi tiết</dt><dd><?= nl2br(h($dv['DV_MoTaChiTiet'] ?: 'Chưa có')) ?></dd>
            <dt>Đánh giá</dt><dd><?= (int) $tong_dg['so'] > 0 ? h(number_format((float) $tong_dg['tb'], 1)) . '/5 (' . (int) $tong_dg['so'] . ' lượt)' : 'Chưa có đánh giá' ?></dd>
        </dl>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Hình ảnh</h2></div>
        <div class="gallery">
            <?php if ($dv['DV_HinhAnh']) : ?><img src="<?= h(duong_dan_anh($dv['DV_HinhAnh'])) ?>" alt="Ảnh đại diện"><?php endif; ?>
            <?php foreach ($anh as $a) : ?><img src="<?= h(duong_dan_anh($a['HA_DuongDan'])) ?>" alt=""><?php endforeach; ?>
            <?php if (!$dv['DV_HinhAnh'] && !$anh) : ?><p class="muted">Chưa có hình ảnh.</p><?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Đánh giá gần đây</h2></div>
        <ul class="service-rows">
            <?php if (!$danh_gia) : ?><li><span class="muted">Chưa có đánh giá.</span></li><?php endif; ?>
            <?php foreach ($danh_gia as $g) : ?>
                <li>
                    <span class="service-name"><?= h($g['ND_Ten']) ?> · <?= (int) $g['DG_SoSao'] ?>/5</span>
                    <span class="service-count"><?= h($g['DG_NoiDung'] ?: '—') ?></span>
                    <span class="muted"><?= h(date('d/m/Y', strtotime($g['DG_NgayTao']))) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php
    admin_cuoi();
    exit;
}

$tu_khoa = trim((string) ($_GET['q'] ?? ''));
$loai = $_GET['loai'] ?? '';
$hien = $_GET['hien'] ?? '';
$gia_tu = trim((string) ($_GET['gia_tu'] ?? ''));
$gia_den = trim((string) ($_GET['gia_den'] ?? ''));
$loi_gia = '';

if (($gia_tu !== '' && !ctype_digit($gia_tu)) || ($gia_den !== '' && !ctype_digit($gia_den))) {
    $loi_gia = 'Khoảng giá phải là số không âm.';
    $gia_tu = $gia_den = '';
} elseif ($gia_tu !== '' && $gia_den !== '' && (int) $gia_tu > (int) $gia_den) {
    $loi_gia = 'Giá từ không được lớn hơn giá đến.';
    $gia_tu = $gia_den = '';
}

$dieu_kien = ['1 = 1'];
$tham_so = [];
if ($tu_khoa !== '') { $dieu_kien[] = "DV_Ten LIKE ?"; $tham_so[] = '%' . $tu_khoa . '%'; }
if (in_array($loai, ['phong_chup', 'thiet_bi', 'trang_phuc', 'phu_kien'], true)) { $dieu_kien[] = "DV_Loai = ?"; $tham_so[] = $loai; } else { $loai = ''; }
if ($hien === '1' || $hien === '0') { $dieu_kien[] = "DV_DangBan = ?"; $tham_so[] = (int) $hien; }
if ($gia_tu !== '') { $dieu_kien[] = "DV_GiaThue >= ?"; $tham_so[] = (int) $gia_tu; }
if ($gia_den !== '') { $dieu_kien[] = "DV_GiaThue <= ?"; $tham_so[] = (int) $gia_den; }
$where = implode(' AND ', $dieu_kien);

$tong = (int) gia_tri($conn, "SELECT COUNT(*) FROM dichvu WHERE $where", $tham_so);
$tong_trang = 1;
$trang = trang_hien_tai($tong, $tong_trang);
$lech = ($trang - 1) * SO_DONG_MOI_TRANG;
$gioi_han = SO_DONG_MOI_TRANG;
$ds = lay_nhieu(
    $conn,
    "SELECT DV_Ma, DV_Ten, DV_Loai, DV_GiaThue, DV_DonViTinh, DV_SoLuong, DV_DangBan, DV_HinhAnh
     FROM dichvu WHERE $where ORDER BY DV_Ma DESC LIMIT $gioi_han OFFSET $lech",
    $tham_so
);

admin_dau('Dịch vụ', 'dich-vu');
?>
        <section class="panel">
            <div class="panel-head"><h2>Danh sách dịch vụ (<?= $tong ?>)</h2></div>
            <?php if ($loi_gia) : ?><p class="flash flash-error"><?= h($loi_gia) ?></p><?php endif; ?>

            <form class="filter" method="get">
                <div class="field wide"><label for="q">Tên dịch vụ</label><input id="q" name="q" value="<?= h($tu_khoa) ?>"></div>
                <div class="field">
                    <label for="loai">Loại</label>
                    <select id="loai" name="loai">
                        <option value="">Tất cả</option>
                        <?php foreach (['phong_chup', 'thiet_bi', 'trang_phuc', 'phu_kien'] as $m) : ?>
                            <option value="<?= $m ?>" <?= $loai === $m ? 'selected' : '' ?>><?= h(ten_loai_dich_vu($m)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="hien">Hiển thị</label>
                    <select id="hien" name="hien">
                        <option value="">Tất cả</option>
                        <option value="1" <?= $hien === '1' ? 'selected' : '' ?>>Đang hiển thị</option>
                        <option value="0" <?= $hien === '0' ? 'selected' : '' ?>>Đang ẩn</option>
                    </select>
                </div>
                <div class="field"><label for="gia_tu">Giá từ</label><input id="gia_tu" name="gia_tu" inputmode="numeric" value="<?= h($gia_tu) ?>"></div>
                <div class="field"><label for="gia_den">Giá đến</label><input id="gia_den" name="gia_den" inputmode="numeric" value="<?= h($gia_den) ?>"></div>
                <button class="btn-solid" type="submit">Lọc</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Ảnh</th><th>Tên dịch vụ</th><th>Loại</th><th>Giá thuê</th><th>Số lượng</th><th>Hiển thị</th><th><span class="sr-only">Thao tác</span></th></tr></thead>
                    <tbody>
                        <?php if (!$ds) : ?><tr><td colspan="7" class="empty">Không tìm thấy dịch vụ nào.</td></tr><?php endif; ?>
                        <?php foreach ($ds as $d) : ?>
                            <tr>
                                <td><?php if ($d['DV_HinhAnh']) : ?><img class="thumb" src="<?= h(duong_dan_anh($d['DV_HinhAnh'])) ?>" alt=""><?php endif; ?></td>
                                <td><?= h($d['DV_Ten']) ?></td>
                                <td><?= h(ten_loai_dich_vu($d['DV_Loai'])) ?></td>
                                <td><?= h(gia_hien_thi($d['DV_GiaThue'], $d['DV_DonViTinh'])) ?></td>
                                <td><?= (int) $d['DV_SoLuong'] ?></td>
                                <td><span class="badge <?= (int) $d['DV_DangBan'] === 1 ? 'badge-xac-nhan' : 'badge-khoa' ?>"><?= (int) $d['DV_DangBan'] === 1 ? 'Đang hiển thị' : 'Đang ẩn' ?></span></td>
                                <td class="row-actions"><a href="?id=<?= (int) $d['DV_Ma'] ?>">Chi tiết</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php hien_phan_trang($tong_trang, $trang); ?>
        </section>
<?php admin_cuoi(); ?>