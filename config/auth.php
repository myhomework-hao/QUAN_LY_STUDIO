<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const VAI_TRO_ADMIN = 1;
const VAI_TRO_NHAN_VIEN = 2;
const VAI_TRO_KHACH_HANG = 3;

const BANG_VAI_TRO = [
    VAI_TRO_ADMIN => 'admin',
    VAI_TRO_NHAN_VIEN => 'nhanvien',
    VAI_TRO_KHACH_HANG => 'khachhang',
];
const TEN_CU_VAI_TRO = ['khach' => 'khachhang', 'vendor' => 'nhanvien'];

function da_dang_nhap(): bool
{
    return isset($_SESSION['user_id']);
}

function vai_tro_hien_tai(): string
{
    if (!da_dang_nhap()) {
        return 'guest';
    }
    $ma = isset($_SESSION['role']) ? (int) $_SESSION['role'] : -1;
    return BANG_VAI_TRO[$ma] ?? 'guest';
}

function ten_nguoi_dung_hien_tai(): string
{
    return $_SESSION['user_name'] ?? '';
}

function ma_nguoi_dung_hien_tai(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function ma_nhan_vien_hien_tai(PDO $conn): ?int
{
    if (vai_tro_hien_tai() !== 'nhanvien') {
        return null;
    }
    $cau_lenh = $conn->prepare('SELECT NV_Ma FROM nhanvien WHERE ND_Ma = ? AND NV_TrangThai = 1');
    $cau_lenh->execute([ma_nguoi_dung_hien_tai()]);
    $ma = $cau_lenh->fetchColumn();
    return $ma === false ? null : (int) $ma;
}

function chuan_hoa_vai_tro(string $vai_tro): string
{
    return TEN_CU_VAI_TRO[$vai_tro] ?? $vai_tro;
}

function yeu_cau_dang_nhap(): void
{
    if (!da_dang_nhap()) {
        header('Location: ' . BASE_URL . 'KB/login.php?tu=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function yeu_cau_vai_tro(string ...$cac_vai_tro): void
{
    yeu_cau_dang_nhap();
    $cho_phep = array_map('chuan_hoa_vai_tro', $cac_vai_tro);
    if (!in_array(vai_tro_hien_tai(), $cho_phep, true)) {
        http_response_code(403);
        die('Bạn không có quyền truy cập trang này.');
    }
}

function trang_chu_theo_vai_tro(string $vai_tro): string
{
    switch (chuan_hoa_vai_tro($vai_tro)) {
        case 'admin':
            return BASE_URL . 'TP/admin/dashboard.php';
        case 'nhanvien':
            return BASE_URL . 'AT/vendor/my-services.php';
        default:
            return BASE_URL . 'TP/index.php';
    }
}