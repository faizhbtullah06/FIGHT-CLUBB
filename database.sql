-- =========================================================
-- Database: fightclub_ticketing
-- Digitalisasi Proses Penjualan Tiket Event Fight Club
-- =========================================================

CREATE DATABASE IF NOT EXISTS fightclub_ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fightclub_ticketing;

-- ---------------------------------------------------------
-- Tabel Users (Admin & Penonton)
-- ---------------------------------------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','penonton') NOT NULL DEFAULT 'penonton',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel Events
-- ---------------------------------------------------------
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  location VARCHAR(200) NOT NULL,
  event_date DATETIME NOT NULL,
  poster VARCHAR(255) DEFAULT NULL,
  description TEXT,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel Kategori Tiket per Event (Reguler / VIP / VVIP)
-- ---------------------------------------------------------
CREATE TABLE ticket_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  category ENUM('Reguler','VIP','VVIP') NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  quota INT NOT NULL,
  sold INT NOT NULL DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel Order / Transaksi Pemesanan Tiket
-- ---------------------------------------------------------
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  event_id INT NOT NULL,
  category_id INT NOT NULL,
  qty INT NOT NULL,
  total_price DECIMAL(12,2) NOT NULL,
  payment_code VARCHAR(50) NOT NULL UNIQUE,
  status ENUM('pending','sudah_bayar','dibatalkan') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid_at TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (event_id) REFERENCES events(id),
  FOREIGN KEY (category_id) REFERENCES ticket_categories(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel Tiket (hasil generate setelah pembayaran dikonfirmasi)
-- ---------------------------------------------------------
CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  ticket_code VARCHAR(50) NOT NULL UNIQUE,
  is_used TINYINT(1) NOT NULL DEFAULT 0,
  used_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Seed data: Admin default
-- Email   : admin@fightclub.com
-- Password: admin123
-- ---------------------------------------------------------
INSERT INTO users (name, email, phone, password, role) VALUES
('Administrator', 'admin@fightclub.com', '0800000000', '$2b$12$h5BjteGAfzVvMl6zOsW7weIeNljEbTTfpQIxNy4pbvg9G5H/KsIpW', 'admin');

-- ---------------------------------------------------------
-- Seed data: Contoh Event
-- ---------------------------------------------------------
INSERT INTO events (name, location, event_date, description, status) VALUES
('Fight Club Championship Night', 'GOR Ciracas, Jakarta Timur', '2026-08-15 19:00:00', 'Malam pertarungan kelas berat antar petarung terbaik regional.', 'aktif');

INSERT INTO ticket_categories (event_id, category, price, quota, sold) VALUES
(1, 'Reguler', 75000, 200, 0),
(1, 'VIP', 150000, 80, 0),
(1, 'VVIP', 300000, 20, 0);
