<?php
require_once 'config/db.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.id as cat_id FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if(!$product) {
    header("Location: index.php");
    exit;
}

include 'includes/header.php';
?>

<main class="flex-1 max-w-7xl mx-auto px-4 py-8 w-full">
    
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
        <a href="index.php" class="hover:text-ixlas-600"><i class="fa-solid fa-house"></i></a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a href="category.php?id=<?= $product['cat_id'] ?>" class="hover:text-ixlas-600"><?= htmlspecialchars($product['category_name']) ?></a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-gray-900 font-medium line-clamp-1"><?= htmlspecialchars($product['name']) ?></span>
    </div>

    <div class="bg-white rounded-3xl p-6 md:p-10 border border-gray-100 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            
            <!-- Şəkil Məntiqi -->
            <div class="bg-gray-50 rounded-2xl aspect-square flex items-center justify-center overflow-hidden relative">
                <?php if(strpos($product['image_url'], 'uploads/') !== false || filter_var($product['image_url'], FILTER_VALIDATE_URL)): ?>
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <span class="text-9xl"><?= htmlspecialchars($product['image_url']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="flex flex-col justify-center">
                <span class="inline-block px-3 py-1 bg-ixlas-50 text-ixlas-600 rounded-full text-xs font-bold mb-4 w-max">
                    Stokda var (<?= $product['stock'] ?> ədəd)
                </span>
                
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 leading-tight">
                    <?= htmlspecialchars($product['name']) ?>
                </h1>
                
                <p class="text-gray-600 mb-8 leading-relaxed">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </p>
                
                <div class="text-4xl font-extrabold text-ixlas-600 mb-8">
                    <?= number_format($product['price'], 2) ?> ₼
                </div>
                
                <form action="actions/add_to_cart.php" method="POST" class="flex gap-4">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="return_url" value="../product.php?id=<?= $product['id'] ?>">
                    
                    <button type="submit" class="flex-1 bg-ixlas-600 hover:bg-ixlas-700 text-white font-bold py-4 rounded-xl text-lg transition-colors shadow-lg shadow-ixlas-500/30 flex items-center justify-center gap-3">
                        <i class="fa-solid fa-cart-plus"></i> İndi Səbətə At
                    </button>
                </form>

                <div class="grid grid-cols-2 gap-4 mt-10 pt-8 border-t border-gray-100">
                    <div class="flex items-center gap-3 text-sm font-medium text-gray-600">
                        <div class="w-10 h-10 rounded-full bg-ixlas-50 text-ixlas-600 flex items-center justify-center"><i class="fa-solid fa-truck-fast"></i></div>
                        Sürətli Çatdırılma
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium text-gray-600">
                        <div class="w-10 h-10 rounded-full bg-green-50 text-green-500 flex items-center justify-center"><i class="fa-solid fa-shield-halved"></i></div>
                        Rəsmi Zəmanət
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>