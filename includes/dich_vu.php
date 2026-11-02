<?php

require_once __DIR__ . '/don_vi.php';

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
    $hinh_thuc = [
        'ngay' => 'Thuê theo ngày',
        'gio'  => 'Thuê theo giờ',
        'luot' => 'Thuê theo lượt',
        'bo'   => 'Thuê theo bộ',
    ];

    $khoa = chuan_hoa_don_vi($don_vi);

    return [
        'ngan'      => nhan_don_vi($don_vi),
        'hinh_thuc' => $hinh_thuc[$khoa] ?? 'Thuê',
    ];
}

function dinh_dang_gia(int $gia, string $don_vi): string
{
    return number_format($gia, 0, ',', '.') . 'đ/' . thong_tin_don_vi($don_vi)['ngan'];
}