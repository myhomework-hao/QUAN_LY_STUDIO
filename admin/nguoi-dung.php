<?php
require_once __DIR__ . '/khung_admin.php';

$ma_admin = (int) ma_nguoi_dung_hien_tai();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dich = ve_trang_truoc('nguoi-dung.php');
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong($dich);
    }
    $ma = (int) ($_POST['ma'] ?? 0);
    $khoa = ($_POST['hanh_dong'] ?? '') === 'khoa';
    if ($ma === $ma_admin) {
        dat_thong_bao('Bạn không thể khóa tài khoản của chính mình.', 'error');
        chuyen_huong($dich);
    }
    $khach = lay_mot($conn, "SELECT ND_Ma, ND_Ten FROM nguoidung WHERE ND_Ma = ? AND LND_Ma = ?", [$ma, VAI_TRO_KHACH_HANG]);
    if (!$khach) {
        dat_thong_bao('Không tìm thấy khách hàng.', 'error');
        chuyen_huong($dich);
    }
    $stmt = $conn->prepare("UPDATE nguoidung SET ND_TrangThai = ? WHERE ND_Ma = ?");
    $stmt->execute([$khoa ? 0 : 1, $ma]);
    dat_thong_bao(($khoa ? 'Đã khóa tài khoản ' : 'Đã mở khóa tài khoản ') . $khach['ND_Ten'] . '.');
    chuyen_huong($dich);
}

$ma_xem = (int) ($_GET['id'] ?? 0);

if ($ma_xem > 0) {
    $khach = lay_mot($conn, "SELECT * FROM nguoidung WHERE ND_Ma = ? AND LND_Ma = ?", [$ma_xem, VAI_TRO_KHACH_HANG]);
    if (!$khach) {
        dat_thong_bao('Không tìm thấy khách hàng.', 'error');
        chuyen_huong('nguoi-dung.php');
    }
    $don = lay_nhieu(
        $conn,
        "SELECT DDL_Ma, DDL_TongTien, DDL_TrangThai, DDL_NgayTao FROM dondatlich
         WHERE ND_Ma = ? ORDER BY DDL_Ma DESC LIMIT 20",
        [$ma_xem]
    );
    $tong_don = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich WHERE ND_Ma = ?", [$ma_xem]);

    admin_dau('Chi tiết khách hàng', 'nguoi-dung');
    ?>
    <section class="panel">
        <div class="panel-head">
            <h2><?= h($khach['ND_Ten']) ?></h2>
            <a href="nguoi-dung.php" class="panel-link">← Danh sách</a>
        </div>
        <dl class="detail-grid">
            <dt>Email</dt><dd><?= h($khach['ND_Email']) ?></dd>
            <dt>Số điện thoại</dt><dd><?= h($khach['ND_SDT'] ?: 'Chưa cập nhật') ?></dd>
            <dt>Giới tính</dt><dd><?= h($khach['ND_GioiTinh'] ?: 'Chưa cập nhật') ?></dd>
            <dt>Ngày sinh</dt><dd><?= $khach['ND_NgaySinh'] ? h(date('d/m/Y', strtotime($khach['ND_NgaySinh']))) : 'Chưa cập nhật' ?></dd>
            <dt>Ngày đăng ký</dt><dd><?= h(dinh_dang_ngay_gio($khach['ND_NgayTao'])) ?></dd>
            <dt>Trạng thái</dt>
            <dd><span class="badge <?= (int) $khach['ND_TrangThai'] === 1 ? 'badge-xac-nhan' : 'badge-khoa' ?>"><?= (int) $khach['ND_TrangThai'] === 1 ? 'Đang hoạt động' : 'Đã khóa' ?></span></dd>
            <dt>Tổng số đơn</dt><dd><?= $tong_don ?></dd>
        </dl>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Đơn thuê gần đây</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th><span class="sr-only">Thao tác</span></th></tr></thead>
                <tbody>
                    <?php if (!$don) : ?><tr><td colspan="5" class="empty">Khách chưa có đơn thuê nào.</td></tr><?php endif; ?>
                    <?php foreach ($don as $d) : ?>
                        <tr>
                            <td><?= h(ma_don_hien_thi((int) $d['DDL_Ma'])) ?></td>
                            <td><?= h(dinh_dang_ngay_gio($d['DDL_NgayTao'])) ?></td>
                            <td><?= h(dinh_dang_tien($d['DDL_TongTien'])) ?></td>
                            <td><span class="badge <?= lop_badge_don($d['DDL_TrangThai']) ?>"><?= h($d['DDL_TrangThai']) ?></span></td>
                            <td class="row-actions"><a href="<?= BASE_URL ?>order-detail.php?id=<?= (int) $d['DDL_Ma'] ?>">Chi tiết</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
    admin_cuoi();
    exit;
}

