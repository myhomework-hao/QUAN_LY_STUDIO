// Nút "về đầu trang": luôn hiện ở góc phải, bấm là cuộn mượt lên đầu trang.
(function () {
    var nut = document.getElementById('back-to-top');
    if (!nut) return;
 
    nut.addEventListener('click', function () {
        var giamChuyenDong = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top: 0, behavior: giamChuyenDong ? 'auto' : 'smooth' });
    });
})();
 

