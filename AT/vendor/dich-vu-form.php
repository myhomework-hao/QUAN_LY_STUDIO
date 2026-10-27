<?php
require_once __DIR__ . '/khung.php';

const LOAI_HOP_LE = ['phong_chup', 'thiet_bi', 'trang_phuc', 'phu_kien'];
const DON_VI_HOP_LE = ['gio', 'ngay', 'bo'];

$ma_sua = (int) ($_GET['id'] ?? $_POST['ma_sua'] ?? 0);
$dv_cu = null;
if ($ma_sua > 0) {
    $dv_cu = lay_mot($conn, "SELECT * FROM dichvu WHERE DV_Ma = ?", [$ma_sua]);
    if (!$dv_cu) {
        dat_thong_bao('Không tìm thấy dịch vụ.', 'error');
        chuyen_huong('my-services.php');
    }
}

function lay_thuoc_tinh(PDO $conn, string $loai, int $ma): string
{
    $cot = ['phong_chup' => ['phongchup', 'PC_SucChua'], 'thiet_bi' => ['thietbi', 'TB_Loai'], 'trang_phuc' => ['trangphuc', 'TP_KichCo'], 'phu_kien' => ['phukien', 'PK_Loai']][$loai];
    return (string) gia_tri($conn, "SELECT {$cot[1]} FROM {$cot[0]} WHERE DV_Ma = ?", [$ma]);
}

function luu_thuoc_tinh(PDO $conn, string $loai, int $ma, string $gia_tri_moi): void
{
    $bang = ['phong_chup' => ['phongchup', 'PC_SucChua'], 'thiet_bi' => ['thietbi', 'TB_Loai'], 'trang_phuc' => ['trangphuc', 'TP_KichCo'], 'phu_kien' => ['phukien', 'PK_Loai']][$loai];
    $gia_tri_luu = $loai === 'phong_chup' ? (int) $gia_tri_moi : ($gia_tri_moi !== '' ? $gia_tri_moi : null);
    if ((int) gia_tri($conn, "SELECT COUNT(*) FROM {$bang[0]} WHERE DV_Ma = ?", [$ma]) > 0) {
        $conn->prepare("UPDATE {$bang[0]} SET {$bang[1]} = ? WHERE DV_Ma = ?")->execute([$gia_tri_luu, $ma]);
    } else {
        $conn->prepare("INSERT INTO {$bang[0]} (DV_Ma, {$bang[1]}) VALUES (?, ?)")->execute([$ma, $gia_tri_luu]);
    }
}

