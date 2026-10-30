
USE studio;

-- 1. Bảng lưu các cuộc hội thoại
CREATE TABLE IF NOT EXISTS chat_hoi_thoai (
    CHT_Ma INT NOT NULL AUTO_INCREMENT,
    ND_Ma INT NOT NULL,
    NV_Ma INT NULL,

    CHT_TieuDe VARCHAR(150) NULL,
    CHT_CheDo VARCHAR(20) NOT NULL DEFAULT 'bot',
    CHT_TrangThai VARCHAR(30) NOT NULL DEFAULT 'dang_mo',

    CHT_NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHT_NgayCapNhat DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CHT_NgayDong DATETIME NULL,

    PRIMARY KEY (CHT_Ma),

    KEY idx_cht_nguoidung (ND_Ma),
    KEY idx_cht_nhanvien (NV_Ma),
    KEY idx_cht_trangthai (CHT_TrangThai, CHT_NgayCapNhat),

    CONSTRAINT fk_cht_nguoidung
        FOREIGN KEY (ND_Ma)
        REFERENCES nguoidung (ND_Ma)
        ON DELETE CASCADE,

    CONSTRAINT fk_cht_nhanvien
        FOREIGN KEY (NV_Ma)
        REFERENCES nhanvien (NV_Ma)
        ON DELETE SET NULL,

    CONSTRAINT ck_cht_chedo
        CHECK (CHT_CheDo IN ('bot', 'nhanvien')),

    CONSTRAINT ck_cht_trangthai
        CHECK (
            CHT_TrangThai IN (
                'dang_mo',
                'cho_nhan_vien',
                'dang_ho_tro',
                'da_dong'
            )
        )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- 2. Bảng lưu tin nhắn trong từng cuộc hội thoại
CREATE TABLE IF NOT EXISTS chat_tin_nhan (
    CTN_Ma BIGINT NOT NULL AUTO_INCREMENT,
    CHT_Ma INT NOT NULL,

    CTN_NguoiGui VARCHAR(20) NOT NULL,
    ND_Ma INT NULL,
    NV_Ma INT NULL,

    CTN_NoiDung TEXT NOT NULL,
    CTN_ThoiDiem DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CTN_DaDoc TINYINT(1) NOT NULL DEFAULT 0,

    PRIMARY KEY (CTN_Ma),

    KEY idx_ctn_hoithoai (CHT_Ma, CTN_Ma),
    KEY idx_ctn_nguoidung (ND_Ma),
    KEY idx_ctn_nhanvien (NV_Ma),

    CONSTRAINT fk_ctn_hoithoai
        FOREIGN KEY (CHT_Ma)
        REFERENCES chat_hoi_thoai (CHT_Ma)
        ON DELETE CASCADE,

    CONSTRAINT fk_ctn_nguoidung
        FOREIGN KEY (ND_Ma)
        REFERENCES nguoidung (ND_Ma)
        ON DELETE SET NULL,

    CONSTRAINT fk_ctn_nhanvien
        FOREIGN KEY (NV_Ma)
        REFERENCES nhanvien (NV_Ma)
        ON DELETE SET NULL,

    CONSTRAINT ck_ctn_nguoigui
        CHECK (
            CTN_NguoiGui IN ('khach', 'bot', 'nhanvien')
        )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
