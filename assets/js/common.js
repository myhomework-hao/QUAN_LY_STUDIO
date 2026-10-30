
// ================================
// MENU TÀI KHOẢN
// ================================
document.addEventListener('click', function (suKien) {
    const khung = document.querySelector('.tai-khoan');
    if (!khung) return;

    const nut = khung.querySelector('.tai-khoan-nut');
    if (!nut) return;

    if (nut.contains(suKien.target)) {
        const dangMo = khung.classList.toggle('mo');
        nut.setAttribute('aria-expanded', dangMo);
    } else if (!khung.contains(suKien.target)) {
        khung.classList.remove('mo');
        nut.setAttribute('aria-expanded', 'false');
    }
});

// Đóng menu tài khoản khi nhấn Escape
document.addEventListener('keydown', function (suKien) {
    if (suKien.key !== 'Escape') return;

    const khung = document.querySelector('.tai-khoan');
    if (!khung) return;

    khung.classList.remove('mo');

    const nut = khung.querySelector('.tai-khoan-nut');
    if (nut) nut.setAttribute('aria-expanded', 'false');
});


// ================================
// NÚT BACK TO TOP
// ================================
document.addEventListener('DOMContentLoaded', function () {
    const nutLenDau = document.getElementById('backToTop');

    if (!nutLenDau) return;

    // Hiện nút khi cuộn xuống
    function capNhatNut() {
        if (window.scrollY > 300) {
            nutLenDau.classList.add('show');
        } else {
            nutLenDau.classList.remove('show');
        }
    }

    window.addEventListener('scroll', capNhatNut);

    // Cuộn lên đầu trang
    nutLenDau.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    capNhatNut();
});
