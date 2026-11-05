<?php
session_start();

if (!isset($_SESSION['sudah_login'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Halaman khusus admin/petugas
if (!in_array($_SESSION['role'] ?? '', ['admin', 'petugas'], true)) {
    header('Location: ../user/home.php');
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

$stmtRiwayat = mysqli_prepare($conn, 'SELECT id FROM peminjaman WHERE buku_id = ? LIMIT 1');
if (!$stmtRiwayat) {
    error_log('Query cek riwayat peminjaman gagal: ' . mysqli_error($conn));
    $_SESSION['flash'] = ['tipe' => 'error', 'pesan' => 'Buku gagal dihapus.'];
    header('Location: kelola.php');
    exit;
}

$stmtRiwayat->bind_param('i', $id);
$stmtRiwayat->execute();
$memilikiRiwayat = (bool) $stmtRiwayat->get_result()->fetch_assoc();
$stmtRiwayat->close();

if ($memilikiRiwayat) {
    $_SESSION['flash'] = ['tipe' => 'error', 'pesan' => 'Buku tidak dapat dihapus karena memiliki riwayat peminjaman.'];
    header('Location: kelola.php');
    exit;
}

// Nama sampul diambil dulu; setelah barisnya dihapus tidak bisa dibaca lagi
$sampul = '';
$result = mysqli_query($conn, "SELECT sampul FROM buku WHERE id = $id");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $sampul = trim($row['sampul']);
}

$stmt = mysqli_prepare($conn, 'DELETE FROM buku WHERE id = ?');
$stmt->bind_param('i', $id);
$berhasil = $stmt->execute();
$stmt->close();

// Ikut hapus file gambarnya (default.jpg dibiarkan, itu gambar bawaan bersama)
if ($sampul !== '' && $sampul !== 'default.jpg') {
    $file = __DIR__ . '/../assets/images/' . basename($sampul);

    if (is_file($file)) {
        unlink($file);
    }
}

$_SESSION['flash'] = $berhasil
    ? ['tipe' => 'sukses', 'pesan' => 'Buku berhasil dihapus.']
    : ['tipe' => 'error', 'pesan' => 'Buku gagal dihapus.'];

header('Location: kelola.php');
exit;
