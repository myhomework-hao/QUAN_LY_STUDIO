<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/cart_helper.php';
require_once '../includes/coupon_helper.php';
require_once '../includes/donhang.php';
require_once '../includes/security.php';

yeu_cau_vai_tro('khachhang');

$ma_nguoi_dung = ma_nguoi_dung_hien_tai();
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$pending = $_SESSION['pending_checkout'] ?? null;

function pending_hop_le($pending, string $token): bool
{
    if (!$pending || !is_array($pending)) return false;
    if (empty($pending['token']) || !hash_equals((string) $pending['token'], $token)) return false;
    if (empty($pending['cart_items']) || !is_array($pending['cart_items'])) return false;
    return true;
}

$loi = '';
$thanh_cong = false;

if (!pending_hop_le($pending, $token)) {
    $loi = 'Phiên thanh toán không hợp lệ hoặc đã hết hạn. Vui lòng đặt lại từ trang thanh toán.';
}

$so_tien_can_thu = $pending ? (float) ($pending['so_tien_can_thu'] ?? 0) : 0;
$la_dat_coc = $pending ? !empty($pending['la_dat_coc']) : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $loi === '' && $pending) {
    if (!csrf_hop_le()) {
        $loi = 'Phiên làm việc đã hết hạn, vui lòng thử lại.';
    } else {
        $so_tien_nhap = (float) str_replace([',', ' '], '', (string) ($_POST['so_tien'] ?? ''));
        // Đối chiếu với số tiền đã tính lúc tạo phiên thanh toán (nguồn kỳ vọng).
        if (abs($so_tien_nhap - $so_tien_can_thu) > 0.5) {
            $loi = 'Số tiền xác nhận không khớp với số tiền cần thanh toán (' . dinh_dang_tien($so_tien_can_thu) . ').';
        } else {
            $so_tien_xac_nhan = $so_tien_can_thu;
            try {
                $conn->beginTransaction();

                $tong = (float) ($pending['totals']['total'] ?? 0);
                $coupon_code = (string) ($pending['coupon_code'] ?? '');
                $discount = (float) ($pending['discount'] ?? 0);
                $ma_phuong_thuc = (string) ($pending['payment'] ?? 'tien_mat');
                $phuong_thuc_label = (string) ($pending['payment_label'] ?? 'Tiền mặt');

                // Kiểm tra lại còn chỗ tại thời điểm xác nhận (A2: lúc này mới tạo đơn)
                foreach ($pending['cart_items'] as $item) {
                    kiem_tra_con_cho(
                        $conn,
                        (int) $item['id'],
                        (string) $item['ten'],
                        (string) $item['bat_dau'],
                        (string) $item['ket_thuc'],
                        (int) $item['qty'],
                        (string) ($item['kich_co'] ?? '')
                    );
                }

                // Re-check coupon ngay trước khi tạo đơn
                if ($coupon_code !== '') {
                    $kq_recheck = kiem_tra_ma_giam_gia($conn, $coupon_code, (float) ($pending['totals']['subtotal'] ?? 0));
                    if (!$kq_recheck['ok']) {
                        throw new DomainException('Mã giảm giá không còn hiệu lực: ' . $kq_recheck['message']);
                    }
                    $discount = (float) $kq_recheck['discount'];
                    $tong = max(0, (float) ($pending['totals']['subtotal'] ?? 0) - $discount);
                    $so_tien_can_thu = $la_dat_coc ? (float) round($tong * 0.25) : $tong;
                }

                $tien_coc_ghi_nhan = $la_dat_coc ? $so_tien_can_thu : 0.0;

                $stmt = $conn->prepare(
                    "INSERT INTO dondatlich
                        (ND_Ma, DDL_TongTien, DDL_MaGiamGia, DDL_SoTienGiam, DDL_TienCoc, DDL_TrangThai,
                         DDL_NgayTao, DDL_GhiChu, DDL_TenNguoiThue, DDL_SDTNguoiThue, DDL_CCCD, DDL_DiaChi)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $ma_nguoi_dung,
                    $tong,
                    $coupon_code !== '' ? $coupon_code : null,
                    $discount,
                    $tien_coc_ghi_nhan,
                    TRANG_THAI_CHO_DUYET,
                    date('Y-m-d H:i:s'),
                    $pending['note'] ?? null,
                    (string) ($pending['ten_nguoi_thue'] ?? ''),
                    (string) ($pending['sdt_nguoi_thue'] ?? ''),
                    (string) ($pending['cccd_nguoi_thue'] ?? ''),
                    (string) ($pending['dia_chi'] ?? ''),
                ]);
                $ma_don = (int) $conn->lastInsertId();

                $stmt_ct = $conn->prepare(
                    "INSERT INTO chitietdon (DDL_Ma, DV_Ma, CTD_SoLuong, CTD_KichCo, CTD_BatDau, CTD_KetThuc, CTD_DonGia, CTD_ThanhTien)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                foreach ($pending['cart_items'] as $item) {
                    $stmt_ct->execute([
                        $ma_don,
                        (int) $item['id'],
                        (int) $item['qty'],
                        !empty($item['kich_co']) ? (string) $item['kich_co'] : null,
                        (string) $item['bat_dau'],
                        (string) $item['ket_thuc'],
                        (float) $item['gia'],
                        (float) $item['subtotal'],
                    ]);
                }

                $tt_loai = $la_dat_coc ? 'Đặt cọc' : 'Thanh toán đủ';
                $stmt_tt = $conn->prepare(
                    "INSERT INTO thanhtoan (DDL_Ma, TT_Loai, TT_SoTien, TT_PhuongThuc, TT_MaThamChieu, TT_TrangThai, TT_ThoiDiem)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt_tt->execute([
                    $ma_don,
                    $tt_loai,
                    $so_tien_xac_nhan,
                    $phuong_thuc_label,
                    'QR-' . date('ymdHis') . '-' . $ma_don,
                    'Thành công',
                    date('Y-m-d H:i:s'),
                ]);

                if ($coupon_code !== '' && $discount > 0) {
                    tang_luot_dung_ma($conn, $coupon_code);
                }

                $conn->commit();

                $_SESSION['dat_hang_ket_qua'] = [
                    'ma_don' => $ma_don,
                    'phuong_thuc' => $phuong_thuc_label . ($la_dat_coc ? ' (Đặt cọc 25%)' : ' (Thanh toán đủ)'),
                    'ten_nguoi_thue' => (string) ($pending['ten_nguoi_thue'] ?? ''),
                    'so_tien_da_thu' => $so_tien_xac_nhan,
                    'la_dat_coc' => $la_dat_coc,
                    'tong_don' => $tong,
                ];
                unset($_SESSION['pending_checkout'], $_SESSION['cart'], $_SESSION['coupon_code']);

                header('Location: thanh-toan-thanh-cong.php');
                exit;

            } catch (DomainException $e) {
                if ($conn->inTransaction()) $conn->rollBack();
                $loi = $e->getMessage();
            } catch (Exception $e) {
                if ($conn->inTransaction()) $conn->rollBack();
                $loi = 'Không thể xác nhận thanh toán, vui lòng thử lại.';
            }
        }
    }
}

