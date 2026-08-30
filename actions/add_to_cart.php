<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    
    $product_id = (int)$_POST['product_id'];
    
    // Geri dönəcəyi ünvanı tapır (Gəlməyibsə ana səhifəyə atır)
    $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : '../index.php';
    
    // Default olaraq 1 ədəd atırıq (əgər fərqli say gəlməyibsə)
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product && $product['stock'] > 0) {
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $new_qty = $_SESSION['cart'][$product_id] + $quantity;
            
            // Stokdan çox səbətə atmağa icazə vermirik
            if ($new_qty <= $product['stock']) {
                $_SESSION['cart'][$product_id] = $new_qty;
            } else {
                $_SESSION['cart'][$product_id] = $product['stock'];
            }
        } else {
            // Məhsul səbətdə yoxdursa, ilk dəfə əlavə edirik
            // Əgər istənilən say stokdan çoxdursa, stoku qədər əlavə edirik
            $_SESSION['cart'][$product_id] = ($quantity <= $product['stock']) ? $quantity : $product['stock'];
        }
    }

    header("Location: " . $return_url);
    exit;
    
} else {
    // Kənar müdaxilə və ya səhv sorğu olarsa
    header("Location: ../index.php");
    exit;
}
?>