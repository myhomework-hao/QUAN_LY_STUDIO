(function () {
    var cfg = window.VIBE_WIDGET_CONFIG || {};
    var widget = document.getElementById('vibe-chat-widget');
    if (!widget) { return; }

    var toggle = document.getElementById('vibe-chat-toggle');
    var panel = document.getElementById('vibe-chat-panel');
    var list = document.getElementById('vibe-chat-messages');
    var form = document.getElementById('vibe-chat-form');
    var input = document.getElementById('vibe-chat-input');
    var sendBtn = document.getElementById('vibe-chat-send');
    var suggestions = widget.querySelector('.vibe-chat-suggestions');
    var subtitle = widget.querySelector('.vibe-chat-brand p');
    var footer = widget.querySelector('.vibe-chat-footer');

    var chuMacDinh = subtitle ? subtitle.textContent : '';
    var st = { conv: null, lastId: 0, seen: {}, daMo: false, dangGui: false, ketThuc: false, dangNhap: true };
    var timerMo = null, timerDong = null, nutKetThuc = null, nutMoi = null;

    function goi(body) {
        // Gắn CSRF token vào mọi yêu cầu gửi API.
        body.csrf = cfg.csrf || '';

        return fetch(cfg.apiUrl, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body)
        }).then(function (r) {
            return r.json().catch(function () { return { success: false, message: 'Phản hồi không hợp lệ.' }; })
                .then(function (d) { d._status = r.status; return d; });
        }).catch(function () { return { success: false, message: 'Mất kết nối tới máy chủ.' }; });
    }

    function gio(s) {
        if (!s) { return 'Vừa xong'; }
        var h = new Date(), p = function (n) { return (n < 10 ? '0' : '') + n; };
        var homNay = h.getFullYear() + '-' + p(h.getMonth() + 1) + '-' + p(h.getDate());
        return s.substr(0, 10) === homNay ? s.substr(11, 5) : s.substr(8, 2) + '/' + s.substr(5, 2) + ' ' + s.substr(11, 5);
    }

    function cuonXuong() { list.scrollTop = list.scrollHeight; }

    function formatTien(so) {
        var n = Number(so);
        if (isNaN(n)) { return ''; }
        try { return new Intl.NumberFormat('vi-VN').format(n) + '₫'; }
        catch (e) { return Math.round(n) + '₫'; }
    }

    function duongDanGoc() {
        return String(cfg.apiUrl || '').replace(/api\/chat_api\.php.*$/, '');
    }

    function veSanPham(sanPham) {
        if (!Array.isArray(sanPham) || !sanPham.length) { return null; }
        var base = duongDanGoc();
        var container = document.createElement('div');
        container.className = 'vibe-product-list';

        sanPham.forEach(function (sp) {
            var card = document.createElement('a');
            card.className = 'vibe-product-card';
            card.href = base + 'user/service-detail.php?id=' + encodeURIComponent(sp && sp.id ? sp.id : '');

            var img = document.createElement('img');
            img.className = 'vibe-product-thumb';
            img.src = base + 'assets/uploads/' + (sp && sp.anh ? sp.anh : 'default.png');
            img.alt = sp && sp.ten ? sp.ten : '';
            img.loading = 'lazy';
            card.appendChild(img);

            var info = document.createElement('div');
            info.className = 'vibe-product-info';

            var name = document.createElement('strong');
            name.className = 'vibe-product-name';
            name.textContent = sp && sp.ten ? sp.ten : '';
            info.appendChild(name);

            var meta = document.createElement('div');
            meta.className = 'vibe-product-meta';

            var code = document.createElement('span');
            code.className = 'vibe-product-code';
            code.textContent = sp && sp.ma ? sp.ma : '';
            meta.appendChild(code);

            var price = document.createElement('span');
            price.className = 'vibe-product-price';
            price.textContent = formatTien(sp && sp.gia) + ' / ' + (sp && sp.don_vi ? sp.don_vi : '');
            meta.appendChild(price);

            info.appendChild(meta);

            if (sp && sp.sizes) {
                var size = document.createElement('div');
                size.className = 'vibe-product-size';
                size.textContent = 'Size: ' + sp.sizes;
                info.appendChild(size);
            } else if (sp && sp.so_luong !== null && sp.so_luong !== undefined) {
                var qty = document.createElement('div');
                qty.className = 'vibe-product-qty';
                qty.textContent = (Number(sp.so_luong) > 0) ? ('Số lượng: ' + sp.so_luong) : 'Tạm hết';
                info.appendChild(qty);
            }

            if (sp && sp.dong_phu) {
                var sub = document.createElement('div');
                sub.className = 'vibe-product-sub';
                sub.textContent = sp.dong_phu;
                info.appendChild(sub);
            }

            card.appendChild(info);
            container.appendChild(card);
        });

        return container;
    }

    function veTin(loai, noiDung, thoiGian, nodeThem) {
        var w = document.createElement('div');
        w.className = 'vibe-message ' + loai;

        if (loai !== 'user') {
            var av = document.createElement('div');
            av.className = 'vibe-message-avatar';
            av.innerHTML = loai === 'staff' ? '<i class="fa-solid fa-headset"></i>' : '<i class="fa-solid fa-camera"></i>';
            w.appendChild(av);
        }

        var c = document.createElement('div');
        c.className = 'vibe-message-content';
        var b = document.createElement('div');
        b.className = 'vibe-message-bubble';
        b.textContent = noiDung;
        if (nodeThem) { b.appendChild(nodeThem); }
        var t = document.createElement('span');
        t.className = 'vibe-message-time';
        t.textContent = thoiGian;
        c.appendChild(b);
        c.appendChild(t);
        w.appendChild(c);
        list.appendChild(w);
        return w;
    }

    function themTin(m) {
        if (m.id && st.seen[m.id]) { return false; }
        if (m.id) { st.seen[m.id] = true; if (m.id > st.lastId) { st.lastId = m.id; } }
        var loai = m.sender === 'khach' ? 'user' : (m.sender === 'nhanvien' ? 'staff' : 'bot');
        var nodeThem = null;
        if (Array.isArray(m.san_pham) && m.san_pham.length) {
            nodeThem = veSanPham(m.san_pham);
        }
        veTin(loai, m.content, gio(m.created_at), nodeThem);
        if (suggestions) { suggestions.hidden = true; }
        return loai !== 'user';
    }

    function capNhatTrangThai(d) {
        if (!d || !d.status) { return; }
        var chu = chuMacDinh;
        if (d.status === 'cho_nhan_vien') { chu = 'Đang chờ nhân viên phản hồi...'; }
        else if (d.status === 'dang_ho_tro') { chu = (d.staff_name ? 'Nhân viên ' + d.staff_name : 'Nhân viên') + ' đang hỗ trợ bạn'; }
        else if (d.status === 'da_dong') { chu = 'Cuộc trò chuyện đã kết thúc'; }
        if (subtitle) { subtitle.textContent = chu; }

        st.ketThuc = d.status === 'da_dong';
        input.disabled = st.ketThuc;
        sendBtn.disabled = st.ketThuc;
        input.placeholder = st.ketThuc ? 'Cuộc trò chuyện đã kết thúc' : 'Nhập tin nhắn của bạn...';
        hienNut();
    }

    function hienNut() {
        if (!footer) { return; }
        if (!nutKetThuc) {
            nutKetThuc = document.createElement('button');
            nutKetThuc.type = 'button';
            nutKetThuc.className = 'vibe-chat-link';
            nutKetThuc.textContent = 'Kết thúc cuộc trò chuyện';
            nutKetThuc.addEventListener('click', ketThuc);
            footer.appendChild(nutKetThuc);
        }
        if (!nutMoi) {
            nutMoi = document.createElement('button');
            nutMoi.type = 'button';
            nutMoi.className = 'vibe-chat-new';
            nutMoi.textContent = 'Bắt đầu cuộc trò chuyện mới';
            nutMoi.addEventListener('click', batDauMoi);
            form.parentNode.insertBefore(nutMoi, form);
        }
        nutKetThuc.hidden = !st.conv || st.ketThuc;
        nutMoi.hidden = !st.ketThuc;
        form.hidden = st.ketThuc;
    }

    function ketThuc() {
        if (!st.conv || !confirm('Kết thúc cuộc trò chuyện này?')) { return; }
        goi({ action: 'close', conversation_id: st.conv }).then(function (d) {
            if (d.success) { poll(); }
        });
    }

    function batDauMoi() {
        st.conv = null; st.lastId = 0; st.seen = {}; st.ketThuc = false;
        Array.prototype.slice.call(list.querySelectorAll('.vibe-message')).forEach(function (n, i) { if (i > 0) { n.remove(); } });
        if (suggestions) { suggestions.hidden = false; }
        if (subtitle) { subtitle.textContent = chuMacDinh; }
        input.disabled = false; sendBtn.disabled = false;
        input.placeholder = 'Nhập tin nhắn của bạn...';
        hienNut();
        input.focus();
    }

    function nhacDangNhap() {
        var a = document.createElement('a');
        a.href = cfg.loginUrl;
        a.textContent = ' Đăng nhập ngay';
        a.className = 'vibe-chat-login';
        veTin('bot', 'Bạn cần đăng nhập để trò chuyện với VIBE STUDIO.', gio(''), a);
        cuonXuong();
    }

    function moDau() {
        if (st.daMo) { return; }
        // Khách vãng lai: API bắt buộc đăng nhập, báo ngay trong khung chat thay vì gọi API chắc chắn nhận 401
        if (cfg.loggedIn === false) {
            st.daMo = true;
            st.dangNhap = false;
            nhacDangNhap();
            return;
        }
        st.daMo = true;
        goi({ action: 'open' }).then(function (d) {
            if (d._status === 401 || d._status === 403) { st.dangNhap = false; st.daMo = false; return; }
            if (!d.success) {
                st.daMo = false;
                veTin('bot', d.message || 'Không tải được lịch sử trò chuyện. Bạn thử đóng rồi mở lại khung chat nhé.', gio(''));
                cuonXuong();
                hienNut();
                return;
            }
            if (!d.conversation_id) { hienNut(); return; }
            st.conv = d.conversation_id;
            (Array.isArray(d.messages) ? d.messages : []).forEach(themTin);
            capNhatTrangThai(d);
            cuonXuong();
        });
    }

    function poll() {
        if (!st.conv || st.dangGui) { return; }
        goi({ action: 'poll', conversation_id: st.conv, after_id: st.lastId }).then(function (d) {
            if (!d.success) { return; }
            var coMoi = false;
            (Array.isArray(d.messages) ? d.messages : []).forEach(function (m) { if (themTin(m)) { coMoi = true; } });
            capNhatTrangThai(d);
            if (d.messages && d.messages.length) { cuonXuong(); }
            if (coMoi && panel.hidden) { toggle.classList.add('has-new'); }
        });
    }

    function chayPoll() {
        clearInterval(timerMo); clearInterval(timerDong);
        if (panel.hidden) {
            timerDong = setInterval(function () { if (!document.hidden && !st.ketThuc) { poll(); } }, 12000);
        } else {
            timerMo = setInterval(function () { if (!document.hidden) { poll(); } }, 3000);
        }
    }

    function mo() {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        toggle.classList.remove('has-new');
        widget.classList.add('is-open');
        moDau();
        chayPoll();
        setTimeout(function () { input.focus(); cuonXuong(); }, 50);
    }

    function dong() {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        widget.classList.remove('is-open');
        chayPoll();
    }

    function gui(noiDung) {
        noiDung = (noiDung || '').trim();
        if (!noiDung || st.dangGui || st.ketThuc) { return; }
        if (!st.dangNhap) { nhacDangNhap(); return; }

        st.dangGui = true;
        sendBtn.disabled = true;
        var tam = veTin('user', noiDung, gio(''));
        var go = veTin('bot', '...', '');
        go.classList.add('is-typing');
        if (suggestions) { suggestions.hidden = true; }
        input.value = '';
        input.style.height = 'auto';
        cuonXuong();

        goi({ action: 'send', message: noiDung, conversation_id: st.conv, product_id: cfg.productId || null }).then(function (d) {
            st.dangGui = false;
            sendBtn.disabled = false;
            go.remove();
            tam.remove();

            if (d._status === 401) {
                st.dangNhap = false;
                nhacDangNhap();
                return;
            }
            if (!d.success) {
                veTin('bot', d.message || 'Không gửi được tin nhắn.', gio(''));
                if (d._status === 409) { st.ketThuc = true; capNhatTrangThai({ status: 'da_dong' }); }
                cuonXuong();
                return;
            }
            st.conv = d.conversation_id;
            (Array.isArray(d.messages) ? d.messages : []).forEach(themTin);
            capNhatTrangThai(d);
            cuonXuong();
        });
    }

    toggle.addEventListener('click', function () { panel.hidden ? mo() : dong(); });
    document.getElementById('vibe-chat-minimize').addEventListener('click', dong);
    document.getElementById('vibe-chat-close').addEventListener('click', dong);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) { dong(); } });

    form.addEventListener('submit', function (e) { e.preventDefault(); gui(input.value); });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); gui(input.value); }
    });
    input.addEventListener('input', function () {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 110) + 'px';
    });

    if (suggestions) {
        suggestions.addEventListener('click', function (e) {
            var nut = e.target.closest('button[data-message]');
            if (nut) { gui(nut.dataset.message); }
        });
    }

    chayPoll();
})();