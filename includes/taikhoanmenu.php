<?php
$vai_tro = function_exists('vai_tro_hien_tai') ? vai_tro_hien_tai() : 'guest';
$ten_hien_thi = function_exists('ten_nguoi_dung_hien_tai') ? ten_nguoi_dung_hien_tai() : '';
$da_dn = function_exists('da_dang_nhap') ? da_dang_nhap() : false;
$chu_cai_dau = $ten_hien_thi !== '' ? mb_strtoupper(mb_substr($ten_hien_thi, 0, 1)) : '?';
?>
<div class="tai-khoan">
    <?php if ($da_dn): ?>
        <button type="button" class="header-icon tai-khoan-nut da-dang-nhap vt-<?= htmlspecialchars($vai_tro) ?>"
                aria-label="Tài khoản của <?= htmlspecialchars($ten_hien_thi) ?>" aria-haspopup="true" aria-expanded="false">
            <span class="tai-khoan-chu"><?= htmlspecialchars($chu_cai_dau) ?></span>
        </button>
    <?php else: ?>
        <button type="button" class="header-icon tai-khoan-nut" aria-label="Tài khoản" aria-haspopup="true" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>
            </svg>
        </button>
    <?php endif; ?>

    <div class="tai-khoan-menu">
        <?php if ($da_dn): ?>

            <?php if ($ten_hien_thi !== ''): ?>
                <p class="tai-khoan-ten"><?= htmlspecialchars($ten_hien_thi) ?></p>
            <?php endif; ?>

            <!-- Khu vực Admin -->
            <?php if ($vai_tro === 'admin'): ?>
                <a href="<?= BASE_URL ?>admin/dashboard.php">QUẢN TRỊ</a>
            
            <!-- Khu vực Nhân viên / Vendor -->
            <?php elseif ($vai_tro === 'nhanvien'): ?>
                <a href="<?= BASE_URL ?>vendor/my-services.php">QUẢN LÝ DỊCH VỤ</a>
                <a href="<?= BASE_URL ?>vendor/vendor-orders.php">XỬ LÝ ĐƠN THUÊ</a>
            <?php endif; ?>

            <!-- Khu vực Khách hàng / Chung (đặt trong thư mục user/) -->
            <a href="<?= BASE_URL ?>user/profile.php">HỒ SƠ CỦA TÔI</a>

            <?php if ($vai_tro === 'khachhang'): ?>
                <a href="<?= BASE_URL ?>user/my-orders.php">ĐƠN HÀNG CỦA TÔI</a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>user/logout.php" class="dang-xuat">ĐĂNG XUẤT</a>

        <?php else: ?>
            <a href="<?= BASE_URL ?>user/login.php">ĐĂNG NHẬP</a>
            <a href="<?= BASE_URL ?>user/register.php">ĐĂNG KÝ</a>
        <?php endif; ?>
    </div>
</div>