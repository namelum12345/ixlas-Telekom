<?php
// TƏHLÜKƏSİZLİK VƏ QURAŞDIRMA YOXLANIŞI
$db_file = __DIR__ . '/../config/db.php';
if (!file_exists($db_file)) {
    // Əgər config/db.php faylı yoxdursa (sistem qurulmayıbsa), 
    // ölümcül xəta vermək əvəzinə istifadəçini quraşdırma səhifəsinə at
    header("Location: install.php");
    exit;
}
require_once $db_file;

// Ana kateqoriyaları bazadan çəkirik
$nav_categories = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL LIMIT 5")->fetchAll();

// Səbətdəki məhsul sayını hesablamaq
$cart_count = 0;
if(isset($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $qty) {
        $cart_count += $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İxlas Telekom - Sizin Texnologiya Partnyorunuz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { 
            theme: { 
                extend: { 
                    colors: { 
                        ixlas: { 
                            50: '#f0fdf4', 
                            100: '#dcfce7', 
                            200: '#bbf7d0', 
                            400: '#4ade80', 
                            500: '#22c55e', 
                            600: '#16a34a', 
                            700: '#15803d', 
                            800: '#166534', 
                            900: '#14532d' 
                        } 
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                } 
            } 
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        /* Premium arxa plan naxışı */
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-11 h-11 bg-ixlas-600 text-white rounded-xl flex items-center justify-center text-xl group-hover:bg-ixlas-700 transition-colors shadow-lg shadow-ixlas-500/30">
                    <i class="fa-solid fa-signal"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight leading-none">İxlas<span class="text-ixlas-600">Telekom</span></h1>
                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Rəsmi Satış</span>
                </div>
            </a>

            <!-- Navigation (Dinamik) -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="index.php" class="text-sm font-bold text-gray-900 hover:text-ixlas-600 transition-colors">Ana Səhifə</a>
                <?php foreach($nav_categories as $cat): ?>
                    <a href="category.php?id=<?= $cat['id'] ?>" class="text-sm font-bold text-gray-600 hover:text-ixlas-600 transition-colors">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="flex items-center gap-3">
                
                <!-- YENİLƏNMİŞ AXTARIŞ FORMU -->
                <form action="search.php" method="GET" class="relative hidden sm:block group">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-ixlas-500 transition-colors"></i>
                    <input type="text" name="q" placeholder="Məhsul axtar..." required class="pl-10 pr-4 py-2.5 bg-gray-100 border border-transparent rounded-full text-sm font-medium focus:border-ixlas-500 focus:bg-white focus:ring-2 focus:ring-ixlas-100 outline-none transition-all w-36 focus:w-64">
                </form>

                <!-- Mobil cihazlar üçün yalnız ikon (kliklədikdə axtarış səhifəsinə atır) -->
                <a href="search.php" class="sm:hidden w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-600 transition-colors">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </a>
                
                <a href="<?= isset($_SESSION['user_id']) ? 'profile.php' : 'login.php' ?>" class="w-10 h-10 rounded-full hover:bg-ixlas-50 hover:text-ixlas-600 flex items-center justify-center text-gray-600 transition-colors">
                    <i class="fa-regular fa-user"></i>
                </a>
                
                <a href="cart.php" class="h-10 px-4 rounded-full bg-ixlas-50 text-ixlas-600 flex items-center justify-center gap-2 hover:bg-ixlas-100 transition-colors font-bold text-sm">
                    <i class="fa-solid fa-cart-shopping"></i>
                    Səbət
                    <?php if($cart_count > 0): ?>
                        <span class="bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-white ml-1">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>