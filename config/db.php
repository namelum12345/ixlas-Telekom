<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();

// Avtomatik yaradılmış config faylı
$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'ixlas_telekom_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("<b>Bağlantı xətası:</b> " . $e->getMessage());
}
?>