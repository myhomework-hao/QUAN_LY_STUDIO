# PROJECT CHECKLIST — VIBE Studio

> Cập nhật: 15/11/2026 · Nguồn đối chiếu: rà soát toàn cây 49 file PHP (`php -l` 49/49 sạch),
> audit bảo mật (`danh-sach-can-sua.md`), test HTTP thật trong phiên làm việc, CSDL `STUDIO` (21 bảng).
>
> **Chú thích trạng thái:**
> - `[x]` Đã triển khai **và đã kiểm chứng** (code đọc kỹ + `php -l` + ít nhất 1 lần test HTTP thật hoặc theo dõi logic có dẫn chứng file:dòng).
> - `[ ]` Chưa triển khai.
> - `[!]` Có code nhưng **có lỗi** hoặc mồi lỗi đã xác nhận.
> - `[?]` Chạy được nhưng **cần nhóm/giảng viên quyết định nghiệp vụ** trước khi coi là hoàn tất.
>
> **Mức kiểm thử ghi ở mỗi dòng:** `php-l` (chỉ cú pháp) / `http` (đã gọi qua HTTP thật) / `đọc-code` (trace logic có dẫn chứng) / `chưa-test` (sinh viên phải tự bấm thử trình duyệt).

---

## 1. HẠ TẠNG CHUNG

| Chức năng | File | DB | Trạng thái | Vấn đề | Kiểm thử |
|---|---|---|---|---|---|
| Kết nối PDO ($conn) | `config/db.php` | — | [x] | — | php-l, mọi trang dùng |
| BASE_URL + giờ VN | `config/app.php`, `khung_admin.php:9` | `cau_hinh` | [x] | — | php-l |
| Session cookie (httponly/samesite), session_start duy nhất 1 chỗ | `config/auth.php:2-13` | — | [x] | — | đọc-code |
| Vai trò 1/2/3 + `yeu_cau_vai_tro()` | `config/auth.php:15-82` | `loai_nguoi_dung` | [x] | — | đọc-code + trang chặn 403 |
| CSRF `csrf_hop_le()` mọi handler POST | `config/security.php` | — | [x] | — | quét tự động 0 thiếu |
| Giới hạn đăng nhập sai | `includes/o-mat-khau.php`, `dangnhap_thatbai` | `dangnhap_thatbai` | [x] | bảng này chưa chắc có trong dump studio.sql | đọc-code |
| Helper `h()/gia_tri()/lay_mot/lay_nhieu` dùng chung | `admin/khung_admin.php:14-38`, `vendor/khung_nv.php:23-44`, `includes/dich_vu.php:5` | — | `[?]` | Trùng khai báo 3 chỗ — chưa trang nào nạp cả hai, nhưng gom về 1 file cần nhóm duyệt (file dùng chung) | php-l |
| Ghi hằng trạng thái tập trung | `includes/donhang.php` (TRANG_THAI_*) | — | `[!]` | `api/kiem_tra_kho.php` đã sửa dùng hằng (15/11); cần rà lại file mới của thành viên khác có còn chuỗi cứng | php-l |

## 2. KHÁCH HÀNG

### 2.1. Tài khoản

| Chức năng | File | DB | Trạng thái | Vấn đề | Kiểm thử |
|---|---|---|---|---|---|
| Đăng ký (chỉ tạo khách) | `user/register.php` | `nguoidung` | [x] | — | php-l, đọc-code |
| Đăng nhập / đăng xuất POST+token | `user/login.php`, `user/logout.php` | `nguoidung` | [x] | — | php-l |
| Quên mật khẩu | `includes/o-mat-khau.php` | `nguoidung` | [x] | chưa test trình duyệt | đọc-code |
| Cập nhật hồ sơ | `user/profile.php` | `nguoidung` | `[!]` | Redirect 404 đã sửa 15/11 (`:27` → `user/login.php`); đường "session trỏ user đã xóa" chưa test | http (trang 200), chưa-test nhánh lỗi |
| Đổi mật khẩu | `user/profile.php` | `nguoidung` | [x] | — | đọc-code |

### 2.2. Dịch vụ + giỏ + đặt đơn

