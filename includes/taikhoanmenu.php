<div class="tai-khoan">
    <button type="button" class="header-icon tai-khoan-nut" aria-label="Tài khoản" aria-haspopup="true" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>
        </svg>
    </button>

    <div class="tai-khoan-menu">
        <?php if ($da_dang_nhap): ?>
    <?php if (!empty($_SESSION['user_name'])): ?>
    <p class="tai-khoan-ten"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
<?php endif; ?>

    <?php if (vai_tro_hien_tai() === 'admin'): ?>
        <a href="<?= BASE_URL ?>TP/admin/dashboard.php">QUẢN TRỊ</a>
    <?php elseif (vai_tro_hien_tai() === 'vendor'): ?>
        <a href="<?= BASE_URL ?>AT/vendor/my-services.php">QUẢN LÝ DỊCH VỤ</a>
        <a href="<?= BASE_URL ?>HH/vendor-orders.php">ĐƠN CỦA DỊCH VỤ</a>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>KB/profile.php">HỒ SƠ CỦA TÔI</a>
    <a href="<?= BASE_URL ?>KB/my-orders.php">ĐƠN HÀNG CỦA TÔI</a>
    <a href="<?= BASE_URL ?>KB/logout.php" class="dang-xuat">ĐĂNG XUẤT</a>
<?php else: ?>
            <a href="<?= BASE_URL ?>KB/login.php">ĐĂNG NHẬP</a>
            <a href="<?= BASE_URL ?>KB/register.php">ĐĂNG KÝ</a>
        <?php endif; ?>
    </div>
</div>