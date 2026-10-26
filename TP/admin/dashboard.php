
<?php
session_start();
 
/*
 * KIỂM TRA QUYỀN ADMIN
 * Sửa tên khóa session cho khớp với trang đăng nhập của bạn
 * (ví dụ $_SESSION['vai_tro'] === 'admin'). Đặt $bat_kiem_tra_quyen = false để xem thử giao diện.
 */
$bat_kiem_tra_quyen = true;
if ($bat_kiem_tra_quyen && (($_SESSION['vai_tro'] ?? '') !== 'admin')) {
    header('Location: ../index.php');
    exit;
}
 
$base = defined('BASE_URL') ? BASE_URL : '../';
$ten_admin = $_SESSION['ho_ten'] ?? 'Quản trị viên';
 
/*
 * DỮ LIỆU MẪU
 * Thay từng mảng bằng truy vấn CSDL khi nối backend
 * (lich_dat, dich_vu, nhan_vien, khach_hang...).
 */
$thong_ke = [
    ['nhan' => 'Lịch hôm nay',        'so' => 6,          'ghi_chu' => '2 lịch đang chờ xác nhận'],
    ['nhan' => 'Doanh thu tháng này', 'so' => '48.600.000đ', 'ghi_chu' => 'Tăng 12% so với tháng trước'],
    ['nhan' => 'Khách hàng mới',      'so' => 23,         'ghi_chu' => 'Trong 30 ngày gần đây'],
    ['nhan' => 'Nhân viên đang làm',  'so' => 5,          'ghi_chu' => '1 người nghỉ phép'],
];
 
$doanh_thu_6_thang = [
    ['thang' => 'T5',  'tien' => 32],
    ['thang' => 'T6',  'tien' => 38],
    ['thang' => 'T7',  'tien' => 35],
    ['thang' => 'T8',  'tien' => 44],
    ['thang' => 'T9',  'tien' => 43],
    ['thang' => 'T10', 'tien' => 48.6],
];
 
$lich_dat_gan_day = [
    ['ma' => 'LD-0241', 'khach' => 'Nguyễn Minh Anh', 'dich_vu' => 'Portrait', 'gio' => '09:00 26/10', 'nv' => 'Trần Quốc Huy', 'trang_thai' => 'cho'],
    ['ma' => 'LD-0240', 'khach' => 'Lê Thảo Vy',      'dich_vu' => 'Couple',   'gio' => '10:30 26/10', 'nv' => 'Phạm Gia Hân',  'trang_thai' => 'xac-nhan'],
    ['ma' => 'LD-0239', 'khach' => 'Công ty An Phát',  'dich_vu' => 'Product',  'gio' => '13:30 26/10', 'nv' => 'Trần Quốc Huy', 'trang_thai' => 'xac-nhan'],
    ['ma' => 'LD-0238', 'khach' => 'Võ Hoàng Nam',     'dich_vu' => 'Event',    'gio' => '15:00 26/10', 'nv' => 'Đặng Thu Trang','trang_thai' => 'cho'],
    ['ma' => 'LD-0237', 'khach' => 'Bùi Ngọc Lan',     'dich_vu' => 'Portrait', 'gio' => '17:00 25/10', 'nv' => 'Phạm Gia Hân',  'trang_thai' => 'hoan-thanh'],
    ['ma' => 'LD-0236', 'khach' => 'Đỗ Khánh Linh',    'dich_vu' => 'Couple',   'gio' => '14:00 25/10', 'nv' => 'Đặng Thu Trang','trang_thai' => 'huy'],
];
 
$nhan_nhan = [
    'cho'        => 'Chờ xác nhận',
    'xac-nhan'   => 'Đã xác nhận',
    'hoan-thanh' => 'Hoàn thành',
    'huy'        => 'Đã hủy',
];
 
$dich_vu = [
    ['ten' => 'Portrait', 'so_lich' => 41, 'hien' => true],
    ['ten' => 'Couple',   'so_lich' => 33, 'hien' => true],
    ['ten' => 'Product',  'so_lich' => 18, 'hien' => true],
    ['ten' => 'Event',    'so_lich' => 12, 'hien' => false],
];
 
$menu = [
    ['muc' => 'Tổng quan',      'lien_ket' => '#tong-quan',  'dang_chon' => true],
    ['muc' => 'Lịch đặt',       'lien_ket' => '#lich-dat'],
    ['muc' => 'Dịch vụ',        'lien_ket' => '#dich-vu'],
    ['muc' => 'Sản phẩm',       'lien_ket' => 'san-pham.php'],
    ['muc' => 'Nhân viên',      'lien_ket' => 'nhan-vien.php'],
    ['muc' => 'Khách hàng',     'lien_ket' => 'khach-hang.php'],
    ['muc' => 'Báo cáo',        'lien_ket' => 'bao-cao.php'],
];
 
