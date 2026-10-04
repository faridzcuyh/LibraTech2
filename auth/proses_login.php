<?php
session_start(); // Memulai session
$conn = mysqli_connect("localhost", "root", "", "perpustakaan");

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // 1. Cari user berdasarkan username
    $result = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");
    
    // Cek apakah username ditemukan
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // 2. Verifikasi password dengan password_verify
        if (password_verify($password, $row['password'])) {
            
            // 3. Jika cocok, buat session tanda pengenal
            $_SESSION['sudah_login'] = true;
            $_SESSION['id_user']      = $row['id'];
            $_SESSION['username']     = $row['username'];
            $_SESSION['nama']         = $row['nama_lengkap'];
            $_SESSION['role']         = $row['role'];

            // Alihkan ke halaman utama dashboard
            header("Location: home.php");
            exit;
        }
    }
    
    // Jika salah username atau password
    echo "<script>alert('Username atau Password salah!'); window.location='login.php';</script>";
}
?>
