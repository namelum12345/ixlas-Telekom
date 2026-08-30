<?php
// TƏHLÜKƏSİZLİK VƏ QURAŞDIRMA YOXLANIŞI
$db_file = __DIR__ . '/../config/db.php';
if (!file_exists($db_file)) {
    header("Location: install.php");
    exit;
}
require_once $db_file;

// Bütün kateqoriyaları çəkib Ana və Alt kateqoriyalara ayırırıq
$all_categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$main_cats = [];
$sub_cats = [];

foreach($all_categories as $c) {
    if(empty($c['parent_id'])) {
        $main_cats[$c['id']] = $c;
        $main_cats[$c['id']]['children'] = [];
    } else {
        $sub_cats[] = $c;
    }
}

foreach($sub_cats as $sub) {
    if(isset($main_cats[$sub['parent_id']])) {
        $main_cats[$sub['parent_id']]['children'][] = $sub;
    }
}

// Menyuda göstərmək üçün ilk 6 ana kateqoriyanı seçirik
$nav_categories = array_slice($main_cats, 0, 6);

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
                        ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0', 400: '#4ade80', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 800: '#166534', 900: '#14532d' } 
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                } 
            } 
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        /* Hamburger Animasiyası üçün */
        .menu-btn-lines div { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .menu-btn:hover .menu-btn-lines div { width: 100%; }
        
        /* Dropdown Animasiyası */
        .dropdown-menu { opacity: 0; visibility: hidden; transform: translateY(10px); transition: all 0.3s ease; }
        .group:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <!-- Əsas Header -->
    <header class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-40 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3 group z-50">
                <div class="w-10 h-10 md:w-11 md:h-11 bg-ixlas-600 text-white rounded-xl flex items-center justify-center text-lg md:text-xl group-hover:bg-ixlas-700 transition-colors shadow-lg shadow-ixlas-500/30">
                    <i class="fa-solid fa-signal"></i>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-extrabold text-gray-900 tracking-tight leading-none">İxlas<span class="text-ixlas-600">Telekom</span></h1>
                    <span class="text-[9px] md:text-[10px] text-gray-500 font-bold uppercase tracking-widest hidden sm:block">Rəsmi Satış</span>
                </div>
            </a>

            <!-- Navigation (Kompüter üçün Dropdown ilə) -->
            <nav class="hidden lg:flex items-center gap-8 h-full">
                <a href="index.php" class="text-sm font-bold text-gray-900 hover:text-ixlas-600 transition-colors">Ana Səhifə</a>
                
                <?php foreach($nav_categories as $cat): ?>
                    <?php if(empty($cat['children'])): ?>
                        <a href="category.php?id=<?= $cat['id'] ?>" class="text-sm font-bold text-gray-600 hover:text-ixlas-600 transition-colors">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php else: ?>
                        <div class="relative group h-full flex items-center">
                            <a href="category.php?id=<?= $cat['id'] ?>" class="text-sm font-bold text-gray-600 hover:text-ixlas-600 transition-colors flex items-center gap-1.5 cursor-pointer">
                                <?= htmlspecialchars($cat['name']) ?>
                                <i class="fa-solid fa-chevron-down text-[10px] opacity-70"></i>
                            </a>
                            
                            <!-- Dropdown Pəncərəsi -->
                            <div class="dropdown-menu absolute top-[70px] left-1/2 -translate-x-1/2 bg-white rounded-2xl shadow-xl border border-gray-100 w-56 p-2 z-50">
                                <?php foreach($cat['children'] as $child): ?>
                                    <a href="category.php?id=<?= $child['id'] ?>" class="block px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-ixlas-50 hover:text-ixlas-600 rounded-xl transition-colors">
                                        <?= htmlspecialchars($child['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <div class="flex items-center gap-2 md:gap-4 z-50">
                <!-- Axtarış Formu -->
                <form action="search.php" method="GET" class="relative hidden sm:block group">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-ixlas-500 transition-colors"></i>
                    <input type="text" name="q" placeholder="Məhsul axtar..." required class="pl-11 pr-4 py-2.5 bg-gray-100/80 border border-transparent rounded-full text-sm font-medium focus:border-ixlas-500 focus:bg-white focus:ring-2 focus:ring-ixlas-100 outline-none transition-all w-48 lg:focus:w-64">
                </form>

                <!-- Mobil Axtarış İkonu -->
                <a href="search.php" class="sm:hidden w-10 h-10 rounded-xl bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-600 transition-colors border border-gray-100">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </a>
                
                <!-- Profil və Səbət -->
                <div class="hidden lg:flex items-center gap-3">
                    <a href="<?= isset($_SESSION['user_id']) ? 'profile.php' : 'login.php' ?>" class="w-10 h-10 rounded-full hover:bg-ixlas-50 hover:text-ixlas-600 flex items-center justify-center text-gray-600 transition-colors" title="Şəxsi Kabinet">
                        <i class="fa-regular fa-user text-lg"></i>
                    </a>
                    
                    <a href="cart.php" class="h-10 px-5 rounded-full bg-ixlas-50 text-ixlas-700 flex items-center justify-center gap-2 hover:bg-ixlas-100 transition-colors font-bold text-sm border border-ixlas-100">
                        <i class="fa-solid fa-cart-shopping"></i>
                        Səbət
                        <?php if($cart_count > 0): ?>
                            <span class="bg-ixlas-600 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full ml-1 shadow-sm">
                                <?= $cart_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Kreativ Mobil Menyu İkonu -->
                <button id="mobile-menu-btn" class="lg:hidden w-10 h-10 rounded-xl bg-ixlas-50 flex items-center justify-center text-ixlas-600 border border-ixlas-100 menu-btn group focus:outline-none">
                    <div class="flex flex-col justify-between w-[18px] h-[12px] menu-btn-lines">
                        <div class="bg-current h-[2px] w-1/2 rounded-full self-end"></div>
                        <div class="bg-current h-[2px] w-full rounded-full"></div>
                        <div class="bg-current h-[2px] w-3/4 rounded-full self-start"></div>
                    </div>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobil Tətbiq Sayağı Slide-in Menyu -->
    <div id="mobile-menu" class="fixed inset-0 z-50 pointer-events-none">
        <!-- Bulanıq Arxa Plan -->
        <div id="mobile-backdrop" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300 ease-in-out cursor-pointer"></div>
        
        <!-- Menyu Paneli -->
        <div id="mobile-drawer" class="absolute top-0 right-0 bottom-0 w-[280px] sm:w-[320px] bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] rounded-l-3xl flex flex-col pointer-events-auto">
            
            <div class="p-6 flex items-center justify-between border-b border-gray-100">
                <span class="font-extrabold text-gray-900 text-lg">Menyu</span>
                <button id="close-menu-btn" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <a href="<?= isset($_SESSION['user_id']) ? 'profile.php' : 'login.php' ?>" class="flex items-center gap-4 bg-white p-3.5 rounded-2xl shadow-sm border border-gray-100 mb-4 hover:border-ixlas-300 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-ixlas-100 text-ixlas-600 flex items-center justify-center shrink-0">
                        <i class="fa-regular fa-user text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate"><?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Hesaba Giriş' ?></p>
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider truncate"><?= isset($_SESSION['user_name']) ? 'Şəxsi Kabinet' : 'Qeydiyyatdan Keç' ?></p>
                    </div>
                </a>

                <a href="cart.php" class="flex items-center justify-between bg-ixlas-600 text-white p-4 rounded-2xl shadow-lg shadow-ixlas-500/30 hover:bg-ixlas-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-cart-shopping text-lg"></i>
                        <span class="font-bold text-sm">Səbətim</span>
                    </div>
                    <?php if($cart_count > 0): ?>
                        <span class="bg-white text-ixlas-600 text-xs font-black px-2.5 py-0.5 rounded-full"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Mobil Akkordeon Menyular -->
            <div class="p-6 flex-1 overflow-y-auto">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Kataloq</p>
                <div class="flex flex-col space-y-1">
                    
                    <a href="index.php" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 text-gray-700 font-bold transition-colors">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-house text-gray-300 w-4 text-center"></i> Ana Səhifə
                        </span>
                    </a>
                    
                    <?php foreach($nav_categories as $cat): ?>
                        <?php if(empty($cat['children'])): ?>
                            <a href="category.php?id=<?= $cat['id'] ?>" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 text-gray-700 font-bold transition-colors">
                                <span class="flex items-center gap-3">
                                    <i class="fa-solid <?= htmlspecialchars($cat['icon'] ?: 'fa-folder') ?> text-gray-300 w-4 text-center"></i>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                            </a>
                        <?php else: ?>
                            <!-- Alt Kateqoriyası olanlar üçün Akkordeon -->
                            <div>
                                <button onclick="toggleMobileSubmenu('sub-<?= $cat['id'] ?>', 'icon-<?= $cat['id'] ?>')" class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 text-gray-700 font-bold transition-colors">
                                    <span class="flex items-center gap-3">
                                        <i class="fa-solid <?= htmlspecialchars($cat['icon'] ?: 'fa-folder') ?> text-gray-300 w-4 text-center"></i>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </span>
                                    <i id="icon-<?= $cat['id'] ?>" class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"></i>
                                </button>
                                
                                <!-- Alt Kateqoriyaların siyahısı -->
                                <div id="sub-<?= $cat['id'] ?>" class="hidden pl-10 pr-3 py-1 space-y-1 bg-gray-50/50 rounded-xl mt-1">
                                    <a href="category.php?id=<?= $cat['id'] ?>" class="block py-2 text-sm font-bold text-ixlas-600">Bütün <?= htmlspecialchars($cat['name']) ?></a>
                                    <?php foreach($cat['children'] as $child): ?>
                                        <a href="category.php?id=<?= $child['id'] ?>" class="block py-2 text-sm font-medium text-gray-600 hover:text-ixlas-600 transition-colors">
                                            <?= htmlspecialchars($child['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </div>
            </div>
            
            <div class="p-6 bg-gray-50 text-center border-t border-gray-100">
                <p class="text-[10px] text-gray-400 font-medium">&copy; <?= date('Y') ?> İxlas Telekom.</p>
            </div>
        </div>
    </div>

    <!-- Mobil Menyu Üçün JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuBtn = document.getElementById('mobile-menu-btn');
            const closeBtn = document.getElementById('close-menu-btn');
            const menu = document.getElementById('mobile-menu');
            const backdrop = document.getElementById('mobile-backdrop');
            const drawer = document.getElementById('mobile-drawer');

            function openMenu() {
                menu.classList.remove('pointer-events-none');
                backdrop.classList.remove('opacity-0');
                drawer.classList.remove('translate-x-full');
                document.body.style.overflow = 'hidden'; 
            }

            function closeMenu() {
                backdrop.classList.add('opacity-0');
                drawer.classList.add('translate-x-full');
                document.body.style.overflow = ''; 
                setTimeout(() => { menu.classList.add('pointer-events-none'); }, 300);
            }

            menuBtn.addEventListener('click', openMenu);
            closeBtn.addEventListener('click', closeMenu);
            backdrop.addEventListener('click', closeMenu);
        });

        // Akkordeon funksiyası
        function toggleMobileSubmenu(submenuId, iconId) {
            const submenu = document.getElementById(submenuId);
            const icon = document.getElementById(iconId);
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                submenu.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>