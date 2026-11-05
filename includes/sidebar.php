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
    ['label' => 'Logout',      'dir' => 'auth',  'file' => 'logout.php',  'role' => ['admin', 'petugas', 'anggota']],
];
?>

<aside class="sidebar">
    <div class="sidebar-head">
        <a href="<?= $base ?>/user/home.php" class="logo">Libratech.</a>
        <button type="button" class="sidebar-toggle" aria-expanded="false" aria-controls="sidebar-menu" aria-label="Buka/tutup menu">
            <span></span><span></span><span></span>
        </button>
    </div>

    <div id="sidebar-menu" class="sidebar-body">
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
    </div>
</aside>

<script>
// Roll menu sidebar di mobile
(function () {
    var btn  = document.querySelector('.sidebar-toggle');
    var menu = document.getElementById('sidebar-menu');
    if (!btn || !menu) return;

    btn.addEventListener('click', function () {
        var buka = menu.classList.toggle('terbuka');
        btn.setAttribute('aria-expanded', buka ? 'true' : 'false');
    });

    // Tutup lagi setelah pilih menu
    menu.querySelectorAll('nav a').forEach(function (a) {
        a.addEventListener('click', function () {
            menu.classList.remove('terbuka');
            btn.setAttribute('aria-expanded', 'false');
        });
    });
})();
</script>