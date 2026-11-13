<?php
require_once '../config/app.php';
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../includes/donhang.php';

yeu_cau_vai_tro('khachhang');

$kq = $_SESSION['dat_hang_ket_qua'] ?? null;
if (!$kq || !is_array($kq) || empty($kq['ma_don'])) {
    header('Location: my-orders.php');
    exit;
}

$ma_don = (int) $kq['ma_don'];
$phuong_thuc = (string) ($kq['phuong_thuc'] ?? '');
$ten = (string) ($kq['ten_nguoi_thue'] ?? '');
$so_tien_da_thu = (float) ($kq['so_tien_da_thu'] ?? 0);
$la_dat_coc = !empty($kq['la_dat_coc']);
$tong_don = (float) ($kq['tong_don'] ?? 0);

$tieu_de_trang = 'Thanh toán thành công - VIBE Studio';
$css_rieng = [BASE_URL . 'assets/css/cart.css?v=' . (is_file(__DIR__ . '/../assets/css/cart.css') ? filemtime(__DIR__ . '/../assets/css/cart.css') : time())];
require_once '../includes/header.php';
?>
<section class="page-head" id="pageHead">
    <p class="page-label">SUCCESS</p>
    <h1>THANH TOÁN <i>THÀNH CÔNG.</i></h1>
    <ol class="steps">
        <li class="done">1. GIỎ HÀNG</li>
        <li class="done">2. THANH TOÁN</li>
        <li class="current">3. HOÀN TẤT</li>
    </ol>
</section>

<section class="shop-area" id="shopArea">
    <div class="card success" style="text-align:center;padding:40px 20px;max-width:720px;margin:0 auto;">
        <h1>ĐÃ XÁC NHẬN<br><i><?= $la_dat_coc ? 'ĐẶT CỌC.' : 'THANH TOÁN.' ?></i></h1>
        <p>Cảm ơn <?= htmlspecialchars($ten) ?>! <?= $la_dat_coc ? 'Khoản cọc' : 'Thanh toán' ?> đã được ghi nhận (giả lập). Đơn thuê của bạn đã được tạo và đang chờ VIBE Studio xét duyệt.</p>
        <div class="order-code" style="margin:15px 0;font-weight:bold;font-size:1.2rem;">MÃ ĐƠN: <?= htmlspecialchars(ma_don_hien_thi($ma_don)) ?></div>
        <p>Phương thức: <?= htmlspecialchars($phuong_thuc) ?></p>
        <p>Số tiền đã xác nhận: <strong><?= dinh_dang_tien($so_tien_da_thu) ?></strong><?php if ($la_dat_coc && $tong_don > 0) : ?> · Tổng đơn: <?= dinh_dang_tien($tong_don) ?> · Còn lại: <?= dinh_dang_tien(max(0, $tong_don - $so_tien_da_thu)) ?><?php endif; ?></p>

        <div style="margin-top:25px;display:flex;gap:15px;justify-content:center;flex-wrap:wrap;">
            <a href="index.php" class="btn-dark" style="text-decoration:none;padding:10px 20px;">VỀ TRANG CHỦ</a>
            <a href="order-detail.php?id=<?= $ma_don ?>" class="btn-dark" style="text-decoration:none;padding:10px 20px;background-color:#333;color:#fff;">XEM ĐƠN THUÊ</a>
        </div>
    </div>
</section>

<?php
// Giữ kết quả cho phép quay lại xem, nhưng nếu cần có thể bỏ unset. Để đơn giản, giữ lại đến khi rời trang.
require_once '../includes/footer.php';
?>