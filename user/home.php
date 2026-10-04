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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <title>LibraTech</title>
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
        }
    </style>
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