$tu_khoa = trim((string) ($_GET['q'] ?? ''));
$trang_thai = $_GET['tt'] ?? '';

$dieu_kien = ["LND_Ma = ?"];
$tham_so = [VAI_TRO_KHACH_HANG];
if ($tu_khoa !== '') {
    $dieu_kien[] = "(ND_Ten LIKE ? OR ND_Email LIKE ? OR ND_SDT LIKE ?)";
    $like = '%' . $tu_khoa . '%';
    array_push($tham_so, $like, $like, $like);
}
if ($trang_thai === '1' || $trang_thai === '0') {
    $dieu_kien[] = "ND_TrangThai = ?";
    $tham_so[] = (int) $trang_thai;
}
$where = implode(' AND ', $dieu_kien);

$tong = (int) gia_tri($conn, "SELECT COUNT(*) FROM nguoidung WHERE $where", $tham_so);
$tong_trang = 1;
$trang = trang_hien_tai($tong, $tong_trang);
$lech = ($trang - 1) * SO_DONG_MOI_TRANG;
$gioi_han = SO_DONG_MOI_TRANG;

$ds = lay_nhieu(
    $conn,
    "SELECT nd.ND_Ma, nd.ND_Ten, nd.ND_Email, nd.ND_SDT, nd.ND_TrangThai, nd.ND_NgayTao,
            (SELECT COUNT(*) FROM dondatlich d WHERE d.ND_Ma = nd.ND_Ma) AS so_don
     FROM nguoidung nd WHERE $where
     ORDER BY nd.ND_Ma DESC LIMIT $gioi_han OFFSET $lech",
    $tham_so
);

admin_dau('Người dùng', 'nguoi-dung');
?>
        <section class="panel">
            <div class="panel-head">
                <h2>Khách hàng (<?= $tong ?>)</h2>
            </div>

            <form class="filter" method="get">
                <div class="field wide">
                    <label for="q">Tìm theo tên, email, số điện thoại</label>
                    <input id="q" name="q" value="<?= h($tu_khoa) ?>">
                </div>
                <div class="field">
                    <label for="tt">Trạng thái</label>
                    <select id="tt" name="tt">
                        <option value="">Tất cả</option>
                        <option value="1" <?= $trang_thai === '1' ? 'selected' : '' ?>>Đang hoạt động</option>
                        <option value="0" <?= $trang_thai === '0' ? 'selected' : '' ?>>Đã khóa</option>
                    </select>
                </div>
                <button class="btn-solid" type="submit">Lọc</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>Ngày đăng ký</th><th>Số đơn</th><th>Trạng thái</th><th><span class="sr-only">Thao tác</span></th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$ds) : ?><tr><td colspan="7" class="empty">Không tìm thấy khách hàng nào.</td></tr><?php endif; ?>
                        <?php foreach ($ds as $k) : $hoat_dong = (int) $k['ND_TrangThai'] === 1; ?>
                            <tr>
                                <td><?= h($k['ND_Ten']) ?></td>
                                <td><?= h($k['ND_Email']) ?></td>
                                <td><?= h($k['ND_SDT'] ?: '—') ?></td>
                                <td><?= h(date('d/m/Y', strtotime($k['ND_NgayTao']))) ?></td>
                                <td><?= (int) $k['so_don'] ?></td>
                                <td><span class="badge <?= $hoat_dong ? 'badge-xac-nhan' : 'badge-khoa' ?>"><?= $hoat_dong ? 'Hoạt động' : 'Đã khóa' ?></span></td>
                                <td class="row-actions">
                                    <a href="?id=<?= (int) $k['ND_Ma'] ?>">Chi tiết</a>
                                    <form method="post" onsubmit="return confirm('<?= $hoat_dong ? 'Khóa' : 'Mở khóa' ?> tài khoản này?')">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="ma" value="<?= (int) $k['ND_Ma'] ?>">
                                        <input type="hidden" name="ve" value="<?= h($_SERVER['QUERY_STRING'] ?? '') ?>">
                                        <input type="hidden" name="hanh_dong" value="<?= $hoat_dong ? 'khoa' : 'mo' ?>">
                                        <button type="submit"><?= $hoat_dong ? 'Khóa' : 'Mở khóa' ?></button>
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