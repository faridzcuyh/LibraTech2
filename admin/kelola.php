<?php
session_start();

if (!isset($_SESSION['sudah_login'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Halaman khusus admin/petugas; anggota dikembalikan ke beranda
if (!in_array($_SESSION['role'] ?? '', ['admin', 'petugas'], true)) {
    header("Location: ../user/home.php");
    exit;
}

// 1. Panggil koneksi database (satu sumber: config/koneksi.php)
require_once __DIR__ . '/../config/koneksi.php';

// Pesan hasil aksi; dibaca lalu dibuang supaya tidak tampil dua kali
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// 2. Cek apakah tombol tambah buku sudah diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori = trim($_POST['kategori'] ?? '');
    $judul    = trim($_POST['judul'] ?? '');
    $penulis  = trim($_POST['penulis'] ?? '');
    $status   = trim($_POST['status'] ?? '');
    $badge    = trim($_POST['badge'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($kategori === '' || $judul === '' || $penulis === '' || $status === '') {
        $flash = ['tipe' => 'error', 'pesan' => 'Kategori, judul, penulis, dan status wajib diisi.'];
    } else {
        // Logika upload file gambar sampul
        $sampul = 'default.jpg'; // Jika tidak upload gambar, pakai gambar default
        $error_upload = '';

        if (isset($_FILES['sampul']) && $_FILES['sampul']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['sampul'];
            $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error_upload = 'Gambar sampul gagal diunggah dari browser.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $error_upload = 'Ukuran gambar sampul maksimal 2 MB.';
            } elseif (!in_array($ekstensi, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) || !is_uploaded_file($file['tmp_name']) || !getimagesize($file['tmp_name'])) {
                $error_upload = 'Gambar sampul harus file JPG, PNG, GIF, atau WEBP.';
            } else {
                $target_dir = __DIR__ . '/../assets/images';
                if (!is_dir($target_dir) && !mkdir($target_dir, 0777, true)) {
                    $error_upload = 'Folder assets/images tidak bisa dibuat. Cek izin tulisnya.';
                } else {
                    // Nama file dibuat unik, bukan memakai nama asli dari user
                    $nama_baru = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi;
                    if (!move_uploaded_file($file['tmp_name'], $target_dir . '/' . $nama_baru)) {
                        $error_upload = 'Gambar sampul gagal disimpan ke server.';
                    } else {
                        $sampul = $nama_baru;
                    }
                }
            }
        }

        if ($error_upload !== '') {
            $flash = ['tipe' => 'error', 'pesan' => $error_upload];
        } else {
            // 3. Jalankan query INSERT ke database (prepared statement)
            $stmt = mysqli_prepare($conn, 'INSERT INTO buku (kategori, judul, penulis, status, badge, sampul, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?)');

            if (!$stmt) {
                error_log('Query tambah buku gagal: ' . mysqli_error($conn));
                $flash = ['tipe' => 'error', 'pesan' => 'Buku gagal ditambahkan ke database.'];
            } else {
                $stmt->bind_param('sssssss', $kategori, $judul, $penulis, $status, $badge, $sampul, $deskripsi);

                if ($stmt->execute()) {
                    // PRG: simpan pesan di session lalu redirect supaya refresh tidak menambah buku lagi
                    $_SESSION['flash'] = ['tipe' => 'sukses', 'pesan' => 'Buku "' . $judul . '" berhasil ditambahkan.'];
                    $stmt->close();
                    header('Location: kelola.php');
                    exit;
                }

                error_log('Query tambah buku gagal: ' . $stmt->error);
                $flash = ['tipe' => 'error', 'pesan' => 'Buku gagal ditambahkan ke database.'];
                $stmt->close();
            }
        }
    }
}

// 4. Ambil data buku untuk tabel daftar
$data_buku = [];
$result = mysqli_query($conn, 'SELECT id, kategori, judul, penulis, status FROM buku ORDER BY id DESC');
if ($result) {
    $data_buku = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
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
    <link rel="stylesheet" href="../assets/css/admin.css">
    <title>LibraTech - Kelola</title>
</head>

<body>
    <div class="dashboard">

        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="content">

            <!-- Memulai struktur layout dua kolom -->
            <div class="kelola-container">

                <?php if ($flash) : ?>
                    <div class="flash flash-<?= $flash['tipe'] === 'sukses' ? 'sukses' : 'error' ?>">
                        <?= htmlspecialchars($flash['pesan']) ?>
                    </div>
                <?php endif; ?>

                <!-- KOLOM KIRI: Form Tambah Buku -->
                <div class="panel-box">
                    <h2 class="panel-title">Tambah Buku</h2>

                    <form action="kelola.php" method="POST" enctype="multipart/form-data" class="form-tambah">
                        <div class="form-group">
                            <label>Kategori</label>
                            <input type="text" name="kategori" value="<?= htmlspecialchars($_POST['kategori'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Judul Buku</label>
                            <input type="text" name="judul" value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Penulis</label>
                            <input type="text" name="penulis" value="<?= htmlspecialchars($_POST['penulis'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <input type="text" name="status" value="<?= htmlspecialchars($_POST['status'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Label / Badge</label>
                            <input type="text" name="badge" value="<?= htmlspecialchars($_POST['badge'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" rows="4" placeholder="Ringkasan singkat buku ini (tampil di halaman Library)..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Upload Sampul</label>
                            <input type="file" name="sampul" accept="image/*">
                        </div>

                        <div class="form-action">
                            <button type="submit" class="btn-tambah">Tambah Buku</button>
                        </div>
                    </form>
                </div>

                <!-- KOLOM KANAN: Daftar Buku Bergaris -->
                <div class="panel-box">
                    <h2 class="panel-title">Daftar Buku</h2>

                    <div class="table-responsive">
                        <table class="table-buku">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">No.</th>
                                    <th style="width: 22%;">Kategori</th>
                                    <th style="width: 30%;">Judul</th>
                                    <th style="width: 18%;">Penulis</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;

                                if (count($data_buku) > 0) {
                                    foreach ($data_buku as $row) {
                                        // Efek garis biru pada baris pertama sebagai highlight seperti mockup
                                        $class_highlight = ($no == 1) ? ' class="row-blue-highlight"' : '';

                                        echo "<tr" . $class_highlight . ">";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>" . htmlspecialchars($row['kategori']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['judul']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['penulis']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                        echo "<td>
                                                <a href='edit.php?id=" . urlencode($row['id']) . "' style='color: #0076db; text-decoration:none; margin-right:5px;'>Edit</a>
                                            <a href='hapus.php?id=" . urlencode($row['id']) . "' style='color: #d9264d; text-decoration:none;' onclick='return confirm(\"Yakin hapus?\")'>Hapus</a>
                                            </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    // Jika database masih kosong, tampilkan baris kosong agar estetika desain tetap terjaga
                                    for ($i = 0; $i < 5; $i++) {
                                        echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
                                    }
                                }
                                ?>
                            </tbody>

                        </table>
                    </div>
                </div>

            </div>

        </main>

    </div>
</body>

</html>
