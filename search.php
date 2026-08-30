<?php
require_once 'config/db.php';

// Axtarış sorğusunu alırıq
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if (!empty($search_query)) {
    // Məhsulun adına görə axtarış edirik (Yalnız aktiv və stoku olanlar)
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? AND is_active = 1 AND stock > 0");
    $stmt->execute(['%' . $search_query . '%']);
    $products = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
    
    <!-- Başlıq və axtarış xülasəsi -->
    <div class="mb-10 text-center md:text-left">
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight mb-2">Axtarış Nəticələri</h1>
        <?php if (!empty($search_query)): ?>
            <p class="text-gray-500">"<span class="font-bold text-ixlas-600"><?= htmlspecialchars($search_query) ?></span>" sorğusu üçün <?= count($products) ?> məhsul tapıldı.</p>
        <?php else: ?>
            <p class="text-gray-500">Axtarış etmək üçün yuxarıdakı formadan istifadə edin.</p>
        <?php endif; ?>
    </div>

    <!-- Mobil Cihazlar Üçün Axtarış Formu (Ekranda böyük görünür) -->
    <div class="sm:hidden mb-8">
        <form action="search.php" method="GET" class="relative">
            <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Burada axtarın..." required class="w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-base focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none shadow-sm">
            <button type="submit" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-ixlas-600 text-xl">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        <?php foreach($products as $product): ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-xl transition-all group relative flex flex-col h-full">
            
            <a href="product.php?id=<?= $product['id'] ?>" class="block mb-4 overflow-hidden rounded-xl bg-gray-50 aspect-square flex items-center justify-center relative">
                <span class="text-6xl transform group-hover:scale-110 transition-transform duration-300"><?= $product['image_url'] ?></span>
            </a>
            
            <div class="flex-1 flex flex-col">
                <a href="product.php?id=<?= $product['id'] ?>">
                    <h3 class="font-bold text-gray-800 text-sm md:text-base leading-tight mb-2 group-hover:text-ixlas-600 transition-colors line-clamp-2">
                        <?= htmlspecialchars($product['name']) ?>
                    </h3>
                </a>
                <div class="mt-auto">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-lg md:text-xl font-extrabold text-ixlas-600"><?= number_format($product['price'], 2) ?> ₼</span>
                    </div>
                    <form action="actions/add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="return_url" value="../search.php?q=<?= urlencode($search_query) ?>">
                        <button type="submit" class="w-full bg-ixlas-600 text-white font-bold py-3.5 rounded-xl hover:bg-ixlas-700 transition-all hover:shadow-lg hover:shadow-ixlas-500/40 flex items-center justify-center gap-2">
                            Səbətə At <i class="fa-solid fa-cart-shopping"></i>
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
        <?php endforeach; ?>
        
        <?php if(empty($products) && !empty($search_query)): ?>
            <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center text-4xl text-gray-300 mx-auto mb-6">
                    <i class="fa-solid fa-magnifying-glass-minus"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Heç nə tapılmadı</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">Axtardığınız meyarlara uyğun məhsul tapılmadı. Fərqli sözlər yazaraq yenidən cəhd edin və ya kataloqa göz atın.</p>
                <a href="index.php" class="inline-block bg-ixlas-100 text-ixlas-700 font-bold px-8 py-3 rounded-xl hover:bg-ixlas-200 transition-colors">
                    Ana Səhifəyə Qayıt
                </a>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php include 'includes/footer.php'; ?>