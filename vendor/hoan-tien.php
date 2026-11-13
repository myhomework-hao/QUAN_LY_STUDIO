<?php
require_once __DIR__ . '/khung_nv.php';

$ma_don = (int) ($_GET['ma'] ?? $_POST['ma'] ?? 0);
$don = lay_mot(
    $conn,
    "SELECT d.DDL_Ma, d.DDL_TrangThai, d.DDL_TrangThaiHoanTien, d.DDL_SoTienHoan, d.DDL_MaGiamGia,
            d.DDL_NganHangHoan, d.DDL_SoTKHoan, d.DDL_ChuTKHoan, d.DDL_ThongTinHoan,
            d.DDL_ThoiDiemHoanTien, nd.ND_Ten, nd.ND_Email
     FROM dondatlich d JOIN nguoidung nd ON nd.ND_Ma = d.ND_Ma
     WHERE d.DDL_Ma = ?",
    [$ma_don]
);

if (!$don) {
    dat_thong_bao('Không tìm thấy đơn thuê.', 'error');
    chuyen_huong('vendor-orders.php');
}

$tong_da_thu = tong_da_thu_don($conn, $ma_don);
$tt_hoan = (string) ($don['DDL_TrangThaiHoanTien'] ?? HOAN_TIEN_KHONG);
$ten_don = ma_don_hien_thi($ma_don);

$ds_ngan_hang = [
    '970436' => 'Vietcombank (VCB)',
    '970415' => 'VietinBank (CTG)',
    '970418' => 'BIDV',
    '970405' => 'Agribank',
    '970407' => 'Techcombank (TCB)',
    '970422' => 'MBBank (MB)',
    '970416' => 'ACB',
    '970432' => 'VPBank',
    '970423' => 'TPBank',
    '970403' => 'Sacombank (STB)',
    '970437' => 'HDBank',
    '970448' => 'OCB',
    '970429' => 'SCB',
    '970441' => 'VIB',
    '970443' => 'SHB',
    '970426' => 'MSB',
    '970431' => 'Eximbank',
    '970438' => 'BaoViet Bank',
    '970449' => 'LienVietPostBank',
    '970454' => 'VietCapitalBank',
];

$nguon = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$ngan_hang = trim((string) ($nguon['ngan_hang'] ?? ($don['DDL_NganHangHoan'] ?? '')));
$so_tk = trim((string) ($nguon['so_tk'] ?? ($don['DDL_SoTKHoan'] ?? '')));
$chu_tk = trim((string) ($nguon['chu_tk'] ?? ($don['DDL_ChuTKHoan'] ?? '')));
$thong_tin = trim((string) ($nguon['thong_tin'] ?? ($don['DDL_ThongTinHoan'] ?? '')));
$so_tien_hoan_raw = $nguon['so_tien_hoan'] ?? ($don['DDL_SoTienHoan'] ?? $tong_da_thu);
$so_tien_hoan = (float) str_replace([',', '.', ' '], '', (string) $so_tien_hoan_raw);
if ($so_tien_hoan <= 0 && $tong_da_thu > 0) {
    $so_tien_hoan = $tong_da_thu;
}

