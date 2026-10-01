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
