<?php
/**
 * Telegram Web App - Mening Navbatlarim
 */

require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

// Faqat ro'yxatdan o'tgan foydalanuvchilar kirishi mumkin
checkUserAuth();

$user_id = $_SESSION['user_id'];

// Foydalanuvchi ma'lumotlari
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: index.php');
    exit;
}

// Sozlamalarni yuklash
$settings = getSettings();

// Navbatlarni olish
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'active';

if ($filter === 'active') {
    $status_condition = "AND a.status IN ('pending', 'confirmed')";
} else {
    $status_condition = "AND a.status IN ('completed', 'cancelled')";
}

$sql = "
    SELECT a.*, 
           d.first_name as doctor_first_name, 
           d.last_name as doctor_last_name,
           d.patronymic as doctor_patronymic,
           s.name as service_name,
           s.price as service_price
    FROM appointments a
    LEFT JOIN doctors d ON a.doctor_id = d.id
    LEFT JOIN services s ON a.service_id = s.id
    WHERE a.user_id = ? $status_condition
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mening Navbatlarim - <?= htmlspecialchars($settings['site_name'] ?? 'Tibbiy Klinika') ?></title>
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

        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            background: var(--card-bg);
            padding: 6px;
            border-radius: 14px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .tab {
            flex: 1;
            padding: 10px 16px;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .tab.active {
            background: var(--primary);
            color: white;
        }

        .appointments-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .appointment-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
            padding: 16px;
            box-shadow: var(--shadow);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .doctor-info {
            flex: 1;
        }

        .doctor-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .service-name {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 149, 0, 0.15);
            color: var(--warning);
        }

        .status-confirmed {
            background: rgba(52, 199, 89, 0.15);
            color: var(--success);
        }

        .status-completed {
            background: rgba(0, 113, 227, 0.15);
            color: var(--primary);
        }

        .status-cancelled {
            background: rgba(255, 59, 48, 0.15);
            color: var(--danger);
        }

        .appointment-details {
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

        .appointment-actions {
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

        .btn-danger {
            background: rgba(255, 59, 48, 0.15);
            color: var(--danger);
        }

        .btn-danger:hover {
            background: rgba(255, 59, 48, 0.25);
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
            margin-bottom: 24px;
        }

        .bottom-spacer {
            height: 32px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="index.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="back-btn">←</a>
            <h1 class="page-title">Mening Navbatlarim</h1>
            <div style="width: 40px;"></div>
        </div>

        <div class="tabs">
            <a href="?filter=active<?= isset($_GET['tgWebAppData']) ? '&tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="tab <?= $filter === 'active' ? 'active' : '' ?>">Faol</a>
            <a href="?filter=past<?= isset($_GET['tgWebAppData']) ? '&tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="tab <?= $filter === 'past' ? 'active' : '' ?>">O'tgan</a>
        </div>

        <?php if (empty($appointments)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <div class="empty-title">Navbatlar yo'q</div>
            <div class="empty-desc">
                <?= $filter === 'active' ? 'Hozircha faol navbatlaringiz yo\'q' : 'O\'tgan navbatlar tarixi bo\'sh' ?>
            </div>
            <?php if ($filter === 'active'): ?>
            <a href="navbat-olish.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="btn btn-primary">Navbatga yozilish</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="appointments-list">
            <?php foreach ($appointments as $apt): ?>
            <div class="appointment-card">
                <div class="appointment-header">
                    <div class="doctor-info">
                        <div class="doctor-name">
                            <?= htmlspecialchars($apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']) ?>
                        </div>
                        <div class="service-name">
                            <?= htmlspecialchars($apt['service_name'] ?? 'Qabul') ?>
                        </div>
                    </div>
                    <span class="status-badge status-<?= $apt['status'] ?>">
                        <?php
                        $statusLabels = [
                            'pending' => 'Kutilmoqda',
                            'confirmed' => 'Tasdiqlandi',
                            'completed' => 'Bajarildi',
                            'cancelled' => 'Bekor qilindi'
                        ];
                        echo $statusLabels[$apt['status']] ?? $apt['status'];
                        ?>
                    </span>
                </div>

                <div class="appointment-details">
                    <div class="detail-item">
                        <div class="detail-label">📅 Sana</div>
                        <div class="detail-value"><?= date('d.m.Y', strtotime($apt['appointment_date'])) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">🕐 Vaqt</div>
                        <div class="detail-value"><?= date('H:i', strtotime($apt['appointment_time'])) ?></div>
                    </div>
                    <?php if (!empty($apt['doctor_room'])): ?>
                    <div class="detail-item">
                        <div class="detail-label">🚪 Xona</div>
                        <div class="detail-value"><?= htmlspecialchars($apt['doctor_room']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($apt['service_price'])): ?>
                    <div class="detail-item">
                        <div class="detail-label">💰 Narx</div>
                        <div class="detail-value"><?= number_format($apt['service_price'], 0, ',', ' ') ?> so'm</div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($apt['status'] === 'pending' || $apt['status'] === 'confirmed'): ?>
                <div class="appointment-actions">
                    <button class="btn btn-danger" onclick="cancelAppointment(<?= $apt['id'] ?>)">Bekor qilish</button>
                </div>
                <?php endif; ?>
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

        function cancelAppointment(appointmentId) {
            if (!confirm('Haqiqatan ham ushbu navbatni bekor qilmoqchimisiz?')) {
                return;
            }

            fetch('../api/cancel-appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    appointment_id: appointmentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Navbat bekor qilindi');
                    location.reload();
                } else {
                    alert('Xatolik: ' + data.message);
                }
            })
            .catch(error => {
                alert('Xatolik yuz berdi');
                console.error(error);
            });
        }
    </script>
</body>
</html>
