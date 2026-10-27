<?php
require_once __DIR__ . '/khung.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dich = ve_trang_truoc('vendor-orders.php');
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong($dich);
    }
    $ma_don = (int) ($_POST['ma'] ?? 0);
    $hanh_dong = (string) ($_POST['hanh_dong'] ?? '');
    $bay_gio = date('Y-m-d H:i:s');
    $don = lay_mot($conn, "SELECT DDL_Ma, DDL_TrangThai FROM dondatlich WHERE DDL_Ma = ?", [$ma_don]);

    if (!$don) {
        dat_thong_bao('Không tìm thấy đơn thuê.', 'error');
        chuyen_huong($dich);
    }
    $ten_don = ma_don_hien_thi($ma_don);
    $thanh_cong = false;

    if ($hanh_dong === 'duyet') {
        $stmt = $conn->prepare("UPDATE dondatlich SET DDL_TrangThai = ?, NV_Ma = ?, DDL_ThoiDiemXuLy = ? WHERE DDL_Ma = ? AND DDL_TrangThai = ?");
        $stmt->execute([TRANG_THAI_DA_DUYET, $ma_nhan_vien, $bay_gio, $ma_don, TRANG_THAI_CHO_DUYET]);
        $thanh_cong = $stmt->rowCount() === 1;
        dat_thong_bao($thanh_cong ? "Đã duyệt đơn $ten_don." : "Đơn $ten_don không còn ở trạng thái chờ duyệt.", $thanh_cong ? 'success' : 'error');
    } elseif ($hanh_dong === 'tu_choi') {
        $ly_do = trim((string) ($_POST['ly_do'] ?? ''));
        if ($ly_do === '' || mb_strlen($ly_do) > 255) {
            dat_thong_bao('Vui lòng nhập lý do từ chối (tối đa 255 ký tự).', 'error');
        } else {
            $stmt = $conn->prepare("UPDATE dondatlich SET DDL_TrangThai = ?, NV_Ma = ?, DDL_ThoiDiemXuLy = ?, DDL_LyDoTuChoi = ? WHERE DDL_Ma = ? AND DDL_TrangThai = ?");
            $stmt->execute([TRANG_THAI_TU_CHOI, $ma_nhan_vien, $bay_gio, $ly_do, $ma_don, TRANG_THAI_CHO_DUYET]);
            $thanh_cong = $stmt->rowCount() === 1;
            dat_thong_bao($thanh_cong ? "Đã từ chối đơn $ten_don." : "Đơn $ten_don không còn ở trạng thái chờ duyệt.", $thanh_cong ? 'success' : 'error');
        }
    } elseif ($hanh_dong === 'hoan_thanh') {
        $bat_dau_som_nhat = gia_tri($conn, "SELECT MIN(CTD_BatDau) FROM chitietdon WHERE DDL_Ma = ?", [$ma_don]);
        if ($bat_dau_som_nhat && $bat_dau_som_nhat > $bay_gio) {
            dat_thong_bao("Đơn $ten_don chưa đến thời gian thuê nên chưa thể hoàn thành.", 'error');
        } else {
            $conn->beginTransaction();
            $stmt = $conn->prepare("UPDATE dondatlich SET DDL_TrangThai = ?, NV_MaHoanThanh = ?, DDL_ThoiDiemHoanThanh = ? WHERE DDL_Ma = ? AND DDL_TrangThai = ?");
            $stmt->execute([TRANG_THAI_HOAN_THANH, $ma_nhan_vien, $bay_gio, $ma_don, TRANG_THAI_DA_DUYET]);
            if ($stmt->rowCount() === 1) {
                $conn->prepare("UPDATE thanhtoan SET TT_TrangThai = 'Thành công' WHERE DDL_Ma = ? AND TT_TrangThai = 'Chờ'")->execute([$ma_don]);
                $conn->commit();
                dat_thong_bao("Đã hoàn thành đơn $ten_don.");
            } else {
                $conn->rollBack();
                dat_thong_bao("Đơn $ten_don không còn ở trạng thái đã duyệt.", 'error');
            }
        }
    } else {
        dat_thong_bao('Thao tác không hợp lệ.', 'error');
    }
    chuyen_huong($dich);
}

$tu_khoa = trim((string) ($_GET['q'] ?? ''));
$trang_thai = (string) ($_GET['tt'] ?? '');
$cac_trang_thai = [TRANG_THAI_CHO_DUYET, TRANG_THAI_DA_DUYET, TRANG_THAI_HOAN_THANH, TRANG_THAI_TU_CHOI, TRANG_THAI_DA_HUY];

$dieu_kien = ['1 = 1'];
$tham_so = [];
if (in_array($trang_thai, $cac_trang_thai, true)) { $dieu_kien[] = "d.DDL_TrangThai = ?"; $tham_so[] = $trang_thai; } else { $trang_thai = ''; }
if ($tu_khoa !== '') {
    $so = preg_replace('/\D/', '', $tu_khoa);
    $dieu_kien[] = "(nd.ND_Ten LIKE ? OR nd.ND_Email LIKE ?" . ($so !== '' ? " OR d.DDL_Ma = ?" : "") . ")";
    $like = '%' . $tu_khoa . '%';
    array_push($tham_so, $like, $like);
    if ($so !== '') $tham_so[] = (int) $so;
}
$where = implode(' AND ', $dieu_kien);

