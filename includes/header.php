<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/QUAN_LY_STUDIO/');
}

$tieu_de_trang = $tieu_de_trang ?? 'VIBE STUDIO';
$trang_hien_tai = $trang_hien_tai ?? '';
$css_rieng = $css_rieng ?? [];

$da_dang_nhap = isset($_SESSION['user_id']);
$so_luong_gio = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tieu_de_trang) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;1,500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/common.css">
    <?php foreach ($css_rieng as $file_css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($file_css) ?>">
    <?php endforeach; ?>
</head>
<body>

    <header class="header">
     <a href="<?= BASE_URL ?>TP/index.php" class="logo">VIBE<span>STUDIO</span></a>

<nav class="navbar">
    <a href="<?= BASE_URL ?>TP/index.php" class="<?= $trang_hien_tai === 'trang-chu' ? 'active' : '' ?>">TRANG CHỦ</a>
    <a href="<?= BASE_URL ?>TP/index.php#about">GIỚI THIỆU</a>
    <a href="<?= BASE_URL ?>TP/index.php#services">DỊCH VỤ</a>
    <a href="<?= BASE_URL ?>AT/services.php" class="<?= $trang_hien_tai === 'san-pham' ? 'active' : '' ?>">SẢN PHẨM</a>
    <a href="<?= BASE_URL ?>TP/index.php#contact">LIÊN HỆ</a>
</nav>

        <div class="header-right">
            <a href="<?= BASE_URL ?>TP/search.php" class="header-icon" aria-label="Tìm kiếm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </a>

            <?php require __DIR__ . '/taikhoanmenu.php'; ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>
                </svg>
            </a>

            <a href="<?= BASE_URL ?>HH/cart.php" class="header-icon cart-icon" aria-label="Giỏ hàng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 7h12l1 13H5L6 7z"/>
                    <path d="M9 7V6a3 3 0 0 1 6 0v1"/>
                </svg>
                <span class="cart-badge"><?= $so_luong_gio ?></span>
            </a>
        </div>
    </header>