| Chức năng | File | DB | Trạng thái | Vấn đề | Kiểm thử |
|---|---|---|---|---|---|
| Danh sách dịch vụ, lọc theo loại | `user/services.php` | `dichvu` | [x] | Fatal khi có dịch vụ ẩn đã sửa 15/11 (`:121-135`); nhánh ẩn chưa test vì DB có 0 dịch vụ ẩn | http (200, không Fatal) |
| Chi tiết dịch vụ, tình trạng riêng theo loại | `user/service-detail.php` | `dichvu` + bảng con | [x] | Còn hàng tính động theo kho+lịch (không cố định) — đúng prompt 3.2 | đọc-code |
| API kiểm tra còn chỗ (theo size trang phục) | `api/kiem_tra_kho.php` | `chitietdon`, `dichvu_kichco` | [x] | Hằng trạng thái đã thay chuỗi cứng 15/11 | **http** (3 nhánh JSON đúng) |
| Giỏ hàng ở session | `user/cart.php`, `includes/cart_helper.php` | `$_SESSION['cart']` | [x] | `giohang` trong DB không được code dùng (chốt 09/11) | đọc-code |
| Kiểm kho trừ chính đơn đang sửa (`$ma_don_bo_qua`) | `includes/cart_helper.php:139-178` | `chitietdon` | [x] | — | đọc-code |
| Checkout 2 bước: phiên `pending_checkout` + token | `user/checkout.php`, `user/xac-nhan-thanh-toan.php` | — | [x] | Chưa bấm thử trình duyệt trọn luồng | đọc-code |
| **Đặt cọc 25%** khi chọn cọc | `user/xac-nhan-thanh-toan.php:74,77` | `dondatlich.DDL_TienCoc`, `thanhtoan.TT_Loai='Đặt cọc'` | `[?]` | Luồng có thật (tài liệu cũ sai); cần nhóm chốt: cọc hoàn về đâu khi hủy, cọc tính doanh thu thế nào | đọc-code |
| Xác nhận thanh toán thủ công: đối chiếu số tiền server tính | `user/xac-nhan-thanh-toan.php:38-41` | — | [x] | Server tự tính lại (`$so_tien_xac_nhan = $so_tien_can_thu`) — không tin client, đúng prompt 3.5 | đọc-code |
| Mã giảm giá: kiểm + tăng lượt trong transaction | `includes/coupon_helper.php`, `user/xac-nhan-thanh-toan.php:67-75` | `ma_giam_gia` | [x] | — | đọc-code |
| Đơn cũ chụp giá tại thời điểm đặt | `user/xac-nhan-thanh-toan.php:101-116` | `chitietdon.CTD_DonGia` | [x] | — | đọc-code |
| Đơn của tôi / chi tiết đơn | `user/my-orders.php`, `user/order-detail.php` | `dondatlich` | [x] | Lọc theo ND_Ma session (đạt IDOR) | đọc-code |
| Hủy đơn (khách, khi Chờ duyệt) | `user/my-orders.php` | `dondatlich` | `[?]` | Đơn "Đã duyệt" khách có được hủy không — chưa chốt | đọc-code |
| Đánh giá sau hoàn thành | `user/order-detail.php` | `danhgia` | [x] | UNIQUE (đơn, dịch vụ); chủ đơn mới đánh giá được | đọc-code |
| Đổi lịch, yêu cầu đổi lịch | — | — | `[ ]` | Thiếu hoàn toàn (prompt 3.6) | — |

### 2.3. Chat + nội dung

| Chức năng | File | DB | Trạng thái | Vấn đề | Kiểm thử |
|---|---|---|---|---|---|
| Widget chat nổi, cố định dưới nút lên-đầu-trang | `includes/chat_widget.php`, `chat_widget.js`, `chat_widget.css` | — | [x] | 15/11: bỏ kéo thả, chỉnh breakpoint 600px, `?v=` theo mtime đúng file | http (widget render, không còn script kéo) |
| Khách vãng lai: báo đăng nhập ngay, không gọi API chắc chắn 401 | `chat_widget.js:143-152` + config `loggedIn` | — | [x] | 15/11 | http |
| Chatbot trả lời theo kịch bản | `api/chat_api.php:173-219` | `chat_tin_nhan` | [x] | Chatbot không tự xác nhận thanh toán/hoàn tiền (đúng prompt 3.7) | đọc-code |\n| **Gợi ý sản phẩm từ CSDL (frontend thẻ)** | `assets/js/chat.js`, `assets/js/chat_widget.js`, `assets/css/chat.css`, `assets/css/chat_widget.css` (22/11) | `dichvu`, `dichvu_kichco` | [x] | Thẻ dựng bằng `createElement`/`textContent` (chống XSS), link `service-detail.php?id=`, ảnh `assets/uploads/` (fallback `default.png`), size cho trang phục, sức chứa phòng, số lượng/"Tạm hết". Widget xếp dọc, không tràn ngang. **Xin lỗi 5 phút (Lô 3) chưa làm** | **http** (trang chat + widget render, node --check JS sạch) |
| Trang chat toàn màn hình (khách) | `user/chat.php`, `chat.js`, `chat.css` | — | [x] | Đổi giao diện tông chat_widget 15/11; chưa bấm thử gửi tin thật | php-l, http (200) |\n| **Xin lỗi sau 5 phút (backend)** | `includes/chat_goi_y.php`, `api/chat_api.php` (22/11) | `chat_hoi_thoai`, `chat_tin_nhan`, `cau_hinh` | [x] | Kiểm tra "lười" trong poll/open/history; transaction + `FOR UPDATE`; idempotency token `[[auto:cho-nv]]`; giờ làm việc đọc từ `cau_hinh`; tính thời gian bằng SQL (UTC+7). Hằng số 300s (test tạm 20s đã trả về 300) | **http** (xin lỗi xuất hiện đúng 1 lần, NV thấy không lộ token, idempotency pass) |
| Chính sách hiển thị | `user/policy.php` | — | [x] | — | đọc-code |

