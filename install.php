<?php
/**
 * install.php - Klinika veb-saytini o'rnatish
 * Server talablari, DB yaratish, admin hisob, konfiguratsiya
 */

// Xatoliklarni ko'rsatish (o'rnatishdan keyin o'chiriladi)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors = [];
$success = [];
$db_config = [];

// POST ma'lumotlarini qayta ishlash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CSRF token tekshiruvi
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['install_token'] ?? '') {
        $errors[] = 'CSRF token xato!';
    }
}

// Sessiyani boshlash
session_start();
if (empty($_SESSION['install_token'])) {
    $_SESSION['install_token'] = bin2hex(random_bytes(32));
}

// 1-qadam: Server talablarini tekshirish
function checkServerRequirements() {
    $requirements = [
        'PHP Version >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'mbstring Extension' => extension_loaded('mbstring'),
        'GD Extension' => extension_loaded('gd'),
        'cURL Extension' => extension_loaded('curl'),
        'OpenSSL Extension' => extension_loaded('openssl'),
        'JSON Extension' => extension_loaded('json'),
    ];
    
    $writable = [
        'uploads/ yozish huquqi' => is_writable(__DIR__ . '/uploads'),
        'data/ yozish huquqi' => is_writable(__DIR__ . '/data'),
    ];
    
    return ['requirements' => $requirements, 'writable' => $writable];
}

