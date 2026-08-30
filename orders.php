<?php
session_start();
require_once '../config/db.php';

// Təsdiqləmə prosesi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    
    if ($_POST['action'] === 'approve') {
        try {
            $pdo->beginTransaction();
            
            // 1. Sifarişin statusunu yenilə
            $stmt = $pdo->prepare("UPDATE orders SET status = 'approved' WHERE id = ? AND status = 'pending'");
            $stmt->execute([$order_id]);
            
            if ($stmt->rowCount() > 0) {
                // 2. Sifarişdəki məhsulları tap və stoku azalt
                $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $items_stmt->execute([$order_id]);
                $items = $items_stmt->fetchAll();
                
                $update_stock = $pdo->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
                foreach($items as $item) {
                    $update_stock->execute([$item['quantity'], $item['product_id']]);
                }
            }
            
            $pdo->commit();
            header("Location: orders.php?msg=approved");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Xəta: " . $e->getMessage();
        }
    }
}

// Bütün sifarişləri (Gözləyən və Təsdiqlənənləri) gətir
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>Sifarişlər - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 600: '#16a34a', 700: '#15803d' } } } } }
    </script>
</head>
<body class="bg-gray-50 flex h-screen">

    <!-- Yenidən Sidebar (Qısa versiya) -->
    <aside class="w-64 bg-white border-r border-gray-200 hidden md:block">
        <div class="h-16 flex items-center px-6 border-b border-gray-200"><span class="text-xl font-bold text-ixlas-600">İxlas Admin</span></div>
        <nav class="p-4 space-y-2">
            <a href="index.php" class="block px-4 py-2.5 text-gray-600 hover:bg-gray-50 rounded-lg">Ümumi Panel</a>
            <a href="orders.php" class="block px-4 py-2.5 bg-ixlas-50 text-ixlas-700 rounded-lg font-medium">Sifarişlər</a>
        </nav>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto">
        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Sifarişlərin İdarə Edilməsi</h1>
                <p class="text-sm text-gray-500 mt-1">Sifarişləri təsdiqlədikdə məhsulların stoku avtomatik azalacaq.</p>
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'approved'): ?>
            <div class="mb-4 p-4 bg-green-50 text-green-700 border border-green-200 rounded-lg font-medium">
                Sifariş təsdiqləndi və stok uğurla azaldıldı!
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3">Sifariş No</th>
                        <th class="px-5 py-3">Müştəri / Ünvan</th>
                        <th class="px-5 py-3">Məbləğ</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Əməliyyat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($orders as $order): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-bold">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['phone']) ?>)</p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($order['address']) ?></p>
                            </td>
                            <td class="px-5 py-4 font-semibold"><?= number_format($order['total_amount'], 2) ?> ₼</td>
                            <td class="px-5 py-4">
                                <?php if($order['status'] == 'pending'): ?>
                                    <span class="bg-yellow-100 text-yellow-700 py-1 px-2 rounded text-xs font-medium border border-yellow-200">Gözləyir</span>
                                <?php else: ?>
                                    <span class="bg-blue-100 text-blue-700 py-1 px-2 rounded text-xs font-medium">Təsdiqlənib</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4">
                                <?php if($order['status'] == 'pending'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="bg-ixlas-50 text-ixlas-600 hover:bg-ixlas-600 hover:text-white border border-ixlas-200 py-1.5 px-3 rounded text-xs font-bold transition-colors">
                                            Təsdiqlə / Stoku Azalt
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs italic"><i class="fa-solid fa-check mr-1"></i> İcra olunub</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>