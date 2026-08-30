<?php
require_once 'config/db.php';

// Məhsulu səbətdən silmək məntiqi
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
    // Səbətdəki ID-ləri vergüllə ayırıb (1,2,3) SQL sorğusuna veririk
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

<main class="flex-1 bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">Səbətiniz</h1>
            <p class="text-gray-500 mt-2">Seçdiyiniz məhsulları buradan idarə edə bilərsiniz.</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-cart-shopping text-4xl text-gray-300"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Səbətiniz boşdur</h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Hazırda səbətinizdə heç bir məhsul yoxdur. İxlas Telekom-un geniş çeşidli məhsulları ilə tanış olmaq üçün ana səhifəyə qayıdın.</p>
                <a href="index.php" class="inline-block bg-ixlas-600 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-ixlas-700 transition-colors shadow-lg shadow-ixlas-500/30">
                    Alış-verişə Başla
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Səbət Elementləri -->
                <div class="lg:w-2/3 space-y-4">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-6 relative group">
                        
                        <div class="w-24 h-24 bg-gray-50 rounded-xl flex items-center justify-center text-4xl">
                            <?= htmlspecialchars($item['image_url']) ?>
                        </div>
                        
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 text-lg mb-1"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-sm text-gray-500 mb-4">Stokda: <?= $item['stock'] ?> ədəd</p>
                            <div class="flex items-center gap-4">
                                <span class="bg-ixlas-50 text-ixlas-700 px-3 py-1 rounded-lg text-sm font-bold">Say: <?= $item['quantity'] ?></span>
                                <span class="font-extrabold text-gray-900"><?= number_format($item['price'], 2) ?> ₼</span>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <p class="text-xs text-gray-400 mb-1">Cəmi</p>
                            <p class="font-extrabold text-xl text-ixlas-600 mb-4"><?= number_format($item['subtotal'], 2) ?> ₼</p>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="remove_item">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Sil">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                        
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="lg:w-1/3">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 sticky top-24">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Sifarişin xülasəsi</h3>
                        
                        <div class="space-y-4 text-sm text-gray-600 border-b border-gray-100 pb-6 mb-6">
                            <div class="flex justify-between">
                                <span>Məhsulların dəyəri</span>
                                <span class="font-bold text-gray-900"><?= number_format($total_price, 2) ?> ₼</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Çatdırılma</span>
                                <span class="text-ixlas-600 font-bold">Pulsuz</span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-end mb-8">
                            <span class="text-gray-900 font-bold">Yekun Məbləğ</span>
                            <span class="text-3xl font-extrabold text-ixlas-600"><?= number_format($total_price, 2) ?> ₼</span>
                        </div>
                        
                        <a href="checkout.php" class="block w-full text-center bg-ixlas-600 text-white font-bold py-4 rounded-xl hover:bg-ixlas-700 transition-colors shadow-lg shadow-ixlas-500/30">
                            Sifarişi Rəsmiləşdir
                        </a>
                        <p class="text-xs text-center text-gray-400 mt-4"><i class="fa-solid fa-shield-halved mr-1"></i> Sifarişiniz təhlükəsiz şəkildə qorunur</p>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>