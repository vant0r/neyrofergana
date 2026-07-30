<?php
/**
 * Telegram Web App - Test Natijalari
 */

require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

checkUserAuth();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: index.php');
    exit;
}

$settings = getSettings();

// Test natijalarini olish
$sql = "
    SELECT t.*, 
           d.first_name as doctor_first_name, 
           d.last_name as doctor_last_name
    FROM test_results t
    LEFT JOIN doctors d ON t.doctor_id = d.id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
    LIMIT 50
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Test Natijalari - <?= htmlspecialchars($settings['site_name'] ?? 'Tibbiy Klinika') ?></title>
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
            --success: #34c759;
            --warning: #ff9500;
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

        .tests-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .test-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
            padding: 16px;
            box-shadow: var(--shadow);
        }

        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .test-name {
            font-size: 16px;
            font-weight: 600;
            flex: 1;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            flex-shrink: 0;
            margin-left: 8px;
        }

        .status-pending {
            background: rgba(255, 149, 0, 0.15);
            color: var(--warning);
        }

        .status-ready {
            background: rgba(52, 199, 89, 0.15);
            color: var(--success);
        }

        .test-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 500;
        }

        .test-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .btn {
            flex: 1;
            padding: 12px 16px;
            border-radius: 12px;
            border: none;
            font-size: 14px;
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

        .btn-disabled {
            background: rgba(134, 134, 139, 0.15);
            color: var(--text-secondary);
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 48px 16px;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-desc {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .bottom-spacer {
            height: 32px;
        }

        .file-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="index.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="back-btn">←</a>
            <h1 class="page-title">Test Natijalari</h1>
            <div style="width: 40px;"></div>
        </div>

        <?php if (empty($tests)): ?>
        <div class="empty-state">
            <div class="empty-icon">🧪</div>
            <div class="empty-title">Test natijalari yo'q</div>
            <div class="empty-desc">Sizda hali hech qanday test natijalari mavjud emas</div>
        </div>
        <?php else: ?>
        <div class="tests-list">
            <?php foreach ($tests as $test): ?>
            <div class="test-card">
                <div class="test-header">
                    <div class="test-name"><?= htmlspecialchars($test['test_name']) ?></div>
                    <span class="status-badge status-<?= $test['status'] ?>">
                        <?= $test['status'] === 'pending' ? 'Jarayonda' : 'Tayyor' ?>
                    </span>
                </div>

                <div class="test-details">
                    <div class="detail-item">
                        <div class="detail-label">📅 Topshirilgan</div>
                        <div class="detail-value"><?= date('d.m.Y', strtotime($test['created_at'])) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">👨‍⚕️ Shifokor</div>
                        <div class="detail-value">
                            <?= htmlspecialchars($test['doctor_first_name'] . ' ' . $test['doctor_last_name'] ?? 'N/A') ?>
                        </div>
                    </div>
                </div>

                <div class="test-actions">
                    <?php if ($test['status'] === 'ready' && !empty($test['file_path'])): ?>
                        <?php
                        $fileExt = strtolower(pathinfo($test['file_path'], PATHINFO_EXTENSION));
                        $isPdf = $fileExt === 'pdf';
                        ?>
                        <?php if ($isPdf): ?>
                        <a href="../<?= htmlspecialchars($test['file_path']) ?>" target="_blank" class="btn btn-primary">
                            📄 Ko'rish
                        </a>
                        <a href="../<?= htmlspecialchars($test['file_path']) ?>" download class="btn btn-secondary">
                            ⬇️ Yuklab olish
                        </a>
                        <?php else: ?>
                        <a href="../<?= htmlspecialchars($test['file_path']) ?>" target="_blank" class="btn btn-primary">
                            🖼️ Ko'rish
                        </a>
                        <a href="../<?= htmlspecialchars($test['file_path']) ?>" download class="btn btn-secondary">
                            ⬇️ Yuklab olish
                        </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-disabled" disabled>Natija tayyorlanmoqda</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

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
