<?php
require_once '../config/app.php';
require_once '../config/db.php';

$code = trim($_GET['code'] ?? '');
$order = null;
$items = [];

if ($code) {
    // 1. Lấy thông tin đơn hàng chính
    $stmt = $conn->prepare("SELECT * FROM orders WHERE code = ?");
    $stmt->execute([$code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // 2. Lấy danh sách sản phẩm/dịch vụ trong đơn
        $stmt_items = $conn->prepare("
            SELECT i.*, s.ten_dich_vu, s.hinh_anh 
            FROM order_items i 
            LEFT JOIN services s ON i.service_id = s.id 
            WHERE i.order_id = ?
        ");
        $stmt_items->execute([$order['id']]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    }
}

$tieu_de_trang = $order ? 'Chi tiết đơn hàng ' . $order['code'] : 'Không tìm thấy đơn hàng';
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
            <p>Không tìm thấy thông tin đơn hàng yêu cầu.</p>
            <br>
            <a href="my-orders.php" class="btn-dark">QUAY LẠI ĐƠN HÀNG CỦA TÔI</a>
        </div>
    <?php else : ?>
        <div style="margin-bottom: 20px;">
            <a href="my-orders.php" style="color: #333; text-decoration: underline;">← Quay lại danh sách đơn hàng</a>
        </div>

        <div class="card" style="border: 1px solid #ddd; padding: 25px; border-radius: 8px; background: #fff;">
            <!-- Header đơn hàng -->
            <div style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="margin: 0 0 5px 0;">MÃ ĐƠN: <?= htmlspecialchars($order['code']) ?></h2>
                    <span style="color: #666; font-size: 0.9rem;">Ngày đặt: <?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></span>
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
                        echo $status_map[$order['status']] ?? htmlspecialchars($order['status']);
                        ?>
                    </strong>
                </div>
            </div>

            <!-- Thông tin khách hàng & Giao hàng -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; background: #f9f9f9; padding: 15px; border-radius: 6px;">
                <div>
                    <h4 style="margin-top: 0;">THÔNG TIN KHÁCH HÀNG</h4>
                    <p><b>Họ tên:</b> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><b>Số điện thoại:</b> <?= htmlspecialchars($order['customer_phone']) ?></p>
                    <p><b>Email:</b> <?= htmlspecialchars($order['customer_email']) ?></p>
                </div>
                <div>
                    <h4 style="margin-top: 0;">THÔNG TIN GIAO HÀNG / THANH TOÁN</h4>
                    <p><b>Địa chỉ:</b> <?= htmlspecialchars($order['shipping_address'] ?: 'Nhận tại Studio') ?></p>
                    <p><b>Thanh toán:</b> <?= htmlspecialchars($order['payment_method']) ?></p>
                    <?php if (!empty($order['note'])) : ?>
                        <p><b>Ghi chú:</b> <?= htmlspecialchars($order['note']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Danh sách sản phẩm -->
            <h4 style="margin-bottom: 15px;">DANH SÁCH SẢN PHẨM / DỊCH VỤ</h4>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="border-bottom: 2px solid #ddd; text-align: left; background: #f4f4f4;">
                        <th style="padding: 10px;">Sản phẩm / Dịch vụ</th>
                        <th style="padding: 10px; text-align: center;">Số lượng</th>
                        <th style="padding: 10px; text-align: right;">Đơn giá</th>
                        <th style="padding: 10px; text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) : ?>
                        <?php $subtotal = $item['price'] * $item['quantity']; ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px 10px;">
                                <strong><?= htmlspecialchars($item['ten_dich_vu'] ?? ('Dịch vụ #' . $item['service_id'])) ?></strong>
                            </td>
                            <td style="padding: 12px 10px; text-align: center;"><?= $item['quantity'] ?></td>
                            <td style="padding: 12px 10px; text-align: right;"><?= number_format($item['price'], 0, ',', '.') ?>₫</td>
                            <td style="padding: 12px 10px; text-align: right;"><?= number_format($subtotal, 0, ',', '.') ?>₫</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Tổng kết -->
            <div style="text-align: right; border-top: 2px solid #ddd; padding-top: 15px;">
                <p style="font-size: 1.2rem; margin: 0;">Tổng thanh toán: <strong style="color: #000; font-size: 1.4rem;"><?= number_format($order['total_price'], 0, ',', '.') ?>₫</strong></p>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require_once '../includes/footer.php'; ?>