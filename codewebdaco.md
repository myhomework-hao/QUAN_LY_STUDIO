# CODE WEB ĐÃ LÀM — Ghi nhận chi tiết để đối chiếu báo cáo

> Tài liệu ghi lại toàn bộ việc đã làm trên dự án **VIBE Studio** (QUAN_LY_STUDIO) qua các lô,
> kèm phần **chưa làm** để đối chiếu. Ngày cập nhật: 11/11/2026.
>
> Quy tắc áp dụng khi làm: mỗi lô tối đa 5 file; chỉ SELECT trên CSDL (test xong xóa sạch dữ liệu test);
> không đổi schema; không commit git (mọi thay đổi nằm trên máy, chưa commit lần nào).

---

## PHẦN A — ĐÃ LÀM

### Lô 1 — Nền tảng bảo mật (xong 09/11)

| File | Đổi gì | Vì sao |
|---|---|---|
| `config/auth.php` | Chỉ nơi duy nhất gọi `session_start()`; thêm `session_set_cookie_params` (HttpOnly, SameSite) | Session an toàn hơn (chặn XSS đọc cookie, chống CSRF chéo site); mọi file khác không tự start session |
| `includes/security.php` | **Tạo mới.** Chứa `csrf_input()` (in token vào form) và `csrf_hop_le()` (kiểm token khi POST) | Mọi form POST phải có CSRF token; helper gom một chỗ để không trùng khai báo |
| `user/login.php`, `user/register.php` | CSRF token + giới hạn đăng nhập/đăng ký sai quá nhiều lần (ghi vào bảng `dangnhap_thatbai`: DNTB_Ma, DNTB_Email, DNTB_IP, DNTB_ThoiGian — bảng này **được duyệt** để thêm) | Chặn dò mật khẩu tự động |
| `database/.htaccess` | **Tạo mới.** `Require all denied` | Chặn truy cập `studio.sql` qua trình duyệt (file chứa email thật + mã băm mật khẩu) |

### Lô 2 — CSRF cho khu khách (xong 09/11)

| File | Đổi gì | Vì sao |
|---|---|---|
| `user/cart.php` | Form POST thêm `csrf_input()`, đầu file kiểm `csrf_hop_le()` | POST không token = ai đó gửi form thay khách được |
| `user/checkout.php` | Như trên | |
| `user/order-detail.php` | Như trên (form hủy đơn) | |

### Lô 4 — Giỏ hàng (xong 09/11, quyết định KHÔNG làm lại AJAX giỏ)

- Xác định giỏ nằm ở `$_SESSION['cart']`, đọc qua `includes/cart_helper.php`; bảng `giohang` trong CSDL **không** được code dùng.
- **Đã xóa** `api/cart_api.php` và `assets/js/cart.js` — là code chết (ghi sai cấu trúc giỏ, không trang nào nạp).
- Quyết định của lô: không viết lại tính năng AJAX giỏ.

### Lô 6+7 — Chat và đăng xuất (xong 09/11)

| File | Đổi gì | Vì sao |
|---|---|---|
| `assets/js/chat.js` | Sửa phần render: đọc `result.messages[]`, hỏi bot / nhận nhân viên qua poll 4 giây, dedup theo id tin nhắn, hiện thông báo chuyển tiếp nhân viên | Chat hiển thị sai/duplicated trước đó |
| `api/chat_api.php` | `chat_save_message()`: đọc `lastInsertId()` **ngay sau INSERT** (trước đó bị lệnh UPDATE reset ID trong MariaDB) → API trả về id thật | Tin nhắn gửi đi không có id để dedup |
| `user/chat.php` | Khách chưa đăng nhập bị redirect bằng `yeu_cau_dang_nhap()` đặt **TRƯỚC** `includes/header.php` (đặt sau thì `header('Location')` chạy âm thầm không hiệu quả) | |
| `api/chat_api.php`, `api/chat_nv_api.php` | CSRF cho JSON: `csrf_hop_le($data['csrf'] ?? null)` với token nằm trong body; gọi `session_write_close()` sau khi xác thực | API nhận JSON không đọc `$_POST`; nhả khóa session sớm cho request gõ liên tục |
| `admin/khung_admin.php`, `vendor/khung_nv.php`, `includes/taikhoanmenu.php` | Nút ĐĂNG XUẤT 1-click = **form POST kèm token** (không còn link GET) | Link GET đăng xuất dễ bị kích hoạt từ ngoài (email, trang lạ) |
| `assets/css/common.css` | Thêm style nút cho form đăng xuất | |
| Quyết định thống nhất | CSRF dùng **một key duy nhất `csrf`** ở mọi nơi | Trước đó nhiều key khác nhau dễ sót |

