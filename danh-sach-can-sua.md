# DANH SÁCH CẦN SỬA — Kết quả rà soát toàn cây thư mục (15/11/2026)

> **CẬP NHẬT 15/11:** mục 1, 2, 3, 4 (Mức 1) và mục 6, 8, 9, 10 (Mức 2) **ĐÃ SỬA** — chi tiết ở mục "KẾT QUẢ SỬA" cuối file. Chưa sửa: mục 5, 7 (cần nhóm chốt), mục 11-14 (cập nhật tài liệu — mục 11, 13 đã cập nhật vào `agents.md`).

> Rà 49 file PHP + JS: `php -l` 49/49 sạch, quét pattern (innerHTML, md5, mysqli, GET-logout,
> SQL nối chuỗi, session_start, NOW()/date()), đọc kỹ khu user/ + includes/ + vendor/ + admin/ + api/.
> Mỗi mục đều có **file:dòng** và đã **đọc/kiểm thật** — không phán theo tên file.
> Danh sách này CHỈ LIỆT KÊ, chưa sửa gì (mỗi lô tối đa 5 file, chờ duyệt).

---

## MỨC 1 — LỖI THẬT, NÊN SỬA TRƯỚC

### 1. `user/services.php:124,126` — gọi hàm không tồn tại → Fatal khi có dịch vụ ẩn
```php
124:  $nhan_trang_thai = gia_tri($conn, "SELECT PC_TrangThai FROM phongchup WHERE DV_Ma = ?", ...) ?: 'Đang bảo trì';
126:  $nhan_trang_thai = gia_tri($conn, "SELECT TB_TinhTrang FROM thietbi WHERE DV_Ma = ?", ...) ?: 'Đang bảo trì';
```
- `gia_tri()` chỉ được định nghĩa trong `admin/khung_admin.php:19` và `vendor/khung_nv.php:28` — chuỗi include của `services.php` (app, db, dich_vu, header, footer) **không nạp file nào có hàm này**.
- Lỗi chỉ nổ khi trang chạy vào nhánh dịch vụ ẩn (`$dang_ban` false, dòng 121-127). **Hiện CSDL có 0 dịch vụ `DV_DangBan = 0`** nên chưa nổ — nhưng tạo 1 dịch vụ ẩn là trang SẢN PHẨM chết cả phần lặp.
- *Cách sửa gợi ý:* tự fetchColumn 2 câu SELECT (không cần hàm chung), hoặc đưa `gia_tri()` vào file dùng chung — nếu chọn cách 2 thì phải xử lý luôn mục 2.

### 2. `user/profile.php:27` — redirect tới trang không tồn tại → 404
```php
header('Location: ' . BASE_URL . 'login.php');
```
- File `login.php` **không có ở gốc dự án**; thật là `user/login.php`. Lỗi nổ khi session trỏ tới user đã bị xóa khỏi DB.

### 3. `vendor/my-services.php:5-6` — kiểm quyền bằng key session không tồn tại (code chết, gây hiểu nhầm)
```php
5:  $vai_tro_hien_tai = $_SESSION['ND_VaiTro'] ?? $_SESSION['vai_tro'] ?? $_SESSION['quyen'] ?? '';
6:  $la_nhan_vien_hoac_admin = in_array($vai_tro_hien_tai, ['admin', 'nhan_vien', 'Admin', 'NhanVien'], true) || !empty($ma_nhan_vien);
```
- Session thật lưu key `role` (số, xem `config/auth.php:36-37`); **cả 3 key trên không tồn tại** → `$vai_tro_hien_tai` luôn `''`.
- Giá trị `'nhan_vien'`/`'NhanVien'` cũng không khớp chuẩn `'nhanvien'`.
- *Hiện tại không lộ quyền* vì `khung_nv.php:11` đã chặn role trước (`yeu_cau_vai_tro('nhanvien')`) và mọi nhân viên đến đây đều có `$ma_nhan_vien` ≠ null → điều kiện rơi vào `!empty($ma_nhan_vien)` luôn đúng.
- *Cần nhóm chốt thêm:* `khung_nv` chặn **admin** vào khu vendor — admin muốn quản lý dịch vụ chỉ có `admin/dich-vu.php`. Đúng ý thiết kế không?

