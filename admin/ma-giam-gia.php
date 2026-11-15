<?php
// Quản lý mã giảm giá (admin): xem, thêm, sửa, bật/tắt.
// Không có nút xóa: đơn hàng lưu mã dạng chuỗi (DDL_MaGiamGia), xóa mã là mất lịch sử đối soát.
require_once __DIR__ . '/khung_admin.php';

$cac_loai = ['percent' => 'Giảm theo %', 'fixed' => 'Giảm số tiền cố định'];

// Chuyển chuỗi từ input datetime-local về format DB; trả null nếu rỗng, false nếu sai định dạng
function doc_gio_form(string $raw)
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }
    $d = DateTime::createFromFormat('Y-m-d\TH:i', $raw);
    if (!$d) {
        $d = DateTime::createFromFormat('Y-m-d\TH:i:s', $raw);
    }
    return $d ? $d->format('Y-m-d H:i:s') : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dich = ve_trang_truoc('ma-giam-gia.php');
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong($dich);
    }

    $hanh_dong = (string) ($_POST['hanh_dong'] ?? '');
    $ma = (int) ($_POST['ma'] ?? 0);
    $loi = ''; // Phải khởi tạo trước chuỗi kiểm tra, nếu không khi hết lỗi thì $loi là null thay vì ''

    // Bật/tắt: không cần nhập liệu, UPDATE khít điều kiện trạng thái hiện tại
    if ($hanh_dong === 'bat' || $hanh_dong === 'tat') {
        $bat = $hanh_dong === 'bat';
        $stmt = $conn->prepare(
            "UPDATE ma_giam_gia SET MG_DangHoatDong = ? WHERE MG_Ma = ? AND MG_DangHoatDong = ?"
        );
        $stmt->execute([$bat ? 1 : 0, $ma, $bat ? 0 : 1]);
        dat_thong_bao($stmt->rowCount() === 1
            ? 'Đã ' . ($bat ? 'bật' : 'tắt') . ' mã giảm giá #' . $ma . '.'
            : 'Mã giảm giá #' . $ma . ' không còn ở trạng thái cũ, hãy tải lại trang.');
        chuyen_huong($dich);
    }

    // ---- Thêm / sửa: đọc + kiểm dữ liệu form ----
    $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
    $mo_ta = trim((string) ($_POST['mo_ta'] ?? ''));
    $loai = (string) ($_POST['loai'] ?? '');
    $gia_tri = (float) str_replace([',', ' '], '', (string) ($_POST['gia_tri'] ?? ''));
    $don_toi_thieu = (float) str_replace([',', ' '], '', (string) ($_POST['don_toi_thieu'] ?? '0'));
    $giam_toi_da_raw = trim((string) ($_POST['giam_toi_da'] ?? ''));
    $giam_toi_da = $giam_toi_da_raw !== '' ? (float) str_replace([',', ' '], '', $giam_toi_da_raw) : null;
    $so_luong = (int) ($_POST['so_luong'] ?? 0);
    $ngay_bat_dau = doc_gio_form((string) ($_POST['ngay_bat_dau'] ?? ''));
    $ngay_ket_thuc = doc_gio_form((string) ($_POST['ngay_ket_thuc'] ?? ''));

    if ($hanh_dong !== 'them' && $hanh_dong !== 'sua') {
        $loi = 'Hành động không hợp lệ.';
    } elseif (!preg_match('/^[A-Z0-9][A-Z0-9_-]{1,49}$/', $code)) {
        $loi = 'Mã chỉ gồm chữ hoa, số, gạch ngang/gạch dưới, dài 2-50 ký tự (ví dụ VIBE10).';
    } elseif (!isset($cac_loai[$loai])) {
        $loi = 'Vui lòng chọn loại giảm giá.';
    } elseif ($gia_tri <= 0) {
        $loi = 'Giá trị giảm phải lớn hơn 0.';
    } elseif ($loai === 'percent' && $gia_tri > 100) {
        $loi = 'Giảm theo % không được vượt quá 100.';
    } elseif ($loai === 'fixed' && $gia_tri > 9999999999) {
        $loi = 'Số tiền giảm quá lớn.';
    } elseif ($don_toi_thieu < 0 || $don_toi_thieu > 9999999999) {
        $loi = 'Đơn tối thiểu không hợp lệ.';
    } elseif ($loai === 'fixed') {
        $giam_toi_da = null; // giảm tối đa chỉ có ý nghĩa với loại percent
    } elseif ($giam_toi_da !== null && $giam_toi_da <= 0) {
        $loi = 'Giảm tối đa phải lớn hơn 0, hoặc bỏ trống nếu không giới hạn.';
    } elseif ($so_luong < 0) {
        $loi = 'Số lượng lượt không được âm (0 = không giới hạn).';
    } elseif ($ngay_bat_dau === false) {
        $loi = 'Thời gian bắt đầu không hợp lệ.';
    } elseif ($ngay_ket_thuc === false) {
        $loi = 'Thời gian kết thúc không hợp lệ.';
    } elseif ($ngay_bat_dau !== null && $ngay_ket_thuc !== null && $ngay_bat_dau >= $ngay_ket_thuc) {
        $loi = 'Thời gian kết thúc phải sau thời gian bắt đầu.';
    } elseif (mb_strlen($mo_ta) > 255) {
        $loi = 'Mô tả không được dài quá 255 ký tự.';
    }

    if ($loi === '') {
        try {
            if ($hanh_dong === 'them') {
                $stmt = $conn->prepare(
                    "INSERT INTO ma_giam_gia
                        (MG_Code, MG_MoTa, MG_Loai, MG_GiaTri, MG_DonToiThieu, MG_GiamToiDa,
                         MG_SoLuong, MG_NgayBatDau, MG_NgayKetThuc, MG_DangHoatDong)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $code, $mo_ta !== '' ? $mo_ta : null, $loai, $gia_tri, $don_toi_thieu,
                    $giam_toi_da, $so_luong,
                    $ngay_bat_dau ?? date('Y-m-d H:i:s'), $ngay_ket_thuc, 1,
                ]);
                dat_thong_bao('Đã thêm mã ' . $code . '.');
                chuyen_huong('ma-giam-gia.php');
            } else {
                $ton_tai = lay_mot($conn, "SELECT MG_Ma FROM ma_giam_gia WHERE MG_Ma = ?", [$ma]);
                $trung = lay_mot($conn, "SELECT MG_Ma FROM ma_giam_gia WHERE MG_Code = ? AND MG_Ma <> ?", [$code, $ma]);
                if (!$ton_tai) {
                    dat_thong_bao('Không tìm thấy mã giảm giá cần sửa.', 'error');
                    chuyen_huong('ma-giam-gia.php');
                }
                if ($trung) {
                    $loi = 'Mã ' . $code . ' đã tồn tại ở dòng #' . (int) $trung['MG_Ma'] . ', không thể dùng trùng.';
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE ma_giam_gia
                         SET MG_Code = ?, MG_MoTa = ?, MG_Loai = ?, MG_GiaTri = ?, MG_DonToiThieu = ?,
                             MG_GiamToiDa = ?, MG_SoLuong = ?, MG_NgayBatDau = ?, MG_NgayKetThuc = ?
                         WHERE MG_Ma = ?"
                    );
                    $stmt->execute([
                        $code, $mo_ta !== '' ? $mo_ta : null, $loai, $gia_tri, $don_toi_thieu,
                        $giam_toi_da, $so_luong, $ngay_bat_dau, $ngay_ket_thuc, $ma,
                    ]);
                    dat_thong_bao('Đã cập nhật mã ' . $code . '.');
                    chuyen_huong('ma-giam-gia.php');
                }
            }
        } catch (PDOException $e) {
            // Lỗi trùng UNIQUE khi hai admin thêm cùng lúc
            if ($e->getCode() === '23000') {
                $loi = 'Mã ' . $code . ' vừa được tạo bởi phiên khác, không thể dùng trùng.';
            } else {
                $loi = 'Không thể lưu mã giảm giá, vui lòng thử lại.';
            }
        }
    }

    // Có lỗi: giữ lại dữ liệu người dùng vừa nhập để sửa tiếp
    $form = [
        'ma' => $ma, 'code' => $code, 'mo_ta' => $mo_ta, 'loai' => $loai, 'gia_tri' => $gia_tri,
        'don_toi_thieu' => $don_toi_thieu,
        'giam_toi_da' => $giam_toi_da_raw,
        'so_luong' => $so_luong,
        'ngay_bat_dau' => (string) ($_POST['ngay_bat_dau'] ?? ''),
        'ngay_ket_thuc' => (string) ($_POST['ngay_ket_thuc'] ?? ''),
    ];
}

