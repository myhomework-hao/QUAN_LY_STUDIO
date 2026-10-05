<?php

function h(?string $chuoi): string
{
    return htmlspecialchars($chuoi ?? '', ENT_QUOTES, 'UTF-8');
}

function thong_tin_loai(string $loai): array
{
    $bang = [
        'may_anh'    => ['nhan' => 'MÁY ẢNH',    'ten' => 'Máy ảnh'],
        'studio'     => ['nhan' => 'STUDIO',     'ten' => 'Chỗ chụp ảnh'],
        'trang_phuc' => ['nhan' => 'TRANG PHỤC', 'ten' => 'Bộ đồ'],
        'phu_kien'   => ['nhan' => 'PHỤ KIỆN',   'ten' => 'Phụ kiện'],
        'tho_chup'   => ['nhan' => 'THỢ CHỤP',   'ten' => 'Thợ chụp'],
    ];
    return $bang[$loai] ?? ['nhan' => strtoupper($loai), 'ten' => $loai];
}

function thong_tin_don_vi(string $don_vi): array
{
    $bang = [
        'ngay' => ['ngan' => 'ngày', 'hinh_thuc' => 'Thuê theo ngày'],
        'gio'  => ['ngan' => 'giờ',  'hinh_thuc' => 'Thuê theo giờ'],
        'bo'   => ['ngan' => 'bộ',   'hinh_thuc' => 'Thuê theo bộ'],
    ];
    return $bang[$don_vi] ?? ['ngan' => $don_vi, 'hinh_thuc' => 'Thuê'];
}

function dinh_dang_gia(int $gia, string $don_vi): string
{
    return number_format($gia, 0, ',', '.') . 'đ/' . thong_tin_don_vi($don_vi)['ngan'];
}