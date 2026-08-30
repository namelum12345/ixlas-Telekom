<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once 'config/db.php';

// Əgər istifadəçi artıq daxil olubsa, onu roluna uyğun səhifəyə yönləndir
if(isset($_SESSION['user_role'])) {
    if($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin') header("Location: admin/index.php");
    elseif($_SESSION['user_role'] === 'courier') header("Location: courier/index.php");
    else header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş və Qeydiyyat - İxlas Telekom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { colors: { ixlas: { 50: '#f0fdf4', 100: '#dcfce7', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 900: '#14532d' } } } } }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap'); 
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden">
    
    <!-- Premium Arxa Plan -->
    <div class="absolute inset-0 bg-ixlas-900 z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-ixlas-600 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse"></div>
        <div class="absolute top-40 -left-20 w-72 h-72 bg-ixlas-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Login/Register Qutusu -->
    <div class="bg-white p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-md relative z-10 mx-4 transition-all duration-500 ease-in-out">
        
        <!-- Başlıq -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center gap-2 mb-4 group">
                <div class="w-10 h-10 bg-ixlas-600 text-white rounded-xl flex items-center justify-center text-xl shadow-lg">
                    <i class="fa-solid fa-signal"></i>
                </div>
                <span class="text-2xl font-extrabold text-gray-900 tracking-tight">İxlas Telekom</span>
            </a>
            <h2 id="form-title" class="text-2xl font-bold text-gray-900">Xoş Gəldiniz!</h2>
            <p id="form-subtitle" class="text-sm text-gray-500 mt-1">Davam etmək üçün sistemə daxil olun</p>
        </div>

        <!-- Xəta Mesajları -->
        <?php if(isset($_GET['error'])): ?>
            <div class="mb-5 p-3 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm font-medium flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php 
                    if($_GET['error'] == 'exists') echo "Bu nömrə ilə artıq hesab mövcuddur!";
                    elseif($_GET['error'] == 'wrong') echo "Nömrə və ya şifrə yanlışdır!";
                    elseif($_GET['error'] == 'password_mismatch') echo "Şifrələr uyğun gəlmir!";
                    else echo "Sistem xətası baş verdi.";
                ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="actions/auth_process.php" method="POST" id="authForm" onsubmit="return validateForm()" class="space-y-4">
            <input type="hidden" name="action" id="form-action" value="login">
            
            <!-- Ad və Soyad (Yalnız Qeydiyyat) -->
            <div id="name-field" class="hidden opacity-0 transition-all duration-300 transform translate-y-2">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Ad və Soyad</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-regular fa-user"></i></span>
                    <input type="text" name="name" id="name" placeholder="Məs: Cavidan Əliyev" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all bg-gray-50 focus:bg-white text-sm">
                </div>
            </div>

            <!-- Mobil Nömrə (Hər ikisi) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Mobil Nömrə</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-phone"></i></span>
                    <input type="text" name="phone" required placeholder="055-XXX-XX-XX" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all bg-gray-50 focus:bg-white text-sm">
                </div>
            </div>

            <!-- Şifrə (Hər ikisi) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Şifrə</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" id="password" required minlength="6" placeholder="••••••••" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all bg-gray-50 focus:bg-white text-sm">
                </div>
            </div>

            <!-- Şifrəni Təsdiqlə (Yalnız Qeydiyyat) -->
            <div id="confirm-password-field" class="hidden opacity-0 transition-all duration-300 transform translate-y-2">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Şifrəni Təsdiqlə</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-shield-check"></i></span>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-ixlas-500 focus:ring-2 focus:ring-ixlas-100 outline-none transition-all bg-gray-50 focus:bg-white text-sm">
                </div>
                <p id="password-error" class="text-xs text-red-500 mt-1 hidden font-medium"><i class="fa-solid fa-triangle-exclamation"></i> Şifrələr eyni deyil!</p>
            </div>

            <!-- Qaydalar və Şərtlər (Yalnız Qeydiyyat) -->
            <div id="terms-field" class="hidden opacity-0 transition-all duration-300 pt-2">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" id="terms" name="terms" class="w-4 h-4 rounded text-ixlas-600 focus:ring-ixlas-500 border-gray-300 cursor-pointer">
                    <span class="text-xs text-gray-600 select-none"><a href="#" class="text-ixlas-600 font-bold hover:underline">İstifadə Şərtləri</a> ilə razıyam.</span>
                </label>
            </div>

            <!-- Düymə -->
            <button type="submit" id="submit-btn" class="w-full bg-ixlas-600 text-white font-bold py-3.5 mt-2 rounded-xl hover:bg-ixlas-700 transition-all shadow-lg shadow-ixlas-500/30 flex justify-center items-center gap-2">
                Daxil Ol <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        <!-- Keçid bölməsi (Login / Register Switch) -->
        <div class="mt-8 text-center text-sm border-t border-gray-100 pt-6">
            <span class="text-gray-500" id="toggle-text">Hesabınız yoxdur?</span>
            <button type="button" onclick="toggleForm()" class="text-ixlas-600 font-bold hover:underline ml-1 focus:outline-none" id="toggle-btn">İndi qeydiyyatdan keçin</button>
        </div>
    </div>

    <script>
        let isLogin = true;

        function toggleForm() {
            isLogin = !isLogin;
            const formAction = document.getElementById('form-action');
            const nameField = document.getElementById('name-field');
            const confirmField = document.getElementById('confirm-password-field');
            const termsField = document.getElementById('terms-field');
            const submitBtn = document.getElementById('submit-btn');
            const toggleText = document.getElementById('toggle-text');
            const toggleBtn = document.getElementById('toggle-btn');
            const title = document.getElementById('form-title');
            const subtitle = document.getElementById('form-subtitle');

            if (!isLogin) {
                // QEYDİYYAT REJİMİ
                formAction.value = 'register';
                
                // Elementləri göstər
                nameField.classList.remove('hidden');
                confirmField.classList.remove('hidden');
                termsField.classList.remove('hidden');
                
                // HTML5 tələbləri
                document.getElementById('name').required = true;
                document.getElementById('confirm_password').required = true;
                
                // Animasiya gecikməsi
                setTimeout(() => {
                    nameField.classList.remove('opacity-0', 'translate-y-2');
                    confirmField.classList.remove('opacity-0', 'translate-y-2');
                    termsField.classList.remove('opacity-0');
                }, 50);

                // Mətnlərin dəyişməsi
                title.innerText = 'Yeni Hesab Yarat';
                subtitle.innerText = 'Məlumatlarınızı dolduraraq alış-verişə başlayın';
                submitBtn.innerHTML = 'Qeydiyyatı Tamamla <i class="fa-solid fa-user-plus"></i>';
                toggleText.innerText = 'Artıq hesabınız var?';
                toggleBtn.innerText = 'Sistemə daxil olun';
            } else {
                // GİRİŞ REJİMİ
                formAction.value = 'login';
                
                // HTML5 tələblərini ləğv et
                document.getElementById('name').required = false;
                document.getElementById('confirm_password').required = false;

                // Animasiya ilə gizlət
                nameField.classList.add('opacity-0', 'translate-y-2');
                confirmField.classList.add('opacity-0', 'translate-y-2');
                termsField.classList.add('opacity-0');

                // Hidden klassını geri qaytar
                setTimeout(() => {
                    nameField.classList.add('hidden');
                    confirmField.classList.add('hidden');
                    termsField.classList.add('hidden');
                }, 300);

                // Mətnlərin dəyişməsi
                title.innerText = 'Xoş Gəldiniz!';
                subtitle.innerText = 'Davam etmək üçün sistemə daxil olun';
                submitBtn.innerHTML = 'Daxil Ol <i class="fa-solid fa-arrow-right-to-bracket"></i>';
                toggleText.innerText = 'Hesabınız yoxdur?';
                toggleBtn.innerText = 'İndi qeydiyyatdan keçin';
            }
        }

        // Forma göndərilməmişdən əvvəl məlumatların yoxlanması
        function validateForm() {
            if(!isLogin) {
                const pass = document.getElementById('password').value;
                const confirm = document.getElementById('confirm_password').value;
                const terms = document.getElementById('terms').checked;
                
                // Şifrə yoxlanışı
                if (pass !== confirm) {
                    document.getElementById('password-error').classList.remove('hidden');
                    document.getElementById('confirm_password').classList.add('border-red-500', 'bg-red-50');
                    return false;
                } else {
                    document.getElementById('password-error').classList.add('hidden');
                    document.getElementById('confirm_password').classList.remove('border-red-500', 'bg-red-50');
                }

                // Şərtlər yoxlanışı
                if (!terms) {
                    alert('Zəhmət olmasa İstifadə Şərtləri ilə razılaşdığınızı qeyd edin.');
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>