function luu_anh(array $tep, ?string &$loi): ?string
{
    if (!isset($tep['error']) || $tep['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($tep['error'] !== UPLOAD_ERR_OK) {
        $loi = 'Tải ảnh lên không thành công.';
        return null;
    }
    if ($tep['size'] > 3 * 1024 * 1024) {
        $loi = 'Ảnh không được lớn hơn 3MB.';
        return null;
    }
    $dang_cho_phep = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tep['tmp_name']);
    if (!isset($dang_cho_phep[$mime])) {
        $loi = 'Ảnh phải có định dạng JPG, PNG hoặc WEBP.';
        return null;
    }
    if (!is_dir(THU_MUC_ANH)) {
        mkdir(THU_MUC_ANH, 0755, true);
    }
    $ten_tep = 'dv_' . date('ymdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $dang_cho_phep[$mime];
    if (!move_uploaded_file($tep['tmp_name'], THU_MUC_ANH . $ten_tep)) {
        $loi = 'Không thể lưu ảnh lên máy chủ.';
        return null;
    }
    return $ten_tep;
}

$loi = [];
$du_lieu = [
    'ten' => $dv_cu['DV_Ten'] ?? '', 'loai' => $dv_cu['DV_Loai'] ?? 'phong_chup',
    'mo_ta' => $dv_cu['DV_MoTa'] ?? '', 'mo_ta_ct' => $dv_cu['DV_MoTaChiTiet'] ?? '',
    'gia' => isset($dv_cu['DV_GiaThue']) ? (string) (int) $dv_cu['DV_GiaThue'] : '',
    'don_vi' => $dv_cu['DV_DonViTinh'] ?? 'gio', 'so_luong' => (string) ($dv_cu['DV_SoLuong'] ?? 1),
    'hien' => (int) ($dv_cu['DV_DangBan'] ?? 1), 'thuoc_tinh' => $dv_cu ? lay_thuoc_tinh($conn, $dv_cu['DV_Loai'], $ma_sua) : '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong('my-services.php');
    }
    $du_lieu = [
        'ten' => trim((string) ($_POST['ten'] ?? '')),
        'loai' => $dv_cu ? $dv_cu['DV_Loai'] : (string) ($_POST['loai'] ?? ''),
        'mo_ta' => trim((string) ($_POST['mo_ta'] ?? '')),
        'mo_ta_ct' => trim((string) ($_POST['mo_ta_ct'] ?? '')),
        'gia' => trim((string) ($_POST['gia'] ?? '')),
        'don_vi' => (string) ($_POST['don_vi'] ?? ''),
        'so_luong' => trim((string) ($_POST['so_luong'] ?? '')),
        'hien' => isset($_POST['hien']) ? 1 : 0,
        'thuoc_tinh' => trim((string) ($_POST['thuoc_tinh'] ?? '')),
    ];

    if ($du_lieu['ten'] === '') $loi['ten'] = 'Vui lòng nhập tên dịch vụ.';
    elseif (mb_strlen($du_lieu['ten']) > 150) $loi['ten'] = 'Tên dịch vụ tối đa 150 ký tự.';
    if (!in_array($du_lieu['loai'], LOAI_HOP_LE, true)) $loi['loai'] = 'Vui lòng chọn loại dịch vụ.';
    if (!ctype_digit($du_lieu['gia']) || (int) $du_lieu['gia'] <= 0) $loi['gia'] = 'Giá thuê phải là số nguyên dương.';
    if (!in_array($du_lieu['don_vi'], DON_VI_HOP_LE, true)) $loi['don_vi'] = 'Vui lòng chọn đơn vị tính.';
    if (!ctype_digit($du_lieu['so_luong']) || (int) $du_lieu['so_luong'] < 1) $loi['so_luong'] = 'Số lượng phải từ 1 trở lên.';
    if ($du_lieu['loai'] === 'phong_chup' && (!ctype_digit($du_lieu['thuoc_tinh']) || (int) $du_lieu['thuoc_tinh'] < 1)) {
        $loi['thuoc_tinh'] = 'Sức chứa phải là số nguyên dương.';
    }
    if (mb_strlen($du_lieu['thuoc_tinh']) > 100) $loi['thuoc_tinh'] = 'Tối đa 100 ký tự.';

    $loi_anh = null;
    $anh_moi = null;
    if (!$loi) {
        $anh_moi = luu_anh($_FILES['anh'] ?? [], $loi_anh);
        if ($loi_anh) $loi['anh'] = $loi_anh;
    }

    if (!$loi) {
        $conn->beginTransaction();
        try {
            if ($dv_cu) {
                $conn->prepare(
                    "UPDATE dichvu SET DV_Ten = ?, DV_MoTa = ?, DV_MoTaChiTiet = ?, DV_GiaThue = ?, DV_DonViTinh = ?, DV_SoLuong = ?, DV_DangBan = ?, DV_HinhAnh = ? WHERE DV_Ma = ?"
                )->execute([
                    $du_lieu['ten'], $du_lieu['mo_ta'] ?: null, $du_lieu['mo_ta_ct'] ?: null, (int) $du_lieu['gia'], $du_lieu['don_vi'],
                    (int) $du_lieu['so_luong'], $du_lieu['hien'], $anh_moi ?? $dv_cu['DV_HinhAnh'], $ma_sua,
                ]);
                $ma_dv = $ma_sua;
            } else {
                $conn->prepare(
                    "INSERT INTO dichvu (DV_Ten, DV_Loai, DV_MoTa, DV_MoTaChiTiet, DV_GiaThue, DV_DonViTinh, DV_SoLuong, DV_DangBan, DV_HinhAnh)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                )->execute([
                    $du_lieu['ten'], $du_lieu['loai'], $du_lieu['mo_ta'] ?: null, $du_lieu['mo_ta_ct'] ?: null, (int) $du_lieu['gia'],
                    $du_lieu['don_vi'], (int) $du_lieu['so_luong'], $du_lieu['hien'], $anh_moi,
                ]);
                $ma_dv = (int) $conn->lastInsertId();
            }
            luu_thuoc_tinh($conn, $du_lieu['loai'], $ma_dv, $du_lieu['thuoc_tinh']);
            $conn->commit();
            dat_thong_bao($dv_cu ? 'Đã cập nhật dịch vụ.' : 'Đã thêm dịch vụ mới.');
            chuyen_huong('my-services.php');
        } catch (PDOException $e) {
            $conn->rollBack();
            if ($anh_moi) @unlink(THU_MUC_ANH . $anh_moi);
            $loi['chung'] = 'Không thể lưu dữ liệu, vui lòng thử lại.';
        }
    }
}

$nhan_thuoc_tinh = ['phong_chup' => 'Sức chứa (người)', 'thiet_bi' => 'Loại thiết bị', 'trang_phuc' => 'Kích cỡ có sẵn (vd: S, M, L)', 'phu_kien' => 'Loại phụ kiện'];

