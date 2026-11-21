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
$duong_dan_quay_lai = 'checkout.php';
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
        // Thông tin nhận hoàn tiền đã nhập ở bước checkout (dùng lại 3 cột hoàn tiền của bảng dondatlich).
        $ds_ngan_hang   = danh_sach_ngan_hang();
        $ngan_hang_nhan = (string) ($pending['ngan_hang'] ?? '');
        $so_tk_nhan     = (string) ($pending['so_tk'] ?? '');
        $chu_tk_nhan    = (string) ($pending['chu_tk'] ?? '');
        // Đối chiếu với số tiền đã tính lúc tạo phiên thanh toán (nguồn kỳ vọng).
        if (!isset($ds_ngan_hang[$ngan_hang_nhan])
            || !preg_match('/^[0-9A-Za-z]{4,30}$/', $so_tk_nhan)
            || $chu_tk_nhan === '' || mb_strlen($chu_tk_nhan) > 100) {
            $loi = 'Thông tin ngân hàng nhận hoàn tiền không hợp lệ. Vui lòng quay lại trang thanh toán và nhập lại.';
        } elseif (abs($so_tien_nhap - $so_tien_can_thu) > 0.5) {
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

                // Tính lại combo từ giỏ đã chốt; mã giảm giá và 5% online tính trên số còn lại.
                $combo_tinh = tinh_giam_combo($pending['cart_items']);
                $sau_combo  = max(0, (float) ($pending['totals']['subtotal'] ?? 0) - (float) $combo_tinh['tong_giam']);
                $tong       = max(0, $sau_combo - $discount);

                // Kiểm tra lại còn chỗ tại thời điểm xác nhận (A2: lúc này mới tạo đơn)
                foreach ($pending['cart_items'] as $vi_tri => $item) {
                    kiem_tra_con_cho(
                        $conn,
                        (int) $item['id'],
                        (string) $item['ten'],
                        (string) $item['bat_dau'],
                        (string) $item['ket_thuc'],
                        (int) $item['qty'],
                        (string) ($item['kich_co'] ?? '')
                    );

                    $bo_phan_chon = $item['bo_phan'] ?? [];
                    if (!is_array($bo_phan_chon)) {
                        throw new DomainException('Danh sách món trong phòng không hợp lệ. Vui lòng quay lại giỏ hàng.');
                    }

                    $stmt_dv = $conn->prepare("SELECT DV_Loai, DV_MoTaChiTiet FROM dichvu WHERE DV_Ma = ?");
                    $stmt_dv->execute([(int) $item['id']]);
                    $thong_tin_dv = $stmt_dv->fetch(PDO::FETCH_ASSOC);
                    if (!$thong_tin_dv) {
                        throw new DomainException('Một dịch vụ trong đơn không còn tồn tại. Vui lòng quay lại giỏ hàng.');
                    }

                    if ($thong_tin_dv['DV_Loai'] === 'phong_chup') {
                        $ds_bo_phan = preg_split('/\R/u', (string) $thong_tin_dv['DV_MoTaChiTiet'], -1, PREG_SPLIT_NO_EMPTY);
                        $ds_bo_phan = array_values(array_unique(array_filter(array_map('trim', $ds_bo_phan))));
                        $bo_phan_chon_chuan = [];
                        foreach ($bo_phan_chon as $ten_bo_phan) {
                            if (!is_string($ten_bo_phan)) {
                                throw new DomainException('Danh sách món trong phòng không hợp lệ.');
                            }
                            if (trim($ten_bo_phan) !== '') {
                                $bo_phan_chon_chuan[] = trim($ten_bo_phan);
                            }
                        }
                        $bo_phan_chon = array_values(array_unique($bo_phan_chon_chuan));

                        $stmt_hu = $conn->prepare(
                            "SELECT BCHH_BoPhan FROM baocao_huhong
                             WHERE DV_Ma = ? AND BCHH_TrangThai <> ? AND BCHH_BoPhan IS NOT NULL"
                        );
                        $stmt_hu->execute([(int) $item['id'], 'Đã xử lý']);
                        $ds_bo_phan_hu = array_values(array_unique(array_filter(array_map('trim', $stmt_hu->fetchAll(PDO::FETCH_COLUMN)))));

                        if (!$ds_bo_phan || !$bo_phan_chon) {
                            throw new DomainException('Vui lòng quay lại giỏ hàng và chọn món trong phòng.');
                        }
                        if (count(array_diff($bo_phan_chon, $ds_bo_phan)) > 0) {
                            throw new DomainException('Danh sách món trong phòng đã thay đổi. Vui lòng quay lại giỏ hàng.');
                        }
                        if (count(array_intersect($bo_phan_chon, $ds_bo_phan_hu)) > 0) {
                            throw new DomainException('Có món trong phòng đang bị báo hư. Vui lòng quay lại và chọn món khác.');
                        }
                        $pending['cart_items'][$vi_tri]['bo_phan'] = $bo_phan_chon;
                    } elseif ($bo_phan_chon) {
                        throw new DomainException('Thông tin món trong giỏ hàng không hợp lệ. Vui lòng quay lại giỏ hàng.');
                    }
                }

                // Re-check coupon ngay trước khi tạo đơn
                if ($coupon_code !== '') {
                    $kq_recheck = kiem_tra_ma_giam_gia($conn, $coupon_code, $sau_combo);
                    if (!$kq_recheck['ok']) {
                        throw new DomainException('Mã giảm giá không còn hiệu lực: ' . $kq_recheck['message']);
                    }
                    $discount = (float) $kq_recheck['discount'];
                    $tong = max(0, $sau_combo - $discount);
                }

                // Thanh toán 100% online: giảm thêm 5% trên số tiền sau mã giảm giá.
                // Tiền mặt: đặt cọc 25% nên không có ưu đãi này, tổng đơn giữ nguyên.
                $giam_online = 0.0;
                if ($la_dat_coc) {
                    $so_tien_can_thu = (float) round($tong * 0.25);
                    $tong_ghi_nhan   = $tong;
                } else {
                    $giam_online     = giam_thanh_toan_online($tong);
                    $so_tien_can_thu = $tong - $giam_online;
                    $tong_ghi_nhan   = $so_tien_can_thu;
                }

                $tien_coc_ghi_nhan = $la_dat_coc ? $so_tien_can_thu : 0.0;

                $stmt = $conn->prepare(
                    "INSERT INTO dondatlich
                        (ND_Ma, DDL_TongTien, DDL_MaGiamGia, DDL_SoTienGiam, DDL_TienCoc, DDL_TrangThai,
                         DDL_NgayTao, DDL_GhiChu, DDL_TenNguoiThue, DDL_SDTNguoiThue, DDL_CCCD, DDL_DiaChi,
                         DDL_NganHangHoan, DDL_SoTKHoan, DDL_ChuTKHoan)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $ma_nguoi_dung,
                    $tong_ghi_nhan,
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
                    $ngan_hang_nhan,
                    $so_tk_nhan,
                    $chu_tk_nhan,
                ]);
                $ma_don = (int) $conn->lastInsertId();

                $stmt_ct = $conn->prepare(
                    "INSERT INTO chitietdon (DDL_Ma, DV_Ma, CTD_SoLuong, CTD_KichCo, CTD_BatDau, CTD_KetThuc, CTD_DonGia, CTD_ThanhTien)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt_ct_bo_phan = $conn->prepare(
                    "INSERT INTO chitietdon_bophan (CTD_Ma, CTDBP_Ten) VALUES (?, ?)"
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
                    $ma_chi_tiet_don = (int) $conn->lastInsertId();
                    foreach (($item['bo_phan'] ?? []) as $bo_phan) {
                        $stmt_ct_bo_phan->execute([$ma_chi_tiet_don, $bo_phan]);
                    }
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
                    'tong_don' => $tong_ghi_nhan,
                ];
                unset($_SESSION['pending_checkout'], $_SESSION['cart'], $_SESSION['coupon_code']);

                header('Location: thanh-toan-thanh-cong.php');
                exit;

            } catch (DomainException $e) {
                if ($conn->inTransaction()) $conn->rollBack();
                $loi = $e->getMessage();
                $duong_dan_quay_lai = 'cart.php';
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
                    <p><a href="<?= htmlspecialchars($duong_dan_quay_lai, ENT_QUOTES, 'UTF-8') ?>" class="btn-dark" style="max-width:260px;"><?= $duong_dan_quay_lai === 'cart.php' ? 'Quay lại giỏ hàng' : 'Quay lại thanh toán' ?></a></p>
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