### 4. `includes/dich_vu.php:10-20` — hàm chết với key sai chuẩn
```php
'may_anh' => ..., 'studio' => ..., 'tho_chup' => ...   // chuẩn DB là 'thiet_bi', 'phong_chup'
```
- `thong_tin_loai()` **không được gọi ở đâu** trong cả dự án (grep toàn cây chỉ thấy định nghĩa) → xóa hoặc sửa key khi được giao.

---

## MỨC 2 — MỒI LỖI / CẦN CHỐT

### 5. Hàm trùng khai báo giữa 3 file khung (chưa nổ vì chưa trang nào nạp cả hai)
| Hàm | Định nghĩa |
|---|---|
| `h()` | `includes/dich_vu.php:5` + `admin/khung_admin.php:14` + `vendor/khung_nv.php:23` |
| `gia_tri()` | `admin/khung_admin.php:19` + `vendor/khung_nv.php:28` |
| `lay_nhieu()` | `admin/khung_admin.php:26` + `vendor/khung_nv.php:35` |
| `lay_mot()` | `admin/khung_admin.php:33` + `vendor/khung_nv.php:42` |

- ⚠️ Nếu sửa mục 1 bằng cách include `khung_nv.php` vào khu user → **nổ "Cannot redeclare" ngay lập tức** (khung_nv nạp donhang/don_vi/security và gọi `yeu_cau_vai_tro('nhanvien')` chặn cả khách).
- *Đề xuất:* gom 4 hàm này vào một file helper dùng chung (vd `includes/truy_van.php`) rồi 2 khung require nó — file dùng chung, **phải hỏi trước khi sửa**.

### 6. Transaction cóBegin nhưng không có try/catch/rollback (chủ đích) hoặc thiếu transaction
- `admin/nhan-vien.php:32-35` — khóa nhân viên: `beginTransaction()` + 2 UPDATE + `commit()` **không có try/catch** (lệnh 2 ném lỗi thì ngoại lệ văng, không rollback có chủ đích; nhánh 'luu' cùng file 95-115 có try/catch đầy đủ — hai nửa không đồng nhất).
- `vendor/vendor-orders.php:130-141` — nhánh `hoan_thanh` có transaction **không try/catch** (3 nhánh còn lại đều có).
- `vendor/my-services.php:32-41` — khóa/mở khóa dịch vụ: UPDATE `dichvu` + UPDATE bảng con **không bọc transaction** (lệnh 2 lỗi thì trạng thái lệch nhau).

### 7. `vendor/vendor-orders.php:102-105` — hủy đơn đã duyệt ghi đè `NV_Ma` (mất người duyệt gốc)
```php
SET DDL_TrangThai = ?, NV_Ma = ?, DDL_ThoiDiemXuLy = ?, DDL_ThoiDiemHuy = ?, DDL_LyDoHuy = ?
```
- `NV_Ma` theo thiết kế là "nhân viên duyệt/từ chối" — nhân viên khác hủy đơn sẽ xóa mã của người đã duyệt. **Cần nhóm chốt** (muốn giữ lịch sử thì cần cột riêng — đổi schema phải xin duyệt).

### 8. Upload ảnh `vendor/dich-vu-form.php` (`luu_anh()` dòng 35-63) — đạt 3/5, thiếu 2 chuẩn
- ✅ Whitelist MIME (finfo), giới hạn 3MB, `move_uploaded_file`, không dùng tên gốc.
- ❌ **Thiếu `getimagesize()`** — file chế header JPEG/PNG giả qua được finfo.
- ❌ Tên ngẫu nhiên ngắn: `bin2hex(random_bytes(3))` = 6 ký tự + timestamp (chuẩn AGENTS là 8 bytes).

### 9. `vendor/my-services.php:29-39` — lý do tình trạng không kiểm server-side
- `$ly_do` từ POST được lưu thẳng vào `PC_TrangThai`/`TB_TinhTrang` (varchar tự do) — chế tay POST được lưu chuỗi tùy ý. Cần whitelist server-side.

### 10. `api/kiem_tra_kho.php:43` — chuỗi trạng thái cứng `'Chờ duyệt', 'Đã duyệt'`
- Các file khác đều dùng hằng `TRANG_THAI_*` trong `includes/donhang.php`; một file dùng chuỗi cứng là chỗ dễ gõ sai dấu trong tương lai. (Nhất quán hóa, không khẩn.)

---

