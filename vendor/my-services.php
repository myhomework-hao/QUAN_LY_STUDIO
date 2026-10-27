<?php
require_once __DIR__ . '/khung_nv.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dich = ve_trang_truoc('my-services.php');
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong($dich);
    }
    $ma = (int) ($_POST['ma'] ?? 0);
    $hien = ($_POST['hanh_dong'] ?? '') === 'hien';
    $dv = lay_mot($conn, "SELECT DV_Ten FROM dichvu WHERE DV_Ma = ?", [$ma]);
    if (!$dv) {
        dat_thong_bao('Không tìm thấy dịch vụ.', 'error');
    } else {
        $conn->prepare("UPDATE dichvu SET DV_DangBan = ? WHERE DV_Ma = ?")->execute([$hien ? 1 : 0, $ma]);
        dat_thong_bao(($hien ? 'Đã hiển thị ' : 'Đã ẩn ') . $dv['DV_Ten'] . '.');
    }
    chuyen_huong($dich);
}

$don_cho = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich WHERE DDL_TrangThai = ?", [TRANG_THAI_CHO_DUYET]);
$don_sap_toi = (int) gia_tri(
    $conn,
    "SELECT COUNT(*) FROM dondatlich WHERE DDL_TrangThai = ?
       AND DDL_Ma IN (SELECT DDL_Ma FROM chitietdon WHERE CTD_KetThuc >= ?)",
    [TRANG_THAI_DA_DUYET, date('Y-m-d H:i:s')]
);
$don_toi_xu_ly = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich WHERE NV_Ma = ? OR NV_MaHoanThanh = ?", [$ma_nhan_vien, $ma_nhan_vien]);
$dv_hien = (int) gia_tri($conn, "SELECT COUNT(*) FROM dichvu WHERE DV_DangBan = 1");
$dv_tong = (int) gia_tri($conn, "SELECT COUNT(*) FROM dichvu");

$tu_khoa = trim((string) ($_GET['q'] ?? ''));
$loai = $_GET['loai'] ?? '';
$hien = $_GET['hien'] ?? '';
$dieu_kien = ['1 = 1'];
$tham_so = [];
if ($tu_khoa !== '') { $dieu_kien[] = "DV_Ten LIKE ?"; $tham_so[] = '%' . $tu_khoa . '%'; }
if (in_array($loai, ['phong_chup', 'thiet_bi', 'trang_phuc', 'phu_kien'], true)) { $dieu_kien[] = "DV_Loai = ?"; $tham_so[] = $loai; } else { $loai = ''; }
if ($hien === '1' || $hien === '0') { $dieu_kien[] = "DV_DangBan = ?"; $tham_so[] = (int) $hien; }
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

nv_dau('Quản lý dịch vụ', 'dich-vu');
?>
        <section class="stats">
            <article class="stat-card"><p class="stat-label">Đơn chờ duyệt</p><p class="stat-number"><?= $don_cho ?></p><p class="stat-note"><a href="vendor-orders.php?tt=<?= urlencode(TRANG_THAI_CHO_DUYET) ?>" class="panel-link">Xử lý ngay</a></p></article>
            <article class="stat-card"><p class="stat-label">Đơn đã duyệt sắp tới</p><p class="stat-number"><?= $don_sap_toi ?></p><p class="stat-note">Chưa kết thúc thời gian thuê</p></article>
            <article class="stat-card"><p class="stat-label">Đơn bạn đã xử lý</p><p class="stat-number"><?= $don_toi_xu_ly ?></p><p class="stat-note">Duyệt, từ chối hoặc hoàn thành</p></article>
            <article class="stat-card"><p class="stat-label">Dịch vụ đang hiển thị</p><p class="stat-number"><?= $dv_hien ?>/<?= $dv_tong ?></p><p class="stat-note">Khách chỉ thấy dịch vụ đang hiển thị</p></article>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2>Danh sách dịch vụ (<?= $tong ?>)</h2>
                <a href="dich-vu-form.php" class="btn-solid">Thêm dịch vụ</a>
            </div>

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
                <button class="btn-solid" type="submit">Lọc</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Ảnh</th><th>Tên dịch vụ</th><th>Loại</th><th>Giá thuê</th><th>Số lượng</th><th>Hiển thị</th><th><span class="sr-only">Thao tác</span></th></tr></thead>
                    <tbody>
                        <?php if (!$ds) : ?><tr><td colspan="7" class="empty">Không tìm thấy dịch vụ nào.</td></tr><?php endif; ?>
                        <?php foreach ($ds as $d) : $dang_ban = (int) $d['DV_DangBan'] === 1; ?>
                            <tr>
                                <td><?php if ($d['DV_HinhAnh']) : ?><img class="thumb" src="<?= h(duong_dan_anh($d['DV_HinhAnh'])) ?>" alt=""><?php endif; ?></td>
                                <td><?= h($d['DV_Ten']) ?></td>
                                <td><?= h(ten_loai_dich_vu($d['DV_Loai'])) ?></td>
                                <td><?= h(gia_hien_thi($d['DV_GiaThue'], $d['DV_DonViTinh'])) ?></td>
                                <td><?= (int) $d['DV_SoLuong'] ?></td>
                                <td><span class="badge <?= $dang_ban ? 'badge-xac-nhan' : 'badge-khoa' ?>"><?= $dang_ban ? 'Đang hiển thị' : 'Đang ẩn' ?></span></td>
                                <td class="row-actions">
                                    <a href="dich-vu-form.php?id=<?= (int) $d['DV_Ma'] ?>">Sửa</a>
                                    <form method="post">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="ma" value="<?= (int) $d['DV_Ma'] ?>">
                                        <input type="hidden" name="ve" value="<?= h($_SERVER['QUERY_STRING'] ?? '') ?>">
                                        <input type="hidden" name="hanh_dong" value="<?= $dang_ban ? 'an' : 'hien' ?>">
                                        <button type="submit"><?= $dang_ban ? 'Ẩn' : 'Hiện' ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php hien_phan_trang($tong_trang, $trang); ?>
        </section>
<?php nv_cuoi(); ?>