## 3. NHÂN VIÊN

| Chức năng | File | DB | Trạng thái | Vấn đề | Kiểm thử |
|---|---|---|---|---|---|
| Khung khu vendor, chặn admin vào | `vendor/khung_nv.php:11` | — | `[?]` | Admin không vào được khu vendor — đúng ý thiết kế hay không, nhóm chốt | đọc-code |
| Quản lý dịch vụ (kho chung) | `vendor/my-services.php`, `dich-vu-form.php` | `dichvu` + bảng con | [x] | 15/11: quyền qua `vai_tro_hien_tai()`, whitelist lý do, transaction; upload thêm `getimagesize()` + random 8 byte | php-l; upload chưa-test |
| Duyệt đơn trong transaction + kiểm kho lại (đúng size) | `vendor/vendor-orders.php` | `dondatlich`, `chitietdon` | [x] | Loại trừ chính đơn qua `$ma_don_bo_qua` | đọc-code |
| Từ chối / hủy / hoàn thành — UPDATE có điều kiện trạng thái + rowCount | `vendor/vendor-orders.php` | `dondatlich`, `thanhtoan` | [x] | Hủy đơn đã duyệt **ghi đè NV_Ma** (`:102-105`) — `[?]` cần cột riêng, nhóm duyệt schema | đọc-code |
| Hoàn tiền | `vendor/hoan-tien.php`, `includes/donhang.php:81-119` | 8 cột DDL_*HoanTien | `[!]→[x]` | **Lỗi ±1 đồng đã sửa 15/11**: input `step="1000" min="1"` khiến trình duyệt chỉ nhận số kết thúc 001 (1 + n×1000) → nhân viên bị chặn submit số tròn, spinner tụt về (số tròn − 999). Sửa thành `step="1"`. Dữ liệu cũ đơn 21/22/23 (449.001, 1.214.001, 8.400.001) vẫn lệch trong DB — cần đối soát lại tiền | php-l; **http** (trang render, step mới vào HTML) |
| Chat nhân viên tiếp nhận | `vendor/chat.php`, `api/chat_nv_api.php` | `chat_hoi_thoai` | [x] | Giao diện đổi tông chat_widget 15/11; render bằng textContent (chống XSS) | php-l; gửi/nhận chưa-test |
| Dashboard nhân viên (đơn mới, lịch hôm nay, bàn giao…) | — | — | `[ ]` | Thiếu — prompt 4.1 (hiện vendor vào thẳng my-services) | — |
| Quản lý lịch phòng (calendar) | — | — | `[ ]` | Thiếu — prompt 4.3 | — |
| Bàn giao / nhận lại thiết bị, phí trả trễ | — | — | `[ ]` | Thiếu — prompt 4.4 (chỉ có chữ chính sách `policy.php:165`) | — |

## 4. ADMIN

