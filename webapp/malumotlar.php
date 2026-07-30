<?php
/**
 * Telegram Web App - Klinika Ma'lumotlari
 */

require_once '../includes/config.php';
require_once '../includes/init.php';

$settings = getSettings();
$contacts = getContacts();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Klinika Ma'lumotlari - <?= htmlspecialchars($settings['site_name'] ?? 'Tibbiy Klinika') ?></title>
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 0 24px;
        }

        .back-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            text-decoration: none;
            color: var(--text-primary);
            box-shadow: var(--shadow);
        }

        .page-title {
            font-size: 20px;
            font-weight: 600;
        }

        .info-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 16px;
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .info-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(0, 113, 227, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .info-title {
            font-size: 18px;
            font-weight: 600;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-item:first-child {
            padding-top: 0;
        }

        .item-icon {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .item-content {
            flex: 1;
        }

        .item-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }

        .item-value {
            font-size: 15px;
            font-weight: 500;
        }

        .item-value a {
            color: var(--primary);
            text-decoration: none;
        }

        .map-container {
            width: 100%;
            height: 200px;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 12px;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .social-links {
            display: flex;
            gap: 12px;
            margin-top: 12px;
        }

        .social-link {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(0, 113, 227, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: rgba(0, 113, 227, 0.25);
            transform: translateY(-2px);
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
        }

        .btn {
            padding: 14px 20px;
            border-radius: 12px;
            border: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: rgba(0, 113, 227, 0.15);
            color: var(--primary);
        }

        .bottom-spacer {
            height: 32px;
        }

        .clinic-name {
            text-align: center;
            padding: 24px 0;
        }

        .clinic-name h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .clinic-name p {
            font-size: 14px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="index.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="back-btn">←</a>
            <h1 class="page-title">Klinika Ma'lumotlari</h1>
            <div style="width: 40px;"></div>
        </div>

        <div class="clinic-name">
            <?php if (!empty($settings['logo'])): ?>
                <img src="<?= htmlspecialchars($settings['logo']) ?>" alt="Logo" style="width: 80px; height: 80px; border-radius: 20px; object-fit: cover; margin-bottom: 12px; box-shadow: var(--shadow);">
            <?php endif; ?>
            <h1><?= htmlspecialchars($settings['site_name'] ?? 'Tibbiy Klinika') ?></h1>
            <p>Sizning sog'lig'ingiz - bizning baxtimiz</p>
        </div>

        <?php if (!empty($contacts)): ?>
        <div class="info-card">
            <div class="info-card-header">
                <div class="info-icon">📞</div>
                <div class="info-title">Aloqa ma'lumotlari</div>
            </div>

            <?php if (!empty($contacts['phone'])): ?>
            <div class="info-item">
                <div class="item-icon">📱</div>
                <div class="item-content">
                    <div class="item-label">Telefon raqam</div>
                    <div class="item-value">
                        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contacts['phone']) ?>"><?= htmlspecialchars($contacts['phone']) ?></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($contacts['email'])): ?>
            <div class="info-item">
                <div class="item-icon">✉️</div>
                <div class="item-content">
                    <div class="item-label">Email</div>
                    <div class="item-value">
                        <a href="mailto:<?= htmlspecialchars($contacts['email']) ?>"><?= htmlspecialchars($contacts['email']) ?></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($contacts['work_hours'])): ?>
            <div class="info-item">
                <div class="item-icon">🕐</div>
                <div class="item-content">
                    <div class="item-label">Ish vaqti</div>
                    <div class="item-value"><?= htmlspecialchars($contacts['work_hours']) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($contacts['address'])): ?>
        <div class="info-card">
            <div class="info-card-header">
                <div class="info-icon">📍</div>
                <div class="info-title">Manzil</div>
            </div>

            <div class="info-item">
                <div class="item-icon">🏢</div>
                <div class="item-content">
                    <div class="item-label">To'liq manzil</div>
                    <div class="item-value"><?= htmlspecialchars($contacts['address']) ?></div>
                </div>
            </div>

            <?php if (!empty($contacts['landmark'])): ?>
            <div class="info-item">
                <div class="item-icon">🎯</div>
                <div class="item-content">
                    <div class="item-label">Mo'ljal</div>
                    <div class="item-value"><?= htmlspecialchars($contacts['landmark']) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($contacts['map_url'])): ?>
            <div class="map-container">
                <iframe src="<?= htmlspecialchars($contacts['map_url']) ?>" allowfullscreen loading="lazy"></iframe>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php
        $hasSocial = !empty($contacts['telegram']) || !empty($contacts['instagram']) || 
                     !empty($contacts['facebook']) || !empty($contacts['youtube']);
        ?>
        <?php if ($hasSocial): ?>
        <div class="info-card">
            <div class="info-card-header">
                <div class="info-icon">🌐</div>
                <div class="info-title">Ijtimoiy tarmoqlar</div>
            </div>

            <div class="social-links">
                <?php if (!empty($contacts['telegram'])): ?>
                <a href="<?= htmlspecialchars($contacts['telegram']) ?>" target="_blank" class="social-link">✈️</a>
                <?php endif; ?>

                <?php if (!empty($contacts['instagram'])): ?>
                <a href="<?= htmlspecialchars($contacts['instagram']) ?>" target="_blank" class="social-link">📷</a>
                <?php endif; ?>

                <?php if (!empty($contacts['facebook'])): ?>
                <a href="<?= htmlspecialchars($contacts['facebook']) ?>" target="_blank" class="social-link">📘</a>
                <?php endif; ?>

                <?php if (!empty($contacts['youtube'])): ?>
                <a href="<?= htmlspecialchars($contacts['youtube']) ?>" target="_blank" class="social-link">📺</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="action-buttons">
            <?php if (!empty($contacts['phone'])): ?>
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contacts['phone']) ?>" class="btn btn-primary">📞 Qo'ng'iroq qilish</a>
            <?php endif; ?>
            
            <?php if (!empty($contacts['telegram'])): ?>
            <a href="<?= htmlspecialchars($contacts['telegram']) ?>" target="_blank" class="btn btn-secondary">✈️ Telegram</a>
            <?php else: ?>
            <a href="https://t.me/" target="_blank" class="btn btn-secondary">✈️ Telegram</a>
            <?php endif; ?>
        </div>

        <div class="bottom-spacer"></div>
    </div>

    <script>
        if (window.Telegram && window.Telegram.WebApp) {
            const tg = window.Telegram.WebApp;
            tg.ready();
            tg.expand();
        }
    </script>
</body>
</html>
