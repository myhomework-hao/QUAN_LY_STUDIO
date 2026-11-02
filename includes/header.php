<?php
// Gọi app.php và auth.php nằm ở thư mục cha /config/
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';
 
$tieu_de_trang = $tieu_de_trang ?? 'VIBE STUDIO';
$trang_hien_tai = $trang_hien_tai ?? '';
$css_rieng = $css_rieng ?? [];
 
$da_dang_nhap = da_dang_nhap();
$so_luong_gio = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $mon_hang) {
        if (is_array($mon_hang)) {
            $so_luong_gio += (int) ($mon_hang['qty'] ?? 0);
        }
    }
}
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

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/common.css?v=2">

    <?php $tep_theme = __DIR__ . '/../assets/css/vibe-theme.css'; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/vibe-theme.css?v=<?= is_file($tep_theme) ? filemtime($tep_theme) : 1 ?>">
    <!-- Thư viện icon dùng chung -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <?php foreach ($css_rieng as $file_css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($file_css) ?>">
    <?php endforeach; ?>

    <style>
        /* Tinh chỉnh căn chỉnh Navbar */
        .header .navbar {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header .navbar a {
            font-size: 12px;
            letter-spacing: 1.5px;
            font-weight: 500;
            color: #666666;
            padding: 8px 18px;
            border-radius: 30px; /* Tạo hình bo tròn viền dạng Pill */
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: inline-block;
        }
        
        /* HIỆU ỨNG 1: Trỏ chuột vào chữ sẽ nổi lên nhẹ và đậm màu hơn */
        .header .navbar a:hover {
            color: #111111;
            transform: translateY(-2px);
        }

        /* HIỆU ỨNG 2: Khi click/đang ở trang đó (Active) -> Tô tròn đen bao phủ nguyên chữ */
        .header .navbar a.active {
            background-color: #111111 !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); /* Đổ bóng nhẹ tạo chiều sâu */
            transform: translateY(0);
        }
    </style>
</head>
<body>
 
    <header class="header">
        <a href="<?= BASE_URL ?>index.php" class="logo">VIBE<span>STUDIO</span></a>
 
        <nav class="navbar">
            <a href="<?= BASE_URL ?>index.php" class="<?= $trang_hien_tai === 'trang-chu' ? 'active' : '' ?>">TRANG CHỦ</a>
            <a href="<?= BASE_URL ?>user/services.php" class="<?= $trang_hien_tai === 'san-pham' ? 'active' : '' ?>">SẢN PHẨM</a>
            <a href="<?= BASE_URL ?>user/policy.php" class="<?= $trang_hien_tai === 'chinh-sach' ? 'active' : '' ?>">CHÍNH SÁCH</a>
        </nav>
 
        <div class="header-right">
            <a href="<?= BASE_URL ?>includes/search.php" class="header-icon" data-search-open aria-label="Tìm kiếm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </a>
 
            <?php require __DIR__ . '/taikhoanmenu.php'; ?>
 
            <!-- Trỏ đúng vào thư mục user/cart.php -->
            <a href="<?= BASE_URL ?>user/cart.php" class="header-icon cart-icon" aria-label="Giỏ hàng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 7h12l1 13H5L6 7z"/>
                    <path d="M9 7V6a3 3 0 0 1 6 0v1"/>
                </svg>
                <span class="cart-badge"><?= $so_luong_gio ?></span>
            </a>
        </div>
    </header>
 
    <?php require __DIR__ . '/search_popup.php'; ?>
 
<script>
    const IS_LOGGED_IN = <?= !empty($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>     