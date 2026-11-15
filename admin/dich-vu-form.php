<?php
require_once __DIR__ . '/khung_admin.php';

const ADMIN_LOAI_DICH_VU = ['phong_chup', 'thiet_bi', 'trang_phuc', 'phu_kien'];
const ADMIN_DON_VI_DICH_VU = ['giờ', 'ngày', 'lượt', 'bộ'];

$ma_sua = (int) ($_GET['id'] ?? 0);
$dv_cu = $ma_sua > 0 ? lay_mot($conn, 'SELECT * FROM dichvu WHERE DV_Ma = ?', [$ma_sua]) : null;
if ($ma_sua > 0 && !$dv_cu) {
    dat_thong_bao('Không tìm thấy dịch vụ.', 'error');
    chuyen_huong('dich-vu.php');
}

$ten_bang_con = [
    'phong_chup' => ['phongchup', 'PC_SucChua', 'Sức chứa'],
    'thiet_bi' => ['thietbi', 'TB_Loai', 'Loại thiết bị'],
    'trang_phuc' => ['trangphuc', 'TP_KichCo', 'Kích cỡ (S, M, L)'],
    'phu_kien' => ['phukien', 'PK_Loai', 'Loại phụ kiện'],
];
$loai = (string) ($dv_cu['DV_Loai'] ?? 'phong_chup');
$bang_con = $ten_bang_con[$loai] ?? $ten_bang_con['phong_chup'];
$gia_tri_thuoc_tinh = $dv_cu ? (string) gia_tri($conn, "SELECT {$bang_con[1]} FROM {$bang_con[0]} WHERE DV_Ma = ?", [$ma_sua]) : '';
$du_lieu = [
    'ten' => (string) ($dv_cu['DV_Ten'] ?? ''),
    'loai' => $loai,
    'mo_ta' => (string) ($dv_cu['DV_MoTa'] ?? ''),
    'mo_ta_ct' => (string) ($dv_cu['DV_MoTaChiTiet'] ?? ''),
    'gia' => isset($dv_cu['DV_GiaThue']) ? (string) (int) $dv_cu['DV_GiaThue'] : '',
    'don_vi' => (string) ($dv_cu['DV_DonViTinh'] ?? 'giờ'),
    'so_luong' => (string) ($dv_cu['DV_SoLuong'] ?? '1'),
    'anh' => (string) ($dv_cu['DV_HinhAnh'] ?? ''),
    'hien' => (int) ($dv_cu['DV_DangBan'] ?? 1),
    'thuoc_tinh' => $gia_tri_thuoc_tinh,
    'size_so_luong' => ['S' => 0, 'M' => 0, 'L' => 0],
];

if ($loai === 'trang_phuc' && $ma_sua > 0) {
    $sizes = lay_nhieu($conn, 'SELECT DVK_Ten, DVK_SoLuong FROM dichvu_kichco WHERE DV_Ma = ?', [$ma_sua]);
    foreach ($sizes as $size) {
        if (isset($du_lieu['size_so_luong'][$size['DVK_Ten']])) {
            $du_lieu['size_so_luong'][$size['DVK_Ten']] = (int) $size['DVK_SoLuong'];
        }
    }
}

