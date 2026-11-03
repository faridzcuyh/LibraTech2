<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URI root aplikasi, misal '' (dserve dari root) atau '/LibraTech2' (dserve dari subfolder)
$base  = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$aktif = basename($_SERVER['SCRIPT_NAME']);
$role  = $_SESSION['role'] ?? 'anggota';

// Menu sidebar: 'dir' = folder penyimpan, 'role' = siapa yang boleh melihat
$menu = [
    ['label' => 'Home',        'dir' => 'user',  'file' => 'home.php',    'role' => ['admin', 'petugas', 'anggota']],
    ['label' => 'Library',     'dir' => 'user',  'file' => 'library.php', 'role' => ['admin', 'petugas', 'anggota']],
    ['label' => 'Pinjam',      'dir' => 'user',  'file' => 'pinjam.php',  'role' => ['admin', 'petugas', 'anggota']],
    ['label' => 'Kelola Buku', 'dir' => 'admin', 'file' => 'kelola.php',  'role' => ['admin', 'petugas']],
    ['label' => 'Logout',      'dir' => 'user',  'file' => 'logout.php',  'role' => ['admin', 'petugas', 'anggota']],
];
?>

<aside class="sidebar">
    <a href="<?= $base ?>/user/home.php" class="logo">Libratech.</a>

    <nav>
        <?php foreach ($menu as $item) : ?>
            <?php if (!in_array($role, $item['role'], true)) {
                continue;
            } ?>
            <a href="<?= $base ?>/<?= $item['dir'] ?>/<?= $item['file'] ?>" class="<?= $aktif === $item['file'] ? 'active' : '' ?>"><?= $item['label'] ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="user-card">
        <div class="avatar"></div>
        <div class="user-info">
            <p class="user-name"><?= htmlspecialchars($_SESSION['nama']) ?></p>
            <p class="user-role"><?= htmlspecialchars($_SESSION['role']) ?></p>
        </div>
    </div>
</aside>
