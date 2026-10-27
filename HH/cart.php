<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/cart_helper.php';
require_once '../includes/donhang.php';

yeu_cau_vai_tro('khachhang');

const SO_LUONG_TOI_DA_MOI_DICH_VU = 20;

function ten_don_vi_gio_hang(string $don_vi): string
{
    return ['gio' => 'giờ', 'ngay' => 'ngày', 'bo' => 'bộ'][$don_vi] ?? $don_vi;
}

function so_luong_toi_da(PDO $conn, int $ma_dich_vu): int
{
    $stmt = $conn->prepare("SELECT DV_SoLuong FROM dichvu WHERE DV_Ma = ? AND DV_DangBan = 1");
    $stmt->execute([$ma_dich_vu]);
    $so = $stmt->fetchColumn();
    return $so === false ? 0 : min(SO_LUONG_TOI_DA_MOI_DICH_VU, (int) $so);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanh_dong = $_POST['action'] ?? 'add';
    $ma = (int) ($_POST['service_id'] ?? $_POST['id'] ?? 0);
    $so_luong = (int) ($_POST['so_luong'] ?? $_POST['qty'] ?? 1);

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if ($hanh_dong === 'clear') {
        unset($_SESSION['cart']);
    } elseif ($ma > 0) {
        if ($hanh_dong === 'add') {
            $toi_da = so_luong_toi_da($conn, $ma);
            $dv = null;
            if ($toi_da > 0) {
                $stmt = $conn->prepare("SELECT DV_DonViTinh FROM dichvu WHERE DV_Ma = ?");
                $stmt->execute([$ma]);
                $dv = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            $bat_dau = (string) ($_POST['bat_dau'] ?? $_POST['ngay_dat'] ?? '');
            $ket_thuc = (string) ($_POST['ket_thuc'] ?? $_POST['gio_dat'] ?? '');
            $tg = $dv ? thoi_gian_thue((string) $dv['DV_DonViTinh'], $bat_dau, $ket_thuc) : null;

            if ($toi_da === 0) {
                $_SESSION['thong_bao_dat'] = 'Dịch vụ này hiện không còn nhận thuê.';
            } elseif ($tg === null) {
                $_SESSION['thong_bao_dat'] = 'Vui lòng chọn thời gian nhận và trả hợp lệ.';
            } elseif (strtotime($tg['ket_thuc']) <= time()) {
                $_SESSION['thong_bao_dat'] = 'Thời gian thuê đã qua, vui lòng chọn lại.';
            } else {
                $da_co = is_array($_SESSION['cart'][$ma] ?? null) ? (int) ($_SESSION['cart'][$ma]['qty'] ?? 0) : 0;
                $_SESSION['cart'][$ma] = [
                    'id' => $ma,
                    'qty' => min($toi_da, max(1, $da_co + $so_luong)),
                    'bat_dau' => $tg['bat_dau'],
                    'ket_thuc' => $tg['ket_thuc'],
                ];
                $_SESSION['thong_bao_dat'] = 'Đã thêm dịch vụ vào giỏ hàng.';
            }
        } elseif ($hanh_dong === 'update' && isset($_SESSION['cart'][$ma]) && is_array($_SESSION['cart'][$ma])) {
            if ($so_luong <= 0) {
                unset($_SESSION['cart'][$ma]);
            } else {
                $_SESSION['cart'][$ma]['qty'] = min(max(1, so_luong_toi_da($conn, $ma)), $so_luong);
            }
        } elseif ($hanh_dong === 'remove') {
            unset($_SESSION['cart'][$ma]);
        }
    }

    header('Location: cart.php');
    exit;
}

$thong_bao = $_SESSION['thong_bao_dat'] ?? '';
unset($_SESSION['thong_bao_dat']);

$cart_items = lay_gio_hang($conn);
$totals = tinh_tong_gio_hang($cart_items);
$tong_so_luong = array_sum(array_column($cart_items, 'qty'));

$co_loi = false;
foreach ($cart_items as $k => $item) {
    $tg = thoi_gian_thue((string) $item['don_vi'], (string) $item['bat_dau'], (string) $item['ket_thuc']);
    $cart_items[$k]['tg'] = $tg;
    $cart_items[$k]['canh_bao'] = '';

    if ($tg === null || strtotime($tg['ket_thuc']) <= time()) {
        $cart_items[$k]['canh_bao'] = 'Thời gian thuê không hợp lệ hoặc đã qua, vui lòng xóa và chọn lại.';
    } else {
        try {
            kiem_tra_con_cho($conn, (int) $item['id'], $item['ten'], $tg['bat_dau'], $tg['ket_thuc'], (int) $item['qty']);
        } catch (DomainException $e) {
            $cart_items[$k]['canh_bao'] = $e->getMessage();
        }
    }
    if ($cart_items[$k]['canh_bao'] !== '') {
        $co_loi = true;
    }
}

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
    <?php if ($thong_bao !== '') : ?>
        <div class="notice" role="status"><span><?= htmlspecialchars($thong_bao) ?></span></div>
    <?php endif; ?>

    <div class="checkout-layout">

        <div class="card">
            <div class="card-head">
                <h2>Dịch vụ trong giỏ</h2>
                <span id="cartCount"><?= (int) $tong_so_luong ?> mục</span>
            </div>

            <div id="cartItems">
                <?php if (empty($cart_items)) : ?>
                    <div class="cart-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7V6a3 3 0 0 1 6 0v1"/></svg>
                        <h3>Giỏ hàng đang trống</h3>
                        <p>Chọn phòng chụp, thiết bị hoặc trang phục, dịch vụ sẽ xuất hiện tại đây.</p>
                        <a href="../AT/services.php" class="btn-dark">XEM DỊCH VỤ</a>
                    </div>
                <?php else : ?>
                    <?php foreach ($cart_items as $item) : $toi_da = max(1, min(SO_LUONG_TOI_DA_MOI_DICH_VU, (int) $item['so_luong'])); ?>
                        <div class="cart-row">
                            <img src="<?= htmlspecialchars(duong_dan_anh($item['anh'] ?: 'default.png')) ?>" alt="<?= htmlspecialchars($item['ten']) ?>">
                            <div>
                                <h3><?= htmlspecialchars($item['ten']) ?></h3>
                                <p class="unit"><?= dinh_dang_tien($item['gia']) ?> / <?= htmlspecialchars(ten_don_vi_gio_hang((string) $item['don_vi'])) ?></p>

                                <?php if ($item['tg']) : ?>
                                    <p class="unit" style="font-size:12px; color:#666;">
                                        <?= htmlspecialchars(dinh_dang_ngay_gio($item['tg']['bat_dau'])) ?>
                                        → <?= htmlspecialchars(dinh_dang_ngay_gio($item['tg']['ket_thuc'])) ?>
                                        (<?= (int) $item['so_don_vi'] ?> <?= htmlspecialchars(ten_don_vi_gio_hang((string) $item['don_vi'])) ?>)
                                    </p>
                                <?php endif; ?>

                                <?php if ($item['canh_bao'] !== '') : ?>
                                    <p class="unit" style="font-size:12px; color:#c00;" role="alert"><?= htmlspecialchars($item['canh_bao']) ?></p>
                                <?php endif; ?>

                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <div class="qty">
                                        <button type="submit" name="qty" value="<?= (int) $item['qty'] - 1 ?>" aria-label="Giảm số lượng">−</button>
                                        <span><?= (int) $item['qty'] ?></span>
                                        <button type="submit" name="qty" value="<?= min($toi_da, (int) $item['qty'] + 1) ?>" aria-label="Tăng số lượng" <?= (int) $item['qty'] >= $toi_da ? 'disabled' : '' ?>>+</button>
                                    </div>
                                </form>
                            </div>
                            <div class="cart-side">
                                <span class="line-total"><?= dinh_dang_tien($item['subtotal']) ?></span>
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" class="link-btn">XÓA</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card-foot">
                <a href="../AT/services.php" class="continue">← TIẾP TỤC CHỌN DỊCH VỤ</a>
                <?php if (!empty($cart_items)) : ?>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="link-btn">XÓA TẤT CẢ</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <aside class="summary">
            <p class="panel-title">Tóm tắt đơn thuê</p>

            <div class="summary-row"><span>Tạm tính</span><span><?= dinh_dang_tien($totals['subtotal']) ?></span></div>
            <div class="summary-row total"><span>Tổng cộng</span><span><?= dinh_dang_tien($totals['total']) ?></span></div>

            <?php if ($co_loi) : ?>
                <p class="ship-note" style="color:#c00;">Có dịch vụ cần chỉnh lại trước khi thanh toán.</p>
            <?php endif; ?>

            <a href="checkout.php" class="btn-dark <?= (empty($cart_items) || $co_loi) ? 'is-disabled' : '' ?>" <?= (empty($cart_items) || $co_loi) ? 'aria-disabled="true" tabindex="-1"' : '' ?>>THANH TOÁN</a>
            <p class="ship-note">Dịch vụ được nhận trực tiếp tại studio nên không có phí vận chuyển. Đơn sẽ chờ studio duyệt sau khi đặt.</p>
        </aside>

    </div>
</section>

<?php require_once '../includes/footer.php'; ?>