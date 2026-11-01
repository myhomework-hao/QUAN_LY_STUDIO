
document.addEventListener('DOMContentLoaded', function () {

    const nutBackToTop = document.getElementById('backToTop');

    if (!nutBackToTop) return;

    // Hiện nút khi cuộn xuống
    function kiemTraViTriCuon() {
        if (window.scrollY > 300) {
            nutBackToTop.classList.add('show');
        } else {
            nutBackToTop.classList.remove('show');
        }
    }

    window.addEventListener('scroll', kiemTraViTriCuon, {
        passive: true
    });

    // Cuộn lên đầu trang
    nutBackToTop.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Kiểm tra trạng thái ban đầu
    kiemTraViTriCuon();

});
