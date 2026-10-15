<?php
session_start();
if (!isset($_SESSION['sudah_login'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Data buku mengikuti halaman kelola (admin/kelola.php): satu sumber, tabel `buku`
require_once __DIR__ . '/../config/koneksi.php';

// ===== Pencarian (prepared statement, LIKE %%q%%) =====
$q = trim($_GET['q'] ?? '');

$sql = 'SELECT id, kategori, judul, penulis, status, badge, sampul, deskripsi FROM buku';
$params = [];

if ($q !== '') {
    $sql .= ' WHERE judul LIKE ? OR penulis LIKE ? OR kategori LIKE ? OR status LIKE ? OR deskripsi LIKE ?';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like, $like];
}

$sql .= ' ORDER BY id DESC';

$buku = [];
$error_db = '';

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if (count($params) > 0) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $buku[] = $row;
        }
    }

    $stmt->close();
} else {
    error_log('Query daftar buku gagal: ' . mysqli_error($conn));
    $error_db = 'Daftar buku gagal dimuat dari database.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibraTech - Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/library.css">
</head>

<body class="dashboard">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="content">
        <search class="search-bar">
            <form action="library.php" method="get" class="search-container">
                <input type="search" class="search-input" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari judul, penulis, atau kategori..." autocomplete="off">
                <button type="submit" class="search-button">Cari</button>
                <?php if ($q !== '') : ?>
                    <a href="library.php" class="search-reset">Reset</a>
                <?php endif; ?>
            </form>
            <p class="search-result">
                <?php if ($q !== '') : ?>
                    <?= count($buku) ?> hasil untuk "<strong><?= htmlspecialchars($q) ?></strong>"
                <?php else : ?>
                    <?= count($buku) ?> buku tersedia
                <?php endif; ?>
            </p>
        </search>

        <?php if ($error_db !== '') : ?>
            <p class="book-error"><?= htmlspecialchars($error_db) ?></p>
        <?php endif; ?>

        <!-- Bagian Rak Buku dengan Sampul -->
        <div class="bookshelf-container">
            <?php if (count($buku) === 0) : ?>
                <p class="book-empty">
                    <?php if ($q !== '') : ?>
                        Buku tidak ditemukan. Coba kata kunci lain.
                    <?php else : ?>
                        Belum ada buku di database. Tambahkan lewat halaman Kelola Buku.
                    <?php endif; ?>
                </p>
            <?php else : ?>
                <div class="book-grid">
                    <?php foreach ($buku as $item) : ?>
                        <?php
                        // Sampul disimpan sebagai nama file di assets/images (kolom `sampul`)
                        $nama_sampul = $item['sampul'] !== '' && $item['sampul'] !== null ? $item['sampul'] : 'default.jpg';
                        ?>
                        <!-- <details> = kartu interaktif: klik sampul untuk buka/tutup detail -->
                        <details class="book-item">
                            <summary class="book-cover">
                                <img src="../assets/images/<?= htmlspecialchars($nama_sampul) ?>" alt="<?= htmlspecialchars($item['judul']) ?>" loading="lazy">
                                <span class="book-meta">
                                    <span class="book-title"><?= htmlspecialchars($item['judul']) ?></span>
                                    <span class="book-author"><?= htmlspecialchars($item['penulis']) ?></span>
                                </span>
                                <span class="book-toggle">Detail</span>
                            </summary>
                            <div class="book-desc">
                                <span class="book-tags">
                                    <span class="book-cat"><?= htmlspecialchars($item['kategori']) ?></span>
                                    <?php if ($item['badge'] !== '' && $item['badge'] !== null) : ?>
                                        <span class="book-badge"><?= htmlspecialchars($item['badge']) ?></span>
                                    <?php endif; ?>
                                </span>
                                <p><?= $item['deskripsi'] !== '' && $item['deskripsi'] !== null
                                    ? nl2br(htmlspecialchars($item['deskripsi']))
                                    : 'Belum ada deskripsi untuk buku ini.' ?></p>
                                <p class="book-status">Status: <strong><?= htmlspecialchars($item['status']) ?></strong></p>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>