### Lô A — Tồn kho theo size + mã giảm giá trong transaction (xong 09/11, 5 file)

**Bối cảnh dữ liệu:** trang phục `DV_SoLuong = 10` nhưng tổng 3 size trong `dichvu_kichco` = 30 — đếm theo tổng dịch vụ là sai; phải đếm theo từng size.

| File | Đổi gì | Vì sao |
|---|---|---|
| `includes/cart_helper.php` | `kiem_tra_con_cho()` thêm tham số tùy chọn `$kich_co = ''`. Có size → khóa dòng `dichvu` (FOR UPDATE) vẫn giữ để 2 đơn xếp hàng, nhưng **tồn lấy từ `dichvu_kichco` của đúng size** và đếm `da_dat` thêm `AND c.CTD_KichCo = ?` | Tồn trang phục là theo size, không theo tổng |
| `includes/coupon_helper.php` | `tang_luot_dung_ma()`: UPDATE có điều kiện `AND (MG_SoLuong = 0 OR MG_DaDung < MG_SoLuong)`; `rowCount() === 0` → ném `DomainException` | Trước: 2 người bấm cùng lúc khi còn 1 lượt là cả hai được dùng mã |
| `user/checkout.php` | (1) Truyền `item['kich_co']` vào `kiem_tra_con_cho()`. (2) Tăng lượt mã chuyển **vào trong transaction, trước commit** — hết lượt thì rollback cả đơn (trước: tăng sau commit, lỗi chỉ ghi error_log → đơn vẫn dùng mã miễn phí) | Tính đúng đắn khi hai người tranh lượt cuối |
| `api/kiem_tra_kho.php` | Nhận `?kich_co=`; có size → `tong_kho`/`da_dat` tính riêng theo size (`dichvu_kichco` + `CTD_KichCo`); size lạ hoặc dịch vụ không có size → `con_lai 0` | Trang chi tiết chỉ hiện "còn X" theo tổng trước đó |
| `assets/js/at-chitiet.js` | Đọc ô `kich_co_chon`: gửi `kich_co` lên API kiểm kho, và kiểm lại khi khách đổi size | Đổi size là đổi tồn kho |

**Kiểm tra đã chạy:** `php -l` 4 file PHP + `node --check` JS ✅; API kiểm kho 4 trường hợp (không size / size M / size lạ XXL / thiết bị gửi size) ✅; đặt đơn E2E thành công (đơn DDL-00017, `CTD_KichCo='M'`, `MG_DaDung` tăng 2→3 trong transaction) ✅; unit test mã không tồn tại ném lỗi ✅. **Dữ liệu test (đơn 17 + thanh toán + chi tiết) đã xóa, `MG_DaDung` trả về 2.**

⚠️ Sửa trong quá trình làm: nhánh size của `api/kiem_tra_kho.php` đầu tiên quên thêm `CTD_KichCo = ?` vào SQL đếm → lỗi `HY093` khi test; đã sửa thành `$sql_dat` + `$tham_so_dat` gom tham số một chỗ.

### Lô B — Duyệt đơn kiểm lại tồn kho (xong 09/11, 3 file)