// Form sửa: nạp dữ liệu theo ?sua=ID (khi không có lỗi POST)
$sua_ma = (int) ($_GET['sua'] ?? 0);
if (!isset($form) && $sua_ma > 0) {
    $dang_sua = lay_mot($conn, "SELECT * FROM ma_giam_gia WHERE MG_Ma = ?", [$sua_ma]);
    if ($dang_sua) {
        $form = [
            'ma' => (int) $dang_sua['MG_Ma'],
            'code' => (string) $dang_sua['MG_Code'],
            'mo_ta' => (string) ($dang_sua['MG_MoTa'] ?? ''),
            'loai' => (string) $dang_sua['MG_Loai'],
            'gia_tri' => (float) $dang_sua['MG_GiaTri'],
            'don_toi_thieu' => (float) $dang_sua['MG_DonToiThieu'],
            'giam_toi_da' => $dang_sua['MG_GiamToiDa'] !== null ? rtrim(rtrim((string) $dang_sua['MG_GiamToiDa'], '0'), '.') : '',
            'so_luong' => (int) $dang_sua['MG_SoLuong'],
            'ngay_bat_dau' => $dang_sua['MG_NgayBatDau'] ? str_replace(' ', 'T', substr($dang_sua['MG_NgayBatDau'], 0, 16)) : '',
            'ngay_ket_thuc' => $dang_sua['MG_NgayKetThuc'] ? str_replace(' ', 'T', substr($dang_sua['MG_NgayKetThuc'], 0, 16)) : '',
        ];
    }
}

