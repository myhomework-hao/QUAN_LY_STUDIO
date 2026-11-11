# AGENTS.md — VIBE Studio (đồ án nhóm CT299)

## 0. Cách dùng file này

- Đọc HẾT file trước khi làm bất cứ việc gì.
- Thứ tự ưu tiên khi có mâu thuẫn: (1) Nội quy mục 2, (2) "NHIỆM VỤ HIỆN TẠI" mục 13, (3) các mục còn lại. Mâu thuẫn không tự giải quyết được thì HỎI một câu ngắn, không tự chọn.
- Mục 5 đến 8 là kết quả đối chiếu với `database/studio.sql` và cây thư mục. Nếu code thật khác file này, BÁO chỗ lệch trong báo cáo; không sửa code cho khớp file này.
- Dòng nào ghi "chưa xác nhận" là phỏng đoán từ dữ liệu, chưa được kiểm bằng code. Không coi là sự thật.

## 1. Bối cảnh

- Đồ án nhóm môn "Phát triển hệ thống web" (CT299), Trường Đại học Cần Thơ. Đề tài: Hệ thống quản lý Studio (thương hiệu "VIBE Studio", cho thuê thiết bị, phòng chụp, trang phục, phụ kiện).
- Công nghệ hiện có: PHP thuần, MySQL/MariaDB (XAMPP), PDO, HTML/CSS/JS thuần. Không framework.
- Người dùng là sinh viên và phải tự giải thích được từng dòng code trước giảng viên. Bạn là người hỗ trợ, không phải người làm thay: sửa nhỏ, giải thích rõ, không tạo ra thứ sinh viên không hiểu.
- CSDL chính: `database/studio.sql` (tên CSDL `STUDIO`). `database/csdl.sql` và `database/chat.sql` chỉ để tham khảo; các bảng chat đã nằm sẵn trong `studio.sql`. KHÔNG dựa vào hai file kia.

## 2. NỘI QUY BẮT BUỘC

### Phạm vi
1. CHỈ làm đúng "NHIỆM VỤ HIỆN TẠI". Việc khác phát hiện được thì ghi vào "Phát hiện thêm", KHÔNG tự sửa.
2. Mỗi lần tối đa 5 file. Xong thì DỪNG, báo cáo, chờ xác nhận.
3. KHÔNG thêm tính năng ngoài mục 7 (cột "Đã có"). Tính năng ở cột "Chưa có" chỉ làm khi nhiệm vụ nêu đích danh.
4. KHÔNG đổi giao diện, màu sắc, bố cục, tên hàm/biến đang dùng ở nhiều nơi.
5. KHÔNG refactor, tối ưu, "dọn cho đẹp" khi không được yêu cầu.

### Toàn vẹn dự án
6. KHÔNG đổi schema CSDL (bảng, cột, khóa, index, dữ liệu). Cần đổi thì viết sẵn câu SQL trong báo cáo rồi DỪNG chờ duyệt. Bảng mới hoàn toàn cũng phải xin phép.
   - Ngoại lệ đã được duyệt: bảng mới `dangnhap_thatbai` (DNTB_Ma, DNTB_Email, DNTB_IP, DNTB_ThoiGian) cho lô 1. Nếu chưa có trong `studio.sql`, nhắc sinh viên cập nhật file SQL chung sau khi nhóm đồng ý.
7. KHÔNG chạy câu lệnh ghi/xóa trực tiếp lên CSDL (INSERT/UPDATE/DELETE/DROP/ALTER/TRUNCATE). Chỉ được SELECT để kiểm tra.
8. KHÔNG xóa, đổi tên, di chuyển file hoặc thư mục. Nghi file thừa thì chỉ liệt kê.
9. KHÔNG cài thư viện, composer package, framework. KHÔNG thêm CDN mới.
10. KHÔNG đụng git: không commit, không push, không sửa `.gitignore`. Chuyện git do sinh viên quyết định.
11. File dùng chung giữa các thành viên phải HỎI trước khi sửa: `config/*`, `includes/header.php`, `includes/footer.php`, `admin/khung_admin.php`, `vendor/khung_nv.php`. Khi sửa thì liệt kê file nào bị ảnh hưởng để sinh viên báo nhóm.
12. KHÔNG sửa file thuộc phần của thành viên khác nếu nhiệm vụ không nêu (xem mục PHÂN CÔNG).

### Trung thực
13. ĐỌC TRƯỚC KHI SỬA: đọc toàn bộ file sẽ sửa và mọi file nó require. Không đoán nội dung.
14. Mọi nhận định phải có dẫn chứng (file và số dòng, hoặc câu SELECT và kết quả). Không chắc thì ghi "chưa xác nhận".
15. KHÔNG bịa tên bảng, cột, hàm. Lấy từ `studio.sql` và code thật.
16. KHÔNG báo "đã sửa xong" nếu chưa chạy kiểm tra. Chưa chạy được thì nói rõ là chưa chạy.
17. Không chắc yêu cầu thì hỏi MỘT câu ngắn trước khi làm.
18. Các đoạn code mẫu trong tài liệu môn học (tên bảng `khachhang`, cột `makhach`, CSDL `quanly_banhang`...) chỉ để minh họa. KHÔNG sao chép tên bảng, cột hay kiểu viết đó vào dự án này.

