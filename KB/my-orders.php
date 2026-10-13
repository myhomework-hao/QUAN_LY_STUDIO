<?php
require_once '../config/app.php';
require_once '../config/db.php';

// Đảm bảo session đã được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra trạng thái đăng nhập chuẩn theo hệ thống phân quyền mới
$da_dang_nhap = function_exists('da_dang_nhap') ? da_dang_nhap() : !empty($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$sdt_session = $_SESSION['last_phone'] ?? '';

$orders = [];

if ($da_dang_nhap && $user_id) {
    // Lấy danh sách đơn hàng theo user_id của tài khoản đang đăng nhập
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? 
        ORDER BY id DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (!empty($sdt_session)) {
    // Nếu chưa đăng nhập nhưng lưu số điện thoại tra cứu nhanh gần nhất
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE customer_phone = ? 
        ORDER BY id DESC
    ");
    $stmt->execute([$sdt_session]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$tieu_de_trang = 'Đơn hàng của tôi - VIBE Studio';
$css_rieng = ['cart.css'];
require_once '../includes/header.php';
?>

<section class="page-head" id="pageHead">
    <p class="page-label">ACCOUNT</p>
    <h1>ĐƠN HÀNG <i>CỦA TÔI.</i></h1>
</section>

<section class="shop-area" id="shopArea" style="max-width: 900px; margin: 0 auto; padding: 20px;">
    <?php if (!$da_dang_nhap && empty($sdt_session)) : ?>
        <div class="card" style="text-align: center; padding: 40px;">
            <p>Bạn cần đăng nhập để quản lý và theo dõi danh sách đơn hàng của mình.</p>
            <br>
            <a href="<?= BASE_URL ?>KB/login.php" class="btn-dark">ĐĂNG NHẬP NGAY</a>
        </div>
    <?php elseif (empty($orders)) : ?>
        <div class="card" style="text-align: center; padding: 40px;">
            <p>Bạn chưa có đơn hàng nào tại VIBE Studio.</p>
            <br>
            <a href="<?= BASE_URL ?>AT/services.php" class="btn-dark">KHÁM PHÁ DỊCH VỤ</a>
        </div>
    <?php else : ?>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php foreach ($orders as $order) : ?>
                <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: #fff;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <strong>MÃ ĐƠN: <?= htmlspecialchars($order['code'] ?? $order['id']) ?></strong>
                            <span style="color: #666; margin-left: 10px; font-size: 0.9rem;">
                                <?= isset($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : '' ?>
                            </span>
                        </div>
                        <div>
                            Trạng thái: 
                            <strong style="color: #d97706;">
                                <?php
                                $status_map = [
                                    'cho_xuly' => 'Đang chờ xử lý',
                                    'da_xac_nhan' => 'Đã xác nhận',
                                    'hoan_tat' => 'Hoàn tất',
                                    'da_huy' => 'Đã hủy'
                                ];
                                $status_key = $order['status'] ?? 'cho_xuly';
                                echo $status_map[$status_key] ?? htmlspecialchars($status_key);
                                ?>
                            </strong>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <p style="margin-bottom: 5px;">Thanh toán: <b><?= htmlspecialchars($order['payment_method'] ?? 'Tiền mặt') ?></b></p>
                            <p>Tổng tiền: <strong style="font-size: 1.2rem; color: #000;"><?= number_format($order['total_price'] ?? 0, 0, ',', '.') ?>₫</strong></p>
                        </div>
                        <div>
                            <a href="order-detail.php?code=<?= urlencode($order['code'] ?? $order['id']) ?>" class="btn-dark" style="text-decoration: none; padding: 8px 16px; font-size: 0.9rem;">CHI TIẾT ĐƠN HÀNG</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>