$tong = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich d JOIN nguoidung nd ON nd.ND_Ma = d.ND_Ma WHERE $where", $tham_so);
$tong_trang = 1;
$trang = trang_hien_tai($tong, $tong_trang);
$lech = ($trang - 1) * SO_DONG_MOI_TRANG;
$gioi_han = SO_DONG_MOI_TRANG;

$ds = lay_nhieu(
    $conn,
    "SELECT d.DDL_Ma, d.DDL_TrangThai, d.DDL_TongTien, d.DDL_NgayTao, d.DDL_LyDoTuChoi, nd.ND_Ten,
            (SELECT MIN(c.CTD_BatDau) FROM chitietdon c WHERE c.DDL_Ma = d.DDL_Ma) AS bat_dau,
            (SELECT dv.DV_Ten FROM chitietdon c JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma WHERE c.DDL_Ma = d.DDL_Ma ORDER BY c.CTD_Ma LIMIT 1) AS ten_dau,
            (SELECT COUNT(*) FROM chitietdon c WHERE c.DDL_Ma = d.DDL_Ma) AS so_dich_vu
     FROM dondatlich d JOIN nguoidung nd ON nd.ND_Ma = d.ND_Ma
     WHERE $where
     ORDER BY (CASE WHEN d.DDL_TrangThai = 'Chờ duyệt' THEN 0 ELSE 1 END), d.DDL_Ma DESC
     LIMIT $gioi_han OFFSET $lech",
    $tham_so
);

nv_dau('Đơn thuê', 'don');
?>
        <section class="panel">
            <div class="panel-head"><h2>Danh sách đơn (<?= $tong ?>)</h2></div>

            <form class="filter" method="get">
                <div class="field wide"><label for="q">Tìm theo mã đơn, tên hoặc email khách</label><input id="q" name="q" value="<?= h($tu_khoa) ?>"></div>
                <div class="field">
                    <label for="tt">Trạng thái</label>
                    <select id="tt" name="tt">
                        <option value="">Tất cả</option>
                        <?php foreach ($cac_trang_thai as $t) : ?><option value="<?= h($t) ?>" <?= $trang_thai === $t ? 'selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <button class="btn-solid" type="submit">Lọc</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Dịch vụ</th><th>Bắt đầu thuê</th><th>Tổng tiền</th><th>Trạng thái</th><th><span class="sr-only">Thao tác</span></th></tr></thead>
                    <tbody>
                        <?php if (!$ds) : ?><tr><td colspan="7" class="empty">Không có đơn thuê nào.</td></tr><?php endif; ?>
                        <?php foreach ($ds as $d) : ?>
                            <tr>
                                <td><?= h(ma_don_hien_thi((int) $d['DDL_Ma'])) ?></td>
                                <td><?= h($d['ND_Ten']) ?></td>
                                <td><?= h($d['ten_dau']) ?><?php if ((int) $d['so_dich_vu'] > 1) : ?> <span class="muted">+<?= (int) $d['so_dich_vu'] - 1 ?></span><?php endif; ?></td>
                                <td><?= h(dinh_dang_ngay_gio($d['bat_dau'])) ?></td>
                                <td><?= h(dinh_dang_tien($d['DDL_TongTien'])) ?></td>
                                <td>
                                    <span class="badge <?= lop_badge_don($d['DDL_TrangThai']) ?>"><?= h($d['DDL_TrangThai']) ?></span>
                                    <?php if ($d['DDL_LyDoTuChoi']) : ?><br><span class="muted"><?= h($d['DDL_LyDoTuChoi']) ?></span><?php endif; ?>
                                </td>
                                <td class="row-actions">
                                    <a href="<?= BASE_URL ?>KB/order-detail.php?id=<?= (int) $d['DDL_Ma'] ?>">Chi tiết</a>
                                    <?php if ($d['DDL_TrangThai'] === TRANG_THAI_CHO_DUYET) : ?>
                                        <form method="post" onsubmit="return confirm('Duyệt đơn này?')">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="ma" value="<?= (int) $d['DDL_Ma'] ?>">
                                            <input type="hidden" name="ve" value="<?= h($_SERVER['QUERY_STRING'] ?? '') ?>">
                                            <input type="hidden" name="hanh_dong" value="duyet">
                                            <button type="submit">Duyệt</button>
                                        </form>
                                        <form method="post" onsubmit="var l=prompt('Nhập lý do từ chối:'); if(!l||!l.trim()){return false;} this.ly_do.value=l;">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="ma" value="<?= (int) $d['DDL_Ma'] ?>">
                                            <input type="hidden" name="ve" value="<?= h($_SERVER['QUERY_STRING'] ?? '') ?>">
                                            <input type="hidden" name="hanh_dong" value="tu_choi">
                                            <input type="hidden" name="ly_do" value="">
                                            <button type="submit">Từ chối</button>
                                        </form>
                                    <?php elseif ($d['DDL_TrangThai'] === TRANG_THAI_DA_DUYET) : ?>
                                        <form method="post" onsubmit="return confirm('Xác nhận khách đã nhận và trả xong, hoàn thành đơn?')">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="ma" value="<?= (int) $d['DDL_Ma'] ?>">
                                            <input type="hidden" name="ve" value="<?= h($_SERVER['QUERY_STRING'] ?? '') ?>">
                                            <input type="hidden" name="hanh_dong" value="hoan_thanh">
                                            <button type="submit">Hoàn thành</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php hien_phan_trang($tong_trang, $trang); ?>
        </section>
<?php nv_cuoi();