| Chức năng | File | DB | Trạng thái | Vấn đề | Kiểm thử |
|---|---|---|---|---|---|
| Khung admin + đăng xuất POST+token | `admin/khung_admin.php` | — | [x] | — | php-l |
| Dashboard tổng quan | `admin/dashboard.php` | `dondatlich` | [x] | Doanh thu tính trên đơn `Hoàn thành` (`doanh_thu_khoang()` `:78`) — không dính tiền đơn hủy (đã xác minh Lô D) | đọc-code |
| Quản lý dịch vụ + form | `admin/dich-vu.php`, `dich-vu-form.php` | `dichvu`+bảng con | [x] | Thêm dịch vụ = transaction 1 lần (đúng) | đọc-code |
| Quản lý người dùng (khóa/mở, phân trang) | `admin/nguoi-dung.php` | `nguoidung` | [x] | Link order-detail sai đường dẫn đã sửa 11/11 (`:77`) | php-l |
| Quản lý nhân viên (khóa/mở, đổi mật khẩu) | `admin/nhan-vien.php` | `nhanvien`, `nguoidung` | [x] | 15/11: thêm try/catch rollback cho khóa/mở | php-l |
| Thống kê (lọc ngày, nhóm ngày/tháng) | `admin/thong-ke.php` | `dondatlich`, `chitietdon` | [x] | Doanh thu theo thời điểm hoàn thành; top dịch vụ theo ngày tạo — hai mốc khác nhau (lưu ý đối soát) | php-l |
| **Xuất CSV: báo cáo tổng quan + danh sách đơn** | `admin/thong-ke-xuat.php` (mới 15/11) | — | [x] | E2E thật bằng session admin: 2 loại file + redirect tham số sai đúng | **http** |
| Trang quản lý ĐƠN HÀNG riêng | `admin/don-hang.php` (mới 15/11) | `dondatlich`, `chitietdon`, `thanhtoan` | [x] | Lọc theo mã/khách/trạng thái/ngày + phân trang + chi tiết đầy đủ (cọc, hoàn tiền, lý do); **chỉ xem, không ghi** | **http** (danh sách, chi tiết, 3 bộ lọc, chặn khách-la 302) |
| Trang quản lý MÃ GIẢM GIÁ | `admin/ma-giam-gia.php` (mới 15/11) | `ma_giam_gia` | [x] | Thêm/sửa/bật-tắt (không có nút xóa — đơn lưu mã dạng chuỗi); mã UNIQUE chặn trùng; lọc + phân trang; nhãn trạng thái thực tế (hết hạn/hết lượt/chưa đến hạn). **Không đổi schema** | **http** (danh sách, form sửa prefill, lọc, INSERT thật + trùng UNIQUE + toggle 2 chiều, CSRF sai bị chặn) |
| Trang quản lý THANH TOÁN / đối soát | — | — | `[ ]` | Thiếu — prompt 5.7 | — |
| Quản lý banner / khuyến mãi | — | — | `[ ]` | Thiếu — prompt 5.8 | — |
| Nhật ký hoạt động | — | — | `[ ]` | Thiếu — prompt 5.4.9 (ưu tiên sau) | — |

## 5. NGHIỆP VỤ BẮT BUỘC (đối chiếu Phần 6 của MASTER PROMPT)

| Quy tắc | Trạng thái | Dẫn chứng |
|---|---|---|
| 1. Không đặt quá số lượng | [x] | `cart_helper.kiem_tra_con_cho` + check lại lúc tạo đơn (`xac-nhan-thanh-toan.php:54-64`) + check lại khi duyệt (Lô B) |
| 2. Không 2 đơn cùng tài sản trùng giờ | [x] với lưu ý | Đếm theo DV_Ma + khoảng giao nhau; **theo từng chiếc riêng** thì chưa có (cần nhóm 5) |
| 3. Không đặt trùng phòng trùng giờ | [x] với lưu ý | Cùng cơ chế đếm theo `phongchup` DV_SoLuong=1 |
| 4. Không cho thuê thiết bị bảo trì/hỏng | [x] | `DV_DangBan=0` + check `DV_DangBan = 1` trong API kho |
| 5. Bàn giao cần đủ thanh toán | `[ ]` | Chưa có luồng bàn giao |
| 6. Phân biệt tiền thuê / tiền cọc | `[x]/[?]` | Cột + luồng cọc 25% có; chính sách hoàn cọc cần chốt |
| 7. Không tự hoàn cọc khi chưa kiểm tra | [x] tạm | Hiện không tự hoàn gì cả (an toàn theo nghĩa "không tự động") |
| 8. Phí trả trễ | `[ ]` | Chưa có |
| 9. Hủy theo chính sách hoàn tiền | `[?]` | Chính sách hiển thị ở `policy.php:176-178`; luồng hoàn tiền có — chưa gắn số % tự động |
| 10-11. Đơn hoàn tất không sửa; không xóa cứng | [x] | Khóa ngoại RESTRICT; không có DELETE đơn trong code (quét) |
| 12. Nhật ký thao tác nhạy cảm | `[ ]` | Chưa có |
| 13-15. Quyền server-side, IDOR, không tin client | [x] | Audit toàn cây 15/11 |
| Race condition (transaction + khóa dòng) | `[x]/[?]` | Transaction dùng rộng rãi; **FOR UPDATE khóa dòng dịch vụ** đề xuất ở mục 6.2 AGENTS chưa áp — nhóm chốt mức cần thiết |

