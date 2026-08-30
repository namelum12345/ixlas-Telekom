<?php
session_start();
require_once '../config/db.php';
require_once '../config/auth_check.php';

// Yalnız superadminlər tənzimləmələri dəyişə bilsin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    die("Bu səhifəyə daxil olmaq üçün Superadmin səlahiyyətiniz olmalıdır.");
}

// Əgər form göndərilibsə, bazadakı dəyərləri yenilə
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $update_stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    foreach ($_POST['settings'] as $key => $value) {
        // Hər bir setting_key üçün bazanı yeniləyir
        $clean_value = trim($value);
        $update_stmt->execute([$key, $clean_value, $clean_value]);
    }
    
    header("Location: settings.php?msg=success");
    exit;
}

// Bütün tənzimləmələri oxu
$settings_raw = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Tənzimləmələri - İxlas Telekom Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 800: '#166534', 900: '#14532d' } } } } }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden text-gray-800">

    <!-- Premium Yaşıl Sidebar (Digər səhifələrlə eyni) -->
    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto relative">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 z-0 sticky top-0">
            <div class="flex flex-col">
                <h1 class="text-xl font-bold text-gray-800">Sistem Tənzimləmələri</h1>
                <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                    <span>Sistem</span><i class="fa-solid fa-chevron-right text-[10px]"></i><span class="text-ixlas-600 font-medium">Əsas Ayarlar</span>
                </div>
            </div>
        </header>

        <div class="p-8 max-w-4xl mx-auto w-full">
            
            <?php if(isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
                <div class="mb-6 p-4 bg-green-50 text-green-700 border border-green-200 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-circle-check text-lg"></i> Tənzimləmələr uğurla yadda saxlanıldı!
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="save_settings">
                
                <!-- Sayt Haqqında Tənzimləmələr -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-100 px-6 py-4">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-globe text-ixlas-600"></i> Ümumi Sayt Məlumatları</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Qısa Təsvir (Footer üçün)</label>
                            <textarea name="settings[site_desc]" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all text-sm resize-none"><?= htmlspecialchars($settings_raw['site_desc'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Əlaqə Məlumatları -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-100 px-6 py-4">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-address-book text-ixlas-600"></i> Əlaqə Məlumatları</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Ünvan</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-location-dot"></i></span>
                                <input type="text" name="settings[contact_address]" value="<?= htmlspecialchars($settings_raw['contact_address'] ?? '') ?>" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Əlaqə Nömrəsi</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-phone"></i></span>
                                <input type="text" name="settings[contact_phone]" value="<?= htmlspecialchars($settings_raw['contact_phone'] ?? '') ?>" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Email Ünvanı</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="settings[contact_email]" value="<?= htmlspecialchars($settings_raw['contact_email'] ?? '') ?>" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sosial Şəbəkələr -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-100 px-6 py-4">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-hashtag text-ixlas-600"></i> Sosial Şəbəkələr (Linklər)</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Instagram Linki</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-brands fa-instagram"></i></span>
                                <input type="text" name="settings[social_ig]" value="<?= htmlspecialchars($settings_raw['social_ig'] ?? '') ?>" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Facebook Linki</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-brands fa-facebook"></i></span>
                                <input type="text" name="settings[social_fb]" value="<?= htmlspecialchars($settings_raw['social_fb'] ?? '') ?>" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">WhatsApp Linki (və ya nömrə)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-brands fa-whatsapp"></i></span>
                                <input type="text" name="settings[social_wa]" value="<?= htmlspecialchars($settings_raw['social_wa'] ?? '') ?>" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-ixlas-600 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-ixlas-600/30 hover:bg-ixlas-700 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Dəyişiklikləri Yadda Saxla
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>