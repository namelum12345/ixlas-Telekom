<?php
session_start();
require_once '../config/db.php';
require_once '../config/auth_check.php';

require_admin();

// Ağıllı Yeniləmə: Sifarişlər cədvəlinə kuryer ID-si üçün xana əlavə edirik
try {
    $pdo->exec("ALTER TABLE orders ADD COLUMN courier_id INT DEFAULT NULL");
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = (int)$_POST['order_id'];
    
    // TƏSDİQLƏ VƏ KURYERƏ VER (STOK AZALIR)
    if ($_POST['action'] === 'approve_and_assign') {
        $courier_id = !empty($_POST['courier_id']) ? (int)$_POST['courier_id'] : NULL;
        
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE orders SET status = 'courier', courier_id = ? WHERE id = ? AND status = 'pending'");
            $stmt->execute([$courier_id, $order_id]);
            
            if ($stmt->rowCount() > 0) {
                // Stoku Azalt
                $items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $items->execute([$order_id]);
                $update_stock = $pdo->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
                foreach($items->fetchAll() as $item) {
                    $update_stock->execute([$item['quantity'], $item['product_id']]);
                }
            }
            $pdo->commit();
            header("Location: orders.php?msg=approved");
            exit;
        } catch (Exception $e) { $pdo->rollBack(); }
    }
    
    // LƏĞV ET (STOK GERİ QAYIDIR)
    if ($_POST['action'] === 'cancel') {
        try {
            $pdo->beginTransaction();
            $check = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
            $check->execute([$order_id]);
            $current_status = $check->fetchColumn();
            
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$order_id]);
            
            // Əgər təsdiqlənmişdisə, stoku geri qaytar
            if ($current_status !== 'pending' && $current_status !== 'cancelled') {
                $items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $items->execute([$order_id]);
                $restore_stock = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                foreach($items->fetchAll() as $item) {
                    $restore_stock->execute([$item['quantity'], $item['product_id']]);
                }
            }
            $pdo->commit();
            header("Location: orders.php?msg=cancelled");
            exit;
        } catch (Exception $e) { $pdo->rollBack(); }
    }
}

