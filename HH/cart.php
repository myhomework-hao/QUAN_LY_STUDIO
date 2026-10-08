<?php
require_once '../config/app.php';
require_once '../config/db.php';
require_once '../includes/cart_helper.php';

// --- CHẶN TRUY CẬP KHI CHƯA ĐĂNG NHẬP ---
if (empty($_SESSION['user_id'])) {
    // Lưu thông báo lỗi vào Session để hiển thị ở trang đăng nhập (nếu cần)
    $_SESSION['error'] = 'Vui lòng đăng nhập để xem giỏ hàng và thực hiện thanh toán!';
    
    // Lưu lại trang người dùng đang muốn vào để sau khi đăng nhập có thể quay lại đúng trang đó
    $_SESSION['redirect_back'] = $_SERVER['REQUEST_URI'];

    // Chuyển hướng người dùng về trang đăng nhập (thay đổi đường dẫn theo file login của bạn)
    header('Location: ../KB/login.php'); 
    exit;
}

// Xử lý các thao tác giỏ hàng (Thêm, Tăng, Giảm, Xóa, Mã giảm giá)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $id = (int)($_POST['service_id'] ?? $_POST['id'] ?? 0);
    $qty = (int)($_POST['so_luong'] ?? $_POST['qty'] ?? 1);
    
    // Đọc chính xác ngày/giờ nhận và trả từ form
    $bat_dau = $_POST['bat_dau'] ?? $_POST['ngay_dat'] ?? '';
    $ket_thuc = $_POST['ket_thuc'] ?? $_POST['gio_dat'] ?? '';

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if ($id > 0) {
        if ($action === 'add') {
            $current_qty = 0;
            if (isset($_SESSION['cart'][$id])) {
                $current_qty = is_array($_SESSION['cart'][$id]) 
                    ? ($_SESSION['cart'][$id]['qty'] ?? 0) 
                    : (int)$_SESSION['cart'][$id];
            }

            $_SESSION['cart'][$id] = [
                'id'       => $id,
                'qty'      => min(20, max(1, $current_qty + $qty)),
                'bat_dau'  => $bat_dau,
                'ket_thuc' => $ket_thuc
            ];

            $_SESSION['thong_bao_dat'] = 'Đã thêm sản phẩm vào giỏ hàng!';
        } 
        elseif ($action === 'update') {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                if (!is_array($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id] = ['id' => $id];
                }
                $_SESSION['cart'][$id]['qty'] = min(20, $qty);
            }
        } 
        elseif ($action === 'remove') {
            unset($_SESSION['cart'][$id]);
        }
    }

    if ($action === 'clear') {
        unset($_SESSION['cart'], $_SESSION['coupon']);
    }

    // Xử lý áp dụng/bỏ mã giảm giá
    if ($action === 'apply_coupon') {
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        if ($code === 'VIBE10') {
            $_SESSION['coupon'] = $code;
            $_SESSION['coupon_msg'] = 'Đã áp dụng mã giảm giá 10%!';
        } else {
            $_SESSION['coupon_msg_error'] = 'Mã giảm giá không hợp lệ!';
        }
    } elseif ($action === 'remove_coupon') {
        unset($_SESSION['coupon']);
    }

    header('Location: cart.php');
    exit;
}

$cart_items = lay_gio_hang($conn);
$totals = tinh_tong_gio_hang($cart_items);
$total_qty = array_sum(array_column($cart_items, 'qty'));

$tieu_de_trang = 'Giỏ hàng - VIBE Studio';
$css_rieng = ['cart.css'];
require_once '../includes/header.php';
?>

<section class="page-head">
    <p class="page-label">YOUR CART</p>
    <h1>GIỎ <i>HÀNG.</i></h1>
    <ol class="steps">
        <li class="current">1. GIỎ HÀNG</li>
        <li>2. THANH TOÁN</li>
        <li>3. HOÀN TẤT</li>
    </ol>
</section>

