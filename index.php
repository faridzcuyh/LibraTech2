<?php
session_start();
if (isset($_SESSION['sudah_login'])) {
    header("Location: user/home.php");
} else {
    header("Location: auth/login.php");
}
exit;
    
