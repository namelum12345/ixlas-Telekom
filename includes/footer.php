<?php
try {
    // Tənzimləmələr Cədvəli
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    )");
    
    $check_settings = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($check_settings->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
            ('site_desc', 'Ən son texnologiyalar, rəsmi zəmanət və sərfəli qiymətlərlə xidmətinizdəyik. Texnologiya artıq sizə daha yaxındır.'),
            ('social_ig', '#'),
            ('social_fb', '#'),
            ('social_wa', '#'),
            ('contact_address', 'Bakı şəh., Nərimanov r-nu, Əhməd Rəcəbli küç.'),
            ('contact_phone', '*1234 / (012) 555-55-55'),
            ('contact_email', 'info@ixlastelekom.az')
        ");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(50) UNIQUE NOT NULL,
        title VARCHAR(100) NOT NULL,
        icon VARCHAR(50) DEFAULT 'fa-file-lines',
        content TEXT,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0
    )");
    
    if ($pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn() == 0) {
        $insert_page = $pdo->prepare("INSERT INTO pages (slug, title, icon, content, sort_order) VALUES (?, ?, ?, ?, ?)");
        
        $insert_page->execute(['about', 'Haqqımızda', 'fa-building', '<h3 class="text-xl font-bold text-gray-900 mb-4">İxlas Telekom - Etibarlı Texnologiya Partnyorunuz</h3><p class="mb-4">İxlas Telekom olaraq illərdir müştərilərimizə ən son texnologiyaları, mobil cihazları və qadjetləri rəsmi zəmanət və sərfəli qiymətlərlə təqdim edirik. Məqsədimiz sadəcə məhsul satmaq deyil, satış sonrası yüksək xidmət göstərərək müştəri məmnuniyyətini qorumaqdır.</p><p>Peşəkar komandamız, sürətli çatdırılma xidmətimiz və 7/24 müştəri dəstəyimizlə texnologiyanı sizə daha da yaxınlaşdırırıq.</p>', 1]);
        $insert_page->execute(['delivery', 'Çatdırılma Şərtləri', 'fa-truck-fast', '<h3 class="text-xl font-bold text-gray-900 mb-4">Sürətli və Güvənli Çatdırılma</h3><ul class="space-y-4 text-gray-600"><li class="flex items-start gap-3"><i class="fa-solid fa-check text-ixlas-500 mt-1"></i> <div><strong>Bakı daxili çatdırılma:</strong> Sifarişləriniz təsdiqləndikdən sonra ən geci 24 saat ərzində qapınıza qədər pulsuz çatdırılır.</div></li><li class="flex items-start gap-3"><i class="fa-solid fa-check text-ixlas-500 mt-1"></i> <div><strong>Rayonlara çatdırılma:</strong> Poçt və ya kuryer şirkətləri vasitəsilə 2-3 iş günü ərzində həyata keçirilir.</div></li><li class="flex items-start gap-3"><i class="fa-solid fa-check text-ixlas-500 mt-1"></i> <div><strong>Ödəniş:</strong> Məhsulu təhvil alıb yoxladıqdan sonra qapıda nəğd və ya kartla ödəniş edə bilərsiniz.</div></li></ul>', 2]);
        $insert_page->execute(['warranty', 'Zəmanət və Qaytarma', 'fa-shield-halved', '<h3 class="text-xl font-bold text-gray-900 mb-4">Rəsmi Zəmanət Şərtləri</h3><p class="mb-4">Bütün smartfon və planşetlərə istehsalçı tərəfindən 1 illik rəsmi zəmanət verilir.</p><h4 class="text-lg font-bold text-gray-900 mb-2 mt-6">Qaytarma Şərtləri:</h4><p class="mb-2">Azərbaycan Respublikasının İstehlakçıların Hüquqlarının Müdafiəsi haqqında qanununa əsasən, alınan məhsul 14 gün ərzində aşağıdakı şərtlərlə qaytarıla və ya dəyişdirilə bilər:</p><ul class="list-disc list-inside space-y-1 ml-2"><li>Məhsulun qablaşdırması zədələnməyib.</li><li>Məhsul istifadə olunmayıb və əmtəə görünüşünü itirməyib.</li><li>Kassa çeki və zəmanət talonu mütləq təqdim edilməlidir.</li></ul>', 3]);
        $insert_page->execute(['privacy', 'Məxfilik Siyasəti', 'fa-user-lock', '<h3 class="text-xl font-bold text-gray-900 mb-4">Şəxsi Məlumatlarınızın Qorunması</h3><p class="mb-4">İxlas Telekom olaraq müştərilərimizin şəxsi məlumatlarının (ad, soyad, mobil nömrə, ünvan) təhlükəsizliyinə tam zəmanət veririk. Toplanan məlumatlar yalnız aşağıdakı məqsədlər üçün istifadə olunur:</p><ul class="list-disc list-inside space-y-2 ml-2"><li>Sifarişlərinizin dəqiq və vaxtında çatdırılması.</li><li>Müştəri xidmətləri tərəfindən sizinlə əlaqə saxlanılması.</li><li>Xüsusi endirim və kampaniyalar haqqında məlumat verilməsi.</li></ul><p class="mt-4">Sizin məlumatlarınız heç bir halda üçüncü tərəf şirkətlərlə paylaşılmır.</p>', 4]);
        $insert_page->execute(['faq', 'Tez-tez Verilən Suallar', 'fa-circle-question', '<div class="space-y-6"><div><h4 class="font-bold text-gray-900 mb-1">Məhsullarınız orijinaldır?</h4><p class="text-sm">Bəli, saytımızda satılan bütün məhsullar 100% orijinaldır və rəsmi qablaşdırmada təqdim olunur.</p></div><div><h4 class="font-bold text-gray-900 mb-1">Kreditlə satış var?</h4><p class="text-sm">Hal-hazırda yalnız nəğd və bank kartı ilə satış həyata keçiririk.</p></div><div><h4 class="font-bold text-gray-900 mb-1">Sifarişimi necə ləğv edə bilərəm?</h4><p class="text-sm">Şəxsi kabinetinizə daxil olaraq "Sifarişlərim" bölməsindən sifarişi ləğv edə bilərsiniz.</p></div></div>', 5]);
    }

} catch(PDOException $e) {}