$loi = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong('dich-vu.php');
    }

    $loai = $dv_cu ? $dv_cu['DV_Loai'] : (string) ($_POST['loai'] ?? '');
    $bang_con = $ten_bang_con[$loai] ?? $ten_bang_con['phong_chup'];
    $du_lieu = [
        'ten' => trim((string) ($_POST['ten'] ?? '')),
        'loai' => $loai,
        'mo_ta' => trim((string) ($_POST['mo_ta'] ?? '')),
        'mo_ta_ct' => trim((string) ($_POST['mo_ta_ct'] ?? '')),
        'gia' => trim((string) ($_POST['gia'] ?? '')),
        'don_vi' => (string) ($_POST['don_vi'] ?? ''),
        'so_luong' => trim((string) ($_POST['so_luong'] ?? '')),
        'anh' => trim((string) ($_POST['anh'] ?? '')),
        'hien' => isset($_POST['hien']) ? 1 : 0,
        'thuoc_tinh' => trim((string) ($_POST['thuoc_tinh'] ?? '')),
        'size_so_luong' => [],
    ];
    foreach (['S', 'M', 'L'] as $size) {
        $du_lieu['size_so_luong'][$size] = trim((string) ($_POST['size_so_luong'][$size] ?? '0'));
    }

    if (!in_array($loai, ADMIN_LOAI_DICH_VU, true)) $loi['loai'] = 'Vui lòng chọn loại dịch vụ hợp lệ.';
    if ($du_lieu['ten'] === '' || mb_strlen($du_lieu['ten']) > 150) $loi['ten'] = 'Tên dịch vụ bắt buộc và tối đa 150 ký tự.';
    if (!ctype_digit($du_lieu['gia']) || (int) $du_lieu['gia'] < 1) $loi['gia'] = 'Giá thuê phải là số nguyên dương.';
    if (!in_array($du_lieu['don_vi'], ADMIN_DON_VI_DICH_VU, true)) $loi['don_vi'] = 'Vui lòng chọn đơn vị tính hợp lệ.';
    if (!ctype_digit($du_lieu['so_luong']) || (int) $du_lieu['so_luong'] < 1) $loi['so_luong'] = 'Số lượng phải từ 1 trở lên.';
    if (mb_strlen($du_lieu['anh']) > 255) $loi['anh'] = 'Tên ảnh tối đa 255 ký tự.';
    if ($loai === 'phong_chup' && (!ctype_digit($du_lieu['thuoc_tinh']) || (int) $du_lieu['thuoc_tinh'] < 1)) $loi['thuoc_tinh'] = 'Sức chứa phải là số nguyên dương.';
    if (mb_strlen($du_lieu['thuoc_tinh']) > 100) $loi['thuoc_tinh'] = 'Thuộc tính tối đa 100 ký tự.';
    if ($loai === 'trang_phuc') {
        $size_hop_le = ['S', 'M', 'L'];
        $ds_size = array_values(array_unique(array_filter(array_map('trim', explode(',', strtoupper($du_lieu['thuoc_tinh']))))));
        if (!$ds_size || array_diff($ds_size, $size_hop_le)) $loi['thuoc_tinh'] = 'Kích cỡ chỉ gồm S, M, L, ví dụ: S, M, L.';
        foreach ($du_lieu['size_so_luong'] as $size => $so_luong_size) {
            if (!ctype_digit($so_luong_size) || ((int) $so_luong_size > 0 && !in_array($size, $ds_size, true))) {
                $loi['size_so_luong'] = 'Số lượng size phải là số không âm và chỉ nhập cho size đã chọn.';
            }
        }
    }

    if (!$loi) {
        $conn->beginTransaction();
        try {
            if ($dv_cu) {
                $conn->prepare('UPDATE dichvu SET DV_Ten = ?, DV_MoTa = ?, DV_MoTaChiTiet = ?, DV_GiaThue = ?, DV_DonViTinh = ?, DV_HinhAnh = ?, DV_SoLuong = ?, DV_DangBan = ? WHERE DV_Ma = ?')
                    ->execute([$du_lieu['ten'], $du_lieu['mo_ta'] ?: null, $du_lieu['mo_ta_ct'] ?: null, (int) $du_lieu['gia'], $du_lieu['don_vi'], $du_lieu['anh'] ?: null, (int) $du_lieu['so_luong'], $du_lieu['hien'], $ma_sua]);
                $ma_dv = $ma_sua;
            } else {
                $conn->prepare('INSERT INTO dichvu (DV_Ten, DV_Loai, DV_MoTa, DV_MoTaChiTiet, DV_GiaThue, DV_DonViTinh, DV_HinhAnh, DV_SoLuong, DV_DangBan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                    ->execute([$du_lieu['ten'], $loai, $du_lieu['mo_ta'] ?: null, $du_lieu['mo_ta_ct'] ?: null, (int) $du_lieu['gia'], $du_lieu['don_vi'], $du_lieu['anh'] ?: null, (int) $du_lieu['so_luong'], $du_lieu['hien']]);
                $ma_dv = (int) $conn->lastInsertId();
            }

            $sql_thuoc_tinh = "SELECT COUNT(*) FROM {$bang_con[0]} WHERE DV_Ma = ?";
            if ((int) gia_tri($conn, $sql_thuoc_tinh, [$ma_dv]) > 0) {
                $conn->prepare("UPDATE {$bang_con[0]} SET {$bang_con[1]} = ? WHERE DV_Ma = ?")->execute([$du_lieu['thuoc_tinh'] ?: null, $ma_dv]);
            } else {
                $conn->prepare("INSERT INTO {$bang_con[0]} (DV_Ma, {$bang_con[1]}) VALUES (?, ?)")->execute([$ma_dv, $du_lieu['thuoc_tinh'] ?: null]);
            }

            if ($loai === 'trang_phuc') {
                $ds_size = array_values(array_unique(array_map('trim', explode(',', strtoupper($du_lieu['thuoc_tinh'])))));
                foreach (['S', 'M', 'L'] as $size) {
                    $so_luong_size = (int) $du_lieu['size_so_luong'][$size];
                    $hoat_dong = in_array($size, $ds_size, true) ? 1 : 0;
                    $ma_size = gia_tri($conn, 'SELECT DVK_Ma FROM dichvu_kichco WHERE DV_Ma = ? AND DVK_Ten = ?', [$ma_dv, $size]);
                    if ($ma_size !== false) {
                        $conn->prepare('UPDATE dichvu_kichco SET DVK_SoLuong = ?, DVK_DangHoatDong = ? WHERE DV_Ma = ? AND DVK_Ten = ?')->execute([$so_luong_size, $hoat_dong, $ma_dv, $size]);
                    } elseif ($hoat_dong) {
                        $conn->prepare('INSERT INTO dichvu_kichco (DV_Ma, DVK_Ten, DVK_SoLuong, DVK_DangHoatDong) VALUES (?, ?, ?, 1)')->execute([$ma_dv, $size, $so_luong_size]);
                    }
                }
            }

            $conn->commit();
            dat_thong_bao($dv_cu ? 'Đã cập nhật dịch vụ.' : 'Đã thêm dịch vụ mới.');
            chuyen_huong('dich-vu.php');
        } catch (Throwable $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            $loi['chung'] = 'Không thể lưu dịch vụ, vui lòng kiểm tra dữ liệu và thử lại.';
        }
    }
}

