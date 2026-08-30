<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin') {
    header("Location: admin/index.php");
    exit;
} elseif ($_SESSION['user_role'] === 'courier') {
    header("Location: courier/index.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim(htmlspecialchars($_POST['name']));
    $new_password = $_POST['new_password'];
    
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE users SET name = ?, password = ? WHERE id = ?");
        $update_stmt->execute([$new_name, $hashed_password, $user_id]);
    } else {
        $update_stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $update_stmt->execute([$new_name, $user_id]);
    }
    
    $_SESSION['user_name'] = $new_name;
    $msg = 'success';
}

$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

$orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';

$tab_active_class = "flex items-center gap-3 px-4 py-3 bg-ixlas-50 text-ixlas-600 rounded-xl font-bold transition-colors";
$tab_inactive_class = "flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-xl font-medium transition-colors";

require_once 'includes/header.php';
?>

<main class="flex-1 bg-gray-50/50 py-10 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="md:hidden mb-6">
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Şəxsi Kabinet</h1>
        </div>

        <div class="flex flex-col md:flex-row gap-8">
            
            <div class="w-full md:w-64 lg:w-72 shrink-0">
                <div class="bg-white rounded-3xl shadow-[0_2px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 overflow-hidden sticky top-28">
                    
                    <div class="p-6 border-b border-gray-100 text-center relative overflow-hidden">
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-ixlas-50 rounded-full mix-blend-multiply opacity-50"></div>
                        <div class="w-20 h-20 bg-ixlas-100 text-ixlas-600 rounded-full flex items-center justify-center text-3xl font-black mx-auto mb-4 shadow-inner relative z-10">
                            <?= mb_substr(htmlspecialchars($user['name']), 0, 1) ?>
                        </div>
                        <h3 class="font-extrabold text-gray-900 text-lg relative z-10"><?= htmlspecialchars($user['name']) ?></h3>
                        <p class="text-gray-500 text-sm relative z-10"><?= htmlspecialchars($user['phone']) ?></p>
                    </div>
                    
                    <nav class="p-4 space-y-1.5">
                        <a href="?tab=orders" class="<?= $active_tab === 'orders' ? $tab_active_class : $tab_inactive_class ?>">
                            <i class="fa-solid fa-box-open w-5 text-center"></i>
                            Mənim Sifarişlərim
                        </a>
                        
                        <a href="?tab=settings" class="<?= $active_tab === 'settings' ? $tab_active_class : $tab_inactive_class ?>">
                            <i class="fa-regular fa-user w-5 text-center"></i>
                            Şəxsi Məlumatlar
                        </a>
                        
                        <a href="?tab=favorites" class="<?= $active_tab === 'favorites' ? $tab_active_class : $tab_inactive_class ?>">
                            <i class="fa-regular fa-heart w-5 text-center"></i>
                            Bəyəndiklərim
                        </a>
                        
                        <a href="?tab=addresses" class="<?= $active_tab === 'addresses' ? $tab_active_class : $tab_inactive_class ?>">
                            <i class="fa-solid fa-location-dot w-5 text-center"></i>
                            Ünvanlarım
                        </a>
                        
                        <div class="pt-4 mt-2 border-t border-gray-100">
                            <a href="actions/logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl font-bold transition-colors">
                                <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>
                                Hesabdan Çıx
                            </a>
                        </div>
                    </nav>
                </div>
            </div>

            <div class="flex-1">
                
                <?php if($msg === 'success'): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3 font-medium">
                        <i class="fa-solid fa-circle-check text-lg"></i> Məlumatlarınız uğurla yeniləndi!
                    </div>
                <?php endif; ?>

                <?php if ($active_tab === 'orders'): ?>
                    <div class="hidden md:flex justify-between items-end mb-6">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">Sifariş Tarixçəsi</h1>
                            <p class="text-gray-500 mt-1 text-sm">Saytımızdan etdiyiniz bütün alış-verişlər burada qeyd olunur.</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-[0_2px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 overflow-hidden h-full flex flex-col min-h-[500px]">
                        <?php if (empty($orders)): ?>
                            <div class="flex-1 flex flex-col items-center justify-center p-10 text-center">
                                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-gray-100">
                                    <i class="fa-solid fa-bag-shopping text-4xl text-gray-300"></i>
                                </div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Hələ heç bir sifarişiniz yoxdur</h4>
                                <p class="text-gray-500 max-w-sm mx-auto mb-8 text-sm">Ən yeni texnologiyalar və sərfəli təkliflər sizi gözləyir. İndi alış-verişə başlayın və ilk sifarişinizi edin.</p>
                                <a href="index.php" class="inline-flex items-center justify-center gap-2 bg-ixlas-600 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-ixlas-700 transition-all shadow-lg shadow-ixlas-500/30 hover:-translate-y-0.5">
                                    Kataloqa Göz At <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="divide-y divide-gray-100 p-2 md:p-6">
                                <?php foreach ($orders as $order): ?>
                                    <div class="p-4 md:p-6 hover:bg-gray-50/50 rounded-2xl transition-colors mb-4 border border-gray-100 shadow-sm bg-white">
                                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-ixlas-50 rounded-xl flex items-center justify-center text-ixlas-500 shrink-0"><i class="fa-solid fa-box"></i></div>
                                                <div>
                                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Sifariş</p>
                                                    <p class="font-extrabold text-gray-900 text-lg">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex gap-6 items-center w-full sm:w-auto border-t sm:border-t-0 pt-4 sm:pt-0 border-gray-100">
                                                <div>
                                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Tarix</p>
                                                    <p class="font-medium text-gray-900 text-sm"><?= date('d.m.Y', strtotime($order['created_at'])) ?></p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Məbləğ</p>
                                                    <p class="font-extrabold text-ixlas-600"><?= number_format($order['total_amount'], 2) ?> ₼</p>
                                                </div>
                                            </div>
                                            
                                            <div class="w-full sm:w-auto">
                                                <?php 
                                                    $status_classes = ['pending' => 'bg-yellow-50 text-yellow-600 border-yellow-200', 'approved' => 'bg-blue-50 text-blue-600 border-blue-200', 'courier' => 'bg-ixlas-50 text-ixlas-600 border-ixlas-200', 'delivered' => 'bg-green-50 text-green-600 border-green-200', 'cancelled' => 'bg-red-50 text-red-600 border-red-200'];
                                                    $status_labels = ['pending' => 'Gözləyir', 'approved' => 'Təsdiqləndi', 'courier' => 'Kuryerdə (Çatdırılır)', 'delivered' => 'Çatdırıldı', 'cancelled' => 'Ləğv edildi'];
                                                ?>
                                                <span class="<?= $status_classes[$order['status']] ?? 'bg-gray-50' ?> border py-2 px-4 rounded-xl text-xs font-bold flex items-center justify-center w-full sm:w-max shadow-sm">
                                                    <?= $status_labels[$order['status']] ?? 'Bilinmir' ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="text-sm text-gray-600 bg-gray-50/50 p-5 rounded-xl border border-gray-100 ml-0 sm:ml-16">
                                            <div class="flex items-start gap-3 mb-4 border-b border-gray-200 pb-4">
                                                <div class="w-8 h-8 rounded-full bg-white border flex items-center justify-center text-gray-400 shrink-0 shadow-sm mt-0.5"><i class="fa-solid fa-location-dot text-xs"></i></div>
                                                <div>
                                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Ünvan</p>
                                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($order['address']) ?></p>
                                                </div>
                                            </div>
                                            
                                            <?php
                                                $items_stmt = $pdo->prepare("SELECT oi.quantity, oi.price, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                                                $items_stmt->execute([$order['id']]);
                                                $items = $items_stmt->fetchAll();
                                            ?>
                                            <div>
                                                <p class="font-bold text-xs text-gray-400 uppercase tracking-wider mb-3">Məhsullar:</p>
                                                <ul class="space-y-3">
                                                    <?php foreach($items as $item): ?>
                                                        <li class="flex justify-between items-center text-sm bg-white p-2.5 rounded-lg border border-gray-100 shadow-sm">
                                                            <div class="flex items-center gap-3">
                                                                <!-- Şəkil Məntiqi -->
                                                                <div class="w-8 h-8 bg-gray-50 rounded overflow-hidden flex items-center justify-center shrink-0">
                                                                    <?php if(strpos($item['image_url'], 'uploads/') !== false || filter_var($item['image_url'], FILTER_VALIDATE_URL)): ?>
                                                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" class="w-full h-full object-cover">
                                                                    <?php else: ?>
                                                                        <span class="text-lg"><?= htmlspecialchars($item['image_url']) ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <span class="font-bold text-gray-900"><?= $item['quantity'] ?>x</span>
                                                                <span class="font-medium text-gray-700"><?= htmlspecialchars($item['name']) ?></span>
                                                            </div>
                                                            <span class="font-bold text-gray-900"><?= number_format($item['price'] * $item['quantity'], 2) ?> ₼</span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>


                <?php elseif ($active_tab === 'settings'): ?>
                    <div class="hidden md:flex justify-between items-end mb-6">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">Şəxsi Məlumatlar</h1>
                            <p class="text-gray-500 mt-1 text-sm">Adınızı və şifrənizi buradan yeniləyə bilərsiniz.</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-[0_2px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 p-8">
                        <form method="POST" class="space-y-6 max-w-lg">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Mobil Nömrə (Login)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-phone"></i></span>
                                    <input type="text" value="<?= htmlspecialchars($user['phone']) ?>" disabled class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed font-medium">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Ad və Soyad</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-regular fa-user"></i></span>
                                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all font-medium">
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-100">
                                <h3 class="text-sm font-bold text-gray-900 mb-4">Şifrəni Yenilə</h3>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Yeni Şifrə</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" name="new_password" placeholder="Yeni şifrə daxil edin (İstəyə bağlı)" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all font-medium">
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="bg-ixlas-600 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-ixlas-700 transition-colors shadow-lg shadow-ixlas-500/30 flex items-center gap-2">
                                    <i class="fa-solid fa-floppy-disk"></i> Dəyişiklikləri Yadda Saxla
                                </button>
                            </div>
                        </form>
                    </div>

                <?php elseif ($active_tab === 'favorites'): ?>
                    <div class="hidden md:flex justify-between items-end mb-6">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">Bəyəndiyim Məhsullar</h1>
                            <p class="text-gray-500 mt-1 text-sm">Seçilmiş məhsullarınızı buradan izləyə bilərsiniz.</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-3xl shadow-[0_2px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 flex flex-col items-center justify-center min-h-[500px] p-10 text-center">
                        <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-red-100 text-red-400">
                            <i class="fa-regular fa-heart text-4xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-2">Bəyəndiyiniz məhsul yoxdur</h4>
                        <p class="text-gray-500 max-w-sm mx-auto mb-8 text-sm">Saytda gəzinərkən bəyəndiyiniz məhsulları işarələyin, onlar burada siyahıya alınacaq.</p>
                        <a href="index.php" class="inline-flex items-center justify-center bg-gray-100 text-gray-700 font-bold px-8 py-3.5 rounded-xl hover:bg-gray-200 transition-colors">
                            Kataloqa Göz At
                        </a>
                    </div>

                <?php elseif ($active_tab === 'addresses'): ?>
                    <div class="hidden md:flex justify-between items-end mb-6">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight">Qeyd Edilmiş Ünvanlar</h1>
                            <p class="text-gray-500 mt-1 text-sm">Sürətli alış-veriş üçün ünvanlarınızı buradan idarə edin.</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-3xl shadow-[0_2px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 flex flex-col items-center justify-center min-h-[500px] p-10 text-center">
                        <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-blue-100 text-blue-400">
                            <i class="fa-solid fa-map-location-dot text-4xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-2">Hələlik ünvan əlavə etməmisiniz</h4>
                        <p class="text-gray-500 max-w-sm mx-auto mb-8 text-sm">Yeni bir sifariş verərkən ünvanınız avtomatik olaraq bura qeyd olunacaq.</p>
                        <a href="index.php" class="inline-flex items-center justify-center bg-gray-100 text-gray-700 font-bold px-8 py-3.5 rounded-xl hover:bg-gray-200 transition-colors">
                            Sifariş Ver
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>