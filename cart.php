<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_item') {
    $remove_id = (int)$_POST['product_id'];
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    header("Location: cart.php");
    exit;
}

require_once 'includes/header.php';

$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    
    while ($row = $stmt->fetch()) {
        $qty = $_SESSION['cart'][$row['id']];
        $row['quantity'] = $qty;
        $row['subtotal'] = $row['price'] * $qty;
        $total_price += $row['subtotal'];
        $cart_items[] = $row;
    }
}
?>

<main class="flex-1 bg-gray-50 py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Səbətiniz</h1>
            <p class="text-sm md:text-base text-gray-500 mt-1 md:mt-2">Seçdiyiniz məhsulları buradan idarə edə bilərsiniz.</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="bg-white rounded-3xl p-8 md:p-12 text-center shadow-sm border border-gray-100">
                <div class="w-20 h-20 md:w-24 md:h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-cart-shopping text-3xl md:text-4xl text-gray-300"></i>
                </div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-3 md:mb-4">Səbətiniz boşdur</h2>
                <p class="text-sm md:text-base text-gray-500 mb-8 max-w-md mx-auto">Hazırda səbətinizdə heç bir məhsul yoxdur. İxlas Telekom-un geniş çeşidli məhsulları ilə tanış olmaq üçün ana səhifəyə qayıdın.</p>
                <a href="index.php" class="inline-block bg-ixlas-600 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-ixlas-700 transition-colors shadow-lg shadow-ixlas-500/30">
                    Alış-verişə Başla
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-6 md:gap-8">
                
                <div class="lg:w-2/3 space-y-4">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-4 sm:gap-6 relative group">
                        
                        <!-- Şəkil və Əsas Məlumatlar (Hər zaman yan-yana) -->
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-50 rounded-xl flex items-center justify-center overflow-hidden shrink-0">
                                <?php if(strpos($item['image_url'], 'uploads/') !== false || filter_var($item['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-3xl sm:text-4xl"><?= htmlspecialchars($item['image_url']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 text-sm sm:text-lg mb-1 line-clamp-2"><?= htmlspecialchars($item['name']) ?></h3>
                                <p class="text-[11px] sm:text-sm text-gray-500 mb-2 sm:mb-3">Stokda: <?= $item['stock'] ?> ədəd</p>
                                <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                                    <span class="bg-ixlas-50 text-ixlas-700 px-2.5 sm:px-3 py-1 rounded-lg text-xs sm:text-sm font-bold">Say: <?= $item['quantity'] ?></span>
                                    <span class="font-extrabold text-gray-900 text-sm sm:text-base"><?= number_format($item['price'], 2) ?> ₼</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cəmi Məbləğ və Silmə Düyməsi (Telefonda aşağıda, Kompüterdə sağda) -->
                        <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center border-t sm:border-t-0 border-gray-100 pt-4 sm:pt-0 shrink-0 mt-2 sm:mt-0">
                            <div class="text-left sm:text-right">
                                <p class="text-[10px] sm:text-xs text-gray-400 mb-0.5 sm:mb-1 uppercase font-bold tracking-wider">Cəmi</p>
                                <p class="font-extrabold text-lg sm:text-xl text-ixlas-600 sm:mb-3"><?= number_format($item['subtotal'], 2) ?> ₼</p>
                            </div>
                            
                            <form method="POST" class="m-0">
                                <input type="hidden" name="action" value="remove_item">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center" title="Sil">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                        
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="lg:w-1/3">
                    <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100 sticky top-24">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-5 sm:mb-6">Sifarişin xülasəsi</h3>
                        
                        <div class="space-y-3 sm:space-y-4 text-sm text-gray-600 border-b border-gray-100 pb-5 sm:pb-6 mb-5 sm:mb-6">
                            <div class="flex justify-between">
                                <span>Məhsulların dəyəri</span>
                                <span class="font-bold text-gray-900"><?= number_format($total_price, 2) ?> ₼</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Çatdırılma</span>
                                <span class="text-ixlas-600 font-bold">Pulsuz</span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-end mb-6 sm:mb-8">
                            <span class="text-gray-900 font-bold text-sm sm:text-base">Yekun Məbləğ</span>
                            <span class="text-2xl sm:text-3xl font-extrabold text-ixlas-600"><?= number_format($total_price, 2) ?> ₼</span>
                        </div>
                        
                        <a href="checkout.php" class="block w-full text-center bg-ixlas-600 text-white font-bold py-3.5 sm:py-4 rounded-xl hover:bg-ixlas-700 transition-colors shadow-lg shadow-ixlas-500/30 text-sm sm:text-base">
                            Sifarişi Rəsmiləşdir
                        </a>
                        <p class="text-[11px] sm:text-xs text-center text-gray-400 mt-4"><i class="fa-solid fa-shield-halved mr-1"></i> Sifarişiniz təhlükəsiz şəkildə qorunur</p>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>