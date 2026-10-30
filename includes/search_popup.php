<?php
/*
 * POPUP TÌM KIẾM - nhúng trong header.php nên trang nào cũng dùng được.
 * Bản này chứa luôn CSS và JS bên trong (không phụ thuộc đường dẫn assets/), chỉ cần file này và header.php.
 * Bấm icon kính lúp -> popup mở ngay tại trang hiện tại, gõ ký tự nào hiện kết quả ký tự đó.
 */
?>
<!-- search-popup-loaded -->
<style>
/* =========================================================
   POPUP TÌM KIẾM (Trắng - Đen)
========================================================= */
 
html.search-open {
    overflow: hidden;
}
 
.search-popup {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 12vh 20px 20px;
}
 
.search-popup[hidden] {
    display: none;
}
 
.search-popup-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(17, 17, 17, 0.5);
    backdrop-filter: blur(3px);
    animation: search-fade 0.2s ease;
}
 
.search-popup-box {
    position: relative;
    width: 100%;
    max-width: 680px;
    max-height: 76vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.3);
    animation: search-pop 0.22s ease;
}
 
/* ---------- Ô nhập ---------- */
 
.search-popup-form {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 12px 0 22px;
    height: 64px;
    flex-shrink: 0;
    border-bottom: 1px solid #eeeeee;
}
 
.search-popup-icon {
    flex-shrink: 0;
    color: #111111;
}
 
.search-popup-input {
    flex: 1;
    min-width: 0;
    height: 100%;
    border: none;
    outline: none;
    background: transparent;
    color: #111111;
    font: inherit;
    font-size: 15px;
}
 
.search-popup-input::placeholder {
    color: #999999;
}
 
.search-popup-close {
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #111111;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
    transition: background 0.2s ease;
}
 
.search-popup-close:hover {
    background: #f0f0f0;
}
 
/* thanh chạy mảnh phía dưới ô nhập khi đang tải */
.search-popup-box.is-loading .search-popup-form {
    position: relative;
}
 
.search-popup-box.is-loading .search-popup-form::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -1px;
    height: 2px;
    width: 30%;
    background: #111111;
    animation: search-load 0.9s ease-in-out infinite;
}
 
/* ---------- Danh sách kết quả ---------- */
 
.search-popup-body {
    overflow-y: auto;
}
 
.search-popup-hint {
    padding: 28px 22px;
    color: #888888;
    font-size: 13px;
    text-align: center;
}
 
.search-popup-item {
    display: grid;
    grid-template-columns: 56px 1fr;
    align-items: center;
    gap: 16px;
    padding: 12px 22px;
    color: #111111;
    text-decoration: none;
    border-bottom: 1px solid #f2f2f2;
}
 
.search-popup-item:hover,
.search-popup-item.is-active {
    background: #f5f5f5;
}
 
.search-popup-item img {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 8px;
    background: #f4f4f4;
}
 
.search-popup-name {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.35;
}
 
.search-popup-name mark {
    padding: 0 1px;
    border-radius: 2px;
    background: #111111;
    color: #ffffff;
}
 
.search-popup-meta {
    margin-top: 3px;
    font-size: 12px;
    color: #777777;
}
 
.search-popup-all {
    display: block;
    padding: 16px;
    color: #111111;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
}
 
.search-popup-all:hover {
    background: #f5f5f5;
}
 
/* ---------- Trang kết quả đầy đủ (includes/search.php) ---------- */
 
.search-result-title {
    margin-bottom: 28px;
    font-size: 24px;
    font-weight: 600;
    color: #111111;
}
 
.search-result-card {
    color: inherit;
    text-decoration: none;
}
 
/* ---------- Hiệu ứng & responsive ---------- */
 
@keyframes search-fade {
    from { opacity: 0; }
    to   { opacity: 1; }
}
 
@keyframes search-pop {
    from { opacity: 0; transform: translateY(-12px) scale(0.98); }
    to   { opacity: 1; transform: none; }
}
 
@keyframes search-load {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(340%); }
}
 
@media (max-width: 600px) {
    .search-popup {
        padding: 10px;
    }
 
    .search-popup-box {
        max-height: 88vh;
    }
 
    .search-popup-item {
        padding: 12px 16px;
    }
}
 
@media (prefers-reduced-motion: reduce) {
    .search-popup-backdrop,
    .search-popup-box,
    .search-popup-box.is-loading .search-popup-form::after {
        animation: none;
    }
}
</style>
 
