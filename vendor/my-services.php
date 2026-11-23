<?php
require_once __DIR__ . '/khung_nv.php';

// Lấy các lựa chọn từ đúng nội dung mô tả đang hiển thị trên trang sản phẩm.
function danh_sach_bo_phan_hu_hong(?string $mo_ta_chi_tiet, ?string $mo_ta): array
{
    $ds_bo_phan = [];
    $noi_dung = trim((string) $mo_ta_chi_tiet) !== '' ? $mo_ta_chi_tiet : $mo_ta;
    foreach (preg_split('/\R/u', $noi_dung ?? '') ?: [] as $dong) {
        $bo_phan = trim($dong);
        if ($bo_phan !== '' && mb_strlen($bo_phan) <= 120) {
            $ds_bo_phan[] = $bo_phan;
        }
    }
    return array_values(array_unique($ds_bo_phan));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dich = ve_trang_truoc('my-services.php');
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong($dich);
    }
    $ma = (int) ($_POST['ma'] ?? 0);
    $hanh_dong = $_POST['hanh_dong'] ?? '';
    $dv = lay_mot($conn, "SELECT DV_Ten, DV_Loai, DV_MoTa, DV_MoTaChiTiet FROM dichvu WHERE DV_Ma = ?", [$ma]);

    if (!$dv) {
        dat_thong_bao('Không tìm thấy dịch vụ.', 'error');
    } elseif ($hanh_dong === 'bao_cao_hu_hong') {
        $bo_phan_gui = $_POST['bo_phan'] ?? [];
        $mo_ta = trim((string) ($_POST['mo_ta_hu_hong'] ?? ''));
        $ds_bo_phan = danh_sach_bo_phan_hu_hong($dv['DV_MoTaChiTiet'], $dv['DV_MoTa']);
        $bo_phan_chon = [];
        $bo_phan_khong_hop_le = !is_array($bo_phan_gui);
        if (is_array($bo_phan_gui)) {
            foreach ($bo_phan_gui as $ten_bo_phan) {
                if (!is_string($ten_bo_phan)) {
                    $bo_phan_khong_hop_le = true;
                    break;
                }
                $ten_bo_phan = trim($ten_bo_phan);
                if ($ten_bo_phan !== '') {
                    $bo_phan_chon[] = $ten_bo_phan;
                }
            }
            $bo_phan_chon = array_values(array_unique($bo_phan_chon));
        }

        if ($mo_ta === '' || mb_strlen($mo_ta) > 2000) {
            dat_thong_bao('Mô tả hư hỏng phải có nội dung và tối đa 2.000 ký tự.', 'error');
        } elseif ($bo_phan_khong_hop_le) {
            dat_thong_bao('Danh sách bộ phận hư hỏng không hợp lệ.', 'error');
        } elseif ($dv['DV_Loai'] === 'phong_chup' && !$ds_bo_phan) {
            dat_thong_bao('Phòng chưa có danh sách món trong mô tả sản phẩm.', 'error');
        } elseif ($dv['DV_Loai'] === 'phong_chup' && !$bo_phan_chon) {
            dat_thong_bao('Vui lòng chọn ít nhất một bộ phận hư hỏng.', 'error');
        } elseif ($dv['DV_Loai'] === 'phong_chup' && count(array_diff($bo_phan_chon, $ds_bo_phan)) > 0) {
            dat_thong_bao('Vui lòng chỉ chọn bộ phận trong danh sách mô tả sản phẩm.', 'error');
        } elseif ($dv['DV_Loai'] !== 'phong_chup' && $bo_phan_chon) {
            dat_thong_bao('Chỉ dịch vụ phòng chụp mới có bộ phận để chọn.', 'error');
        } else {
            try {
                $conn->beginTransaction();
                if ($dv['DV_Loai'] === 'phong_chup') {
                    $stmt_khoa_phong = $conn->prepare("SELECT DV_Ma FROM dichvu WHERE DV_Ma = ? FOR UPDATE");
                    $stmt_khoa_phong->execute([$ma]);
                }
                $stmt = $conn->prepare(
                    "INSERT INTO baocao_huhong (DV_Ma, NV_Ma, BCHH_BoPhan, BCHH_MoTa)
                     VALUES (?, ?, ?, ?)"
                );
                if ($dv['DV_Loai'] === 'phong_chup') {
                    foreach ($bo_phan_chon as $bo_phan) {
                        $stmt->execute([$ma, $ma_nhan_vien, $bo_phan, $mo_ta]);
                    }
                } else {
                    $stmt->execute([$ma, $ma_nhan_vien, null, $mo_ta]);
                }

                $phong_tu_dong_khoa = false;
                if ($dv['DV_Loai'] === 'phong_chup') {
                    $stmt_dem_bo_phan = $conn->prepare(
                        "SELECT COUNT(DISTINCT BCHH_BoPhan)
                         FROM baocao_huhong
                         WHERE DV_Ma = ? AND BCHH_TrangThai <> ?
                           AND BCHH_BoPhan IS NOT NULL AND TRIM(BCHH_BoPhan) <> ''"
                    );
                    $stmt_dem_bo_phan->execute([$ma, 'Đã xử lý']);
                    if ((int) $stmt_dem_bo_phan->fetchColumn() > 2) {
                        $conn->prepare(
                            "UPDATE dichvu SET DV_DangBan = 0 WHERE DV_Ma = ? AND DV_Loai = ?"
                        )->execute([$ma, 'phong_chup']);
                        $phong_tu_dong_khoa = true;
                    }
                }

                $conn->commit();
                dat_thong_bao(
                    $phong_tu_dong_khoa
                        ? 'Đã gửi báo cáo. Phòng tự động bị khóa vì có hơn 2 món chưa khắc phục; khách không thể đặt thuê.'
                        : 'Đã gửi báo cáo hư hỏng cho quản trị viên.'
                );
            } catch (PDOException $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                dat_thong_bao('Không thể lưu báo cáo hư hỏng, vui lòng thử lại.', 'error');
            }
        }
    } else {
        dat_thong_bao('Chỉ quản trị viên mới được sửa, ẩn hoặc khóa/bảo trì dịch vụ.', 'error');
    }
    chuyen_huong($dich);
}

