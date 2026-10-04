<!--<footer>

    <div class="footer-logo">
        VIBE<span>STUDIO</span>
    </div>

    <p>© 2026 VIBE Studio. All Rights Reserved.</p>

    <a href="#">BACK TO TOP ↑</a>

</footer>
-->
<?php $js_rieng = $js_rieng ?? []; ?>

    <footer>
        <div class="footer-logo">
            VIBE<span>STUDIO</span>
        </div>

        <p>© <?= date('Y') ?> VIBE Studio. All Rights Reserved.</p>

        <a href="#">BACK TO TOP ↑</a>
    </footer>

    <script src="<?= BASE_URL ?>assets/js/common.js"></script>
    <?php foreach ($js_rieng as $file_js): ?>
        <script src="<?= htmlspecialchars($file_js) ?>"></script>
    <?php endforeach; ?>
</body>
</html>