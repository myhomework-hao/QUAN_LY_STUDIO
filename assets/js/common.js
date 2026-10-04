document.addEventListener('click', function (suKien) {
    const khung = document.querySelector('.tai-khoan');
    if (!khung) return;

    const nut = khung.querySelector('.tai-khoan-nut');

    if (nut.contains(suKien.target)) {
        const dangMo = khung.classList.toggle('mo');
        nut.setAttribute('aria-expanded', dangMo);
    } else if (!khung.contains(suKien.target)) {
        khung.classList.remove('mo');
        nut.setAttribute('aria-expanded', 'false');
    }
});

document.addEventListener('keydown', function (suKien) {
    if (suKien.key !== 'Escape') return;
    const khung = document.querySelector('.tai-khoan');
    if (khung) khung.classList.remove('mo');
});