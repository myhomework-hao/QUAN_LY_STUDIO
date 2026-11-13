<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/donhang.php';
require_once '../includes/security.php';

yeu_cau_vai_tro('khachhang');

$pending = $_SESSION['pending_checkout'] ?? null;
if (!$pending || !is_array($pending) || empty($pending['token'])) {
    header('Location: checkout.php');
    exit;
}

$token = (string) $pending['token'];
$xac_nhan_url = BASE_URL . 'user/xac-nhan-thanh-toan.php?token=' . urlencode($token);
$so_tien = (float) ($pending['so_tien_can_thu'] ?? 0);
$la_dat_coc = !empty($pending['la_dat_coc']);
$tieu_de_khoan = $la_dat_coc ? 'ĐẶT CỌC (25%)' : 'THANH TOÁN ĐỦ';
$phuong_thuc = (string) ($pending['payment_label'] ?? '');

$tieu_de_trang = 'Thanh toán - VIBE Studio';
$css_rieng = [BASE_URL . 'assets/css/cart.css?v=' . (is_file(__DIR__ . '/../assets/css/cart.css') ? filemtime(__DIR__ . '/../assets/css/cart.css') : time())];
require_once '../includes/header.php';
?>
<section class="page-head" id="pageHead">
    <p class="page-label">PAYMENT</p>
    <h1>THANH <i>TOÁN.</i></h1>
    <ol class="steps">
        <li class="done">1. GIỎ HÀNG</li>
        <li class="current">2. THANH TOÁN</li>
        <li>3. HOÀN TẤT</li>
    </ol>
</section>

<section class="shop-area">
    <div class="checkout-layout">
        <div class="stack">
            <div class="card">
                <div class="card-head"><h2><?= htmlspecialchars($tieu_de_khoan) ?></h2></div>
                <div class="card-body" style="text-align:center;">
                    <p style="margin-bottom:8px;">Phương thức: <strong><?= htmlspecialchars($phuong_thuc) ?></strong></p>
                    <p style="font-size:28px;font-weight:700;color:#111;margin:10px 0;"><?= dinh_dang_tien($so_tien) ?></p>
                    <p style="color:#777;font-size:13px;margin-bottom:16px;">Đây là bước mô phỏng trên localhost, <strong>không trừ tiền thật</strong>.</p>

                    <div style="display:inline-block;padding:12px;border:1px solid #eaeaea;border-radius:12px;background:#fff;margin-bottom:14px;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode($xac_nhan_url) ?>" alt="Mã QR xác nhận thanh toán (giả lập)" width="220" height="220" style="display:block;">
                    </div>
                    <p style="font-size:12px;color:#777;margin-bottom:12px;">Quét mã để mở trang xác nhận (cần điện thoại và máy cùng mạng LAN, dùng IP của máy thay cho localhost). Nếu ảnh QR không tải được, dùng nút bên dưới.</p>

                    <a href="<?= htmlspecialchars($xac_nhan_url) ?>" class="btn-dark" style="max-width:320px;margin:0 auto 10px;">MỞ TRANG XÁC NHẬN (GIẢ LẬP QUÉT)</a>
                    <p style="font-size:12px;color:#666;word-break:break-all;">Link xác nhận: <br><span><?= htmlspecialchars($xac_nhan_url) ?></span></p>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><h2>Hướng dẫn</h2></div>
                <div class="card-body">
                    <ol style="margin:0;padding-left:18px;color:#444;font-size:14px;line-height:1.6;">
                        <li>Kiểm tra số tiền cần <?= $la_dat_coc ? 'đặt cọc (25%)' : 'thanh toán' ?>.</li>
                        <li>Mở trang xác nhận bằng cách quét QR hoặc bấm nút giả lập.</li>
                        <li>Nhập đúng số tiền và bấm xác nhận để hệ thống ghi nhận.</li>
                        <li>Sau khi xác nhận thành công, đơn thuê mới được tạo và chờ nhân viên duyệt.</li>
                    </ol>
                </div>
            </div>
        </div>

        <aside class="summary">
            <p class="panel-title">Tóm tắt</p>
            <div class="summary-items">
                <?php foreach (($pending['cart_items'] ?? []) as $item) : ?>
                    <div class="summary-row">
                        <span><?= htmlspecialchars((string) ($item['ten'] ?? '')) ?> × <?= (int) ($item['qty'] ?? 1) ?></span>
                        <span><?= dinh_dang_tien((float) ($item['subtotal'] ?? 0)) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-row total"><span>Tổng đơn</span><span><?= dinh_dang_tien((float) (($pending['totals']['total'] ?? 0))) ?></span></div>
            <div class="summary-row"><span><strong>Cần thanh toán lúc này</strong></span><span><strong><?= dinh_dang_tien($so_tien) ?></strong></span></div>
            <p class="ship-note"><a href="checkout.php" style="border-bottom:1px solid #888">Quay lại chỉnh sửa thông tin</a></p>
        </aside>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