$don_cho = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich WHERE DDL_TrangThai = ?", [TRANG_THAI_CHO_DUYET]);
$don_sap_toi = (int) gia_tri(
    $conn,
    "SELECT COUNT(*) FROM dondatlich WHERE DDL_TrangThai = ?
       AND DDL_Ma IN (SELECT DDL_Ma FROM chitietdon WHERE CTD_KetThuc >= ?)",
    [TRANG_THAI_DA_DUYET, date('Y-m-d H:i:s')]
);
$don_toi_xu_ly = (int) gia_tri($conn, "SELECT COUNT(*) FROM dondatlich WHERE NV_Ma = ? OR NV_MaHoanThanh = ?", [$ma_nhan_vien, $ma_nhan_vien]);
$dv_hien = (int) gia_tri($conn, "SELECT COUNT(*) FROM dichvu WHERE DV_DangBan = 1");
$dv_tong = (int) gia_tri($conn, "SELECT COUNT(*) FROM dichvu");

$tu_khoa = trim((string) ($_GET['q'] ?? ''));
$loai = $_GET['loai'] ?? '';
$hien = $_GET['hien'] ?? '';
$dieu_kien = ['1 = 1'];
$tham_so = [];

if ($tu_khoa !== '') { $dieu_kien[] = "DV_Ten LIKE ?"; $tham_so[] = '%' . $tu_khoa . '%'; }
if (in_array($loai, ['phong_chup', 'thiet_bi', 'trang_phuc', 'phu_kien'], true)) { $dieu_kien[] = "DV_Loai = ?"; $tham_so[] = $loai; } else { $loai = ''; }
if ($hien === '1') {
    $dieu_kien[] = "DV_DangBan = ?";
    $tham_so[] = 1;
} elseif ($hien === '0') {
    $dieu_kien[] = "(DV_DangBan = ? OR (DV_Loai = ? AND EXISTS (
        SELECT 1 FROM baocao_huhong b
        WHERE b.DV_Ma = dichvu.DV_Ma AND b.BCHH_TrangThai <> ?
          AND b.BCHH_BoPhan IS NOT NULL AND TRIM(b.BCHH_BoPhan) <> ''
    )))";
    array_push($tham_so, 0, 'phong_chup', 'Đã xử lý');
}
$where = implode(' AND ', $dieu_kien);

