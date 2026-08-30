<?php
session_start();
require_once '../config/db.php';
require_once '../config/auth_check.php';

require_admin();

// Ağıllı Yeniləmə: Əgər Products cədvəlində spesifikasiya sütunları yoxdursa, avtomatik yarat!
try {
    $pdo->exec("ALTER TABLE products ADD COLUMN ram VARCHAR(50) DEFAULT NULL");
    $pdo->exec("ALTER TABLE products ADD COLUMN storage VARCHAR(50) DEFAULT NULL");
    $pdo->exec("ALTER TABLE products ADD COLUMN processor VARCHAR(100) DEFAULT NULL");
    $pdo->exec("ALTER TABLE products ADD COLUMN camera VARCHAR(100) DEFAULT NULL");
} catch (PDOException $e) {
    // Sütunlar artıq mövcuddursa xətanı gözardı et.
}

// ================= POST ƏMƏLİYYATLARI =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // YENİ MƏHSUL ƏLAVƏ ETMƏK
    if ($_POST['action'] === 'add') {
        $name = htmlspecialchars($_POST['name']);
        $category_id = (int)$_POST['category_id'];
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $image_url = htmlspecialchars($_POST['image_url']);
        $description = htmlspecialchars($_POST['description']);
        $ram = htmlspecialchars($_POST['ram']);
        $storage = htmlspecialchars($_POST['storage']);
        $processor = htmlspecialchars($_POST['processor']);
        $camera = htmlspecialchars($_POST['camera']);
        
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, stock, image_url, description, ram, storage, processor, camera, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $category_id, $price, $stock, $image_url, $description, $ram, $storage, $processor, $camera]);
        header("Location: products.php?msg=added");
        exit;
    }
    
    // MƏHSULU YENİLƏMƏK (REDAKTƏ)
    if ($_POST['action'] === 'edit') {
        $id = (int)$_POST['product_id'];
        $name = htmlspecialchars($_POST['name']);
        $category_id = (int)$_POST['category_id'];
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $image_url = htmlspecialchars($_POST['image_url']);
        $description = htmlspecialchars($_POST['description']);
        $ram = htmlspecialchars($_POST['ram']);
        $storage = htmlspecialchars($_POST['storage']);
        $processor = htmlspecialchars($_POST['processor']);
        $camera = htmlspecialchars($_POST['camera']);
        
        $stmt = $pdo->prepare("UPDATE products SET name=?, category_id=?, price=?, stock=?, image_url=?, description=?, ram=?, storage=?, processor=?, camera=? WHERE id=?");
        $stmt->execute([$name, $category_id, $price, $stock, $image_url, $description, $ram, $storage, $processor, $camera, $id]);
        header("Location: products.php?msg=updated");
        exit;
    }

    // AKTİV/PASSİV STATUSUNU DƏYİŞMƏK
    if ($_POST['action'] === 'toggle_status') {
        $stmt = $pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([(int)$_POST['product_id']]);
        header("Location: products.php?msg=updated");
        exit;
    }

    // MƏHSULU SİLMƏK
    if ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([(int)$_POST['product_id']]);
        header("Location: products.php?msg=deleted");
        exit;
    }
}

// ================= AXTARIŞ (SEARCH) FUNKSİYASI =================
$search_query = "";
$params = [];
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $search_query = " WHERE p.name LIKE ? ";
    $params[] = "%$search%"; // Sonda və əvvəldə hərflərə görə tapır
}

