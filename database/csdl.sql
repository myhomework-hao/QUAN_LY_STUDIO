CREATE DATABASE IF NOT EXISTS quan_ly_studio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quan_ly_studio;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS giohang, danhgia, thanhtoan, lichthue, chitietdon, dondatlich, hinhanh,
  phukien, trangphuc, thietbi, phongchup, dichvu, nhanvien, nguoidung, loai_nguoi_dung;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE loai_nguoi_dung (
  LND_Ma INT(11) NOT NULL AUTO_INCREMENT,
  LND_Ten VARCHAR(100) NOT NULL,
  PRIMARY KEY (LND_Ma),
  UNIQUE KEY uq_lnd_ten (LND_Ten)
) ENGINE=InnoDB;

CREATE TABLE nguoidung (
  ND_Ma INT(11) NOT NULL AUTO_INCREMENT,
  ND_Ten VARCHAR(100) NOT NULL,
  ND_GioiTinh VARCHAR(10) NULL,
  ND_NgaySinh DATE NULL,
  ND_SDT VARCHAR(15) NULL,
  ND_Email VARCHAR(100) NOT NULL,
  ND_MatKhau VARCHAR(255) NOT NULL,
  ND_TrangThai TINYINT(1) NOT NULL DEFAULT 1,
  ND_NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  LND_Ma INT(11) NOT NULL,
  PRIMARY KEY (ND_Ma),
  UNIQUE KEY uq_nd_email (ND_Email),
  CONSTRAINT fk_nd_lnd FOREIGN KEY (LND_Ma) REFERENCES loai_nguoi_dung (LND_Ma)
) ENGINE=InnoDB;

CREATE TABLE nhanvien (
  NV_Ma INT(11) NOT NULL AUTO_INCREMENT,
  ND_Ma INT(11) NOT NULL,
  NV_MaNhanVien VARCHAR(20) NOT NULL,
  NV_ChucVu VARCHAR(50) NULL,
  NV_NgayVaoLam DATE NULL,
  NV_TrangThai TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (NV_Ma),
  UNIQUE KEY uq_nv_nd (ND_Ma),
  UNIQUE KEY uq_nv_ma (NV_MaNhanVien),
  CONSTRAINT fk_nv_nd FOREIGN KEY (ND_Ma) REFERENCES nguoidung (ND_Ma)
) ENGINE=InnoDB;

CREATE TABLE dichvu (
  DV_Ma INT(11) NOT NULL AUTO_INCREMENT,
  DV_Ten VARCHAR(150) NOT NULL,
  DV_Loai VARCHAR(30) NOT NULL,
  DV_MoTa TEXT NULL,
  DV_GiaThue DECIMAL(12,0) NOT NULL,
  DV_DonViTinh VARCHAR(20) NOT NULL DEFAULT 'giờ',
  DV_HinhAnh VARCHAR(255) NULL,
  DV_SoLuong INT(11) NOT NULL DEFAULT 1,
  DV_DangBan TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (DV_Ma),
  KEY ix_dv_loai (DV_Loai, DV_DangBan),
  CONSTRAINT ck_dv_loai CHECK (DV_Loai IN ('phong_chup','thiet_bi','trang_phuc','phu_kien'))
) ENGINE=InnoDB;

