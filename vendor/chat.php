<?php
require_once __DIR__ . '/khung_nv.php';

nv_dau('Chat khách hàng', 'chat');
?>

<div class="nv-chat">
    <div class="nv-chat__header">
        <h1>Chat khách hàng</h1>
        <p>Tiếp nhận và phản hồi tin nhắn của khách hàng theo thời gian thực.</p>
    </div>

    <div class="nv-chat__layout">
        <aside class="nv-chat__list">
            <div class="nv-chat__tabs" id="nv-tabs" role="tablist">
                <button type="button" role="tab" data-loc="cho" class="active">Chờ <span class="nv-chat__so" id="so-cho">0</span></button>
                <button type="button" role="tab" data-loc="dang">Đang hỗ trợ <span class="nv-chat__so" id="so-dang">0</span></button>
                <button type="button" role="tab" data-loc="bot">Chatbot</button>
                <button type="button" role="tab" data-loc="dong">Đã đóng</button>
            </div>
            <ul class="nv-chat__ds" id="nv-ds" aria-live="polite"></ul>
        </aside>

        <section class="nv-chat__content" id="nv-khung">
            <div class="nv-chat__empty" id="nv-trong">
                <h2>Chọn một cuộc hội thoại</h2>
                <p>Tin nhắn của khách hàng sẽ hiển thị tại đây.</p>
            </div>

            <div class="nv-chat__thread" id="nv-thread" hidden>
                <div class="nv-chat__thread-head">
                    <div>
                        <h2 id="nv-ten-khach"></h2>
                        <p id="nv-phu"></p>
                    </div>
                    <div class="nv-chat__thread-actions">
                        <button type="button" id="nv-nhan" class="nv-nut">Nhận hỗ trợ</button>
                        <button type="button" id="nv-dong" class="nv-nut nv-nut--vien">Đóng hội thoại</button>
                    </div>
                </div>

                <div class="nv-chat__msgs" id="nv-msgs" aria-live="polite"></div>

                <form class="nv-chat__form" id="nv-form">
                    <textarea id="nv-input" rows="1" maxlength="2000" placeholder="Nhập phản hồi cho khách..." aria-label="Nội dung phản hồi" required></textarea>
                    <button type="submit" class="nv-nut" id="nv-gui">Gửi</button>
                </form>
                <p class="nv-chat__thongbao" id="nv-thongbao" role="alert" hidden></p>
            </div>
        </section>
    </div>
</div>

