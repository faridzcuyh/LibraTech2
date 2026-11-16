<?php
session_start();

if (!isset($_SESSION['sudah_login'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';

// ===== Statistik dashboard (query baca, pola prepared statement ala library.php) =====
$statDipinjam = 0;
$statTersedia = 0;
$populer      = [];

// Buku terpopuler: top 3 berdasarkan jumlah transaksi pinjam yang pernah jalan
$stmtPopuler = mysqli_prepare($conn, "SELECT b.id, b.judul, b.sampul, COUNT(p.id) AS jumlah
    FROM buku b
    LEFT JOIN peminjaman p ON p.buku_id = b.id AND p.status IN ('disetujui', 'dikembalikan')
    GROUP BY b.id, b.judul, b.sampul
    ORDER BY jumlah DESC, b.id DESC
    LIMIT 3");
if ($stmtPopuler) {
    $stmtPopuler->execute();
    $resultPopuler = $stmtPopuler->get_result();
    while ($row = $resultPopuler->fetch_assoc()) {
        $populer[] = $row;
    }
    $stmtPopuler->close();
} else {
    error_log('Query buku terpopuler gagal: ' . mysqli_error($conn));
}

$stmtDipinjam = mysqli_prepare($conn, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE status = 'disetujui'");
if ($stmtDipinjam) {
    $stmtDipinjam->execute();
    $row = $stmtDipinjam->get_result()->fetch_assoc();
    $statDipinjam = (int) ($row['jumlah'] ?? 0);
    $stmtDipinjam->close();
} else {
    error_log('Query statistik dipinjam gagal: ' . mysqli_error($conn));
}

$stmtTersedia = mysqli_prepare($conn, "SELECT COUNT(*) AS jumlah FROM buku WHERE status = 'Tersedia'");
if ($stmtTersedia) {
    $stmtTersedia->execute();
    $row = $stmtTersedia->get_result()->fetch_assoc();
    $statTersedia = (int) ($row['jumlah'] ?? 0);
    $stmtTersedia->close();
} else {
    error_log('Query statistik tersedia gagal: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css?v=6">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=19">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <title>LibraTech - Home</title>
</head>

<body>
    <div class="dashboard">

        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="content">

            <!-- Bingkai + panel box, sama seperti Library/Pinjam/Kelola -->
            <div class="kelola-container home-frame">

                <!-- Baris 1: panel teks (kiri) + gambar abu di LUAR panel (kanan) -->
                <div class="home-top-row">
                <section class="panel-box home-panel">
                    <div class="card-text">
                            <h1>Selamat Datang, <span class="pink">"<?= htmlspecialchars($_SESSION['nama']) ?>"</span></h1>

                            <!-- Kartu Paling dicari: 3 sampul teratas + angka ranking -->
                            <div class="popular-card">
                                <p class="stat-title">Paling dicari</p>
                                <?php if (count($populer) > 0) : ?>
                                    <div class="popular-grid">
                                        <?php foreach ($populer as $i => $item) : ?>
                                            <?php
                                            // Sampul disimpan sebagai nama file di assets/images (kolom `sampul`)
                                            $nama_sampul = $item['sampul'] !== '' && $item['sampul'] !== null ? $item['sampul'] : 'default.jpg';
                                            ?>
                                            <div class="popular-item">
                                                <span class="popular-rank"><?= $i + 1 ?></span>
                                                <img src="../assets/images/<?= htmlspecialchars($nama_sampul) ?>" alt="<?= htmlspecialchars($item['judul']) ?>" loading="lazy">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <p class="popular-empty">Belum ada data peminjaman.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Kartu statistik: Dipinjam & Tersedia -->
                            <div class="stat-row">
                                <div class="stat-card">
                                    <p class="stat-title">Dipinjam</p>
                                    <p class="stat-number stat-merah"><?= $statDipinjam ?></p>
                                </div>
                                <div class="stat-card">
                                    <p class="stat-title">Tersedia</p>
                                    <p class="stat-number stat-hijau"><?= $statTersedia ?></p>
                                </div>
                            </div>
                        </div>
                </section>
                <div class="card-image"></div>
                </div><!-- /.home-top-row -->

                <?php if ($_SESSION['role'] == 'petugas') : ?>
                    <section class="panel-box home-panel home-menu-panel">
                        <div class="menu-card">
                            <h3>Menu Petugas</h3>
                            <ul>
                                <li><a href="../admin/kelola.php">Kelola Buku</a></li>
                                <li><a href="pinjam.php">Transaksi Pinjam</a></li>
                            </ul>
                        </div>
                    </section>
                <?php endif; ?>

            </div><!-- /.kelola-container -->

        </main>

    </div>
</body>

</html>
