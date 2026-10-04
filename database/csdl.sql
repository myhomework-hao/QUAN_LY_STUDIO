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