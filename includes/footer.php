<?php $js_rieng = $js_rieng ?? []; ?>

<footer>
    <div class="footer-logo">
        VIBE<span>STUDIO</span>
    </div>
    <p>© <?= date('Y') ?> VIBE Studio. All Rights Reserved.</p>
</footer>

<button
    type="button"
    id="backToTop"
    class="back-to-top"
    aria-label="Lên đầu trang"
    title="Lên đầu trang"
>
    <i class="fa-solid fa-arrow-up"></i>
</button>

<script src="<?= BASE_URL ?>assets/js/common.js"></script>

<?php foreach ($js_rieng as $file_js): ?>
    <script src="<?= htmlspecialchars($file_js) ?>"></script>
<?php endforeach; ?>

<?php require_once __DIR__ . '/chat_widget.php'; ?>

</body>
</html>