## MỨC 3 — TÀI LIỆU LỆCH VỚI CODE (agents.md cần cập nhật — code có thể là phần sinh viên làm thêm)

### 11. **Luồng hoàn tiền ĐÃ TỒN TẠI** — agents.md ghi "chưa có cơ chế hoàn tiền" là SAI
- `vendor/hoan-tien.php` (trang xử lý hoàn tiền: form QR hoàn tiền + "Xác nhận đã hoàn tiền", có CSRF dòng 62/176, link từ `vendor-orders.php:227`).
- `phat_sinh_cho_hoan_tien()` — `includes/donhang.php:81-119` (từ chối/hủy đơn đã thanh toán → tự chuyển `DDL_TrangThaiHoanTien = 'Chờ hoàn tiền'`).
- `studio.sql` đã có 8 cột hoàn tiền: `DDL_CCCD` (366), `DDL_TrangThaiHoanTien` (370), `DDL_SoTienHoan`, `DDL_NganHangHoan`, `DDL_SoTKHoan`, `DDL_ChuTKHoan`, `DDL_ThongTinHoan`, `NV_MaHoanTien` (376), `DDL_ThoiDiemHoanTien` (377) + 2 index (958-959) + FK (1191).
- Tích hợp sẵn ở: `vendor/vendor-orders.php:77,107`, `user/order-detail.php:68-71`, `user/my-orders.php:65-70`, `admin/dashboard.php:165-166`, `admin/nguoi-dung.php:78-79`.
- → Mục 8.4 cũ (điểm nghi vấn 4) cần viết lại: hiện tượng "không có bản ghi hoàn tiền" đã được giải quyết bằng luồng này hay chưa phải đối chiếu dữ liệu đơn 3/9.

### 12. `user/thanh-toan.php` — trang QR giả lập không có trong mục 4 (cấu trúc) của agents.md.

### 13. `lichthue` KHÔNG phải bảng chết (cập nhật mục 8.6)
- Được **đọc** ở `includes/cart_helper.php:186-193`: chặn đặt khi dịch vụ bị khóa lịch thủ công (`LT_TrangThai = 'khóa'`).
- Nhưng **không có chỗ nào ghi** (INSERT/UPDATE/DELETE: 0 kết quả toàn dự án) → nửa tính năng: có kiểm tra khóa nhưng không ai tạo khóa được. Cần nhóm chốt: bỏ check, hay làm trang quản lý khóa lịch.

### 14. `vendor/dich-vu-form.php:6` — cho phép đơn vị `'bộ'` ngoài dữ liệu hiện có (`giờ`/`ngày`/`lượt`)
- `includes/don_vi.php` đã xử lý `'bo'` (chuẩn hóa + `tinh_so_don_vi()` trả 1) → **không phải lỗi tính tiền**, chỉ lưu ý dữ liệu sẽ có đơn vị thứ 4.

---

## MỨC 4 — ĐÃ KIỂM, SẠCH (không cần sửa)

| Hạng mục | Kết quả |
|---|---|
| Cú pháp PHP | 49/49 file `php -l` sạch |
| CSRF mọi handler POST | ✅ 0 file POST thiếu `csrf_hop_le()` (kể cả `hoan-tien.php:62`) |
| XSS server-side | ✅ Mọi `<?= ... ?>` đều qua `h()`/`htmlspecialchars`/`(int)`; `nl2br` sau escape đúng thứ tự |
| XSS JS | ✅ chat.js/chat_widget.js/vendor chat.php render bằng `textContent`; innerHTML còn lại là chuỗi cứng (icon) hoặc `''` (xóa trắng) |
| SQL injection | ✅ Prepared statement khắp nơi; SQL động đều ghép từ hằng/int (`LIMIT` từ hằng 10, `$trang` đã int; `$thuTu` chat_nv_api 2 giá trị cứng) |
| UPDATE đơn thiếu điều kiện trạng thái | ✅ 4 nhánh vendor-orders (duyệt/từ chối/hủy/hoàn thành) đều có `AND DDL_TrangThai = ?` + rowCount |
| Quyền sở hữu khách | ✅ profile/my-orders đều lọc theo session `ND_Ma` |
| md5 / mysqli / GET-logout / session_start rải rác | ✅ 0 (md5 chỉ làm khóa giỏ `cart.php:93` — không phải mật khẩu; session_start chỉ ở `config/auth.php:12`) |
| Trộn NOW() MySQL vs date() PHP | ✅ Không thấy trộn trong cùng phép so sánh |
| Include file không tồn tại | ✅ (ngoài 2 mục 1, 2 ở trên) |

