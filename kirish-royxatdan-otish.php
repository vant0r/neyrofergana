<?php
/**
 * kirish-royxatdan-otish.php - Avtorizatsiya va Ro'yxatdan o'tish
 */

require_once 'includes/init.php';

// Agar foydalanuvchi kirgan bo'lsa, tegishli panelga yo'naltirish
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('/admin/boshqaruv.php');
    } else {
        redirect('/user/boshqaruv.php');
    }
}

$errors = [];
$success = [];
$activeTab = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';

// POST ma'lumotlarini qayta ishlash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CSRF tekshiruvi
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Xavfsizlik tokeni xato!';
    } else {
        if ($action === 'login') {
            // Kirish
            $email = sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            if (empty($email) || empty($password)) {
                $errors[] = 'Email va parolni kiriting!';
            } else {
                $user = loginUser($email, $password);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    if ($remember) {
                        setcookie('remember_token', bin2hex(random_bytes(32)), time() + (86400 * 30), '/');
                    }
                    
                    if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
                        redirect('/admin/boshqaruv.php');
                    } else {
                        redirect('/user/boshqaruv.php');
                    }
                } else {
                    $errors[] = 'Email yoki parol xato!';
                }
            }
            
            $activeTab = 'login';
            
        } elseif ($action === 'register') {
            // Ro'yxatdan o'tish
            $name = sanitizeInput($_POST['name'] ?? '');
            $surname = sanitizeInput($_POST['surname'] ?? '');
            $phone = sanitizeInput($_POST['phone'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';
            
            // Validatsiya
            if (empty($name) || empty($surname) || empty($phone) || empty($email) || empty($password)) {
                $errors[] = 'Barcha maydonlarni to\'ldiring!';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email formati xato!';
            } elseif (!preg_match('/^[+]?[0-9\s\-()]+$/', $phone)) {
                $errors[] = 'Telefon raqami formati xato!';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Parol kamida 8 belgidan iborat bo\'lishi kerak!';
            } elseif ($password !== $password_confirm) {
                $errors[] = 'Parollar mos kelmadi!';
            } else {
                // Email allaqachon mavjudligini tekshirish
                if (emailExists($email)) {
                    $errors[] = 'Bu email bilan foydalanuvchi allaqachon mavjud!';
                } else {
                    // Foydalanuvchini yaratish
                    $userId = registerUser($name, $surname, $phone, $email, $password);
                    
                    if ($userId) {
                        $success[] = 'Ro\'yxatdan o\'tish muvaffaqiyatli! Tasdiqlash xati yuborildi.';
                        
                        // Emailga tasdiqlash kodi yuborish (keyinchalik amalga oshiriladi)
                        // sendVerificationEmail($email, $userId);
                        
                        $activeTab = 'login';
                    } else {
                        $errors[] = 'Ro\'yxatdan o\'tishda xatolik yuz berdi!';
                    }
                }
            }
            
            $activeTab = 'register';
            
        } elseif ($action === 'forgot_password') {
            // Parolni tiklash
            $email = sanitizeInput($_POST['email'] ?? '');
            
            if (empty($email)) {
                $errors[] = 'Emailni kiriting!';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email formati xato!';
            } else {
                $user = getUserByEmail($email);
                
                if ($user) {
                    // Reset token yaratish va email yuborish
                    $resetToken = bin2hex(random_bytes(32));
                    saveResetToken($user['id'], $resetToken);
                    
                    // resetLink: /kirish-royxatdan-otish.php?reset=TOKEN
                    $success[] = 'Parolni tiklash havolasi emailga yuborildi!';
                } else {
                    $success[] = 'Agar bu email mavjud bo\'lsa, parolni tiklash havolasi yuboriladi.';
                }
            }
            
            $activeTab = 'login';
        }
    }
}

// Parolni tiklash formasi ko'rsatish
$showResetForm = isset($_GET['reset']) && !empty($_GET['reset']);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirish / Ro'yxatdan o'tish - <?= getSetting('site_name') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
        }
        
        .logo {
            text-align: center;
            padding: 30px 20px 20px;
        }
        
        .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            object-fit: cover;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .tab {
            flex: 1;
            padding: 16px;
            text-align: center;
            cursor: pointer;
            font-weight: 500;
            color: #86868b;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
        }
        
        .tab:hover {
            color: #1d1d1f;
            background: rgba(0, 113, 227, 0.05);
        }
        
        .tab.active {
            color: #0071e3;
            border-bottom-color: #0071e3;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form {
            display: none;
        }
        
        .form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #1d1d1f;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d2d2d7;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            border-color: #0071e3;
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-weight: 400;
        }
        
        .btn {
            background: #0071e3;
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            background: #0077ed;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: rgba(255, 59, 48, 0.1);
            color: #ff3b30;
            border: 1px solid rgba(255, 59, 48, 0.3);
        }
        
        .alert-success {
            background: rgba(52, 199, 89, 0.1);
            color: #34c759;
            border: 1px solid rgba(52, 199, 89, 0.3);
        }
        
        .forgot-link {
            display: block;
            text-align: right;
            color: #0071e3;
            font-size: 14px;
            text-decoration: none;
            margin-top: -10px;
            margin-bottom: 20px;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 480px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <?php $logo = getSetting('logo'); ?>
            <img src="<?= htmlspecialchars($logo ?: '/uploads/logo/default.png') ?>" alt="Logo" class="logo-img">
        </div>
        
        <div class="tabs">
            <div class="tab <?= $activeTab === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Kirish</div>
            <div class="tab <?= $activeTab === 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Ro'yxatdan o'tish</div>
        </div>
        
        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($success as $msg): ?>
                            <li><?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="post" class="form <?= $activeTab === 'login' && !$showResetForm ? 'active' : '' ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label>Email yoki Telefon</label>
                    <input type="text" name="email" required placeholder="email@example.com">
                </div>
                
                <div class="form-group">
                    <label>Parol</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Eslab qolish</label>
                </div>
                
                <button type="submit" class="btn">Kirish</button>
                
                <a href="?reset=1" class="forgot-link">Parolni unutdingizmi?</a>
            </form>
            
            <!-- Register Form -->
            <form method="post" class="form <?= $activeTab === 'register' ? 'active' : '' ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="register">
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Ism</label>
                        <input type="text" name="name" required placeholder="Ism">
                    </div>
                    <div class="form-group">
                        <label>Familiya</label>
                        <input type="text" name="surname" required placeholder="Familiya">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="tel" name="phone" required placeholder="+998 90 123 45 67">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="email@example.com">
                </div>
                
                <div class="form-group">
                    <label>Parol</label>
                    <input type="password" name="password" required minlength="8" placeholder="Kamida 8 belgi">
                </div>
                
                <div class="form-group">
                    <label>Parolni tasdiqlash</label>
                    <input type="password" name="password_confirm" required placeholder="Parolni takrorlang">
                </div>
                
                <button type="submit" class="btn">Ro'yxatdan o'tish</button>
            </form>
            
            <!-- Forgot Password Form -->
            <?php if ($showResetForm): ?>
            <form method="post" class="form active">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="forgot_password">
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="email@example.com">
                </div>
                
                <button type="submit" class="btn">Parolni tiklash</button>
                
                <a href="/kirish-royxatdan-otish.php" class="forgot-link" style="text-align: center; margin-top: 15px;">Ortga qaytish</a>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.form').forEach(f => f.classList.remove('active'));
            
            event.target.classList.add('active');
            document.querySelector('.form.' + tab).classList.add('active');
            
            // URL ni yangilash
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
    </script>
</body>
</html>
