<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Lấy danh sách sản phẩm trong giỏ hàng cùng thông tin chi tiết từ CSDL
 */
function lay_gio_hang($conn) {
    if (empty($_SESSION['cart'])) {
        return [];
    }

    $cart_items = [];
    foreach ($_SESSION['cart'] as $id => $item) {
        $qty = is_array($item) ? ($item['qty'] ?? 1) : (int)$item;
        $bat_dau = is_array($item) ? ($item['bat_dau'] ?? '') : '';
        $ket_thuc = is_array($item) ? ($item['ket_thuc'] ?? '') : '';

        $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([(int)$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $product['qty'] = $qty;
            $product['bat_dau'] = $bat_dau;
            $product['ket_thuc'] = $ket_thuc;
            
            // BỔ SUNG DÒNG NÀY ĐỂ TRÁNH LỖI SUBTOTAL:
            $product['subtotal'] = (int)$product['gia'] * $qty;
            
            $cart_items[] = $product;
        }
    }

    return $cart_items;
}
/**
 * Tính toán tổng số tiền, giảm giá, phí ship
 */
function tinh_tong_gio_hang($cart_items) {
    $subtotal = 0;
    $need_ship = false;

    foreach ($cart_items as $item) {
        $subtotal += $item['gia'] * $item['qty'];
        // Nếu có thuộc tính physical hoặc loại là ấn phẩm/hàng hóa cần giao
        if (!empty($item['physical']) || ($item['loai'] ?? '') === 'an-pham') {
            $need_ship = true;
        }
    }

    $code = $_SESSION['coupon'] ?? null;
    $discount = ($code === 'VIBE10') ? round($subtotal * 0.1) : 0;
    
    $free_ship_from = 1000000;
    $ship_fee = 30000;
    
    $shipping = ($need_ship && $subtotal > 0 && ($subtotal - $discount) < $free_ship_from) ? $ship_fee : 0;
    $total = max(0, $subtotal - $discount + $shipping);

    return [
        'subtotal'       => $subtotal,
        'code'           => $code,
        'discount'       => $discount,
        'need_ship'      => $need_ship,
        'shipping'       => $shipping,
        'total'          => $total,
        'free_ship_from' => $free_ship_from
    ];
}