// Bütün Sifarişləri gətir
$orders = $pdo->query("
    SELECT o.*, u.name as courier_name 
    FROM orders o 
    LEFT JOIN users u ON o.courier_id = u.id 
    ORDER BY o.created_at DESC
")->fetchAll();

// Kuryerləri seçmək üçün siyahı
$couriers = $pdo->query("SELECT id, name FROM users WHERE role = 'courier'")->fetchAll();

// AJAX üçün Sifariş Detallarını gətirən kod bloku
if(isset($_GET['ajax_order_id'])) {
    $oid = (int)$_GET['ajax_order_id'];
    $stmt = $pdo->prepare("SELECT oi.quantity, oi.price, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([$oid]);
    $items = $stmt->fetchAll();
    $html = '<ul class="space-y-3">';
    foreach($items as $i) {
        $html .= '<li class="flex justify-between items-center bg-gray-50 p-3 rounded-lg"><span class="font-bold text-gray-800">'.$i['quantity'].'x '.$i['name'].'</span><span class="text-ixlas-600 font-extrabold">'.number_format($i['price']*$i['quantity'],2).' ₼</span></li>';
    }
    $html .= '</ul>';
    echo $html;
    exit;
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>Sifarişlər - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 900: '#14532d' } } } } }
    </script>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-y-auto relative">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 z-0 sticky top-0">
            <h2 class="font-bold text-gray-800">Satış və Sifarişlər</h2>
        </header>

        <div class="p-8">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Sifarişlərin İdarə Edilməsi</h1>
                    <p class="text-sm text-gray-500 mt-1">Sifarişi təsdiqlədikdə stok azalır və bağlama seçilmiş kuryerə tapşırılır.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Sifariş / Tarix</th>
                                <th class="px-6 py-4">Müştəri / Əlaqə</th>
                                <th class="px-6 py-4">Məbləğ</th>
                                <th class="px-6 py-4">Status & Kuryer</th>
                                <th class="px-6 py-4 text-right">İdarəetmə</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach($orders as $order): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-900">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></p>
                                        <p class="text-[10px] text-gray-400 mt-1"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-800"><?= htmlspecialchars($order['customer_name']) ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5 truncate max-w-[200px]"><?= htmlspecialchars($order['address']) ?></p>
                                    </td>
                                    <td class="px-6 py-4 font-extrabold text-ixlas-700"><?= number_format($order['total_amount'], 2) ?> ₼</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1 items-start">
                                            <?php if($order['status'] == 'pending'): ?>
                                                <span class="bg-yellow-50 text-yellow-700 border border-yellow-200 py-1 px-3 rounded-full text-[10px] font-bold">Yeni (Gözləyir)</span>
                                            <?php elseif($order['status'] == 'courier'): ?>
                                                <span class="bg-blue-50 text-blue-700 border border-blue-200 py-1 px-3 rounded-full text-[10px] font-bold flex items-center gap-1"><i class="fa-solid fa-motorcycle"></i> <?= htmlspecialchars($order['courier_name'] ?: 'Kuryerdə') ?></span>
                                            <?php elseif($order['status'] == 'delivered'): ?>
                                                <span class="bg-green-50 text-green-700 border border-green-200 py-1 px-3 rounded-full text-[10px] font-bold"><i class="fa-solid fa-check"></i> Çatdırıldı</span>
                                            <?php elseif($order['status'] == 'cancelled'): ?>
                                                <span class="bg-red-50 text-red-700 border border-red-200 py-1 px-3 rounded-full text-[10px] font-bold">Ləğv edilib</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            
                                            <!-- WhatsApp ilə Birbaşa Əlaqə -->
                                            <?php 
                                                $wa_number = preg_replace('/[^0-9]/', '', $order['phone']);
                                                // Əgər nömrə 0 ilə başlayırsa 994 ilə əvəz et (Azərbaycan üçün)
                                                if(substr($wa_number, 0, 1) === '0') $wa_number = '994' . substr($wa_number, 1);
                                                $wa_text = urlencode("Salam " . $order['customer_name'] . ", İxlas Telekomdan narahat edirik. #ORD-" . str_pad($order['id'], 4, '0', STR_PAD_LEFT) . " nömrəli sifarişinizlə bağlı...");
                                            ?>
                                            <a href="https://wa.me/<?= $wa_number ?>?text=<?= $wa_text ?>" target="_blank" class="w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-600 hover:text-white transition-colors flex items-center justify-center" title="WhatsApp-da yaz">
                                                <i class="fa-brands fa-whatsapp text-lg"></i>
                                            </a>

                                            <!-- Sifarişə Baxış Modalını açır -->
                                            <button onclick="openDetailsModal(<?= $order['id'] ?>)" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" title="Detallara Bax">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            <?php if($order['status'] == 'pending'): ?>
                                                <!-- Təsdiqlə və Kuryer Təyin et (Form) -->
                                                <form method="POST" class="inline flex items-center gap-2">
                                                    <input type="hidden" name="action" value="approve_and_assign">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <select name="courier_id" class="text-xs border border-gray-200 rounded-lg p-1.5 outline-none focus:border-ixlas-500 bg-white max-w-[100px]">
                                                        <option value="">Kuryer Seç</option>
                                                        <?php foreach($couriers as $cr): ?><option value="<?= $cr['id'] ?>"><?= htmlspecialchars($cr['name']) ?></option><?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="bg-ixlas-600 text-white text-[10px] font-bold px-3 py-2 rounded-lg hover:bg-ixlas-700 transition-colors">Təsdiqlə</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <!-- Ləğv et düyməsi (Yalnız çatdırılmamışsa) -->
                                            <?php if($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                                                <form method="POST" class="inline" onsubmit="return confirm('Sifarişi ləğv etsəniz, stok geri qayıdacaq. Əminsiniz?');">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center" title="Sifarişi Ləğv Et"><i class="fa-solid fa-xmark"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Sifariş Detalları Modalı -->
    <div id="details-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-lg">Sifariş Detalları</h3>
                <button onclick="document.getElementById('details-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6" id="modal-content">
                <p class="text-center text-gray-500 py-4"><i class="fa-solid fa-spinner fa-spin text-2xl"></i> Yüklənir...</p>
            </div>
            <div class="p-6 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                <button onclick="document.getElementById('details-modal').classList.add('hidden')" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition-colors">Bağla</button>
            </div>
        </div>
    </div>

    <script>
        function openDetailsModal(orderId) {
            document.getElementById('details-modal').classList.remove('hidden');
            document.getElementById('modal-content').innerHTML = '<p class="text-center text-gray-500 py-8"><i class="fa-solid fa-spinner fa-spin text-3xl"></i></p>';
            
            fetch('orders.php?ajax_order_id=' + orderId)
                .then(response => response.text())
                .then(data => { document.getElementById('modal-content').innerHTML = data; });
        }
    </script>
</body>
</html>