CREATE TABLE hinhanh (
  HA_Ma INT(11) NOT NULL AUTO_INCREMENT,
  DV_Ma INT(11) NOT NULL,
  HA_DuongDan VARCHAR(255) NOT NULL,
  HA_NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (HA_Ma),
  CONSTRAINT fk_ha_dv FOREIGN KEY (DV_Ma) REFERENCES dichvu (DV_Ma) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE phongchup (
  DV_Ma INT(11) NOT NULL,
  PC_SucChua INT(11) NOT NULL,
  PC_TrangThai VARCHAR(20) NOT NULL DEFAULT 'Hoạt động',
  PRIMARY KEY (DV_Ma),
  CONSTRAINT fk_pc_dv FOREIGN KEY (DV_Ma) REFERENCES dichvu (DV_Ma) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE thietbi (
  DV_Ma INT(11) NOT NULL,
  TB_Loai VARCHAR(50) NULL,
  TB_TinhTrang VARCHAR(30) NOT NULL DEFAULT 'Tốt',
  PRIMARY KEY (DV_Ma),
  CONSTRAINT fk_tb_dv FOREIGN KEY (DV_Ma) REFERENCES dichvu (DV_Ma) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE trangphuc (
  DV_Ma INT(11) NOT NULL,
  TP_KichCo VARCHAR(20) NULL,
  TP_TinhTrang VARCHAR(30) NOT NULL DEFAULT 'Tốt',
  PRIMARY KEY (DV_Ma),
  CONSTRAINT fk_tp_dv FOREIGN KEY (DV_Ma) REFERENCES dichvu (DV_Ma) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE phukien (
  DV_Ma INT(11) NOT NULL,
  PK_Loai VARCHAR(100) NULL,
  PK_TinhTrang VARCHAR(30) NOT NULL DEFAULT 'Tốt',
  PRIMARY KEY (DV_Ma),
  CONSTRAINT fk_pk_dv FOREIGN KEY (DV_Ma) REFERENCES dichvu (DV_Ma) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE dondatlich (
  DDL_Ma INT(11) NOT NULL AUTO_INCREMENT,
  ND_Ma INT(11) NOT NULL,
  DDL_TongTien DECIMAL(12,0) NOT NULL,
  DDL_TienCoc DECIMAL(12,0) NOT NULL DEFAULT 0,
  DDL_TrangThai VARCHAR(20) NOT NULL DEFAULT 'Chờ duyệt',
  NV_Ma INT(11) NULL,
  DDL_ThoiDiemXuLy DATETIME NULL,
  DDL_LyDoTuChoi VARCHAR(255) NULL,
  DDL_NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  DDL_GhiChu TEXT NULL,
  DDL_ThoiDiemHuy DATETIME NULL,
  DDL_LyDoHuy VARCHAR(255) NULL,
  NV_MaHoanThanh INT(11) NULL,
  DDL_ThoiDiemHoanThanh DATETIME NULL,
  PRIMARY KEY (DDL_Ma),
  KEY ix_ddl_nd (ND_Ma),
  KEY ix_ddl_trangthai (DDL_TrangThai),
  CONSTRAINT fk_ddl_nd FOREIGN KEY (ND_Ma) REFERENCES nguoidung (ND_Ma),
  CONSTRAINT fk_ddl_nv FOREIGN KEY (NV_Ma) REFERENCES nhanvien (NV_Ma),
  CONSTRAINT fk_ddl_nv_ht FOREIGN KEY (NV_MaHoanThanh) REFERENCES nhanvien (NV_Ma)
) ENGINE=InnoDB;

CREATE TABLE chitietdon (
  CTD_Ma INT(11) NOT NULL AUTO_INCREMENT,
  DDL_Ma INT(11) NOT NULL,
  DV_Ma INT(11) NOT NULL,
  CTD_SoLuong INT(11) NOT NULL DEFAULT 1,
  CTD_KichCo VARCHAR(20) NULL,
  CTD_BatDau DATETIME NOT NULL,
  CTD_KetThuc DATETIME NOT NULL,
  CTD_DonGia DECIMAL(12,0) NOT NULL,
  CTD_ThanhTien DECIMAL(12,0) NOT NULL,
  PRIMARY KEY (CTD_Ma),
  KEY ix_ctd_trung_lich (DV_Ma, CTD_BatDau, CTD_KetThuc),
  CONSTRAINT fk_ctd_ddl FOREIGN KEY (DDL_Ma) REFERENCES dondatlich (DDL_Ma) ON DELETE CASCADE,
  CONSTRAINT fk_ctd_dv FOREIGN KEY (DV_Ma) REFERENCES dichvu (DV_Ma),
  CONSTRAINT ck_ctd_gio CHECK (CTD_KetThuc > CTD_BatDau)
) ENGINE=InnoDB;

CREATE TABLE lichthue (
  LT_Ma INT(11) NOT NULL AUTO_INCREMENT,
  DV_Ma INT(11) NOT NULL,
  LT_BatDau DATETIME NOT NULL,
  LT_KetThuc DATETIME NOT NULL,
  LT_TrangThai VARCHAR(20) NOT NULL DEFAULT 'trống',
  LT_HetHanGiu DATETIME NULL,
  PRIMARY KEY (LT_Ma),
  UNIQUE KEY uq_lt_dv_batdau (DV_Ma, LT_BatDau),
  CONSTRAINT fk_lt_dv FOREIGN KEY (DV_Ma) REFERENCES dichvu (DV_Ma) ON DELETE CASCADE,
  CONSTRAINT ck_lt_gio CHECK (LT_KetThuc > LT_BatDau)
) ENGINE=InnoDB;

CREATE TABLE giohang (
  GH_Ma INT(11) NOT NULL AUTO_INCREMENT,
  ND_Ma INT(11) NOT NULL,
  DV_Ma INT(11) NOT NULL,
  GH_SoLuong INT(11) NOT NULL DEFAULT 1,
  GH_KichCo VARCHAR(20) NULL,
  GH_BatDau DATETIME NOT NULL,
  GH_KetThuc DATETIME NOT NULL,
  GH_NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (GH_Ma),
  KEY ix_gh_nd (ND_Ma),
  CONSTRAINT fk_gh_nd FOREIGN KEY (ND_Ma) REFERENCES nguoidung (ND_Ma) ON DELETE CASCADE,
  CONSTRAINT fk_gh_dv FOREIGN KEY (DV_Ma) REFERENCES dichvu (DV_Ma) ON DELETE CASCADE,
  CONSTRAINT ck_gh_gio CHECK (GH_KetThuc > GH_BatDau)
) ENGINE=InnoDB;

CREATE TABLE thanhtoan (
  TT_Ma INT(11) NOT NULL AUTO_INCREMENT,
  DDL_Ma INT(11) NOT NULL,
  TT_Loai VARCHAR(20) NOT NULL,
  TT_SoTien DECIMAL(12,0) NOT NULL,
  TT_PhuongThuc VARCHAR(30) NOT NULL,
  TT_MaThamChieu VARCHAR(100) NULL,
  TT_TrangThai VARCHAR(20) NOT NULL DEFAULT 'Chờ',
  TT_ThoiDiem DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (TT_Ma),
  CONSTRAINT fk_tt_ddl FOREIGN KEY (DDL_Ma) REFERENCES dondatlich (DDL_Ma) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE danhgia (
  DG_Ma INT(11) NOT NULL AUTO_INCREMENT,
  ND_Ma INT(11) NOT NULL,
  DDL_Ma INT(11) NOT NULL,
  DV_Ma INT(11) NOT NULL,
  DG_SoSao TINYINT(1) NOT NULL,
  DG_NoiDung TEXT NULL,
  DG_NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (DG_Ma),
  UNIQUE KEY uq_dg_don_dv (DDL_Ma, DV_Ma),
  CONSTRAINT fk_dg_nd FOREIGN KEY (ND_Ma) REFERENCES nguoidung (ND_Ma),
  CONSTRAINT fk_dg_ddl FOREIGN KEY (DDL_Ma) REFERENCES dondatlich (DDL_Ma) ON DELETE CASCADE,
  CONSTRAINT fk_dg_dv FOREIGN KEY (DV_Ma) REFERENCES dichvu (DV_Ma),
  CONSTRAINT ck_dg_sao CHECK (DG_SoSao BETWEEN 1 AND 5)
) ENGINE=InnoDB;

INSERT INTO loai_nguoi_dung (LND_Ma, LND_Ten) VALUES (1,'admin'),(2,'nhanvien'),(3,'khachhang');

INSERT INTO nguoidung (ND_Ten, ND_Email, ND_MatKhau, ND_TrangThai, LND_Ma) VALUES
('Quản trị viên','admin@studio.local','$2y$10$ty.f5jb3Y4PH3M2GgsBrDOFQz/cxEHhxLCA48kvQAg0MrrREkfSu.',1,1);

INSERT INTO nguoidung (ND_Ten, ND_Email, ND_MatKhau, ND_TrangThai, LND_Ma) VALUES
('Nhân viên mẫu','nhanvien@studio.local','$2y$10$oNrCu9BRHOMCQUVpwZd1BOtZrIrs/Z.7e4kHubUI2Z/0.E6MkNhg.',1,2),
('Khách hàng mẫu','khach@studio.local','$2y$10$5C2Z/Fn58f9I5SbMn44gfObkoUCmRcDatdG7N.x4wHw/i.SVsyr3K',1,3);

INSERT INTO nhanvien (ND_Ma, NV_MaNhanVien, NV_ChucVu, NV_NgayVaoLam) VALUES (2,'NV001','Nhân viên studio',CURDATE());

INSERT INTO dichvu (DV_Ma, DV_Ten, DV_Loai, DV_MoTa, DV_GiaThue, DV_DonViTinh, DV_SoLuong) VALUES
(1,'Phòng chụp Phông trắng','phong_chup','Phòng chụp tối giản, nền trắng, đèn softbox.',300000,'giờ',1),
(2,'Phòng chụp Vintage','phong_chup','Phòng decor phong cách cổ điển.',400000,'giờ',1),
(3,'Máy ảnh Sony A7 III','thiet_bi','Máy ảnh mirrorless full-frame.',250000,'giờ',3),
(4,'Đèn Godox 400W','thiet_bi','Đèn flash studio kèm softbox.',100000,'giờ',4),
(5,'Váy cưới trắng','trang_phuc','Váy cưới ren, dáng chữ A.',200000,'giờ',5),
(6,'Áo dài truyền thống','trang_phuc','Áo dài lụa nhiều màu.',150000,'giờ',6),
(7,'Hoa khô trang trí','phu_kien','Bó hoa khô dùng làm đạo cụ.',30000,'lượt',10),
(8,'Ghế mây','phu_kien','Ghế mây phong cách Bắc Âu.',50000,'giờ',3);

INSERT INTO phongchup (DV_Ma, PC_SucChua) VALUES (1,6),(2,8);
INSERT INTO thietbi (DV_Ma, TB_Loai) VALUES (3,'Máy ảnh'),(4,'Đèn');
INSERT INTO trangphuc (DV_Ma, TP_KichCo) VALUES (5,'S, M, L'),(6,'S, M, L');
INSERT INTO phukien (DV_Ma, PK_Loai) VALUES (7,'Hoa'),(8,'Nội thất');

INSERT INTO lichthue (DV_Ma, LT_BatDau, LT_KetThuc, LT_TrangThai)
SELECT d.DV_Ma, TIMESTAMP(CURDATE() + INTERVAL n.ngay DAY, '08:00:00'), TIMESTAMP(CURDATE() + INTERVAL n.ngay DAY, '20:00:00'), 'trống'
FROM dichvu d JOIN (SELECT 1 ngay UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7) n;