<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['cart'])) {
    
    // Formdan gələn məlumatları təhlükəsizləşdirmək (Sanitize)
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    
    // YENİ: Əgər istifadəçi daxil olubsa, ID-sini alaq. Yoxsa NULL olacaq.
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : NULL;
    
    // Məbləği yenidən arxa planda hesablamaq (Təhlükəsizlik üçün - frontendə güvənmirik)
    $total_amount = 0;
    $ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $pdo->query("SELECT id, price FROM products WHERE id IN ($ids)");
    $products = [];
    
    while ($row = $stmt->fetch()) {
        $qty = $_SESSION['cart'][$row['id']];
        $total_amount += ($row['price'] * $qty);
        $row['quantity'] = $qty;
        $products[] = $row; // Sonra order_items cədvəlinə yazmaq üçün saxlayırıq
    }
    
    try {
        $pdo->beginTransaction(); // Transaction başlat ki, yarıda qırılmasın
        
        // DƏYİŞDİRİLDİ: Sifarişi orders cədvəlinə yazarkən user_id-ni də göndəririk
        $order_stmt = $pdo->prepare("INSERT INTO orders (user_id, customer_name, phone, address, total_amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $order_stmt->execute([$user_id, $customer_name, $phone, $address, $total_amount]);
        
        $order_id = $pdo->lastInsertId();
        
        // 2. Sifarişin içindəki məhsulları "order_items" cədvəlinə yazırıq
        $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        foreach ($products as $prod) {
            $item_stmt->execute([$order_id, $prod['id'], $prod['quantity'], $prod['price']]);
        }
        
        $pdo->commit(); // Hər şey uğurludursa bazaya təsdiqlə
        
        // Səbəti boşalt
        unset($_SESSION['cart']);
        
        // Təşəkkür və uğur mesajı ilə ana səhifəyə qaytar (Sonra bura xüsusi Təşəkkür səhifəsi yığarıq)
        header("Location: ../index.php?msg=order_success");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Sifariş xətası: " . $e->getMessage());
    }
} else {
    // Səbət boşdursa və ya qanunsuz yolla girilibsə qaytar
    header("Location: ../index.php");
    exit;
}
?>