$tieu_de_trang = 'Xác nhận thanh toán - VIBE Studio';
$css_rieng = [BASE_URL . 'assets/css/cart.css?v=' . (is_file(__DIR__ . '/../assets/css/cart.css') ? filemtime(__DIR__ . '/../assets/css/cart.css') : time())];
require_once '../includes/header.php';
?>
<section class="page-head" id="pageHead">
    <p class="page-label">CONFIRM</p>
    <h1>XÁC NHẬN <i>THANH TOÁN.</i></h1>
</section>

<section class="shop-area">
    <div class="checkout-layout" style="grid-template-columns:1fr;">
        <div class="card" style="max-width:640px;margin:0 auto;">
            <div class="card-head"><h2><?= $la_dat_coc ? 'Xác nhận đặt cọc (25%)' : 'Xác nhận thanh toán' ?></h2></div>
            <div class="card-body">
                <?php if ($loi !== '') : ?>
                    <div style="border:1px solid #ff4d4f;background:#fff2f2;color:#cf1322;padding:14px;border-radius:8px;margin-bottom:16px;">
                        <?= htmlspecialchars($loi) ?>
                    </div>
                    <p><a href="checkout.php" class="btn-dark" style="max-width:260px;">Quay lại thanh toán</a></p>
                <?php else : ?>
                    <p>Phương thức: <strong><?= htmlspecialchars((string) ($pending['payment_label'] ?? '')) ?></strong></p>
                    <p style="font-size:24px;font-weight:700;margin:10px 0;">Số tiền cần xác nhận: <?= dinh_dang_tien($so_tien_can_thu) ?></p>
                    <p style="color:#777;font-size:13px;margin-bottom:16px;">Giả lập: nhập đúng số tiền trên và bấm xác nhận. Hệ thống sẽ tạo đơn thuê và ghi nhận thanh toán thành công (không trừ tiền thật).</p>

                    <form method="POST">
                        <?= csrf_input() ?>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <label class="field full">Số tiền xác nhận (VND)
                            <input type="text" name="so_tien" required value="<?= htmlspecialchars((string) (int) round($so_tien_can_thu)) ?>" inputmode="numeric">
                        </label>
                        <div class="submit-row">
                            <button type="submit" class="btn-dark">XÁC NHẬN ĐÃ THANH TOÁN</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