| File | Đổi gì | Vì sao |
|---|---|---|
| `user/cart.php:174` | `kiem_tra_con_cho(..., $item['kich_co'])` — giỏ cũng cảnh báo theo size (file sót của Lô A) | |
| `vendor/vendor-orders.php` | **Duyệt nằm trong transaction**: đọc toàn bộ `chitietdon` của đơn → `kiem_tra_con_cho()` từng dòng (đúng size, **loại trừ chính đơn đang duyệt**) → UPDATE có điều kiện `AND DDL_TrangThai = 'Chờ duyệt'` → `rowCount() = 1` mới commit; thiếu kho thì rollback + thông báo. Thêm `require_once ../includes/cart_helper.php` | Trước: duyệt không kiểm kho → đơn chồng lịch lọt qua dù checkout đã chặn |
| `includes/cart_helper.php` | Thêm tham số `$ma_don_bo_qua = 0`: khi kiểm lại lúc duyệt, `AND d.DDL_Ma <> ?` loại chính đơn khỏi số đã đặt | **Lỗi bắt được khi E2E:** đơn đang duyệt đã nằm ở `chitietdon` trạng thái 'Chờ duyệt', không loại trừ thì đếm 2 lần (8+8>10) → duyệt luôn bị chặn sai |

**Kiểm tra đã chạy:** E2E đủ cả hai đường — thiếu kho (đơn 8×size M + 2×size M cùng khung) bị chặn, đơn giữ 'Chờ duyệt', `NV_Ma` NULL ✅; đủ kho (đơn C 1×size M) chuyển 'Đã duyệt', ghi `NV_Ma` + `DDL_ThoiDiemXuLy` ✅. `php -l` ✅. **Dữ liệu test (đơn 18/19/20 + thanh toán + chi tiết) đã xóa sạch.**

⚠️ Lưu ý cho bạn: trong lúc test E2E, đơn 18/19 bị ai đó (rất có thể bạn bấm trên tab trình duyệt thật) đổi sang 'Đã từ chối', sau đó **đã bị xóa** trong bước dọn dữ liệu test (các DDL_Ma 18, 19, 20). Kiểm lại danh sách đơn nếu bạn từng bấm từ chối hai đơn này.

### Lô C — Tìm kiếm popup chống XSS (xong 11/11, 1 file)

| File | Đổi gì | Vì sao |
|---|---|---|
| `includes/search_popup.php` | (1) Thêm `data-base="<?= BASE_URL ?>"` vào `#search-popup`. (2) JS lấy `base` từ thuộc tính này thay vì `script.src` (script là **inline**, không có `src` — đường cũ suy ra `/` sai thư mục). (3) `buildPopup()` dựng bằng `createElement`/`createElementNS` (SVG icon), **bỏ gán `innerHTML`**, bỏ link `assets/css/search.css` (file không tồn tại → 404). (4) Bỏ `hintHtml = body.innerHTML`; ô nhập rỗng render lại gợi ý bằng `textContent` | `innerHTML` là đường XSS trực tiếp nếu dữ liệu ngoài lọt vào; `search.php` đã an toàn sẵn (mọi chỗ in qua `h()`) nên **không đụng** |

Còn giữ 2 chỗ `body.innerHTML = ''` — chỉ xóa trắng danh sách (chuỗi rỗng), không có dữ liệu nào vào.

**Kiểm tra đã chạy:** `php -l` 2 file ✅; `node --check` phần JS ✅; trang chủ chứa `data-base` đúng ✅; JSON search trả đúng 8 items ✅; chèn `q=<script>alert(1)</script>` vào trang kết quả → in ra `&lt;script&gt;` không chạy ✅.

### Lô D — Doanh thu / hoàn tiền (xác minh 11/11 — KHÔNG sửa code vì không có lỗi)

**Kết luận điểm nghi vấn 4: code đúng, không phải bug.** Dẫn chứng:

- `doanh_thu_khoang()` (`admin/khung_admin.php:78-88`): `WHERE DDL_TrangThai = 'Hoàn thành'` — doanh thu mọi nơi (`thong-ke.php:31-36`, `dashboard.php:13-14,41-48`) đều qua hàm này hoặc viết cùng mẫu.
- Grep toàn dự án: `FROM thanhtoan` chỉ được đọc ở `user/order-detail.php:139` để hiển thị — **không chỗ nào tính doanh thu theo bảng thanh toán**.
- SQL đối chiếu: doanh thu đơn Hoàn thành = 5.584.000đ = tổng `thanhtoan` của đúng các đơn đó (khớp từng đồng). Tiền của đơn hủy/từ chối đã 'Thành công' (1.350.000đ) **nằm ngoài mọi phép tính doanh thu**.
- `vendor/vendor-orders.php:86` đã có `UPDATE thanhtoan SET TT_TrangThai = 'Thành công' WHERE ... TT_TrangThai = 'Chờ'` khi hoàn thành — đúng thiết kế (QR thành công ngay khi đặt, tiền mặt chờ đến hoàn thành).

### Lô E — Phân trang admin + link sai (xong 11/11, 1 file)

| File | Đổi gì | Vì sao |
|---|---|---|
| `admin/nguoi-dung.php:77` | Link "Chi tiết" bảng đơn thuê: `BASE_URL . order-detail.php` → `BASE_URL . **user/**order-detail.php` | Thiếu `user/` → 404; `dashboard.php:166` viết đúng, chỗ này copy sót |

**Phân trang đối chiếu xong — không có lỗi, không sửa:** cả 3 trang `admin/dich-vu.php:98`, `admin/nguoi-dung.php:107`, `admin/nhan-vien.php:181` dùng đúng mẫu chung: `trang_hien_tai($tong, $tong_trang)` (tính `$tong_trang` **qua tham chiếu** — `khung_admin.php:119-123`, nên dòng `$tong_trang = 1;` khởi tạo ở nguoi-dung.php:106 vô hại) + `hien_phan_trang($tong_trang, $trang)` + `SO_DONG_MOI_TRANG = 10` (`khung_admin.php:12`). Link phân trang giữ filter (`q`, `tt`) nhờ `lien_ket_trang()` dựa trên `$_GET`.

**Kiểm tra đã chạy:** `php -l` ✅; trang chi tiết khách `?id=4` (khách có 15 đơn) — 3 link đầu đều `user/order-detail.php?id=16/15/14` ✅; phân trang thật trên `dich-vu.php` (30 dịch vụ): pager 4 trang, `?trang=3` có dữ liệu ✅; `nguoi-dung.php` (4 khách < 10) pager tự ẩn ✅; `?trang=99` không lỗi (min/max chặn) ✅.

---

## PHẦN B — CHƯA LÀM (chờ giao từng lô)

| # | Việc | File | Trạng thái |
|---|---|---|---|
| 1 | **Xác minh pattern UPDATE khu admin** — khóa/mở khóa khách (`admin/nguoi-dung.php:23-24`) UPDATE không có điều kiện trạng thái trong WHERE; `admin/dich-vu.php`, `admin/nhan-vien.php` cần đọc cùng pattern. Chỉ đọc + báo, sửa khi được giao | `admin/nguoi-dung.php`, `admin/dich-vu.php`, `admin/nhan-vien.php` | Chưa làm |
| 2 | **Điểm nghi vấn 8.2** — đơn vị `lượt` tính thế nào? CTD 8 (dịch vụ 24, thuê 9 ngày) bị tính 1.620.000 = 9 × 180.000, trong khi `tinh_so_don_vi()` (`includes/cart_helper.php`) hiện trả **1** cho `luot`/`bo` — đơn cũ tính theo ngày, đơn mới sẽ tính 1 lượt. Cần đối chiếu + chốt (xác nhận đơn cũ không bị tính lại) | `includes/don_vi.php`, `includes/cart_helper.php` | Chưa làm |
| 3 | **Điểm nghi vấn 8.3** — đặt ngoài giờ mở cửa? CTD 15 bắt đầu 21:00 dù `gio_dong_cua` = 20:00, `cho_phep_chon_ngoai_gio` = 0. Cần xác nhận quy tắc giờ có áp dụng cho dịch vụ tính theo `ngày` không | logic giờ trong `user/checkout.php`, `api/kiem_tra_kho.php` | Chưa làm |

