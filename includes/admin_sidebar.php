<?php
// Hazırda olduğumuz səhifənin adını tapırıq (məsələn: index.php, orders.php)
$current_page = basename($_SERVER['PHP_SELF']);

// Aktiv və Passiv linklər üçün CSS klassları
$active_class = "flex items-center gap-3 px-4 py-3 bg-ixlas-700 text-white rounded-xl font-bold transition-colors shadow-sm";
$inactive_class = "flex items-center gap-3 px-4 py-3 text-ixlas-100 hover:bg-ixlas-800 rounded-xl font-medium transition-colors";

// Sifarişlər menyusunda "Yeni" yazısını göstərmək üçün gözləyən sifarişlərin sayını tapırıq
$pending_count = 0;
if (isset($pdo)) {
    try {
        $pending_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    } catch (PDOException $e) {}
}
?>

<aside class="w-64 bg-ixlas-900 text-white flex flex-col hidden md:flex shrink-0 transition-all duration-300 border-r border-ixlas-800">
    <!-- Loqo Bölməsi -->
    <div class="h-16 flex items-center px-6 border-b border-ixlas-700">
        <span class="text-xl font-bold text-white"><i class="fa-solid fa-signal text-ixlas-400 mr-2"></i>İxlas Admin</span>
    </div>

    <!-- Menyu Linkləri -->
    <nav class="flex-1 overflow-y-auto p-4 space-y-2">
        <a href="index.php" class="<?= $current_page == 'index.php' ? $active_class : $inactive_class ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center"></i> Ümumi Panel
        </a>
        <a href="orders.php" class="<?= $current_page == 'orders.php' ? $active_class : $inactive_class ?>">
            <i class="fa-solid fa-cart-shopping w-5 text-center"></i> Sifarişlər
            <?php if($pending_count > 0): ?>
                <span class="ml-auto bg-red-500 text-white py-0.5 px-2 rounded-full text-[10px] font-bold"><?= $pending_count ?> Yeni</span>
            <?php endif; ?>
        </a>
        <a href="products.php" class="<?= $current_page == 'products.php' ? $active_class : $inactive_class ?>">
            <i class="fa-solid fa-box-open w-5 text-center"></i> Məhsullar
        </a>
        <a href="categories.php" class="<?= $current_page == 'categories.php' ? $active_class : $inactive_class ?>">
            <i class="fa-solid fa-layer-group w-5 text-center"></i> Kateqoriyalar
        </a>
        
        <div class="pt-4 mt-4 border-t border-ixlas-700">
            <p class="px-4 text-xs font-semibold text-ixlas-200 uppercase tracking-wider mb-2">Sistem və Məzmun</p>
            <a href="users.php" class="<?= $current_page == 'users.php' ? $active_class : $inactive_class ?>">
                <i class="fa-solid fa-users w-5 text-center"></i> İstifadəçilər
            </a>
            <a href="settings.php" class="<?= $current_page == 'settings.php' ? $active_class : $inactive_class ?>">
                <i class="fa-solid fa-sliders w-5 text-center"></i> Tənzimləmələr
            </a>
            <a href="pages.php" class="<?= $current_page == 'pages.php' ? $active_class : $inactive_class ?>">
                <i class="fa-solid fa-file-lines w-5 text-center"></i> Səhifələr (CMS)
            </a>
        </div>
    </nav>

    <!-- İstifadəçi Profili və Çıxış -->
    <div class="p-4 border-t border-ixlas-700 bg-ixlas-950/30">
        <div class="flex items-center gap-3 px-2">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name'] ?? 'Admin') ?>&background=22c55e&color=fff" class="w-9 h-9 rounded-full border-2 border-ixlas-700">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></p>
                <p class="text-[10px] text-ixlas-300 truncate uppercase font-bold tracking-wider"><?= htmlspecialchars($_SESSION['user_role'] ?? 'admin') ?></p>
            </div>
            <a href="../actions/logout.php" class="text-ixlas-400 hover:text-red-400 transition-colors" title="Çıxış">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
        <div class="mt-4 px-2">
            <a href="../index.php" target="_blank" class="flex items-center gap-2 text-xs text-ixlas-400 hover:text-white transition-colors font-medium">
                <i class="fa-solid fa-globe"></i> Sayta Bax (Yeni tabda)
            </a>
        </div>
    </div>
</aside>