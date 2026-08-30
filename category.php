<?php
require_once 'config/db.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if(!$category) {
    header("Location: index.php");
    exit;
}

$prod_stmt = $pdo->prepare("SELECT * FROM products WHERE (category_id = ? OR category_id IN (SELECT id FROM categories WHERE parent_id = ?)) AND is_active = 1 AND stock > 0");
$prod_stmt->execute([$category_id, $category_id]);
$products = $prod_stmt->fetchAll();

include 'includes/header.php';
?>

<main class="flex-1 max-w-7xl mx-auto px-4 py-8 w-full">
    
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="index.php" class="hover:text-ixlas-600"><i class="fa-solid fa-house"></i></a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-gray-900 font-medium"><?= htmlspecialchars($category['name']) ?></span>
    </div>

    <div class="flex items-center gap-3 mb-8">
        <div class="w-12 h-12 rounded-xl bg-ixlas-100 text-ixlas-600 flex items-center justify-center text-2xl">
            <i class="fa-solid <?= htmlspecialchars($category['icon'] ?? 'fa-box') ?>"></i>
        </div>
        <h2 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($category['name']) ?></h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        <?php foreach($products as $product): ?>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-xl transition-all group relative flex flex-col h-full">
                <!-- Şəkil Məntiqi -->
                <a href="product.php?id=<?= $product['id'] ?>" class="block mb-4 overflow-hidden rounded-xl bg-gray-50 aspect-square flex items-center justify-center relative">
                    <?php if(strpos($product['image_url'], 'uploads/') !== false || filter_var($product['image_url'], FILTER_VALIDATE_URL)): ?>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-300">
                    <?php else: ?>
                        <span class="text-6xl transform group-hover:scale-110 transition-transform duration-300"><?= htmlspecialchars($product['image_url']) ?></span>
                    <?php endif; ?>
                </a>
                
                <div class="flex-1 flex flex-col">
                    <a href="product.php?id=<?= $product['id'] ?>">
                        <h3 class="font-bold text-gray-800 text-sm md:text-base leading-tight mb-2 group-hover:text-ixlas-600 transition-colors line-clamp-2"><?= htmlspecialchars($product['name']) ?></h3>
                    </a>
                    <div class="mt-auto">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-lg md:text-xl font-extrabold text-ixlas-600"><?= number_format($product['price'], 2) ?> ₼</span>
                        </div>
                        <form action="actions/add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="return_url" value="../category.php?id=<?= $category_id ?>">
                            <button type="submit" class="w-full bg-ixlas-600 hover:bg-ixlas-700 text-white font-bold py-2.5 rounded-xl text-sm transition-colors shadow-lg shadow-ixlas-500/30 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cart-plus"></i> Səbətə at
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($products)): ?>
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-gray-100">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-3xl text-gray-400 mx-auto mb-4">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Məhsul tapılmadı</h3>
                <p class="text-gray-500">Bu kateqoriyada hal-hazırda aktiv məhsul yoxdur.</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php include 'includes/footer.php'; ?>