<div class="search-popup" id="search-popup" hidden>
    <div class="search-popup-backdrop" data-search-close></div>
 
    <div class="search-popup-box" role="dialog" aria-modal="true" aria-label="Tìm kiếm sản phẩm"
         data-endpoint="<?= BASE_URL ?>includes/search.php">
 
        <form class="search-popup-form" action="<?= BASE_URL ?>includes/search.php" method="get" role="search" autocomplete="off">
            <svg class="search-popup-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path>
            </svg>
            <input type="text" name="q" class="search-popup-input"
                   placeholder="Tìm máy ảnh, chỗ chụp, bộ đồ, phụ kiện..."
                   maxlength="60" inputmode="search" spellcheck="false"
                   role="combobox" aria-label="Tìm kiếm sản phẩm"
                   aria-autocomplete="list" aria-controls="search-popup-results">
            <button type="button" class="search-popup-close" data-search-close aria-label="Đóng tìm kiếm">&times;</button>
        </form>
 
        <div class="search-popup-body" id="search-popup-results" role="listbox">
            <p class="search-popup-hint">Gõ tên máy ảnh, chỗ chụp, bộ đồ hoặc phụ kiện để tìm.</p>
        </div>
    </div>
</div>
 
<script>
// POPUP TÌM KIẾM: bấm icon kính lúp ([data-search-open]) -> mở popup ngay tại trang đang xem,
// gõ từng ký tự là gọi includes/search.php?format=json và hiện kết quả luôn trong popup.
(function () {
    'use strict';
 
    // Đường dẫn gốc của site, suy ra từ chính địa chỉ file search.js (…/assets/js/search.js)
    var script = document.currentScript;
    var base = script && script.src ? script.src.split('assets/js/')[0] : '/';
    var searchUrl = base + 'includes/search.php';
 
    // Nếu header chưa có sẵn khung popup thì tự tạo, để chỉ cần nạp search.js là chạy
    function buildPopup() {
        if (!document.querySelector('link[href*="assets/css/search.css"]')) {
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = base + 'assets/css/search.css';
            document.head.appendChild(css);
        }
 
        var wrap = document.createElement('div');
        wrap.className = 'search-popup';
        wrap.id = 'search-popup';
        wrap.hidden = true;
        wrap.innerHTML =
            '<div class="search-popup-backdrop" data-search-close></div>' +
            '<div class="search-popup-box" role="dialog" aria-modal="true" aria-label="Tìm kiếm sản phẩm" data-endpoint="' + searchUrl + '">' +
            '<form class="search-popup-form" action="' + searchUrl + '" method="get" role="search" autocomplete="off">' +
            '<svg class="search-popup-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path></svg>' +
            '<input type="text" name="q" class="search-popup-input" placeholder="Tìm máy ảnh, chỗ chụp, bộ đồ, phụ kiện..." maxlength="60" inputmode="search" spellcheck="false" role="combobox" aria-label="Tìm kiếm sản phẩm" aria-autocomplete="list" aria-controls="search-popup-results">' +
            '<button type="button" class="search-popup-close" data-search-close aria-label="Đóng tìm kiếm">&times;</button>' +
            '</form>' +
            '<div class="search-popup-body" id="search-popup-results" role="listbox">' +
            '<p class="search-popup-hint">Gõ tên máy ảnh, chỗ chụp, bộ đồ hoặc phụ kiện để tìm.</p>' +
            '</div></div>';
        document.body.appendChild(wrap);
        return wrap;
    }
 
    var popup = document.getElementById('search-popup') || buildPopup();
 
    var box = popup.querySelector('.search-popup-box');
    var form = popup.querySelector('.search-popup-form');
    var input = popup.querySelector('.search-popup-input');
    var body = popup.querySelector('.search-popup-body');
    var endpoint = box.getAttribute('data-endpoint');
    var pageUrl = form.getAttribute('action');
    var hintHtml = body.innerHTML;
 
    var DELAY = 120;           // ms chờ sau ký tự cuối, tránh gọi server quá dày khi gõ nhanh
    var timer = null;
    var controller = null;
    var items = [];
    var active = -1;
    var lastFocus = null;
 
    /* ---------- Mở / đóng ---------- */
 
    function isOpen() {
        return !popup.hidden;
    }
 
    function openPopup() {
        if (isOpen()) return;
        lastFocus = document.activeElement;
        popup.hidden = false;
        document.documentElement.classList.add('search-open');
        input.setAttribute('aria-expanded', 'true');
        window.requestAnimationFrame(function () {
            input.focus();
            input.select();
        });
    }
 
    function closePopup() {
        if (!isOpen()) return;
        popup.hidden = true;
        document.documentElement.classList.remove('search-open');
        input.setAttribute('aria-expanded', 'false');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
 
    // Icon kính lúp trên header: nhận cả link có data-search-open lẫn link tìm kiếm cũ (…/search.php, không kèm ?q=)
    var OPENER = 'a[data-search-open], a.header-icon[aria-label="Tìm kiếm"], a[href$="includes/search.php"], a[href$="/search.php"]';
 
    // Dùng capture để chặn việc chuyển trang trước khi script khác kịp xử lý
    document.addEventListener('click', function (e) {
        var opener = e.target.closest(OPENER);
        if (opener) {
            if (e.ctrlKey || e.metaKey || e.shiftKey) return;   // cho phép mở tab mới như link thường
            e.preventDefault();
            e.stopPropagation();
            openPopup();
            return;
        }
        if (e.target.closest('[data-search-close]')) {
            closePopup();
        }
    }, true);
 
    document.addEventListener('keydown', function (e) {
        if (!isOpen()) return;
 
        if (e.key === 'Escape') {
            e.preventDefault();
            closePopup();
            return;
        }
 
        // Giữ phím Tab quay vòng trong popup
        if (e.key === 'Tab') {
            var focusables = box.querySelectorAll('input, button, a[href]');
            if (!focusables.length) return;
            var first = focusables[0];
            var last = focusables[focusables.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });
 
    /* ---------- Hiển thị kết quả ---------- */
 
    // Bỏ dấu nhưng giữ nguyên độ dài chuỗi, để tô sáng đúng vị trí ("may" khớp "Máy")
    function plain(s) {
        s = s.normalize('NFC');
        var out = '';
        for (var i = 0; i < s.length; i++) {
            var c = s.charAt(i);
            out += (c === 'đ' || c === 'Đ') ? 'd' : c.normalize('NFD').charAt(0).toLowerCase();
        }
        return out;
    }
 
    function highlight(el, text, q) {
        text = text.normalize('NFC');
        var pq = plain(q);
        var i = pq ? plain(text).indexOf(pq) : -1;
        if (i < 0) {
            el.textContent = text;
            return;
        }
        var m = document.createElement('mark');
        m.textContent = text.slice(i, i + pq.length);
        el.appendChild(document.createTextNode(text.slice(0, i)));
        el.appendChild(m);
        el.appendChild(document.createTextNode(text.slice(i + pq.length)));
    }
 
    function setActive(i) {
        var nodes = body.querySelectorAll('.search-popup-item');
        active = i;
        nodes.forEach(function (n, idx) {
            var on = idx === i;
            n.classList.toggle('is-active', on);
            n.setAttribute('aria-selected', on ? 'true' : 'false');
            if (on) n.scrollIntoView({ block: 'nearest' });
        });
    }
 
    function message(text) {
        body.innerHTML = '';
        var p = document.createElement('p');
        p.className = 'search-popup-hint';
        p.textContent = text;
        body.appendChild(p);
        items = [];
        active = -1;
    }
 
    function render(q, list) {
        items = list;
        active = -1;
 
        if (!list.length) {
            message('Không tìm thấy sản phẩm nào cho “' + q + '”.');
            return;
        }
 
        body.innerHTML = '';
 
        list.forEach(function (it) {
            var a = document.createElement('a');
            a.className = 'search-popup-item';
            a.href = it.link;
            a.setAttribute('role', 'option');
 
            var img = document.createElement('img');
            img.src = it.anh;
            img.alt = '';
            img.loading = 'lazy';
 
            var info = document.createElement('div');
            var name = document.createElement('p');
            name.className = 'search-popup-name';
            highlight(name, it.ten, q);
 
            var meta = document.createElement('p');
            meta.className = 'search-popup-meta';
            meta.textContent = it.loai + ' · ' + it.gia;
 
            info.appendChild(name);
            info.appendChild(meta);
            a.appendChild(img);
            a.appendChild(info);
            body.appendChild(a);
        });
 
        var all = document.createElement('a');
        all.className = 'search-popup-all';
        all.href = pageUrl + '?q=' + encodeURIComponent(q);
        all.textContent = 'Xem tất cả kết quả cho “' + q + '” →';
        body.appendChild(all);
    }
 
    /* ---------- Gọi server ---------- */
 
    function search(q) {
        if (controller) controller.abort();          // bỏ request cũ, chỉ giữ kết quả của ký tự mới nhất
        controller = new AbortController();
        box.classList.add('is-loading');
 
        fetch(endpoint + '?format=json&q=' + encodeURIComponent(q), {
            signal: controller.signal,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                box.classList.remove('is-loading');
                if (input.value.trim() !== q) return;    // ô đã đổi nội dung trong lúc chờ
                render(q, data.items || []);
            })
            .catch(function (err) {
                if (err.name === 'AbortError') return;
                box.classList.remove('is-loading');
                message('Không tải được kết quả, vui lòng thử lại.');
            });
    }
 
    input.addEventListener('input', function () {
        var q = input.value.trim();
        clearTimeout(timer);
 
        if (q === '') {
            if (controller) controller.abort();
            box.classList.remove('is-loading');
            body.innerHTML = hintHtml;
            items = [];
            active = -1;
            return;
        }
        timer = setTimeout(function () { search(q); }, DELAY);
    });
 
    input.addEventListener('keydown', function (e) {
        var n = items.length;
 
        if (e.key === 'ArrowDown' && n) {
            e.preventDefault();
            setActive((active + 1) % n);
        } else if (e.key === 'ArrowUp' && n) {
            e.preventDefault();
            setActive((active - 1 + n) % n);
        }
    });
 
    form.addEventListener('submit', function (e) {
        if (input.value.trim() === '') {
            e.preventDefault();
            return;
        }
        if (active >= 0 && items[active]) {           // Enter khi đang chọn một gợi ý -> mở thẳng sản phẩm đó
            e.preventDefault();
            window.location.href = items[active].link;
        }
    });
})();
</script>
 