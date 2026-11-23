<?php
require_once __DIR__ . '/khung_nv.php';

nv_dau('Lịch thuê Studio', 'lich');
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..700;1,6..96,400..700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/lich-thue.css">


<section class="khung-lich">
    <p class="nhan-nho-trang no-print">LỊCH THUÊ</p>
    <h1 class="tieu-de-trang">Lịch thuê <i>Studio</i></h1>

    <div class="thanh-loc no-print">
        <button class="nut-phu" onclick="window.print()">In lịch</button>
            <select id="locLoai">
                <option value="">Tất cả loại</option>
                <?php foreach (['phong_chup', 'thiet_bi', 'trang_phuc', 'phu_kien', 'dich_vu', 'goi_chup'] as $m) : ?>
                    <option value="<?= h($m) ?>"><?= h(ten_loai_dich_vu($m)) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="locTrangThai">
                <option value="">Tất cả trạng thái</option>
                <?php foreach (['Chờ duyệt', 'Đã duyệt', 'Hoàn thành'] as $tt) : ?>
                    <option value="<?= h($tt) ?>"><?= h($tt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="chu-thich-mau no-print">
            <span class="mau"><span class="o-mau" style="background:#b08d57"></span>Chờ duyệt</span>
            <span class="mau"><span class="o-mau" style="background:#49352b"></span>Đã duyệt</span>
            <span class="mau"><span class="o-mau" style="background:#2e6b3f"></span>Đang thuê</span>
            <span class="mau"><span class="o-mau" style="background:#777777"></span>Khác</span>
        </div>

        <div id="calendar"></div>
    </div>
</section>

<div class="hop-chi-tiet" id="lichModal">
    <div class="hop-chi-tiet-box">
        <h3>Chi tiết đơn thuê</h3>
        <div class="row"><strong>Mã đơn:</strong> <span id="mdMaDon"></span></div>
        <div class="row"><strong>Khách:</strong> <span id="mdKhach"></span></div>
        <div class="row"><strong>Thiết bị:</strong> <span id="mdThietBi"></span></div>
        <div class="row"><strong>Thời gian:</strong> <span id="mdThoiGian"></span></div>
        <div class="row"><strong>Trạng thái:</strong> <span id="mdTrangThai"></span></div>
        <div style="text-align:right; margin-top:12px;">
            <button class="main-button" onclick="document.getElementById('lichModal').classList.remove('show')">Đóng</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/vi.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'vi',
        firstDay: 1,
        nowIndicator: true,
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: {
            url: '../api/lich-thue-api.php',
            extraParams: function () {
                return {
                    loai: document.getElementById('locLoai').value,
                    trang_thai: document.getElementById('locTrangThai').value
                };
            }
        },
        eventClick: function (info) {
            var p = info.event.extendedProps;
            document.getElementById('mdMaDon').textContent = 'DDL-' + String(p.maDon).padStart(5, '0');
            document.getElementById('mdKhach').textContent = p.khach;
            document.getElementById('mdThietBi').textContent = p.thietBi;
            document.getElementById('mdThoiGian').textContent = p.batDau + ' → ' + p.ketThuc;
            document.getElementById('mdTrangThai').textContent = p.trangThai;
            document.getElementById('lichModal').classList.add('show');
        }
    });
    calendar.render();

    document.getElementById('locLoai').addEventListener('change', function () { calendar.refetchEvents(); });
    document.getElementById('locTrangThai').addEventListener('change', function () { calendar.refetchEvents(); });
});
</script>
<?php nv_cuoi(); ?>