<style>
.nv-chat { padding: 24px; color: #302820; }
.nv-chat__header { margin-bottom: 24px; }
.nv-chat__header h1 { margin: 0 0 8px; font-size: 28px; }
.nv-chat__header p, .nv-chat__empty p { color: #766e65; }

.nv-chat__layout {
    display: grid;
    grid-template-columns: 340px minmax(0, 1fr);
    height: calc(100vh - 220px);
    min-height: 480px;
    background: #fffdf9;
    border: 1px solid #e7dfd5;
    border-radius: 14px;
    overflow: hidden;
}

.nv-chat__list { display: flex; flex-direction: column; min-height: 0; border-right: 1px solid #e7dfd5; }

.nv-chat__tabs { display: flex; flex-wrap: wrap; gap: 6px; padding: 12px; border-bottom: 1px solid #e7dfd5; }
.nv-chat__tabs button {
    padding: 7px 12px; font: inherit; font-size: 12px; font-weight: 600; color: #766e65;
    background: none; border: 1px solid #e7dfd5; border-radius: 999px; cursor: pointer;
}
.nv-chat__tabs button.active { color: #fff; background: #302820; border-color: #302820; }
.nv-chat__so {
    display: inline-block; min-width: 18px; margin-left: 4px; padding: 0 5px;
    font-size: 11px; line-height: 18px; text-align: center; color: #fff; background: #c0392b; border-radius: 9px;
}
.nv-chat__so:empty, .nv-chat__so[data-n="0"] { display: none; }

.nv-chat__ds { flex: 1; margin: 0; padding: 0; overflow-y: auto; list-style: none; }
.nv-chat__ds li { border-bottom: 1px solid #f0e9df; }
.nv-chat__ds button {
    display: block; width: 100%; padding: 14px 16px; font: inherit; text-align: left; color: inherit;
    background: none; border: 0; cursor: pointer;
}
.nv-chat__ds button:hover, .nv-chat__ds li.active button { background: #f6f0e7; }
.nv-chat__ds .ten { display: flex; justify-content: space-between; gap: 8px; font-weight: 700; font-size: 14px; }
.nv-chat__ds .gio { font-weight: 400; font-size: 11px; color: #9a9187; white-space: nowrap; }
.nv-chat__ds .tin { margin-top: 4px; font-size: 13px; color: #766e65; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.nv-chat__ds .chua-doc { color: #302820; font-weight: 700; }
.nv-chat__ds .nhan-nv { display: inline-block; margin-top: 6px; padding: 2px 8px; font-size: 11px; color: #766e65; background: #efe7da; border-radius: 6px; }
.nv-chat__trong-ds { padding: 24px 16px; font-size: 13px; color: #766e65; text-align: center; }

.nv-chat__content { display: flex; min-width: 0; min-height: 0; align-items: center; justify-content: center; }
.nv-chat__empty { padding: 24px; text-align: center; }
.nv-chat__empty h2 { font-size: 18px; }

.nv-chat__thread { display: flex; flex-direction: column; width: 100%; height: 100%; min-height: 0; }
.nv-chat__thread[hidden] { display: none; }
.nv-chat__thread-head {
    display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px;
    padding: 14px 20px; border-bottom: 1px solid #e7dfd5;
}
.nv-chat__thread-head h2 { margin: 0; font-size: 17px; }
.nv-chat__thread-head p { margin: 2px 0 0; font-size: 12px; color: #766e65; }
.nv-chat__thread-actions { display: flex; gap: 8px; }

.nv-nut {
    padding: 9px 16px; font: inherit; font-size: 12px; font-weight: 600; letter-spacing: .5px;
    color: #fff; background: #302820; border: 1px solid #302820; border-radius: 8px; cursor: pointer;
}
.nv-nut:hover { background: #4a3f33; }
.nv-nut:disabled { opacity: .5; cursor: default; }
.nv-nut--vien { color: #302820; background: #fff; }
.nv-nut--vien:hover { background: #f6f0e7; }

.nv-chat__msgs { flex: 1; min-height: 0; padding: 20px; overflow-y: auto; background: #faf6ef; }
.nv-msg { display: flex; flex-direction: column; max-width: 70%; margin-bottom: 14px; }
.nv-msg .bong { padding: 10px 14px; font-size: 14px; line-height: 1.5; white-space: pre-wrap; word-break: break-word; border-radius: 14px; }
.nv-msg .gio { margin-top: 4px; font-size: 11px; color: #9a9187; }
.nv-msg.khach { align-items: flex-start; }
.nv-msg.khach .bong { background: #fff; border: 1px solid #e7dfd5; border-top-left-radius: 4px; }
.nv-msg.nhanvien { align-items: flex-end; margin-left: auto; }
.nv-msg.nhanvien .bong { color: #fff; background: #302820; border-top-right-radius: 4px; }
.nv-msg.bot { align-items: center; max-width: 90%; margin-left: auto; margin-right: auto; }
.nv-msg.bot .bong { font-size: 12px; color: #766e65; background: #efe7da; }

.nv-chat__form { display: flex; gap: 10px; padding: 14px 20px; border-top: 1px solid #e7dfd5; }
.nv-chat__form textarea {
    flex: 1; max-height: 120px; padding: 10px 14px; font: inherit; font-size: 14px; resize: none;
    border: 1px solid #d9cfc2; border-radius: 10px; outline: none;
}
.nv-chat__form textarea:focus { border-color: #302820; }
.nv-chat__thongbao { margin: 0; padding: 8px 20px 12px; font-size: 12px; color: #b3261e; }
.nv-chat__thongbao[hidden] { display: none; }

@media (max-width: 768px) {
    .nv-chat { padding: 12px; }
    .nv-chat__layout { grid-template-columns: 1fr; height: auto; }
    .nv-chat__list { max-height: 260px; border-right: 0; border-bottom: 1px solid #e7dfd5; }
    .nv-chat__thread { height: 70vh; }
    .nv-msg { max-width: 88%; }
}
</style>

<script>
(function () {
    var API = <?= json_encode(BASE_URL . 'api/chat_nv_api.php') ?>;
    var $ = function (id) { return document.getElementById(id); };

    var st = { loc: 'cho', conv: null, lastId: 0, seen: {}, me: null, busy: false };
    var ds = $('nv-ds'), msgs = $('nv-msgs'), input = $('nv-input'), thongbao = $('nv-thongbao');

    function goi(body) {
        return fetch(API, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body)
        }).then(function (r) {
            return r.json().catch(function () { return { success: false, message: 'Phản hồi không hợp lệ.' }; })
                .then(function (d) { d._status = r.status; return d; });
        }).catch(function () { return { success: false, message: 'Mất kết nối tới máy chủ.' }; });
    }

    function baoLoi(chu) {
        thongbao.textContent = chu || '';
        thongbao.hidden = !chu;
    }

    function gioHienThi(s) {
        if (!s) { return ''; }
        var hom = new Date(), p = function (n) { return (n < 10 ? '0' : '') + n; };
        var homNay = hom.getFullYear() + '-' + p(hom.getMonth() + 1) + '-' + p(hom.getDate());
        return s.substr(0, 10) === homNay ? s.substr(11, 5) : s.substr(8, 2) + '/' + s.substr(5, 2) + ' ' + s.substr(11, 5);
    }

    function veDanhSach(d) {
        ds.innerHTML = '';
        $('so-cho').textContent = d.counts.cho || '';
        $('so-cho').dataset.n = d.counts.cho;
        $('so-dang').textContent = d.counts.dang || '';
        $('so-dang').dataset.n = d.counts.dang;

        if (!d.conversations.length) {
            var rong = document.createElement('li');
            rong.className = 'nv-chat__trong-ds';
            rong.textContent = 'Không có cuộc hội thoại nào.';
            ds.appendChild(rong);
            return;
        }

        d.conversations.forEach(function (c) {
            var li = document.createElement('li');
            if (c.id === st.conv) { li.className = 'active'; }
            var nut = document.createElement('button');
            nut.type = 'button';

            var ten = document.createElement('div');
            ten.className = 'ten';
            var tenKhach = document.createElement('span');
            tenKhach.textContent = c.customer_name + (c.unread > 0 ? ' (' + c.unread + ')' : '');
            var gio = document.createElement('span');
            gio.className = 'gio';
            gio.textContent = gioHienThi(c.updated_at);
            ten.appendChild(tenKhach);
            ten.appendChild(gio);

            var tin = document.createElement('div');
            tin.className = 'tin' + (c.unread > 0 ? ' chua-doc' : '');
            tin.textContent = c.last_message || c.title || '';

            nut.appendChild(ten);
            nut.appendChild(tin);

            if (c.staff_name) {
                var nv = document.createElement('span');
                nv.className = 'nhan-nv';
                nv.textContent = 'NV: ' + c.staff_name;
                nut.appendChild(nv);
            }

            nut.addEventListener('click', function () { moHoiThoai(c.id); });
            li.appendChild(nut);
            ds.appendChild(li);
        });
    }

    function taiDanhSach() {
        return goi({ action: 'list', loc: st.loc }).then(function (d) {
            if (d.success) { st.me = d.me; veDanhSach(d); }
        });
    }

    function themTin(m) {
        if (st.seen[m.id]) { return; }
        st.seen[m.id] = true;
        if (m.id > st.lastId) { st.lastId = m.id; }

        var gan = msgs.scrollHeight - msgs.scrollTop - msgs.clientHeight < 80;
        var w = document.createElement('div');
        w.className = 'nv-msg ' + m.sender;
        var b = document.createElement('div');
        b.className = 'bong';
        b.textContent = m.content;
        var g = document.createElement('span');
        g.className = 'gio';
        g.textContent = gioHienThi(m.created_at);
        w.appendChild(b);
        if (m.sender !== 'bot') { w.appendChild(g); }
        msgs.appendChild(w);
        if (gan || m.sender === 'nhanvien') { msgs.scrollTop = msgs.scrollHeight; }
    }

    var trangThaiChu = {
        dang_mo: 'Đang chat với chatbot',
        cho_nhan_vien: 'Đang chờ nhân viên',
        dang_ho_tro: 'Đang được hỗ trợ',
        da_dong: 'Đã đóng'
    };

    function capNhatDau(c) {
        $('nv-ten-khach').textContent = c.customer_name;
        $('nv-phu').textContent = c.customer_email + ' · ' + (trangThaiChu[c.status] || c.status) +
            (c.staff_name ? ' · NV: ' + c.staff_name : '');

        var laCuaToi = c.staff_id === st.me;
        $('nv-nhan').hidden = !(c.status === 'cho_nhan_vien' || (c.status === 'dang_ho_tro' && !laCuaToi));
        $('nv-nhan').textContent = c.status === 'dang_ho_tro' ? 'Tiếp quản' : 'Nhận hỗ trợ';
        $('nv-dong').hidden = c.status === 'da_dong' || c.status === 'dang_mo';

        var duocGui = c.status === 'cho_nhan_vien' || (c.status === 'dang_ho_tro' && laCuaToi);
        input.disabled = !duocGui;
        $('nv-gui').disabled = !duocGui;
        input.placeholder = duocGui ? 'Nhập phản hồi cho khách...' :
            (c.status === 'da_dong' ? 'Cuộc hội thoại đã đóng.' :
            (c.status === 'dang_mo' ? 'Khách đang chat với chatbot.' : 'Nhân viên khác đang hỗ trợ.'));
    }

    function moHoiThoai(id) {
        st.conv = id; st.lastId = 0; st.seen = {};
        msgs.innerHTML = '';
        baoLoi('');
        $('nv-trong').hidden = true;
        $('nv-thread').hidden = false;
        return tai(true).then(taiDanhSach);
    }

    function tai(dauTien) {
        if (!st.conv || st.busy) { return Promise.resolve(); }
        var id = st.conv;
        return goi({ action: 'history', conversation_id: id, after_id: st.lastId }).then(function (d) {
            if (id !== st.conv) { return; }
            if (!d.success) { if (dauTien) { baoLoi(d.message); } return; }
            st.me = d.me;
            capNhatDau(d.conversation);
            d.messages.forEach(themTin);
            if (dauTien) { msgs.scrollTop = msgs.scrollHeight; }
        });
    }

    $('nv-tabs').addEventListener('click', function (e) {
        var nut = e.target.closest('button[data-loc]');
        if (!nut) { return; }
        st.loc = nut.dataset.loc;
        Array.prototype.forEach.call($('nv-tabs').children, function (n) { n.classList.toggle('active', n === nut); });
        taiDanhSach();
    });

    $('nv-nhan').addEventListener('click', function () {
        if (!st.conv) { return; }
        var tiepQuan = $('nv-nhan').textContent === 'Tiếp quản';
        if (tiepQuan && !confirm('Nhân viên khác đang hỗ trợ cuộc này. Bạn có chắc muốn tiếp quản?')) { return; }
        goi({ action: 'nhan', conversation_id: st.conv }).then(function (d) {
            if (!d.success) { baoLoi(d.message); return; }
            baoLoi('');
            tai(false).then(taiDanhSach);
        });
    });

    $('nv-dong').addEventListener('click', function () {
        if (!st.conv || !confirm('Đóng cuộc hội thoại này? Khách sẽ cần bắt đầu cuộc trò chuyện mới.')) { return; }
        goi({ action: 'dong', conversation_id: st.conv }).then(function (d) {
            if (!d.success) { baoLoi(d.message); return; }
            tai(false).then(taiDanhSach);
        });
    });

    function gui() {
        var noiDung = input.value.trim();
        if (!noiDung || !st.conv || input.disabled) { return; }
        $('nv-gui').disabled = true;
        goi({ action: 'gui', conversation_id: st.conv, message: noiDung }).then(function (d) {
            if (d.success) {
                baoLoi('');
                input.value = '';
                input.style.height = 'auto';
                themTin(d.message_sent);
                capNhatDau(d.conversation);
                taiDanhSach();
            } else {
                baoLoi(d.message);
                $('nv-gui').disabled = false;
            }
            input.focus();
        });
    }

    $('nv-form').addEventListener('submit', function (e) { e.preventDefault(); gui(); });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); gui(); }
    });
    input.addEventListener('input', function () {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
    });

    taiDanhSach();
    setInterval(function () { if (!document.hidden) { taiDanhSach(); } }, 6000);
    setInterval(function () { if (!document.hidden) { tai(false); } }, 3000);
})();
</script>

<?php nv_cuoi(); ?>