
<?php
$js_rieng = $js_rieng ?? [];
?>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
    <div class="footer-logo">
        <strong>VIBE</strong><span>STUDIO</span>
    </div>

    <p class="footer-copyright">
        © <?= date('Y') ?> VIBE Studio. All Rights Reserved.
    </p>
</footer>


<!-- ===== JAVASCRIPT DÙNG CHUNG ===== -->
<script src="<?= BASE_URL ?>assets/js/common.js"></script>


<!-- ===== JAVASCRIPT RIÊNG TỪNG TRANG ===== -->
<?php foreach ($js_rieng as $file_js): ?>
    <script src="<?= htmlspecialchars($file_js, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>


<!-- ===== BACK TO TOP ===== -->
<?php require_once __DIR__ . '/back_to_top.php'; ?>


<!-- ===== CHAT WIDGET ===== -->
<?php require_once __DIR__ . '/chat_widget.php'; ?>


</body>
</html>
