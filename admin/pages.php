<?php
session_start();
require_once '../config/db.php';
require_once '../config/auth_check.php';

// Yalnız admin və superadmin girə bilər
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'add') {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug']))); // URL formatı
        $title = htmlspecialchars($_POST['title']);
        $icon = htmlspecialchars($_POST['icon']);
        $content = $_POST['content']; // HTML olduğu üçün təmizləmirik
        $sort_order = (int)$_POST['sort_order'];
        
        $stmt = $pdo->prepare("INSERT INTO pages (slug, title, icon, content, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$slug, $title, $icon, $content, $sort_order]);
        header("Location: pages.php?msg=added");
        exit;
    }
    
    if ($_POST['action'] === 'edit') {
        $id = (int)$_POST['page_id'];
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'])));
        $title = htmlspecialchars($_POST['title']);
        $icon = htmlspecialchars($_POST['icon']);
        $content = $_POST['content'];
        $sort_order = (int)$_POST['sort_order'];
        
        $stmt = $pdo->prepare("UPDATE pages SET slug=?, title=?, icon=?, content=?, sort_order=? WHERE id=?");
        $stmt->execute([$slug, $title, $icon, $content, $sort_order, $id]);
        header("Location: pages.php?msg=updated");
        exit;
    }

    if ($_POST['action'] === 'toggle_status') {
        $stmt = $pdo->prepare("UPDATE pages SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([(int)$_POST['page_id']]);
        header("Location: pages.php?msg=updated");
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->execute([(int)$_POST['page_id']]);
        header("Location: pages.php?msg=deleted");
        exit;
    }
}

// Bütün səhifələri sıralı şəkildə çəkirik
$pages = $pdo->query("SELECT * FROM pages ORDER BY sort_order ASC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Səhifələr (CMS) - İxlas Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 900: '#14532d' } } } } }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">

    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="font-bold text-gray-800">Səhifə İdarəetməsi (CMS)</h2>
        </header>

        <div class="flex-1 overflow-auto p-8 relative">
            
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Məlumat Səhifələri</h1>
                    <p class="text-sm text-gray-500 mt-1">Saytın aşağısında (Footer) görünən və məlumat verən səhifələri idarə edin.</p>
                </div>
                <button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="bg-ixlas-600 hover:bg-ixlas-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-ixlas-600/30 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-plus"></i> Yeni Səhifə
                </button>
            </div>

            <!-- Cədvəl -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4 w-12">Sıra</th>
                            <th class="px-6 py-4">Başlıq & İkon</th>
                            <th class="px-6 py-4">URL (Slug)</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Əməliyyat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($pages as $p): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-gray-400"><?= $p['sort_order'] ?></td>
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-ixlas-50 flex items-center justify-center text-ixlas-600">
                                    <i class="fa-solid <?= htmlspecialchars($p['icon']) ?>"></i>
                                </div>
                                <span class="font-bold text-gray-800"><?= htmlspecialchars($p['title']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs font-mono bg-gray-50 rounded">page.php?p=<span class="font-bold text-ixlas-600"><?= htmlspecialchars($p['slug']) ?></span></td>
                            <td class="px-6 py-4">
                                <?php if($p['is_active']): ?>
                                    <span class="bg-green-100 text-green-700 border border-green-200 text-[10px] font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 w-max"><i class="fa-solid fa-eye"></i> Saytda Görünür</span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 border border-gray-200 text-[10px] font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 w-max"><i class="fa-solid fa-eye-slash"></i> Gizlədilib</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button" 
                                        onclick="openEditModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['slug']) ?>', '<?= htmlspecialchars(addslashes($p['title'])) ?>', '<?= htmlspecialchars($p['icon']) ?>', '<?= htmlspecialchars(addslashes($p['content'])) ?>', <?= $p['sort_order'] ?>)"
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center" title="Redaktə et">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="page_id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors" title="Aktiv/Passiv Et"><i class="fa-solid fa-<?= $p['is_active'] ? 'eye-slash' : 'eye' ?>"></i></button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Silmək istədiyinizə əminsiniz?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="page_id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($pages)): ?><tr><td colspan="5" class="text-center py-8 text-gray-500">Heç bir səhifə yoxdur.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Yeni Səhifə Yarat Modalı -->
    <div id="add-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-lg">Yeni Məlumat Səhifəsi</h3>
                <button onclick="document.getElementById('add-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="overflow-y-auto p-6 flex-1">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="block text-xs font-bold text-gray-700 mb-1">Başlıq</label><input type="text" name="title" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                        <div><label class="block text-xs font-bold text-gray-700 mb-1">URL (Slug) - Məs: haqqimizda</label><input type="text" name="slug" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none font-mono text-sm"></div>
                        <div><label class="block text-xs font-bold text-gray-700 mb-1">İkon (FontAwesome)</label><input type="text" name="icon" value="fa-file-lines" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                        <div><label class="block text-xs font-bold text-gray-700 mb-1">Sıra Nömrəsi (Footer üçün)</label><input type="number" name="sort_order" value="10" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Səhifənin Məzmunu (HTML dəstəklənir)</label>
                        <textarea name="content" rows="10" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none resize-y font-mono text-sm bg-gray-50"></textarea>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="px-8 py-3.5 bg-ixlas-600 text-white font-bold rounded-xl shadow-lg shadow-ixlas-600/30 hover:bg-ixlas-700 transition-colors">Səhifəni Yarat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Redaktə Modalı -->
    <div id="edit-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-blue-100 flex justify-between items-center bg-blue-50/50">
                <h3 class="font-bold text-gray-900 text-lg">Səhifəni Redaktə Et</h3>
                <button onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="overflow-y-auto p-6 flex-1">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="page_id" id="edit_page_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="block text-xs font-bold text-gray-700 mb-1">Başlıq</label><input type="text" name="title" id="edit_title" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none"></div>
                        <div><label class="block text-xs font-bold text-gray-700 mb-1">URL (Slug)</label><input type="text" name="slug" id="edit_slug" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none font-mono text-sm"></div>
                        <div><label class="block text-xs font-bold text-gray-700 mb-1">İkon (FontAwesome)</label><input type="text" name="icon" id="edit_icon" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none"></div>
                        <div><label class="block text-xs font-bold text-gray-700 mb-1">Sıra Nömrəsi</label><input type="number" name="sort_order" id="edit_sort_order" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none"></div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Səhifənin Məzmunu (HTML dəstəklənir)</label>
                        <textarea name="content" id="edit_content" rows="10" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 outline-none resize-y font-mono text-sm bg-gray-50"></textarea>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="px-5 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Ləğv et</button>
                        <button type="submit" class="px-8 py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 hover:bg-blue-700 transition-colors">Yenilə və Yadda Saxla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(id, slug, title, icon, content, sort) {
            document.getElementById('edit_page_id').value = id;
            document.getElementById('edit_slug').value = slug;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_icon').value = icon;
            document.getElementById('edit_content').value = content;
            document.getElementById('edit_sort_order').value = sort;
            document.getElementById('edit-modal').classList.remove('hidden');
        }
    </script>
</body>
</html>