### Tính học tập
19. Với mỗi lỗi: mở đầu bằng một ví dụ đời thường 1-2 câu, rồi giải thích NGUYÊN NHÂN GỐC (vì sao lỗi xảy ra), cuối cùng mới đến cách sửa.
20. Có hai cách thì chọn cách đơn giản hơn và nêu lý do. Giải pháp phải đủ đơn giản để sinh viên tự giải thích được.
21. KHÔNG dán nguyên khối code lớn không giải thích. Mỗi thay đổi có lý do kèm theo.

## 3. QUY ƯỚC CODE

- **Tên**: tiếng Việt không dấu, theo kiểu hiện có (`ma_nhan_vien_hien_tai`, `trang_hien_tai`, `DDL_TrangThai`).
- **Chú thích**: tiếng Việt, ngắn gọn. Một dòng nêu mục đích ở đầu mỗi hàm mới và mỗi khối xử lý chính; không chú thích từng dòng. Chỉ áp dụng cho code mới hoặc code được sửa.
- **SQL**: luôn PDO prepared statement, không nối chuỗi. Dự án đang dùng PDO; KHÔNG chuyển sang MySQLi (xem mục 9).
- **Transaction**: mọi thao tác ghi nhiều bước (đặt đơn, dùng mã giảm giá, tạo thanh toán, đổi trạng thái kèm cập nhật liên quan) nằm trong MỘT transaction, lỗi thì rollback.
- **Chống XSS**: escape KHI IN RA (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` hoặc `h()` nếu file đã có). KHÔNG escape lúc nhận/lưu dữ liệu (kiểu hàm `test_input()` lưu chuỗi đã escape vào DB sẽ bị escape hai lần khi in ra, ví dụ "Tom & Jerry" hiện thành "Tom &amp; Jerry").
- **JavaScript**: KHÔNG gán dữ liệu từ server/người dùng vào `innerHTML` (ví dụ `${item.ten}` trong chuỗi HTML). Dùng `textContent` hoặc `createElement`, hoặc escape trước.
- **Form POST**: có CSRF token (`csrf_input()` và `csrf_hop_le()` trong `includes/security.php`, file đã tồn tại). API nhận JSON (chat) dùng `csrf_hop_le($data['csrf'] ?? null)` với token nằm trong body.
- **Đăng xuất**: mọi nút ĐĂNG XUẤT là form POST kèm token (`user/logout.php`), không còn liên kết GET.
- **Phân quyền**: kiểm tra vai trò bằng `yeu_cau_vai_tro()` VÀ kiểm tra quyền sở hữu: khách chỉ được đọc/sửa/hủy đơn, giỏ, đánh giá có `ND_Ma` của chính mình.
- **Hai loại mã người**: `ND_Ma` là tài khoản; `NV_Ma` là hồ sơ nhân viên. Session lưu `ND_Ma` (`$_SESSION['user_id']`). `dondatlich` và `chat_*` dùng `NV_Ma`. Đổi qua lại bằng `ma_nhan_vien_hien_tai($conn)`. Không trộn lẫn hai loại mã (lỗi rất dễ mắc).
- **Session**: chỉ `config/auth.php` được gọi `session_start()`. Không gọi ở file khác.
- **Mật khẩu**: `password_hash()` và `password_verify()` (dữ liệu hiện có đã là bcrypt). KHÔNG dùng md5. Xem mục 9.
- **Chuỗi trạng thái** (`'Chờ duyệt'`, `'Đã duyệt'`...): các cột này là varchar tự do, gõ sai dấu sẽ lỗi âm thầm. Dùng hàm/hằng sẵn có trong `includes/donhang.php`. Chưa có thì KHÔNG tự tạo, ghi vào "Phát hiện thêm".
- **Tiền**: số nguyên VND. Không so sánh số thực.
- **Giờ mở/đóng cửa và bước giờ**: đọc từ bảng `cau_hinh`, không viết cứng.
- **Đường dẫn**: dùng `BASE_URL`, không viết cứng.
- **Thông báo lỗi**: tiếng Việt, rõ ràng, không lộ lỗi kỹ thuật hay câu SQL.
- **Upload ảnh** (chỉ khi nhiệm vụ yêu cầu): whitelist đuôi (jpg, jpeg, png, webp), kiểm tra ảnh thật bằng `getimagesize()`, giới hạn dung lượng, đặt tên ngẫu nhiên (`bin2hex(random_bytes(8)) . '.' . $duoi`), không dùng tên file gốc của người dùng.
- Giữ nguyên thụt lề, kiểu viết, cấu trúc file hiện có. Sửa cục bộ, không viết lại nguyên file.

## 4. KIẾN TRÚC (xác nhận qua cây thư mục và code đã xem)

```
config/    app.php (BASE_URL), auth.php (session, vai trò), db.php (PDO, biến $conn)
includes/  header, footer, cart_helper, coupon_helper, dich_vu, don_vi, donhang,
           o-mat-khau, search, search_popup, taikhoanmenu, chat_widget, back_to_top
user/      khu khách hàng: login, register, logout, profile, services, service-detail,
           cart, checkout, my-orders, order-detail, chat, policy
vendor/    KHU NHÂN VIÊN (không phải thư mục Composer): my-services, dich-vu-form,
           vendor-orders, chat, khung_nv
admin/     dashboard, dich-vu, nguoi-dung, nhan-vien, thong-ke, khung_admin
api/       kiem_tra_kho, chat_api, chat_nv_api (endpoint cho JS; cart_api đã xóa 09/11)
assets/    css, js, images, uploads (chứa cả ảnh mẫu mà studio.sql tham chiếu)
database/  studio.sql (chính), csdl.sql, chat.sql
```

- Vai trò: `LND_Ma` 1 = admin, 2 = nhân viên, 3 = khách hàng (hằng `VAI_TRO_*` trong `auth.php`). Tên vai trò dùng với `yeu_cau_vai_tro()`: `'admin'`, `'nhanvien'`, `'khachhang'`.
- Session: `user_id`, `user_name`, `user_email`, `role`.
- Layout nhân viên dùng `nv_dau()` và `nv_cuoi()` trong `vendor/khung_nv.php`; layout admin dùng `admin/khung_admin.php`.
- Tài khoản nhân viên do admin tạo; `register.php` chỉ tạo khách hàng.

## 5. CƠ SỞ DỮ LIỆU `studio.sql` (tóm tắt; khi cần chi tiết, đọc `CREATE TABLE` trong file)

**Người dùng**
- `nguoidung`: ND_Ma, ND_Ten, ND_GioiTinh, ND_NgaySinh, ND_SDT, ND_Email (duy nhất), ND_MatKhau (bcrypt), ND_TrangThai (1 hoạt động, 0 khóa), ND_NgayTao, LND_Ma.
- `loai_nguoi_dung`: LND_Ma, LND_Ten (admin, nhanvien, khachhang).
- `nhanvien`: NV_Ma, ND_Ma (duy nhất), NV_MaNhanVien (duy nhất), NV_ChucVu, NV_NgayVaoLam, NV_TrangThai.

**Dịch vụ (kho chung của studio, không thuộc riêng nhân viên nào)**
- `dichvu`: DV_Ma, DV_Ten, DV_Loai (`thiet_bi`, `phong_chup`, `trang_phuc`, `phu_kien`), DV_MoTa, DV_MoTaChiTiet, DV_GiaThue, DV_DonViTinh (dữ liệu hiện có: `giờ`, `ngày`, `lượt`), DV_HinhAnh (tên file trong `assets/uploads/`), DV_SoLuong (tồn kho), DV_DangBan (1 hiện, 0 ẩn).
- Bảng con theo loại, khóa là DV_Ma: `thietbi` (TB_Loai, TB_TinhTrang), `phongchup` (PC_SucChua, PC_TrangThai), `phukien` (PK_Loai, PK_TinhTrang), `trangphuc` (TP_KichCo, TP_TinhTrang). Thêm dịch vụ = ghi `dichvu` + bảng con đúng loại (+ `dichvu_kichco` nếu là trang phục) trong MỘT transaction.
- `dichvu_kichco`: DVK_Ma, DV_Ma, DVK_Ten (S/M/L), DVK_SoLuong, DVK_DangHoatDong; duy nhất theo (DV_Ma, DVK_Ten).
- `hinhanh` (ảnh phụ, đang trống), `giohang` (đang trống), `lichthue` (xem mục 8).

**Đơn hàng**
- `dondatlich`: DDL_Ma, ND_Ma, DDL_TongTien (đã trừ giảm giá), DDL_MaGiamGia (VARCHAR lưu MÃ như `VIBE10`, không phải khóa ngoại), DDL_SoTienGiam, DDL_TienCoc (luôn 0, chưa dùng), DDL_TrangThai, NV_Ma (nhân viên duyệt/từ chối), DDL_ThoiDiemXuLy, DDL_LyDoTuChoi, DDL_NgayTao, DDL_GhiChu, DDL_TenNguoiThue, DDL_SDTNguoiThue, DDL_DiaChi (đơn cũ có thể NULL, phải xử lý NULL khi hiển thị), DDL_ThoiDiemHuy, DDL_LyDoHuy, NV_MaHoanThanh, DDL_ThoiDiemHoanThanh.
- `chitietdon`: CTD_Ma, DDL_Ma, DV_Ma, CTD_SoLuong, CTD_KichCo, CTD_BatDau, CTD_KetThuc, CTD_DonGia, CTD_ThanhTien. Đơn giá và thành tiền là bản chụp tại thời điểm đặt; KHÔNG tính lại đơn cũ theo `dichvu.DV_GiaThue`.
- `thanhtoan`: TT_Ma, DDL_Ma, TT_Loai (hiện chỉ `Thanh toán đủ`), TT_SoTien, TT_PhuongThuc (`Tiền mặt`, `Chuyển khoản QR giả lập`), TT_MaThamChieu, TT_TrangThai (`Chờ`, `Thành công`), TT_ThoiDiem.
- `danhgia`: DG_Ma, ND_Ma, DDL_Ma, DV_Ma, DG_SoSao, DG_NoiDung, DG_NgayTao; duy nhất theo (DDL_Ma, DV_Ma).
- `ma_giam_gia`: MG_Ma, MG_Code (duy nhất), MG_Loai (`percent` | `fixed`), MG_GiaTri, MG_DonToiThieu, MG_GiamToiDa (chỉ cho percent), MG_SoLuong (**0 = không giới hạn**), MG_DaDung, MG_NgayBatDau, MG_NgayKetThuc (NULL = không hết hạn), MG_DangHoatDong.

**Khác**
- `cau_hinh` (khóa-giá trị): `gio_mo_cua` 08:00, `gio_dong_cua` 20:00, `buoc_thoi_gian_phut` 60, `cho_phep_chon_ngoai_gio` 0.
- `chat_hoi_thoai` (CHT_CheDo: `bot` | `nhanvien`; CHT_TrangThai: `dang_mo`, `dang_ho_tro`...) và `chat_tin_nhan` (CTN_NguoiGui: `khach` | `bot` | `nhanvien`; CTN_DaDoc).

**Hành vi khi xóa (khóa ngoại)**
- `chitietdon` và `thanhtoan` bị XÓA THEO khi xóa `dondatlich` (CASCADE) → KHÔNG xóa đơn hàng thật, vì mất luôn lịch sử thanh toán.
- `chitietdon.DV_Ma`, `danhgia.DV_Ma`, `dondatlich.ND_Ma` là RESTRICT → không xóa được dịch vụ/người dùng đã có đơn. Ẩn dịch vụ bằng `DV_DangBan = 0`, khóa người dùng bằng `ND_TrangThai = 0`. Không DELETE.

## 6. NGHIỆP VỤ (suy ra từ schema và dữ liệu; mục nào ghi "chưa xác nhận" thì đối chiếu code)

### 6.1 Vòng đời đơn (chuỗi trạng thái đúng, có dấu)
`'Chờ duyệt'` (mặc định) → `'Đã duyệt'` → `'Hoàn thành'`
`'Chờ duyệt'` → `'Đã từ chối'` (nhân viên; ghi NV_Ma, DDL_ThoiDiemXuLy, DDL_LyDoTuChoi)
`'Chờ duyệt'` → `'Đã hủy'` (khách; ghi DDL_ThoiDiemHuy)

- Trạng thái cuối, không sửa lại: Hoàn thành, Đã hủy, Đã từ chối.
- Khi duyệt ghi NV_Ma và DDL_ThoiDiemXuLy. Khi hoàn thành ghi NV_MaHoanThanh và DDL_ThoiDiemHoanThanh.
- Dữ liệu chỉ cho thấy khách hủy khi đơn còn "Chờ duyệt" (NV_Ma rỗng). Khách có được hủy đơn "Đã duyệt" không: chưa xác nhận, hỏi sinh viên.
- Mọi lần đổi trạng thái phải kiểm tra trạng thái hiện tại NGAY TRONG câu UPDATE, rồi kiểm `rowCount()`:
  `UPDATE dondatlich SET DDL_TrangThai = ? ... WHERE DDL_Ma = ? AND DDL_TrangThai = 'Chờ duyệt'`
  Chỉ kiểm bằng PHP rồi mới UPDATE thì hai người bấm cùng lúc vẫn lọt.

### 6.2 Tồn kho và chống đặt quá số lượng
- Studio KHÔNG phải "một dịch vụ một đơn": thiết bị/trang phục/phụ kiện có nhiều cái (DV_SoLuong, ví dụ 3 máy ảnh), phòng chụp có DV_SoLuong = 1 nên tự loại trừ nhau.
- Quy tắc: tổng CTD_SoLuong của các đơn đang chiếm chỗ, giao nhau về thời gian với khoảng muốn đặt, cộng số lượng mới, không được vượt DV_SoLuong.
- Hai khoảng giao nhau khi: `bat_dau_moi < ket_thuc_cu AND ket_thuc_moi > bat_dau_cu` (đơn nối tiếp, hết giờ này vào đúng giờ kia, KHÔNG tính là trùng).
- Đơn chiếm chỗ: `'Chờ duyệt'` và `'Đã duyệt'` (đề xuất; cần nhóm xác nhận). `'Đã hủy'`, `'Đã từ chối'`, `'Hoàn thành'` không chặn đặt mới.
- Trang phục có size (CTD_KichCo khác NULL): tồn kho theo `dichvu_kichco.DVK_SoLuong` của đúng (DV_Ma, size), không theo DV_SoLuong. Code hiện tại xử lý thế nào: chưa xác nhận.
- Kiểm tra và ghi đơn phải nằm CÙNG MỘT transaction. Cách đơn giản để hai khách đặt cùng lúc không lọt: khóa dòng dịch vụ trước khi đếm.
  ```sql
  SELECT DV_SoLuong FROM dichvu WHERE DV_Ma = ? AND DV_DangBan = 1 FOR UPDATE;
  SELECT COALESCE(SUM(c.CTD_SoLuong), 0)
  FROM chitietdon c JOIN dondatlich d ON d.DDL_Ma = c.DDL_Ma
  WHERE c.DV_Ma = ? AND d.DDL_TrangThai IN ('Chờ duyệt', 'Đã duyệt')
    AND c.CTD_BatDau < ? AND c.CTD_KetThuc > ?;   -- ket_thuc_moi, bat_dau_moi
  ```
  Đây là mẫu để lô 5; chỉ áp dụng khi được giao.
- Khi nhân viên duyệt đơn, kiểm tra lại tồn kho (đơn có thể đã bị đặt chồng từ trước).

### 6.3 Tính tiền (suy ra từ dữ liệu, chưa xác nhận bằng `don_vi.php`)
- Thành tiền = đơn giá × số lượng × số đơn vị thời gian.
- `giờ`: số giờ (9 giờ × 300.000 = 2.700.000). `ngày`: làm tròn lên theo mỗi 24 giờ (12 giờ tính 1 ngày; 48 giờ tính 2 ngày). `lượt`: chưa rõ (xem mục 8, điểm 2).
- Mã giảm giá: `percent` giảm theo %, tối đa MG_GiamToiDa; `fixed` trừ số tiền cố định. Điều kiện: đang hoạt động, trong hạn, đủ MG_DonToiThieu, còn lượt. DDL_TongTien lưu số đã trừ giảm (1.000.000 − 100.000 = 900.000).
- Tăng lượt dùng PHẢI nằm trong cùng transaction tạo đơn, với điều kiện đúng cột thật:
  `UPDATE ma_giam_gia SET MG_DaDung = MG_DaDung + 1 WHERE MG_Code = ? AND (MG_SoLuong = 0 OR MG_DaDung < MG_SoLuong)` rồi kiểm `rowCount()`; bằng 0 thì rollback.

### 6.4 Thanh toán (suy ra từ dữ liệu, chưa xác nhận)
- Chuyển khoản QR (giả lập): `TT_TrangThai = 'Thành công'` ngay khi đặt. Tiền mặt: `'Chờ'` cho đến khi đơn `Hoàn thành` thì thành `'Thành công'`.
- Chưa có cơ chế hoàn tiền (xem mục 8, điểm 4). KHÔNG tự thêm.

### 6.5 Đánh giá
- Chỉ khi đơn `'Hoàn thành'`; người đánh giá phải là chủ đơn (`danhgia.ND_Ma = dondatlich.ND_Ma`); DV_Ma phải nằm trong đơn đó; mỗi (đơn, dịch vụ) một lần (có UNIQUE; bắt lỗi trùng và báo thân thiện); sao 1 đến 5.

### 6.6 Chat
- Khách chat với bot (trả lời theo kịch bản), có thể chuyển sang nhân viên. Nhân viên tiếp nhận ở `vendor/chat.php`. Chỉ khách trong hội thoại đó và nhân viên mới đọc được tin nhắn của hội thoại đó.

## 7. HIỆN TRẠNG CHỨC NĂNG (theo cây thư mục; chưa xác nhận từng tính năng chạy đúng)

| Khu | Đã có file | Chưa có (KHÔNG tự làm) |
|---|---|---|
| Khách | đăng ký, đăng nhập, đăng xuất, hồ sơ, danh sách và chi tiết dịch vụ, giỏ hàng, thanh toán, đơn của tôi, chi tiết đơn (và đánh giá), chat, chính sách | quên mật khẩu, xác thực email |
| Nhân viên | dịch vụ (xem, thêm/sửa), đơn thuê (duyệt, từ chối, hoàn thành), chat | lịch làm việc, phân công nhân sự |
| Admin | dashboard, quản lý dịch vụ, người dùng, nhân viên, thống kê | trang quản lý đơn hàng riêng, trang quản lý mã giảm giá (mã hiện chỉ có trong CSDL) |
| Chung | tìm kiếm, mã giảm giá (`coupon_helper.php`), chat widget | đặt cọc (cột `DDL_TienCoc` có nhưng luôn 0), giao ảnh, portfolio, hoàn tiền |

Quyết định thiết kế của nhóm: nhân viên cùng quản lý TOÀN BỘ dịch vụ của studio. Không thêm cột chủ sở hữu vào `dichvu`.

## 8. ĐIỂM NGHI VẤN ĐÃ PHÁT HIỆN TRONG DỮ LIỆU (KHÔNG tự sửa; chỉ xác minh khi được giao)

1. **Có dấu hiệu đặt quá tồn kho.** `dichvu` 8 (Fujifilm X-A5) có DV_SoLuong = 3. Ngày 27/10/2026 08:00 đến 28/10 08:00 có hai đơn đều `Hoàn thành`: CTD 7 (đơn 7, số lượng 1) và CTD 9 (đơn 8, số lượng 3), tổng 4 > 3. Đơn 8 tạo lúc đơn 7 còn `Chờ duyệt`. Giả thuyết (chưa xác nhận): lúc kiểm tra, code chỉ đếm đơn `Đã duyệt` nên bỏ sót đơn `Chờ duyệt`, và lúc duyệt không kiểm tra lại. Cần đọc `api/kiem_tra_kho.php`, `user/checkout.php`, `includes/donhang.php`.
   Câu kiểm tra (chỉ SELECT):
   ```sql
   SELECT a.DV_Ma, a.CTD_BatDau AS thoi_diem, SUM(b.CTD_SoLuong) AS tong_dang_giu, dv.DV_SoLuong
   FROM chitietdon a
   JOIN dondatlich da ON da.DDL_Ma = a.DDL_Ma AND da.DDL_TrangThai IN ('Chờ duyệt','Đã duyệt','Hoàn thành')
   JOIN chitietdon b ON b.DV_Ma = a.DV_Ma AND b.CTD_BatDau <= a.CTD_BatDau AND b.CTD_KetThuc > a.CTD_BatDau
   JOIN dondatlich db ON db.DDL_Ma = b.DDL_Ma AND db.DDL_TrangThai IN ('Chờ duyệt','Đã duyệt','Hoàn thành')
   JOIN dichvu dv ON dv.DV_Ma = a.DV_Ma
   GROUP BY a.CTD_Ma, a.DV_Ma, a.CTD_BatDau, dv.DV_SoLuong
   HAVING tong_dang_giu > dv.DV_SoLuong;
   ```
   (Câu này đếm theo dịch vụ, chưa tách theo size trang phục.)
2. **Đơn vị `lượt` có thể bị tính theo ngày.** CTD 8: dịch vụ 24 (Zoey Dress, `lượt`, 180.000) thuê 27/10 đến 05/11 (9 ngày) bị tính 1.620.000 = 9 × 180.000. Cần xác nhận `lượt` là phí một lần hay theo ngày (xem `includes/don_vi.php`).
3. **Đơn bắt đầu ngoài giờ mở cửa.** CTD 15: bắt đầu 02/11 21:00 trong khi `gio_dong_cua` = 20:00 và `cho_phep_chon_ngoai_gio` = 0 (đơn tạo lúc 20:10). Cần xác nhận quy tắc giờ có áp dụng cho thiết bị tính theo ngày không.
4. **Đơn đã thanh toán rồi hủy/từ chối nhưng không có bản ghi hoàn tiền.** ĐÃ XÁC MINH (11/11): code KHÔNG tính doanh thu theo `thanhtoan` mà theo đơn `Hoàn thành` (`doanh_thu_khoang()` — `admin/khung_admin.php:78`; `thong-ke.php:33-36`; dashboard cũng gọi hàm này). `thanhtoan` chỉ được đọc để hiển thị (`user/order-detail.php:139`). Dữ liệu nghi vấn vẫn đúng hiện tượng: đơn 3 (`Đã hủy`) và đơn 9 (`Đã từ chối`) có `thanhtoan` `Thành công` tổng 1.350.000, không có bản ghi hoàn tiền → nếu sau này nhóm thêm hoàn tiền thì phải nhớ loại các đơn này khỏi doanh thu. Việc thêm cơ chế hoàn tiền: KHÔNG tự làm (chờ nhóm).
5. **Hủy đơn không trả lượt mã giảm giá.** `ma_giam_gia` VIBE10 có MG_DaDung = 1, nhưng đơn duy nhất dùng nó (đơn 11) đã `Đã hủy`. Cần nhóm quyết định có trả lượt khi hủy không.
6. **`lichthue` có vẻ là bảng cũ.** Chỉ có dữ liệu ngày 21 đến 27/10 cho dịch vụ 1 đến 24, tất cả `trống`, dù đơn 4 đã chiếm dịch vụ 7 ngày 25/10. Không thấy bảng này được cập nhật theo đơn. Việc chống trùng lịch dựa vào index `ix_ctd_trung_lich` trên `chitietdon`. Cần grep `lichthue` trong code để xác nhận có còn dùng không; không xóa.
7. **Giỏ hàng lưu ở đâu?** ĐÃ XÁC ĐỊNH (09/11): giỏ nằm ở `$_SESSION['cart']` (đọc qua `includes/cart_helper.php`), `giohang` trong CSDL không được code dùng. `api/cart_api.php` ghi sai cấu trúc giỏ và không trang nào nạp nó → đã xóa `api/cart_api.php` + `assets/js/cart.js`, không viết lại AJAX giỏ.
8. **Dữ liệu cá nhân thật trong `studio.sql`**: email thật của người dùng và mã băm mật khẩu. Không đưa file này lên GitHub công khai khi chưa làm sạch.
9. **Hai nguồn dữ liệu cho size**: `trangphuc.TP_KichCo` ('S, M, L', chuỗi) và `dichvu_kichco`. Không tự gộp.
10. **Cột trạng thái và loại là varchar tự do** (DV_Loai, DV_DonViTinh, DDL_TrangThai, TT_TrangThai), không có enum hay khóa ngoại. Gõ sai dấu là lỗi âm thầm.
11. **Collation lẫn lộn** (vietnamese_ci, unicode_ci, general_ci giữa các bảng). Có thể gây lỗi "Illegal mix of collations" khi JOIN hoặc so sánh cột chuỗi giữa hai bảng khác collation.
12. **Múi giờ**: dump đặt `+00:00`, dữ liệu thực là giờ Việt Nam, PHP đặt `Asia/Ho_Chi_Minh`, và có file `check-tz.php` ở thư mục gốc. Không trộn `NOW()` của MySQL với `date()` của PHP trong cùng một phép so sánh giờ.

## 9. QUYẾT ĐỊNH CẦN SINH VIÊN XÁC NHẬN VỚI GIẢNG VIÊN (cho đến khi có xác nhận: GIỮ NGUYÊN hiện trạng)

1. **PDO hay MySQLi.** Một tài liệu môn học tham khảo nêu MySQLi (`mysqli_stmt`); dự án đang dùng PDO prepared statement. Chưa xác minh được đây có phải yêu cầu thật của CT299. KHÔNG chuyển đổi.
2. **Băm mật khẩu.** Tài liệu tham khảo nêu `md5`; dự án đang dùng `password_hash()` (bcrypt) và toàn bộ tài khoản hiện có là bcrypt. KHÔNG hạ xuống md5 (md5 không an toàn và sẽ làm hỏng mọi tài khoản hiện có).
3. **Mức chú thích code**: mục 3 đang đặt mức vừa phải (một dòng cho mỗi hàm/khối chính). Nếu giảng viên yêu cầu dày hơn, sinh viên sửa dòng đó.
4. **`.gitignore`** (do sinh viên quyết định, không phải việc của bạn):
   - KHÔNG ignore `/vendor/`: ở đây là khu nhân viên, không phải Composer.
   - KHÔNG ignore `assets/uploads/*`: thư mục chứa ảnh mẫu mà `studio.sql` tham chiếu; ignore thì máy khác thấy ảnh vỡ.
   - Ignore `config/db.php` chỉ khi nhóm có `config/db.sample.php` và hướng dẫn trong README.

Yêu cầu môn học đã xác minh với giảng viên (sinh viên điền; để trống thì KHÔNG suy diễn, hãy hỏi):
- [chức năng bắt buộc, tiêu chí chấm, yêu cầu báo cáo, hạn nộp]

## 10. ĐỐI CHIẾU BÁO CÁO RÀ SOÁT CŨ VỚI `studio.sql`

- Mục 1.1 (thêm `dichvu.NV_Ma`, mỗi nhân viên chỉ thấy dịch vụ của mình): SAI hướng. Dịch vụ là kho chung, nhóm đã chọn nhân viên cùng quản lý toàn bộ. KHÔNG làm.
- Mục 1.2 (ownership đơn): `dondatlich.NV_Ma` được gán khi duyệt/từ chối. Việc bắt buộc là điều kiện trạng thái trong câu UPDATE (mục 6.1). Có cho nhân viên khác hoàn thành đơn do người khác duyệt hay không là quyết định của nhóm; chưa quyết thì không chặn.
- Mục 2.3 (mã giảm giá): `DDL_MaGiamGia` là VARCHAR lưu mã, không phải khóa ngoại → KHÔNG cần đổi kiểu. Câu SQL mẫu trong báo cáo dùng tên cột sai (`MGG_...`); tên đúng là `MG_DaDung`, `MG_SoLuong`, và `MG_SoLuong = 0` nghĩa là không giới hạn (điều kiện `<` thuần sẽ chặn nhầm mã không giới hạn). Phần "tăng lượt dùng ngoài transaction" vẫn cần kiểm bằng code.
- Mục 1.4 (`.gitignore` thêm `database/*.sql`, `assets/uploads/*`): KHÔNG làm, xem mục 9.4.
- Mọi kết luận khác của báo cáo cũ: chỉ dùng sau khi đã đối chiếu với code thật.

## 11. LỘ TRÌNH THEO LÔ (mỗi lô một lần giao việc)

1. Session cookie (httponly, samesite), tạo `includes/security.php`, CSRF + giới hạn đăng nhập sai cho `login.php`/`register.php`, chặn truy cập web vào `database/` (`.htaccess`: `Require all denied`). — **ĐÃ XONG (09/11)**.
2. CSRF cho `user/cart.php`, `user/checkout.php`, `user/order-detail.php`. — **ĐÃ XONG (09/11)**.
3. Phân trang admin (đối chiếu `trang_hien_tai` và biến `$tong_trang`), link `order-detail` sai đường dẫn trong `admin/nguoi-dung.php`. — **ĐÃ XONG (11/11, Lô E)**: phân trang 3 trang (`dich-vu`, `nguoi-dung`, `nhan-vien`) đều đúng mẫu (`trang_hien_tai()` tính `$tong_trang` qua tham chiếu); sửa link sai `admin/nguoi-dung.php:77` thiếu `user/` trước `order-detail.php`.
4. Giỏ hàng: ĐÃ XONG (09/11) — giỏ ở session; `api/cart_api.php` + `cart.js` là code chết nên đã xóa, quyết định KHÔNG viết lại tính năng AJAX giỏ.
5. Tồn kho và mã giảm giá trong transaction: `user/checkout.php`, `api/kiem_tra_kho.php`, `includes/coupon_helper.php`, `includes/donhang.php` (mục 6.2, 6.3; điểm nghi vấn 1, 2, 3, 5). — **ĐÃ XONG (09/11, Lô A)**: tồn kho theo size (`dichvu_kichco`) cho trang phục, tăng lượt mã nằm trong transaction có UPDATE điều kiện + `rowCount()`.
6. Quy trình đơn của nhân viên: điều kiện trạng thái trong UPDATE, kiểm tra lại tồn kho khi duyệt (`vendor/vendor-orders.php`). — **ĐÃ XONG (09/11, Lô B)**: duyệt nằm trong transaction, kiểm lại kho từng dòng (đúng size, loại trừ chính đơn đang duyệt qua `$ma_don_bo_qua`).
7. Tìm kiếm popup: `includes/search.php`, `includes/search_popup.php` (cả lỗi `innerHTML`). — **ĐÃ XONG (11/11, Lô C)**: JS dựng kết quả bằng `textContent`/`createElement` (bản cũ đã đúng), còn sửa `buildPopup()` bỏ `innerHTML` + bỏ link `search.css` không tồn tại, `base` lấy từ `data-base` do PHP in.
8. Doanh thu và hoàn tiền: `admin/thong-ke.php`, `admin/dashboard.php` (điểm nghi vấn 4). — **ĐÃ XÁC MINH (11/11, Lô D), KHÔNG cần sửa**: doanh thu mọi nơi đều tính trên đơn `Hoàn thành` (`doanh_thu_khoang()` trong `admin/khung_admin.php:78`, `thong-ke.php:33-36`); không chỗ nào đếm doanh thu từ `thanhtoan` (file này chỉ được đọc để hiển thị ở `user/order-detail.php:139`). Đơn hủy/từ chối đã "Thành công" 1.350.000 KHÔNG bị tính vào doanh thu. Chưa có cơ chế hoàn tiền — chờ nhóm quyết định (mục 8.4).

### Danh sách việc CÒN LẠI (làm theo từng lô, mỗi lô tối đa 5 file)

- **Lô C — Tìm kiếm popup chống XSS:** `includes/search.php`, `includes/search_popup.php` + JS liên quan (mục 11.7).
- **Lô D — Doanh thu và hoàn tiền:** **ĐÃ XÁC MINH (11/11) — không có lỗi**: doanh thu mọi nơi tính trên đơn `Hoàn thành`, không đếm `thanhtoan` của đơn hủy/từ chối (chi tiết ở mục 11.8 và mục 8.4).
- **Điểm nghi vấn 8.2:** xác nhận cách tính đơn vị `lượt` — CTD 8 (dịch vụ 24, 9 ngày) bị tính 1.620.000 = 9 × 180.000; xem `includes/don_vi.php` và `includes/cart_helper.php` (`tinh_so_don_vi()` hiện trả 1 cho `luot` khi mới, nhưng CTD 8 cũ vẫn tính theo ngày → xác nhận đơn cũ không bị tính lại).
- **Điểm nghi vấn 8.3:** xác nhận quy tắc giờ mở cửa có áp dụng cho dịch vụ tính theo `ngày` không (CTD 15 bắt đầu 21:00 sau `gio_dong_cua` 20:00).
- **Điểm nghi vấn 8.5:** hủy/từ chối đơn có trả lại lượt mã giảm giá không (VIBE10: đơn duy nhất dùng nó đã `Đã hủy` nhưng `MG_DaDung` vẫn = 1). Cần nhóm quyết định rồi mới làm.
- **Xác minh pattern UPDATE ở khu admin:** `admin/dich-vu.php`, `admin/nguoi-dung.php`, `admin/nhan-vien.php` — UPDATE muốn an toàn song song thì cũng cần điều kiện trong WHERE + `rowCount()`. Chỉ đọc và báo trước, sửa khi được giao.
- **Đồng bộ `database/studio.sql`:** CSDL thật đang có 21 bảng, file SQL chỉ có 18 — thiếu `cau_hinh` và `dangnhap_thatbai` (đã duyệt ở lô 1). Sinh viên xuất lại dump (cần làm sạch email/mật khẩu thật trước khi nộp, xem mục 8.8).
- **Lịch sử khảo sát cũ mục 10:** đã đối chiếu xong, phần sai hướng không làm.

Mục 11.3 (phân trang + link) và mục 11.7, 11.8 đã xong/xác minh — lộ trình gốc giờ chỉ còn việc chờ nhóm quyết định (hoàn tiền, trả lượt mã) và các mục "Phát hiện thêm".

## 12. KHÔNG LÀM, KIỂM TRA, BÁO CÁO

**Không làm**
- Không tạo dữ liệu mẫu, tài khoản mẫu; không chạy lệnh ghi/xóa lên CSDL.
- Không kết luận "lỗi" chỉ dựa vào tên file hay tên hàm; phải đọc nội dung.
- Không viết test framework, không thêm công cụ build.
- Không in mật khẩu, mã băm, email thật của người dùng vào báo cáo.

**Kiểm tra trước khi báo xong**
1. `php -l` cho mọi file PHP đã sửa; ghi kết quả.
2. Tìm trùng khai báo hàm ("Cannot redeclare") giữa các file được include chung.
3. Đối chiếu mọi tên bảng/cột đã dùng với `studio.sql`.
4. Liệt kê các bước sinh viên tự kiểm tra bằng tay trên trình duyệt (đường bình thường và đường lỗi).

**Định dạng báo cáo sau mỗi lô**
1. Việc đã làm: mỗi file một dòng (file, đổi gì, vì sao).
2. Nguyên nhân gốc: ví dụ đời thường trước, kỹ thuật sau.
3. Kết quả kiểm tra (`php -l`, đối chiếu SQL).
4. Các bước sinh viên tự kiểm tra bằng tay.
5. Ảnh hưởng đến thành viên khác (file dùng chung đã sửa).
6. Phát hiện thêm (chưa sửa).
7. Cần sinh viên quyết định gì.
8. Câu hỏi bảo vệ: 2-3 câu giảng viên có thể hỏi về phần vừa sửa, kèm gợi ý trả lời ngắn.

## 13. NHIỆM VỤ HIỆN TẠI

**Trạng thái hôm nay (11/11):** Lô 1, 2, 3, 4, 5, 6, 7, 8 của lộ trình đã xong hoặc xác minh (xem mục 11). Chưa commit gì, mọi thay đổi nằm trên máy.

**Việc tiếp theo — sinh viên chọn một lô rồi dán vào đây (mỗi lô tối đa 5 file):**

| Ưu tiên | Lô | Việc | File |
|---|---|---|---|
| 1 | Lô C | ~~Tìm kiếm popup chống XSS~~ — **ĐÃ XONG (11/11)** | `includes/search_popup.php` |
| 2 | Lô D | ~~Doanh thu/hoàn tiền~~ — **ĐÃ XÁC MINH (11/11), không có lỗi** | — |
| 3 | Lô E | ~~Phân trang admin + link `order-detail` sai đường dẫn~~ — **ĐÃ XONG (11/11)** | `admin/nguoi-dung.php` |
| 4 | Xác minh | Cách tính đơn vị `lượt` (8.2), giờ ngoài mở cửa (8.3) — chỉ đọc + báo, KHÔNG sửa trước khi chốt | `includes/don_vi.php`, `includes/cart_helper.php` |
| 5 | Chờ nhóm | Trả lượt mã giảm giá khi hủy/từ chối (8.5) — nhóm chốt chính sách rồi mới làm | `vendor/vendor-orders.php`, `user/my-orders.php` |
| 6 | Sinh viên tự làm | Xuất lại `database/studio.sql` cho khớp CSDL thật (thiếu `cau_hinh`, `dangnhap_thatbai`); làm sạch dữ liệu cá nhân trước khi nộp | `database/studio.sql` |

Chưa ghi gì vào phần dưới: nhiệm vụ cụ thể của lô đang chờ sinh viên chọn.

PHÂN CÔNG NHÓM (sinh viên điền; để trống thì chỉ sửa file được nêu trong nhiệm vụ):
[ai phụ trách thư mục/file nào]