// Məhsulları Çəkmək (Axtarışa görə filtrlənir)
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id" . $search_query . " ORDER BY p.id DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>Məhsullar - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 900: '#14532d' } } } } }
    </script>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    
    <aside class="w-64 bg-ixlas-900 text-white flex flex-col hidden md:flex">
        <div class="h-16 flex items-center px-6 border-b border-ixlas-700">
            <span class="text-xl font-bold text-white"><i class="fa-solid fa-signal text-ixlas-400 mr-2"></i>İxlas Admin</span>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-ixlas-100 hover:bg-ixlas-800 rounded-xl font-medium transition-colors">
                <i class="fa-solid fa-chart-pie w-5 text-center"></i> Ümumi Panel
            </a>
            <a href="orders.php" class="flex items-center gap-3 px-4 py-3 text-ixlas-100 hover:bg-ixlas-800 rounded-xl font-medium transition-colors">
                <i class="fa-solid fa-cart-shopping w-5 text-center"></i> Sifarişlər
            </a>
            <a href="categories.php" class="flex items-center gap-3 px-4 py-3 text-ixlas-100 hover:bg-ixlas-800 rounded-xl font-medium transition-colors">
                <i class="fa-solid fa-layer-group w-5 text-center"></i> Kateqoriyalar
            </a>
            <a href="products.php" class="flex items-center gap-3 px-4 py-3 bg-ixlas-700 text-white rounded-xl font-bold transition-colors">
                <i class="fa-solid fa-box-open w-5 text-center"></i> Məhsullar
            </a>
            <a href="users.php" class="flex items-center gap-3 px-4 py-3 text-ixlas-100 hover:bg-ixlas-800 rounded-xl font-medium transition-colors">
                <i class="fa-solid fa-users w-5 text-center"></i> İstifadəçilər
            </a>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 z-0 sticky top-0">
            <h2 class="font-bold text-gray-800">Məhsul Kataloqu</h2>
        </header>

        <div class="p-8">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">Məhsullar</h1>
                    <p class="text-sm text-gray-500 mt-1">Sistemdəki bütün məhsullar, spesifikasiyalar və stok vəziyyəti.</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Axtarış Formu -->
                    <form method="GET" class="relative">
                        <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Məhsul adı axtar..." class="pl-10 pr-8 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all w-64 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="products.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-red-400 hover:text-red-600 transition-colors"><i class="fa-solid fa-xmark"></i></a>
                        <?php endif; ?>
                    </form>

                    <button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="bg-ixlas-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-ixlas-600/30 hover:bg-ixlas-700 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Yeni Məhsul
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Şəkil</th>
                                <th class="px-6 py-4">Məhsul Adı</th>
                                <th class="px-6 py-4">Texniki Detallar (Müqayisə)</th>
                                <th class="px-6 py-4">Qiymət</th>
                                <th class="px-6 py-4">Stok / Status</th>
                                <th class="px-6 py-4 text-right">İdarə</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach($products as $prod): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-3xl"><?= htmlspecialchars($prod['image_url']) ?></td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-900"><?= htmlspecialchars($prod['name']) ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($prod['category_name']) ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500 space-y-1">
                                        <?php if($prod['ram']): ?><p><span class="font-bold">RAM:</span> <?= htmlspecialchars($prod['ram']) ?></p><?php endif; ?>
                                        <?php if($prod['storage']): ?><p><span class="font-bold">Yaddaş:</span> <?= htmlspecialchars($prod['storage']) ?></p><?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 font-extrabold text-ixlas-700"><?= number_format($prod['price'], 2) ?> ₼</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-2 items-start">
                                            <?php if($prod['stock'] > 0): ?>
                                                <span class="bg-green-50 text-green-700 py-1 px-3 rounded-full text-[10px] font-bold border border-green-200"><?= $prod['stock'] ?> ədəd Stokda</span>
                                            <?php else: ?>
                                                <span class="bg-red-50 text-red-700 py-1 px-3 rounded-full text-[10px] font-bold border border-red-200">Bitib (Gizli)</span>
                                            <?php endif; ?>
                                            
                                            <?php if($prod['is_active']): ?>
                                                <span class="text-[10px] font-bold text-blue-600 flex items-center gap-1"><i class="fa-solid fa-eye"></i> Saytda Görünür</span>
                                            <?php else: ?>
                                                <span class="text-[10px] font-bold text-gray-400 flex items-center gap-1"><i class="fa-solid fa-eye-slash"></i> Passivdir</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <!-- Redaktə Düyməsi (HTML5 dataset ilə) -->
                                            <button type="button" 
                                                onclick="openEditModal(this)"
                                                data-id="<?= $prod['id'] ?>"
                                                data-name="<?= htmlspecialchars($prod['name']) ?>"
                                                data-cat="<?= $prod['category_id'] ?>"
                                                data-price="<?= $prod['price'] ?>"
                                                data-stock="<?= $prod['stock'] ?>"
                                                data-img="<?= htmlspecialchars($prod['image_url']) ?>"
                                                data-desc="<?= htmlspecialchars($prod['description'] ?? '') ?>"
                                                data-ram="<?= htmlspecialchars($prod['ram'] ?? '') ?>"
                                                data-storage="<?= htmlspecialchars($prod['storage'] ?? '') ?>"
                                                data-proc="<?= htmlspecialchars($prod['processor'] ?? '') ?>"
                                                data-cam="<?= htmlspecialchars($prod['camera'] ?? '') ?>"
                                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center" title="Düzəliş et">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>

                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-200 transition-colors" title="Aktiv/Passiv Et"><i class="fa-solid fa-<?= $prod['is_active'] ? 'eye-slash' : 'eye' ?>"></i></button>
                                            </form>
                                            
                                            <form method="POST" class="inline" onsubmit="return confirm('Silinməsinə əminsiniz?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($products)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-500">Axtarışa uyğun məhsul tapılmadı.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- YENİ MƏHSUL MODALI -->
    <div id="add-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-lg">Yeni Məhsul & Spesifikasiyalar</h3>
                <button onclick="document.getElementById('add-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="overflow-y-auto p-6 flex-1">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="product_id" id="edit_product_id" value="">
                    <input type="hidden" name="existing_image" id="edit_existing_image" value="">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 border-b border-gray-100 pb-2">Ümumi Məlumat</h4>
                            <div><label class="block text-xs font-bold text-gray-700 mb-1">Məhsulun Adı</label><input type="text" name="name" id="edit_name" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kateqoriya</label>
                                <select name="category_id" id="edit_category_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none bg-white">
                                    <?php foreach($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="block text-xs font-bold text-gray-700 mb-1">Qiymət (₼)</label><input type="number" step="0.01" name="price" id="edit_price" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                                <div><label class="block text-xs font-bold text-gray-700 mb-1">Stok (Say)</label><input type="number" name="stock" id="edit_stock" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Yeni Şəkil Seçin (İstəyə bağlı)</label>
                                <input type="file" name="image_file" accept="image/*" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none bg-white text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer transition-all">
                                <p class="text-[10px] text-gray-400 mt-1">Yeni şəkil seçməsəniz, məhsulun əvvəlki şəkli qalacaq.</p>
                            </div>
                            <div><label class="block text-xs font-bold text-gray-700 mb-1">Qısa Təsvir</label><textarea name="description" id="edit_description" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none resize-none"></textarea></div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 border-b border-gray-100 pb-2">Texniki Spesifikasiyalar</h4>
                            <div><label class="block text-xs font-bold text-gray-700 mb-1">RAM (məs: 8 GB)</label><input type="text" name="ram" id="edit_ram" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                            <div><label class="block text-xs font-bold text-gray-700 mb-1">Daxili Yaddaş (məs: 256 GB)</label><input type="text" name="storage" id="edit_storage" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                            <div><label class="block text-xs font-bold text-gray-700 mb-1">Prosessor (CPU)</label><input type="text" name="processor" id="edit_processor" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                            <div><label class="block text-xs font-bold text-gray-700 mb-1">Kamera (məs: 50MP + 12MP)</label><input type="text" name="camera" id="edit_camera" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none"></div>
                        </div>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-100 mt-6 flex justify-end gap-3">
                        <button type="button" onclick="closeEditModal()" class="px-5 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Ləğv et</button>
                        <button type="submit" class="px-8 py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 hover:bg-blue-700 transition-colors">Yenilə və Yadda Saxla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Məhsul məlumatlarını dataset-dən çəkib modala doldurur
        function openEditModal(btn) {
            const ds = btn.dataset;
            document.getElementById('edit_product_id').value = ds.id;
            document.getElementById('edit_existing_image').value = ds.img;
            document.getElementById('edit_name').value = ds.name;
            document.getElementById('edit_category_id').value = ds.cat;
            document.getElementById('edit_price').value = ds.price;
            document.getElementById('edit_stock').value = ds.stock;
            document.getElementById('edit_image_url').value = ds.img;
            document.getElementById('edit_description').value = ds.desc;
            document.getElementById('edit_ram').value = ds.ram;
            document.getElementById('edit_storage').value = ds.storage;
            document.getElementById('edit_processor').value = ds.proc;
            document.getElementById('edit_camera').value = ds.cam;
            
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }
    </script>
</body>
</html>