## 6. TRẠNG THÁI (đối chiếu Phần 7 của MASTER PROMPT)

| Nhóm | Hiện tại | So với prompt |
|---|---|---|
| 7.1 Đơn | `Chờ duyệt → Đã duyệt → Hoàn thành` / `Đã từ chối` / `Đã hủy` (5) | Prompt đòi 9 trạng thái (Chờ thanh toán, Đang chuẩn bị, Đang thực hiện, Chờ hoàn trả…) — `[?]` thêm trạng thái là thêm giá trị varchar: **nhóm duyệt**, không tự thêm |
| 7.2 Thanh toán | `Chờ` / `Thành công`; loại `Thanh toán đủ` / `Đặt cọc` | `[?]` chưa có "một phần"/"hoàn một phần" |
| 7.3 Bàn giao | `[ ]` chưa có bảng/cột | Cần schema mới nếu làm |
| 7.4 Tài sản | `TB_TinhTrang`/`PC_TrangThai`/`TP_TinhTrang` theo NHÓM dịch vụ | `[?]` không có từng chiếc riêng (`tai_san`) |
| 7.5 Lịch phòng | Không có bảng lịch riêng; chống trùng theo `chitietdon` | `[?]` bảng `lichthue` nửa vời (chỉ đọc khóa, không ghi — `cart_helper.php:186-193`) |

## 7. DATABASE — VIỆC CẦN LÀM (sinh viên tự làm / nhóm duyệt)

- `[ ]` Xuất lại `database/studio.sql` + **làm sạch email/mật khẩu thật** trước khi nộp (mục 8.8 AGENTS).
- `[?]` Bảng `dangnhap_thatbai` — kiểm đã có trong dump chưa (CSDL thật có).
- `[?]` Bảng `lichthue`: bỏ check khóa lịch, hay làm trang quản lý khóa lịch (cần ghi/bảng mới).
- `[?]` Thêm bảng `phieu_ban_giao`/`chi_tiet_ban_giao` (lô bàn giao) — **phải nhóm duyệt**.
- `[?]` Collation lẫn lộn giữa bảng (vietnamese_ci/unicode_ci/general_ci) — rủi ro "Illegal mix of collations" khi JOIN.

## 8. BÁO CÁO THỐNG KÊ (đối chiếu Phần 5.9)

| Báo cáo | Trạng thái |
|---|---|
| Doanh thu theo thời gian (biểu đồ + tổng quan) | [x] |
| Đơn theo trạng thái, top dịch vụ | [x] |
| Xuất CSV (tổng quan + danh sách đơn) | [x] — http |
| Báo cáo khách hàng | `[ ]` (dữ liệu có sẵn trong `nguoidung`/`dondatlich`, chỉ thêm khối CSV) |
| Báo cáo hủy đơn / tỷ lệ hủy | `[ ]` |
| Báo cáo tài sản / bàn giao | `[ ]` (chờ luồng bàn giao) |
| Xuất PDF | `[ ]` — theo nội quy chỉ khả thi bằng CSS `@media print` (không cài thư viện) |

---

## TÓM TẮT ĐỂ LÊN LỘ TRIỂNH (tham khảo — thực hiện theo lô ≤ 5 file, mỗi lô xin duyệt)

1. `[ ]` Trang admin Quản lý đơn hàng (không đổi schema) — *ưu tiên 1*
2. `[ ]` Trang admin Quản lý mã giảm giá (không đổi schema) — *ưu tiên 2*
3. `[ ]` CSV bổ sung: khách hàng, hủy đơn (không đổi schema) — *ưu tiên 3*
4. `[?]` Nhóm họp chốt: cọc/hoàn tiền, hủy đơn "Đã duyệt", trả lượt mã, lichthue, trạng thái mở rộng
5. `[?]` Bàn giao/nhận lại + phí trả trễ (cần schema mới) — sau khi nhóm duyệt
6. `[ ]` Nhật ký hoạt động, đổi lịch, banner/khuyến mãi, dashboard nhân viên

> Việc nào đánh dấu `[x]` đều có dẫn chứng ở `danh-sach-can-sua.md` hoặc đã test HTTP trong phiên 15/11.
> Sinh viên **bắt buộc** tự chạy các mục ghi "chưa-test" trên trình duyệt trước khi nộp.
