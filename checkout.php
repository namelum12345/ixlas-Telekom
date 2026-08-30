<?php
require_once 'config/db.php';

// Əgər səbət boşdursa, ana səhifəyə qaytar
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

require_once 'includes/header.php';

$total_price = 0;
$ids = implode(',', array_keys($_SESSION['cart']));
$stmt = $pdo->query("SELECT price, id FROM products WHERE id IN ($ids)");
while ($row = $stmt->fetch()) {
    $qty = $_SESSION['cart'][$row['id']];
    $total_price += ($row['price'] * $qty);
}
?>

<main class="flex-1 bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-extrabold text-gray-900">Sifarişi Tamamla</h1>
            <p class="text-gray-500 mt-2">Məlumatlarınızı daxil edin, qapıda ödəyin.</p>
        </div>

        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
            <form action="actions/checkout_process.php" method="POST">
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Ad və Soyad *</label>
                        <input type="text" name="customer_name" required placeholder="Məs: Cavidan Əliyev" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-200 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mobil Nömrə * (Əlaqə üçün)</label>
                        <input type="text" name="phone" required placeholder="055-123-45-67" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-200 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Çatdırılma Ünvanı *</label>
                        <textarea name="address" required rows="3" placeholder="Şəhər, Rayon, Küçə və Bina nömrəsi" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-200 outline-none transition-all resize-none"></textarea>
                    </div>
                </div>
                
                <div class="mt-8 pt-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Yekun Ödəniləcək Məbləğ</p>
                        <p class="text-3xl font-extrabold text-ixlas-600"><?= number_format($total_price, 2) ?> ₼</p>
                    </div>
                    
                    <button type="submit" class="w-full sm:w-auto bg-ixlas-600 text-white font-bold px-10 py-4 rounded-xl hover:bg-ixlas-700 transition-colors shadow-lg shadow-ixlas-500/30 flex items-center justify-center gap-2">
                        Sifarişi Təsdiqlə <i class="fa-solid fa-check"></i>
                    </button>
                </div>
                
            </form>
        </div>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>