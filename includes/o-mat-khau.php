<?php
function o_mat_khau(string $id, string $ten, string $nhan, array $tuy_chon = []): void
{
    static $da_in_script = false;

    $goi_y = $tuy_chon['goi_y'] ?? '';
    $tu_dong_dien = $tuy_chon['autocomplete'] ?? 'current-password';
    $loi = $tuy_chon['loi'] ?? '';
    ?>
    <label for="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($nhan) ?></label>
    <div class="o-mat-khau">
        <input type="password" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($ten) ?>"
               placeholder="<?= htmlspecialchars($goi_y) ?>" autocomplete="<?= htmlspecialchars($tu_dong_dien) ?>"
               class="<?= $loi !== '' ? 'co-loi' : '' ?>" <?= $loi !== '' ? 'aria-invalid="true"' : '' ?>
               <?= !empty($tuy_chon['autofocus']) ? 'autofocus' : '' ?> required>
        <button type="button" class="nut-mat" data-dich="<?= htmlspecialchars($id) ?>"
                aria-pressed="false" aria-label="Hiện mật khẩu" title="Hiện mật khẩu">
            <svg class="bieu-tuong-hien" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>
            </svg>
            <svg class="bieu-tuong-an" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        </button>
    </div>
    <?php if ($loi !== '') : ?><span class="loi-truong" role="alert"><?= htmlspecialchars($loi) ?></span><?php endif; ?>
    <?php
    if (!$da_in_script) {
        $da_in_script = true;
        ?>
<style>
.o-mat-khau { position: relative; }
.o-mat-khau input { padding-right: 48px; }
.o-mat-khau .nut-mat {
    position: absolute; top: 0; right: 0; width: 44px; height: 100%; margin: 0; padding: 0;
    display: flex; align-items: center; justify-content: center;
    background: none; color: #666666; border: 0; border-radius: 0 8px 8px 0; cursor: pointer;
}
.o-mat-khau .nut-mat:hover { background: none; color: #111111; }
.o-mat-khau .nut-mat svg { width: 20px; height: 20px; }
.o-mat-khau .bieu-tuong-an { display: none; }
.o-mat-khau .nut-mat[aria-pressed="true"] .bieu-tuong-hien { display: none; }
.o-mat-khau .nut-mat[aria-pressed="true"] .bieu-tuong-an { display: block; }
.loi-truong { display: block; margin-top: 6px; font-size: 12px; color: #b3261e; }
.co-loi { border-color: #b3261e !important; }
</style>
<script>
document.addEventListener('click', function (e) {
    var nut = e.target.closest('.nut-mat');
    if (!nut) { return; }
    var o = document.getElementById(nut.dataset.dich);
    var dangAn = o.type === 'password';
    o.type = dangAn ? 'text' : 'password';
    nut.setAttribute('aria-pressed', dangAn ? 'true' : 'false');
    nut.setAttribute('aria-label', dangAn ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    nut.title = nut.getAttribute('aria-label');
});
</script>
<?php
    }
}