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

// Lấy thông tin khách hàng
$stmt = $conn->prepare("SELECT ND_Ten, ND_Email, ND_SDT FROM nguoidung WHERE ND_Ma = ?");
$stmt->execute([$ma_nguoi_dung]);
$khach = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['ND_Ten' => '', 'ND_Email' => '', 'ND_SDT' => ''];

$cart_items = lay_gio_hang($conn);
$totals     = tinh_tong_gio_hang($cart_items);

// ===== COUPON =====
$coupon_code = $_SESSION['coupon_code'] ?? '';
$coupon_info = null;
$discount    = 0.0;

if ($coupon_code !== '') {
    $kq = kiem_tra_ma_giam_gia($conn, $coupon_code, (float) $totals['subtotal']);
    if ($kq['ok']) {
        $coupon_info = $kq['coupon'];
        $discount    = (float) $kq['discount'];
    } else {
        unset($_SESSION['coupon_code']);
        $coupon_code = '';
    }
}
$totals['discount'] = $discount;
$totals['total']    = max(0, (float) $totals['total'] - $discount);
// ==================

$empty = empty($cart_items);

$cac_phuong_thuc = [
    'tien_mat' => 'Tiền mặt',
    'qr'       => 'Chuyển khoản QR giả lập',
];

$dat_hang_thanh_cong = false;
$ma_don              = 0;
$phuong_thuc_da_chon = '';
$loi_form            = '';
$ten_nguoi_thue  = trim($_POST['ten']     ?? ($khach['ND_Ten'] ?? ''));
$sdt_nguoi_thue  = trim($_POST['sdt']     ?? ($khach['ND_SDT'] ?? ''));
$cccd_nguoi_thue = trim($_POST['cccd']    ?? '');
$dia_chi         = trim($_POST['dia_chi'] ?? '');