<section class="shop-area">
    <div class="checkout-layout">

        <div class="card">
            <div class="card-head">
                <h2>Sản phẩm trong giỏ</h2>
                <span id="cartCount"><?= $total_qty ?> sản phẩm</span>
            </div>

            <div id="cartItems">
                <?php if (empty($cart_items)) : ?>
                    <div class="cart-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7V6a3 3 0 0 1 6 0v1"/></svg>
                        <h3>Giỏ hàng đang trống</h3>
                        <p>Chọn gói chụp hoặc ấn phẩm, sản phẩm sẽ xuất hiện tại đây.</p>
                        <a href="../AT/services.php" class="btn-dark">XEM SẢN PHẨM</a>
                    </div>
                <?php else : ?>
                    <?php foreach ($cart_items as $item) : ?>
                        <div class="cart-row">
                            <img src="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($item['anh'] ?: 'default.png') ?>" alt="<?= htmlspecialchars($item['ten']) ?>">
                            <div>
                                <h3><?= htmlspecialchars($item['ten']) ?></h3>
                                <p class="unit"><?= number_format($item['gia'], 0, ',', '.') ?>₫</p>
                                
                                <!-- SỬA LỖI LINE 106: Kiểm tra bat_dau và ket_thuc -->
                                <?php if (!empty($item['bat_dau'])) : ?>
                                    <p class="unit" style="font-size:12px; color:#666;">
                                        Nhận: <?= htmlspecialchars($item['bat_dau']) ?>
                                        <?= !empty($item['ket_thuc']) ? ' - Trả: ' . htmlspecialchars($item['ket_thuc']) : '' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <div class="qty">
                                        <button type="submit" name="qty" value="<?= $item['qty'] - 1 ?>" aria-label="Giảm số lượng">−</button>
                                        <span><?= $item['qty'] ?></span>
                                        <button type="submit" name="qty" value="<?= $item['qty'] + 1 ?>" aria-label="Tăng số lượng">+</button>
                                    </div>
                                </form>
                            </div>
                            <div class="cart-side">
                                <span class="line-total"><?= number_format($item['subtotal'], 0, ',', '.') ?>₫</span>
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="link-btn">XÓA</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card-foot">
                <a href="../AT/services.php" class="continue">← TIẾP TỤC MUA SẮM</a>
                <?php if (!empty($cart_items)) : ?>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="link-btn">XÓA TẤT CẢ</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <aside class="summary">
            <p class="panel-title">Tóm tắt đơn hàng</p>

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
                    if (empty($cart_items)) echo '—';
                    elseif (!$totals['need_ship']) echo 'Không cần';
                    else echo $totals['shipping'] > 0 ? number_format($totals['shipping'], 0, ',', '.') . '₫' : 'Miễn phí';
                    ?>
                </span>
            </div>

            <form class="coupon" method="POST">
                <input type="hidden" name="action" value="apply_coupon">
                <input type="text" name="coupon_code" placeholder="Mã giảm giá" aria-label="Mã giảm giá" <?= empty($cart_items) ? 'disabled' : '' ?>>
                <button type="submit" <?= empty($cart_items) ? 'disabled' : '' ?>>ÁP DỤNG</button>
            </form>
            
            <p class="coupon-msg">
                <?php
                if (!empty($_SESSION['coupon_msg'])) {
                    echo htmlspecialchars($_SESSION['coupon_msg']) . ' <form method="POST" style="display:inline"><input type="hidden" name="action" value="remove_coupon"><button type="submit" class="link-btn">Bỏ mã</button></form>';
                    unset($_SESSION['coupon_msg']);
                } elseif (!empty($_SESSION['coupon_msg_error'])) {
                    echo '<span style="color:red;">' . htmlspecialchars($_SESSION['coupon_msg_error']) . '</span>';
                    unset($_SESSION['coupon_msg_error']);
                } else {
                    echo 'Thử mã <b>VIBE10</b> để giảm 10%.';
                }
                ?>
            </p>

            <div class="summary-row total"><span>Tổng cộng</span><span><?= number_format($totals['total'], 0, ',', '.') ?>₫</span></div>

            <a href="checkout.php" class="btn-dark <?= empty($cart_items) ? 'is-disabled' : '' ?>">THANH TOÁN</a>
            <p class="ship-note">Miễn phí vận chuyển cho ấn phẩm từ <?= number_format($totals['free_ship_from'] ?? 1000000, 0, ',', '.') ?>₫. Gói chụp và voucher không tính phí ship.</p>
        </aside>

    </div>
</section>

<?php require_once '../includes/footer.php'; ?>