<?php
session_start();
require_once '../config/db.php';
require_once '../config/auth_check.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = htmlspecialchars($_POST['name']);
        $icon = htmlspecialchars($_POST['icon']);
        $parent_id = empty($_POST['parent_id']) ? NULL : (int)$_POST['parent_id'];
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, parent_id, icon) VALUES (?, ?, ?)");
        $stmt->execute([$name, $parent_id, $icon]);
        header("Location: categories.php?msg=added");
        exit;
    }
    if ($_POST['action'] === 'delete') {
        $category_id = (int)$_POST['category_id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        header("Location: categories.php?msg=deleted");
        exit;
    }
}

// Bütün kateqoriyaları gətirək
$query = "SELECT c1.*, c2.name as parent_name FROM categories c1 LEFT JOIN categories c2 ON c1.parent_id = c2.id ORDER BY c1.parent_id ASC, c1.name ASC";
$categories = $pdo->query($query)->fetchAll();

// Yalnız Ana kateqoriyaları gətirək
$main_categories = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>Kateqoriyalar - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 900: '#14532d' } } } } }
    </script>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 z-0 sticky top-0">
            <h2 class="font-bold text-gray-800">Məhsul Kataloqu</h2>
        </header>

        <div class="p-8">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Kateqoriyalar</h1>
                    <p class="text-sm text-gray-500 mt-1">Ana və alt kateqoriyaları buradan tənzimləyin.</p>
                </div>
                <button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="bg-ixlas-600 text-white px-5 py-3 rounded-xl text-sm font-bold shadow-lg shadow-ixlas-600/30 hover:bg-ixlas-700 transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Yeni Kateqoriya
                </button>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-xl border border-green-200 font-bold">Əməliyyat uğurla tamamlandı!</div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Kateqoriya Adı</th>
                            <th class="px-6 py-4">İyerarxiya (Növü)</th>
                            <th class="px-6 py-4 text-right">Sil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($categories as $cat): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-500"><i class="fa-solid <?= htmlspecialchars($cat['icon'] ?: 'fa-folder') ?>"></i></div>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($cat['parent_id']): ?>
                                        <span class="bg-blue-50 text-blue-600 py-1 px-3 rounded-full text-xs font-bold border border-blue-100"><i class="fa-solid fa-level-up-alt rotate-90 mr-1"></i> Alt: <?= htmlspecialchars($cat['parent_name']) ?></span>
                                    <?php else: ?>
                                        <span class="bg-ixlas-50 text-ixlas-700 py-1 px-3 rounded-full text-xs font-bold border border-ixlas-100">Ana Kateqoriya</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" class="inline" onsubmit="return confirm('Bu kateqoriyanı silmək istədiyinizə əminsiniz? İçindəki alt-kateqoriyalar da silinəcək!');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($categories)): ?><tr><td colspan="3" class="text-center py-8 text-gray-500">Heç bir kateqoriya yoxdur.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="add-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-lg">Yeni Kateqoriya</h3>
                <button onclick="document.getElementById('add-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Kateqoriya Adı</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-200 outline-none transition-all font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Ana Kateqoriya (Seçilməyə bilər)</label>
                    <select name="parent_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-200 outline-none transition-all font-medium bg-white">
                        <option value="">-- Özü Ana Kateqoriya Olsun --</option>
                        <?php foreach($main_categories as $main): ?>
                            <option value="<?= $main['id'] ?>"><?= htmlspecialchars($main['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">İkon (FontAwesome)</label>
                    <input type="text" name="icon" placeholder="fa-mobile" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-200 outline-none transition-all font-medium">
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full py-3.5 bg-ixlas-600 text-white font-bold rounded-xl shadow-lg shadow-ixlas-600/30 hover:bg-ixlas-700 transition-colors">Yadda Saxla</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>