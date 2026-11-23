-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th10 23, 2026 lúc 06:52 AM
-- Phiên bản máy phục vụ: 10.4.28-MariaDB
-- Phiên bản PHP: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `studio`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `baocao_huhong`
--

CREATE TABLE `baocao_huhong` (
  `BCHH_Ma` int(11) NOT NULL,
  `DV_Ma` int(11) NOT NULL,
  `NV_Ma` int(11) NOT NULL,
  `BCHH_BoPhan` varchar(120) DEFAULT NULL,
  `BCHH_MoTa` text NOT NULL,
  `BCHH_TrangThai` varchar(30) NOT NULL DEFAULT 'Chờ xử lý',
  `BCHH_NgayBao` datetime NOT NULL DEFAULT current_timestamp(),
  `BCHH_NgayCapNhat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `baocao_huhong`
--

INSERT INTO `baocao_huhong` (`BCHH_Ma`, `DV_Ma`, `NV_Ma`, `BCHH_BoPhan`, `BCHH_MoTa`, `BCHH_TrangThai`, `BCHH_NgayBao`, `BCHH_NgayCapNhat`) VALUES
(1, 15, 1, 'Đèn softbox bên phải', 'hỏng', 'Đang bảo trì', '2026-11-23 09:33:48', '2026-11-23 09:34:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cau_hinh`
--

CREATE TABLE `cau_hinh` (
  `CH_Khoa` varchar(50) NOT NULL,
  `CH_GiaTri` varchar(255) NOT NULL,
  `CH_MoTa` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `cau_hinh`
--

INSERT INTO `cau_hinh` (`CH_Khoa`, `CH_GiaTri`, `CH_MoTa`) VALUES
('buoc_thoi_gian_phut', '60', 'Bước chọn giờ (phút). VD 30 hoặc 60'),
('cho_phep_chon_ngoai_gio', '0', '1 = cho phép chọn ngoài giờ mở/đóng cửa'),
('gio_dong_cua', '20:00', 'Giờ đóng cửa studio (HH:MM)'),
('gio_mo_cua', '08:00', 'Giờ mở cửa studio (HH:MM)');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_hoi_thoai`
--

CREATE TABLE `chat_hoi_thoai` (
  `CHT_Ma` int(11) NOT NULL,
  `ND_Ma` int(11) NOT NULL,
  `NV_Ma` int(11) DEFAULT NULL,
  `CHT_TieuDe` varchar(150) DEFAULT NULL,
  `CHT_CheDo` varchar(20) NOT NULL DEFAULT 'bot',
  `CHT_TrangThai` varchar(30) NOT NULL DEFAULT 'dang_mo',
  `CHT_NgayTao` datetime NOT NULL DEFAULT current_timestamp(),
  `CHT_NgayCapNhat` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `CHT_NgayDong` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chat_hoi_thoai`
--

INSERT INTO `chat_hoi_thoai` (`CHT_Ma`, `ND_Ma`, `NV_Ma`, `CHT_TieuDe`, `CHT_CheDo`, `CHT_TrangThai`, `CHT_NgayTao`, `CHT_NgayCapNhat`, `CHT_NgayDong`) VALUES
(1, 4, 1, 'Hỗ trợ khách hàng VIBE STUDIO', 'nhanvien', 'dang_ho_tro', '2026-11-01 14:45:34', '2026-11-07 15:25:37', NULL),
(2, 4, 1, 'Tư vấn: Fujifilm X-S20', 'nhanvien', 'da_dong', '2026-11-01 15:26:15', '2026-11-07 15:25:50', '2026-11-07 15:25:50'),
(3, 6, NULL, 'Tư vấn: Bộ Đồ Retro', 'bot', 'dang_mo', '2026-11-02 07:19:35', '2026-11-02 07:19:35', NULL),
(4, 6, 1, 'Tư vấn: Bộ Đồ Retro', 'nhanvien', 'dang_ho_tro', '2026-11-02 07:19:53', '2026-11-02 19:01:14', NULL),
(5, 4, NULL, 'Hỗ trợ khách hàng VIBE STUDIO', 'nhanvien', 'cho_nhan_vien', '2026-11-09 13:08:22', '2026-11-11 15:02:20', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_tin_nhan`
--

CREATE TABLE `chat_tin_nhan` (
  `CTN_Ma` bigint(20) NOT NULL,
  `CHT_Ma` int(11) NOT NULL,
  `CTN_NguoiGui` varchar(20) NOT NULL,
  `ND_Ma` int(11) DEFAULT NULL,
  `NV_Ma` int(11) DEFAULT NULL,
  `CTN_NoiDung` text NOT NULL,
  `CTN_ThoiDiem` datetime NOT NULL DEFAULT current_timestamp(),
  `CTN_DaDoc` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chat_tin_nhan`
--

INSERT INTO `chat_tin_nhan` (`CTN_Ma`, `CHT_Ma`, `CTN_NguoiGui`, `ND_Ma`, `NV_Ma`, `CTN_NoiDung`, `CTN_ThoiDiem`, `CTN_DaDoc`) VALUES
(1, 1, 'khach', 4, NULL, 'hi', '2026-11-01 14:45:34', 1),
(2, 1, 'bot', NULL, NULL, 'Xin chào bạn! VIBE STUDIO có thể hỗ trợ thông tin sản phẩm, giá thuê và cách đặt lịch. Bạn muốn tìm hiểu điều gì?', '2026-11-01 14:45:34', 1),
(3, 1, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê dịch vụ.', '2026-11-01 14:45:38', 1),
(4, 1, 'bot', NULL, NULL, 'Bạn có thể xem giá thuê và đơn vị tính ngay trên thẻ sản phẩm hoặc trang chi tiết sản phẩm. Nếu bạn cần tư vấn giá cho thời gian thuê cụ thể, hãy nhắn “Gặp nhân viên”.', '2026-11-01 14:45:38', 1),
(5, 1, 'khach', 4, NULL, 'Tôi muốn đặt lịch chụp ảnh.', '2026-11-01 14:45:44', 1),
(6, 1, 'bot', NULL, NULL, 'Bạn hãy chọn sản phẩm, nhấn “Thuê ngay”, nhập thông tin và thời gian thuê, sau đó kiểm tra lại đơn trước khi xác nhận. Nếu cần trợ giúp, hãy nhắn “Gặp nhân viên”.', '2026-11-01 14:45:44', 1),
(7, 1, 'khach', 4, NULL, 'Tôi cần nhân viên hỗ trợ.', '2026-11-01 14:45:46', 1),
(8, 1, 'bot', NULL, NULL, 'Mình đã ghi nhận yêu cầu hỗ trợ trực tiếp. Tin nhắn của bạn sẽ được lưu để nhân viên tiếp nhận. Hiện hệ thống cần có nhân viên trực và giao diện quản lý chat để nhân viên phản hồi.', '2026-11-01 14:45:46', 1),
(9, 1, 'khach', 4, NULL, 'hi', '2026-11-01 14:55:23', 1),
(10, 1, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê dịch vụ.', '2026-11-01 14:55:31', 1),
(11, 1, 'khach', 4, NULL, 'Tôi muốn đặt lịch chụp ảnh.', '2026-11-01 14:55:33', 1),
(12, 1, 'khach', 4, NULL, 'Tôi cần nhân viên hỗ trợ.', '2026-11-01 14:55:34', 1),
(13, 1, 'khach', 4, NULL, 'Tôi cần nhân viên hỗ trợ.', '2026-11-01 14:55:36', 1),
(14, 1, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê dịch vụ.', '2026-11-01 15:22:44', 1),
(15, 1, 'khach', 4, NULL, 'Tôi muốn đặt lịch chụp ảnh.', '2026-11-01 15:22:48', 1),
(16, 1, 'khach', 4, NULL, 'Tôi cần nhân viên hỗ trợ.', '2026-11-01 15:22:52', 1),
(17, 1, 'bot', NULL, NULL, 'Nhân viên Nhân viên mẫu đã tham gia hỗ trợ bạn.', '2026-11-01 15:24:49', 1),
(18, 1, 'nhanvien', NULL, 1, 'xin chào t có thể giúp gì cho bạn', '2026-11-01 15:25:03', 1),
(19, 2, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê sản phẩm.', '2026-11-01 15:26:15', 1),
(20, 2, 'bot', NULL, NULL, 'Bạn có thể xem giá thuê và đơn vị tính ngay trên thẻ sản phẩm hoặc trang chi tiết sản phẩm. Nếu bạn cần tư vấn giá cho thời gian thuê cụ thể, hãy nhắn “Gặp nhân viên”.', '2026-11-01 15:26:15', 1),
(21, 2, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê.', '2026-11-01 15:26:18', 1),
(22, 2, 'bot', NULL, NULL, 'Bạn có thể xem giá thuê và đơn vị tính ngay trên thẻ sản phẩm hoặc trang chi tiết sản phẩm. Nếu bạn cần tư vấn giá cho thời gian thuê cụ thể, hãy nhắn “Gặp nhân viên”.', '2026-11-01 15:26:18', 1),
(23, 2, 'khach', 4, NULL, 'Tôi cần nhân viên hỗ trợ.', '2026-11-01 15:26:19', 1),
(24, 2, 'bot', NULL, NULL, 'Mình đã chuyển yêu cầu của bạn cho nhân viên VIBE STUDIO. Nhân viên sẽ phản hồi ngay tại khung chat này, bạn vui lòng chờ trong giây lát nhé.', '2026-11-01 15:26:19', 1),
(25, 2, 'bot', NULL, NULL, 'Nhân viên Nhân viên mẫu đã tham gia hỗ trợ bạn.', '2026-11-01 15:26:49', 1),
(26, 2, 'nhanvien', NULL, 1, 'hi', '2026-11-01 15:26:49', 1),
(27, 1, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê dịch vụ.', '2026-11-01 15:29:14', 1),
(28, 1, 'khach', 4, NULL, 'Tôi muốn đặt lịch chụp ảnh.', '2026-11-01 15:29:15', 1),
(29, 1, 'khach', 4, NULL, 'Tôi cần nhân viên hỗ trợ.', '2026-11-01 15:29:16', 1),
(30, 1, 'khach', 4, NULL, 'hi', '2026-11-01 15:30:46', 1),
(31, 1, 'khach', 4, NULL, 'hi', '2026-11-01 15:31:03', 1),
(32, 3, 'khach', 6, NULL, 'Tôi muốn hỏi giá thuê.', '2026-11-02 07:19:35', 1),
(33, 3, 'bot', NULL, NULL, 'Bạn có thể xem giá thuê và đơn vị tính ngay trên thẻ sản phẩm hoặc trang chi tiết sản phẩm. Nếu bạn cần tư vấn giá cho thời gian thuê cụ thể, hãy nhắn “Gặp nhân viên”.', '2026-11-02 07:19:35', 0),
(34, 4, 'khach', 6, NULL, 'Tôi cần nhân viên hỗ trợ.', '2026-11-02 07:19:53', 1),
(35, 4, 'bot', NULL, NULL, 'Mình đã chuyển yêu cầu của bạn cho nhân viên VIBE STUDIO. Nhân viên sẽ phản hồi ngay tại khung chat này, bạn vui lòng chờ trong giây lát nhé.', '2026-11-02 07:19:53', 1),
(36, 1, 'khach', 4, NULL, 'hi', '2026-11-02 18:53:06', 1),
(37, 1, 'khach', 4, NULL, 'hi', '2026-11-02 18:57:14', 1),
(38, 4, 'bot', NULL, NULL, 'Nhân viên Nhân viên mẫu đã tham gia hỗ trợ bạn.', '2026-11-02 19:01:14', 0),
(39, 1, 'khach', 4, NULL, 'hi', '2026-11-02 19:46:45', 1),
(40, 1, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê.', '2026-11-07 13:53:28', 1),
(41, 1, 'khach', 4, NULL, 'Tôi cần nhân viên hỗ trợ.', '2026-11-07 13:53:29', 1),
(42, 1, 'nhanvien', NULL, 1, 'chào bạn , t có thể giúp gì nào', '2026-11-07 15:25:37', 0),
(43, 2, 'bot', NULL, NULL, 'Nhân viên đã kết thúc cuộc trò chuyện. Cảm ơn bạn đã liên hệ VIBE STUDIO!', '2026-11-07 15:25:50', 1),
(44, 5, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê dịch vụ.', '2026-11-09 13:08:22', 1),
(45, 5, 'bot', NULL, NULL, 'Bạn có thể xem giá thuê và đơn vị tính ngay trên thẻ sản phẩm hoặc trang chi tiết sản phẩm. Nếu bạn cần tư vấn giá cho thời gian thuê cụ thể, hãy nhắn “Gặp nhân viên”.', '2026-11-09 13:08:22', 1),
(46, 5, 'khach', 4, NULL, 'Gặp nhân viên', '2026-11-09 13:09:03', 1),
(47, 5, 'bot', NULL, NULL, 'Mình đã chuyển yêu cầu của bạn cho nhân viên VIBE STUDIO. Nhân viên sẽ phản hồi ngay tại khung chat này, bạn vui lòng chờ trong giây lát nhé.', '2026-11-09 13:09:03', 1),
(50, 5, 'khach', 4, NULL, 'hi bạn', '2026-11-09 13:09:12', 1),
(51, 5, 'khach', 4, NULL, 'hi bạn', '2026-11-09 13:09:18', 1),
(52, 5, 'khach', 4, NULL, 'hi', '2026-11-09 13:09:25', 1),
(53, 5, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê.', '2026-11-09 13:09:51', 1),
(54, 5, 'khach', 4, NULL, 'Tôi cần nhân viên hỗ trợ.', '2026-11-09 13:09:53', 1),
(55, 5, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê sản phẩm.', '2026-11-09 13:10:04', 1),
(56, 5, 'khach', 4, NULL, 'Tôi muốn biết cách đặt thuê.', '2026-11-09 13:10:07', 1),
(58, 5, 'khach', 4, NULL, 'Tôi muốn gặp nhân viên hỗ trợ.', '2026-11-09 13:10:15', 1),
(59, 5, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê.', '2026-11-09 13:10:17', 1),
(60, 5, 'khach', 4, NULL, 'hi', '2026-11-09 13:10:29', 1),
(65, 5, 'khach', 4, NULL, 'hu', '2026-11-09 15:03:07', 1),
(66, 5, 'khach', 4, NULL, 'Tôi muốn hỏi giá thuê.', '2026-11-11 15:02:20', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdon`
--

CREATE TABLE `chitietdon` (
  `CTD_Ma` int(11) NOT NULL,
  `DDL_Ma` int(11) NOT NULL,
  `DV_Ma` int(11) NOT NULL,
  `CTD_SoLuong` int(11) NOT NULL DEFAULT 1,
  `CTD_KichCo` varchar(20) DEFAULT NULL,
  `CTD_BatDau` datetime NOT NULL,
  `CTD_KetThuc` datetime NOT NULL,
  `CTD_DonGia` decimal(12,0) NOT NULL,
  `CTD_ThanhTien` decimal(12,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdon`
--

INSERT INTO `chitietdon` (`CTD_Ma`, `DDL_Ma`, `DV_Ma`, `CTD_SoLuong`, `CTD_KichCo`, `CTD_BatDau`, `CTD_KetThuc`, `CTD_DonGia`, `CTD_ThanhTien`) VALUES
(1, 1, 8, 1, NULL, '2026-10-20 08:00:00', '2026-10-20 20:00:00', 380000, 380000),
(2, 2, 3, 1, NULL, '2026-10-20 08:00:00', '2026-10-20 20:00:00', 350000, 350000),
(3, 3, 7, 1, NULL, '2026-10-20 08:00:00', '2026-10-22 08:00:00', 450000, 900000),
(4, 4, 7, 1, NULL, '2026-10-25 08:00:00', '2026-10-26 08:00:00', 450000, 450000),
(5, 5, 7, 1, NULL, '2026-10-24 08:00:00', '2026-10-25 08:00:00', 450000, 450000),
(6, 6, 20, 1, NULL, '2026-10-26 08:00:00', '2026-10-27 08:00:00', 200000, 200000),
(7, 7, 8, 1, NULL, '2026-10-27 08:00:00', '2026-10-28 08:00:00', 380000, 380000),
(8, 8, 24, 1, NULL, '2026-10-27 08:00:00', '2026-11-05 08:00:00', 180000, 1620000),
(9, 8, 8, 3, NULL, '2026-10-27 08:00:00', '2026-10-28 08:00:00', 380000, 1140000),
(10, 9, 7, 1, NULL, '2026-10-27 08:00:00', '2026-10-28 08:00:00', 450000, 450000),
(11, 10, 6, 1, NULL, '2026-10-27 08:00:00', '2026-10-28 08:00:00', 320000, 320000),
(12, 11, 1, 1, NULL, '2026-10-31 08:00:00', '2026-11-02 08:00:00', 500000, 1000000),
(13, 12, 12, 1, NULL, '2026-11-01 08:00:00', '2026-11-01 17:00:00', 300000, 2700000),
(14, 13, 8, 2, NULL, '2026-11-03 08:00:00', '2026-11-03 20:00:00', 380000, 760000),
(15, 14, 8, 1, NULL, '2026-11-02 21:00:00', '2026-11-03 21:00:00', 380000, 380000),
(16, 15, 8, 1, NULL, '2026-11-07 16:00:00', '2026-11-09 16:00:00', 380000, 760000),
(17, 16, 8, 1, NULL, '2026-11-09 14:00:00', '2026-11-10 14:00:00', 380000, 380000),
(22, 21, 7, 1, NULL, '2026-11-14 22:00:00', '2026-11-17 22:00:00', 450000, 1350000),
(23, 22, 7, 1, NULL, '2026-11-13 22:00:00', '2026-11-14 22:00:00', 450000, 450000),
(24, 23, 6, 1, NULL, '2026-11-13 23:00:00', '2026-11-20 23:00:00', 320000, 2240000),
(25, 23, 6, 1, NULL, '2026-11-13 23:00:00', '2026-12-03 23:00:00', 320000, 6400000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdon_bophan`
--

CREATE TABLE `chitietdon_bophan` (
  `CTD_Ma` int(11) NOT NULL,
  `CTDBP_Ten` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dangnhap_thatbai`
--

CREATE TABLE `dangnhap_thatbai` (
  `DNTB_Ma` int(11) NOT NULL,
  `DNTB_Email` varchar(100) NOT NULL,
  `DNTB_IP` varchar(45) NOT NULL,
  `DNTB_ThoiGian` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhgia`
--

CREATE TABLE `danhgia` (
  `DG_Ma` int(11) NOT NULL,
  `ND_Ma` int(11) NOT NULL,
  `DDL_Ma` int(11) NOT NULL,
  `DV_Ma` int(11) NOT NULL,
  `DG_SoSao` tinyint(1) NOT NULL,
  `DG_NoiDung` text DEFAULT NULL,
  `DG_NgayTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `danhgia`
--

INSERT INTO `danhgia` (`DG_Ma`, `ND_Ma`, `DDL_Ma`, `DV_Ma`, `DG_SoSao`, `DG_NoiDung`, `DG_NgayTao`) VALUES
(1, 4, 6, 20, 5, NULL, '2026-10-27 12:20:44'),
(2, 4, 7, 8, 5, 'trải nghiệm tốt', '2026-10-27 12:23:47'),
(3, 4, 4, 7, 4, 'tốt', '2026-10-27 12:26:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dichvu`
--

CREATE TABLE `dichvu` (
  `DV_Ma` int(11) NOT NULL,
  `DV_Ten` varchar(150) NOT NULL,
  `DV_Loai` varchar(30) NOT NULL,
  `DV_MoTa` text DEFAULT NULL,
  `DV_MoTaChiTiet` text DEFAULT NULL,
  `DV_GiaThue` decimal(12,0) NOT NULL,
  `DV_DonViTinh` varchar(20) NOT NULL DEFAULT 'giờ',
  `DV_HinhAnh` varchar(255) DEFAULT NULL,
  `DV_SoLuong` int(11) NOT NULL DEFAULT 1,
  `DV_DangBan` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `dichvu`
--

INSERT INTO `dichvu` (`DV_Ma`, `DV_Ten`, `DV_Loai`, `DV_MoTa`, `DV_MoTaChiTiet`, `DV_GiaThue`, `DV_DonViTinh`, `DV_HinhAnh`, `DV_SoLuong`, `DV_DangBan`) VALUES
(1, 'Lumix', 'thiet_bi', 'Máy ảnh Full Frame phù hợp cho chụp chân dung và sự kiện.', NULL, 500000, 'ngày', 'cam1.png', 3, 1),
(2, 'Sony 7C', 'thiet_bi', 'Máy ảnh đa dụng, chất lượng hình ảnh cao, phù hợp nhiều concept.', NULL, 450000, 'ngày', 'cam2.png', 3, 1),
(3, 'Fujifilm X-S10', 'thiet_bi', 'Máy ảnh Fujifilm dễ sử dụng, thích hợp cho người mới bắt đầu.', NULL, 350000, 'ngày', 'cam3.png', 3, 1),
(4, 'Canon EOS', 'thiet_bi', 'Thiết kế nhỏ gọn, tiện lợi cho chụp ảnh cá nhân.', NULL, 300000, 'ngày', 'cam4.png', 3, 1),
(5, 'Canon EOS 250D', 'thiet_bi', 'Máy ảnh phong cách cổ điển, phù hợp chụp chân dung nghệ thuật.', NULL, 400000, 'ngày', 'cam5.png', 3, 1),
(6, 'Fujifilm X-T4', 'thiet_bi', 'Máy ảnh nhỏ gọn, lấy nét nhanh và dễ dàng sử dụng.', NULL, 320000, 'ngày', 'cam6.png', 3, 1),
(7, 'Fujifilm X-T5', 'thiet_bi', 'Máy ảnh Full Frame phù hợp cho nhiều thể loại chụp ảnh chuyên nghiệp.', NULL, 450000, 'ngày', 'cam7.png', 3, 1),
(8, 'Fujifilm X-A5', 'thiet_bi', 'Máy ảnh nhỏ gọn, phù hợp chụp ảnh và quay video sáng tạo.', NULL, 380000, 'ngày', 'cam8.png', 3, 1),
(9, 'Phòng Studio Trắng', 'phong_chup', 'Không gian trắng tối giản, phù hợp chụp chân dung và sản phẩm.', 'Phông nền trắng\nĐèn softbox bên trái\nĐèn softbox bên phải\nĐèn chiếu sáng trên ray trần\nBàn làm việc\nGhế đẩu\nMàn hình theo dõi', 200000, 'giờ', 'phong1.png', 1, 1),
(10, 'Phòng Vintage', 'phong_chup', 'Không gian mang phong cách vintage, ấm áp và độc đáo.', 'Sofa xanh vintage\nBàn trà gỗ\nBàn ăn gỗ\nGhế ăn gỗ\nKhung tranh treo tường\nĐèn tản sáng studio\nRay trượt máy quay', 250000, 'giờ', 'phong2.png', 1, 1),
(11, 'Phòng Concept Hàn Quốc', 'phong_chup', 'Không gian sáng, nhẹ nhàng phù hợp chụp ảnh cá nhân, lookbook.', 'Sofa đen\nBàn trà nhỏ\nThảm khu chụp\nPhông nền trắng\nĐèn softbox\nĐèn đứng studio', 220000, 'giờ', 'phong3.png', 1, 1),
(12, 'Khu Chụp Ngoài Trời', 'phong_chup', 'Không gian ngoài trời với nhiều góc chụp tự nhiên, thoáng đãng.', 'Phông nền cuộn trắng\nĐèn softbox\nĐèn tản sáng dạng lưới\nGiàn khung treo đèn\nXe đẩy máy quay\nMàn hình theo dõi', 300000, 'giờ', 'phong4.png', 1, 1),
(13, 'Phòng Couple', 'phong_chup', 'Không gian lãng mạn dành cho các concept chụp ảnh đôi, cưới.', 'Ghế bành trắng\nBàn đá lớn\nChậu cây trang trí\nKệ trang trí\nPhông nền trắng\nĐèn softbox', 280000, 'giờ', 'phong5.png', 1, 1),
(14, 'Phòng Concept Hoa', 'phong_chup', 'Không gian trang trí nhiều hoa tươi và màu sắc sinh động.', 'Phông nền trắng\nGhế gỗ\nGhế đẩu gỗ\nĐèn softbox bên trái\nĐèn softbox bên phải\nMàn hình theo dõi', 250000, 'giờ', 'phong6.png', 1, 1),
(15, 'Phòng Minimal', 'phong_chup', 'Không gian tối giản, hiện đại, dễ dàng phối hợp nhiều concept.', 'Phông nền trắng\nĐèn softbox bên trái\nĐèn softbox bên phải\nMàn hình theo dõi\nGiá treo trang phục\nBàn làm việc\nGhế bàn', 180000, 'giờ', 'phong7.png', 1, 1),
(16, 'Phòng Luxury', 'phong_chup', 'Không gian sang trọng, đẳng cấp, thích hợp chụp ảnh thời trang.', 'Phông nền trắng\nĐèn softbox bên trái\nĐèn softbox bên phải\nMàn hình theo dõi\nGiá treo trang phục\nBàn dụng cụ', 350000, 'giờ', 'phong8.png', 1, 1),
(17, 'Streetwear', 'trang_phuc', 'Trang phục phong cách đường phố pha chút cổ điển cực kỳ cá tính và nổi bật.', NULL, 150000, 'lượt', 'bo1.png', 10, 1),
(18, 'Aesthetic Grunge', 'trang_phuc', 'Trang phục với phong cách Y2K pha chút Vintage Grunge đầy cá tính.', NULL, 180000, 'lượt', 'bo2.png', 10, 1),
(19, 'Hanbok Hàn Quốc', 'trang_phuc', 'Trang phục Hanbok truyền thống nhiều màu sắc nổi bật.', NULL, 200000, 'lượt', 'bo3.png', 10, 1),
(20, 'Mori Boy', 'trang_phuc', 'Trang phục mang phong cách Mori Boy kết hợp với Goblincore hoặc Gorpcore nhẹ.', NULL, 200000, 'lượt', 'bo4.png', 10, 1),
(21, 'Gothic Minimalist', 'trang_phuc', 'Trang phục mang phong cách Darkwear cá tính. ', NULL, 220000, 'lượt', 'bo5.png', 10, 1),
(22, 'Retro', 'trang_phuc', 'Trang phục mang phong cách retro năng động, cá tính.', NULL, 160000, 'lượt', 'bo6.png', 10, 1),
(23, 'Napoleon Jacket', 'trang_phuc', 'Bộ trang phục mang đậm phong cách Subversive Grunge kết hợp Dark Coquette.', NULL, 170000, 'lượt', 'bo7.png', 10, 1),
(24, 'Zoey Dress', 'trang_phuc', 'Bộ trang phục mang phong cách Coquette kết hợp với tông màu nâu và hồng nữ tính.', NULL, 180000, 'lượt', 'bo8.png', 10, 1),
(25, 'Chân đèn Victory MD-240A', 'phu_kien', 'Chân đèn chắc chắn, chịu lực cao cho đèn flash studio.', NULL, 30000, 'giờ', 'chanden1.png', 4, 1),
(26, 'Chân đèn AMmastar AT-850', 'phu_kien', 'Thiết kế ba chân thấp và xoè rộng giúp hạ thấp trọng tâm, tăng cường độ chịu lực và chống lật hiệu quả cho các thiết bị gắn phía trên', NULL, 45000, 'giờ', 'chanden2.png', 3, 1),
(27, 'Chân đèn Jinbei M1', 'phu_kien', 'Chân đèn với thiết kế ba chân có độ cao khác nhau xếp chồng lên nhau được, giúp chịu tải trọng lớn của đèn studio', NULL, 60000, 'giờ', 'chanden3.png', 2, 1),
(28, 'Chân đèn mini LS-32', 'phu_kien', 'Chân đèn nhỏ gọn hỗ trợ đánh sáng góc thấp.', NULL, 20000, 'giờ', 'chanden4.png', 5, 1),
(29, 'Chân máy quay Ulanzi MT -81', 'phu_kien', 'Chân máy ảnh vững chãi, đầu bi mượt mà.', NULL, 300000, 'giờ', 'chanmay1.png', 3, 1),
(30, 'Chân máy ảnh Tripob Beike Q166A', 'phu_kien', 'Chân máy chất liệu carbon siêu nhẹ, dễ di chuyển.', NULL, 100000, 'giờ', 'chanmay2.png', 2, 1),
(31, 'Tấm hắt sáng phẳng', 'phu_kien', 'Đạo cụ hắt sáng hỗ trợ đổi màu (vàng, bạc, trắng, đen, mờ).', NULL, 100000, 'lượt', 'hatsang1.png', 4, 1),
(32, 'Tấm hắt sáng 5in1 80cm', 'phu_kien', 'Tấm hắt sáng kích thước lớn dùng cho chụp toàn thân.', NULL, 800000, 'lượt', 'hatsang2.png', 3, 1),
(33, 'Len Canon RF 85mm f/2 Macro', 'phu_kien', 'Len chụp chân dung quốc dân, nhỏ gọn, nét tốt.', NULL, 500000, 'ngày', 'len1.png', 3, 1),
(34, 'Len Nikon PC-E 45mm f2.8D ED', 'phu_kien', 'Len zoom cao cấp khẩu lớn chuyên nghiệp.', NULL, 400000, 'ngày', 'len2.png', 2, 1),
(35, 'Len Canon EF 20-35mm f/2.8L USM', 'phu_kien', 'Len chuyên chụp phong cảnh, không gian rộng trong studio.', NULL, 450000, 'ngày', 'len3.png', 2, 1),
(36, 'Len Fujifilm XF 500mm F5.6 LM', 'phu_kien', 'Len chuyên chụp cận cảnh chi tiết sản phẩm, trang sức.', NULL, 1300000, 'ngày', 'len4.png', 2, 1),
(37, 'Look 1 - Royal White', 'dich_vu', 'Gói chụp ảnh cưới phong cách hoàng gia thanh lịch tại studio.', 'cuoi1.png,vay1.png,vay2.png,vay3.png,vay4.png,vay5.png', 2500000, 'gói', 'cuoi12.png', 5, 1),
(38, 'Look 2 - Classic Couple', 'dich_vu', 'Concept chụp đôi cổ điển, nhẹ nhàng và tự nhiên.', 'Bao gồm: Trang phục cô dâu chú rể, makeup tone Hàn Quốc, chụp không giới hạn file tại 2 bối cảnh studio.', 2200000, 'gói', 'cuoi2.png', 5, 1),
(39, 'Look 3 - European Vintage', 'dich_vu', 'Phong cách cưới châu Âu cổ điển sang trọng.', 'Bao gồm: Váy cưới đuôi cá hoặc xòe bồng, vest tông trầm, hỗ trợ phụ kiện hoa cầm tay và khăn voan.', 2800000, 'gói', 'cuoi3.png', 5, 1),
(40, 'Look 4 - Modern Confetti', 'dich_vu', 'Concept hiện đại, trẻ trung và rạng rỡ.', 'Bao gồm: Trang phục hiện đại, đạo cụ pháo giấy/hoa tươi, ekip nhiếp ảnh gia và stylist theo sát buổi chụp.', 2400000, 'gói', 'cuoi4.png', 5, 1),
(41, 'Look 5 - Champagne Toast', 'dich_vu', 'Bối cảnh tiệc rượu sang trọng, ấm cúng.', 'Bao gồm: Bàn tiệc decor sẵn, ly champagne, trang phục dự tiệc/cưới cao cấp và chỉnh màu nghệ thuật.', 2600000, 'gói', 'cuoi5.png', 5, 1),
(42, 'Look 6 - Luxury Golden', 'dich_vu', 'Concept ánh vàng hoàng gia lộng lẫy.', 'Bao gồm: Bối cảnh đèn chùm pha lê, váy cưới đính kết thủ công, makeup chuyên nghiệp.', 3200000, 'gói', 'cuoi6.png', 5, 1),
(43, 'Look 7 - Spring Blossom', 'dich_vu', 'Không gian ngập tràn hoa tươi mùa xuân lãng mạn.', 'Bao gồm: Decor hoa tươi theo mùa, 2 bộ trang phục tự chọn, tặng kèm 1 ảnh phóng lớn ép gỗ.', 2700000, 'gói', 'cuoi7.png', 5, 1),
(44, 'Look 8 - Minimalist White', 'dich_vu', 'Concept chụp ảnh cưới mang phong cách tạp chí thời trang hiện đại, cá tính.', 'Bao gồm: Trang phục cô dâu chú rể thiết kế riêng, makeup tone editorial, chụp tại studio với ánh sáng nghệ thuật.', 2900000, 'gói', 'cuoi8.png', 5, 1),
(45, 'Mẫu 1 - Váy Cưới 1', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay1.png', 10, 1),
(46, 'Mẫu 2 - Váy Cưới 2', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay2.png', 10, 1),
(47, 'Mẫu 3 - Váy Cưới 3', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay3.png', 10, 1),
(48, 'Mẫu 4 - Váy Cưới 4', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay4.png', 10, 1),
(49, 'Mẫu 5 - Váy Cưới 5', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay5.png', 10, 1),
(50, 'Mẫu 6 - Váy Cưới 6', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay6.png', 10, 1),
(51, 'Mẫu 7 - Váy Cưới 7', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay7.png', 10, 1),
(52, 'Mẫu 8 - Váy Cưới 8', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay8.png', 10, 1),
(53, 'Mẫu 9 - Váy Cưới 9', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay9.png', 10, 1),
(54, 'Mẫu 10 - Váy Cưới 10', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay10.png', 10, 1),
(55, 'Mẫu 11 - Váy Cưới 11', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay11.png', 10, 1),
(56, 'Mẫu 12 - Váy Cưới 12', 'trang_phuc', 'Trang phục cô dâu & chú rể.', NULL, 180000, 'ngày', 'vay12.png', 10, 1),
(57, 'Áo Dài 1 - Tĩnh Mịch Hoa Loa Kèn', 'trang_phuc', 'Áo dài gấm đỏ sang trọng phù hợp lễ ăn hỏi và chụp ảnh cưới truyền thống.', NULL, 220000, 'gói', 'aodai1.png', 10, 1),
(58, 'Áo Dài 2 - Thanh Khí Sen Trắng', 'trang_phuc', 'Áo dài lụa trắng tinh khôi, nhẹ nhàng, dễ phối hợp nhiều concept.', NULL, 200000, 'gói', 'aodai2.png', 10, 1),
(59, 'Áo Dài 3 - Duyên Xanh Bên Quạt', 'trang_phuc', 'Thiết kế áo dài cách tân cổ yếm trẻ trung, tôn dáng và nổi bật.', NULL, 210000, 'gói', 'aodai3.png', 10, 1),
(60, 'Áo Dài 4 - Sắc Hồng Gia Tiên', 'trang_phuc', 'Áo dài họa tiết hoa sen thêu tinh xảo mang nét đẹp truyền thống Việt Nam.', NULL, 220000, 'gói', 'aodai4.png', 10, 1),
(61, 'Áo Dài 5 - Nàng Xuân Cổng Cổ', 'trang_phuc', 'Set áo dài đồng điệu dành cho cặp đôi chụp ảnh lễ gia tiên và kỷ niệm.', NULL, 260000, 'gói', 'aodai5.png', 10, 1),
(62, 'Áo Dài 6 - Khúc Cầm Nón Hoa', 'trang_phuc', 'Tông màu pastel ngọt ngào, nữ tính, phù hợp chụp studio và ngoại cảnh.', NULL, 200000, 'gói', 'aodai6.png', 10, 1),
(63, 'Áo Dài 7 - Mộng Sen Voan Trắng', 'trang_phuc', 'Chất liệu nhung cao cấp, lên ảnh đằm thắm và quý phái.', NULL, 240000, 'gói', 'aodai7.png', 10, 1),
(64, 'Áo Dài 8 - Phu Thê Son Sắt', 'trang_phuc', 'Phong cách áo dài cổ điển nhẹ nhàng, hợp concept phố cổ và vintage.', NULL, 210000, 'gói', 'aodai8.png', 10, 1),
(69, 'Áo Dài 1 - Gấm Đỏ Hoàng Gia', 'trang_phuc', 'Áo dài gấm đỏ sang trọng phù hợp lễ ăn hỏi và chụp ảnh cưới truyền thống.', NULL, 220000, 'gói', 'ao1.png', 10, 1),
(70, 'Áo Dài 2 - Lụa Trắng Thanh Lịch', 'trang_phuc', 'Áo dài lụa trắng tinh khôi, nhẹ nhàng, dễ phối hợp nhiều concept.', NULL, 200000, 'gói', 'ao2.png', 10, 1),
(71, 'Áo Dài 3 - Cổ Yếm Cách Tân', 'trang_phuc', 'Thiết kế áo dài cách tân cổ yếm trẻ trung, tôn dáng và nổi bật.', NULL, 210000, 'gói', 'ao3.png', 10, 1),
(72, 'Áo Dài 4 - Thêu Hoa Sen', 'trang_phuc', 'Áo dài họa tiết hoa sen thêu tinh xảo mang nét đẹp truyền thống Việt Nam.', NULL, 220000, 'gói', 'ao4.png', 10, 1),
(73, 'Áo Dài 5 - Cặp Đôi Gia Tiên', 'trang_phuc', 'Set áo dài đồng điệu dành cho cặp đôi chụp ảnh lễ gia tiên và kỷ niệm.', NULL, 260000, 'gói', 'ao5.png', 10, 1),
(74, 'Áo Dài 6 - Pastel Dịu Dàng', 'trang_phuc', 'Tông màu pastel ngọt ngào, nữ tính, phù hợp chụp studio và ngoại cảnh.', NULL, 200000, 'gói', 'ao6.png', 10, 1),
(75, 'Áo Dài 7 - Nhung Sang Trọng', 'trang_phuc', 'Chất liệu nhung cao cấp, lên ảnh đằm thắm và quý phái.', NULL, 240000, 'gói', 'ao7.png', 10, 1),
(76, 'Áo Dài 8 - Nàng Thơ Phố Cổ', 'trang_phuc', 'Phong cách áo dài cổ điển nhẹ nhàng, hợp concept phố cổ và vintage.', NULL, 210000, 'gói', 'ao8.png', 10, 1),
(77, 'Áo Dài 9 - Ren Hoa Tinh Xảo', 'trang_phuc', 'Phối ren hoa thanh thoát, tạo điểm nhấn mềm mại và duyên dáng.', NULL, 230000, 'gói', 'ao9.png', 10, 1),
(78, 'Áo Dài 10 - Tơ Tằm Ánh Kim', 'trang_phuc', 'Chất liệu tơ tằm bắt sáng nhẹ, sang trọng cho tiệc cưới và lễ hỏi.', NULL, 250000, 'gói', 'ao10.png', 10, 1),
(79, 'Áo Dài 11 - Ngọc Trai Thanh Tú', 'trang_phuc', 'Đính kết ngọc trai tinh tế, tôn nét dịu dàng của cô dâu.', NULL, 240000, 'gói', 'ao11.png', 10, 1),
(80, 'Áo Dài 12 - Hoàng Hôn Hiện Đại', 'trang_phuc', 'Form áo dài cách tân hiện đại, dễ di chuyển khi chụp ngoại cảnh.', NULL, 220000, 'gói', 'ao12.png', 10, 1),
(81, 'Hanbok 1 - Hỷ Phục Gấm Trắng', 'trang_phuc', 'Hanbok cặp đôi trắng kem thêu họa tiết bạc trang nhã trong studio.', NULL, 1200000, 'bộ', 'phuc1.png', 1, 1),
(82, 'Hanbok 2 - Ánh Trăng Thuần Khiết', 'trang_phuc', 'Hanbok trắng voan kết hợp vương miện và tua rua tinh tế trên nền tối.', NULL, 1200000, 'bộ', 'phuc2.png', 1, 1),
(83, 'Hanbok 3 - Tím Mộng Nàng Thơ', 'trang_phuc', 'Hanbok cặp đôi sắc hồng phấn và tím nhạt dịu dàng, thanh lịch.', NULL, 1200000, 'bộ', 'phuc3.png', 1, 1),
(84, 'Hanbok 4 - Sương Mai Thanh Tân', 'trang_phuc', 'Hanbok cặp đôi trắng - xanh pastel nhẹ nhàng, tươi sáng.', NULL, 1200000, 'bộ', 'phuc4.png', 1, 1),
(85, 'Hanbok 5 - Đào Phai Giao Duyên', 'trang_phuc', 'Hanbok cặp đôi hồng phấn kem nhạt bên bình hoa mùa xuân.', NULL, 1200000, 'bộ', 'phuc5.png', 1, 1),
(86, 'Hanbok 6 - Xanh Lam Lưu Luyến', 'trang_phuc', 'Hanbok cặp đôi xanh lam thêu bạc thanh thoát cùng nhành hoa xanh.', NULL, 1200000, 'bộ', 'phuc6.png', 1, 1),
(87, 'Hanbok 7 - Họa Tiết Thủy Mặc', 'trang_phuc', 'Hanbok xanh khói hoa văn cổ điển trên nền tranh hoa mẫu đơn.', NULL, 1200000, 'bộ', 'phuc7.png', 1, 1),
(88, 'Hanbok 8 - Khổng Tước Hạc Tiên', 'trang_phuc', 'Hanbok cặp đôi họa tiết chim công và hạc sang trọng, đậm nét cổ phong.', NULL, 1200000, 'bộ', 'phuc8.png', 1, 1),
(89, 'Hanbok 9 - Sơn Thủy Hữu Tình', 'trang_phuc', 'Hanbok cặp đôi thêu hoa hồng pastel giữa núi đồi xanh mát.', NULL, 1200000, 'bộ', 'phuc9.png', 1, 1),
(90, 'Hanbok 10 - Dạ Khúc Huyền Bí', 'trang_phuc', 'Hanbok cặp đôi sắc xám khói và hồng ngọc trai nổi bật trên nền đen studio.', NULL, 1200000, 'bộ', 'phuc10.png', 1, 1),
(91, 'Hanbok 11 - Nắng Mai Kiến Trúc', 'trang_phuc', 'Hanbok hồng phấn tay bồng và áo choàng kem thanh thoát bên cầu thang cong trắng.', NULL, 1200000, 'bộ', 'phuc11.png', 1, 1),
(92, 'Hanbok 12 - Giao Thoa Lịch Lãm', 'trang_phuc', 'Sự kết hợp độc đáo giữa Hanbok cổ truyền và vest tuxedo đen sang trọng.', NULL, 1200000, 'bộ', 'phuc12.png', 1, 1),
(93, 'Hanbok 1 - Palace Corridor', 'goi_chup', 'Gói chụp Hanbok truyền thống dọc hành lang cung điện cổ kính.', NULL, 3500000, 'gói', 'han1.png', 1, 1),
(94, 'Hanbok 2 - Traditional Hanok', 'goi_chup', 'Gói chụp Hanbok cặp đôi thanh lịch trước mái ngói cong nhà cổ Hanok.', NULL, 3500000, 'gói', 'han2.png', 1, 1),
(95, 'Hanbok 3 - Cherry Blossom Palace', 'goi_chup', 'Gói chụp Hanbok cặp đôi sắc xanh - hồng dịu dàng dưới hoa anh đào.', NULL, 3500000, 'gói', 'han3.png', 1, 1),
(96, 'Hanbok 4 - Winter Snow', 'goi_chup', 'Gói chụp Hanbok đỏ thẫm thêu rồng vàng nổi bật dưới tuyết và ô đỏ.', NULL, 3500000, 'gói', 'han4.png', 1, 1),
(97, 'Hanbok 5 - Autumn Village', 'goi_chup', 'Gói chụp Hanbok cặp đôi sắc xám - trắng trang nhã khung cảnh thu.', NULL, 3500000, 'gói', 'han5.png', 1, 1),
(98, 'Hanbok 6 - Royal Pavilion', 'goi_chup', 'Gói chụp Hanbok cặp đôi nắm tay thanh thoát dưới hiên lầu các.', NULL, 3500000, 'gói', 'han6.png', 1, 1),
(99, 'Hanbok 7 - Traditional Wedding Gate', 'goi_chup', 'Gói chụp Hanbok nghi lễ sắc cam - xanh đậm trước cổng gỗ cổ.', NULL, 3500000, 'gói', 'han7.png', 1, 1),
(100, 'Hanbok 8 - Outdoor Garden', 'goi_chup', 'Gói chụp Hanbok cặp đôi sang trọng, tinh tế trong không gian sân vườn.', NULL, 3500000, 'gói', 'han8.png', 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dichvu_kichco`
--

CREATE TABLE `dichvu_kichco` (
  `DVK_Ma` int(11) NOT NULL,
  `DV_Ma` int(11) NOT NULL,
  `DVK_Ten` varchar(20) NOT NULL,
  `DVK_SoLuong` int(11) NOT NULL DEFAULT 0,
  `DVK_DangHoatDong` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `dichvu_kichco`
--

INSERT INTO `dichvu_kichco` (`DVK_Ma`, `DV_Ma`, `DVK_Ten`, `DVK_SoLuong`, `DVK_DangHoatDong`) VALUES
(1, 17, 'S', 10, 1),
(2, 18, 'S', 10, 1),
(3, 19, 'S', 10, 1),
(4, 20, 'S', 10, 1),
(5, 21, 'S', 10, 1),
(6, 22, 'S', 10, 1),
(7, 23, 'S', 10, 1),
(8, 24, 'S', 10, 1),
(9, 17, 'M', 10, 1),
(10, 18, 'M', 10, 1),
(11, 19, 'M', 10, 1),
(12, 20, 'M', 10, 1),
(13, 21, 'M', 10, 1),
(14, 22, 'M', 10, 1),
(15, 23, 'M', 10, 1),
(16, 24, 'M', 10, 1),
(17, 17, 'L', 10, 1),
(18, 18, 'L', 10, 1),
(19, 19, 'L', 10, 1),
(20, 20, 'L', 10, 1),
(21, 21, 'L', 10, 1),
(22, 22, 'L', 10, 1),
(23, 23, 'L', 10, 1),
(24, 24, 'L', 10, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dondatlich`
--

CREATE TABLE `dondatlich` (
  `DDL_Ma` int(11) NOT NULL,
  `ND_Ma` int(11) NOT NULL,
  `DDL_TongTien` decimal(12,0) NOT NULL,
  `DDL_MaGiamGia` varchar(50) DEFAULT NULL,
  `DDL_SoTienGiam` decimal(12,2) NOT NULL DEFAULT 0.00,
  `DDL_TienCoc` decimal(12,0) NOT NULL DEFAULT 0,
  `DDL_TrangThai` varchar(20) NOT NULL DEFAULT 'Chờ duyệt',
  `NV_Ma` int(11) DEFAULT NULL,
  `DDL_ThoiDiemXuLy` datetime DEFAULT NULL,
  `DDL_LyDoTuChoi` varchar(255) DEFAULT NULL,
  `DDL_NgayTao` datetime NOT NULL DEFAULT current_timestamp(),
  `DDL_GhiChu` text DEFAULT NULL,
  `DDL_TenNguoiThue` varchar(100) DEFAULT NULL,
  `DDL_SDTNguoiThue` varchar(15) DEFAULT NULL,
  `DDL_CCCD` varchar(12) DEFAULT NULL,
  `DDL_DiaChi` varchar(255) DEFAULT NULL,
  `DDL_ThoiDiemHuy` datetime DEFAULT NULL,
  `DDL_LyDoHuy` varchar(255) DEFAULT NULL,
  `DDL_TrangThaiHoanTien` varchar(30) NOT NULL DEFAULT 'Không',
  `DDL_SoTienHoan` decimal(12,0) DEFAULT NULL,
  `DDL_NganHangHoan` varchar(20) DEFAULT NULL,
  `DDL_SoTKHoan` varchar(30) DEFAULT NULL,
  `DDL_ChuTKHoan` varchar(100) DEFAULT NULL,
  `DDL_ThongTinHoan` varchar(255) DEFAULT NULL,
  `NV_MaHoanTien` int(11) DEFAULT NULL,
  `DDL_ThoiDiemHoanTien` datetime DEFAULT NULL,
  `NV_MaHoanThanh` int(11) DEFAULT NULL,
  `DDL_ThoiDiemHoanThanh` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `dondatlich`
--

INSERT INTO `dondatlich` (`DDL_Ma`, `ND_Ma`, `DDL_TongTien`, `DDL_MaGiamGia`, `DDL_SoTienGiam`, `DDL_TienCoc`, `DDL_TrangThai`, `NV_Ma`, `DDL_ThoiDiemXuLy`, `DDL_LyDoTuChoi`, `DDL_NgayTao`, `DDL_GhiChu`, `DDL_TenNguoiThue`, `DDL_SDTNguoiThue`, `DDL_CCCD`, `DDL_DiaChi`, `DDL_ThoiDiemHuy`, `DDL_LyDoHuy`, `DDL_TrangThaiHoanTien`, `DDL_SoTienHoan`, `DDL_NganHangHoan`, `DDL_SoTKHoan`, `DDL_ChuTKHoan`, `DDL_ThongTinHoan`, `NV_MaHoanTien`, `DDL_ThoiDiemHoanTien`, `NV_MaHoanThanh`, `DDL_ThoiDiemHoanThanh`) VALUES
(1, 4, 380000, NULL, 0.00, 0, 'Hoàn thành', 1, '2026-10-27 17:55:35', NULL, '2026-10-20 12:51:16', 'HIHI', NULL, NULL, NULL, NULL, NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-10-27 18:23:21'),
(2, 4, 350000, NULL, 0.00, 0, 'Hoàn thành', 1, '2026-10-27 18:22:25', NULL, '2026-10-20 12:54:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-10-27 18:23:18'),
(3, 4, 900000, NULL, 0.00, 0, 'Đã hủy', NULL, NULL, NULL, '2026-10-20 15:31:57', NULL, NULL, NULL, NULL, NULL, '2026-10-20 15:32:07', NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 4, 450000, NULL, 0.00, 0, 'Hoàn thành', 1, '2026-10-27 18:22:22', NULL, '2026-10-24 09:50:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-10-27 18:23:15'),
(5, 4, 450000, NULL, 0.00, 0, 'Đã hủy', NULL, NULL, NULL, '2026-10-24 09:55:14', NULL, NULL, NULL, NULL, NULL, '2026-10-24 09:58:58', NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 4, 200000, NULL, 0.00, 0, 'Hoàn thành', 1, '2026-10-27 17:54:40', NULL, '2026-10-26 04:21:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-10-27 17:55:31'),
(7, 4, 380000, NULL, 0.00, 0, 'Hoàn thành', 1, '2026-10-27 18:22:18', NULL, '2026-10-27 12:16:09', NULL, 'LÊ BĂNG', '0943485421', NULL, 'haha', NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-10-27 18:23:13'),
(8, 4, 2760000, NULL, 0.00, 0, 'Hoàn thành', 1, '2026-10-27 19:33:33', NULL, '2026-10-27 13:10:33', NULL, 'LÊ BĂNG', '0943485421', NULL, 'haha', NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-10-27 23:15:16'),
(9, 4, 450000, NULL, 0.00, 0, 'Đã từ chối', 1, '2026-10-27 23:15:10', 'bận', '2026-10-27 16:43:26', NULL, 'LÊ BĂNG', '0943485421', NULL, 'haha', NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 4, 320000, NULL, 0.00, 0, 'Đã duyệt', 1, '2026-10-27 23:15:19', NULL, '2026-10-27 16:58:10', NULL, 'LÊ BĂNG', '0943485421', NULL, 'haha', NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 5, 900000, 'VIBE10', 100000.00, 0, 'Đã hủy', NULL, NULL, NULL, '2026-10-30 23:34:11', 'bdsm', 'Hao Le', '0948215946', NULL, 'ấp trường', '2026-10-30 23:34:24', NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 4, 2700000, NULL, 0.00, 0, 'Đã duyệt', 1, '2026-11-02 20:34:05', NULL, '2026-10-31 18:34:08', NULL, 'LÊ BĂNG', '0943485421', NULL, 'haha', NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 4, 760000, NULL, 0.00, 0, 'Đã duyệt', 1, '2026-11-02 20:34:02', NULL, '2026-11-02 19:52:12', NULL, 'LÊ BĂNG', '0943485421', NULL, 'haha', NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 4, 380000, NULL, 0.00, 0, 'Đã hủy', 1, '2026-11-13 21:49:51', NULL, '2026-11-02 20:10:33', NULL, 'LÊ BĂNG', '0943485421', NULL, 'haha', '2026-11-13 21:49:51', 'thấy ghét', 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 4, 684000, 'VIBE10', 76000.00, 0, 'Hoàn thành', 1, '2026-11-07 15:25:17', NULL, '2026-11-07 15:24:19', NULL, 'LÊ BĂNG', '0943485421', NULL, 'haha', NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-11-11 15:13:14'),
(16, 4, 380000, NULL, 0.00, 0, 'Hoàn thành', 1, '2026-11-09 15:04:31', NULL, '2026-11-09 13:10:59', NULL, 'LÊ BĂNG', '0943485421', NULL, 'haha', NULL, NULL, 'Không', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-11-11 15:13:08'),
(21, 5, 1215000, 'VIBE10', 135000.00, 0, 'Đã hủy', NULL, NULL, NULL, '2026-11-13 21:46:13', NULL, 'Hao Le', '0948215946', '098765432', 'ấp trường', '2026-11-13 21:46:19', NULL, 'Đã hoàn tiền', 1214001, '970436', '65754745754754', 'hao', 'da', 1, '2026-11-13 22:04:11', NULL, NULL),
(22, 5, 450000, NULL, 0.00, 0, 'Đã từ chối', 1, '2026-11-13 21:51:28', 'yeu ck', '2026-11-13 21:50:59', NULL, 'Hao Le', '0948215946', '234567891', 'ấp trường', NULL, NULL, 'Đã hoàn tiền', 449001, '970436', '65754745754754', 'hao', 'da', 1, '2026-11-13 21:57:59', NULL, NULL),
(23, 5, 8440000, 'VIBE10', 200000.00, 0, 'Đã từ chối', 1, '2026-11-13 22:09:13', 'ghet', '2026-11-13 22:08:31', NULL, 'Hao Le', '0948215946', '234325467', 'ấp trường', NULL, NULL, 'Chờ hoàn tiền', 8440000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `GH_Ma` int(11) NOT NULL,
  `ND_Ma` int(11) NOT NULL,
  `DV_Ma` int(11) NOT NULL,
  `GH_SoLuong` int(11) NOT NULL DEFAULT 1,
  `GH_KichCo` varchar(20) DEFAULT NULL,
  `GH_BatDau` datetime NOT NULL,
  `GH_KetThuc` datetime NOT NULL,
  `GH_NgayTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinhanh`
--

CREATE TABLE `hinhanh` (
  `HA_Ma` int(11) NOT NULL,
  `DV_Ma` int(11) NOT NULL,
  `HA_DuongDan` varchar(255) NOT NULL,
  `HA_NgayTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lichthue`
--

CREATE TABLE `lichthue` (
  `LT_Ma` int(11) NOT NULL,
  `DV_Ma` int(11) NOT NULL,
  `LT_BatDau` datetime NOT NULL,
  `LT_KetThuc` datetime NOT NULL,
  `LT_TrangThai` varchar(20) NOT NULL DEFAULT 'trống',
  `LT_HetHanGiu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `lichthue`
--

INSERT INTO `lichthue` (`LT_Ma`, `DV_Ma`, `LT_BatDau`, `LT_KetThuc`, `LT_TrangThai`, `LT_HetHanGiu`) VALUES
(57, 9, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(58, 9, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(59, 9, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(60, 9, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(61, 9, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(62, 9, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(63, 9, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(64, 10, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(65, 10, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(66, 10, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(67, 10, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(68, 10, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(69, 10, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(70, 10, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(71, 11, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(72, 11, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(73, 11, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(74, 11, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(75, 11, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(76, 11, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(77, 11, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(78, 12, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(79, 12, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(80, 12, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(81, 12, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(82, 12, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(83, 12, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(84, 12, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(85, 13, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(86, 13, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(87, 13, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(88, 13, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(89, 13, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(90, 13, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(91, 13, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(92, 14, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(93, 14, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(94, 14, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(95, 14, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(96, 14, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(97, 14, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(98, 14, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(99, 15, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(100, 15, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(101, 15, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(102, 15, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(103, 15, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(104, 15, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(105, 15, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(106, 16, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(107, 16, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(108, 16, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(109, 16, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(110, 16, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(111, 16, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(112, 16, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(113, 1, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(114, 1, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(115, 1, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(116, 1, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(117, 1, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(118, 1, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(119, 1, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(120, 2, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(121, 2, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(122, 2, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(123, 2, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(124, 2, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(125, 2, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(126, 2, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(127, 3, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(128, 3, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(129, 3, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(130, 3, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(131, 3, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(132, 3, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(133, 3, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(134, 4, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(135, 4, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(136, 4, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(137, 4, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(138, 4, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(139, 4, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(140, 4, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(141, 5, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(142, 5, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(143, 5, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(144, 5, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(145, 5, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(146, 5, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(147, 5, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(148, 6, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(149, 6, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(150, 6, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(151, 6, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(152, 6, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(153, 6, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(154, 6, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(155, 7, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(156, 7, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(157, 7, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(158, 7, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(159, 7, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(160, 7, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(161, 7, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(162, 8, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(163, 8, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(164, 8, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(165, 8, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(166, 8, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(167, 8, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(168, 8, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(169, 17, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(170, 17, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(171, 17, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(172, 17, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(173, 17, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(174, 17, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(175, 17, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(176, 18, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(177, 18, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(178, 18, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(179, 18, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(180, 18, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(181, 18, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(182, 18, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(183, 19, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(184, 19, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(185, 19, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(186, 19, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(187, 19, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(188, 19, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(189, 19, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(190, 20, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(191, 20, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(192, 20, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(193, 20, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(194, 20, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(195, 20, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(196, 20, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(197, 21, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(198, 21, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(199, 21, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(200, 21, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(201, 21, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(202, 21, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(203, 21, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(204, 22, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(205, 22, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(206, 22, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(207, 22, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(208, 22, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(209, 22, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(210, 22, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(211, 23, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(212, 23, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(213, 23, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(214, 23, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(215, 23, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(216, 23, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(217, 23, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL),
(218, 24, '2026-10-21 08:00:00', '2026-10-21 20:00:00', 'trống', NULL),
(219, 24, '2026-10-22 08:00:00', '2026-10-22 20:00:00', 'trống', NULL),
(220, 24, '2026-10-23 08:00:00', '2026-10-23 20:00:00', 'trống', NULL),
(221, 24, '2026-10-24 08:00:00', '2026-10-24 20:00:00', 'trống', NULL),
(222, 24, '2026-10-25 08:00:00', '2026-10-25 20:00:00', 'trống', NULL),
(223, 24, '2026-10-26 08:00:00', '2026-10-26 20:00:00', 'trống', NULL),
(224, 24, '2026-10-27 08:00:00', '2026-10-27 20:00:00', 'trống', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loai_nguoi_dung`
--

CREATE TABLE `loai_nguoi_dung` (
  `LND_Ma` int(11) NOT NULL,
  `LND_Ten` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `loai_nguoi_dung`
--

INSERT INTO `loai_nguoi_dung` (`LND_Ma`, `LND_Ten`) VALUES
(1, 'admin'),
(3, 'khachhang'),
(2, 'nhanvien');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ma_giam_gia`
--

CREATE TABLE `ma_giam_gia` (
  `MG_Ma` int(11) NOT NULL,
  `MG_Code` varchar(50) NOT NULL,
  `MG_MoTa` varchar(255) DEFAULT NULL,
  `MG_Loai` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `MG_GiaTri` decimal(12,2) NOT NULL COMMENT 'Percent: 0-100, Fixed: VND',
  `MG_DonToiThieu` decimal(12,2) NOT NULL DEFAULT 0.00,
  `MG_GiamToiDa` decimal(12,2) DEFAULT NULL COMMENT 'Chỉ áp dụng cho percent',
  `MG_SoLuong` int(11) NOT NULL DEFAULT 0 COMMENT '0 = không giới hạn',
  `MG_DaDung` int(11) NOT NULL DEFAULT 0,
  `MG_NgayBatDau` datetime NOT NULL DEFAULT current_timestamp(),
  `MG_NgayKetThuc` datetime DEFAULT NULL,
  `MG_DangHoatDong` tinyint(1) NOT NULL DEFAULT 1,
  `MG_NgayTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ma_giam_gia`
--

INSERT INTO `ma_giam_gia` (`MG_Ma`, `MG_Code`, `MG_MoTa`, `MG_Loai`, `MG_GiaTri`, `MG_DonToiThieu`, `MG_GiamToiDa`, `MG_SoLuong`, `MG_DaDung`, `MG_NgayBatDau`, `MG_NgayKetThuc`, `MG_DangHoatDong`, `MG_NgayTao`) VALUES
(1, 'VIBE10', 'Giảm 10% cho đơn từ 500.000đ', 'percent', 10.00, 500000.00, 200000.00, 100, 2, '2025-01-01 00:00:00', '2030-12-31 23:59:59', 1, '2026-10-30 21:59:58'),
(2, 'WELCOME50', 'Giảm 50.000đ cho đơn từ 300.000đ', 'fixed', 50000.00, 300000.00, NULL, 50, 0, '2025-01-01 00:00:00', NULL, 1, '2026-10-30 21:59:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
--

CREATE TABLE `nguoidung` (
  `ND_Ma` int(11) NOT NULL,
  `ND_Ten` varchar(100) NOT NULL,
  `ND_GioiTinh` varchar(10) DEFAULT NULL,
  `ND_NgaySinh` date DEFAULT NULL,
  `ND_SDT` varchar(15) DEFAULT NULL,
  `ND_Email` varchar(100) NOT NULL,
  `ND_MatKhau` varchar(255) NOT NULL,
  `ND_TrangThai` tinyint(1) NOT NULL DEFAULT 1,
  `ND_NgayTao` datetime NOT NULL DEFAULT current_timestamp(),
  `LND_Ma` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoidung`
--

INSERT INTO `nguoidung` (`ND_Ma`, `ND_Ten`, `ND_GioiTinh`, `ND_NgaySinh`, `ND_SDT`, `ND_Email`, `ND_MatKhau`, `ND_TrangThai`, `ND_NgayTao`, `LND_Ma`) VALUES
(1, 'Quản trị viên', NULL, NULL, NULL, 'admin@studio.local', '$2y$10$ty.f5jb3Y4PH3M2GgsBrDOFQz/cxEHhxLCA48kvQAg0MrrREkfSu.', 1, '2026-10-20 11:46:32', 1),
(2, 'Nhân viên mẫu', NULL, NULL, NULL, 'nhanvien@studio.local', '$2y$10$oNrCu9BRHOMCQUVpwZd1BOtZrIrs/Z.7e4kHubUI2Z/0.E6MkNhg.', 1, '2026-10-20 11:46:32', 2),
(3, 'Khách hàng mẫu', NULL, NULL, NULL, 'khach@studio.local', '$2y$10$5C2Z/Fn58f9I5SbMn44gfObkoUCmRcDatdG7N.x4wHw/i.SVsyr3K', 0, '2026-10-20 11:46:32', 3),
(4, 'LÊ BĂNG', NULL, NULL, NULL, 'bang@123.com', '$2y$10$nEuKh4u8uo81vXmmJmtY/.c0C2kmHwHdL/eTPEPyt579ztR3Ej0NO', 1, '2026-10-20 12:11:10', 3),
(5, 'Hao Le', NULL, NULL, NULL, 'haob2404855@student.ctu.edu.vn', '$2y$10$pZplQkvhyn8xnF5g7LSAhuDvt8aAu67jyR06HZZuM5g3U4tVPGG/W', 1, '2026-10-30 21:55:05', 3),
(6, 'Thu Anh', NULL, NULL, NULL, 'truongphamanhthu2022ht@gmail.com', '$2y$10$iJjmOZX/8wGWC26dnTnjION/7ATW7b5cnvDtctjTJT9bIRPY4affq', 1, '2026-11-02 07:19:20', 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhanvien`
--

CREATE TABLE `nhanvien` (
  `NV_Ma` int(11) NOT NULL,
  `ND_Ma` int(11) NOT NULL,
  `NV_MaNhanVien` varchar(20) NOT NULL,
  `NV_ChucVu` varchar(50) DEFAULT NULL,
  `NV_NgayVaoLam` date DEFAULT NULL,
  `NV_TrangThai` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhanvien`
--

INSERT INTO `nhanvien` (`NV_Ma`, `ND_Ma`, `NV_MaNhanVien`, `NV_ChucVu`, `NV_NgayVaoLam`, `NV_TrangThai`) VALUES
(1, 2, 'NV001', 'Nhân viên studio', '2026-10-20', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phongchup`
--

CREATE TABLE `phongchup` (
  `DV_Ma` int(11) NOT NULL,
  `PC_SucChua` int(11) NOT NULL,
  `PC_TrangThai` varchar(20) NOT NULL DEFAULT 'Hoạt động'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phongchup`
--

INSERT INTO `phongchup` (`DV_Ma`, `PC_SucChua`, `PC_TrangThai`) VALUES
(9, 6, 'Hoạt động'),
(10, 6, 'Hoạt động'),
(11, 6, 'Hoạt động'),
(12, 6, 'Hoạt động'),
(13, 6, 'Hoạt động'),
(14, 6, 'Hoạt động'),
(15, 6, 'Hoạt động'),
(16, 6, 'Hoạt động');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phukien`
--

CREATE TABLE `phukien` (
  `DV_Ma` int(11) NOT NULL,
  `PK_Loai` varchar(100) DEFAULT NULL,
  `PK_TinhTrang` varchar(30) NOT NULL DEFAULT 'Tốt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phukien`
--

INSERT INTO `phukien` (`DV_Ma`, `PK_Loai`, `PK_TinhTrang`) VALUES
(25, 'Chân đèn studio', 'Tốt'),
(26, 'Chân đèn studio', 'Tốt'),
(27, 'Chân đèn studio', 'Tốt'),
(28, 'Chân đèn studio', 'Tốt'),
(29, 'Chân máy ảnh', 'Tốt'),
(30, 'Chân máy ảnh', 'Tốt'),
(31, 'Tấm hắt sáng', 'Tốt'),
(32, 'Tấm hắt sáng', 'Tốt'),
(33, 'Len máy ảnh', 'Tốt'),
(34, 'Len máy ảnh', 'Tốt'),
(35, 'Len máy ảnh', 'Tốt'),
(36, 'Len máy ảnh', 'Tốt');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanhtoan`
--

CREATE TABLE `thanhtoan` (
  `TT_Ma` int(11) NOT NULL,
  `DDL_Ma` int(11) NOT NULL,
  `TT_Loai` varchar(20) NOT NULL,
  `TT_SoTien` decimal(12,0) NOT NULL,
  `TT_PhuongThuc` varchar(30) NOT NULL,
  `TT_MaThamChieu` varchar(100) DEFAULT NULL,
  `TT_TrangThai` varchar(20) NOT NULL DEFAULT 'Chờ',
  `TT_ThoiDiem` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thanhtoan`
--

INSERT INTO `thanhtoan` (`TT_Ma`, `DDL_Ma`, `TT_Loai`, `TT_SoTien`, `TT_PhuongThuc`, `TT_MaThamChieu`, `TT_TrangThai`, `TT_ThoiDiem`) VALUES
(1, 1, 'Thanh toán đủ', 380000, 'Chuyển khoản QR giả lập', 'QR-261020125116-1', 'Thành công', '2026-10-20 12:51:16'),
(2, 2, 'Thanh toán đủ', 350000, 'Tiền mặt', NULL, 'Thành công', '2026-10-20 12:54:13'),
(3, 3, 'Thanh toán đủ', 900000, 'Chuyển khoản QR giả lập', 'QR-261020153157-3', 'Thành công', '2026-10-20 15:31:57'),
(4, 4, 'Thanh toán đủ', 450000, 'Tiền mặt', NULL, 'Thành công', '2026-10-24 09:50:47'),
(5, 5, 'Thanh toán đủ', 450000, 'Tiền mặt', NULL, 'Chờ', '2026-10-24 09:55:14'),
(6, 6, 'Thanh toán đủ', 200000, 'Chuyển khoản QR giả lập', 'QR-261026042121-6', 'Thành công', '2026-10-26 04:21:21'),
(7, 7, 'Thanh toán đủ', 380000, 'Tiền mặt', NULL, 'Thành công', '2026-10-27 12:16:09'),
(8, 8, 'Thanh toán đủ', 2760000, 'Tiền mặt', NULL, 'Thành công', '2026-10-27 13:10:33'),
(9, 9, 'Thanh toán đủ', 450000, 'Chuyển khoản QR giả lập', 'QR-261027164326-9', 'Thành công', '2026-10-27 16:43:26'),
(10, 10, 'Thanh toán đủ', 320000, 'Chuyển khoản QR giả lập', 'QR-261027165810-10', 'Thành công', '2026-10-27 16:58:10'),
(11, 11, 'Thanh toán đủ', 900000, 'Tiền mặt', NULL, 'Chờ', '2026-10-30 23:34:11'),
(12, 12, 'Thanh toán đủ', 2700000, 'Tiền mặt', NULL, 'Chờ', '2026-10-31 18:34:08'),
(13, 13, 'Thanh toán đủ', 760000, 'Tiền mặt', NULL, 'Chờ', '2026-11-02 19:52:12'),
(14, 14, 'Thanh toán đủ', 380000, 'Tiền mặt', NULL, 'Chờ', '2026-11-02 20:10:33'),
(15, 15, 'Thanh toán đủ', 684000, 'Tiền mặt', NULL, 'Thành công', '2026-11-07 15:24:19'),
(16, 16, 'Thanh toán đủ', 380000, 'Tiền mặt', NULL, 'Thành công', '2026-11-09 13:10:59'),
(21, 21, 'Thanh toán đủ', 1215000, 'Chuyển khoản QR giả lập', 'QR-261113214613-21', 'Thành công', '2026-11-13 21:46:13'),
(22, 22, 'Thanh toán đủ', 450000, 'Chuyển khoản QR giả lập', 'QR-261113215059-22', 'Thành công', '2026-11-13 21:50:59'),
(23, 22, 'Hoàn tiền', 449001, 'Chuyển khoản', 'RF-22-261113215759', 'Thành công', '2026-11-13 21:57:59'),
(24, 21, 'Hoàn tiền', 1214001, 'Chuyển khoản', 'RF-21-261113220411', 'Thành công', '2026-11-13 22:04:11'),
(25, 23, 'Thanh toán đủ', 8440000, 'Chuyển khoản QR giả lập', 'QR-261113220831-23', 'Thành công', '2026-11-13 22:08:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thietbi`
--

CREATE TABLE `thietbi` (
  `DV_Ma` int(11) NOT NULL,
  `TB_Loai` varchar(50) DEFAULT NULL,
  `TB_TinhTrang` varchar(30) NOT NULL DEFAULT 'Tốt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thietbi`
--

INSERT INTO `thietbi` (`DV_Ma`, `TB_Loai`, `TB_TinhTrang`) VALUES
(1, 'Máy ảnh', 'Tốt'),
(2, 'Máy ảnh', 'Tốt'),
(3, 'Máy ảnh', 'Tốt'),
(4, 'Máy ảnh', 'Tốt'),
(5, 'Máy ảnh', 'Tốt'),
(6, 'Máy ảnh', 'Tốt'),
(7, 'Máy ảnh', 'Tốt'),
(8, 'Máy ảnh', 'Tốt');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `trangphuc`
--

CREATE TABLE `trangphuc` (
  `DV_Ma` int(11) NOT NULL,
  `TP_KichCo` varchar(20) DEFAULT NULL,
  `TP_TinhTrang` varchar(30) NOT NULL DEFAULT 'Tốt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `trangphuc`
--

INSERT INTO `trangphuc` (`DV_Ma`, `TP_KichCo`, `TP_TinhTrang`) VALUES
(17, 'S, M, L', 'Tốt'),
(18, 'S, M, L', 'Tốt'),
(19, 'S, M, L', 'Tốt'),
(20, 'S, M, L', 'Tốt'),
(21, 'S, M, L', 'Tốt'),
(22, 'S, M, L', 'Tốt'),
(23, 'S, M, L', 'Tốt'),
(24, 'S, M, L', 'Tốt'),
(69, 'S, M, L', 'Tốt'),
(70, 'S, M, L', 'Tốt'),
(71, 'S, M, L', 'Tốt'),
(72, 'S, M, L', 'Tốt'),
(73, 'S, M, L', 'Tốt'),
(74, 'S, M, L', 'Tốt'),
(75, 'S, M, L', 'Tốt'),
(76, 'S, M, L', 'Tốt'),
(77, 'S, M, L', 'Tốt'),
(78, 'S, M, L', 'Tốt'),
(79, 'S, M, L', 'Tốt'),
(80, 'S, M, L', 'Tốt');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `baocao_huhong`
--
ALTER TABLE `baocao_huhong`
  ADD PRIMARY KEY (`BCHH_Ma`),
  ADD KEY `ix_bchh_trangthai_ngay` (`BCHH_TrangThai`,`BCHH_NgayBao`),
  ADD KEY `ix_bchh_dv` (`DV_Ma`),
  ADD KEY `fk_bchh_nv` (`NV_Ma`);

--
-- Chỉ mục cho bảng `cau_hinh`
--
ALTER TABLE `cau_hinh`
  ADD PRIMARY KEY (`CH_Khoa`);

--
-- Chỉ mục cho bảng `chat_hoi_thoai`
--
ALTER TABLE `chat_hoi_thoai`
  ADD PRIMARY KEY (`CHT_Ma`),
  ADD KEY `idx_cht_nguoidung` (`ND_Ma`),
  ADD KEY `idx_cht_nhanvien` (`NV_Ma`),
  ADD KEY `idx_cht_trangthai` (`CHT_TrangThai`,`CHT_NgayCapNhat`);

--
-- Chỉ mục cho bảng `chat_tin_nhan`
--
ALTER TABLE `chat_tin_nhan`
  ADD PRIMARY KEY (`CTN_Ma`),
  ADD KEY `idx_ctn_hoithoai` (`CHT_Ma`,`CTN_Ma`),
  ADD KEY `idx_ctn_nguoidung` (`ND_Ma`),
  ADD KEY `idx_ctn_nhanvien` (`NV_Ma`);

--
-- Chỉ mục cho bảng `chitietdon`
--
ALTER TABLE `chitietdon`
  ADD PRIMARY KEY (`CTD_Ma`),
  ADD KEY `ix_ctd_trung_lich` (`DV_Ma`,`CTD_BatDau`,`CTD_KetThuc`),
  ADD KEY `fk_ctd_ddl` (`DDL_Ma`);

--
-- Chỉ mục cho bảng `chitietdon_bophan`
--
ALTER TABLE `chitietdon_bophan`
  ADD PRIMARY KEY (`CTD_Ma`,`CTDBP_Ten`);

--
-- Chỉ mục cho bảng `dangnhap_thatbai`
--
ALTER TABLE `dangnhap_thatbai`
  ADD PRIMARY KEY (`DNTB_Ma`),
  ADD KEY `idx_email_tg` (`DNTB_Email`,`DNTB_ThoiGian`),
  ADD KEY `idx_ip_tg` (`DNTB_IP`,`DNTB_ThoiGian`);

--
-- Chỉ mục cho bảng `danhgia`
--
ALTER TABLE `danhgia`
  ADD PRIMARY KEY (`DG_Ma`),
  ADD UNIQUE KEY `uq_dg_don_dv` (`DDL_Ma`,`DV_Ma`),
  ADD KEY `fk_dg_nd` (`ND_Ma`),
  ADD KEY `fk_dg_dv` (`DV_Ma`);

--
-- Chỉ mục cho bảng `dichvu`
--
ALTER TABLE `dichvu`
  ADD PRIMARY KEY (`DV_Ma`),
  ADD KEY `ix_dv_loai` (`DV_Loai`,`DV_DangBan`);

--
-- Chỉ mục cho bảng `dichvu_kichco`
--
ALTER TABLE `dichvu_kichco`
  ADD PRIMARY KEY (`DVK_Ma`),
  ADD UNIQUE KEY `uq_dv_kichco` (`DV_Ma`,`DVK_Ten`),
  ADD KEY `ix_dvk_dv` (`DV_Ma`);

--
-- Chỉ mục cho bảng `dondatlich`
--
ALTER TABLE `dondatlich`
  ADD PRIMARY KEY (`DDL_Ma`),
  ADD KEY `ix_ddl_nd` (`ND_Ma`),
  ADD KEY `ix_ddl_trangthai` (`DDL_TrangThai`),
  ADD KEY `fk_ddl_nv` (`NV_Ma`),
  ADD KEY `fk_ddl_nv_ht` (`NV_MaHoanThanh`),
  ADD KEY `ix_ddl_hoantien` (`DDL_TrangThaiHoanTien`),
  ADD KEY `ix_ddl_nvhoantien` (`NV_MaHoanTien`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`GH_Ma`),
  ADD KEY `ix_gh_nd` (`ND_Ma`),
  ADD KEY `fk_gh_dv` (`DV_Ma`);

--
-- Chỉ mục cho bảng `hinhanh`
--
ALTER TABLE `hinhanh`
  ADD PRIMARY KEY (`HA_Ma`),
  ADD KEY `fk_ha_dv` (`DV_Ma`);

--
-- Chỉ mục cho bảng `lichthue`
--
ALTER TABLE `lichthue`
  ADD PRIMARY KEY (`LT_Ma`),
  ADD UNIQUE KEY `uq_lt_dv_batdau` (`DV_Ma`,`LT_BatDau`);

--
-- Chỉ mục cho bảng `loai_nguoi_dung`
--
ALTER TABLE `loai_nguoi_dung`
  ADD PRIMARY KEY (`LND_Ma`),
  ADD UNIQUE KEY `uq_lnd_ten` (`LND_Ten`);

--
-- Chỉ mục cho bảng `ma_giam_gia`
--
ALTER TABLE `ma_giam_gia`
  ADD PRIMARY KEY (`MG_Ma`),
  ADD UNIQUE KEY `MG_Code` (`MG_Code`),
  ADD KEY `idx_code` (`MG_Code`);

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`ND_Ma`),
  ADD UNIQUE KEY `uq_nd_email` (`ND_Email`),
  ADD KEY `fk_nd_lnd` (`LND_Ma`);

--
-- Chỉ mục cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD PRIMARY KEY (`NV_Ma`),
  ADD UNIQUE KEY `uq_nv_nd` (`ND_Ma`),
  ADD UNIQUE KEY `uq_nv_ma` (`NV_MaNhanVien`);

--
-- Chỉ mục cho bảng `phongchup`
--
ALTER TABLE `phongchup`
  ADD PRIMARY KEY (`DV_Ma`);

--
-- Chỉ mục cho bảng `phukien`
--
ALTER TABLE `phukien`
  ADD PRIMARY KEY (`DV_Ma`);

--
-- Chỉ mục cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD PRIMARY KEY (`TT_Ma`),
  ADD KEY `fk_tt_ddl` (`DDL_Ma`);

--
-- Chỉ mục cho bảng `thietbi`
--
ALTER TABLE `thietbi`
  ADD PRIMARY KEY (`DV_Ma`);

--
-- Chỉ mục cho bảng `trangphuc`
--
ALTER TABLE `trangphuc`
  ADD PRIMARY KEY (`DV_Ma`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `baocao_huhong`
--
ALTER TABLE `baocao_huhong`
  MODIFY `BCHH_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `chat_hoi_thoai`
--
ALTER TABLE `chat_hoi_thoai`
  MODIFY `CHT_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `chat_tin_nhan`
--
ALTER TABLE `chat_tin_nhan`
  MODIFY `CTN_Ma` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT cho bảng `chitietdon`
--
ALTER TABLE `chitietdon`
  MODIFY `CTD_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `dangnhap_thatbai`
--
ALTER TABLE `dangnhap_thatbai`
  MODIFY `DNTB_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `danhgia`
--
ALTER TABLE `danhgia`
  MODIFY `DG_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `dichvu`
--
ALTER TABLE `dichvu`
  MODIFY `DV_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT cho bảng `dichvu_kichco`
--
ALTER TABLE `dichvu_kichco`
  MODIFY `DVK_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `dondatlich`
--
ALTER TABLE `dondatlich`
  MODIFY `DDL_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `giohang`
--
ALTER TABLE `giohang`
  MODIFY `GH_Ma` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `hinhanh`
--
ALTER TABLE `hinhanh`
  MODIFY `HA_Ma` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `lichthue`
--
ALTER TABLE `lichthue`
  MODIFY `LT_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT cho bảng `loai_nguoi_dung`
--
ALTER TABLE `loai_nguoi_dung`
  MODIFY `LND_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `ma_giam_gia`
--
ALTER TABLE `ma_giam_gia`
  MODIFY `MG_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `ND_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  MODIFY `NV_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `TT_Ma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `baocao_huhong`
--
ALTER TABLE `baocao_huhong`
  ADD CONSTRAINT `fk_bchh_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`),
  ADD CONSTRAINT `fk_bchh_nv` FOREIGN KEY (`NV_Ma`) REFERENCES `nhanvien` (`NV_Ma`);

--
-- Các ràng buộc cho bảng `chat_hoi_thoai`
--
ALTER TABLE `chat_hoi_thoai`
  ADD CONSTRAINT `fk_cht_nguoidung` FOREIGN KEY (`ND_Ma`) REFERENCES `nguoidung` (`ND_Ma`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cht_nhanvien` FOREIGN KEY (`NV_Ma`) REFERENCES `nhanvien` (`NV_Ma`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `chat_tin_nhan`
--
ALTER TABLE `chat_tin_nhan`
  ADD CONSTRAINT `fk_ctn_hoithoai` FOREIGN KEY (`CHT_Ma`) REFERENCES `chat_hoi_thoai` (`CHT_Ma`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ctn_nguoidung` FOREIGN KEY (`ND_Ma`) REFERENCES `nguoidung` (`ND_Ma`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ctn_nhanvien` FOREIGN KEY (`NV_Ma`) REFERENCES `nhanvien` (`NV_Ma`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `chitietdon`
--
ALTER TABLE `chitietdon`
  ADD CONSTRAINT `fk_ctd_ddl` FOREIGN KEY (`DDL_Ma`) REFERENCES `dondatlich` (`DDL_Ma`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ctd_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`);

--
-- Các ràng buộc cho bảng `chitietdon_bophan`
--
ALTER TABLE `chitietdon_bophan`
  ADD CONSTRAINT `fk_ctdbp_ctd` FOREIGN KEY (`CTD_Ma`) REFERENCES `chitietdon` (`CTD_Ma`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `danhgia`
--
ALTER TABLE `danhgia`
  ADD CONSTRAINT `fk_dg_ddl` FOREIGN KEY (`DDL_Ma`) REFERENCES `dondatlich` (`DDL_Ma`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dg_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`),
  ADD CONSTRAINT `fk_dg_nd` FOREIGN KEY (`ND_Ma`) REFERENCES `nguoidung` (`ND_Ma`);

--
-- Các ràng buộc cho bảng `dichvu_kichco`
--
ALTER TABLE `dichvu_kichco`
  ADD CONSTRAINT `fk_dvk_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `dondatlich`
--
ALTER TABLE `dondatlich`
  ADD CONSTRAINT `fk_ddl_nd` FOREIGN KEY (`ND_Ma`) REFERENCES `nguoidung` (`ND_Ma`),
  ADD CONSTRAINT `fk_ddl_nv` FOREIGN KEY (`NV_Ma`) REFERENCES `nhanvien` (`NV_Ma`),
  ADD CONSTRAINT `fk_ddl_nv_hoantien` FOREIGN KEY (`NV_MaHoanTien`) REFERENCES `nhanvien` (`NV_Ma`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ddl_nv_ht` FOREIGN KEY (`NV_MaHoanThanh`) REFERENCES `nhanvien` (`NV_Ma`);

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `fk_gh_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_gh_nd` FOREIGN KEY (`ND_Ma`) REFERENCES `nguoidung` (`ND_Ma`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `hinhanh`
--
ALTER TABLE `hinhanh`
  ADD CONSTRAINT `fk_ha_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lichthue`
--
ALTER TABLE `lichthue`
  ADD CONSTRAINT `fk_lt_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD CONSTRAINT `fk_nd_lnd` FOREIGN KEY (`LND_Ma`) REFERENCES `loai_nguoi_dung` (`LND_Ma`);

--
-- Các ràng buộc cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD CONSTRAINT `fk_nv_nd` FOREIGN KEY (`ND_Ma`) REFERENCES `nguoidung` (`ND_Ma`);

--
-- Các ràng buộc cho bảng `phongchup`
--
ALTER TABLE `phongchup`
  ADD CONSTRAINT `fk_pc_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `phukien`
--
ALTER TABLE `phukien`
  ADD CONSTRAINT `fk_pk_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD CONSTRAINT `fk_tt_ddl` FOREIGN KEY (`DDL_Ma`) REFERENCES `dondatlich` (`DDL_Ma`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thietbi`
--
ALTER TABLE `thietbi`
  ADD CONSTRAINT `fk_tb_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `trangphuc`
--
ALTER TABLE `trangphuc`
  ADD CONSTRAINT `fk_tp_dv` FOREIGN KEY (`DV_Ma`) REFERENCES `dichvu` (`DV_Ma`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
