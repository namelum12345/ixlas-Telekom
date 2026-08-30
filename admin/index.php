<?php
session_start();
require_once '../config/db.php';
require_once '../config/auth_check.php';

require_admin();

try {
    $stats = [
        'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0,
        'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn() ?: 0,
        'total_sales' => $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'")->fetchColumn() ?: 0,
        'total_customers' => $pdo->query("SELECT COUNT(DISTINCT phone) FROM orders")->fetchColumn() ?: 0
    ];
} catch (PDOException $e) {
    $stats = ['total_orders' => 0, 'pending_orders' => 0, 'total_sales' => 0, 'total_customers' => 0];
}

try {
    $low_stock_products = $pdo->query("SELECT id, name, stock FROM products WHERE stock < 3 AND is_active = 1")->fetchAll();
} catch (PDOException $e) {
    $low_stock_products = [];
}

try {
    $recent_orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    $recent_orders = [];
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>İdarə Paneli - İxlas Telekom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 900: '#14532d' } } } } }
    </script>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 z-0 sticky top-0">
            <h2 class="font-bold text-gray-800">İdarə Paneli</h2>
            <div class="flex items-center gap-4">
                <span class="text-sm font-bold text-gray-600 bg-gray-100 py-1.5 px-4 rounded-full border border-gray-200"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?> (<?= htmlspecialchars($_SESSION['user_role'] ?? 'admin') ?>)</span>
            </div>
        </header>

        <div class="p-8">
            
            <?php if(!empty($low_stock_products)): ?>
                <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 text-xl"></i>
                        <div>
                            <h3 class="font-bold text-red-800">Kritik Stok Xəbərdarlığı!</h3>
                            <p class="text-sm text-red-600">Aşağıdakı məhsulların stoku bitmək üzrədir. Anbarı yeniləyin:</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <?php foreach($low_stock_products as $lsp): ?>
                                    <span class="bg-white border border-red-200 text-red-700 text-xs px-2 py-1 rounded font-bold shadow-sm">
                                        <?= htmlspecialchars($lsp['name']) ?> (<?= $lsp['stock'] ?> ədəd qalıb)
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-xl bg-ixlas-50 text-ixlas-600 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Ümumi Sifariş</p>
                        <p class="text-3xl font-extrabold text-gray-900"><?= $stats['total_orders'] ?></p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-xl bg-yellow-50 text-yellow-500 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Gözləyən</p>
                        <p class="text-3xl font-extrabold text-gray-900"><?= $stats['pending_orders'] ?></p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Təsdiqlənmiş Gəlir</p>
                        <p class="text-3xl font-extrabold text-gray-900"><?= number_format($stats['total_sales'], 2) ?> ₼</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Müştərilər</p>
                        <p class="text-3xl font-extrabold text-gray-900"><?= $stats['total_customers'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-gray-900 text-lg">Son Sifarişlər</h3>
                    <a href="orders.php" class="text-sm text-ixlas-600 hover:text-ixlas-700 font-bold">Hamısına bax <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Sifariş No</th>
                                <th class="px-6 py-4">Müştəri</th>
                                <th class="px-6 py-4">Məbləğ</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Tarix</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach($recent_orders as $order): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td class="px-6 py-4 font-extrabold text-ixlas-700"><?= number_format($order['total_amount'], 2) ?> ₼</td>
                                    <td class="px-6 py-4">
                                        <?php if($order['status'] == 'pending'): ?>
                                            <span class="bg-yellow-50 text-yellow-700 border border-yellow-200 py-1 px-3 rounded-full text-xs font-bold">Gözləyir</span>
                                        <?php elseif($order['status'] == 'approved'): ?>
                                            <span class="bg-blue-50 text-blue-700 border border-blue-200 py-1 px-3 rounded-full text-xs font-bold">Təsdiqləndi</span>
                                        <?php elseif($order['status'] == 'courier'): ?>
                                            <span class="bg-ixlas-50 text-ixlas-600 border border-ixlas-200 py-1 px-3 rounded-full text-xs font-bold"><i class="fa-solid fa-motorcycle mr-1"></i> Kuryerdə</span>
                                        <?php elseif($order['status'] == 'delivered'): ?>
                                            <span class="bg-green-50 text-green-700 border border-green-200 py-1 px-3 rounded-full text-xs font-bold">Çatdırıldı</span>
                                        <?php elseif($order['status'] == 'cancelled'): ?>
                                            <span class="bg-red-50 text-red-700 border border-red-200 py-1 px-3 rounded-full text-xs font-bold">Ləğv edilib</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 text-xs font-medium"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recent_orders)): ?>
                                <tr><td colspan="5" class="text-center py-8 text-gray-500 font-medium">Heç bir sifariş yoxdur.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>