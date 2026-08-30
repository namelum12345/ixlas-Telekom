<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_admin() {
    if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'superadmin' && $_SESSION['user_role'] !== 'admin')) {
        // Əgər admin deyilsə, login səhifəsinə at
        header("Location: ../login.php");
        exit;
    }
}
?>