---

## ĐỀ XUẤT LÔ TIẾP THEO (tối đa 5 file/lô)

- **Lô F (đề xuất):** mục 1 + 2 + 3 + 4 (4 file: `user/services.php`, `user/profile.php`, `vendor/my-services.php`, `includes/dich_vu.php`) — toàn lỗi thật, mỗi chỗ sửa nhỏ.
- **Lô G:** mục 5 (gom helper dùng chung — cần bạn hỏi nhóm trước) + mục 6 (3 chỗ transaction).
- **Lô H:** mục 8 + 9 + 10 (upload + whitelist + nhất quán hằng).
- **Lô Tài liệu:** mục 11-14 chỉ cập nhật `agents.md` (không đụng code).

---

## KẾT QUẢ SỬA (15/11 — Lô F + Lô G, đã duyệt "sửa hết")

### Lô F (4 file)
| File | Sửa gì |
|---|---|
| `user/services.php:121-135` | Thay `gia_tri()` (hàm không có trong chuỗi include → Fatal khi có dịch vụ ẩn) bằng prepared statement + `fetchColumn()` trực tiếp cho 2 nhánh `phongchup`/`thietbi` |
| `user/profile.php:27` | Redirect `BASE_URL . 'login.php'` → `BASE_URL . 'user/login.php'` (bỏ 404) |
| `vendor/my-services.php:4-5` | Kiểm quyền bằng key session không tồn tại (`ND_VaiTro`/`vai_tro`/`quyen`) → `in_array(vai_tro_hien_tai(), ['nhanvien','admin'], true)` |
| `includes/dich_vu.php:12-18` | Key `thong_tin_loai()` `may_anh`/`studio`/`tho_chup` → `thiet_bi`/`phong_chup` (bỏ `tho_chup` không có trong DB) |

### Lô G (4 file)
| File | Sửa gì |
|---|---|
| `admin/nhan-vien.php:31-42` | Khóa/mở NV: beginTransaction + 2 UPDATE → thêm try/catch + rollback có chủ đích |
| `vendor/vendor-orders.php:129-150` | Nhánh `hoan_thanh`: transaction có try/catch + rollback (đồng bộ với 3 nhánh còn lại) |
| `vendor/dich-vu-form.php` | Upload: thêm `getimagesize()` (chặn file giả định dạng ảnh); tên ngẫu nhiên `random_bytes(3)` → `random_bytes(8)` |
| `api/kiem_tra_kho.php` | Chuỗi `'Chờ duyệt','Đã duyệt'` cứng → placeholder + hằng `TRANG_THAI_CHO_DUYET`/`TRANG_THAI_DA_DUYET` (nạp thêm `includes/donhang.php`); cũng sửa luôn `vendor/my-services.php` thêm whitelist `$ly_do` ∈ {'Đang bảo trì','Hư hỏng'} + bọc transaction khóa/mở dịch vụ |

### Kiểm tra đã chạy
- `php -l` 8/8 file sạch.
- E2E `kiem_tra_kho.php`: không size / size M / thiết bị gửi size → JSON đúng như trước khi đổi ✅.
- `user/services.php` nạp 200, không Fatal/Warning ✅.
- Chưa chạy được: đường dịch vụ ẩn (DB hiện 0 dịch vụ `DV_DangBan = 0` — sinh viên tạo 1 dịch vụ ẩn rồi xem trang Sản Phẩm để xác nhận); upload ảnh thật qua form `dich-vu-form.php`; khóa/mở dịch vụ + nhân viên qua giao diện.

### Chưa sửa (cần nhóm chốt — xem mục 5, 7)
- Mục 5: gom `h()`/`gia_tri()`/`lay_nhieu`/`lay_mot` trùng 3 file khung vào helper dùng chung (đụng file chung, cần nhóm đồng ý; rủi ro redeclare đã giảm vì mục 1 sửa bằng code nội bộ).
- Mục 7: `vendor-orders.php:102-105` hủy đơn ghi đè `NV_Ma` — cần quyết định thiết kế (giữ lịch sử cần cột mới → phải xin duyệt schema).

