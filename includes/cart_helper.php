<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function tinh_so_don_vi(string $don_vi, string $bat_dau, string $ket_thuc): int
{
    if ($don_vi === 'bo') {
        return 1;
    }

    $tu = strtotime($bat_dau);
    $den = strtotime($ket_thuc);

    if ($tu === false || $den === false || $den <= $tu) {
        return 1;
    }

    if ($don_vi === 'gio') {
        return max(1, (int) ceil(($den - $tu) / 3600));
    }

    return max(1, (int) round(($den - $tu) / 86400));
}

function lay_gio_hang($conn) {
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return [];
    }

    $cart_items = [];
    foreach ($_SESSION['cart'] as $cart_key => $item) {
        if (!is_array($item)) {
            continue;
        }

        $id = (int) ($item['id'] ?? 0);
        $qty = (int) ($item['qty'] ?? 1);
        $bat_dau = (string) ($item['bat_dau'] ?? '');
        $ket_thuc = (string) ($item['ket_thuc'] ?? '');
        $kich_co = (string) ($item['kich_co'] ?? '');

        if ($id <= 0) {
            continue;
        }

        $stmt = $conn->prepare(
            "SELECT DV_Ma AS id, DV_Ten AS ten, DV_Loai AS loai, DV_GiaThue AS gia,
                    DV_DonViTinh AS don_vi, DV_SoLuong AS so_luong, DV_MoTa AS mo_ta,
                    DV_MoTaChiTiet AS mo_ta_chi_tiet, DV_HinhAnh AS anh
             FROM dichvu
             WHERE DV_Ma = ? AND DV_DangBan = 1"
        );
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $product['cart_key'] = $cart_key; // Gắn cart_key để view giỏ hàng nhận diện chính xác
            $product['qty'] = $qty;
            $product['bat_dau'] = $bat_dau;
            $product['ket_thuc'] = $ket_thuc;
            $product['kich_co'] = $kich_co;
            $product['so_don_vi'] = tinh_so_don_vi((string) $product['don_vi'], (string) $bat_dau, (string) $ket_thuc);
            $product['subtotal'] = (int) $product['gia'] * $product['so_don_vi'] * $qty;

            $cart_items[] = $product;
        }
    }

    return $cart_items;
}

function thoi_gian_thue(string $don_vi, string $bat_dau, string $ket_thuc): ?array
{
    $tu = strtotime($bat_dau);
    $den = strtotime($ket_thuc);

    if ($tu === false || $den === false) {
        return null;
    }

    if ($don_vi === 'gio') {
        if ($den <= $tu) {
            return null;
        }
        return ['bat_dau' => date('Y-m-d H:i:s', $tu), 'ket_thuc' => date('Y-m-d H:i:s', $den)];
    }

    $ngay_nhan = date('Y-m-d', $tu);
    $ngay_tra = date('Y-m-d', $den);

    if ($ngay_tra < $ngay_nhan) {
        return null;
    }

    return [
        'bat_dau' => $ngay_nhan . ' 08:00:00',
        'ket_thuc' => $ngay_tra === $ngay_nhan ? $ngay_nhan . ' 20:00:00' : $ngay_tra . ' 08:00:00',
    ];
}

function kiem_tra_con_cho(PDO $conn, int $ma_dich_vu, string $ten, string $bat_dau, string $ket_thuc, int $so_luong): void
{
    $khoa_dong = $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';

    $stmt = $conn->prepare("SELECT DV_SoLuong FROM dichvu WHERE DV_Ma = ? AND DV_DangBan = 1" . $khoa_dong);
    $stmt->execute([$ma_dich_vu]);
    $tong_so_luong = $stmt->fetchColumn();

    if ($tong_so_luong === false) {
        throw new DomainException($ten . ' hiện không còn nhận thuê.');
    }

    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(c.CTD_SoLuong), 0)
         FROM chitietdon c
         JOIN dondatlich d ON d.DDL_Ma = c.DDL_Ma
         WHERE c.DV_Ma = ?
           AND d.DDL_TrangThai IN ('Chờ duyệt', 'Đã duyệt')
           AND c.CTD_BatDau < ? AND c.CTD_KetThuc > ?"
    );
    $stmt->execute([$ma_dich_vu, $ket_thuc, $bat_dau]);
    $da_dat = (int) $stmt->fetchColumn();

    if ($da_dat + $so_luong > (int) $tong_so_luong) {
        $con_lai = max(0, (int) $tong_so_luong - $da_dat);
        throw new DomainException($ten . ' không đủ số lượng trong khung thời gian đã chọn (còn ' . $con_lai . ').');
    }

    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM lichthue
         WHERE DV_Ma = ? AND LT_TrangThai = 'khóa' AND LT_BatDau < ? AND LT_KetThuc > ?"
    );
    $stmt->execute([$ma_dich_vu, $ket_thuc, $bat_dau]);

    if ((int) $stmt->fetchColumn() > 0) {
        throw new DomainException($ten . ' tạm ngưng nhận khách trong khung giờ đã chọn.');
    }
}

function tinh_tong_gio_hang($cart_items) {
    $subtotal = 0;

    foreach ($cart_items as $item) {
        $subtotal += $item['subtotal'] ?? ($item['gia'] * $item['qty']);
    }

    return [
        'subtotal'       => $subtotal,
        'code'           => null,
        'discount'       => 0,
        'need_ship'      => false,
        'shipping'       => 0,
        'total'          => $subtotal,
        'free_ship_from' => 0
    ];
}