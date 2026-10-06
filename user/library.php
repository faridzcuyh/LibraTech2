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
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="content">
        <h1>Library</h1>
        <p>Selamat datang di halaman Library.</p>
    </main>
</body>
</html>