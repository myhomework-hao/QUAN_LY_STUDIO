<?php
require_once __DIR__ . '/don_vi.php';

// Dịch vụ tính theo lượt: tạm dùng khung 1 giờ để vẫn kiểm tra được trùng lịch.
const THOI_LUONG_LUOT_GIAY = 3600;

function tinh_so_don_vi(string $don_vi, string $bat_dau, string $ket_thuc): int
{
    $don_vi = chuan_hoa_don_vi($don_vi);

    if ($don_vi === 'bo' || $don_vi === 'luot') {
        return 1;
    }

    $tu = strtotime($bat_dau);
    $den = strtotime($ket_thuc);

    if ($tu === false || $den === false || $den <= $tu) {
        return 1;
    }

    $diff_seconds = $den - $tu;

    // Nếu là giờ: tính chuẩn xác số giờ chênh lệch (làm tròn lên mỗi giờ)
    if ($don_vi === 'gio') {
        return max(1, (int) ceil($diff_seconds / 3600));
    }

    // Nếu là ngày: giữ nguyên công thức tính ngày
    return max(1, (int) round($diff_seconds / 86400));
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
            $product['cart_key'] = $cart_key;
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
    $don_vi = chuan_hoa_don_vi($don_vi);
    $tu = strtotime($bat_dau);

    if ($tu === false) {
        return null;
    }

    // Dịch vụ tính theo LƯỢT: khách chỉ chọn một mốc thời điểm.
    // Mở rộng thành khung 1 giờ để vẫn kiểm tra được trùng lịch.
    if ($don_vi === 'luot') {
        return [
            'bat_dau'  => date('Y-m-d H:i:s', $tu),
            'ket_thuc' => date('Y-m-d H:i:s', $tu + THOI_LUONG_LUOT_GIAY),
        ];
    }

    $den = strtotime($ket_thuc);

    if ($den === false || $den <= $tu) {
        return null;
    }

    // Dịch vụ tính theo GIỜ: Cho phép chọn linh hoạt mọi khung giờ (ví dụ 8:00 -> 9:00, hoặc 8:00 -> 20:00)
    if ($don_vi === 'gio') {
        return [
            'bat_dau'  => date('Y-m-d H:i:s', $tu),
            'ket_thuc' => date('Y-m-d H:i:s', $den),
        ];
    }

    // Dịch vụ tính theo NGÀY / BỘ: giữ đúng giờ khách đã chọn trên form.
    return [
        'bat_dau' => date('Y-m-d H:i:s', $tu),
        'ket_thuc' => date('Y-m-d H:i:s', $den),
    ];
}

function kiem_tra_con_cho(PDO $conn, int $ma_dich_vu, string $ten, string $bat_dau, string $ket_thuc, int $so_luong, string $kich_co = '', int $ma_don_bo_qua = 0): void
{
    $khoa_dong = $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
    // Khi kiểm tra lại tồn kho lúc DUYỆT, bản thân đơn đang duyệt đã nằm ở chitietdon
    // ('Chờ duyệt'); phải loại nó khỏi số đã đặt, nếu không sẽ bị đếm hai lần.
    $bo_qua = $ma_don_bo_qua > 0 ? ' AND d.DDL_Ma <> ?' : '';

    // Khóa dòng dịch vụ trước để hai đơn đặt cùng lúc xếp hàng chờ theo thứ tự.
    $stmt = $conn->prepare("SELECT DV_SoLuong FROM dichvu WHERE DV_Ma = ? AND DV_DangBan = 1" . $khoa_dong);
    $stmt->execute([$ma_dich_vu]);
    $tong_so_luong = $stmt->fetchColumn();

    if ($tong_so_luong === false) {
        throw new DomainException($ten . ' hiện không còn nhận thuê.');
    }

    // Trang phục có size: tồn kho tính theo dichvu_kichco của đúng size, không theo tổng dịch vụ.
    if ($kich_co !== '') {
        $stmt = $conn->prepare(
            "SELECT DVK_SoLuong FROM dichvu_kichco WHERE DV_Ma = ? AND DVK_Ten = ? AND DVK_DangHoatDong = 1" . $khoa_dong
        );
        $stmt->execute([$ma_dich_vu, $kich_co]);
        $tong_theo_size = $stmt->fetchColumn();

        if ($tong_theo_size === false) {
            throw new DomainException($ten . ' không còn nhận thuê size ' . $kich_co . '.');
        }

        $tong_so_luong = $tong_theo_size;
    }

    // Đếm số lượng đã bị các đơn Chờ duyệt/Đã duyệt chiếm trong khung giờ giao nhau.
    if ($kich_co !== '') {
        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(c.CTD_SoLuong), 0)
             FROM chitietdon c
             JOIN dondatlich d ON d.DDL_Ma = c.DDL_Ma
             WHERE c.DV_Ma = ? AND c.CTD_KichCo = ?
               AND d.DDL_TrangThai IN ('Chờ duyệt', 'Đã duyệt')" . $bo_qua . "
               AND c.CTD_BatDau < ? AND c.CTD_KetThuc > ?"
        );
        $tham_so = [$ma_dich_vu, $kich_co];
        if ($ma_don_bo_qua > 0) { $tham_so[] = $ma_don_bo_qua; }
        array_push($tham_so, $ket_thuc, $bat_dau);
        $stmt->execute($tham_so);
    } else {
        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(c.CTD_SoLuong), 0)
             FROM chitietdon c
             JOIN dondatlich d ON d.DDL_Ma = c.DDL_Ma
             WHERE c.DV_Ma = ?
               AND d.DDL_TrangThai IN ('Chờ duyệt', 'Đã duyệt')" . $bo_qua . "
               AND c.CTD_BatDau < ? AND c.CTD_KetThuc > ?"
        );
        $tham_so = [$ma_dich_vu];
        if ($ma_don_bo_qua > 0) { $tham_so[] = $ma_don_bo_qua; }
        array_push($tham_so, $ket_thuc, $bat_dau);
        $stmt->execute($tham_so);
    }
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
