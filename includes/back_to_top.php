<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/app.php';
}

// Version CSS theo mtime: sửa file là trình duyệt tự nạp bản mới, khỏi Ctrl+F5
$css_back_top = __DIR__ . '/../assets/css/back_to_top.css';
$phien_ban_back_top = is_file($css_back_top) ? (string) filemtime($css_back_top) : '1';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/back_to_top.css?v=<?= $phien_ban_back_top ?>">

<button
    type="button"
    id="backToTop"
    class="back-to-top"
    aria-label="Lên đầu trang"
    title="Lên đầu trang"
>
    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
</button>

<script src="<?= BASE_URL ?>assets/js/back_to_top.js" defer></script>