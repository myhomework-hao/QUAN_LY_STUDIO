<?php
require_once '../config/app.php';
require_once '../config/db.php';

// Đảm bảo session đã được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$code = trim($_GET['code'] ?? '');
$order = null;
$items = [];

$user_id = $_SESSION['user_id'] ?? null;
$sdt_session = $_SESSION['last_phone'] ?? '';
$vai_tro = function_exists('vai_tro_hien_tai') ? vai_tro_hien_tai() : ($_SESSION['role'] ?? '');

if ($code) {
    // 1. Kiểm tra cấu trúc các cột thực tế của bảng dondatlich để tránh lỗi truy vấn
    $columns_don = [];
    try {
        $stmt_cols = $conn->query("SHOW COLUMNS FROM dondatlich");
        $columns_don = $stmt_cols->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}

    $has_ma_don = in_array('ma_don', $columns_don);

    // Truy vấn lấy thông tin đơn hàng chính
    if ($has_ma_don) {
        $stmt = $conn->prepare("SELECT * FROM dondatlich WHERE ma_don = ? OR id = ?");
        $stmt->execute([$code, $code]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM dondatlich WHERE id = ?");
        $stmt->execute([$code]);
    }
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Kiểm tra quyền xem đơn nếu là khách hàng
        if ($vai_tro === 'khachhang' || empty($vai_tro)) {
            $order_user_id = $order['user_id'] ?? ($order['id_khach_hang'] ?? ($order['khach_hang_id'] ?? null));
            $order_phone = $order['so_dien_thoai'] ?? ($order['customer_phone'] ?? '');

            $khop_user = ($user_id && $order_user_id == $user_id);
            $khop_sdt = (!empty($sdt_session) && $order_phone == $sdt_session);
            
            if (!$khop_user && !$khop_sdt) {
                $order = null; // Không có quyền xem
            }
        }

        if ($order) {
            // 2. Lấy danh sách item từ bảng chitietdon kết nối linh hoạt với bảng dichvu
            try {
                $stmt_items = $conn->prepare("
                    SELECT ct.*, 
                           COALESCE(dv.ten_dich_vu, dv.name, 'Dịch vụ') as ten_dv, 
                           COALESCE(dv.gia, dv.gia_tien, dv.price, ct.gia, 0) as don_gia 
                    FROM chitietdon ct 
                    LEFT JOIN dichvu dv ON (ct.id_dich_vu = dv.id OR ct.service_id = dv.id)
                    WHERE ct.id_don_dat_lich = ? OR ct.order_id = ?
                ");
                $stmt_items->execute([$order['id'], $order['id']]);
                $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // Trường hợp tên cột chi tiết đơn khác biệt hoàn toàn
                $items = [];
            }
        }
    }
}

$ma_hien_thi = $order['ma_don'] ?? ($order['id'] ?? $code);
$tieu_de_trang = $order ? 'Chi tiết đơn hàng ' . $ma_hien_thi : 'Không tìm thấy đơn hàng';
$css_rieng = ['cart.css'];
require_once '../includes/header.php';
?>

<section class="page-head" id="pageHead">
    <p class="page-label">ORDER DETAILS</p>
    <h1>CHI TIẾT <i>ĐƠN HÀNG.</i></h1>
</section>

<section class="shop-area" id="shopArea" style="max-width: 900px; margin: 0 auto; padding: 20px;">
    <?php if (!$order) : ?>
        <div class="card" style="text-align: center; padding: 40px;">
            <p>Không tìm thấy thông tin đơn hàng hoặc bạn không có quyền xem đơn hàng này.</p>
            <br>
            <a href="my-orders.php" class="btn-dark">QUAY LẠI ĐƠN HÀNG CỦA TÔI</a>
        </div>
    <?php else : ?>
        <div style="margin-bottom: 20px;">
            <a href="my-orders.php" style="color: #333; text-decoration: underline; font-weight: 500;">← Quay lại danh sách đơn hàng</a>
        </div>

        <div class="card" style="border: 1px solid #ddd; padding: 25px; border-radius: 8px; background: #fff;">
            <!-- Header đơn hàng -->
            <div style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="margin: 0 0 5px 0;">MÃ ĐƠN: <?= htmlspecialchars($ma_hien_thi) ?></h2>
                    <span style="color: #666; font-size: 0.9rem;">Ngày đặt: <?= isset($order['ngay_tao']) ? date('d/m/Y H:i:s', strtotime($order['ngay_tao'])) : (isset($order['created_at']) ? date('d/m/Y H:i:s', strtotime($order['created_at'])) : '') ?></span>
                </div>
                <div>
                    Trạng thái: 
                    <strong style="color: #d97706; font-size: 1.1rem;">
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

            <!-- Thông tin khách hàng & Giao hàng -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; background: #f9f9f9; padding: 15px; border-radius: 6px;">
                <div>
                    <h4 style="margin-top: 0;">THÔNG TIN KHÁCH HÀNG</h4>
                    <p style="margin: 5px 0;"><b>Họ tên:</b> <?= htmlspecialchars($order['ho_ten'] ?? ($order['customer_name'] ?? '')) ?></p>
                    <p style="margin: 5px 0;"><b>Số điện thoại:</b> <?= htmlspecialchars($order['so_dien_thoai'] ?? ($order['customer_phone'] ?? '')) ?></p>
                    <p style="margin: 5px 0;"><b>Email:</b> <?= htmlspecialchars($order['email'] ?? ($order['customer_email'] ?? '')) ?></p>
                </div>
                <div>
                    <h4 style="margin-top: 0;">THÔNG TIN ĐẶT LỊCH / THANH TOÁN</h4>
                    <p style="margin: 5px 0;"><b>Hình thức:</b> <?= htmlspecialchars($order['hinh_thuc'] ?? 'Tại Studio') ?></p>
                    <p style="margin: 5px 0;"><b>Thanh toán:</b> <?= htmlspecialchars($order['phuong_thuc_thanh_toan'] ?? ($order['payment_method'] ?? 'Tiền mặt')) ?></p>
                    <?php 
                    $ghi_chu = $order['ghi_chu'] ?? ($order['note'] ?? '');
                    if (!empty($ghi_chu)) : ?>
                        <p style="margin: 5px 0;"><b>Ghi chú:</b> <?= htmlspecialchars($ghi_chu) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Danh sách dịch vụ trong đơn -->
            <h4 style="margin-bottom: 15px;">DANH SÁCH DỊCH VỤ ĐÃ ĐẶT</h4>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="border-bottom: 2px solid #ddd; text-align: left; background: #f4f4f4;">
                        <th style="padding: 10px;">Dịch vụ</th>
                        <th style="padding: 10px; text-align: center;">Số lượng</th>
                        <th style="padding: 10px; text-align: right;">Đơn giá</th>
                        <th style="padding: 10px; text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)) : ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 15px; color: #777;">Không có dịch vụ nào trong đơn hàng này.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($items as $item) : ?>
                            <?php 
                                $ten_dv = $item['ten_dv'] ?? ('Dịch vụ #' . ($item['id_dich_vu'] ?? ''));
                                $quantity = $item['so_luong'] ?? ($item['quantity'] ?? 1);
                                $price = $item['don_gia'] ?? 0;
                                $subtotal = $price * $quantity; 
                            ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 10px;">
                                    <strong><?= htmlspecialchars($ten_dv) ?></strong>
                                </td>
                                <td style="padding: 12px 10px; text-align: center;"><?= $quantity ?></td>
                                <td style="padding: 12px 10px; text-align: right;"><?= number_format($price, 0, ',', '.') ?>₫</td>
                                <td style="padding: 12px 10px; text-align: right;"><?= number_format($subtotal, 0, ',', '.') ?>₫</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Tổng kết -->
            <div style="text-align: right; border-top: 2px solid #ddd; padding-top: 15px;">
                <p style="font-size: 1.2rem; margin: 0;">Tổng thanh toán: <strong style="color: #000; font-size: 1.4rem;"><?= number_format($order['tong_tien'] ?? ($order['total_price'] ?? 0), 0, ',', '.') ?>₫</strong></p>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>