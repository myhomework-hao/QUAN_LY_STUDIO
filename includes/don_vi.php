<?php
/**
 * Chuẩn hoá đơn vị tính của dịch vụ.
 *
 * DB đang lưu: "giờ", "ngày", "lượt" (có dấu).
 * Code cũ lại so sánh "gio", "ngay", "luot" (không dấu) nên luôn sai.
 * Mọi nơi cần so sánh đơn vị phải đi qua file này.
 */

function chuan_hoa_don_vi(string $don_vi): string
{
    $don_vi = mb_strtolower(trim($don_vi), 'UTF-8');

    $bang = [
        'gio'  => 'gio',
        'giờ'  => 'gio',
        'ngay' => 'ngay',
        'ngày' => 'ngay',
        'luot' => 'luot',
        'lượt' => 'luot',
        'bo'   => 'bo',
        'bộ'   => 'bo',
    ];

    return $bang[$don_vi] ?? $don_vi;
}

function la_don_vi_luot(string $don_vi): bool
{
    return chuan_hoa_don_vi($don_vi) === 'luot';
}

function nhan_don_vi(string $don_vi): string
{
    $nhan = [
        'gio'  => 'giờ',
        'ngay' => 'ngày',
        'luot' => 'lượt',
        'bo'   => 'bộ',
    ];

    $khoa = chuan_hoa_don_vi($don_vi);

    return $nhan[$khoa] ?? $don_vi;
}
