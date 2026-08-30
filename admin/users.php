<?php
session_start();
require_once '../config/db.php';

// Təhlükəsizlik: Yalnız admin və ya superadmin girə bilər
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['superadmin', 'admin'])) {
    header("Location: ../login.php");
    exit;
}

// AĞILLI ÖZÜNÜ DÜZƏLDƏN MEXANİZM
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
} catch (PDOException $e) {}

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN permissions TEXT DEFAULT NULL");
} catch (PDOException $e) {}

// --- POST ƏMƏLİYYATLARI (YARATMAQ VƏ REDAKTƏ ETMƏK) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Yeni İstifadəçi Yaratmaq
    if (isset($_POST['action']) && $_POST['action'] === 'create_user') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        // Nömrənin təkrar olub-olmadığını yoxla
        $check = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $check->execute([$phone]);
        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $password, $role]);
        }
        header("Location: users.php");
        exit;
    }

    // 2. İcazələri Dəyişmək
    if (isset($_POST['action']) && $_POST['action'] === 'update_permissions') {
        $user_id = (int)$_POST['user_id'];
        $role = $_POST['role'];
        
        // JSON formatında icazələri toplayırıq
        $perms = [
            'manage_products' => isset($_POST['perm_products']),
            'manage_orders' => isset($_POST['perm_orders']),
            'manage_settings' => isset($_POST['perm_settings']),
        ];
        $perms_json = json_encode($perms);

        // Superadminin öz icazələrini dəyişməsinə icazə vermirik
        $stmt = $pdo->prepare("UPDATE users SET role = ?, permissions = ? WHERE id = ? AND role != 'superadmin'");
        $stmt->execute([$role, $perms_json, $user_id]);
        
        header("Location: users.php");
        exit;
    }
}

