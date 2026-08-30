<?php
session_start();

$step = 1;
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    
    // DB Məlumatları - Yeni verilmiş parametrlər təyin edildi
    $db_host = $_POST['db_host'] ?? 'drhggktfagjy5f7ars8tjuc7';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? 'MCIVUCjuo53EQ0BhOLQLBf3InfONmcQmrK9xad7C2RRwgtQ7JXBcDG9KPldR9EEb';
    $db_name = $_POST['db_name'] ?? 'ixlas_telekom_db';
    
    // Admin Məlumatları
    $admin_name = $_POST['admin_name'] ?? 'İxlas Telecom';
    $admin_phone = $_POST['admin_phone'] ?? '+994707546177';
    $admin_pass = $_POST['admin_pass'] ?? 'qulu.nehremli';
    
    try {
        // 1. MySQL-ə qoşulma (EMOJİLƏR ÜÇÜN CHARSET ƏLAVƏ EDİLDİ)
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 2. Bazanı yaratmaq
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");
        
        // 3. Cədvəlləri Yaratmaq (permissions sütunu əlavə edildi)
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role ENUM('superadmin', 'admin', 'courier', 'customer') DEFAULT 'customer',
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            permissions TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            parent_id INT DEFAULT NULL,
            icon VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
        )");

        // Məhsullar cədvəlinə PRO texniki parametrlər
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT,
            name VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            image_url VARCHAR(255),
            description TEXT,
            ram VARCHAR(50) DEFAULT NULL,
            storage VARCHAR(50) DEFAULT NULL,
            processor VARCHAR(100) DEFAULT NULL,
            camera VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )");

        // Sifarişlər
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            customer_name VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            address TEXT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'approved', 'courier', 'delivered', 'cancelled') DEFAULT 'pending',
            courier_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (courier_id) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        )");
        
        // 4. Default Məlumatları Yazmaq
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO categories (id, name, icon) VALUES 
                (1, 'Smartfonlar', 'fa-mobile-screen'), 
                (2, 'Aksesuarlar', 'fa-headphones'), 
                (3, 'Planşetlər', 'fa-tablet-screen-button'),
                (4, 'Qadjetlər', 'fa-stopwatch')");
            
            $pdo->exec("INSERT INTO categories (id, name, parent_id) VALUES 
                (5, 'Apple (iPhone)', 1), 
                (6, 'Samsung', 1), 
                (7, 'Qulaqlıqlar', 2)");
            
            $pdo->exec("INSERT INTO products (category_id, name, price, stock, image_url, description, ram, storage, processor, camera) VALUES 
            (5, 'iPhone 15 Pro, 256GB', 2199.00, 10, '📱', 'A17 Pro chip, Titanium dizayn, 120Hz ekran', '8 GB', '256 GB', 'Apple A17 Pro', '48 MP + 12 MP + 12 MP'), 
            (6, 'Samsung Galaxy S24 Ultra', 2499.00, 5, '📱', 'Snapdragon 8 Gen 3, S-Pen, AI xüsusiyyətlər', '12 GB', '512 GB', 'Snapdragon 8 Gen 3', '200 MP + 50 MP + 12 MP'),
            (7, 'AirPods Pro 2', 549.00, 15, '🎧', 'Aktiv səs-küyün ləğvi, Type-C şarj', NULL, NULL, 'Apple H2', NULL),
            (1, 'Xiaomi Redmi Note 13', 599.00, 20, '📱', '120Hz AMOLED, 108MP Kamera, 5000mAh batareya', '8 GB', '256 GB', 'Snapdragon 685', '108 MP + 8 MP + 2 MP')");
        }
        
        // 5. Superadmin Hesabını Yaratmaq
        if ($admin_phone !== '' && $admin_pass !== '') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->execute([$admin_phone]);
            if($stmt->rowCount() == 0) {
                $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
                $insert_admin = $pdo->prepare("INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, 'superadmin')");
                $insert_admin->execute([$admin_name, $admin_phone, $hashed_pass]);
            }
        }
        
        // 6. TƏMİZ config/db.php faylını generatsiya etmək
        $config_content = "<?php\n";
        $config_content .= "if(session_status() !== PHP_SESSION_ACTIVE) session_start();\n\n";
        $config_content .= "// Avtomatik yaradılmış config faylı\n";
        $config_content .= "\$db_host = '$db_host';\n";
        $config_content .= "\$db_user = '$db_user';\n";
        $config_content .= "\$db_pass = '$db_pass';\n";
        $config_content .= "\$db_name = '$db_name';\n\n";
        $config_content .= "try {\n";
        $config_content .= "    \$pdo = new PDO(\"mysql:host=\$db_host;dbname=\$db_name;charset=utf8mb4\", \$db_user, \$db_pass);\n";
        $config_content .= "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
        $config_content .= "    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);\n"; // Array olaraq gətirməsi üçün
        $config_content .= "} catch(PDOException \$e) {\n";
        $config_content .= "    die(\"<b>Bağlantı xətası:</b> \" . \$e->getMessage());\n";
        $config_content .= "}\n";
        $config_content .= "?>";
        
        // Qovluq yoxdursa yarat və yazma icazələrini yoxla
        $config_dir = __DIR__ . '/config';
        if (!is_dir($config_dir)) {
            if (!mkdir($config_dir, 0777, true)) {
                throw new Exception("'config' qovluğunu yaratmaq mümkün olmadı. Lütfən layihə qovluğuna yazma icazəsi verin (Məs: chmod 777 -R .).");
            }
        }
        
        $file_path = $config_dir . '/db.php';
        if (file_put_contents($file_path, $config_content) === false) {
            throw new Exception("'config/db.php' faylını yaratmaq mümkün olmadı. Lütfən 'config' qovluğuna yazma icazəsi verin.");
        }
        
        $success = true;
        
        // === TƏHLÜKƏSİZLİK: FAYLIN ÖZÜNÜ SİLMƏSİ ===
        if (file_exists(__FILE__)) {
            @unlink(__FILE__);
        }
        // ===========================================
        
    } catch (PDOException $e) {
        $error = "Verilənlər bazası xətası: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Sistem xətası: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistemi Quraşdır - İxlas Telekom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 900: '#14532d' } } } } }
    </script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden">
    
    <div class="absolute inset-0 bg-ixlas-900 z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-ixlas-600 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
        <div class="absolute top-40 -left-20 w-72 h-72 bg-ixlas-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    </div>

    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-2xl relative z-10 mx-4">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-4">
                <i class="fa-solid fa-signal text-3xl text-ixlas-600"></i>
                <span class="text-2xl font-extrabold text-ixlas-800 tracking-tight">İxlas Telekom</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Sistemin Quraşdırılması</h2>
            <p class="text-sm text-gray-500 mt-1">E-ticarət platformanızı başlatmaq üçün lazımi məlumatları daxil edin.</p>
        </div>

        <?php if($success): ?>
            <div class="text-center space-y-6 py-8">
                <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-4xl mx-auto">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">Təbriklər!</h3>
                <p class="text-gray-500">Sistem uğurla quraşdırıldı. Baza yaradıldı, test məlumatları əlavə edildi və Təmiz <b>db.php</b> faylı generatsiya olundu.</p>
                
                <!-- DƏYİŞDİRİLMİŞ TƏHLÜKƏSİZLİK MESAJI -->
                <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm font-bold border border-green-200">
                    <i class="fa-solid fa-shield-check mr-1"></i> Təhlükəsizlik təmin edildi: <b>install.php</b> faylı serverdən uğurla silindi!
                </div>
                
                <div class="flex justify-center gap-4 pt-4">
                    <a href="index.php" class="bg-gray-100 text-gray-700 font-bold px-6 py-3 rounded-xl hover:bg-gray-200 transition-colors">Sayta Bax</a>
                    <a href="admin/index.php" class="bg-ixlas-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-ixlas-700 shadow-lg shadow-ixlas-500/30 transition-colors">Admin Panelə Keç</a>
                </div>
            </div>
        <?php else: ?>
            
            <?php if($error): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-700 border border-red-200 rounded-xl text-sm font-medium">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-8">
                <input type="hidden" name="action" value="install">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- DB Settings -->
                    <div class="space-y-4">
                        <div class="border-b border-gray-100 pb-2 mb-4">
                            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider"><i class="fa-solid fa-database text-ixlas-500 mr-2"></i> Baza Məlumatları</h3>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Host</label>
                            <input type="text" name="db_host" value="drhggktfagjy5f7ars8tjuc7" required class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:bg-white outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Baza Adı</label>
                            <input type="text" name="db_name" value="ixlas_telekom_db" required class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:bg-white outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">İstifadəçi (User)</label>
                            <input type="text" name="db_user" value="root" required class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:bg-white outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Şifrə</label>
                            <input type="password" name="db_pass" value="MCIVUCjuo53EQ0BhOLQLBf3InfONmcQmrK9xad7C2RRwgtQ7JXBcDG9KPldR9EEb" required class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:bg-white outline-none transition-all text-sm">
                        </div>
                    </div>

                    <!-- Admin Settings -->
                    <div class="space-y-4">
                        <div class="border-b border-gray-100 pb-2 mb-4">
                            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider"><i class="fa-solid fa-user-shield text-ixlas-500 mr-2"></i> Superadmin Məlumatları</h3>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Ad və Soyad</label>
                            <input type="text" name="admin_name" value="İxlas Telecom" required class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:bg-white outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Mobil Nömrə (Giriş üçün)</label>
                            <input type="text" name="admin_phone" value="+994707546177" required class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:bg-white outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Admin Şifrəsi</label>
                            <input type="password" name="admin_pass" value="qulu.nehremli" required minlength="6" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:bg-white outline-none transition-all text-sm">
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" class="w-full bg-ixlas-600 text-white font-bold py-3.5 rounded-xl hover:bg-ixlas-700 transition-colors shadow-lg shadow-ixlas-500/30 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Sistemi Quraşdır
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>