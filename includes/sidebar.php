<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<aside class="sidebar">
    <a href="home.php" class="logo">Libratech.</a>

    <nav>
        <a href="home.php" class="active">Home</a>
        <a href="library.php">Library</a>
        <a href="pinjam.php">Pinjam</a>
    </nav>

    <div class="user-card">
        <div class="avatar"></div>
        <div class="user-info">
            <p class="user-name"><?= htmlspecialchars($_SESSION['nama']) ?></p>
            <p class="user-role"><?= htmlspecialchars($_SESSION['role']) ?></p>
        </div>
    </div>
</aside>
