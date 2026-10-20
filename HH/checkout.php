<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/cart_helper.php';
require_once '../includes/donhang.php';

yeu_cau_vai_tro('khachhang');

$ma_nguoi_dung = ma_nguoi_dung_hien_tai();

$stmt = $conn->prepare("SELECT ND_Ten, ND_Email, ND_SDT FROM nguoidung WHERE ND_Ma = ?");
$stmt->execute([$ma_nguoi_dung]);
$khach = $stmt->fetch(PDO::FETCH_ASSOC);

$cart_items = lay_gio_hang($conn);
$totals = tinh_tong_gio_hang($cart_items);
$empty = empty($cart_items);

$cac_phuong_thuc = [
    'tien_mat' => 'Tiền mặt',
    'qr' => 'Chuyển khoản QR giả lập',
];

$dat_hang_thanh_cong = false;
$ma_don = 0;
$phuong_thuc_da_chon = '';
$loi_form = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$empty) {

    $ma_phuong_thuc = $_POST['payment'] ?? 'tien_mat';
    $ghi_chu = trim($_POST['note'] ?? '');

    $cac_dong = [];

    if (!isset($cac_phuong_thuc[$ma_phuong_thuc])) {
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
            if (strtotime($tg['ket_thuc']) <= time()) {
                $loi_form = 'Thời gian thuê của "' . $item['ten'] . '" đã qua, vui lòng chọn lại.';
                break;
            }

            $cac_dong[] = ['item' => $item, 'tg' => $tg];
        }
    }

    if ($loi_form === '') {
        try {
            $conn->beginTransaction();

            foreach ($cac_dong as $dong) {
                kiem_tra_con_cho(
                    $conn, (int) $dong['item']['id'], $dong['item']['ten'],
                    $dong['tg']['bat_dau'], $dong['tg']['ket_thuc'], (int) $dong['item']['qty']
                );
            }

            $stmt = $conn->prepare(
                "INSERT INTO dondatlich (ND_Ma, DDL_TongTien, DDL_TienCoc, DDL_TrangThai, DDL_NgayTao, DDL_GhiChu)
                 VALUES (?, ?, 0, ?, ?, ?)"
            );
            $stmt->execute([
                $ma_nguoi_dung, $totals['total'], TRANG_THAI_CHO_DUYET,
                date('Y-m-d H:i:s'), $ghi_chu !== '' ? $ghi_chu : null,
            ]);
            $ma_don = (int) $conn->lastInsertId();

            $stmt_ct = $conn->prepare(
                "INSERT INTO chitietdon (DDL_Ma, DV_Ma, CTD_SoLuong, CTD_KichCo, CTD_BatDau, CTD_KetThuc, CTD_DonGia, CTD_ThanhTien)
                 VALUES (?, ?, ?, NULL, ?, ?, ?, ?)"
            );
            foreach ($cac_dong as $dong) {
                $stmt_ct->execute([
                    $ma_don, $dong['item']['id'], $dong['item']['qty'],
                    $dong['tg']['bat_dau'], $dong['tg']['ket_thuc'],
                    $dong['item']['gia'], $dong['item']['subtotal'],
                ]);
            }

            $da_thanh_toan = $ma_phuong_thuc === 'qr';
            $stmt_tt = $conn->prepare(
                "INSERT INTO thanhtoan (DDL_Ma, TT_Loai, TT_SoTien, TT_PhuongThuc, TT_MaThamChieu, TT_TrangThai, TT_ThoiDiem)
                 VALUES (?, 'Thanh toán đủ', ?, ?, ?, ?, ?)"
            );
            $stmt_tt->execute([
                $ma_don, $totals['total'], $cac_phuong_thuc[$ma_phuong_thuc],
                $da_thanh_toan ? 'QR-' . date('ymdHis') . '-' . $ma_don : null,
                $da_thanh_toan ? 'Thành công' : 'Chờ',
                date('Y-m-d H:i:s'),
            ]);

            $conn->commit();

            unset($_SESSION['cart'], $_SESSION['coupon']);

            $dat_hang_thanh_cong = true;
            $phuong_thuc_da_chon = $cac_phuong_thuc[$ma_phuong_thuc];

        } catch (DomainException $e) {
            $conn->rollBack();
            $loi_form = $e->getMessage();
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $loi_form = 'Không thể lưu đơn thuê, vui lòng thử lại.';
        }
    }
}