// ---- Danh sách ----
$tu_khoa = strtoupper(trim((string) ($_GET['q'] ?? '')));
$dieu_kien = ["1=1"];
$tham_so = [];
if ($tu_khoa !== '') {
    $dieu_kien[] = "(MG_Code LIKE ? OR MG_MoTa LIKE ?)";
    array_push($tham_so, '%' . $tu_khoa . '%', '%' . $tu_khoa . '%');
}
$where = implode(' AND ', $dieu_kien);

$tong = (int) gia_tri($conn, "SELECT COUNT(*) FROM ma_giam_gia WHERE $where", $tham_so);
$tong_trang = 1;
$trang = trang_hien_tai($tong, $tong_trang);
$lech = ($trang - 1) * SO_DONG_MOI_TRANG;

$ds = lay_nhieu(
    $conn,
    "SELECT * FROM ma_giam_gia WHERE $where ORDER BY MG_Ma DESC LIMIT " . SO_DONG_MOI_TRANG . " OFFSET $lech",
    $tham_so
);

// Nhãn trạng thái thực tế (không chỉ cờ MG_DangHoatDong, cả hạn và lượt)
function nhan_trang_thai_ma(array $m): array
{
    $bay_gio = date('Y-m-d H:i:s');
    if ((int) $m['MG_DangHoatDong'] !== 1) {
        return ['Đã tắt', 'badge-huy'];
    }
    if (!empty($m['MG_NgayBatDau']) && (string) $m['MG_NgayBatDau'] > $bay_gio) {
        return ['Chưa đến hạn', 'badge-cho'];
    }
    if (!empty($m['MG_NgayKetThuc']) && (string) $m['MG_NgayKetThuc'] < $bay_gio) {
        return ['Hết hạn', 'badge-huy'];
    }
    if ((int) $m['MG_SoLuong'] > 0 && (int) $m['MG_DaDung'] >= (int) $m['MG_SoLuong']) {
        return ['Hết lượt', 'badge-huy'];
    }
    return ['Đang dùng được', 'badge-hoan-thanh'];
}

