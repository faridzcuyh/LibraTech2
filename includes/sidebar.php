<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi otomatis halaman aktif dari nama file yang sedang dirender
$menu_aktif = basename($_SERVER['PHP_SELF'], '.php');
?>

<aside class="sidebar">
    <a href="home.php" class="logo">Libratech.</a>

    <nav>
        <a href="home.php" class="<?= $menu_aktif === 'home' ? 'active' : '' ?>">Home</a>
        <a href="library.php" class="<?= $menu_aktif === 'library' ? 'active' : '' ?>">Library</a>
        <a href="pinjam.php" class="<?= $menu_aktif === 'pinjam' ? 'active' : '' ?>">Pinjam</a>
    </nav>

    <div class="user-card">
        <div class="avatar"></div>
        <div class="user-info">
            <p class="user-name"><?= htmlspecialchars($_SESSION['nama']) ?></p>
            <p class="user-role"><?= htmlspecialchars($_SESSION['role']) ?></p>
        </div>
    </div>
</aside>