$loi = '';
$xac_nhan = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xac_nhan']));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $xac_nhan) {
    if (!csrf_hop_le()) {
        dat_thong_bao('Phiên làm việc không hợp lệ, vui lòng thử lại.', 'error');
        chuyen_huong('hoan-tien.php?ma=' . $ma_don);
    }

    if ($tt_hoan !== HOAN_TIEN_CHO) {
        dat_thong_bao("Đơn $ten_don không ở trạng thái chờ hoàn tiền.", 'error');
        chuyen_huong('hoan-tien.php?ma=' . $ma_don);
    } elseif (!isset($ds_ngan_hang[$ngan_hang])) {
        $loi = 'Vui lòng chọn ngân hàng nhận hoàn tiền.';
    } elseif ($so_tk === '' || !preg_match('/^[0-9A-Za-z]{4,30}$/', $so_tk)) {
        $loi = 'Vui lòng nhập số tài khoản hợp lệ (4-30 ký tự, chữ/số).';
    } elseif ($chu_tk === '' || mb_strlen($chu_tk) > 100) {
        $loi = 'Vui lòng nhập tên chủ tài khoản (tối đa 100 ký tự).';
    } elseif (mb_strlen($thong_tin) > 255) {
        $loi = 'Thông tin bổ sung không được dài quá 255 ký tự.';
    } elseif ($so_tien_hoan <= 0) {
        $loi = 'Số tiền hoàn phải lớn hơn 0.';
    } elseif ($so_tien_hoan - $tong_da_thu > 0.5) {
        $loi = 'Số tiền hoàn không được vượt quá số đã thu (' . dinh_dang_tien($tong_da_thu) . ').';
    } else {
        $bay_gio = date('Y-m-d H:i:s');
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare(
                "UPDATE dondatlich
                 SET DDL_TrangThaiHoanTien = ?, DDL_SoTienHoan = ?, DDL_NganHangHoan = ?, DDL_SoTKHoan = ?,
                     DDL_ChuTKHoan = ?, DDL_ThongTinHoan = ?, NV_MaHoanTien = ?, DDL_ThoiDiemHoanTien = ?
                 WHERE DDL_Ma = ? AND DDL_TrangThaiHoanTien = ?"
            );
            $stmt->execute([
                HOAN_TIEN_DA,
                $so_tien_hoan,
                $ngan_hang,
                $so_tk,
                $chu_tk,
                $thong_tin !== '' ? $thong_tin : null,
                $ma_nhan_vien,
                $bay_gio,
                $ma_don,
                HOAN_TIEN_CHO,
            ]);

            if ($stmt->rowCount() === 1) {
                $stmt_tt = $conn->prepare(
                    "INSERT INTO thanhtoan (DDL_Ma, TT_Loai, TT_SoTien, TT_PhuongThuc, TT_MaThamChieu, TT_TrangThai, TT_ThoiDiem)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt_tt->execute([
                    $ma_don,
                    'Hoàn tiền',
                    $so_tien_hoan,
                    'Chuyển khoản',
                    'RF-' . $ma_don . '-' . date('ymdHis'),
                    'Thành công',
                    $bay_gio,
                ]);
                $conn->commit();
                dat_thong_bao("Đã xác nhận hoàn tiền cho đơn $ten_don (" . dinh_dang_tien($so_tien_hoan) . ").", 'success');
                chuyen_huong('vendor-orders.php');
            } else {
                $conn->rollBack();
                dat_thong_bao("Đơn $ten_don không còn ở trạng thái chờ hoàn tiền.", 'error');
                chuyen_huong('hoan-tien.php?ma=' . $ma_don);
            }
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $loi = 'Không thể xác nhận hoàn tiền, vui lòng thử lại.';
        }
    }
}

// Tạo URL ảnh QR VietQR (cần Internet để tải ảnh). Dự phòng: luôn hiển thị thông tin chữ.
$qr_url = '';
if ($ngan_hang !== '' && $so_tk !== '' && $so_tien_hoan > 0) {
    $ten_qr = $chu_tk !== '' ? $chu_tk : 'Khach hang';
    $noi_dung = 'Hoan tien ' . $ten_don;
    $qr_url = 'https://img.vietqr.io/image/' . rawurlencode($ngan_hang) . '-' . rawurlencode($so_tk) . '-compact2.png'
        . '?amount=' . urlencode((string) (int) round($so_tien_hoan))
        . '&addInfo=' . urlencode($noi_dung)
        . '&accountName=' . urlencode($ten_qr);
}

