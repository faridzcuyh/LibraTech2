<?php
$conn = mysqli_connect("localhost", "root", "", "perpustakaan");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$username = "admin_perpus";
$nama_lengkap = "Farid (atmin)";
$password = "perpus2026";

$password_aman = password_hash($password, PASSWORD_DEFAULT);
$role = "petugas";

$stmt = mysqli_prepare($conn, "INSERT INTO user (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssss", $username, $password_aman, $nama_lengkap, $role);

if (mysqli_stmt_execute($stmt)) {
    echo "Akun petugas berhasil dibuat! Silakan hapus file ini.";
} else {
    echo "Gagal membuat akun: " . mysqli_stmt_error($stmt);
}
?>