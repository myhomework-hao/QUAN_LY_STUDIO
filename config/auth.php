<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const BANG_VAI_TRO = [0 => 'khachhang', 1 => 'admin', 2 => 'nhanvien'];
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