// 2-qadam: DB ulanishni test qilish
function testDBConnection($host, $name, $user, $pass) {
    try {
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // DB yaratish
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");
        
        return ['success' => true, 'pdo' => $pdo];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// 3-qadam: Schema import qilish
function importSchema($pdo, $sqlFile) {
    $sql = file_get_contents($sqlFile);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
}

// 4-qadam: Config faylini yaratish
function createConfigFile($config) {
    $content = "<?php\n";
    $content .= "// Ma'lumotlar bazasi sozlamalari\n";
    $content .= "define('DB_HOST', '" . addslashes($config['db_host']) . "');\n";
    $content .= "define('DB_NAME', '" . addslashes($config['db_name']) . "');\n";
    $content .= "define('DB_USER', '" . addslashes($config['db_user']) . "');\n";
    $content .= "define('DB_PASS', '" . addslashes($config['db_pass']) . "');\n";
    $content .= "define('DB_CHARSET', 'utf8mb4');\n\n";
    $content .= "// Umumiy sozlamalar\n";
    $content .= "define('SITE_ROOT', '" . addslashes($config['site_root']) . "');\n";
    $content .= "define('UPLOAD_DIR', SITE_ROOT . '/uploads');\n";
    $content .= "define('DATA_DIR', SITE_ROOT . '/data');\n";
    $content .= "define('INCLUDES_DIR', SITE_ROOT . '/includes');\n\n";
    $content .= "// Xavfsizlik\n";
    $content .= "define('HASH_COST', 12);\n";
    $content .= "define('SESSION_LIFETIME', 3600);\n";
    
    file_put_contents(__DIR__ . '/includes/config.php', $content);
}

// HTML Header
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinikani O'rnatish</title>
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
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #1d1d1f;
            font-size: 32px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #86868b;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        
        .progress::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            transform: translateY(-50%);
            z-index: 1;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #86868b;
            z-index: 2;
            position: relative;
        }
        
        .step.active {
            background: #0071e3;
            color: white;
        }
        
        .step.completed {
            background: #34c759;
            color: white;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #1d1d1f;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"] {
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
        
        .btn:disabled {
            background: #86868b;
            cursor: not-allowed;
            transform: none;
        }
        
        .status-list {
            list-style: none;
        }
        
        .status-list li {
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-list .ok {
            color: #34c759;
        }
        
        .status-list .error {
            color: #ff3b30;
        }
        
        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
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
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏥 Klinikani O'rnatish</h1>
        <p class="subtitle">Zamonaviy tibbiyot klinikasi veb-tizimini sozlash</p>
        
        <!-- Progress indicator -->
        <div class="progress">
            <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">1</div>
            <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">2</div>
            <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">3</div>
            <div class="step <?= $step >= 4 ? 'active' : '' ?> <?= $step > 4 ? 'completed' : '' ?>">4</div>
            <div class="step <?= $step >= 5 ? 'active' : '' ?> <?= $step > 5 ? 'completed' : '' ?>">5</div>
            <div class="step <?= $step >= 6 ? 'active' : '' ?>">6</div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <ul>
                    <?php foreach ($success as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Step 1: Server Requirements -->
        <?php if ($step === 1): ?>
            <h2 style="margin-bottom: 20px;">Server Talablari</h2>
            <?php 
            $check = checkServerRequirements();
            $allOk = true;
            ?>
            <h3 style="margin: 20px 0 10px;">PHP Kengaytmalar</h3>
            <ul class="status-list">
                <?php foreach ($check['requirements'] as $name => $status): ?>
                    <li>
                        <span class="<?= $status ? 'ok' : 'error' ?>">
                            <?= $status ? '✓' : '✗' ?>
                        </span>
                        <?= htmlspecialchars($name) ?>
                    </li>
                    <?php if (!$status) $allOk = false; ?>
                <?php endforeach; ?>
            </ul>
            
            <h3 style="margin: 20px 0 10px;">Papkalar Huquqi</h3>
            <ul class="status-list">
                <?php foreach ($check['writable'] as $name => $status): ?>
                    <li>
                        <span class="<?= $status ? 'ok' : 'error' ?>">
                            <?= $status ? '✓' : '✗' ?>
                        </span>
                        <?= htmlspecialchars($name) ?>
                    </li>
                    <?php if (!$status) $allOk = false; ?>
                <?php endforeach; ?>
            </ul>
            
            <?php if ($allOk): ?>
                <form method="post" style="margin-top: 30px;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['install_token'] ?>">
                    <input type="hidden" name="action" value="next_step">
                    <button type="submit" class="btn">Davom etish →</button>
                </form>
            <?php else: ?>
                <p style="color: #ff3b30; margin-top: 20px;">Iltimos, barcha talablarni bajarib, qayta urinib ko'ring.</p>
            <?php endif; ?>
        
        <!-- Step 2: Database Configuration -->
        <?php elseif ($step === 2): ?>
            <h2 style="margin-bottom: 20px;">Ma'lumotlar Bazasi Sozlamalari</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['install_token'] ?>">
                <input type="hidden" name="action" value="setup_db">
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>MySQL Host</label>
                        <input type="text" name="db_host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label>Database Nomi</label>
                        <input type="text" name="db_name" value="clinic_db" required>
                    </div>
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>MySQL Foydalanuvchi</label>
                        <input type="text" name="db_user" required>
                    </div>
                    <div class="form-group">
                        <label>MySQL Parol</label>
                        <input type="password" name="db_pass" required>
                    </div>
                </div>
                
                <button type="submit" class="btn">Ulanish va Davom etish</button>
            </form>
            
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'setup_db'): ?>
                <?php
                $db_host = $_POST['db_host'] ?? 'localhost';
                $db_name = $_POST['db_name'] ?? '';
                $db_user = $_POST['db_user'] ?? '';
                $db_pass = $_POST['db_pass'] ?? '';
                
                $result = testDBConnection($db_host, $db_name, $db_user, $db_pass);
                
                if ($result['success']) {
                    $_SESSION['db_config'] = [
                        'db_host' => $db_host,
                        'db_name' => $db_name,
                        'db_user' => $db_user,
                        'db_pass' => $db_pass
                    ];
                    
                    // Schema import
                    try {
                        importSchema($result['pdo'], __DIR__ . '/sql/schema.sql');
                        $success[] = 'Ma\'lumotlar bazasi muvaffaqiyatli yaratildi!';
                        $success[] = 'SQL sxema import qilindi!';
                        
                        echo '<script>setTimeout(function(){ window.location.href = "?step=3"; }, 2000);</script>';
                    } catch (Exception $e) {
                        $errors[] = 'SQL import xatosi: ' . $e->getMessage();
                    }
                } else {
                    $errors[] = 'DB ulanish xatosi: ' . ($result['error'] ?? 'Noma\'lum xatolik');
                }
                ?>
            <?php endif; ?>
        
        <!-- Step 3: Super Admin Account -->
        <?php elseif ($step === 3): ?>
            <h2 style="margin-bottom: 20px;">Super Admin Hisob Yaratish</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['install_token'] ?>">
                <input type="hidden" name="action" value="create_admin">
                
                <div class="form-group">
                    <label>Ism Familiya</label>
                    <input type="text" name="admin_name" required placeholder="Masalan: Dr. John Doe">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="admin_email" required placeholder="admin@clinic.com">
                </div>
                
                <div class="form-group">
                    <label>Parol</label>
                    <input type="password" name="admin_password" required minlength="8" placeholder="Kamida 8 belgi">
                </div>
                
                <div class="form-group">
                    <label>Parolni Tasdiqlash</label>
                    <input type="password" name="admin_password_confirm" required>
                </div>
                
                <button type="submit" class="btn">Admin Yaratish</button>
            </form>
            
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_admin'): ?>
                <?php
                $admin_name = trim($_POST['admin_name'] ?? '');
                $admin_email = trim($_POST['admin_email'] ?? '');
                $admin_password = $_POST['admin_password'] ?? '';
                $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';
                
                if ($admin_password !== $admin_password_confirm) {
                    $errors[] = 'Parollar mos kelmadi!';
                } elseif (strlen($admin_password) < 8) {
                    $errors[] = 'Parol kamida 8 belgidan iborat bo\'lishi kerak!';
                } else {
                    $db_config = $_SESSION['db_config'];
                    $result = testDBConnection($db_config['db_host'], $db_config['db_name'], $db_config['db_user'], $db_config['db_pass']);
                    
                    if ($result['success']) {
                        $pdo = $result['pdo'];
                        
                        // Admin yaratish
                        $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT, ['cost' => 12]);
                        
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'superadmin', NOW())");
                        $stmt->execute([$admin_name, $admin_email, $hashed_password]);
                        
                        $success[] = 'Super admin muvaffaqiyatli yaratildi!';
                        echo '<script>setTimeout(function(){ window.location.href = "?step=4"; }, 2000);</script>';
                    }
                }
                ?>
            <?php endif; ?>
        
        <!-- Step 4: Clinic Information -->
        <?php elseif ($step === 4): ?>
            <h2 style="margin-bottom: 20px;">Klinika Ma'lumotlari</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['install_token'] ?>">
                <input type="hidden" name="action" value="save_clinic_info">
                
                <div class="form-group">
                    <label>Klinika Nomi</label>
                    <input type="text" name="clinic_name" required placeholder="Masalan: Shifo Medical Center">
                </div>
                
                <div class="form-group">
                    <label>Telefon Raqam</label>
                    <input type="text" name="clinic_phone" required placeholder="+998 90 123 45 67">
                </div>
                
                <div class="form-group">
                    <label>Manzil</label>
                    <input type="text" name="clinic_address" required placeholder="Toshkent sh., ...">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="clinic_email" required placeholder="info@clinic.com">
                </div>
                
                <button type="submit" class="btn">Saqlash va Davom etish</button>
            </form>
            
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_clinic_info'): ?>
                <?php
                $db_config = $_SESSION['db_config'];
                $result = testDBConnection($db_config['db_host'], $db_config['db_name'], $db_config['db_user'], $db_config['db_pass']);
                
                if ($result['success']) {
                    $pdo = $result['pdo'];
                    
                    // settings.json yaratish
                    $settings = [
                        'site_name' => $_POST['clinic_name'] ?? 'Klinika',
                        'site_description' => 'Zamonaviy tibbiyot klinikasi',
                        'site_keywords' => 'klinika, shifokor, tibbiyot, salomatlik',
                        'phone' => $_POST['clinic_phone'] ?? '',
                        'email' => $_POST['clinic_email'] ?? '',
                        'address' => $_POST['clinic_address'] ?? '',
                        'opened_year' => date('Y'),
                        'smtp_host' => '',
                        'smtp_port' => 587,
                        'smtp_username' => '',
                        'smtp_password' => '',
                        'telegram_bot_token' => '',
                        'payment_details' => []
                    ];
                    
                    file_put_contents(__DIR__ . '/data/settings.json', json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    // contacts.json yaratish
                    $contacts = [
                        'phones' => [$_POST['clinic_phone'] ?? ''],
                        'email' => $_POST['clinic_email'] ?? '',
                        'address' => $_POST['clinic_address'] ?? '',
                        'work_hours' => [
                            'monday_friday' => '08:00-18:00',
                            'saturday' => '09:00-14:00',
                            'sunday' => 'Dam olish'
                        ],
                        'social_media' => [
                            'telegram' => '',
                            'instagram' => '',
                            'facebook' => '',
                            'youtube' => ''
                        ]
                    ];
                    
                    file_put_contents(__DIR__ . '/data/contacts.json', json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    // ads.json yaratish
                    file_put_contents(__DIR__ . '/data/ads.json', json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    $success[] = 'Klinika ma\'lumotlari saqlandi!';
                    echo '<script>setTimeout(function(){ window.location.href = "?step=5"; }, 2000);</script>';
                }
                ?>
            <?php endif; ?>
        
        <!-- Step 5: Telegram Bot -->
        <?php elseif ($step === 5): ?>
            <h2 style="margin-bottom: 20px;">Telegram Bot Sozlamalari</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['install_token'] ?>">
                <input type="hidden" name="action" value="save_telegram">
                
                <div class="form-group">
                    <label>Bot Token</label>
                    <input type="text" name="telegram_token" placeholder="@BotFather dan olingan token">
                    <small style="color: #86868b;">Ixtiyoriy. Keyinchalik ham sozlashingiz mumkin.</small>
                </div>
                
                <button type="submit" class="btn">Saqlash va Davom etish</button>
            </form>
            
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_telegram'): ?>
                <?php
                $db_config = $_SESSION['db_config'];
                $result = testDBConnection($db_config['db_host'], $db_config['db_name'], $db_config['db_user'], $db_config['db_pass']);
                
                if ($result['success']) {
                    $pdo = $result['pdo'];
                    
                    $settings = json_decode(file_get_contents(__DIR__ . '/data/settings.json'), true);
                    $settings['telegram_bot_token'] = $_POST['telegram_token'] ?? '';
                    file_put_contents(__DIR__ . '/data/settings.json', json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    $success[] = 'Telegram bot sozlamalari saqlandi!';
                    echo '<script>setTimeout(function(){ window.location.href = "?step=6"; }, 2000);</script>';
                }
                ?>
            <?php endif; ?>
        
        <!-- Step 6: Completion -->
        <?php elseif ($step === 6): ?>
            <div style="text-align: center; padding: 40px 0;">
                <div style="font-size: 80px; margin-bottom: 20px;">🎉</div>
                <h2 style="color: #34c759; margin-bottom: 20px;">O'rnatish Muvaffaqiyatli Yakunlandi!</h2>
                <p style="color: #86868b; margin-bottom: 30px;">
                    Klinika veb-tizimi ishga tayyor.<br>
                    Admin panelga kirish uchun quyidagi ma'lumotlardan foydalaning:
                </p>
                
                <div style="background: rgba(0, 113, 227, 0.1); padding: 20px; border-radius: 12px; margin-bottom: 30px;">
                    <p><strong>Admin Panel:</strong> <a href="/admin/" style="color: #0071e3;">/admin/</a></p>
                    <p><strong>User Panel:</strong> <a href="/user/" style="color: #0071e3;">/user/</a></p>
                </div>
                
                <div style="background: rgba(255, 59, 48, 0.1); padding: 20px; border-radius: 12px; margin-bottom: 30px;">
                    <p style="color: #ff3b30;"><strong>⚠️ Muhim!</strong></p>
                    <p style="color: #86868b;">Xavfsizlik uchun install.php faylini o'chiring yoki nomini o'zgartiring!</p>
                </div>
                
                <a href="/admin/" class="btn" style="display: inline-block; text-decoration: none; width: auto;">Admin Panelga Kirish</a>
            </div>
            
            <?php
            // Install faylini bloklash
            if (file_exists(__DIR__ . '/install.php')) {
                rename(__DIR__ . '/install.php', __DIR__ . '/install.php.bak');
            }
            ?>
        
        <?php endif; ?>
    </div>
</body>
</html>
