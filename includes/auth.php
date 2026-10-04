<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function da_dang_nhap(): bool
{
    return isset($_SESSION['user_id']);
}
function vai_tro_hien_tai(): string
{
    $bang_vai_tro = [0 => 'khach', 1 => 'admin', 2 => 'vendor'];
    $ma = isset($_SESSION['role']) ? (int) $_SESSION['role'] : -1;
    return $bang_vai_tro[$ma] ?? 'guest';
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
    if (!in_array(vai_tro_hien_tai(), $cac_vai_tro, true)) {
        http_response_code(403);
        die('Bạn không có quyền truy cập trang này.');
    }
}

function trang_chu_theo_vai_tro(string $vai_tro): string
{
    switch ($vai_tro) {
        case 'admin':
            return BASE_URL . 'TP/admin/dashboard.php';
        case 'vendor':
            return BASE_URL . 'AT/vendor/my-services.php';
        default:
            return BASE_URL . 'TP/index.php';
    }
}