// A2: sau khi xác nhận thanh toán/cọc thành công sẽ quay về với thông báo.
if (isset($_GET['ok'], $_SESSION['dat_hang_ket_qua']) && is_array($_SESSION['dat_hang_ket_qua'])) {
    $kq = $_SESSION['dat_hang_ket_qua'];
    if (isset($kq['ma_don'], $kq['phuong_thuc'])) {
        $dat_hang_thanh_cong = true;
        $ma_don = (int) $kq['ma_don'];
        $phuong_thuc_da_chon = (string) $kq['phuong_thuc'];
        $ten_nguoi_thue = (string) ($kq['ten_nguoi_thue'] ?? $ten_nguoi_thue);
    }
    unset($_SESSION['dat_hang_ket_qua']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$empty) {

    if (!csrf_hop_le()) {
        $loi_form = 'Phiên làm việc đã hết hạn, vui lòng tải lại trang và thử lại.';
    }

    // ===== RE-VALIDATE COUPON NGAY TRƯỚC KHI XỬ LÝ =====
    if ($loi_form === '' && $coupon_code !== '') {
        $kq_recheck = kiem_tra_ma_giam_gia($conn, $coupon_code, (float) $totals['subtotal']);
        if (!$kq_recheck['ok']) {
            $loi_form = 'Mã giảm giá không còn hiệu lực: ' . $kq_recheck['message']
                      . ' Vui lòng bỏ mã hoặc thử mã khác.';
        } else {
            $discount           = (float) $kq_recheck['discount'];
            $totals['discount'] = $discount;
            $totals['total']    = max(0, (float) $totals['subtotal'] - $discount);
        }
    }
    // ===================================================

    $ma_phuong_thuc = $_POST['payment'] ?? 'tien_mat';
    $ghi_chu        = trim($_POST['note'] ?? '');

    $cac_dong = [];

    // Chỉ validate form nếu coupon đã pass
    if ($loi_form === '') {
        if ($ten_nguoi_thue === '' || mb_strlen($ten_nguoi_thue) > 100) {
            $loi_form = 'Vui lòng nhập họ tên người thuê (tối đa 100 ký tự).';
        } elseif (!preg_match('/^0[0-9]{9,10}$/', $sdt_nguoi_thue)) {
            $loi_form = 'Số điện thoại không hợp lệ (bắt đầu bằng 0, gồm 10 hoặc 11 chữ số).';
        } elseif (!preg_match('/^(\d{9}|\d{12})$/', $cccd_nguoi_thue)) {
            $loi_form = 'Vui lòng nhập số CCCD/CMND hợp lệ (9 hoặc 12 chữ số).';
        } elseif ($dia_chi === '' || mb_strlen($dia_chi) > 255) {
            $loi_form = 'Vui lòng nhập địa chỉ (tối đa 255 ký tự).';
        } elseif (!isset($cac_phuong_thuc[$ma_phuong_thuc])) {
            $loi_form = 'Phương thức thanh toán không hợp lệ.';
        } elseif (mb_strlen($ghi_chu) > 1000) {
            $loi_form = 'Ghi chú không được dài quá 1000 ký tự.';
        } else {
            foreach ($cart_items as $item) {
                $tg = thoi_gian_thue((string) $item['don_vi'], (string) $item['bat_dau'], (string) $item['ket_thuc']);
                if ($tg === null) {
                    $loi_form = 'Vui lòng chọn lại thời gian thuê hợp lệ cho "' . $item['ten'] . '".';
                    break;
                }
                if (strtotime($tg['bat_dau']) <= time()) {
                    $loi_form = 'Thời gian thuê của "' . $item['ten'] . '" đã qua, vui lòng chọn lại.';
                    break;
                }
                if (strtotime($tg['ket_thuc']) <= time()) {
                    $loi_form = 'Thời gian thuê của "' . $item['ten'] . '" đã qua, vui lòng chọn lại.';
                    break;
                }
                $cac_dong[] = ['item' => $item, 'tg' => $tg];
            }
        }
    }

    if ($loi_form === '') {
        try {
            // Kiểm tra nhanh còn chỗ trước khi chuyển sang bước cọc/thanh toán (A2: chưa tạo đơn).
            foreach ($cac_dong as $dong) {
                kiem_tra_con_cho(
                    $conn, (int) $dong['item']['id'], $dong['item']['ten'],
                    $dong['tg']['bat_dau'], $dong['tg']['ket_thuc'], (int) $dong['item']['qty'],
                    (string) $dong['item']['kich_co']
                );
            }

            $tong_can_thu = (float) $totals['total'];
            // Tiền mặt: cọc 25%. Chuyển khoản QR giả lập: thu đủ 100% qua trang xác nhận.
            $so_tien_can_thu = ($ma_phuong_thuc === 'tien_mat')
                ? (float) round($tong_can_thu * 0.25)
                : $tong_can_thu;

            $snapshot = [];
            foreach ($cac_dong as $dong) {
                $snapshot[] = [
                    'id'       => (int) $dong['item']['id'],
                    'ten'      => (string) $dong['item']['ten'],
                    'qty'      => (int) $dong['item']['qty'],
                    'kich_co'  => (string) ($dong['item']['kich_co'] ?? ''),
                    'bat_dau'  => $dong['tg']['bat_dau'],
                    'ket_thuc' => $dong['tg']['ket_thuc'],
                    'gia'      => (float) $dong['item']['gia'],
                    'subtotal' => (float) $dong['item']['subtotal'],
                    'don_vi'   => (string) $dong['item']['don_vi'],
                    'so_don_vi'=> (int) ($dong['item']['so_don_vi'] ?? 1),
                ];
            }

            $_SESSION['pending_checkout'] = [
                'token'            => bin2hex(random_bytes(16)),
                'created_at'       => time(),
                'cart_items'       => $snapshot,
                'totals'           => $totals,
                'coupon_code'      => $coupon_code !== '' ? $coupon_code : '',
                'discount'         => $discount,
                'payment'          => $ma_phuong_thuc,
                'payment_label'    => $cac_phuong_thuc[$ma_phuong_thuc],
                'note'             => $ghi_chu !== '' ? $ghi_chu : null,
                'ten_nguoi_thue'   => $ten_nguoi_thue,
                'sdt_nguoi_thue'   => $sdt_nguoi_thue,
                'cccd_nguoi_thue'  => $cccd_nguoi_thue,
                'dia_chi'          => $dia_chi,
                'so_tien_can_thu'  => $so_tien_can_thu,
                'la_dat_coc'       => ($ma_phuong_thuc === 'tien_mat'),
            ];

            header('Location: thanh-toan.php');
            exit;

        } catch (DomainException $e) {
            $loi_form = $e->getMessage();
        } catch (Exception $e) {
            $loi_form = 'Không thể chuyển sang bước thanh toán, vui lòng thử lại.';
        }
    }
}

$tieu_de_trang = 'Thanh toán - VIBE Studio';
$css_rieng = [BASE_URL . 'assets/css/cart.css?v=' . (is_file(__DIR__ . '/../assets/css/cart.css') ? filemtime(__DIR__ . '/../assets/css/cart.css') : time())];
require_once '../includes/header.php';
?>

<section class="page-head" id="pageHead">
    <p class="page-label">CHECKOUT</p>
    <?php if ($dat_hang_thanh_cong) : ?>
        <h1>HOÀN <i>TẤT.</i></h1>
        <ol class="steps">
            <li class="done">1. GIỎ HÀNG</li>
            <li class="done">2. THANH TOÁN</li>
            <li class="current">3. HOÀN TẤT</li>
        </ol>
    <?php else : ?>
        <h1>THANH <i>TOÁN.</i></h1>
        <ol class="steps">
            <li class="done">1. GIỎ HÀNG</li>
            <li class="current">2. THANH TOÁN</li>
            <li>3. HOÀN TẤT</li>
        </ol>
    <?php endif; ?>
</section>

<section class="shop-area" id="shopArea">
    <?php if ($dat_hang_thanh_cong) : ?>
        <div class="card success" style="text-align: center; padding: 40px 20px;">
            <h1>ĐẶT THUÊ<br><i>THÀNH CÔNG.</i></h1>
            <p>Cảm ơn <?= htmlspecialchars($ten_nguoi_thue) ?>! Thanh toán/cọc đã được xác nhận (giả lập). Đơn thuê của bạn đang chờ VIBE Studio xét duyệt. Bạn có thể theo dõi trạng thái trong mục đơn hàng của tôi.</p>
            <div class="order-code" style="margin: 15px 0; font-weight: bold; font-size: 1.2rem;">MÃ ĐƠN: <?= htmlspecialchars(ma_don_hien_thi($ma_don)) ?></div>
            <p>Phương thức thanh toán: <?= htmlspecialchars($phuong_thuc_da_chon) ?></p>

            <div style="margin-top: 25px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="index.php" class="btn-dark" style="text-decoration: none; padding: 10px 20px;">VỀ TRANG CHỦ</a>
                <a href="order-detail.php?id=<?= $ma_don ?>" class="btn-dark" style="text-decoration: none; padding: 10px 20px; background-color: #333; color: #fff;">XEM ĐƠN THUÊ</a>
            </div>
        </div>
    <?php else : ?>
        <div class="checkout-layout">
            <form class="checkout-form stack" method="POST" id="checkoutForm">

                <?= csrf_input() ?>

                <?php if ($empty) : ?>
                    <div class="notice">
                        <span>Giỏ hàng của bạn đang trống nên chưa thể đặt thuê.</span>
                        <a href="services.php" class="btn-dark">XEM SẢN PHẨM</a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($loi_form)) : ?>
                    <div class="card" style="border: 1px solid #ff4d4f; background: #fff2f2; color: #cf1322; padding: 16px; margin-bottom: 20px; border-radius: 8px;" role="alert">
                        <strong><i class="fa-solid fa-triangle-exclamation"></i> Không thể hoàn tất đặt thuê:</strong>
                        <p style="margin: 5px 0 0 0;"><?= htmlspecialchars($loi_form) ?></p>
                        <p style="margin: 10px 0 0 0; font-size: 13px;">Vui lòng <a href="cart.php" style="color: #cf1322; text-decoration: underline; font-weight: bold;">quay lại giỏ hàng</a> để điều chỉnh lại số lượng hoặc thời gian cho phù hợp.</p>
                    </div>
                <?php endif; ?>

                <fieldset id="formFields" class="stack" <?= $empty ? 'disabled' : '' ?>>

                    <div class="card">
                        <div class="card-head"><h2>Thông tin người thuê</h2></div>
                        <div class="card-body">
                            <label class="field full">Họ tên người thuê
                                <input type="text" name="ten" maxlength="100" required value="<?= htmlspecialchars($ten_nguoi_thue) ?>">
                            </label>
                            <label class="field full">Số điện thoại
                                <input type="tel" name="sdt" maxlength="11" inputmode="numeric" required value="<?= htmlspecialchars($sdt_nguoi_thue) ?>" placeholder="Ví dụ: 0901234567">
                            </label>
                            <label class="field full">Số CCCD/CMND
                                <input type="text" name="cccd" maxlength="12" inputmode="numeric" required value="<?= htmlspecialchars($cccd_nguoi_thue) ?>" placeholder="Nhập 9 hoặc 12 chữ số">
                            </label>
                            <label class="field full">Địa chỉ
                                <input type="text" name="dia_chi" maxlength="255" required value="<?= htmlspecialchars($dia_chi) ?>" placeholder="Số nhà, đường, phường/xã, tỉnh/thành">
                            </label>
                            <p style="color:#777; font-size:.9rem;">Đặt giúp người khác? Hãy điền thông tin của người trực tiếp đến thuê. Đơn vẫn nằm trong tài khoản <?= htmlspecialchars($khach['ND_Email']) ?>.</p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head"><h2>Ghi chú đơn thuê</h2></div>
                        <div class="card-body">
                            <label class="field full">Ghi chú
                                <textarea name="note" rows="3" maxlength="1000" placeholder="Ví dụ: concept muốn chụp, số người tham gia…"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                            </label>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head"><h2>Phương thức thanh toán</h2></div>
                        <div class="card-body">

                            <label class="pay-option">
                                <input type="radio" name="payment" value="tien_mat" checked>
                                <div>
                                    <strong>Tiền mặt</strong>
                                    <span>Thanh toán trực tiếp tại studio khi đến nhận dịch vụ.</span>
                                </div>
                            </label>

                            <label class="pay-option">
                                <input type="radio" name="payment" value="qr">
                                <div>
                                    <strong>Chuyển khoản QR (giả lập)</strong>
                                    <span>Quét mã QR để thanh toán. Đây là bước mô phỏng, không trừ tiền thật.</span>
                                </div>
                            </label>

                        </div>
                    </div>

                </fieldset>

                <div class="submit-row">
                    <button type="submit" class="btn-dark" <?= $empty ? 'disabled' : '' ?>>ĐẶT THUÊ</button>
                </div>
            </form>

            <aside class="summary">
                <p class="panel-title">Đơn thuê của bạn</p>
                <div class="summary-items">
                    <?php if ($empty) : ?>
                        <p class="none">Chưa có sản phẩm nào.</p>
                    <?php else : ?>
                        <?php foreach ($cart_items as $item) : ?>
                            <div class="summary-row summary-item">
                                <div class="summary-item-left">
                                    <img class="summary-item-img" src="<?= htmlspecialchars(duong_dan_anh($item['anh'] ?: 'default.png')) ?>" alt="<?= htmlspecialchars($item['ten']) ?>">
                                    <div class="summary-item-info">
                                        <span class="summary-item-name"><?= htmlspecialchars($item['ten']) ?> × <?= (int) $item['qty'] ?></span>
                                        <?php if (!empty($item['kich_co'])) : ?>
                                            <small class="summary-item-size">Size: <?= htmlspecialchars($item['kich_co']) ?></small>
                                        <?php endif; ?>
                                        <small class="summary-item-time">
                                            <?= htmlspecialchars(dinh_dang_ngay_gio($item['bat_dau'])) ?>
                                            <?php if (!la_don_vi_luot((string) $item['don_vi'])) : ?>
                                                → <?= htmlspecialchars(dinh_dang_ngay_gio($item['ket_thuc'])) ?>
                                            <?php endif; ?>
                                            (<?= (int) $item['so_don_vi'] ?> <?= htmlspecialchars(nhan_don_vi((string) $item['don_vi'])) ?>)
                                        </small>
                                    </div>
                                </div>
                                <span class="summary-item-price"><?= dinh_dang_tien($item['subtotal']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($discount > 0) : ?>
                    <div class="summary-row">
                        <span>Giảm giá (<?= htmlspecialchars($coupon_code) ?>)</span>
                        <span style="color: var(--success); font-weight: 700;">−<?= dinh_dang_tien($discount) ?></span>
                    </div>
                <?php endif; ?>

                <div class="summary-row total"><span>Tổng cộng</span><span><?= dinh_dang_tien($totals['total']) ?></span></div>
                <p class="ship-note"><a href="cart.php" style="border-bottom:1px solid #888">Chỉnh sửa giỏ hàng</a></p>
            </aside>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>