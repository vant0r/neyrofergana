<?php
/**
 * Telegram Web App - Bosh sahifa
 * Bemorlar uchun tezkor xizmatlar
 */

require_once '../includes/config.php';
require_once '../includes/init.php';

// Telegram Web App ma'lumotlarini tekshirish
$telegramUser = null;
if (isset($_GET['tgWebAppData'])) {
    $initData = $_GET['tgWebAppData'];
    // Telegram ma'lumotlarini parse qilish (soddalashtirilgan)
    parse_str($initData, $data);
    if (isset($data['user'])) {
        $telegramUser = json_decode($data['user'], true);
    }
}

// Agar foydalanuvchi tizimda ro'yxatdan o'tgan bo'lsa, sessiyani tekshirish
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;
if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Sozlamalarni yuklash
$settings = getSettings();
$contacts = getContacts();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($settings['site_name'] ?? 'Tibbiy Klinika') ?> - Web App</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-color: #f5f5f7;
            --card-bg: rgba(255, 255, 255, 0.7);
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --primary: #0071e3;
            --primary-hover: #0077ed;
            --success: #34c759;
            --danger: #ff3b30;
            --border-radius: 16px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 16px;
            line-height: 1.6;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            padding: 24px 0;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            object-fit: cover;
            margin-bottom: 12px;
            box-shadow: var(--shadow);
        }

        .welcome {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-top: 24px;
        }

        .action-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .action-card:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.9);
        }

        .action-card:active {
            transform: scale(0.98);
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .icon-blue { background: rgba(0, 113, 227, 0.15); }
        .icon-green { background: rgba(52, 199, 89, 0.15); }
        .icon-purple { background: rgba(175, 82, 222, 0.15); }
        .icon-orange { background: rgba(255, 149, 0, 0.15); }
        .icon-red { background: rgba(255, 59, 48, 0.15); }

        .action-info {
            flex: 1;
        }

        .action-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .action-desc {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .action-arrow {
            font-size: 20px;
            color: var(--text-secondary);
        }

        .info-block {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
            padding: 16px;
            margin-top: 24px;
            box-shadow: var(--shadow);
        }

        .info-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-secondary);
        }

        .info-content {
            font-size: 15px;
        }

        .info-content p {
            margin-bottom: 8px;
        }

        .info-content a {
            color: var(--primary);
            text-decoration: none;
        }

        .bottom-spacer {
            height: 32px;
        }

        @media (min-width: 481px) {
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <?php if (!empty($settings['logo'])): ?>
                <img src="<?= htmlspecialchars($settings['logo']) ?>" alt="Logo" class="logo">
            <?php else: ?>
                <div class="logo" style="background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: bold;">🏥</div>
            <?php endif; ?>
            
            <h1 class="welcome">
                <?= $user ? 'Assalomu alaykum, ' . htmlspecialchars($user['first_name']) : 'Assalomu alaykum!' ?>
            </h1>
            <p class="subtitle"><?= htmlspecialchars($settings['site_name'] ?? 'Tibbiy Klinika') ?></p>
        </div>

        <div class="quick-actions">
            <a href="navbat-olish.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="action-card">
                <div class="action-icon icon-blue">📅</div>
                <div class="action-info">
                    <div class="action-title">Navbatga yozilish</div>
                    <div class="action-desc">Shifokor qabuliga yoziling</div>
                </div>
                <div class="action-arrow">→</div>
            </a>

            <a href="navbatlar.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="action-card">
                <div class="action-icon icon-green">📋</div>
                <div class="action-info">
                    <div class="action-title">Mening navbatlarim</div>
                    <div class="action-desc">Barcha yozuvlaringizni ko'ring</div>
                </div>
                <div class="action-arrow">→</div>
            </a>

            <a href="testlar.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="action-card">
                <div class="action-icon icon-purple">🧪</div>
                <div class="action-info">
                    <div class="action-title">Test natijalari</div>
                    <div class="action-desc">Tahlil natijalarini yuklab oling</div>
                </div>
                <div class="action-arrow">→</div>
            </a>

            <a href="profil.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="action-card">
                <div class="action-icon icon-orange">👤</div>
                <div class="action-info">
                    <div class="action-title">Profil</div>
                    <div class="action-desc">Shaxsiy ma'lumotlaringiz</div>
                </div>
                <div class="action-arrow">→</div>
            </a>

            <a href="malumotlar.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="action-card">
                <div class="action-icon icon-red">📞</div>
                <div class="action-info">
                    <div class="action-title">Klinika ma'lumotlari</div>
                    <div class="action-desc">Aloqa va manzil</div>
                </div>
                <div class="action-arrow">→</div>
            </a>
        </div>

        <?php if (!empty($contacts)): ?>
        <div class="info-block">
            <div class="info-title">Tezkor aloqa</div>
            <div class="info-content">
                <?php if (!empty($contacts['phone'])): ?>
                    <p>📱 <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contacts['phone']) ?>"><?= htmlspecialchars($contacts['phone']) ?></a></p>
                <?php endif; ?>
                <?php if (!empty($contacts['work_hours'])): ?>
                    <p>🕐 <?= htmlspecialchars($contacts['work_hours']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="bottom-spacer"></div>
    </div>

    <script>
        // Telegram Web App ni ishga tushirish
        if (window.Telegram && window.Telegram.WebApp) {
            const tg = window.Telegram.WebApp;
            tg.ready();
            tg.expand();
            
            // Tema ranglarini moslashtirish
            document.documentElement.style.setProperty('--bg-color', tg.themeParams.bg_color || '#f5f5f7');
            document.documentElement.style.setProperty('--text-primary', tg.themeParams.text_color || '#1d1d1f');
        }

        // Asosiy sahifaga qaytishni oldini olish
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.pushState(null, null, location.href);
        };
    </script>
</body>
</html>
