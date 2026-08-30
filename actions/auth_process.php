<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Qeydiyyat prosesi
    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Şifrə uyğunluğunu yoxla (Backend təhlükəsizliyi)
        if ($password !== $confirm_password) {
            header("Location: ../login.php?error=password_mismatch");
            exit;
        }

        // Nömrənin bazada olub-olmadığını yoxla
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            header("Location: ../login.php?error=exists");
            exit;
        }

        // Şifrəni təhlükəsizləşdir və bazaya yaz
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_stmt = $pdo->prepare("INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, 'customer')");
        
        try {
            $insert_stmt->execute([$name, $phone, $hashed_password]);
            
            // Avtomatik daxil olmaq üçün məlumatları sessiyaya atırıq
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'customer';
            
            // Qeydiyyat bitdikdən sonra müştərini ana səhifəyə (vitrinə) göndəririk
            header("Location: ../index.php");
            exit;
        } catch (Exception $e) {
            header("Location: ../login.php?error=sys");
            exit;
        }
    } 
    // Giriş prosesi
    elseif ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        // Nömrə tapıldı və şifrə düzgündürsə (password_verify hash yoxlayır)
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Roluna uyğun avtomatik yönləndirmə
            if ($user['role'] === 'superadmin' || $user['role'] === 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($user['role'] === 'courier') {
                header("Location: ../courier/index.php");
            } else {
                // Adi müştəridirsə alış-verişə davam etsin
                header("Location: ../index.php");
            }
            exit;
        } else {
            // Şifrə və ya nömrə səhvdirsə
            header("Location: ../login.php?error=wrong");
            exit;
        }
    }
} else {
    // Kənar müdaxilə olarsa loginə qaytar
    header("Location: ../login.php");
    exit;
}