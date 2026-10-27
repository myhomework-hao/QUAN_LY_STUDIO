<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/donhang.php';

yeu_cau_dang_nhap();

$ma_don = (int) ($_GET['id'] ?? 0);
$vai_tro = vai_tro_hien_tai();
$ma_nguoi_dung = ma_nguoi_dung_hien_tai();
$xem_tat_ca = in_array($vai_tro, ['admin', 'nhanvien'], true);

$thong_bao = '';
$loai_thong_bao = '';

function lay_don(PDO $conn, int $ma_don, int $ma_nguoi_dung, bool $xem_tat_ca)
{
    $sql = "SELECT d.*, nd.ND_Ten, nd.ND_Email
            FROM dondatlich d
            JOIN nguoidung nd ON nd.ND_Ma = d.ND_Ma
            WHERE d.DDL_Ma = ?";
    $tham_so = [$ma_don];
    if (!$xem_tat_ca) {
        $sql .= " AND d.ND_Ma = ?";
        $tham_so[] = $ma_nguoi_dung;
    }
    $stmt = $conn->prepare($sql);
    $stmt->execute($tham_so);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$don = lay_don($conn, $ma_don, $ma_nguoi_dung, $xem_tat_ca);
$la_chu_don = $don && (int) $don['ND_Ma'] === $ma_nguoi_dung && $vai_tro === 'khachhang';

if ($don && $la_chu_don && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $hanh_dong = $_POST['hanh_dong'] ?? '';

    if ($hanh_dong === 'huy_don') {

        $ly_do = trim($_POST['ly_do'] ?? '');

        if (mb_strlen($ly_do) > 255) {

            $thong_bao = 'Lý do hủy không được dài quá 255 ký tự.';
            $loai_thong_bao = 'error';

        } else {

            $stmt = $conn->prepare("UPDATE dondatlich
                                    SET DDL_TrangThai = ?, DDL_ThoiDiemHuy = ?, DDL_LyDoHuy = ?
                                    WHERE DDL_Ma = ? AND ND_Ma = ? AND DDL_TrangThai = ?");
            $stmt->execute([
                TRANG_THAI_DA_HUY, date('Y-m-d H:i:s'), $ly_do !== '' ? $ly_do : null,
                $ma_don, $ma_nguoi_dung, TRANG_THAI_CHO_DUYET,
            ]);

            if ($stmt->rowCount() > 0) {
                $thong_bao = 'Đã hủy đơn thuê.';
                $loai_thong_bao = 'success';
            } else {
                $thong_bao = 'Chỉ có thể hủy đơn đang chờ duyệt.';
                $loai_thong_bao = 'error';
            }
        }

    } elseif ($hanh_dong === 'gui_danh_gia') {

        $ma_dich_vu = (int) ($_POST['ma_dich_vu'] ?? 0);
        $so_sao = (int) ($_POST['so_sao'] ?? 0);
        $noi_dung = trim($_POST['noi_dung'] ?? '');

        $stmt = $conn->prepare("SELECT COUNT(*) FROM chitietdon WHERE DDL_Ma = ? AND DV_Ma = ?");
        $stmt->execute([$ma_don, $ma_dich_vu]);
        $co_trong_don = (int) $stmt->fetchColumn() > 0;

        $stmt = $conn->prepare("SELECT COUNT(*) FROM danhgia WHERE DDL_Ma = ? AND DV_Ma = ?");
        $stmt->execute([$ma_don, $ma_dich_vu]);
        $da_danh_gia = (int) $stmt->fetchColumn() > 0;

        if ($don['DDL_TrangThai'] !== TRANG_THAI_HOAN_THANH || !$co_trong_don) {

            $thong_bao = 'Chỉ đánh giá được dịch vụ thuộc đơn đã hoàn thành.';
            $loai_thong_bao = 'error';

        } elseif ($da_danh_gia) {

            $thong_bao = 'Mỗi dịch vụ trong đơn chỉ được đánh giá một lần.';
            $loai_thong_bao = 'error';

        } elseif ($so_sao < 1 || $so_sao > 5) {

            $thong_bao = 'Vui lòng chọn số sao từ 1 đến 5.';
            $loai_thong_bao = 'error';

        } else {

            $stmt = $conn->prepare("INSERT INTO danhgia (ND_Ma, DDL_Ma, DV_Ma, DG_SoSao, DG_NoiDung, DG_NgayTao)
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $ma_nguoi_dung, $ma_don, $ma_dich_vu, $so_sao,
                $noi_dung !== '' ? $noi_dung : null, date('Y-m-d H:i:s'),
            ]);

            $thong_bao = 'Cảm ơn bạn đã gửi đánh giá.';
            $loai_thong_bao = 'success';
        }
    }

    $don = lay_don($conn, $ma_don, $ma_nguoi_dung, $xem_tat_ca);
}

$cac_muc = [];
$cac_thanh_toan = [];
$cac_danh_gia = [];

if ($don) {
    $stmt = $conn->prepare("SELECT c.*, dv.DV_Ten, dv.DV_Loai, dv.DV_HinhAnh
                            FROM chitietdon c
                            JOIN dichvu dv ON dv.DV_Ma = c.DV_Ma
                            WHERE c.DDL_Ma = ?
                            ORDER BY c.CTD_Ma");
    $stmt->execute([$ma_don]);
    $cac_muc = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM thanhtoan WHERE DDL_Ma = ? ORDER BY TT_Ma");
    $stmt->execute([$ma_don]);
    $cac_thanh_toan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM danhgia WHERE DDL_Ma = ?");
    $stmt->execute([$ma_don]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $dg) {
        $cac_danh_gia[(int) $dg['DV_Ma']] = $dg;
    }
}

$cac_dich_vu_don = [];
foreach ($cac_muc as $muc) {
    $cac_dich_vu_don[(int) $muc['DV_Ma']] = $muc['DV_Ten'];
}

$tieu_de_trang = $don ? 'Chi tiết đơn ' . ma_don_hien_thi($ma_don) : 'Không tìm thấy đơn thuê';
// Trỏ chuẩn vào thư mục assets/css chung
$css_rieng = [
       BASE_URL . 'assets/css/kb.css'];
require_once '../includes/header.php';
?>

<section class="page-head" id="pageHead">
    <p class="page-label">ORDER DETAILS</p>
    <h1>CHI TIẾT <i>ĐƠN THUÊ.</i></h1>
</section>

<section class="dh-khung">
    <?php if (!$don) : ?>
        <div class="dh-trong">
            <p>Không tìm thấy đơn thuê hoặc bạn không có quyền xem đơn này.</p>
            <a href="my-orders.php" class="dh-nut">QUAY LẠI ĐƠN HÀNG CỦA TÔI</a>
        </div>
    <?php else : ?>

        <a href="my-orders.php" class="dh-quay-lai">← Quay lại danh sách đơn</a>

        <?php if ($thong_bao !== '') : ?>
            <p class="dh-thongbao <?= htmlspecialchars($loai_thong_bao) ?>" role="alert"><?= htmlspecialchars($thong_bao) ?></p>
        <?php endif; ?>

        <article class="dh-the">
            <div class="dh-the-dau">
                <div>
                    <p class="dh-ma"><?= htmlspecialchars(ma_don_hien_thi($ma_don)) ?></p>
                    <p class="dh-phu">Đặt lúc <?= htmlspecialchars(dinh_dang_ngay_gio($don['DDL_NgayTao'])) ?></p>
                </div>
                <span class="dh-nhan dh-<?= lop_trang_thai($don['DDL_TrangThai']) ?>">
                    <?= htmlspecialchars($don['DDL_TrangThai']) ?>
                </span>
            </div>

            <?php if ($don['DDL_TrangThai'] === TRANG_THAI_TU_CHOI && $don['DDL_LyDoTuChoi']) : ?>
                <p class="dh-ghi dh-ghi-loi"><b>Lý do từ chối:</b> <?= htmlspecialchars($don['DDL_LyDoTuChoi']) ?></p>
            <?php elseif ($don['DDL_TrangThai'] === TRANG_THAI_DA_HUY) : ?>
                <p class="dh-ghi dh-ghi-loi">
                    <b>Đã hủy</b> lúc <?= htmlspecialchars(dinh_dang_ngay_gio($don['DDL_ThoiDiemHuy'])) ?>
                    <?php if ($don['DDL_LyDoHuy']) : ?> - <?= htmlspecialchars($don['DDL_LyDoHuy']) ?><?php endif; ?>
                </p>
            <?php endif; ?>

            <div class="dh-thong-tin">
                <div>
                    <h3>Người thuê</h3>
                    <p><b>Họ tên:</b> <?= htmlspecialchars($don['DDL_TenNguoiThue'] ?: $don['ND_Ten']) ?></p>
                    <?php if ($don['DDL_SDTNguoiThue']) : ?><p><b>Điện thoại:</b> <?= htmlspecialchars($don['DDL_SDTNguoiThue']) ?></p><?php endif; ?>
                    <?php if ($don['DDL_DiaChi']) : ?><p><b>Địa chỉ:</b> <?= htmlspecialchars($don['DDL_DiaChi']) ?></p><?php endif; ?>
                    <p><b>Tài khoản đặt:</b> <?= htmlspecialchars($don['ND_Ten']) ?> (<?= htmlspecialchars($don['ND_Email']) ?>)</p>
                </div>
                <div>
                    <h3>Ghi chú</h3>
                    <p><?= $don['DDL_GhiChu'] ? nl2br(htmlspecialchars($don['DDL_GhiChu'])) : 'Không có ghi chú.' ?></p>
                </div>
            </div>

            <h3 class="dh-muc-tieu-de">Dịch vụ đã thuê</h3>
            <div class="dh-bang-boc">
                <table class="dh-bang">
                    <thead>
                        <tr>
                            <th>Dịch vụ</th>
                            <th>Thời gian thuê</th>
                            <th class="so">SL</th>
                            <th class="so">Đơn giá</th>
                            <th class="so">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cac_muc as $muc) : ?>
                            <tr>
                                <td>
                                    <div class="dh-dich-vu">
                                        <?php if ($muc['DV_HinhAnh']) : ?>
                                            <img src="<?= htmlspecialchars(duong_dan_anh($muc['DV_HinhAnh'])) ?>" alt="">
                                        <?php endif; ?>
                                        <div>
                                            <b><?= htmlspecialchars($muc['DV_Ten']) ?></b>
                                            <span class="dh-phu">
                                                <?= htmlspecialchars(ten_loai_dich_vu($muc['DV_Loai'])) ?>
                                                <?php if ($muc['CTD_KichCo']) : ?> - Size <?= htmlspecialchars($muc['CTD_KichCo']) ?><?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= htmlspecialchars(dinh_dang_ngay_gio($muc['CTD_BatDau'])) ?><br>
                                    <span class="dh-phu">đến <?= htmlspecialchars(dinh_dang_ngay_gio($muc['CTD_KetThuc'])) ?></span>
                                </td>
                                <td class="so"><?= (int) $muc['CTD_SoLuong'] ?></td>
                                <td class="so"><?= dinh_dang_tien($muc['CTD_DonGia']) ?></td>
                                <td class="so"><b><?= dinh_dang_tien($muc['CTD_ThanhTien']) ?></b></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="dh-tong">
                <?php if ((float) $don['DDL_TienCoc'] > 0) : ?>
                    <p>Tiền đặt cọc: <b><?= dinh_dang_tien($don['DDL_TienCoc']) ?></b></p>
                <?php endif; ?>
                <p>Tổng tiền: <strong><?= dinh_dang_tien($don['DDL_TongTien']) ?></strong></p>
            </div>

            <?php if (!empty($cac_thanh_toan)) : ?>
                <h3 class="dh-muc-tieu-de">Thanh toán</h3>
                <ul class="dh-thanh-toan">
                    <?php foreach ($cac_thanh_toan as $tt) : ?>
                        <li>
                            <span><b><?= htmlspecialchars($tt['TT_Loai']) ?></b> - <?= htmlspecialchars($tt['TT_PhuongThuc']) ?></span>
                            <span><?= dinh_dang_tien($tt['TT_SoTien']) ?> · <?= htmlspecialchars($tt['TT_TrangThai']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($la_chu_don && $don['DDL_TrangThai'] === TRANG_THAI_CHO_DUYET) : ?>
                <form method="POST" action="" class="dh-huy" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn thuê này?');">
                    <input type="hidden" name="hanh_dong" value="huy_don">
                    <label for="ly_do">Lý do hủy (không bắt buộc)</label>
                    <input type="text" id="ly_do" name="ly_do" maxlength="255">
                    <button type="submit" class="dh-nut dh-nut-vien">HỦY ĐƠN THUÊ</button>
                </form>
            <?php endif; ?>
        </article>

        <?php if ($la_chu_don && $don['DDL_TrangThai'] === TRANG_THAI_HOAN_THANH) : ?>
            <article class="dh-the">
                <h3 class="dh-muc-tieu-de dh-khong-lien">Đánh giá dịch vụ</h3>

                <?php foreach ($cac_dich_vu_don as $ma_dv => $ten_dv) : ?>
                    <div class="dh-danh-gia">
                        <p class="dh-dv-ten"><?= htmlspecialchars($ten_dv) ?></p>

                        <?php if (isset($cac_danh_gia[$ma_dv])) : $dg = $cac_danh_gia[$ma_dv]; ?>
                            <p class="dh-sao-hien" aria-label="<?= (int) $dg['DG_SoSao'] ?> sao">
                                <?= str_repeat('★', (int) $dg['DG_SoSao']) ?><span><?= str_repeat('★', 5 - (int) $dg['DG_SoSao']) ?></span>
                            </p>
                            <?php if ($dg['DG_NoiDung']) : ?>
                                <p><?= nl2br(htmlspecialchars($dg['DG_NoiDung'])) ?></p>
                            <?php endif; ?>
                            <p class="dh-phu">Đã đánh giá lúc <?= htmlspecialchars(dinh_dang_ngay_gio($dg['DG_NgayTao'])) ?></p>
                        <?php else : ?>
                            <form method="POST" action="">
                                <input type="hidden" name="hanh_dong" value="gui_danh_gia">
                                <input type="hidden" name="ma_dich_vu" value="<?= (int) $ma_dv ?>">

                                <div class="dh-sao-chon">
                                    <?php for ($i = 5; $i >= 1; $i--) : ?>
                                        <input type="radio" id="sao<?= $ma_dv ?>_<?= $i ?>" name="so_sao" value="<?= $i ?>" required>
                                        <label for="sao<?= $ma_dv ?>_<?= $i ?>" title="<?= $i ?> sao">★</label>
                                    <?php endfor; ?>
                                </div>

                                <textarea name="noi_dung" rows="3" maxlength="1000" placeholder="Chia sẻ trải nghiệm của bạn (không bắt buộc)"></textarea>
                                <button type="submit" class="dh-nut">GỬI ĐÁNH GIÁ</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </article>
        <?php endif; ?>

    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>