<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once 'config/db.php';

// URL-dən səhifə növünü (slug) alırıq
$slug = isset($_GET['p']) ? $_GET['p'] : 'about';

$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$current_page = $stmt->fetch();

// Əgər belə bir səhifə yoxdursa və ya passivdirsə, ana səhifəyə qaytar
if (!$current_page) {
    header("Location: index.php");
    exit;
}

include 'includes/header.php';
?>

<main class="flex-1 bg-gray-50 py-12 lg:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Səhifə Başlığı -->
        <div class="text-center mb-12">
            <div class="w-20 h-20 bg-ixlas-100 text-ixlas-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-6 shadow-inner">
                <i class="fa-solid <?= htmlspecialchars($current_page['icon'] ?? 'fa-file-lines') ?>"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight"><?= htmlspecialchars($current_page['title']) ?></h1>
            <div class="w-16 h-1.5 bg-ixlas-500 rounded-full mx-auto mt-6"></div>
        </div>

        <!-- Məzmun Qutusu -->
        <div class="bg-white rounded-3xl p-8 md:p-12 shadow-[0_2px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 prose prose-lg prose-ixlas max-w-none text-gray-700 leading-relaxed">
            <?= $current_page['content'] ?>
        </div>
        
        <!-- Geriyə qayıt -->
        <div class="mt-8 text-center">
            <a href="index.php" class="inline-flex items-center gap-2 text-ixlas-600 font-bold hover:text-ixlas-700 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Ana Səhifəyə Qayıt
            </a>
        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>