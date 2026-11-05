<?php
session_start();

if (!isset($_SESSION['sudah_login'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!in_array($_SESSION['role'] ?? '', ['admin', 'petugas'], true)) {
    header('Location: ../user/home.php');
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    $_SESSION['flash'] = ['tipe' => 'error', 'pesan' => 'ID buku tidak valid.'];
    header('Location: kelola.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT kategori, judul, penulis, status, badge, sampul, deskripsi FROM buku WHERE id = ?');
if (!$stmt) {
    error_log('Query ambil buku gagal: ' . mysqli_error($conn));
    $_SESSION['flash'] = ['tipe' => 'error', 'pesan' => 'Data buku gagal dimuat.'];
    header('Location: kelola.php');
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$buku = $result->fetch_assoc();
$stmt->close();

if (!$buku) {
    $_SESSION['flash'] = ['tipe' => 'error', 'pesan' => 'Buku tidak ditemukan.'];
    header('Location: kelola.php');
    exit;
}

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori = trim($_POST['kategori'] ?? '');
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $badge = trim($_POST['badge'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($kategori === '' || $judul === '' || $penulis === '' || $status === '') {
        $flash = ['tipe' => 'error', 'pesan' => 'Kategori, judul, penulis, dan status wajib diisi.'];
    }

    $sampul = $buku['sampul'] ?: 'default.jpg';
    $sampul_baru = false;

    if ($flash === null && isset($_FILES['sampul']) && $_FILES['sampul']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['sampul'];
        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $flash = ['tipe' => 'error', 'pesan' => 'Gambar sampul gagal diunggah dari browser.'];
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $flash = ['tipe' => 'error', 'pesan' => 'Ukuran gambar sampul maksimal 2 MB.'];
        } elseif (!in_array($ekstensi, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) || !is_uploaded_file($file['tmp_name']) || !getimagesize($file['tmp_name'])) {
            $flash = ['tipe' => 'error', 'pesan' => 'Gambar sampul harus file JPG, PNG, GIF, atau WEBP.'];
        } else {
            $target_dir = __DIR__ . '/../assets/images';
            if (!is_dir($target_dir) && !mkdir($target_dir, 0777, true)) {
                $flash = ['tipe' => 'error', 'pesan' => 'Folder gambar tidak bisa dibuat. Cek izin tulisnya.'];
            } else {
                $nama_baru = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi;
                if (!move_uploaded_file($file['tmp_name'], $target_dir . '/' . $nama_baru)) {
                    $flash = ['tipe' => 'error', 'pesan' => 'Gambar sampul gagal disimpan ke server.'];
                } else {
                    $sampul = $nama_baru;
                    $sampul_baru = true;
                }
            }
        }
    }

    if ($flash === null) {
        $stmt = mysqli_prepare($conn, 'UPDATE buku SET kategori = ?, judul = ?, penulis = ?, status = ?, badge = ?, sampul = ?, deskripsi = ? WHERE id = ?');

        if (!$stmt) {
            error_log('Query edit buku gagal: ' . mysqli_error($conn));
            $flash = ['tipe' => 'error', 'pesan' => 'Perubahan buku gagal disimpan.'];
        } else {
            $stmt->bind_param('sssssssi', $kategori, $judul, $penulis, $status, $badge, $sampul, $deskripsi, $id);

            if ($stmt->execute()) {
                $stmt->close();

                if ($sampul_baru && $buku['sampul'] !== '' && $buku['sampul'] !== 'default.jpg') {
                    $sampul_lama = __DIR__ . '/../assets/images/' . basename($buku['sampul']);
                    if (is_file($sampul_lama)) {
                        unlink($sampul_lama);
                    }
                }

                $_SESSION['flash'] = ['tipe' => 'sukses', 'pesan' => 'Buku "' . $judul . '" berhasil diperbarui.'];
                header('Location: kelola.php');
                exit;
            }

            error_log('Query edit buku gagal: ' . $stmt->error);
            $stmt->close();
            $flash = ['tipe' => 'error', 'pesan' => 'Perubahan buku gagal disimpan.'];

            if ($sampul_baru) {
                $file_baru = __DIR__ . '/../assets/images/' . basename($sampul);
                if (is_file($file_baru)) {
                    unlink($file_baru);
                }
            }
        }
    }

    if ($flash !== null) {
        $buku['kategori'] = $kategori;
        $buku['judul'] = $judul;
        $buku['penulis'] = $penulis;
        $buku['status'] = $status;
        $buku['badge'] = $badge;
        $buku['deskripsi'] = $deskripsi;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css?v=5">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=5">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <title>LibraTech - Edit Buku</title>
</head>

<body>
    <div class="dashboard">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="content">
            <div class="kelola-container">
                <div class="panel-box">
                    <?php if ($flash) : ?>
                        <div class="flash flash-<?= $flash['tipe'] === 'sukses' ? 'sukses' : 'error' ?>">
                            <?= htmlspecialchars($flash['pesan'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <h2 class="panel-title">Edit Buku</h2>

                    <form action="edit.php?id=<?= (int) $id ?>" method="POST" enctype="multipart/form-data" class="form-tambah">
                        <div class="form-group">
                            <label for="kategori">Kategori</label>
                            <input id="kategori" type="text" name="kategori" value="<?= htmlspecialchars($buku['kategori'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="judul">Judul Buku</label>
                            <input id="judul" type="text" name="judul" value="<?= htmlspecialchars($buku['judul'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="penulis">Penulis</label>
                            <input id="penulis" type="text" name="penulis" value="<?= htmlspecialchars($buku['penulis'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <input id="status" type="text" name="status" value="<?= htmlspecialchars($buku['status'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="badge">Label / Badge</label>
                            <input id="badge" type="text" name="badge" value="<?= htmlspecialchars($buku['badge'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="4"><?= htmlspecialchars($buku['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Sampul saat ini</label>
                            <img src="../assets/images/<?= rawurlencode(basename($buku['sampul'] ?: 'default.jpg')) ?>" alt="Sampul <?= htmlspecialchars($buku['judul'], ENT_QUOTES, 'UTF-8') ?>" style="width: 120px; height: auto;">
                        </div>

                        <div class="form-group">
                            <label for="sampul">Ganti Sampul (opsional, maksimal 2 MB)</label>
                            <input id="sampul" type="file" name="sampul" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                        </div>

                        <div class="form-action">
                            <a href="kelola.php" style="align-self: center; margin-right: 12px; color: #333;">Batal</a>
                            <button type="submit" class="btn-tambah">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>