
/* ========================================
   VIBE STUDIO - COMMON JS
======================================== */


/* ========================================
   1. MENU TÀI KHOẢN
======================================== */

function khoiTaoMenuTaiKhoan() {
    if (window.vibeMenuTaiKhoanDaKhoiTao) return;
    window.vibeMenuTaiKhoanDaKhoiTao = true;

    document.addEventListener('click', function (suKien) {
        const khung = document.querySelector('.tai-khoan');

        if (!khung) return;

        const nut = khung.querySelector('.tai-khoan-nut');

        if (!nut) return;

        if (nut.contains(suKien.target)) {
            const dangMo = khung.classList.toggle('mo');

            nut.setAttribute('aria-expanded', String(dangMo));
        } else if (!khung.contains(suKien.target)) {
            khung.classList.remove('mo');

            nut.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', function (suKien) {
        if (suKien.key !== 'Escape') return;

        const khung = document.querySelector('.tai-khoan');

        if (!khung) return;

        khung.classList.remove('mo');

        const nut = khung.querySelector('.tai-khoan-nut');

        if (nut) {
            nut.setAttribute('aria-expanded', 'false');
        }
    });
}



/* ========================================
   3. KHỞI TẠO
   Hỗ trợ cả khi DOM chưa tải xong
   hoặc đã tải xong.
======================================== */

function khoiTaoChucNangChung() {
    khoiTaoMenuTaiKhoan();
    khoiTaoBackToTop();
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        khoiTaoChucNangChung,
        { once: true }
    );
} else {
    khoiTaoChucNangChung();
}
