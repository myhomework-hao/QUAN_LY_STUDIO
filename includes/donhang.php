<?php

const TRANG_THAI_CHO_DUYET = 'Chờ duyệt';
const TRANG_THAI_DA_DUYET = 'Đã duyệt';
const TRANG_THAI_TU_CHOI = 'Đã từ chối';
const TRANG_THAI_DA_HUY = 'Đã hủy';
const TRANG_THAI_HOAN_THANH = 'Hoàn thành';

// Trạng thái hoàn tiền lưu trên dondatlich (cột DDL_TrangThaiHoanTien)
const HOAN_TIEN_KHONG = 'Không';
const HOAN_TIEN_CHO = 'Chờ hoàn tiền';
const HOAN_TIEN_DA = 'Đã hoàn tiền';

function ma_don_hien_thi(int $ma_don): string
{
    return 'DDL-' . str_pad((string) $ma_don, 5, '0', STR_PAD_LEFT);
}

function lop_trang_thai(string $trang_thai): string
{
    switch ($trang_thai) {
        case TRANG_THAI_CHO_DUYET:
            return 'cho';
        case TRANG_THAI_DA_DUYET:
            return 'duyet';
        case TRANG_THAI_HOAN_THANH:
            return 'xong';
        default:
            return 'huy';
    }
}

function ten_loai_dich_vu(string $loai): string
{
    return [
        'phong_chup' => 'Phòng chụp',
        'thiet_bi' => 'Máy ảnh - thiết bị',
        'trang_phuc' => 'Trang phục',
        'phu_kien' => 'Phụ kiện - đạo cụ',
    ][$loai] ?? $loai;
}

function dinh_dang_tien($so): string
{
    return number_format((float) $so, 0, ',', '.') . '₫';
}

function dinh_dang_ngay_gio(?string $thoi_diem): string
{
    return $thoi_diem ? date('d/m/Y H:i', strtotime($thoi_diem)) : '';
}

function duong_dan_anh(?string $duong_dan): string
{
    if (!$duong_dan) {
        return '';
    }
    if (preg_match('#^(https?:)?//#', $duong_dan)) {
        return $duong_dan;
    }
    if (strpos($duong_dan, '/') === false) {
        return BASE_URL . 'assets/uploads/' . $duong_dan;
    }
    return BASE_URL . ltrim($duong_dan, '/');
}

// Tính tổng số tiền đã thu thành công của đơn (không tính bản ghi hoàn tiền).
function tong_da_thu_don(PDO $conn, int $ma_don): float
{
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(TT_SoTien), 0)
         FROM thanhtoan
         WHERE DDL_Ma = ? AND TT_TrangThai = 'Thành công' AND TT_Loai <> 'Hoàn tiền'"
    );
    $stmt->execute([$ma_don]);
    return (float) $stmt->fetchColumn();
}

// Phát sinh trạng thái 'Chờ hoàn tiền' cho đơn đã có khoản thu (gọi trong transaction).
// Trả về mảng: ['co_hoan_tien' => bool, 'so_tien' => float, 'da_phat_sinh' => bool]
function phat_sinh_cho_hoan_tien(PDO $conn, int $ma_don): array
{
    $khoa = $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
    $stmt = $conn->prepare(
        "SELECT DDL_MaGiamGia, DDL_TrangThaiHoanTien FROM dondatlich WHERE DDL_Ma = ?" . $khoa
    );
    $stmt->execute([$ma_don]);
    $don = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$don) {
        return ['co_hoan_tien' => false, 'so_tien' => 0.0, 'da_phat_sinh' => false];
    }

    $tt_hoan = (string) ($don['DDL_TrangThaiHoanTien'] ?? HOAN_TIEN_KHONG);
    $tong_da_thu = tong_da_thu_don($conn, $ma_don);

    // Đã ở trạng thái chờ/đã hoàn: không phát sinh lại, không trả mã lần nữa.
    if ($tt_hoan === HOAN_TIEN_CHO || $tt_hoan === HOAN_TIEN_DA) {
        return ['co_hoan_tien' => true, 'so_tien' => $tong_da_thu, 'da_phat_sinh' => false];
    }

    if ($tong_da_thu <= 0) {
        return ['co_hoan_tien' => false, 'so_tien' => 0.0, 'da_phat_sinh' => false];
    }

    $stmt = $conn->prepare(
        "UPDATE dondatlich SET DDL_TrangThaiHoanTien = ?, DDL_SoTienHoan = ? WHERE DDL_Ma = ?"
    );
    $stmt->execute([HOAN_TIEN_CHO, $tong_da_thu, $ma_don]);

    // Trả 1 lượt mã giảm giá (nếu đơn có dùng mã) ngay khi chuyển sang chờ hoàn.
    $ma_giam_gia = trim((string) ($don['DDL_MaGiamGia'] ?? ''));
    if ($ma_giam_gia !== '') {
        $stmt = $conn->prepare(
            "UPDATE ma_giam_gia SET MG_DaDung = CASE WHEN MG_DaDung > 0 THEN MG_DaDung - 1 ELSE 0 END WHERE MG_Code = ?"
        );
        $stmt->execute([$ma_giam_gia]);
    }

    return ['co_hoan_tien' => true, 'so_tien' => $tong_da_thu, 'da_phat_sinh' => true];
}