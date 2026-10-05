CREATE DATABASE studio_management;
USE studio_management;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role TINYINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- them cột trang thái cho user
ALTER TABLE users
ADD COLUMN trang_thai ENUM('hoat_dong','cho_duyet','tu_choi','bi_khoa')
NOT NULL DEFAULT 'hoat_dong';
--
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_item_id INT NOT NULL,
    service_id INT NOT NULL,
    so_sao TINYINT NOT NULL,
    binh_luan TEXT,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY mot_mon_mot_danh_gia (order_item_id)
);
--services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    ten VARCHAR(150) NOT NULL,
    loai ENUM('may_anh','studio','trang_phuc','phu_kien','tho_chup') NOT NULL,
    gia INT NOT NULL,
    don_vi ENUM('ngay','gio','bo') NOT NULL,
    so_luong INT NOT NULL DEFAULT 1,
    mo_ta VARCHAR(255),
    mo_ta_chi_tiet TEXT,
    anh VARCHAR(255),
    trang_thai ENUM('hien','an') NOT NULL DEFAULT 'hien',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS lich_trong (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    ngay DATE NOT NULL,
    gio_bat_dau TIME NOT NULL,
    gio_ket_thuc TIME NOT NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tong_tien INT NOT NULL,
    ghi_chu TEXT,
    phuong_thuc_thanh_toan ENUM('chuyen_khoan','tien_mat') NOT NULL,
    da_thanh_toan TINYINT NOT NULL DEFAULT 0,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
ALTER TABLE orders ADD COLUMN code VARCHAR(50) AFTER id;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    service_id INT NOT NULL,
    so_luong INT NOT NULL DEFAULT 1,
    gia_luc_dat INT NOT NULL,
    bat_dau DATETIME NOT NULL,
    ket_thuc DATETIME NOT NULL,
    trang_thai ENUM('cho_duyet','da_duyet','dang_thue','da_tra','tu_choi','da_huy')
        NOT NULL DEFAULT 'cho_duyet',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id)
);