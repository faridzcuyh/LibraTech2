<?php
session_start();

// Hapus semua variabel session
$_SESSION = [];
session_unset();

// Hancurkan session server
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();

// Tendang kembali ke halaman login
header("Location: login.php");
exit;
?>
