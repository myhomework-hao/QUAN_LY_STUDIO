<?php
require_once '../config/app.php';
require_once '../config/db.php';
require_once '../includes/cart_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'add' && $id > 0) {
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $ngay_dat = $_POST['ngay_dat'] ?? '';
        $gio_dat = $_POST['gio_dat'] ?? '';

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Lấy số lượng hiện tại
        $current_qty = 0;
        if (isset($_SESSION['cart'][$id])) {
            $current_qty = is_array($_SESSION['cart'][$id]) ? ($_SESSION['cart'][$id]['qty'] ?? 0) : (int)$_SESSION['cart'][$id];
        }

        $new_qty = min(20, $current_qty + $qty);

        $_SESSION['cart'][$id] = [
            'qty' => $new_qty,
            'ngay_dat' => $ngay_dat,
            'gio_dat' => $gio_dat
        ];

        // Tính lại tổng số lượng trong giỏ
        $cart_items = lay_gio_hang($conn);
        $total_qty = array_sum(array_column($cart_items, 'qty'));

        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng thành công!',
            'total_qty' => $total_qty
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);