$tong = (int) gia_tri($conn, "SELECT COUNT(*) FROM dichvu WHERE $where", $tham_so);
$tong_trang = 1;
$trang = trang_hien_tai($tong, $tong_trang);
$lech = ($trang - 1) * SO_DONG_MOI_TRANG;
$gioi_han = SO_DONG_MOI_TRANG;
$tham_so_danh_sach = array_merge(['Đang bảo trì'], $tham_so);
$ds = lay_nhieu(
    $conn,
    "SELECT DV_Ma, DV_Ten, DV_Loai, DV_GiaThue, DV_DonViTinh, DV_SoLuong, DV_DangBan, DV_HinhAnh,
            (SELECT COUNT(DISTINCT b.BCHH_BoPhan)
             FROM baocao_huhong b
             WHERE b.DV_Ma = dichvu.DV_Ma AND b.BCHH_TrangThai <> ?
               AND b.BCHH_BoPhan IS NOT NULL AND TRIM(b.BCHH_BoPhan) <> '') AS so_mon_chua_khac_phuc
     FROM dichvu WHERE $where ORDER BY DV_Ma DESC LIMIT $gioi_han OFFSET $lech",
    $tham_so_danh_sach
);
$ds_dich_vu_bao_cao = lay_nhieu(
    $conn,
    "SELECT DV_Ma, DV_Ten, DV_Loai, DV_MoTa, DV_MoTaChiTiet FROM dichvu ORDER BY DV_Ten"
);

