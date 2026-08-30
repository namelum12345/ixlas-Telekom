<?php
session_start();

// Bütün sessiya dəyişənlərini təmizləyirik
session_unset();

// Sessiyanı tamamilə məhv edirik
session_destroy();

// İstifadəçini ana səhifəyə yönləndiririk
header("Location: ../index.php");
exit;
?>