// Bütün istifadəçiləri bazadan təhlükəsiz şəkildə çəkirik
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY 
        CASE role 
            WHEN 'superadmin' THEN 1 
            WHEN 'admin' THEN 2 
            WHEN 'courier' THEN 3 
            ELSE 4 
        END, id DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İstifadəçilər - İxlas Telekom Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 900: '#14532d' } } } } }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        
        /* Premium Toggle Switch CSS */
        .toggle-checkbox:checked { right: 0; border-color: #16a34a; }
        .toggle-checkbox:checked + .toggle-label { background-color: #16a34a; }
        .toggle-checkbox:checked + .toggle-label:after { transform: translateX(100%); border-color: white; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden text-gray-800">

    <!-- Sidebar (Digər səhifələrlə eyni YAŞIL rəng kodları və dizayn) -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto relative">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 z-0 sticky top-0">
            <div class="flex flex-col">
                <h1 class="text-xl font-bold text-gray-800">İstifadəçilər və İcazələr</h1>
                <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                    <span>Sistem</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    <span class="text-ixlas-600 font-medium">İdarəçilər</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" placeholder="İstifadəçi axtar..." class="pl-10 pr-4 py-2 bg-gray-100 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-ixlas-500/50 outline-none w-64 transition-all">
                </div>
            </div>
        </header>

        <div class="p-8">
            
            <!-- Əsas İdarəetmə Paneli (TABLAR İŞLƏK VƏZİYYƏTDƏDİR) -->
            <div class="flex justify-between items-end mb-6">
                <!-- Tabs -->
                <div class="flex space-x-1 bg-gray-200/50 p-1 rounded-xl w-max">
                    <button id="btn-tab-all" onclick="switchTab('all')" class="px-5 py-2 text-sm font-bold rounded-lg bg-white text-ixlas-600 shadow-sm border border-gray-100 transition-colors">Bütün İstifadəçilər (<?= count($users) ?>)</button>
                    <button id="btn-tab-admins" onclick="switchTab('admins')" class="px-5 py-2 text-sm font-medium rounded-lg text-gray-500 bg-transparent hover:text-gray-700 transition-colors">İdarə Heyəti</button>
                </div>
                
                <button onclick="openCreateModal()" class="bg-ixlas-600 hover:bg-ixlas-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-ixlas-600/30 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-user-plus"></i> Yeni İdarəçi Yarat
                </button>
            </div>

            <!-- Users Data Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">İstifadəçi</th>
                            <th class="px-6 py-4">Əlaqə</th>
                            <th class="px-6 py-4">Rol / Vəzifə</th>
                            <th class="px-6 py-4 text-right">Əməliyyat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($users as $user): ?>
                        <tr class="user-row hover:bg-gray-50 transition-colors" data-role="<?= $user['role'] ?>">
                            <td class="px-6 py-4 flex items-center gap-3">
                                <!-- Dinamik Avatar -->
                                <?php 
                                    $bg = 'f3f4f6'; $color = '4b5563'; // Default (Müştəri)
                                    if($user['role'] == 'superadmin') { $bg = '14532d'; $color = 'fff'; }
                                    elseif($user['role'] == 'admin') { $bg = 'dcfce7'; $color = '15803d'; }
                                    elseif($user['role'] == 'courier') { $bg = 'fef3c7'; $color = 'd97706'; }
                                ?>
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=<?= $bg ?>&color=<?= $color ?>" class="w-10 h-10 rounded-xl">
                                <div>
                                    <p class="font-bold text-gray-900"><?= htmlspecialchars($user['name']) ?></p>
                                    <p class="text-xs text-gray-400">Qeydiyyat: <?= isset($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : 'Məlum deyil' ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-medium"><?= htmlspecialchars($user['phone']) ?></td>
                            <td class="px-6 py-4">
                                <?php if($user['role'] == 'superadmin'): ?>
                                    <span class="bg-ixlas-900 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 w-max shadow-sm">
                                        <i class="fa-solid fa-crown text-yellow-400"></i> Superadmin
                                    </span>
                                <?php elseif($user['role'] == 'admin'): ?>
                                    <span class="bg-ixlas-100 text-ixlas-700 border border-ixlas-200 text-[10px] font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 w-max">
                                        <i class="fa-solid fa-user-shield"></i> Admin
                                    </span>
                                <?php elseif($user['role'] == 'courier'): ?>
                                    <span class="bg-amber-100 text-amber-700 border border-amber-200 text-[10px] font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 w-max">
                                        <i class="fa-solid fa-motorcycle"></i> Kuryer
                                    </span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 border border-gray-200 text-[10px] font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 w-max">
                                        <i class="fa-solid fa-user"></i> Müştəri
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if($user['role'] == 'superadmin' && $_SESSION['user_role'] != 'superadmin'): ?>
                                    <button class="text-gray-400 cursor-not-allowed" disabled><i class="fa-solid fa-lock"></i></button>
                                <?php else: ?>
                                    <button onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['name'])) ?>', '<?= $user['role'] ?>', '<?= htmlspecialchars(addslashes($user['permissions'] ?? '{}')) ?>')" class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors font-medium text-xs border border-transparent hover:border-blue-200">
                                        <i class="fa-solid fa-pen-to-square mr-1"></i> İcazələri Dəyiş
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">İstifadəçi tapılmadı.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <!-- MODAL 1: YENİ İSTİFADƏÇİ (ADMIN) YARAT -->
    <div id="create-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-lg">Yeni İdarəçi Yarat</h3>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-900"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_user">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Ad və Soyad</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Mobil Nömrə</label>
                    <input type="text" name="phone" required placeholder="055-XXX-XX-XX" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Şifrə</label>
                    <input type="password" name="password" required minlength="6" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Vəzifəsi (Rolu)</label>
                    <select name="role" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 outline-none bg-white">
                        <option value="admin">Sistem Admini</option>
                        <option value="courier">Kuryer</option>
                        <option value="customer">Müştəri</option>
                    </select>
                </div>
                <div class="pt-4 mt-2">
                    <button type="submit" class="w-full py-3.5 bg-ixlas-600 text-white font-bold rounded-xl shadow-lg shadow-ixlas-600/30 hover:bg-ixlas-700 transition-colors">Yadda Saxla və Yarat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: İCAZƏLƏRİ VƏ ROLU DƏYİŞ -->
    <div id="edit-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-gray-200 shadow-2xl overflow-hidden max-w-2xl w-full">
            <div class="bg-ixlas-900 px-8 py-5 flex justify-between items-center">
                <div>
                    <h2 class="text-white font-bold text-lg">İstifadəçi İcazələri</h2>
                    <p id="edit-user-name" class="text-ixlas-200 text-xs mt-1">İstifadəçi üçün səlahiyyət idarəsi</p>
                </div>
                <button onclick="closeEditModal()" class="text-white hover:text-red-400 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="update_permissions">
                <input type="hidden" name="user_id" id="edit-user-id" value="">

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Sol tərəf: Əsas Rol -->
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider border-b border-gray-100 pb-2">Rolu Seç</h3>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-4 border border-gray-200 hover:border-ixlas-300 rounded-xl cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-ixlas-50 text-ixlas-600 flex items-center justify-center"><i class="fa-solid fa-user-shield"></i></div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">Admin</p>
                                        <p class="text-xs text-gray-500">Paneldə əməliyyat edə bilər</p>
                                    </div>
                                </div>
                                <input type="radio" name="role" value="admin" id="role-admin" class="w-5 h-5 text-ixlas-600 focus:ring-ixlas-500">
                            </label>

                            <label class="flex items-center justify-between p-4 border border-gray-200 hover:border-amber-300 rounded-xl cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center"><i class="fa-solid fa-motorcycle"></i></div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">Kuryer</p>
                                        <p class="text-xs text-gray-500">Yalnız çatdırılma ekranını görər</p>
                                    </div>
                                </div>
                                <input type="radio" name="role" value="courier" id="role-courier" class="w-5 h-5 text-amber-500 focus:ring-amber-500">
                            </label>
                            
                            <label class="flex items-center justify-between p-4 border border-gray-200 hover:border-gray-300 rounded-xl cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center"><i class="fa-solid fa-user"></i></div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">Müştəri</p>
                                        <p class="text-xs text-gray-500">Heç bir admin səlahiyyəti yoxdur</p>
                                    </div>
                                </div>
                                <input type="radio" name="role" value="customer" id="role-customer" class="w-5 h-5 text-gray-600 focus:ring-gray-500">
                            </label>
                        </div>
                    </div>

                    <!-- Sağ tərəf: Spesifik İcazələr (Premium Toggle Switch) -->
                    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                        <h3 class="text-sm font-bold text-gray-800 mb-1">Xüsusi Səlahiyyətlər</h3>
                        <p class="text-xs text-gray-500 mb-6">Əgər istifadəçi Admin seçilibsə, nələri edə biləcəyini dəqiqləşdirin.</p>
                        
                        <div class="space-y-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-sm text-gray-800">Məhsul İdarəetməsi</p>
                                    <p class="text-[11px] text-gray-500 mt-0.5">Kataloqa baxa və düzəliş edə bilər</p>
                                </div>
                                <div class="relative inline-block w-12 mr-2 align-middle select-none">
                                    <input type="checkbox" name="perm_products" id="perm-products" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-all duration-300 z-10 top-0 left-0 border-gray-300"/>
                                    <label for="perm-products" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-300"></label>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-sm text-gray-800">Sifarişlərin Təsdiqi</p>
                                    <p class="text-[11px] text-gray-500 mt-0.5">Sifarişləri kuryerə təyin edə bilər</p>
                                </div>
                                <div class="relative inline-block w-12 mr-2 align-middle select-none">
                                    <input type="checkbox" name="perm_orders" id="perm-orders" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-all duration-300 z-10 top-0 left-0 border-gray-300"/>
                                    <label for="perm-orders" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-300"></label>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-sm text-gray-800">Sistem Tənzimləmələri</p>
                                    <p class="text-[11px] text-gray-500 mt-0.5">Başqa istifadəçilərin icazələrini dəyişə bilər</p>
                                </div>
                                <div class="relative inline-block w-12 mr-2 align-middle select-none">
                                    <input type="checkbox" name="perm_settings" id="perm-settings" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-all duration-300 z-10 top-0 left-0 border-gray-300"/>
                                    <label for="perm-settings" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-300"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-xl text-gray-600 font-bold text-sm hover:bg-gray-200 transition-colors">Ləğv Et</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-ixlas-600 text-white font-bold text-sm hover:bg-ixlas-700 shadow-lg shadow-ixlas-600/30 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> İcazələri Yadda Saxla
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT KODLARI (Təblər və Modallar üçün) -->
    <script>
        function switchTab(tabType) {
            const rows = document.querySelectorAll('.user-row');
            const btnAll = document.getElementById('btn-tab-all');
            const btnAdmins = document.getElementById('btn-tab-admins');

            if (tabType === 'all') {
                rows.forEach(row => row.style.display = '');
                
                btnAll.classList.replace('text-gray-500', 'text-ixlas-600');
                btnAll.classList.replace('bg-transparent', 'bg-white');
                btnAll.classList.add('shadow-sm', 'border', 'border-gray-100');
                
                btnAdmins.classList.replace('text-ixlas-600', 'text-gray-500');
                btnAdmins.classList.replace('bg-white', 'bg-transparent');
                btnAdmins.classList.remove('shadow-sm', 'border', 'border-gray-100');
            } else {
                rows.forEach(row => {
                    if (row.dataset.role === 'admin' || row.dataset.role === 'superadmin') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                btnAdmins.classList.replace('text-gray-500', 'text-ixlas-600');
                btnAdmins.classList.replace('bg-transparent', 'bg-white');
                btnAdmins.classList.add('shadow-sm', 'border', 'border-gray-100');
                
                btnAll.classList.replace('text-ixlas-600', 'text-gray-500');
                btnAll.classList.replace('bg-white', 'bg-transparent');
                btnAll.classList.remove('shadow-sm', 'border', 'border-gray-100');
            }
        }

        function openCreateModal() {
            document.getElementById('create-modal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('create-modal').classList.add('hidden');
        }

        function openEditModal(id, name, role, permissionsJSON) {
            // Dəyərləri sıfırla
            document.getElementById('edit-user-id').value = id;
            document.getElementById('edit-user-name').innerText = name + ' adlı istifadəçi';
            
            // Rolu seç
            let roleRadio = document.getElementById('role-' + role);
            if(roleRadio) roleRadio.checked = true;

            // İcazələri parse et
            let perms = {};
            try { perms = JSON.parse(permissionsJSON) || {}; } catch(e) {}
            
            document.getElementById('perm-products').checked = !!perms.manage_products;
            document.getElementById('perm-orders').checked = !!perms.manage_orders;
            document.getElementById('perm-settings').checked = !!perms.manage_settings;

            // Modalı aç
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }
    </script>
</body>
</html>