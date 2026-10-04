<?php
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_POST['register'])) {
    // Akses langsung tanpa submit form -> kembalikan ke halaman register
    header("Location: register.php");
    exit;
}

$nama     = trim($_POST['nama']);
$username = trim($_POST['username']);
$password = $_POST['password'];
$password2 = $_POST['password2'];

// Helper: kembali ke form dengan pesan error (isi ulang nama & username)
function gagal($pesan)
{
    $query = http_build_query([
        'error'    => $pesan,
        'nama'     => $_POST['nama'] ?? '',
        'username' => $_POST['username'] ?? '',
    ]);
    header("Location: register.php?" . $query);
    exit;
}

// 1. Validasi dasar
if ($nama === '' || $username === '' || $password === '') {
    gagal('Semua kolom wajib diisi!');
}

if (strlen($username) < 4 || strlen($username) > 50) {
    gagal('Username harus 4-50 karakter!');
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    gagal('Username hanya boleh huruf, angka, dan underscore!');
}

if (strlen($password) < 6) {
    gagal('Password minimal 6 karakter!');
}

if ($password !== $password2) {
    gagal('Konfirmasi password tidak cocok!');
}

// 2. Cek username sudah dipakai atau belum (prepared statement)
$stmt = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    gagal('Username sudah dipakai, silakan pilih yang lain!');
}
mysqli_stmt_close($stmt);

// 3. Simpan user baru (password di-hash, role default anggota)
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$role = "anggota";

$stmt = mysqli_prepare($conn, "INSERT INTO user (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssss", $username, $password_hash, $nama, $role);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: login.php?registered=1");
    exit;
}

gagal('Gagal mendaftar: ' . mysqli_error($conn));