$settings = [];
$footer_pages = [];
try {
    $settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    $footer_pages = $pdo->query("SELECT slug, title FROM pages WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
} catch(PDOException $e) {}
?>
    <footer class="bg-ixlas-900 text-ixlas-100 mt-auto border-t-[8px] border-ixlas-600">
        <!-- Mobil üçün xüsusi boşluq və Grid nizamlamaları əlavə edildi -->
        <div class="max-w-7xl mx-auto px-4 py-10 lg:py-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
            
            <!-- Loqo və Təsvir (Planşetdə tam en tutacaq) -->
            <div class="sm:col-span-2 lg:col-span-1 mb-4 sm:mb-0 text-center sm:text-left">
                <a href="index.php" class="flex items-center justify-center sm:justify-start gap-3 mb-6 group">
                    <div class="w-10 h-10 bg-white text-ixlas-600 rounded-xl flex items-center justify-center text-xl shadow-lg">
                        <i class="fa-solid fa-signal"></i>
                    </div>
                    <span class="text-2xl font-extrabold text-white tracking-tight">İxlas Telekom</span>
                </a>
                <p class="text-sm text-ixlas-200 mb-6 leading-relaxed max-w-sm mx-auto sm:mx-0">
                    <?= htmlspecialchars($settings['site_desc'] ?? 'Ən son texnologiyalar xidmətinizdədir.') ?>
                </p>
                <div class="flex gap-4 justify-center sm:justify-start">
                    <a href="<?= htmlspecialchars($settings['social_ig'] ?? '#') ?>" target="_blank" class="w-10 h-10 rounded-full bg-ixlas-800 flex items-center justify-center hover:bg-ixlas-500 hover:text-white transition-all hover:-translate-y-1"><i class="fa-brands fa-instagram text-lg"></i></a>
                    <a href="<?= htmlspecialchars($settings['social_fb'] ?? '#') ?>" target="_blank" class="w-10 h-10 rounded-full bg-ixlas-800 flex items-center justify-center hover:bg-ixlas-500 hover:text-white transition-all hover:-translate-y-1"><i class="fa-brands fa-facebook-f text-lg"></i></a>
                    <a href="<?= htmlspecialchars($settings['social_wa'] ?? '#') ?>" target="_blank" class="w-10 h-10 rounded-full bg-ixlas-800 flex items-center justify-center hover:bg-ixlas-500 hover:text-white transition-all hover:-translate-y-1"><i class="fa-brands fa-whatsapp text-lg"></i></a>
                </div>
            </div>
            
            <!-- Dinamik Məlumat Linkləri -->
            <div class="text-center sm:text-left">
                <h3 class="font-bold text-white mb-5 sm:mb-6 text-lg">Məlumat</h3>
                <ul class="space-y-3 text-sm flex flex-col items-center sm:items-start">
                    <?php if(!empty($footer_pages)): foreach($footer_pages as $fp): ?>
                        <li><a href="page.php?p=<?= htmlspecialchars($fp['slug']) ?>" class="text-ixlas-200 hover:text-white transition-colors flex items-center gap-2 group"><i class="fa-solid fa-angle-right text-xs text-ixlas-500 group-hover:translate-x-1 transition-transform hidden sm:inline-block"></i> <?= htmlspecialchars($fp['title']) ?></a></li>
                    <?php endforeach; else: ?>
                        <li class="text-ixlas-400">Səhifə yoxdur</li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Müştəri Xidmətləri Linkləri -->
            <div class="text-center sm:text-left">
                <h3 class="font-bold text-white mb-5 sm:mb-6 text-lg">Müştəri Xidmətləri</h3>
                <ul class="space-y-3 text-sm flex flex-col items-center sm:items-start">
                    <li><a href="profile.php" class="text-ixlas-200 hover:text-white transition-colors flex items-center gap-2 group"><i class="fa-solid fa-angle-right text-xs text-ixlas-500 group-hover:translate-x-1 transition-transform hidden sm:inline-block"></i> Şəxsi Kabinet</a></li>
                    <li><a href="cart.php" class="text-ixlas-200 hover:text-white transition-colors flex items-center gap-2 group"><i class="fa-solid fa-angle-right text-xs text-ixlas-500 group-hover:translate-x-1 transition-transform hidden sm:inline-block"></i> Səbətim</a></li>
                    <li><a href="profile.php?tab=orders" class="text-ixlas-200 hover:text-white transition-colors flex items-center gap-2 group"><i class="fa-solid fa-angle-right text-xs text-ixlas-500 group-hover:translate-x-1 transition-transform hidden sm:inline-block"></i> Sifarişlərin izlənməsi</a></li>
                </ul>
            </div>
            
            <!-- Dinamik Əlaqə Məlumatları (Planşetdə tam en tutacaq) -->
            <div class="sm:col-span-2 lg:col-span-1 mt-4 sm:mt-0">
                <h3 class="font-bold text-white mb-5 sm:mb-6 text-lg text-center sm:text-left">Bizimlə Əlaqə</h3>
                <ul class="space-y-4 text-sm max-w-sm mx-auto sm:mx-0">
                    <li class="flex flex-col sm:flex-row items-center sm:items-start gap-3 sm:gap-4 text-center sm:text-left">
                        <div class="w-8 h-8 rounded-full bg-ixlas-800 flex items-center justify-center text-ixlas-400 shrink-0"><i class="fa-solid fa-location-dot"></i></div>
                        <span class="text-ixlas-200 sm:pt-1"><?= htmlspecialchars($settings['contact_address'] ?? '') ?></span>
                    </li>
                    <li class="flex flex-col sm:flex-row items-center sm:items-start gap-3 sm:gap-4 text-center sm:text-left">
                        <div class="w-8 h-8 rounded-full bg-ixlas-800 flex items-center justify-center text-ixlas-400 shrink-0"><i class="fa-solid fa-phone"></i></div>
                        <span class="text-ixlas-200 sm:pt-1"><?= htmlspecialchars($settings['contact_phone'] ?? '') ?></span>
                    </li>
                    <li class="flex flex-col sm:flex-row items-center sm:items-start gap-3 sm:gap-4 text-center sm:text-left">
                        <div class="w-8 h-8 rounded-full bg-ixlas-800 flex items-center justify-center text-ixlas-400 shrink-0"><i class="fa-solid fa-envelope"></i></div>
                        <span class="text-ixlas-200 sm:pt-1"><?= htmlspecialchars($settings['contact_email'] ?? '') ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Müəllif Hüquqları -->
        <div class="border-t border-ixlas-800">
            <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row justify-between items-center text-xs text-ixlas-400 gap-4 text-center md:text-left">
                <p>&copy; <?= date('Y') ?> İxlas Telekom. Bütün hüquqlar qorunur.</p>
                <div class="flex gap-3 text-ixlas-500">
                    <i class="fa-brands fa-cc-visa text-3xl opacity-70 hover:opacity-100 transition-opacity"></i>
                    <i class="fa-brands fa-cc-mastercard text-3xl opacity-70 hover:opacity-100 transition-opacity"></i>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>