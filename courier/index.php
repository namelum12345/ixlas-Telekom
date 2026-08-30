<?php
session_start();
require_once '../config/db.php';

// Təhlükəsizlik: Yalnız kuryerlər girə bilər
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'courier') {
    header("Location: ../login.php");
    exit;
}

// Kuryer əməliyyatları (Status dəyişmə)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    
    if ($_POST['action'] === 'take_delivery') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'courier' WHERE id = ?");
        $stmt->execute([$order_id]);
    } elseif ($_POST['action'] === 'mark_delivered') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'delivered' WHERE id = ?");
        $stmt->execute([$order_id]);
    }
    header("Location: index.php?msg=success");
    exit;
}

// Yalnız "Təsdiqlənmiş" (Götürülməyi gözləyən) və ya "Kuryerdə" (Özündə olan) sifarişləri gətir
$orders_stmt = $pdo->query("SELECT * FROM orders WHERE status IN ('approved', 'courier') ORDER BY created_at ASC");
$orders = $orders_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuryer Paneli - İxlas Telekom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 500: '#22c55e', 600: '#16a34a', 700: '#15803d' } } } } }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <header class="bg-ixlas-700 text-white p-4 shadow-md sticky top-0 z-50 flex justify-between items-center">
        <div>
            <h1 class="font-bold text-lg">Kuryer Paneli</h1>
            <p class="text-xs text-ixlas-200"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
        </div>
        <a href="../actions/logout.php" class="bg-ixlas-800 p-2 rounded-lg text-sm hover:bg-ixlas-900 transition-colors">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Çıxış
        </a>
    </header>

    <main class="p-4 pb-20 max-w-md mx-auto">
        <?php if(isset($_GET['msg'])): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm font-bold text-center border border-green-200">
                Uğurla güncəlləndi!
            </div>
        <?php endif; ?>

        <h2 class="text-gray-500 font-bold uppercase text-xs mb-4">Aktiv Çatdırılmalar</h2>
        
        <?php if(empty($orders)): ?>
            <div class="bg-white p-8 rounded-2xl text-center shadow-sm">
                <i class="fa-solid fa-mug-hot text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 font-medium">Hazırda heç bir çatdırılma yoxdur. İstirahət edə bilərsiniz.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach($orders as $order): ?>
                    <div class="bg-white p-4 rounded-2xl shadow-sm border <?= $order['status'] == 'courier' ? 'border-ixlas-400' : 'border-gray-200' ?>">
                        
                        <div class="flex justify-between items-start border-b border-gray-100 pb-3 mb-3">
                            <div>
                                <span class="text-xs font-bold text-gray-400">Sifariş #ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                <h3 class="font-bold text-gray-900 text-lg"><?= number_format($order['total_amount'], 2) ?> ₼</h3>
                            </div>
                            <?php if($order['status'] == 'approved'): ?>
                                <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-1 rounded">Anbarda Gözləyir</span>
                            <?php else: ?>
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-1 rounded animate-pulse">Sizdədir (Yolda)</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <p class="text-sm text-gray-700 flex items-start gap-2">
                                <i class="fa-solid fa-user text-gray-400 mt-1"></i> 
                                <span class="font-medium"><?= htmlspecialchars($order['customer_name']) ?></span>
                            </p>
                            <p class="text-sm text-gray-700 flex items-start gap-2">
                                <i class="fa-solid fa-phone text-gray-400 mt-1"></i> 
                                <a href="tel:<?= htmlspecialchars($order['phone']) ?>" class="text-ixlas-600 font-bold underline"><?= htmlspecialchars($order['phone']) ?></a>
                            </p>
                            <p class="text-sm text-gray-700 flex items-start gap-2 bg-gray-50 p-2 rounded-lg">
                                <i class="fa-solid fa-location-dot text-red-400 mt-1"></i> 
                                <span><?= htmlspecialchars($order['address']) ?></span>
                            </p>
                        </div>
                        
                        <div class="pt-3 border-t border-gray-100">
                            <?php if($order['status'] == 'approved'): ?>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="action" value="take_delivery">
                                    <button type="submit" class="w-full bg-ixlas-50 text-ixlas-600 font-bold py-3 rounded-xl border border-ixlas-200 hover:bg-ixlas-100 transition-colors">
                                        Bağlamanı Anbardan Götür
                                    </button>
                                </form>
                            <?php elseif($order['status'] == 'courier'): ?>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="action" value="mark_delivered">
                                    <button type="submit" class="w-full bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-black transition-colors flex justify-center items-center gap-2">
                                        <i class="fa-solid fa-check-double"></i> Çatdırıldı kimi işarələ
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>