## PHẦN C — CHỜ NHÓM QUYẾT ĐỊNH rồi mới làm

| # | Việc | Ghi chú |
|---|---|---|
| 1 | **Trả lượt mã giảm giá khi hủy/từ chối đơn?** (8.5) — VIBE10 `MG_DaDung = 1` nhưng đơn duy nhất dùng nó đã `Đã hủy`. Nếu trả lượt sẽ đụng `vendor/vendor-orders.php`, `user/my-orders.php` | Cần chính sách của nhóm |
| 2 | **Cơ chế hoàn tiền** cho đơn đã thanh toán rồi hủy/từ chối — chưa có; nếu làm phải xử lý 1.350.000đ "tiền đã thu của đơn chết" trong dữ liệu cũ | Mục 7: KHÔNG tự thêm |
| 3 | **Chốt trạng thái "đang chiếm chỗ"** — hiện code đếm `'Chờ duyệt'` + `'Đã duyệt'`; `'Hoàn thành'`/'Đã hủy'/'Đã từ chối' không chặn đặt mới. Cần nhóm gật đầu lần cuối | Đã mô tả ở mục 6.2 |

## PHẦN D — SINH VIÊN TỰ LÀM

1. **Xuất lại `database/studio.sql`**: CSDL thật có **21 bảng**, file SQL chỉ có **18** — thiếu `cau_hinh` và `dangnhap_thatbai` (đã duyệt ở lô 1).
2. **Làm sạch dữ liệu cá nhân** trong dump (email thật, mã băm mật khẩu) trước khi nộp / đưa lên GitHub (mục 8.8).
3. Điền **PHÂN CÔNG NHÓM** trong `agents.md` mục 13 (đang trống).

## PHẦN E — PHÁT HIỆN THÊM (chưa xử lý, chưa ai giao)

1. Font Awesome trên trang kết quả tìm kiếm nạp từ CDN `cdnjs.cloudflare.com` (`includes/search.php:90`) — có trước, KHÔNG phải CDN mới; lưu ý nếu phòng máy chặn internet thì mất icon.
2. Đường dự phòng `buildPopup()` trong `includes/search_popup.php` thực tế không bao giờ chạy (header.php:113 luôn nhúng sẵn popup) — đã sửa cho đúng nhưng giữ lại; muốn xóa hẳn thì nhóm quyết.
3. Cột `lichthue` có vẻ là bảng cũ không được code cập nhật (mục 8.6 cũ) — chỉ liệt kê, không xóa.
4. Collation lẫn lộn giữa các bảng (vietnamese_ci / unicode_ci / general_ci) — có thể gây "Illegal mix of collations" khi JOIN chuỗi (mục 8.11 cũ).

---

## PHỤ LỤC — File bị đụng tổng hợp (để rà lại khi đối chiếu)

**Đã sửa:** `config/auth.php`, `user/login.php`, `user/register.php`, `user/cart.php`, `user/checkout.php`, `user/order-detail.php`, `user/chat.php`, `user/logout.php` (form POST), `includes/security.php` (mới), `includes/cart_helper.php`, `includes/coupon_helper.php`, `includes/taikhoanmenu.php`, `includes/search_popup.php`, `assets/js/at-chitiet.js`, `assets/js/chat.js`, `assets/css/common.css`, `admin/khung_admin.php`, `admin/nguoi-dung.php`, `vendor/khung_nv.php`, `vendor/vendor-orders.php`, `api/chat_api.php`, `api/chat_nv_api.php`, `api/kiem_tra_kho.php`, `database/.htaccess` (mới), `agents.md`.

**Đã xóa (code chết):** `api/cart_api.php`, `assets/js/cart.js` — quyết định của lô 4.

**Chưa commit gì.** Dữ liệu test trong CSDL đã dọn sạch (đơn 17, 18, 19, 20 + chi tiết + thanh toán; `MG_DaDung` VIBE10 trả về 2; file session test + file tạm đã xóa).