admin_dau('Mã giảm giá', 'ma-giam-gia');
?>
        <section class="panel">
            <div class="panel-head">
                <h2><?= isset($form) && $form['ma'] > 0 && ($sua_ma > 0 || isset($loi)) ? 'Sửa mã giảm giá' : 'Thêm mã giảm giá' ?></h2>
            </div>
            <?php if (isset($loi) && $loi !== '') : ?>
                <div class="notice error" style="margin:0 16px 12px;"><span><?= h($loi) ?></span></div>
            <?php endif; ?>
            <form method="post" class="stack" style="padding:0 16px 16px;display:flex;flex-direction:column;gap:12px;max-width:640px;">
                <?= csrf_input() ?>
                <input type="hidden" name="ma" value="<?= (int) ($form['ma'] ?? 0) ?>">
                <input type="hidden" name="ve" value="<?= h($_SERVER['QUERY_STRING'] ?? '') ?>">

                <div class="field">
                    <label for="code">Mã (viết hoa, ví dụ VIBE10)</label>
                    <input id="code" name="code" maxlength="50" required value="<?= h($form['code'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="mo_ta">Mô tả (tùy chọn)</label>
                    <input id="mo_ta" name="mo_ta" maxlength="255" value="<?= h($form['mo_ta'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="loai">Loại giảm</label>
                    <select id="loai" name="loai" required>
                        <?php foreach ($cac_loai as $k => $ten) : ?>
                            <option value="<?= h($k) ?>" <?= ($form['loai'] ?? 'percent') === $k ? 'selected' : '' ?>><?= h($ten) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="gia_tri">Giá trị (%, hoặc số tiền VND theo loại)</label>
                    <input id="gia_tri" name="gia_tri" type="number" min="0" step="1" required value="<?= h((string) ($form['gia_tri'] ?? '')) ?>">
                </div>
                <div class="field">
                    <label for="don_toi_thieu">Đơn tối thiểu (VND, 0 = không yêu cầu)</label>
                    <input id="don_toi_thieu" name="don_toi_thieu" type="number" min="0" step="1000" value="<?= h((string) ($form['don_toi_thieu'] ?? '0')) ?>">
                </div>
                <div class="field">
                    <label for="giam_toi_da">Giảm tối đa (VND, chỉ cho loại %; bỏ trống = không giới hạn)</label>
                    <input id="giam_toi_da" name="giam_toi_da" type="number" min="0" step="1000" value="<?= h((string) ($form['giam_toi_da'] ?? '')) ?>">
                </div>
                <div class="field">
                    <label for="so_luong">Số lượt dùng (0 = không giới hạn)</label>
                    <input id="so_luong" name="so_luong" type="number" min="0" step="1" value="<?= h((string) ($form['so_luong'] ?? '0')) ?>">
                </div>
                <div class="field">
                    <label for="ngay_bat_dau">Bắt đầu (bỏ trống = hiệu lực ngay)</label>
                    <input id="ngay_bat_dau" name="ngay_bat_dau" type="datetime-local" value="<?= h($form['ngay_bat_dau'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="ngay_ket_thuc">Kết thúc (bỏ trống = không hết hạn)</label>
                    <input id="ngay_ket_thuc" name="ngay_ket_thuc" type="datetime-local" value="<?= h($form['ngay_ket_thuc'] ?? '') ?>">
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" name="hanh_dong" value="<?= (int) ($form['ma'] ?? 0) > 0 ? 'sua' : 'them' ?>" class="btn-solid">
                        <?= (int) ($form['ma'] ?? 0) > 0 ? 'Lưu thay đổi' : 'Thêm mã' ?>
                    </button>
                    <?php if ((int) ($form['ma'] ?? 0) > 0) : ?>
                        <a href="ma-giam-gia.php" class="btn-ghost" style="text-decoration:none;display:inline-flex;align-items:center;">Bỏ sửa</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Danh sách mã (<?= $tong ?>)</h2></div>
            <form class="filter" method="get">
                <div class="field wide"><label for="q">Tìm theo mã hoặc mô tả</label><input id="q" name="q" value="<?= h($tu_khoa) ?>"></div>
                <button class="btn-solid" type="submit">Lọc</button>
            </form>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Mã</th><th>Loại</th><th>Giá trị</th><th>Đơn tối thiểu</th><th>Giảm tối đa</th><th>Lượt dùng</th><th>Hạn</th><th>Trạng thái</th><th><span class="sr-only">Thao tác</span></th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$ds) : ?><tr><td colspan="9" class="empty">Chưa có mã giảm giá nào.</td></tr><?php endif; ?>
                        <?php foreach ($ds as $m) : ?>
                            <?php [$nhan_tt, $lop_tt] = nhan_trang_thai_ma($m); ?>
                            <tr>
                                <td><strong><?= h($m['MG_Code']) ?></strong><?= $m['MG_MoTa'] ? '<br><span class="muted">' . h($m['MG_MoTa']) . '</span>' : '' ?></td>
                                <td><?= h($cac_loai[$m['MG_Loai']] ?? $m['MG_Loai']) ?></td>
                                <td><?= $m['MG_Loai'] === 'percent' ? h((string) (float) $m['MG_GiaTri']) . '%' : h(dinh_dang_tien($m['MG_GiaTri'])) ?></td>
                                <td><?= (float) $m['MG_DonToiThieu'] > 0 ? h(dinh_dang_tien($m['MG_DonToiThieu'])) : '—' ?></td>
                                <td><?= $m['MG_GiamToiDa'] !== null ? h(dinh_dang_tien($m['MG_GiamToiDa'])) : '—' ?></td>
                                <td><?= (int) $m['MG_DaDung'] ?><?= (int) $m['MG_SoLuong'] > 0 ? ' / ' . (int) $m['MG_SoLuong'] : ' / ∞' ?></td>
                                <td><?= h(dinh_dang_ngay_gio($m['MG_NgayBatDau'])) ?><br>→ <?= $m['MG_NgayKetThuc'] ? h(dinh_dang_ngay_gio($m['MG_NgayKetThuc'])) : 'không hết hạn' ?></td>
                                <td><span class="badge <?= h($lop_tt) ?>"><?= h($nhan_tt) ?></span></td>
                                <td class="row-actions">
                                    <a href="?sua=<?= (int) $m['MG_Ma'] ?>">Sửa</a>
                                    <form method="post" style="display:inline" onsubmit="return confirm('<?= (int) $m['MG_DangHoatDong'] === 1 ? 'Tắt' : 'Bật' ?> mã <?= h($m['MG_Code']) ?>?');">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="ma" value="<?= (int) $m['MG_Ma'] ?>">
                                        <input type="hidden" name="ve" value="<?= h($_SERVER['QUERY_STRING'] ?? '') ?>">
                                        <button type="submit" name="hanh_dong" value="<?= (int) $m['MG_DangHoatDong'] === 1 ? 'tat' : 'bat' ?>"><?= (int) $m['MG_DangHoatDong'] === 1 ? 'Tắt' : 'Bật' ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php hien_phan_trang($tong_trang, $trang); ?>
        </section>
<?php admin_cuoi(); ?>
