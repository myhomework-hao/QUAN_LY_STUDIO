<?php
require_once __DIR__ . '/khung_admin.php';

$loi = [];
$du_lieu = ['ten' => '', 'email' => '', 'sdt' => '', 'ma_nv' => '', 'chuc_vu' => '', 'ngay_vao' => date('Y-m-d'), 'mat_khau' => ''];
$che_do = isset($_GET['them']) ? 'them' : (isset($_GET['sua']) ? 'sua' : 'ds');
$ma_sua = (int) ($_GET['sua'] ?? $_POST['ma_sua'] ?? 0);

function lay_nhan_vien(PDO $conn, int $nv_ma)
{
    return lay_mot(
        $conn,
        "SELECT nv.*, nd.ND_Ten, nd.ND_Email, nd.ND_SDT, nd.ND_TrangThai
         FROM nhanvien nv JOIN nguoidung nd ON nd.ND_Ma = nv.ND_Ma WHERE nv.NV_Ma = ?",
        [$nv_ma]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanh_dong = $_POST['hanh_dong'] ?? '';
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong('nhan-vien.php');
    }

    if ($hanh_dong === 'khoa' || $hanh_dong === 'mo') {
        $nv = lay_nhan_vien($conn, (int) ($_POST['ma'] ?? 0));
        if (!$nv) {
            dat_thong_bao('Không tìm thấy nhân viên.', 'error');
        } else {
            $gia_tri_moi = $hanh_dong === 'mo' ? 1 : 0;
            $conn->beginTransaction();
            $conn->prepare("UPDATE nhanvien SET NV_TrangThai = ? WHERE NV_Ma = ?")->execute([$gia_tri_moi, $nv['NV_Ma']]);
            $conn->prepare("UPDATE nguoidung SET ND_TrangThai = ? WHERE ND_Ma = ?")->execute([$gia_tri_moi, $nv['ND_Ma']]);
            $conn->commit();
            dat_thong_bao(($gia_tri_moi ? 'Đã mở khóa ' : 'Đã khóa ') . $nv['ND_Ten'] . '.');
        }
        chuyen_huong('nhan-vien.php');
    }

    if ($hanh_dong === 'doi_mat_khau') {
        $nv = lay_nhan_vien($conn, (int) ($_POST['ma'] ?? 0));
        $mat_khau = (string) ($_POST['mat_khau_moi'] ?? '');
        if (!$nv) {
            dat_thong_bao('Không tìm thấy nhân viên.', 'error');
        } elseif (strlen($mat_khau) < 6) {
            dat_thong_bao('Mật khẩu mới phải có ít nhất 6 ký tự.', 'error');
        } else {
            $conn->prepare("UPDATE nguoidung SET ND_MatKhau = ? WHERE ND_Ma = ?")
                ->execute([password_hash($mat_khau, PASSWORD_DEFAULT), $nv['ND_Ma']]);
            dat_thong_bao('Đã đặt lại mật khẩu cho ' . $nv['ND_Ten'] . '.');
        }
        chuyen_huong('nhan-vien.php');
    }

    if ($hanh_dong === 'luu') {
        $che_do = $ma_sua > 0 ? 'sua' : 'them';
        $du_lieu = [
            'ten' => trim((string) ($_POST['ten'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'sdt' => trim((string) ($_POST['sdt'] ?? '')),
            'ma_nv' => strtoupper(trim((string) ($_POST['ma_nv'] ?? ''))),
            'chuc_vu' => trim((string) ($_POST['chuc_vu'] ?? '')),
            'ngay_vao' => trim((string) ($_POST['ngay_vao'] ?? '')),
            'mat_khau' => (string) ($_POST['mat_khau'] ?? ''),
        ];
        $nv_cu = $ma_sua > 0 ? lay_nhan_vien($conn, $ma_sua) : null;
        if ($ma_sua > 0 && !$nv_cu) {
            dat_thong_bao('Không tìm thấy nhân viên.', 'error');
            chuyen_huong('nhan-vien.php');
        }
        $nd_bo_qua = $nv_cu['ND_Ma'] ?? 0;
        $nv_bo_qua = $nv_cu['NV_Ma'] ?? 0;

        if ($du_lieu['ten'] === '') $loi['ten'] = 'Vui lòng nhập họ tên.';
        if (!filter_var($du_lieu['email'], FILTER_VALIDATE_EMAIL)) {
            $loi['email'] = 'Email không hợp lệ.';
        } elseif (gia_tri($conn, "SELECT COUNT(*) FROM nguoidung WHERE ND_Email = ? AND ND_Ma <> ?", [$du_lieu['email'], $nd_bo_qua])) {
            $loi['email'] = 'Email đã được sử dụng.';
        }
        if ($du_lieu['sdt'] !== '' && !preg_match('/^[0-9]{9,11}$/', $du_lieu['sdt'])) $loi['sdt'] = 'Số điện thoại gồm 9 đến 11 chữ số.';
        if ($du_lieu['ma_nv'] === '') {
            $loi['ma_nv'] = 'Vui lòng nhập mã nhân viên.';
        } elseif (gia_tri($conn, "SELECT COUNT(*) FROM nhanvien WHERE NV_MaNhanVien = ? AND NV_Ma <> ?", [$du_lieu['ma_nv'], $nv_bo_qua])) {
            $loi['ma_nv'] = 'Mã nhân viên đã tồn tại.';
        }
        $ngay = DateTime::createFromFormat('Y-m-d', $du_lieu['ngay_vao']);
        if ($du_lieu['ngay_vao'] !== '' && (!$ngay || $ngay->format('Y-m-d') !== $du_lieu['ngay_vao'])) $loi['ngay_vao'] = 'Ngày vào làm không hợp lệ.';
        if ($ma_sua === 0 && strlen($du_lieu['mat_khau']) < 6) $loi['mat_khau'] = 'Mật khẩu phải có ít nhất 6 ký tự.';

        if (!$loi) {
            $ngay_vao = $du_lieu['ngay_vao'] !== '' ? $du_lieu['ngay_vao'] : null;
            $sdt = $du_lieu['sdt'] !== '' ? $du_lieu['sdt'] : null;
            $chuc_vu = $du_lieu['chuc_vu'] !== '' ? $du_lieu['chuc_vu'] : null;
            $conn->beginTransaction();
            try {
                if ($ma_sua > 0) {
                    $conn->prepare("UPDATE nguoidung SET ND_Ten = ?, ND_Email = ?, ND_SDT = ? WHERE ND_Ma = ?")
                        ->execute([$du_lieu['ten'], $du_lieu['email'], $sdt, $nd_bo_qua]);
                    $conn->prepare("UPDATE nhanvien SET NV_MaNhanVien = ?, NV_ChucVu = ?, NV_NgayVaoLam = ? WHERE NV_Ma = ?")
                        ->execute([$du_lieu['ma_nv'], $chuc_vu, $ngay_vao, $nv_bo_qua]);
                } else {
                    $conn->prepare("INSERT INTO nguoidung (ND_Ten, ND_Email, ND_SDT, ND_MatKhau, ND_TrangThai, LND_Ma) VALUES (?, ?, ?, ?, 1, ?)")
                        ->execute([$du_lieu['ten'], $du_lieu['email'], $sdt, password_hash($du_lieu['mat_khau'], PASSWORD_DEFAULT), VAI_TRO_NHAN_VIEN]);
                    $nd_ma = (int) $conn->lastInsertId();
                    $conn->prepare("INSERT INTO nhanvien (ND_Ma, NV_MaNhanVien, NV_ChucVu, NV_NgayVaoLam, NV_TrangThai) VALUES (?, ?, ?, ?, 1)")
                        ->execute([$nd_ma, $du_lieu['ma_nv'], $chuc_vu, $ngay_vao]);
                }
                $conn->commit();
                dat_thong_bao($ma_sua > 0 ? 'Đã cập nhật thông tin nhân viên.' : 'Đã thêm nhân viên mới.');
                chuyen_huong('nhan-vien.php');
            } catch (PDOException $e) {
                $conn->rollBack();
                $loi['chung'] = 'Không thể lưu dữ liệu, vui lòng thử lại.';
            }
        }
    }
}

if ($che_do === 'sua' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $nv = lay_nhan_vien($conn, $ma_sua);
    if (!$nv) {
        dat_thong_bao('Không tìm thấy nhân viên.', 'error');
        chuyen_huong('nhan-vien.php');
    }
    $du_lieu = [
        'ten' => $nv['ND_Ten'], 'email' => $nv['ND_Email'], 'sdt' => (string) $nv['ND_SDT'],
        'ma_nv' => $nv['NV_MaNhanVien'], 'chuc_vu' => (string) $nv['NV_ChucVu'],
        'ngay_vao' => (string) $nv['NV_NgayVaoLam'], 'mat_khau' => '',
    ];
}

if ($che_do !== 'ds') {
    admin_dau($che_do === 'them' ? 'Thêm nhân viên' : 'Sửa nhân viên', 'nhan-vien');
    $o = function (string $khoa, string $nhan, string $loai = 'text', bool $bat_buoc = false) use ($du_lieu, $loi) {
        $gia_tri = $loai === 'password' ? '' : $du_lieu[$khoa];
        echo '<div class="field"><label for="' . $khoa . '">' . h($nhan) . '</label>';
        echo '<input id="' . $khoa . '" name="' . $khoa . '" type="' . $loai . '" value="' . h($gia_tri) . '"' . ($bat_buoc ? ' required' : '') . '>';
        if (isset($loi[$khoa])) echo '<span class="field-error">' . h($loi[$khoa]) . '</span>';
        echo '</div>';
    };
    ?>
    <section class="panel">
        <div class="panel-head"><h2><?= $che_do === 'them' ? 'Thông tin nhân viên mới' : 'Cập nhật thông tin' ?></h2><a href="nhan-vien.php" class="panel-link">← Danh sách</a></div>
        <?php if (isset($loi['chung'])) : ?><p class="flash flash-error"><?= h($loi['chung']) ?></p><?php endif; ?>
        <form method="post" novalidate>
            <?= csrf_input() ?>
            <input type="hidden" name="hanh_dong" value="luu">
            <input type="hidden" name="ma_sua" value="<?= $ma_sua ?>">
            <div class="form-grid">
                <?php $o('ten', 'Họ tên', 'text', true); $o('email', 'Email', 'email', true); $o('sdt', 'Số điện thoại'); $o('ma_nv', 'Mã nhân viên', 'text', true); $o('chuc_vu', 'Chức vụ'); $o('ngay_vao', 'Ngày vào làm', 'date'); ?>
                <?php if ($che_do === 'them') $o('mat_khau', 'Mật khẩu (tối thiểu 6 ký tự)', 'password', true); ?>
            </div>
            <div class="form-actions">
                <button class="btn-solid" type="submit">Lưu</button>
                <a class="btn-line" href="nhan-vien.php">Hủy</a>
            </div>
        </form>
    </section>
    <?php
    admin_cuoi();
    exit;
}

$tu_khoa = trim((string) ($_GET['q'] ?? ''));
$trang_thai = $_GET['tt'] ?? '';
$dieu_kien = ['1 = 1'];
$tham_so = [];
if ($tu_khoa !== '') {
    $dieu_kien[] = "(nd.ND_Ten LIKE ? OR nd.ND_Email LIKE ? OR nv.NV_MaNhanVien LIKE ?)";
    $like = '%' . $tu_khoa . '%';
    array_push($tham_so, $like, $like, $like);
}
if ($trang_thai === '1' || $trang_thai === '0') {
    $dieu_kien[] = "nv.NV_TrangThai = ?";
    $tham_so[] = (int) $trang_thai;
}
$where = implode(' AND ', $dieu_kien);
$tong = (int) gia_tri($conn, "SELECT COUNT(*) FROM nhanvien nv JOIN nguoidung nd ON nd.ND_Ma = nv.ND_Ma WHERE $where", $tham_so);
$tong_trang = 1;
$trang = trang_hien_tai($tong, $tong_trang);
$lech = ($trang - 1) * SO_DONG_MOI_TRANG;
$gioi_han = SO_DONG_MOI_TRANG;
$ds = lay_nhieu(
    $conn,
    "SELECT nv.NV_Ma, nv.NV_MaNhanVien, nv.NV_ChucVu, nv.NV_NgayVaoLam, nv.NV_TrangThai, nd.ND_Ten, nd.ND_Email, nd.ND_TrangThai,
            (SELECT COUNT(*) FROM dondatlich d WHERE d.NV_Ma = nv.NV_Ma) AS so_don
     FROM nhanvien nv JOIN nguoidung nd ON nd.ND_Ma = nv.ND_Ma
     WHERE $where ORDER BY nv.NV_Ma DESC LIMIT $gioi_han OFFSET $lech",
    $tham_so
);

admin_dau('Nhân viên', 'nhan-vien');
?>
        <section class="panel">
            <div class="panel-head">
                <h2>Danh sách nhân viên (<?= $tong ?>)</h2>
                <a href="?them=1" class="btn-solid">Thêm nhân viên</a>
            </div>

            <form class="filter" method="get">
                <div class="field wide">
                    <label for="q">Tìm theo tên, email, mã nhân viên</label>
                    <input id="q" name="q" value="<?= h($tu_khoa) ?>">
                </div>
                <div class="field">
                    <label for="tt">Trạng thái</label>
                    <select id="tt" name="tt">
                        <option value="">Tất cả</option>
                        <option value="1" <?= $trang_thai === '1' ? 'selected' : '' ?>>Đang làm</option>
                        <option value="0" <?= $trang_thai === '0' ? 'selected' : '' ?>>Đã khóa</option>
                    </select>
                </div>
                <button class="btn-solid" type="submit">Lọc</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Email</th><th>Chức vụ</th><th>Vào làm</th><th>Đơn đã xử lý</th><th>Trạng thái</th><th><span class="sr-only">Thao tác</span></th></tr></thead>
                    <tbody>
                        <?php if (!$ds) : ?><tr><td colspan="8" class="empty">Không tìm thấy nhân viên nào.</td></tr><?php endif; ?>
                        <?php foreach ($ds as $n) : $dang_lam = (int) $n['NV_TrangThai'] === 1 && (int) $n['ND_TrangThai'] === 1; ?>
                            <tr>
                                <td><?= h($n['NV_MaNhanVien']) ?></td>
                                <td><?= h($n['ND_Ten']) ?></td>
                                <td><?= h($n['ND_Email']) ?></td>
                                <td><?= h($n['NV_ChucVu'] ?: '—') ?></td>
                                <td><?= $n['NV_NgayVaoLam'] ? h(date('d/m/Y', strtotime($n['NV_NgayVaoLam']))) : '—' ?></td>
                                <td><?= (int) $n['so_don'] ?></td>
                                <td><span class="badge <?= $dang_lam ? 'badge-xac-nhan' : 'badge-khoa' ?>"><?= $dang_lam ? 'Đang làm' : 'Đã khóa' ?></span></td>
                                <td class="row-actions">
                                    <a href="?sua=<?= (int) $n['NV_Ma'] ?>">Sửa</a>
                                    <form method="post" onsubmit="var m=prompt('Nhập mật khẩu mới (tối thiểu 6 ký tự):'); if(!m){return false;} this.mat_khau_moi.value=m;">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="hanh_dong" value="doi_mat_khau">
                                        <input type="hidden" name="ma" value="<?= (int) $n['NV_Ma'] ?>">
                                        <input type="hidden" name="mat_khau_moi" value="">
                                        <button type="submit">Đặt lại mật khẩu</button>
                                    </form>
                                    <form method="post" onsubmit="return confirm('<?= $dang_lam ? 'Khóa' : 'Mở khóa' ?> tài khoản nhân viên này?')">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="hanh_dong" value="<?= $dang_lam ? 'khoa' : 'mo' ?>">
                                        <input type="hidden" name="ma" value="<?= (int) $n['NV_Ma'] ?>">
                                        <button type="submit"><?= $dang_lam ? 'Khóa' : 'Mở khóa' ?></button>
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