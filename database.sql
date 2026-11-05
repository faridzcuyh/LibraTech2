-- Skema database LibraTech2 (Sistem Informasi Perpustakaan)
-- Import: mysql -u root -p < database.sql  (atau phpMyAdmin > Import)

CREATE DATABASE IF NOT EXISTS perpustakaan
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE perpustakaan;

CREATE TABLE IF NOT EXISTS user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  role ENUM('admin', 'petugas', 'anggota') NOT NULL DEFAULT 'petugas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel buku (dipakai admin/kelola.php)
CREATE TABLE IF NOT EXISTS buku (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kategori VARCHAR(100) NOT NULL,
  judul VARCHAR(255) NOT NULL,
  penulis VARCHAR(150) NOT NULL,
  status VARCHAR(50) NOT NULL,
  badge VARCHAR(50) DEFAULT NULL,
  sampul VARCHAR(255) DEFAULT 'default.jpg',
  deskripsi TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Transaksi peminjaman buku
CREATE TABLE IF NOT EXISTS peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buku_id INT NOT NULL,
  user_id INT NOT NULL,
  tanggal_pinjam DATE NOT NULL,
  tanggal_kembali DATE DEFAULT NULL,
  status ENUM('menunggu', 'disetujui', 'ditolak', 'dikembalikan') NOT NULL DEFAULT 'menunggu',
  dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_peminjaman_buku_status (buku_id, status),
  INDEX idx_peminjaman_user (user_id),
  CONSTRAINT fk_peminjaman_buku FOREIGN KEY (buku_id) REFERENCES buku (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_peminjaman_user FOREIGN KEY (user_id) REFERENCES user (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
