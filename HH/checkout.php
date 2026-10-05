<?php
require_once '../config/app.php';
require_once '../config/db.php';
require_once '../includes/cart_helper.php';

$cart_items = lay_gio_hang($conn);
$totals = tinh_tong_gio_hang($cart_items);
$empty = empty($cart_items);

$dat_hang_thanh_cong = false;
$ma_don_hang = '';
$thong_tin_don = [];
$loi_form = '';

// Xử lý Đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$empty) {
    $ho_ten = trim($_POST['name'] ?? '');
    $sdt = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dia_chi = trim($_POST['address'] ?? '');
    $phuong_xa = trim($_POST['ward'] ?? '');
    $quan_huyen = trim($_POST['district'] ?? '');
    $tinh_tp = trim($_POST['city'] ?? '');
    $ghi_chu = trim($_POST['note'] ?? '');
    $phuong_thuc = $_POST['payment'] ?? 'COD';

    if (!$ho_ten || !$sdt || !$email) {
        $loi_form = 'Vui lòng điền đầy đủ các thông tin bắt buộc (Họ tên, SĐT, Email).';
    } elseif ($totals['need_ship'] && (!$dia_chi || !$phuong_xa || !$quan_huyen)) {
        $loi_form = 'Vui lòng nhập đầy đủ địa chỉ giao hàng.';
    } else {
        try {
            $conn->beginTransaction();

            $ma_don_hang = 'VB' . substr(time(), -7);
            $dia_chi_giao = $totals['need_ship'] ? trim("$dia_chi, $phuong_xa, $quan_huyen, $tinh_tp", ', ') : 'Nhận tại Studio/Digital';
            $user_id = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : NULL;

            // 1. Tạo đơn hàng chính vào bảng orders
            $col_total = 'total_price';
            try {
                $conn->query("SELECT price_total FROM orders LIMIT 1");
                $col_total = 'price_total';
            } catch (Exception $chk_ex) {
                // Mặc định là total_price
            }

            $sql_order = "
                INSERT INTO orders (code, user_id, customer_name, customer_phone, customer_email, shipping_address, payment_method, note, {$col_total}, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'cho_xuly', NOW())
            ";

            $stmt = $conn->prepare($sql_order);
            $stmt->execute([
                $ma_don_hang, 
                $user_id, 
                $ho_ten, 
                $sdt, 
                $email, 
                $dia_chi_giao, 
                $phuong_thuc, 
                $ghi_chu, 
                $totals['total']
            ]);
            $order_id = $conn->lastInsertId();

            // 2. Thêm từng chi tiết sản phẩm vào bảng order_items
            $stmt_item = $conn->prepare("
                INSERT INTO order_items (order_id, service_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($cart_items as $item) {
                $stmt_item->execute([$order_id, $item['id'], $item['qty'], $item['gia']]);
            }

            $conn->commit();

            // Xóa giỏ hàng sau khi đặt thành công & lưu SĐT tạm thời
            unset($_SESSION['cart'], $_SESSION['coupon']);
            $_SESSION['last_phone'] = $sdt;
            
            $dat_hang_thanh_cong = true;
            $thong_tin_don = [
                'ho_ten' => $ho_ten,
                'sdt' => $sdt,
                'phuong_thuc' => $phuong_thuc
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            $loi_form = 'Lỗi lưu đơn hàng: ' . $e->getMessage();
        }
    }
}

$tieu_de_trang = 'Thanh toán - VIBE Studio';
$css_rieng = ['cart.css'];
require_once '../includes/header.php';
?>

<section class="page-head" id="pageHead">
    <?php if ($dat_hang_thanh_cong) : ?>
        <p class="page-label">CHECKOUT</p>
        <h1>HOÀN <i>TẤT.</i></h1>
        <ol class="steps">
            <li class="done">1. GIỎ HÀNG</li>
            <li class="done">2. THANH TOÁN</li>
            <li class="current">3. HOÀN TẤT</li>
        </ol>
    <?php else : ?>
        <p class="page-label">CHECKOUT</p>
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
            <h1>ĐẶT HÀNG<br><i>THÀNH CÔNG.</i></h1>
            <p>Cảm ơn <?= htmlspecialchars($thong_tin_don['ho_ten']) ?>! VIBE Studio sẽ liên hệ qua số <?= htmlspecialchars($thong_tin_don['sdt']) ?> để xác nhận đơn trong vòng 24 giờ.</p>
            <div class="order-code" style="margin: 15px 0; font-weight: bold; font-size: 1.2rem;">MÃ ĐƠN: <?= htmlspecialchars($ma_don_hang) ?></div>
            <p>Phương thức thanh toán: <?= htmlspecialchars($thong_tin_don['phuong_thuc']) ?></p>
            
            <div style="margin-top: 25px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="../TP/index.php" class="btn-dark" style="text-decoration: none; padding: 10px 20px;">VỀ TRANG CHỦ</a>
                <a href="my-orders.php" class="btn-dark" style="text-decoration: none; padding: 10px 20px; background-color: #333; color: #fff;">XEM ĐƠN HÀNG CỦA TÔI</a>
            </div>
        </div>
    <?php else : ?>
        <div class="checkout-layout">
            <form class="checkout-form stack" method="POST" id="checkoutForm">

                <?php if ($empty) : ?>
                    <div class="notice">
                        <span>Giỏ hàng của bạn đang trống nên chưa thể đặt hàng.</span>
                        <a href="../AT/services.php" class="btn-dark">XEM SẢN PHẨM</a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($loi_form)) : ?>
                    <div class="card" style="border: 1px solid red; background: #fff2f2; color: red; padding: 15px;">
                        <strong>Thông báo lỗi:</strong> <?= htmlspecialchars($loi_form) ?>
                    </div>
                <?php endif; ?>

                <fieldset id="formFields" class="stack" <?= $empty ? 'disabled' : '' ?>>

                    <div class="card">
                        <div class="card-head"><h2>Thông tin liên hệ</h2></div>
                        <div class="card-body">
                            <div class="field-grid">
                                <label class="field full">Họ tên
                                    <input type="text" name="name" autocomplete="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                </label>
                                <label class="field">Số điện thoại
                                    <input type="tel" name="phone" autocomplete="tel" pattern="0[0-9]{9}" title="Nhập 10 số, bắt đầu bằng 0" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                </label>
                                <label class="field">Email
                                    <input type="email" name="email" autocomplete="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head"><h2><?= $totals['need_ship'] ? 'Địa chỉ nhận hàng' : 'Ghi chú đơn hàng' ?></h2></div>
                        <div class="card-body">
                            <div class="field-grid">
                                <?php if ($totals['need_ship']) : ?>
                                    <label class="field full">Địa chỉ
                                        <input type="text" name="address" autocomplete="street-address" placeholder="Số nhà, tên đường" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                                    </label>
                                    <label class="field">Phường / Xã
                                        <input type="text" name="ward" required value="<?= htmlspecialchars($_POST['ward'] ?? '') ?>">
                                    </label>
                                    <label class="field">Quận / Huyện
                                        <input type="text" name="district" required value="<?= htmlspecialchars($_POST['district'] ?? '') ?>">
                                    </label>
                                    <label class="field full">Tỉnh / Thành phố
                                        <select name="city">
                                            <option value="Cần Thơ">Cần Thơ</option>
                                            <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
                                            <option value="Hà Nội">Hà Nội</option>
                                            <option value="Đà Nẵng">Đà Nẵng</option>
                                            <option value="Khác">Tỉnh / thành khác</option>
                                        </select>
                                    </label>
                                <?php endif; ?>
                                <label class="field full">Ghi chú
                                    <textarea name="note" rows="3" placeholder="Ví dụ: ngày giờ muốn chụp, giao ngoài giờ hành chính…"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head"><h2>Phương thức thanh toán</h2></div>
                        <div class="card-body">

                            <label class="pay-option">
                                <input type="radio" name="payment" value="Thanh toán khi nhận hàng (COD)" checked>
                                <div>
                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                    <span>Trả tiền mặt cho nhân viên giao hàng, hoặc trả tại studio khi đến chụp.</span>
                                </div>
                            </label>

                            <label class="pay-option">
                                <input type="radio" name="payment" value="Chuyển khoản ngân hàng">
                                <div>
                                    <strong>Chuyển khoản ngân hàng</strong>
                                    <span>Chuyển khoản trước, studio xác nhận đơn trong giờ làm việc.</span>
                                    <div class="bank-box">
                                        Ngân hàng: <b>Vietcombank</b><br>
                                        Số tài khoản: <b>0123 456 789</b><br>
                                        Chủ tài khoản: <b>VIBE STUDIO</b><br>
                                        Nội dung: <b>Mã đơn hàng + số điện thoại</b>
                                    </div>
                                </div>
                            </label>

                            <label class="pay-option">
                                <input type="radio" name="payment" value="Ví điện tử (MoMo / ZaloPay)">
                                <div>
                                    <strong>Ví điện tử (MoMo / ZaloPay)</strong>
                                    <span>Studio sẽ gửi mã QR qua Zalo hoặc email sau khi nhận đơn.</span>
                                </div>
                            </label>

                        </div>
                    </div>

                </fieldset>

                <div class="submit-row">
                    <button type="submit" class="btn-dark" <?= $empty ? 'disabled' : '' ?>>ĐẶT HÀNG</button>
                    <p class="form-error" role="alert"><?= htmlspecialchars($loi_form) ?></p>
                </div>
            </form>

            <aside class="summary">
                <p class="panel-title">Đơn hàng của bạn</p>
                <div class="summary-items">
                    <?php if ($empty) : ?>
                        <p class="none">Chưa có sản phẩm nào.</p>
                    <?php else : ?>
                        <?php foreach ($cart_items as $item) : ?>
                            <div class="summary-row">
                                <span><?= htmlspecialchars($item['ten']) ?> × <?= $item['qty'] ?></span>
                                <span><?= number_format($item['subtotal'] ?? ($item['gia'] * $item['qty']), 0, ',', '.') ?>₫</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="summary-row"><span>Tạm tính</span><span><?= number_format($totals['subtotal'], 0, ',', '.') ?>₫</span></div>
                <?php if ($totals['discount'] > 0) : ?>
                    <div class="summary-row">
                        <span>Giảm giá (<?= htmlspecialchars($totals['code']) ?>)</span>
                        <span>−<?= number_format($totals['discount'], 0, ',', '.') ?>₫</span>
                    </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Vận chuyển</span>
                    <span>
                        <?php 
                        if ($empty) echo '—';
                        elseif (!$totals['need_ship']) echo 'Không cần';
                        else echo $totals['shipping'] > 0 ? number_format($totals['shipping'], 0, ',', '.') . '₫' : 'Miễn phí';
                        ?>
                    </span>
                </div>
                <div class="summary-row total"><span>Tổng cộng</span><span><?= number_format($totals['total'], 0, ',', '.') ?>₫</span></div>
                <p class="ship-note"><a href="cart.php" style="border-bottom:1px solid #888">Chỉnh sửa giỏ hàng</a></p>
            </aside>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>