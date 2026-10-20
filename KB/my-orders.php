<?php
require_once '../config/app.php';
require_once '../config/db.php';

// Đảm bảo session đã được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$da_dang_nhap = function_exists('da_dang_nhap') ? da_dang_nhap() : !empty($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$sdt_session = $_SESSION['last_phone'] ?? '';

$orders = [];

// 1. Kiểm tra xem bảng dondatlich thực tế đang có những cột nào
$columns_in_db = [];
try {
    $stmt_cols = $conn->query("SHOW COLUMNS FROM dondatlich");
    $columns_in_db = $stmt_cols->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Xử lý nếu bảng không tồn tại hoặc lỗi kết nối
}

// Xác định tên cột liên kết tài khoản dựa trên các cột thực tế có trong database
$valid_col = null;
$possible_user_columns = ['user_id', 'id_khach_hang', 'khach_hang_id', 'taikhoan_id'];
foreach ($possible_user_columns as $col) {
    if (in_array($col, $columns_in_db)) {
        $valid_col = $col;
        break;
    }
}

// Kiểm tra xem bảng có cột lưu số điện thoại không
$has_phone_col = in_array('so_dien_thoai', $columns_in_db) || in_array('customer_phone', $columns_in_db);
$phone_col_name = in_array('so_dien_thoai', $columns_in_db) ? 'so_dien_thoai' : 'customer_phone';

// 2. Thực hiện truy vấn dữ liệu an toàn
if ($da_dang_nhap && $user_id && $valid_col) {
    $stmt = $conn->prepare("
        SELECT * FROM dondatlich 
        WHERE $valid_col = ? 
        ORDER BY id DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} 

// Nếu chưa có đơn theo tài khoản hoặc chưa đăng nhập mà có lưu số điện thoại tra cứu
if (empty($orders) && !empty($sdt_session) && $has_phone_col) {
    $stmt = $conn->prepare("
        SELECT * FROM dondatlich 
        WHERE $phone_col_name = ? 
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
            <p>Bạn cần đăng nhập để quản lý và theo dõi danh sách đơn lịch đặt của mình.</p>
            <br>
            <a href="<?= BASE_URL ?>KB/login.php" class="btn-dark">ĐĂNG NHẬP NGAY</a>
        </div>
    <?php elseif (empty($orders)) : ?>
        <div class="card" style="text-align: center; padding: 40px;">
            <p>Bạn chưa có đơn đặt lịch nào tại VIBE Studio.</p>
            <br>
            <a href="<?= BASE_URL ?>AT/services.php" class="btn-dark">KHÁM PHÁ DỊCH VỤ</a>
        </div>
    <?php else : ?>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php foreach ($orders as $order) : ?>
                <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: #fff;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <strong>MÃ ĐƠN: <?= htmlspecialchars($order['ma_don'] ?? $order['id']) ?></strong>
                            <span style="color: #666; margin-left: 10px; font-size: 0.9rem;">
                                <?= isset($order['ngay_tao']) ? date('d/m/Y H:i', strtotime($order['ngay_tao'])) : (isset($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : '') ?>
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
                                $status_key = $order['trang_thai'] ?? ($order['status'] ?? 'cho_xuly');
                                echo $status_map[$status_key] ?? htmlspecialchars($status_key);
                                ?>
                            </strong>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <p style="margin-bottom: 5px;">Thanh toán: <b><?= htmlspecialchars($order['phuong_thuc_thanh_toan'] ?? ($order['payment_method'] ?? 'Tiền mặt')) ?></b></p>
                            <p>Tổng tiền: <strong style="font-size: 1.2rem; color: #000;"><?= number_format($order['tong_tien'] ?? ($order['total_price'] ?? 0), 0, ',', '.') ?>₫</strong></p>
                        </div>
                        <div>
                            <a href="order-detail.php?code=<?= urlencode($order['ma_don'] ?? $order['id']) ?>" class="btn-dark" style="text-decoration: none; padding: 8px 16px; font-size: 0.9rem;">CHI TIẾT ĐƠN HÀNG</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>