<?php
session_start();

if (!isset($_SESSION['sudah_login'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';

$userId = (int) ($_SESSION['id_user'] ?? 0);
$role = $_SESSION['role'] ?? 'anggota';
$bolehKelola = in_array($role, ['admin', 'petugas'], true);

if (empty($_SESSION['csrf_peminjaman'])) {
    $_SESSION['csrf_peminjaman'] = bin2hex(random_bytes(32));
}
$tokenCsrf = $_SESSION['csrf_peminjaman'];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenDikirim = $_POST['csrf_token'] ?? '';

    if (!is_string($tokenDikirim) || !hash_equals($tokenCsrf, $tokenDikirim)) {
        $flash = ['tipe' => 'error', 'pesan' => 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.'];
    } elseif (($_POST['aksi'] ?? '') === 'ajukan') {
        $bukuId = (int) ($_POST['buku_id'] ?? 0);

        if ($userId < 1 || $bukuId < 1) {
            $flash = ['tipe' => 'error', 'pesan' => 'Pilih buku yang ingin dipinjam.'];
        } elseif (!mysqli_begin_transaction($conn)) {
            $flash = ['tipe' => 'error', 'pesan' => 'Pengajuan pinjam gagal diproses.'];
        } else {
            $stmtBuku = mysqli_prepare($conn, 'SELECT id FROM buku WHERE id = ? FOR UPDATE');
            if ($stmtBuku) {
                $stmtBuku->bind_param('i', $bukuId);
                $stmtBuku->execute();
                $bukuAda = $stmtBuku->get_result()->fetch_assoc();
                $stmtBuku->close();
            } else {
                $bukuAda = false;
            }

            if (!$bukuAda) {
                mysqli_rollback($conn);
                $flash = ['tipe' => 'error', 'pesan' => 'Buku tidak ditemukan.'];
            } else {
                $stmtCek = mysqli_prepare($conn, "SELECT id FROM peminjaman WHERE buku_id = ? AND status IN ('menunggu', 'disetujui') LIMIT 1");
                if ($stmtCek) {
                    $stmtCek->bind_param('i', $bukuId);
                    $stmtCek->execute();
                    $sudahDipinjam = (bool) $stmtCek->get_result()->fetch_assoc();
                    $stmtCek->close();
                } else {
                    $sudahDipinjam = true;
                }

                if ($sudahDipinjam) {
                    mysqli_rollback($conn);
                    $flash = ['tipe' => 'error', 'pesan' => 'Buku sedang dalam pengajuan atau masih dipinjam.'];
                } else {
                    $stmtTambah = mysqli_prepare($conn, "INSERT INTO peminjaman (buku_id, user_id, tanggal_pinjam, status) VALUES (?, ?, CURDATE(), 'menunggu')");
                    if ($stmtTambah) {
                        $stmtTambah->bind_param('ii', $bukuId, $userId);
                        $berhasil = $stmtTambah->execute();
                        if (!$berhasil) {
                            error_log('Pengajuan pinjam gagal: ' . $stmtTambah->error);
                        }
                        $stmtTambah->close();
                    } else {
                        $berhasil = false;
                        error_log('Query pengajuan pinjam gagal: ' . mysqli_error($conn));
                    }

                    if ($berhasil) {
                        mysqli_commit($conn);
                        $_SESSION['flash'] = ['tipe' => 'sukses', 'pesan' => 'Pengajuan peminjaman berhasil dikirim.'];
                        header('Location: pinjam.php');
                        exit;
                    }

                    mysqli_rollback($conn);
                    $flash = ['tipe' => 'error', 'pesan' => 'Pengajuan peminjaman gagal disimpan.'];
                }
            }
        }
    } elseif (($_POST['aksi'] ?? '') === 'ubah_status') {
        $peminjamanId = (int) ($_POST['peminjaman_id'] ?? 0);
        $statusBaru = $_POST['status'] ?? '';

        if (!$bolehKelola) {
            $flash = ['tipe' => 'error', 'pesan' => 'Anda tidak memiliki izin untuk mengubah status peminjaman.'];
        } elseif ($peminjamanId < 1 || !in_array($statusBaru, ['disetujui', 'ditolak', 'dikembalikan'], true)) {
            $flash = ['tipe' => 'error', 'pesan' => 'Data perubahan status tidak valid.'];
        } elseif (!mysqli_begin_transaction($conn)) {
            $flash = ['tipe' => 'error', 'pesan' => 'Status peminjaman gagal diperbarui.'];
        } else {
            $stmtStatus = mysqli_prepare($conn, 'SELECT status FROM peminjaman WHERE id = ? FOR UPDATE');
            if ($stmtStatus) {
                $stmtStatus->bind_param('i', $peminjamanId);
                $stmtStatus->execute();
                $dataStatus = $stmtStatus->get_result()->fetch_assoc();
                $stmtStatus->close();
            } else {
                $dataStatus = false;
            }

            $transisiValid = $dataStatus && (
                ($dataStatus['status'] === 'menunggu' && in_array($statusBaru, ['disetujui', 'ditolak'], true)) ||
                ($dataStatus['status'] === 'disetujui' && $statusBaru === 'dikembalikan')
            );

            if (!$transisiValid) {
                mysqli_rollback($conn);
                $flash = ['tipe' => 'error', 'pesan' => 'Perubahan status tidak dapat dilakukan.'];
            } else {
                $tanggalKembali = $statusBaru === 'dikembalikan' ? date('Y-m-d') : null;
                $stmtUbah = mysqli_prepare($conn, 'UPDATE peminjaman SET status = ?, tanggal_kembali = ? WHERE id = ?');
                if ($stmtUbah) {
                    $stmtUbah->bind_param('ssi', $statusBaru, $tanggalKembali, $peminjamanId);
                    $berhasil = $stmtUbah->execute();
                    if (!$berhasil) {
                        error_log('Perubahan status pinjam gagal: ' . $stmtUbah->error);
                    }
                    $stmtUbah->close();
                } else {
                    $berhasil = false;
                    error_log('Query perubahan status pinjam gagal: ' . mysqli_error($conn));
                }

                if ($berhasil) {
                    mysqli_commit($conn);
                    $_SESSION['flash'] = ['tipe' => 'sukses', 'pesan' => 'Status peminjaman berhasil diperbarui.'];
                    header('Location: pinjam.php');
                    exit;
                }

                mysqli_rollback($conn);
                $flash = ['tipe' => 'error', 'pesan' => 'Status peminjaman gagal diperbarui.'];
            }
        }
    } else {
        $flash = ['tipe' => 'error', 'pesan' => 'Aksi tidak dikenali.'];
    }
}

$daftarBuku = [];
$queryBuku = "SELECT b.id, b.judul, b.penulis FROM buku b
    WHERE NOT EXISTS (
        SELECT 1 FROM peminjaman p
        WHERE p.buku_id = b.id AND p.status IN ('menunggu', 'disetujui')
    )
    ORDER BY b.judul ASC";
$resultBuku = mysqli_query($conn, $queryBuku);
if ($resultBuku) {
    $daftarBuku = mysqli_fetch_all($resultBuku, MYSQLI_ASSOC);
    mysqli_free_result($resultBuku);
}

$daftarPeminjaman = [];
$sqlPeminjaman = 'SELECT p.id, p.user_id, p.tanggal_pinjam, p.tanggal_kembali, p.status,
        b.judul, b.penulis, u.nama_lengkap
    FROM peminjaman p
    INNER JOIN buku b ON b.id = p.buku_id
    INNER JOIN user u ON u.id = p.user_id';
if (!$bolehKelola) {
    $sqlPeminjaman .= ' WHERE p.user_id = ?';
}
$sqlPeminjaman .= ' ORDER BY p.id DESC';
$stmtPeminjaman = mysqli_prepare($conn, $sqlPeminjaman);
if ($stmtPeminjaman) {
    if (!$bolehKelola) {
        $stmtPeminjaman->bind_param('i', $userId);
    }
    $stmtPeminjaman->execute();
    $resultPeminjaman = $stmtPeminjaman->get_result();
    $daftarPeminjaman = mysqli_fetch_all($resultPeminjaman, MYSQLI_ASSOC);
    $stmtPeminjaman->close();
} else {
    error_log('Query daftar peminjaman gagal: ' . mysqli_error($conn));
}

$labelStatus = [
    'menunggu' => 'Menunggu',
    'disetujui' => 'Disetujui',
    'ditolak' => 'Ditolak',
    'dikembalikan' => 'Dikembalikan',
];
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
    <title>LibraTech - Peminjaman</title>
</head>

<body>
    <div class="dashboard">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="content">
            <div class="kelola-container">
                <?php if ($flash) : ?>
                    <div class="flash flash-<?= ($flash['tipe'] ?? '') === 'sukses' ? 'sukses' : 'error' ?>">
                        <?= htmlspecialchars($flash['pesan'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <section class="panel-box">
                    <h2 class="panel-title">Ajukan Peminjaman</h2>
                    <form action="pinjam.php" method="POST" class="form-tambah">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="aksi" value="ajukan">

                        <div class="form-group">
                            <label for="buku_id">Pilih Buku</label>
                            <select id="buku_id" name="buku_id" required <?= count($daftarBuku) === 0 ? 'disabled' : '' ?> style="width: 100%; padding: 10px 12px; font-size: 14px; font-family: inherit; border: 1.5px solid #cccccc; border-radius: 8px; background: #fff;">
                                <option value="">-- Pilih buku --</option>
                                <?php foreach ($daftarBuku as $buku) : ?>
                                    <option value="<?= (int) $buku['id'] ?>">
                                        <?= htmlspecialchars($buku['judul'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($buku['penulis'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (count($daftarBuku) === 0) : ?>
                            <p>Belum ada buku yang tersedia untuk diajukan.</p>
                        <?php endif; ?>

                        <div class="form-action">
                            <button type="submit" class="btn-tambah" <?= count($daftarBuku) === 0 ? 'disabled' : '' ?>>Ajukan Pinjam</button>
                        </div>
                    </form>
                </section>

                <section class="panel-box">
                    <h2 class="panel-title"><?= $bolehKelola ? 'Daftar Transaksi Peminjaman' : 'Peminjaman Saya' ?></h2>
                    <div class="table-responsive">
                        <table class="table-buku">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <?php if ($bolehKelola) : ?><th>Peminjam</th><?php endif; ?>
                                    <th>Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status</th>
                                    <?php if ($bolehKelola) : ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($daftarPeminjaman) > 0) : ?>
                                    <?php foreach ($daftarPeminjaman as $nomor => $peminjaman) : ?>
                                        <tr>
                                            <td><?= $nomor + 1 ?></td>
                                            <?php if ($bolehKelola) : ?>
                                                <td><?= htmlspecialchars($peminjaman['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <?php endif; ?>
                                            <td>
                                                <?= htmlspecialchars($peminjaman['judul'], ENT_QUOTES, 'UTF-8') ?><br>
                                                <small><?= htmlspecialchars($peminjaman['penulis'], ENT_QUOTES, 'UTF-8') ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($peminjaman['tanggal_pinjam'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= $peminjaman['tanggal_kembali'] ? htmlspecialchars($peminjaman['tanggal_kembali'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                            <td><?= htmlspecialchars($labelStatus[$peminjaman['status']] ?? $peminjaman['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <?php if ($bolehKelola) : ?>
                                                <td>
                                                    <?php if ($peminjaman['status'] === 'menunggu' || $peminjaman['status'] === 'disetujui') : ?>
                                                        <form action="pinjam.php" method="POST" style="display: flex; gap: 6px; align-items: center;">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                                            <input type="hidden" name="aksi" value="ubah_status">
                                                            <input type="hidden" name="peminjaman_id" value="<?= (int) $peminjaman['id'] ?>">
                                                            <select name="status" aria-label="Ubah status peminjaman">
                                                                <?php if ($peminjaman['status'] === 'menunggu') : ?>
                                                                    <option value="disetujui">Setujui</option>
                                                                    <option value="ditolak">Tolak</option>
                                                                <?php else : ?>
                                                                    <option value="dikembalikan">Tandai kembali</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <button type="submit" class="btn-tambah" style="padding: 8px 12px;">Simpan</button>
                                                        </form>
                                                    <?php else : ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="<?= $bolehKelola ? '7' : '5' ?>">Belum ada transaksi peminjaman.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>

</html>