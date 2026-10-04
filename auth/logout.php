<?php
session_start();

// Hapus semua variabel session
$_SESSION = [];
session_unset();

// Hancurkan session server
session_destroy();

// Tendang kembali ke halaman login
header("Location: login.php");
exit;
?>