$doanh_thu_max = max(array_column($doanh_thu_6_thang, 'tien'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý Studio | VIBE Studio</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
 
<div class="admin">
 
    <!-- THANH BÊN -->
    <aside class="admin-sidebar">
        <a href="<?= $base ?>index.php" class="admin-logo">VIBE <i>Admin</i></a>
 
        <nav class="admin-menu" aria-label="Menu quản trị">
            <?php foreach ($menu as $m): ?>
                <a href="<?= htmlspecialchars($m['lien_ket']) ?>"
                   class="<?= !empty($m['dang_chon']) ? 'active' : '' ?>"><?= htmlspecialchars($m['muc']) ?></a>
            <?php endforeach; ?>
        </nav>
 
        <a href="<?= $base ?>index.php" class="admin-back">← Về trang chủ</a>
    </aside>
 
    <!-- NỘI DUNG -->
    <div class="admin-main">
 
        <header class="admin-topbar">
            <div>
                <h1>Quản lý Studio</h1>
                <p>Thứ Hai, 26/10/2026</p>
            </div>
            <div class="admin-user">
                <span><?= htmlspecialchars($ten_admin) ?></span>
                <a href="<?= $base ?>logout.php" class="btn-line">Đăng xuất</a>
            </div>
        </header>
 
        <!-- THỐNG KÊ NHANH -->
        <section class="stats" id="tong-quan">
            <?php foreach ($thong_ke as $tk): ?>
                <article class="stat-card">
                    <p class="stat-label"><?= htmlspecialchars($tk['nhan']) ?></p>
                    <p class="stat-number"><?= htmlspecialchars((string) $tk['so']) ?></p>
                    <p class="stat-note"><?= htmlspecialchars($tk['ghi_chu']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>
 
        <div class="admin-grid">
 
            <!-- DOANH THU -->
            <section class="panel">
                <div class="panel-head">
                    <h2>Doanh thu 6 tháng gần nhất</h2>
                    <span class="panel-unit">Triệu đồng</span>
                </div>
 
                <div class="chart" role="img" aria-label="Biểu đồ cột doanh thu 6 tháng gần nhất">
                    <?php foreach ($doanh_thu_6_thang as $c): ?>
                        <div class="chart-col">
                            <span class="chart-value"><?= $c['tien'] ?></span>
                            <div class="chart-bar" style="height: <?= round($c['tien'] / $doanh_thu_max * 100) ?>%"></div>
                            <span class="chart-month"><?= htmlspecialchars($c['thang']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
 
            <!-- THAO TÁC NHANH -->
            <section class="panel">
                <div class="panel-head">
                    <h2>Thao tác nhanh</h2>
                </div>
 
                <div class="quick">
                    <a href="lich-dat-them.php" class="btn-solid">Tạo lịch đặt mới</a>
                    <a href="dich-vu-them.php" class="btn-line">Thêm dịch vụ</a>
                    <a href="san-pham-them.php" class="btn-line">Thêm sản phẩm</a>
                    <a href="nhan-vien-them.php" class="btn-line">Thêm nhân viên</a>
                </div>
            </section>
 
        </div>
 
        <!-- LỊCH ĐẶT -->
        <section class="panel" id="lich-dat">
            <div class="panel-head">
                <h2>Lịch đặt gần đây</h2>
                <a href="lich-dat.php" class="panel-link">Xem tất cả</a>
            </div>
 
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Khách hàng</th>
                            <th>Dịch vụ</th>
                            <th>Thời gian</th>
                            <th>Nhân viên</th>
                            <th>Trạng thái</th>
                            <th><span class="sr-only">Thao tác</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lich_dat_gan_day as $ld): ?>
                            <tr>
                                <td><?= htmlspecialchars($ld['ma']) ?></td>
                                <td><?= htmlspecialchars($ld['khach']) ?></td>
                                <td><?= htmlspecialchars($ld['dich_vu']) ?></td>
                                <td><?= htmlspecialchars($ld['gio']) ?></td>
                                <td><?= htmlspecialchars($ld['nv']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $ld['trang_thai'] ?>">
                                        <?= $nhan_nhan[$ld['trang_thai']] ?>
                                    </span>
                                </td>
                                <td class="row-actions">
                                    <?php if ($ld['trang_thai'] === 'cho'): ?>
                                        <a href="lich-dat-xac-nhan.php?ma=<?= urlencode($ld['ma']) ?>">Xác nhận</a>
                                    <?php endif; ?>
                                    <a href="lich-dat-sua.php?ma=<?= urlencode($ld['ma']) ?>">Chi tiết</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
 
        <!-- DỊCH VỤ -->
        <section class="panel" id="dich-vu">
            <div class="panel-head">
                <h2>Dịch vụ</h2>
                <a href="dich-vu.php" class="panel-link">Quản lý dịch vụ</a>
            </div>
 
            <ul class="service-rows">
                <?php foreach ($dich_vu as $dv): ?>
                    <li>
                        <span class="service-name"><?= htmlspecialchars($dv['ten']) ?></span>
                        <span class="service-count"><?= (int) $dv['so_lich'] ?> lịch trong 90 ngày</span>
                        <span class="badge <?= $dv['hien'] ? 'badge-xac-nhan' : 'badge-huy' ?>">
                            <?= $dv['hien'] ? 'Đang hiển thị' : 'Đang ẩn' ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
 
    </div>
</div>
 
</body>
</html>
 