nv_dau('Quản lý dịch vụ', 'dich-vu');
?>
        <section class="stats">
            <article class="stat-card"><p class="stat-label">Đơn chờ duyệt</p><p class="stat-number"><?= $don_cho ?></p><p class="stat-note"><a href="vendor-orders.php?tt=<?= urlencode(TRANG_THAI_CHO_DUYET) ?>" class="panel-link">Xử lý ngay</a></p></article>
            <article class="stat-card"><p class="stat-label">Đơn đã duyệt sắp tới</p><p class="stat-number"><?= $don_sap_toi ?></p><p class="stat-note">Chưa kết thúc thời gian thuê</p></article>
            <article class="stat-card"><p class="stat-label">Đơn bạn đã xử lý</p><p class="stat-number"><?= $don_toi_xu_ly ?></p><p class="stat-note">Duyệt, từ chối hoặc hoàn thành</p></article>
            <article class="stat-card"><p class="stat-label">Dịch vụ đang hiển thị</p><p class="stat-number"><?= $dv_hien ?>/<?= $dv_tong ?></p><p class="stat-note">Khách chỉ thấy dịch vụ đang hiển thị</p></article>
        </section>

        <section class="panel">
            <div class="panel-head"><h2>Báo cáo hư hỏng</h2></div>
            <form method="post" class="filter">
                <?= csrf_input() ?>
                <input type="hidden" name="hanh_dong" value="bao_cao_hu_hong">
                <input type="hidden" name="ve" value="<?= h($_SERVER['QUERY_STRING'] ?? '') ?>">
                <div class="field wide">
                    <label for="dich-vu-bao-cao">Dịch vụ / món bị hư</label>
                    <select id="dich-vu-bao-cao" name="ma" required>
                        <option value="">-- Chọn dịch vụ --</option>
                        <?php foreach ($ds_dich_vu_bao_cao as $dv_bao_cao) : ?>
                            <option value="<?= (int) $dv_bao_cao['DV_Ma'] ?>"
                                    data-loai="<?= h($dv_bao_cao['DV_Loai']) ?>"
                                    data-bo-phan="<?= h(json_encode(danh_sach_bo_phan_hu_hong($dv_bao_cao['DV_MoTaChiTiet'], $dv_bao_cao['DV_MoTa']), JSON_UNESCAPED_UNICODE) ?: '[]') ?>">
                                <?= h($dv_bao_cao['DV_Ten'] . ' (' . $dv_bao_cao['DV_Loai'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field wide" id="truong-bo-phan" style="display:none;">
                    <span style="display: block; font-weight: 600; margin-bottom: 6px;">Bộ phận hư hỏng trong phòng</span>
                    <details id="dropdown-bo-phan" style="position: relative;">
                        <summary style="cursor: pointer; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; background: #fff;">
                            <span id="nhan-bo-phan">Chọn bộ phận hư hỏng</span>
                        </summary>
                        <div id="ds-bo-phan" style="display: grid; grid-template-columns: 1fr; gap: 2px; position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 20; max-height: 180px; overflow-y: auto; padding: 8px; border: 1px solid #ddd; border-radius: 6px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,.12);"></div>
                    </details>
                </div>
                <div class="field wide">
                    <label for="mo_ta_hu_hong">Mô tả hư hỏng</label>
                    <textarea id="mo_ta_hu_hong" name="mo_ta_hu_hong" rows="1" maxlength="2000" required></textarea>
                </div>
                <button class="btn-solid" type="submit">Gửi báo cáo</button>
            </form>
            <script>
                const dichVuBaoCao = document.getElementById('dich-vu-bao-cao');
                const truongBoPhan = document.getElementById('truong-bo-phan');
                const dropdownBoPhan = document.getElementById('dropdown-bo-phan');
                const nhanBoPhan = document.getElementById('nhan-bo-phan');
                const danhSachBoPhan = document.getElementById('ds-bo-phan');

                function capNhatBoPhanBaoCao() {
                    const luaChon = dichVuBaoCao.options[dichVuBaoCao.selectedIndex];
                    const laPhong = luaChon && luaChon.dataset.loai === 'phong_chup';
                    truongBoPhan.style.display = laPhong ? '' : 'none';
                    dropdownBoPhan.open = false;
                    nhanBoPhan.textContent = 'Chọn bộ phận hư hỏng';
                    while (danhSachBoPhan.firstChild) {
                        danhSachBoPhan.removeChild(danhSachBoPhan.firstChild);
                    }
                    if (!laPhong) {
                        return;
                    }

                    const dsBoPhan = JSON.parse(luaChon.dataset.boPhan || '[]');
                    if (dsBoPhan.length === 0) {
                        nhanBoPhan.textContent = 'Chưa có danh sách bộ phận';
                        const chuaCo = document.createElement('p');
                        chuaCo.textContent = 'Chưa có danh sách trong mô tả chi tiết sản phẩm.';
                        danhSachBoPhan.appendChild(chuaCo);
                        return;
                    }
                    dsBoPhan.forEach(function (boPhan) {
                        const nhan = document.createElement('label');
                        nhan.style.display = 'flex';
                        nhan.style.alignItems = 'center';
                        nhan.style.gap = '8px';
                        nhan.style.padding = '8px';
                        nhan.style.cursor = 'pointer';

                        const oChon = document.createElement('input');
                        oChon.type = 'checkbox';
                        oChon.name = 'bo_phan[]';
                        oChon.value = boPhan;
                        oChon.addEventListener('change', function () {
                            const daChon = danhSachBoPhan.querySelectorAll('input[name="bo_phan[]"]:checked').length;
                            nhanBoPhan.textContent = daChon > 0
                                ? 'Đã chọn ' + daChon + ' bộ phận'
                                : 'Chọn bộ phận hư hỏng';
                        });

                        const ten = document.createElement('span');
                        ten.textContent = boPhan;

                        nhan.appendChild(oChon);
                        nhan.appendChild(ten);
                        danhSachBoPhan.appendChild(nhan);
                    });
                }

                dichVuBaoCao.addEventListener('change', capNhatBoPhanBaoCao);
                capNhatBoPhanBaoCao();
            </script>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2>Danh sách dịch vụ (<?= $tong ?>)</h2>
                <!-- Nhân viên không thêm dịch vụ mới; admin quản lý thêm mới. -->
            </div>

            <form class="filter" method="get">
                <div class="field wide"><label for="q">Tên dịch vụ</label><input id="q" name="q" value="<?= h($tu_khoa) ?>"></div>
                <div class="field">
                    <label for="loai">Loại</label>
                    <select id="loai" name="loai">
                        <option value="">Tất cả</option>
                        <?php foreach (['phong_chup', 'thiet_bi', 'trang_phuc', 'phu_kien'] as $m) : ?>
                            <option value="<?= $m ?>" <?= $loai === $m ? 'selected' : '' ?>><?= h(ten_loai_dich_vu($m)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="hien">Hiển thị</label>
                    <select id="hien" name="hien">
                        <option value="">Tất cả</option>
                        <option value="1" <?= $hien === '1' ? 'selected' : '' ?>>Đang hiển thị</option>
                        <option value="0" <?= $hien === '0' ? 'selected' : '' ?>>Đang bảo trì / Hư hỏng</option>
                    </select>
                </div>
                <button class="btn-solid" type="submit">Lọc</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Ảnh</th><th>Tên dịch vụ</th><th>Loại</th><th>Giá thuê</th><th>Số lượng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                    <tbody>
                        <?php if (!$ds) : ?><tr><td colspan="7" class="empty">Không tìm thấy dịch vụ nào.</td></tr><?php endif; ?>
                        <?php foreach ($ds as $d) : 
                            $dang_ban = (int) $d['DV_DangBan'] === 1;
                            $so_mon_chua_khac_phuc = (int) $d['so_mon_chua_khac_phuc'];
                            $trang_thai_chi_tiet = $dang_ban ? 'Đang hiển thị' : 'Đang bảo trì';
                            if ($d['DV_Loai'] === 'phong_chup' && $so_mon_chua_khac_phuc > 0) {
                                $trang_thai_chi_tiet = ($dang_ban ? 'Đang hiển thị' : 'Đang ẩn')
                                    . ' · ' . $so_mon_chua_khac_phuc . ' món chưa khắc phục';
                            } elseif (!$dang_ban) {
                                if ($d['DV_Loai'] === 'phong_chup') {
                                    $trang_thai_chi_tiet = gia_tri($conn, "SELECT PC_TrangThai FROM phongchup WHERE DV_Ma = ?", [$d['DV_Ma']]) ?: 'Đang bảo trì';
                                } elseif ($d['DV_Loai'] === 'thiet_bi') {
                                    $trang_thai_chi_tiet = gia_tri($conn, "SELECT TB_TinhTrang FROM thietbi WHERE DV_Ma = ?", [$d['DV_Ma']]) ?: 'Đang bảo trì';
                                }
                            }
                        ?>
                            <tr>
                                <td><?php if ($d['DV_HinhAnh']) : ?><img class="thumb" src="<?= h(duong_dan_anh($d['DV_HinhAnh'])) ?>" alt=""><?php endif; ?></td>
                                <td><?= h($d['DV_Ten']) ?></td>
                                <td><?= h(ten_loai_dich_vu($d['DV_Loai'])) ?></td>
                                <td><?= h(gia_hien_thi($d['DV_GiaThue'], $d['DV_DonViTinh'])) ?></td>
                                <td><?= (int) $d['DV_SoLuong'] ?></td>
                                <td>
                                    <span class="badge <?= $dang_ban ? 'badge-xac-nhan' : 'badge-khoa' ?>">
                                        <?= h($trang_thai_chi_tiet) ?>
                                    </span>
                                </td>
                                <td><a href="dich-vu-form.php?id=<?= (int) $d['DV_Ma'] ?>" class="panel-link">Sửa</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php hien_phan_trang($tong_trang, $trang); ?>
        </section>
<?php nv_cuoi(); ?>
