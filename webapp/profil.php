<?php
/**
 * Telegram Web App - Profil
 */

require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

checkUserAuth();

$user_id = $_SESSION['user_id'];
$success = null;
$error = null;

// Profil ma'lumotlarini yangilash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Xavfsizlik tokeni noto\'g\'ri';
    } else {
        $action = $_POST['action'];
        
        if ($action === 'update_profile') {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $birth_date = $_POST['birth_date'] ?? null;
            $gender = $_POST['gender'] ?? '';
            $blood_type = $_POST['blood_type'] ?? '';
            
            if (empty($first_name) || empty($last_name)) {
                $error = 'Ism va familiya majburiy';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, phone = ?, birth_date = ?, gender = ?, blood_type = ?
                    WHERE id = ?
                ");
                $stmt->execute([$first_name, $last_name, $phone, $birth_date, $gender, $blood_type, $user_id]);
                $success = 'Profil muvaffaqiyatli yangilandi';
                
                // Sessiyani yangilash
                $_SESSION['user_first_name'] = $first_name;
            }
        }
    }
}

// Foydalanuvchi ma'lumotlari
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: index.php');
    exit;
}

$settings = getSettings();

// CSRF token generatsiya
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profil - <?= htmlspecialchars($settings['site_name'] ?? 'Tibbiy Klinika') ?></title>
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

        .profile-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--shadow);
            text-align: center;
            margin-bottom: 20px;
        }

        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            margin: 0 auto 16px;
            box-shadow: var(--shadow);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .profile-email {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .info-section {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .form-value {
            font-size: 15px;
            font-weight: 500;
            padding: 12px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            font-size: 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
        }

        select.form-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2386868b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }

        .btn {
            width: 100%;
            padding: 14px;
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

        .edit-form {
            display: none;
        }

        .edit-form.active {
            display: block;
        }

        .view-mode {
            display: block;
        }

        .view-mode.hidden {
            display: none;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-success {
            background: rgba(52, 199, 89, 0.15);
            color: var(--success);
            border: 1px solid rgba(52, 199, 89, 0.3);
        }

        .alert-error {
            background: rgba(255, 59, 48, 0.15);
            color: var(--danger);
            border: 1px solid rgba(255, 59, 48, 0.3);
        }

        .bottom-spacer {
            height: 32px;
        }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="index.php<?= isset($_GET['tgWebAppData']) ? '?tgWebAppData=' . urlencode($_GET['tgWebAppData']) : '' ?>" class="back-btn">←</a>
            <h1 class="page-title">Profil</h1>
            <div style="width: 40px;"></div>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="avatar">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="../<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
                <?php else: ?>
                    <?= mb_strtoupper(mb_substr($user['first_name'], 0, 1)) ?><?= mb_strtoupper(mb_substr($user['last_name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="profile-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
            <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
        </div>

        <div class="info-section">
            <div class="section-title">Shaxsiy ma'lumotlar</div>
            
            <form method="POST" class="edit-form" id="editForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label class="form-label">Ism</label>
                    <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Familiya</label>
                    <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Telefon</label>
                    <input type="tel" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tug'ilgan sana</label>
                    <input type="date" name="birth_date" class="form-input" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Jinsi</label>
                    <select name="gender" class="form-input">
                        <option value="">Tanlanmagan</option>
                        <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Erkak</option>
                        <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Ayol</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Qon guruhi</label>
                    <select name="blood_type" class="form-input">
                        <option value="">Noma'lum</option>
                        <option value="A+" <?= $user['blood_type'] === 'A+' ? 'selected' : '' ?>>A+</option>
                        <option value="A-" <?= $user['blood_type'] === 'A-' ? 'selected' : '' ?>>A-</option>
                        <option value="B+" <?= $user['blood_type'] === 'B+' ? 'selected' : '' ?>>B+</option>
                        <option value="B-" <?= $user['blood_type'] === 'B-' ? 'selected' : '' ?>>B-</option>
                        <option value="AB+" <?= $user['blood_type'] === 'AB+' ? 'selected' : '' ?>>AB+</option>
                        <option value="AB-" <?= $user['blood_type'] === 'AB-' ? 'selected' : '' ?>>AB-</option>
                        <option value="O+" <?= $user['blood_type'] === 'O+' ? 'selected' : '' ?>>O+</option>
                        <option value="O-" <?= $user['blood_type'] === 'O-' ? 'selected' : '' ?>>O-</option>
                    </select>
                </div>
                
                <div class="actions">
                    <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary">Saqlash</button>
                </div>
            </form>
            
            <div class="view-mode" id="viewMode">
                <div class="form-group">
                    <div class="form-label">Ism</div>
                    <div class="form-value"><?= htmlspecialchars($user['first_name']) ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Familiya</div>
                    <div class="form-value"><?= htmlspecialchars($user['last_name']) ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Telefon</div>
                    <div class="form-value"><?= htmlspecialchars($user['phone'] ?? 'Kiritilmagan') ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Tug'ilgan sana</div>
                    <div class="form-value"><?= !empty($user['birth_date']) ? date('d.m.Y', strtotime($user['birth_date'])) : 'Kiritilmagan' ?></div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Jinsi</div>
                    <div class="form-value">
                        <?php
                        $genderLabels = ['male' => 'Erkak', 'female' => 'Ayol'];
                        echo $genderLabels[$user['gender']] ?? 'Kiritilmagan';
                        ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Qon guruhi</div>
                    <div class="form-value"><?= $user['blood_type'] ?? 'Noma\'lum' ?></div>
                </div>
                
                <button class="btn btn-primary" onclick="enableEdit()" style="margin-top: 16px;">Tahrirlash</button>
            </div>
        </div>

        <div class="bottom-spacer"></div>
    </div>

    <script>
        if (window.Telegram && window.Telegram.WebApp) {
            const tg = window.Telegram.WebApp;
            tg.ready();
            tg.expand();
        }

        function enableEdit() {
            document.getElementById('viewMode').classList.add('hidden');
            document.getElementById('editForm').classList.add('active');
        }

        function cancelEdit() {
            document.getElementById('viewMode').classList.remove('hidden');
            document.getElementById('editForm').classList.remove('active');
        }

        // Agar xabar bo'lsa, 3 soniyadan keyin yopish
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => alert.style.display = 'none');
        }, 5000);
    </script>
</body>
</html>
