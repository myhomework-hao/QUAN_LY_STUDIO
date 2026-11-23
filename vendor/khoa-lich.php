<?php
require_once __DIR__ . '/khung_nv.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong('khoa-lich.php');
    }

    $hanh_dong = (string) ($_POST['hanh_dong'] ?? '');
    if ($hanh_dong === 'them') {
        $ma_dv = (int) ($_POST['ma_dv'] ?? 0);
        $bat_dau = trim((string) ($_POST['bat_dau'] ?? ''));
        $ket_thuc = trim((string) ($_POST['ket_thuc'] ?? ''));

        $dv = lay_mot($conn, "SELECT DV_Ma, DV_Ten FROM dichvu WHERE DV_Ma = ? AND DV_DangBan = 1", [$ma_dv]);
        if (!$dv) {
            dat_thong_bao('Dịch vụ không hợp lệ.', 'error');
        } elseif (strtotime($bat_dau) === false || strtotime($ket_thuc) === false || strtotime($ket_thuc) <= strtotime($bat_dau)) {
            dat_thong_bao('Thời gian khóa không hợp lệ.', 'error');
        } else {
            try {
                $conn->prepare(
                    "INSERT INTO lichthue (DV_Ma, LT_BatDau, LT_KetThuc, LT_TrangThai) VALUES (?, ?, ?, 'khóa')"
                )->execute([$ma_dv, $bat_dau, $ket_thuc]);
                dat_thong_bao('Đã thêm khóa lịch.');
            } catch (Throwable $e) {
                dat_thong_bao('Không thể thêm khóa lịch.', 'error');
            }
        }
        chuyen_huong('khoa-lich.php');
    }

    if ($hanh_dong === 'xoa') {
        $ma = (int) ($_POST['ma'] ?? 0);
        if ($ma > 0) {
            $conn->prepare("DELETE FROM lichthue WHERE LT_Ma = ? AND LT_TrangThai = 'khóa'")->execute([$ma]);
            dat_thong_bao('Đã xóa khóa lịch.');
        }
        chuyen_huong('khoa-lich.php');
    }
}

$ds_dv = lay_nhieu($conn, "SELECT DV_Ma, DV_Ten, DV_Loai FROM dichvu WHERE DV_DangBan = 1 ORDER BY DV_Ten");
$ds_khoa = lay_nhieu(
    $conn,
    "SELECT lt.LT_Ma, lt.DV_Ma, dv.DV_Ten, lt.LT_BatDau, lt.LT_KetThuc
     FROM lichthue lt JOIN dichvu dv ON dv.DV_Ma = lt.DV_Ma
     WHERE lt.LT_TrangThai = 'khóa'
     ORDER BY lt.LT_BatDau DESC"
);

nv_dau('Khóa lịch dịch vụ', 'khoa-lich');
?>
<section class="panel">
    <div class="panel-head"><h2>Thêm khóa lịch</h2></div>
    <form method="post" class="filter">
        <?= csrf_input() ?>
        <input type="hidden" name="hanh_dong" value="them">
        <div class="field wide">
            <label for="ma_dv">Dịch vụ</label>
            <select id="ma_dv" name="ma_dv" required>
                <option value="">-- Chọn dịch vụ --</option>
                <?php foreach ($ds_dv as $dv) : ?>
                    <option value="<?= (int) $dv['DV_Ma'] ?>"><?= h($dv['DV_Ten']) ?> (<?= h(ten_loai_dich_vu($dv['DV_Loai'])) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="bat_dau">Từ</label>
            <input id="bat_dau" type="datetime-local" name="bat_dau" required>
        </div>
        <div class="field">
            <label for="ket_thuc">Đến</label>
            <input id="ket_thuc" type="datetime-local" name="ket_thuc" required>
        </div>
        <button class="btn-solid" type="submit">Khóa lịch</button>
    </form>
</section>

<section class="panel">
    <div class="panel-head"><h2>Danh sách khóa lịch</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Dịch vụ</th><th>Từ</th><th>Đến</th><th>Thao tác</th></tr></thead>
            <tbody>
                <?php if (!$ds_khoa) : ?><tr><td colspan="4" class="empty">Chưa có khóa lịch nào.</td></tr><?php endif; ?>
                <?php foreach ($ds_khoa as $k) : ?>
                    <tr>
                        <td><?= h($k['DV_Ten']) ?></td>
                        <td><?= h(dinh_dang_ngay_gio($k['LT_BatDau'])) ?></td>
                        <td><?= h(dinh_dang_ngay_gio($k['LT_KetThuc'])) ?></td>
                        <td>
                            <form method="post" style="display:inline" onsubmit="return confirm('Xóa khóa lịch này?')">
                                <?= csrf_input() ?>
                                <input type="hidden" name="hanh_dong" value="xoa">
                                <input type="hidden" name="ma" value="<?= (int) $k['LT_Ma'] ?>">
                                <button type="submit" class="panel-link" style="color:#c00;">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php nv_cuoi(); ?>