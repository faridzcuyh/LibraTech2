<?php
session_start();

if (!isset($_SESSION['sudah_login'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>Selamat Datang, <?php echo $_SESSION['nama']; ?>!</h1>
    <p>Anda Masuk sebagai: <strong> <?php echo ucfirst($_SESSION['role']); ?></strong></p>

    <hr>
    <?php if ($_SESSION['role'] == 'petugas') : ?>
        <h3>Menu Petugas:</h3>
        <ul>
            <li><a href="input_buku.php">Kelola</a></li>
            <li><a href="transaksi_pinjam.php">Pinjam</a></li>

        </ul>
    <?php endif; ?>
        <h3>Menu Umum</h3>
        <ul>
            <li><a href="library.php">Library</a></li>
            <li><a href="logout.php" onclick="return confirm('yakin ingin keluar?')">Logout</a></li>
        </ul>
</body>

</html>