$tieu_de_trang = 'Thanh toán - VIBE Studio';
$css_rieng = ['cart.css'];
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
            <p>Cảm ơn <?= htmlspecialchars($khach['ND_Ten']) ?>! Đơn thuê của bạn đang chờ VIBE Studio xét duyệt. Bạn có thể theo dõi trạng thái trong mục đơn hàng của tôi.</p>
            <div class="order-code" style="margin: 15px 0; font-weight: bold; font-size: 1.2rem;">MÃ ĐƠN: <?= htmlspecialchars(ma_don_hien_thi($ma_don)) ?></div>
            <p>Phương thức thanh toán: <?= htmlspecialchars($phuong_thuc_da_chon) ?></p>

            <div style="margin-top: 25px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="../TP/index.php" class="btn-dark" style="text-decoration: none; padding: 10px 20px;">VỀ TRANG CHỦ</a>
                <a href="../KB/order-detail.php?id=<?= $ma_don ?>" class="btn-dark" style="text-decoration: none; padding: 10px 20px; background-color: #333; color: #fff;">XEM ĐƠN THUÊ</a>
            </div>
        </div>
    <?php else : ?>
        <div class="checkout-layout">
            <form class="checkout-form stack" method="POST" id="checkoutForm">

                <?php if ($empty) : ?>
                    <div class="notice">
                        <span>Giỏ hàng của bạn đang trống nên chưa thể đặt thuê.</span>
                        <a href="../AT/services.php" class="btn-dark">XEM SẢN PHẨM</a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($loi_form)) : ?>
                    <div class="card" style="border: 1px solid red; background: #fff2f2; color: red; padding: 15px;" role="alert">
                        <strong>Thông báo lỗi:</strong> <?= htmlspecialchars($loi_form) ?>
                    </div>
                <?php endif; ?>

                <fieldset id="formFields" class="stack" <?= $empty ? 'disabled' : '' ?>>

                    <div class="card">
                        <div class="card-head"><h2>Thông tin người đặt</h2></div>
                        <div class="card-body">
                            <p><b>Họ tên:</b> <?= htmlspecialchars($khach['ND_Ten']) ?></p>
                            <p><b>Email:</b> <?= htmlspecialchars($khach['ND_Email']) ?></p>
                            <p style="color:#777; font-size:.9rem;">Thông tin lấy từ tài khoản của bạn. Có thể sửa tên trong mục hồ sơ.</p>
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
                            <div class="summary-row">
                                <span>
                                    <?= htmlspecialchars($item['ten']) ?> × <?= (int) $item['qty'] ?><br>
                                    <small style="color:#777;">
                                        <?= htmlspecialchars(dinh_dang_ngay_gio($item['bat_dau'])) ?>
                                        → <?= htmlspecialchars(dinh_dang_ngay_gio($item['ket_thuc'])) ?>
                                        (<?= (int) $item['so_don_vi'] ?> <?= ['gio' => 'giờ', 'ngay' => 'ngày', 'bo' => 'lần'][$item['don_vi']] ?? '' ?>)
                                    </small>
                                </span>
                                <span><?= dinh_dang_tien($item['subtotal']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="summary-row total"><span>Tổng cộng</span><span><?= dinh_dang_tien($totals['total']) ?></span></div>
                <p class="ship-note"><a href="cart.php" style="border-bottom:1px solid #888">Chỉnh sửa giỏ hàng</a></p>
            </aside>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>