nv_dau('Hoàn tiền ' . $ten_don, 'don');
?>
        <section class="panel">
            <div class="panel-head">
                <h2>Xử lý hoàn tiền - <?= h($ten_don) ?></h2>
            </div>

            <?php if ($tt_hoan === HOAN_TIEN_DA) : ?>
                <div class="notice" style="margin-bottom:16px;">
                    <span>Đơn này đã được xác nhận <strong>Đã hoàn tiền</strong> (<?= h(dinh_dang_tien($don['DDL_SoTienHoan'])) ?>) lúc <?= h(dinh_dang_ngay_gio($don['DDL_ThoiDiemHoanTien'])) ?>.</span>
                </div>
            <?php elseif ($tt_hoan !== HOAN_TIEN_CHO) : ?>
                <div class="notice" style="margin-bottom:16px;">
                    <span>Đơn này hiện không ở trạng thái <strong>Chờ hoàn tiền</strong> (trạng thái hoàn: <?= h($tt_hoan) ?>). Chỉ xử lý khi đơn đang chờ hoàn tiền.</span>
                </div>
            <?php endif; ?>

            <?php if ($loi !== '') : ?>
                <div class="notice error" style="margin-bottom:16px;"><span><?= h($loi) ?></span></div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
                <div>
                    <p><strong>Khách hàng:</strong> <?= h($don['ND_Ten']) ?> (<?= h($don['ND_Email']) ?>)</p>
                    <p><strong>Trạng thái đơn:</strong> <?= h($don['DDL_TrangThai']) ?></p>
                    <p><strong>Tổng đã thu (thành công):</strong> <?= h(dinh_dang_tien($tong_da_thu)) ?></p>
                    <p class="muted" style="margin:10px 0 16px;">Nhập thông tin nhận hoàn do khách cung cấp, kiểm tra số tiền và quét mã QR (cần Internet) hoặc chuyển theo thông tin chữ, sau đó bấm xác nhận đã hoàn tiền.</p>

                    <form method="post" class="stack" style="display:flex;flex-direction:column;gap:12px;max-width:520px;">
                        <?= csrf_input() ?>
                        <input type="hidden" name="ma" value="<?= (int) $ma_don ?>">

                        <div class="field">
                            <label for="ngan_hang">Ngân hàng nhận (BIN)</label>
                            <select id="ngan_hang" name="ngan_hang" required <?= $tt_hoan !== HOAN_TIEN_CHO ? 'disabled' : '' ?>>
                                <option value="">-- Chọn ngân hàng --</option>
                                <?php foreach ($ds_ngan_hang as $bin => $ten) : ?>
                                    <option value="<?= h($bin) ?>" <?= $ngan_hang === $bin ? 'selected' : '' ?>><?= h($ten) ?> (<?= h($bin) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="so_tk">Số tài khoản</label>
                            <input id="so_tk" name="so_tk" maxlength="30" required value="<?= h($so_tk) ?>" <?= $tt_hoan !== HOAN_TIEN_CHO ? 'disabled' : '' ?>>
                        </div>
                        <div class="field">
                            <label for="chu_tk">Chủ tài khoản</label>
                            <input id="chu_tk" name="chu_tk" maxlength="100" required value="<?= h($chu_tk) ?>" <?= $tt_hoan !== HOAN_TIEN_CHO ? 'disabled' : '' ?>>
                        </div>
                        <div class="field">
                            <label for="thong_tin">Thông tin cá nhân/ghi chú (khách cung cấp)</label>
                            <input id="thong_tin" name="thong_tin" maxlength="255" value="<?= h($thong_tin) ?>" <?= $tt_hoan !== HOAN_TIEN_CHO ? 'disabled' : '' ?>>
                        </div>
                        <div class="field">
                            <label for="so_tien_hoan">Số tiền hoàn (VND, ≤ <?= h(dinh_dang_tien($tong_da_thu)) ?>)</label>
                            <input id="so_tien_hoan" name="so_tien_hoan" type="number" min="1" step="1000" max="<?= (int) round($tong_da_thu) ?>" required value="<?= h((string) (int) round($so_tien_hoan)) ?>" <?= $tt_hoan !== HOAN_TIEN_CHO ? 'disabled' : '' ?>>
                        </div>

                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <button type="submit" name="xem_qr" value="1" class="btn-solid" <?= $tt_hoan !== HOAN_TIEN_CHO ? 'disabled' : '' ?>>Xem/Cập nhật mã QR</button>
                            <button type="submit" name="xac_nhan" value="1" class="btn-solid" <?= $tt_hoan !== HOAN_TIEN_CHO ? 'disabled' : '' ?> onclick="return confirm('Xác nhận đã chuyển khoản hoàn tiền cho khách?');">Xác nhận đã hoàn tiền</button>
                            <a href="vendor-orders.php" class="btn-ghost" style="text-decoration:none;display:inline-flex;align-items:center;">Quay lại</a>
                        </div>
                    </form>
                </div>

                <div>
                    <h3 style="margin-top:0;">Mã QR hoàn tiền (VietQR)</h3>
                    <?php if ($qr_url !== '') : ?>
                        <div style="padding:12px;border:1px solid #eaeaea;border-radius:12px;display:inline-block;background:#fff;margin-bottom:12px;">
                            <img src="<?= h($qr_url) ?>" alt="Mã QR hoàn tiền" style="max-width:280px;width:100%;height:auto;display:block;">
                        </div>
                        <p class="muted" style="font-size:12px;">Ảnh QR tải từ dịch vụ VietQR (cần Internet). Nếu không hiển thị, dùng thông tin bên dưới để chuyển khoản.</p>
                    <?php else : ?>
                        <p class="muted">Nhập ngân hàng, số tài khoản, chủ tài khoản và số tiền để hiển thị mã QR.</p>
                    <?php endif; ?>

                    <div style="margin-top:12px;padding:12px;border:1px dashed #ddd;border-radius:10px;background:#fafafa;">
                        <p style="margin:0 0 6px;"><strong>Thông tin chuyển khoản (dự phòng)</strong></p>
                        <p style="margin:0;">Ngân hàng: <?= h($ds_ngan_hang[$ngan_hang] ?? ($ngan_hang !== '' ? $ngan_hang : '—')) ?></p>
                        <p style="margin:0;">Số tài khoản: <?= h($so_tk !== '' ? $so_tk : '—') ?></p>
                        <p style="margin:0;">Chủ tài khoản: <?= h($chu_tk !== '' ? $chu_tk : '—') ?></p>
                        <p style="margin:0;">Số tiền hoàn: <strong><?= h(dinh_dang_tien($so_tien_hoan)) ?></strong></p>
                        <p style="margin:0;">Nội dung gợi ý: <?= h('Hoan tien ' . $ten_don) ?></p>
                        <?php if ($thong_tin !== '') : ?><p style="margin:6px 0 0;">Ghi chú KH: <?= h($thong_tin) ?></p><?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
<?php nv_cuoi(); ?>
