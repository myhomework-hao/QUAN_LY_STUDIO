<?php

const TRANG_THAI_CHO_DUYET = 'Chờ duyệt';
const TRANG_THAI_DA_DUYET = 'Đã duyệt';
const TRANG_THAI_TU_CHOI = 'Đã từ chối';
const TRANG_THAI_DA_HUY = 'Đã hủy';
const TRANG_THAI_HOAN_THANH = 'Hoàn thành';

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