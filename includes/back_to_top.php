<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/app.php';
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/back_to_top.css">

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