admin_dau($dv_cu ? 'Sửa dịch vụ' : 'Thêm dịch vụ', 'dich-vu');
?>
<section class="panel">
    <div class="panel-head"><h2><?= $dv_cu ? 'Cập nhật ' . h($dv_cu['DV_Ten']) : 'Thông tin dịch vụ mới' ?></h2><a href="dich-vu.php" class="panel-link">← Danh sách</a></div>
    <?php if (isset($loi['chung'])) : ?><p class="flash flash-error"><?= h($loi['chung']) ?></p><?php endif; ?>
    <form method="post">
        <?= csrf_input() ?>
        <div class="form-grid">
            <div class="field"><label for="ten">Tên dịch vụ</label><input id="ten" name="ten" maxlength="150" value="<?= h($du_lieu['ten']) ?>" required><?php if (isset($loi['ten'])) : ?><span class="field-error"><?= h($loi['ten']) ?></span><?php endif; ?></div>
            <div class="field"><label for="loai">Loại dịch vụ</label><select id="loai" name="loai" <?= $dv_cu ? 'disabled' : '' ?>><?php foreach (ADMIN_LOAI_DICH_VU as $m) : ?><option value="<?= h($m) ?>" <?= $du_lieu['loai'] === $m ? 'selected' : '' ?>><?= h(ten_loai_dich_vu($m)) ?></option><?php endforeach; ?></select><?php if (isset($loi['loai'])) : ?><span class="field-error"><?= h($loi['loai']) ?></span><?php endif; ?></div>
            <div class="field"><label for="gia">Giá thuê (VND)</label><input id="gia" name="gia" inputmode="numeric" value="<?= h($du_lieu['gia']) ?>" required><?php if (isset($loi['gia'])) : ?><span class="field-error"><?= h($loi['gia']) ?></span><?php endif; ?></div>
            <div class="field"><label for="don_vi">Đơn vị tính</label><select id="don_vi" name="don_vi"><?php foreach (ADMIN_DON_VI_DICH_VU as $m) : ?><option value="<?= h($m) ?>" <?= $du_lieu['don_vi'] === $m ? 'selected' : '' ?>><?= h($m) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="so_luong">Số lượng cho thuê cùng lúc</label><input id="so_luong" name="so_luong" inputmode="numeric" value="<?= h($du_lieu['so_luong']) ?>" required><?php if (isset($loi['so_luong'])) : ?><span class="field-error"><?= h($loi['so_luong']) ?></span><?php endif; ?></div>
            <div class="field"><label for="thuoc_tinh"><?= h($bang_con[2]) ?></label><input id="thuoc_tinh" name="thuoc_tinh" value="<?= h($du_lieu['thuoc_tinh']) ?>"><?php if (isset($loi['thuoc_tinh'])) : ?><span class="field-error"><?= h($loi['thuoc_tinh']) ?></span><?php endif; ?></div>
            <?php if ($du_lieu['loai'] === 'trang_phuc') : ?>
                <?php foreach (['S', 'M', 'L'] as $size) : ?><div class="field"><label for="size_<?= $size ?>">Số lượng size <?= $size ?></label><input id="size_<?= $size ?>" name="size_so_luong[<?= $size ?>]" inputmode="numeric" value="<?= h((string) $du_lieu['size_so_luong'][$size]) ?>"></div><?php endforeach; ?>
            <?php endif; ?>
            <div class="field"><label for="anh">Tên ảnh trong assets/uploads (để trống nếu chưa có)</label><input id="anh" name="anh" maxlength="255" value="<?= h($du_lieu['anh']) ?>"><?php if (isset($loi['anh'])) : ?><span class="field-error"><?= h($loi['anh']) ?></span><?php endif; ?></div>
            <div class="field"><label for="mo_ta">Mô tả ngắn</label><textarea id="mo_ta" name="mo_ta" rows="3"><?= h($du_lieu['mo_ta']) ?></textarea></div>
            <div class="field"><label for="mo_ta_ct">Mô tả chi tiết</label><textarea id="mo_ta_ct" name="mo_ta_ct" rows="3"><?= h($du_lieu['mo_ta_ct']) ?></textarea></div>
            <div class="field"><label><input type="checkbox" name="hien" value="1" <?= $du_lieu['hien'] ? 'checked' : '' ?>> Hiển thị cho khách thuê</label></div>
        </div>
        <div class="form-actions"><button class="btn-solid" type="submit">Lưu dịch vụ</button><a class="btn-line" href="dich-vu.php">Hủy</a></div>
    </form>
</section>
<?php admin_cuoi(); ?>
