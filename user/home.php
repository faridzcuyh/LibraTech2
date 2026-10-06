<?php
session_start();

if (!isset($_SESSION['sudah_login'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title>LibraTech - Home</title>
</head>

<body>
    <div class="dashboard">

        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="content">

            <div class="welcome-card">
                <div class="card-text">
                    <h1>Selamat Datang, <span class="pink">"<?= htmlspecialchars($_SESSION['nama']) ?>"</span></h1>
                    <p>
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                        when an unknown printer took a galley of type and scrambled it to make a type
                        specimen book. It has survived not only five centuries, but also the leap into
                        electronic typesetting, remaining essentially unchanged.
                    </p>
                </div>
                <div class="card-image"></div>
            </div>

            <?php if ($_SESSION['role'] == 'petugas') : ?>
                <div class="menu-card">
                    <h3>Menu Petugas</h3>
                    <ul>
                        <li><a href="input_buku.php">Kelola Buku</a></li>
                        <li><a href="transaksi_pinjam.php">Transaksi Pinjam</a></li>
                    </ul>
                </div>
            <?php endif; ?>

        </main>

    </div>
</body>

</html>
