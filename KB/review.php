<?php
require_once '../config/app.php';
require_once '../config/db.php';

// Đảm bảo session đã được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Thêm đoạn kiểm tra trạng thái đơn hàng trong review.php nếu muốn bắt buộc phải hoàn tất mới được đánh giá
if ($order['status'] !== 'hoan_tat') {
    $loi = 'Bạn chỉ có thể đánh giá dịch vụ sau khi đơn hàng đã hoàn tất.';
}
// Bắt buộc đăng nhập
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ' . BASE_URL . 'KB/login.php');
    exit;
}

$order_code = trim($_GET['code'] ?? '');
$service_id = intval($_GET['service_id'] ?? 0);
$thong_bao = '';
$loi = '';

// Kiểm tra xem đơn hàng có thuộc về user này không và có chứa dịch vụ đó không
$order = null;
if ($order_code && $service_id) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE code = ? AND user_id = ?");
    $stmt->execute([$order_code, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$order) {
    $loi = 'Không tìm thấy đơn hàng hợp lệ hoặc bạn không có quyền đánh giá.';
}

// Xử lý khi submit form đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order) {
    $rating = intval($_POST['rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $loi = 'Vui lòng chọn số sao đánh giá từ 1 đến 5.';
    } else {
        // Lưu vào bảng reviews (hoặc bảng đánh giá tương ứng trong CSDL)
        // Kiểm tra xem bảng reviews đã có chưa, nếu có thì insert
        try {
            // Thử lưu đánh giá
            $stmt_ins = $conn->prepare("
                INSERT INTO reviews (user_id, service_id, order_id, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt_ins->execute([$user_id, $service_id, $order['id'], $rating, $comment]);
            $thong_bao = 'Cảm ơn bạn đã đánh giá dịch vụ!';
        } catch (Exception $e) {
            $loi = 'Có lỗi xảy ra hoặc bạn đã đánh giá dịch vụ này rồi.';
        }
    }
}

$tieu_de_trang = 'Đánh giá dịch vụ - VIBE Studio';
$css_rieng = ['cart.css'];
require_once '../includes/header.php';
?>

<section class="page-head" id="pageHead">
    <p class="page-label">REVIEW</p>
    <h1>ĐÁNH GIÁ <i>DỊCH VỤ.</i></h1>
</section>

<section class="shop-area" style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <?php if (!empty($thong_bao)): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: center;">
            <p style="margin-bottom: 10px;"><?= htmlspecialchars($thong_bao) ?></p>
            <a href="order-detail.php?code=<?= urlencode($order_code) ?>" class="btn-dark" style="text-decoration: none; padding: 8px 16px; font-size: 0.9rem;">QUAY LẠI CHI TIẾT ĐƠN HÀNG</a>
        </div>
    <?php elseif (!empty($loi)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: center;">
            <p><?= htmlspecialchars($loi) ?></p>
            <br>
            <a href="my-orders.php" class="btn-dark" style="text-decoration: none; padding: 8px 16px; font-size: 0.9rem;">ĐƠN HÀNG CỦA TÔI</a>
        </div>
    <?php else: ?>
        <div class="card" style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <form action="review.php?code=<?= urlencode($order_code) ?>&service_id=<?= $service_id ?>" method="POST">
                <div style="margin-bottom: 20px; text-align: center;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem;">Chất lượng dịch vụ thế nào?</label>
                    <select name="rating" style="padding: 10px; font-size: 1rem; border-radius: 4px; border: 1px solid #ddd; width: 100%;">
                        <option value="5">⭐⭐⭐⭐⭐ - Tuyệt vời (5 sao)</option>
                        <option value="4">⭐⭐⭐⭐ - Hài lòng (4 sao)</option>
                        <option value="3">⭐⭐⭐ - Bình thường (3 sao)</option>
                        <option value="2">⭐⭐ - Tạm được (2 sao)</option>
                        <option value="1">⭐ - Không tốt (1 sao)</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nhận xét chi tiết của bạn</label>
                    <textarea name="comment" rows="5" placeholder="Hãy chia sẻ trải nghiệm của bạn về dịch vụ tại studio..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <a href="order-detail.php?code=<?= urlencode($order_code) ?>" style="color: #666; text-decoration: none;">Hủy bỏ</a>
                    <button type="submit" class="btn-dark" style="padding: 10px 25px; cursor: pointer;">GỬI ĐÁNH GIÁ</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>