nv_dau($dv_cu ? 'Sửa dịch vụ' : 'Thêm dịch vụ', $dv_cu ? 'dich-vu' : 'them');
$loi_truong = function (string $khoa) use ($loi) {
    if (isset($loi[$khoa])) echo '<span class="field-error">' . h($loi[$khoa]) . '</span>';
};
?>
        <section class="panel">
            <div class="panel-head"><h2><?= $dv_cu ? h($dv_cu['DV_Ten']) : 'Thông tin dịch vụ mới' ?></h2><a href="my-services.php" class="panel-link">← Danh sách</a></div>
            <?php if (isset($loi['chung'])) : ?><p class="flash flash-error"><?= h($loi['chung']) ?></p><?php endif; ?>

            <form method="post" enctype="multipart/form-data" novalidate>
                <?= csrf_input() ?>
                <input type="hidden" name="ma_sua" value="<?= $ma_sua ?>">
                <div class="form-grid">
                    <div class="field"><label for="ten">Tên dịch vụ</label><input id="ten" name="ten" value="<?= h($du_lieu['ten']) ?>" required><?php $loi_truong('ten'); ?></div>
                    <div class="field">
                        <label for="loai">Loại</label>
                        <select id="loai" name="loai" <?= $dv_cu ? 'disabled' : '' ?>>
                            <?php foreach (LOAI_HOP_LE as $m) : ?><option value="<?= $m ?>" <?= $du_lieu['loai'] === $m ? 'selected' : '' ?>><?= h(ten_loai_dich_vu($m)) ?></option><?php endforeach; ?>
                        </select>
                        <?php $loi_truong('loai'); ?>
                    </div>
                    <div class="field"><label for="gia">Giá thuê (₫)</label><input id="gia" name="gia" inputmode="numeric" value="<?= h($du_lieu['gia']) ?>"><?php $loi_truong('gia'); ?></div>
                    <div class="field">
                        <label for="don_vi">Tính theo</label>
                        <select id="don_vi" name="don_vi">
                            <?php foreach (DON_VI_HOP_LE as $m) : ?><option value="<?= $m ?>" <?= $du_lieu['don_vi'] === $m ? 'selected' : '' ?>><?= h(ucfirst(ten_don_vi_gia($m))) ?></option><?php endforeach; ?>
                        </select>
                        <?php $loi_truong('don_vi'); ?>
                    </div>
                    <div class="field"><label for="so_luong">Số lượng cho thuê cùng lúc</label><input id="so_luong" name="so_luong" inputmode="numeric" value="<?= h($du_lieu['so_luong']) ?>"><?php $loi_truong('so_luong'); ?></div>
                    <div class="field"><label for="thuoc_tinh" id="nhan_thuoc_tinh"><?= h($nhan_thuoc_tinh[$du_lieu['loai']] ?? 'Thuộc tính') ?></label><input id="thuoc_tinh" name="thuoc_tinh" value="<?= h($du_lieu['thuoc_tinh']) ?>"><?php $loi_truong('thuoc_tinh'); ?></div>
                    <div class="field"><label for="mo_ta">Mô tả ngắn</label><textarea id="mo_ta" name="mo_ta" rows="3"><?= h($du_lieu['mo_ta']) ?></textarea></div>
                    <div class="field"><label for="mo_ta_ct">Mô tả chi tiết</label><textarea id="mo_ta_ct" name="mo_ta_ct" rows="3"><?= h($du_lieu['mo_ta_ct']) ?></textarea></div>
                    <div class="field">
                        <label for="anh">Ảnh đại diện (JPG, PNG, WEBP, tối đa 3MB)</label>
                        <input id="anh" name="anh" type="file" accept="image/jpeg,image/png,image/webp">
                        <?php $loi_truong('anh'); ?>
                        <?php if ($dv_cu && $dv_cu['DV_HinhAnh']) : ?><img class="thumb" src="<?= h(duong_dan_anh($dv_cu['DV_HinhAnh'])) ?>" alt="Ảnh hiện tại"><?php endif; ?>
                    </div>
                    <div class="field"><label><input type="checkbox" name="hien" value="1" <?= $du_lieu['hien'] ? 'checked' : '' ?>> Hiển thị cho khách thuê</label></div>
                </div>
                <div class="form-actions">
                    <button class="btn-solid" type="submit">Lưu</button>
                    <a class="btn-line" href="my-services.php">Hủy</a>
                </div>
            </form>
        </section>
<?php if (!$dv_cu) : ?>
<script>
const nhan = <?= json_encode($nhan_thuoc_tinh, JSON_UNESCAPED_UNICODE) ?>;
document.getElementById('loai').addEventListener('change', function () {
    document.getElementById('nhan_thuoc_tinh').textContent = nhan[this.value];
});
</script>
<?php endif; ?>
<?php nv_cuoi();