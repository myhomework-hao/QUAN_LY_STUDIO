<?php
/**
 * includes/coupon_helper.php
 * Helper xử lý mã giảm giá.
 * Yêu cầu: đã include cart_helper.php (dùng dinh_dang_tien()).
 */

/**
 * Kiểm tra mã giảm giá có hợp lệ không (chưa áp dụng, chỉ validate).
 *
 * @return array{ok:bool,message:string,coupon:?array,discount:float}
 */
function kiem_tra_ma_giam_gia(PDO $conn, string $code, float $subtotal): array
{
    $code = strtoupper(trim($code));
    if ($code === '') {
        return ['ok' => false, 'message' => 'Vui lòng nhập mã giảm giá.', 'coupon' => null, 'discount' => 0.0];
    }

    $stmt = $conn->prepare("SELECT * FROM ma_giam_gia WHERE MG_Code = ? LIMIT 1");
    $stmt->execute([$code]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$c) {
        return ['ok' => false, 'message' => 'Mã giảm giá không tồn tại.', 'coupon' => null, 'discount' => 0.0];
    }
    if ((int) $c['MG_DangHoatDong'] !== 1) {
        return ['ok' => false, 'message' => 'Mã giảm giá đã bị vô hiệu hóa.', 'coupon' => null, 'discount' => 0.0];
    }

    $now_str = date('Y-m-d H:i:s');   // Dùng cùng format với MySQL DATETIME
    if (!empty($c['MG_NgayBatDau']) && (string) $c['MG_NgayBatDau'] > $now_str) {
        return ['ok' => false, 'message' => 'Mã giảm giá chưa đến thời gian sử dụng.', 'coupon' => null, 'discount' => 0.0];
    }
    if (!empty($c['MG_NgayKetThuc']) && (string) $c['MG_NgayKetThuc'] < $now_str) {
        return ['ok' => false, 'message' => 'Mã giảm giá đã hết hạn.', 'coupon' => null, 'discount' => 0.0];
    }
    if ((int) $c['MG_SoLuong'] > 0 && (int) $c['MG_DaDung'] >= (int) $c['MG_SoLuong']) {
        return ['ok' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.', 'coupon' => null, 'discount' => 0.0];
    }
    if ($subtotal < (float) $c['MG_DonToiThieu']) {
        return [
            'ok' => false,
            'message' => 'Đơn hàng tối thiểu ' . dinh_dang_tien((float) $c['MG_DonToiThieu']) . ' để dùng mã này.',
            'coupon' => null,
            'discount' => 0.0,
        ];
    }

    // Tính số tiền giảm
    $discount = 0.0;
    if ($c['MG_Loai'] === 'percent') {
        $discount = $subtotal * ((float) $c['MG_GiaTri'] / 100.0);
        if (!empty($c['MG_GiamToiDa']) && (float) $c['MG_GiamToiDa'] > 0) {
            $discount = min($discount, (float) $c['MG_GiamToiDa']);
        }
    } else { // fixed
        $discount = (float) $c['MG_GiaTri'];
    }

    $discount = min($discount, $subtotal);      // không vượt quá tạm tính
    $discount = round($discount, 0);            // VND làm tròn đồng

    return ['ok' => true, 'message' => 'Áp dụng mã thành công.', 'coupon' => $c, 'discount' => $discount];
}

/**
 * Tăng lượt dùng — gọi SAU khi đặt hàng thành công (trong checkout.php).
 */
function tang_luot_dung_ma(PDO $conn, string $code): void
{
    $stmt = $conn->prepare("UPDATE ma_giam_gia SET MG_DaDung = MG_DaDung + 1 WHERE MG_Code = ?");
    $stmt->execute([strtoupper(trim($code))]);
}