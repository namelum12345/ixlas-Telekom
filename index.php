<?php
// PHP 8+ xəbərdarlıqlarının qarşısını almaq üçün
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once 'config/db.php';

// Məhsulları çəkirik
$products_stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 AND stock > 0 ORDER BY id DESC LIMIT 12");
$products = $products_stmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- Hərəkətli dalğalar üçün CSS -->
<style>
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob { animation: blob 7s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
    .animation-delay-4000 { animation-delay: 4s; }
</style>

<main class="flex-1 w-full bg-gray-50">
    
    <!-- Hero Banner Bölməsi -->
    <div class="relative bg-ixlas-900 overflow-hidden pt-16 pb-32 lg:pb-40">
        <div class="absolute inset-0 z-0">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-ixlas-600 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
            <div class="absolute top-1/2 right-12 w-96 h-96 bg-ixlas-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-24 left-1/2 w-96 h-96 bg-ixlas-700 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob animation-delay-4000"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <span class="inline-block py-1 px-3 rounded-full bg-ixlas-800 text-ixlas-200 text-xs font-bold tracking-widest uppercase mb-6 border border-ixlas-700 shadow-sm">Premium Xidmət</span>
                    <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl mb-6 leading-tight">
                        Texnologiya İndi Daha <span class="text-transparent bg-clip-text bg-gradient-to-r from-ixlas-400 to-ixlas-200">Yaxındır</span>
                    </h1>
                    <p class="mt-4 text-lg sm:text-xl text-ixlas-100 max-w-3xl lg:max-w-none mx-auto lg:mx-0 mb-10 leading-relaxed">
                        Ən son model smartfonlar, ağıllı saatlar və qadjetlər rəsmi zəmanət və sərfəli qiymətlərlə İxlas Telekom-da.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="#products" class="bg-ixlas-500 hover:bg-ixlas-400 text-white font-bold py-4 px-8 rounded-xl transition-all shadow-[0_0_20px_rgba(34,197,94,0.4)] hover:shadow-[0_0_30px_rgba(34,197,94,0.6)] flex items-center justify-center gap-2">
                            Məhsullara Bax <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="hidden lg:block relative perspective-1000">
                    <div class="absolute inset-0 bg-gradient-to-tr from-ixlas-400 to-ixlas-600 rounded-[2.5rem] transform rotate-3 scale-105 opacity-40 blur-xl"></div>
                    <div class="relative bg-white/10 backdrop-blur-xl border border-white/20 rounded-[2.5rem] p-8 shadow-2xl transform -rotate-2 hover:rotate-0 transition-transform duration-500">
                        <img src="https://images.unsplash.com/photo-1605236453806-6ff36851218e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="iPhone Preview" class="rounded-2xl shadow-lg w-full object-cover h-80">
                        <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-2xl shadow-xl flex items-center gap-4 animate-bounce hover:animate-none">
                            <div class="w-12 h-12 bg-ixlas-100 text-ixlas-600 rounded-full flex items-center justify-center text-xl">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Rəsmi Zəmanət</p>
                                <p class="text-xs text-gray-500">1 İl Tam Zəmanət</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none z-0">
            <svg class="relative block w-full h-[60px] sm:h-[100px] lg:h-[120px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,123.82,200,115.82C240.28,111.18,278.43,89.5,321.39,56.44Z" fill="#ffffff"></path>
            </svg>
        </div>
    </div>
    
    <!-- Xüsusiyyətlər Bölməsi -->
    <div class="bg-white pb-16 pt-8 relative z-10 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="p-6">
                    <div class="w-16 h-16 mx-auto bg-ixlas-50 text-ixlas-600 rounded-full flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Sürətli Çatdırılma</h3>
                    <p class="text-gray-500 text-sm">Sifarişləriniz eyni gün ərzində qapınıza qədər çatdırılır. Ödənişi yalnız məhsulu təhvil aldıqda edirsiniz.</p>
                </div>
                <div class="p-6">
                    <div class="w-16 h-16 mx-auto bg-ixlas-50 text-ixlas-600 rounded-full flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">1 İl Rəsmi Zəmanət</h3>
                    <p class="text-gray-500 text-sm">Bütün məhsullarımıza istehsalçı tərəfindən rəsmi zəmanət verilir. Orijinallığa 100% zəmanət.</p>
                </div>
                <div class="p-6">
                    <div class="w-16 h-16 mx-auto bg-ixlas-50 text-ixlas-600 rounded-full flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">7/24 Müştəri Dəstəyi</h3>
                    <p class="text-gray-500 text-sm">Texniki və ya sifarişlə bağlı hər hansı sualınız yaranarsa, peşəkar komandamız hər zaman xidmətinizdədir.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Məhsullar Vitrini -->
    <div id="products" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="flex justify-between items-end mb-10">
            <div>
                <span class="inline-block py-1.5 px-4 rounded-full bg-ixlas-100 text-ixlas-700 text-[10px] font-bold tracking-widest uppercase mb-3 border border-ixlas-200">Vitrin</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Yeni Məhsullar</h2>
                <div class="w-16 h-1 bg-ixlas-500 mt-3 rounded-full"></div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            <?php foreach($products as $product): ?>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-xl transition-all group relative flex flex-col h-full">
                <!-- Şəkil Məntiqi (Yüklənmiş və ya Emoji) -->
                <a href="product.php?id=<?= $product['id'] ?>" class="block mb-4 overflow-hidden rounded-xl bg-gray-50 aspect-square flex items-center justify-center relative">
                    <?php if(strpos($product['image_url'], 'uploads/') !== false || filter_var($product['image_url'], FILTER_VALIDATE_URL)): ?>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-300">
                    <?php else: ?>
                        <span class="text-6xl transform group-hover:scale-110 transition-transform duration-300"><?= htmlspecialchars($product['image_url']) ?></span>
                    <?php endif; ?>
                    <div class="absolute top-2 right-2 bg-ixlas-100 text-ixlas-600 text-[10px] font-bold px-2 py-1 rounded-full border border-ixlas-200">
                        Stokda: <?= $product['stock'] ?>
                    </div>
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
                            <input type="hidden" name="return_url" value="../index.php">
                            <button type="submit" class="w-full bg-ixlas-600 text-white font-bold py-3.5 rounded-xl hover:bg-ixlas-700 transition-all hover:shadow-lg hover:shadow-ixlas-500/40 flex items-center justify-center gap-2">
                                Səbətə At <i class="fa-solid fa-cart-shopping"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($products)): ?>
                <div class="col-span-full text-center py-12 text-gray-500 bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <div class="text-4xl mb-4">📦</div>
                    <p class="font-medium">Hal-hazırda satışda məhsul yoxdur.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</main>

<?php require_once 'includes/footer.php'; ?>