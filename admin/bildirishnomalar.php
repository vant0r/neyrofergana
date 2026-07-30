<?php
/**
 * admin/bildirishnomalar.php - Ommaviy bildirishnomalar yuborish
 * Email va Telegram orqali xabar tarqatish
 */

require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

// Faqat adminlar kirishi mumkin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../kirish-royxatdan-otish.php');
    exit;
}

$success_message = '';
$error_message = '';

// Sozlamalarni olish
$settings = get_settings();

// Xabar yuborish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_message = "Xavfsizlik tokeni noto'g'ri";
    } else {
        $notification_type = $_POST['notification_type'] ?? 'both'; // email, telegram, both
        $recipient_filter = $_POST['recipient_filter'] ?? 'all'; // all, active, selected
        $selected_users = $_POST['selected_users'] ?? [];
        $subject = trim($_POST['subject'] ?? '');
        $message_text = trim($_POST['message'] ?? '');
        
        if (empty($message_text)) {
            $error_message = "Xabar matni bo'sh bo'lishi mumkin emas";
        } elseif ($notification_type === 'email' && empty($subject)) {
            $error_message = "Email mavzusi bo'sh bo'lishi mumkin emas";
        } else {
            try {
                // Qabul qiluvchilarni tanlash
                if ($recipient_filter === 'selected' && !empty($selected_users)) {
                    $placeholders = implode(',', array_fill(0, count($selected_users), '?'));
                    $stmt = $pdo->prepare("SELECT id, email, telegram_id, full_name FROM users WHERE id IN ($placeholders) AND is_active = 1");
                    $stmt->execute($selected_users);
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $sql = "SELECT id, email, telegram_id, full_name FROM users WHERE is_active = 1";
                    if ($recipient_filter === 'active') {
                        $sql .= " AND last_login_at > DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    }
                    $stmt = $pdo->query($sql);
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                
                $sent_count = 0;
                $failed_count = 0;
                
                // Har bir foydalanuvchiga yuborish
                foreach ($users as $user) {
                    $success = true;
                    
                    // Email yuborish
                    if (in_array($notification_type, ['email', 'both']) && !empty($user['email'])) {
                        require_once '../includes/mailer.php';
                        if (!send_email($user['email'], $subject, $message_text, $user['full_name'])) {
                            $success = false;
                        }
                    }
                    
                    // Telegram yuborish
                    if (in_array($notification_type, ['telegram', 'both']) && !empty($user['telegram_id']) && !empty($settings['telegram_bot_token'])) {
                        require_once '../includes/telegram.php';
                        if (!send_telegram_message($user['telegram_id'], $message_text)) {
                            $success = false;
                        }
                    }
                    
                    if ($success) {
                        $sent_count++;
                    } else {
                        $failed_count++;
                    }
                }
                
                // Tarixga saqlash
                $stmt = $pdo->prepare("
                    INSERT INTO notification_history (notification_type, recipient_count, subject, message, sent_at, created_by)
                    VALUES (?, ?, ?, ?, NOW(), ?)
                ");
                $stmt->execute([$notification_type, count($users), $subject, $message_text, $_SESSION['user_id']]);
                
                $success_message = "$sent_count ta xabar muvaffaqiyatli yuborildi. $failed_count ta xatolik.";
                
            } catch (PDOException $e) {
                $error_message = "Xabarlarni yuborishda xatolik: " . $e->getMessage();
            }
        }
    }
}

// Yuborilgan xabarlar tarixini olish
try {
    $stmt = $pdo->query("
        SELECT nh.*, u.full_name as admin_name
        FROM notification_history nh
        LEFT JOIN users u ON nh.created_by = u.id
        ORDER BY nh.sent_at DESC
        LIMIT 50
    ");
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $history = [];
}

// Barcha foydalanuvchilarni olish (tanlash uchun)
try {
    $stmt = $pdo->query("SELECT id, full_name, email FROM users WHERE is_active = 1 ORDER BY full_name");
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_users = [];
}

$page_title = "Bildirishnomalar - Admin Panel";
include '../includes/admin-header.php';
?>

<style>
.notification-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 1024px) {
    .notification-container {
        grid-template-columns: 1fr;
    }
}

.card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #1d1d1f;
    font-weight: 600;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    font-size: 15px;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #0071e3;
    box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.radio-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.radio-option input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.user-select-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.5);
}

.user-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.user-checkbox:hover {
    background: rgba(0, 113, 227, 0.1);
}

.user-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.btn-primary {
    width: 100%;
    padding: 14px 24px;
    background: #0071e3;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #0077ed;
    transform: translateY(-2px);
}

.btn-primary:active {
    transform: translateY(0);
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.history-table th,
.history-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.history-table th {
    background: rgba(0, 113, 227, 0.1);
    color: #1d1d1f;
    font-weight: 600;
    font-size: 14px;
}

.history-table tr:hover {
    background: rgba(0, 113, 227, 0.05);
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-email {
    background: rgba(0, 113, 227, 0.1);
    color: #0071e3;
}

.badge-telegram {
    background: rgba(0, 136, 204, 0.1);
    color: #0088cc;
}

.badge-both {
    background: rgba(52, 199, 89, 0.1);
    color: #34c759;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.5);
    padding: 16px;
    border-radius: 12px;
    text-align: center;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #0071e3;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 13px;
    color: #86868b;
}

.hidden {
    display: none;
}
</style>

<div class="page-header">
    <h1>📢 Bildirishnomalar</h1>
    <p>Ommaviy xabar yuborish va tarix</p>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<!-- Statistika -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-number"><?= count($all_users) ?></div>
        <div class="stat-label">Faol foydalanuvchilar</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= count($history) ?></div>
        <div class="stat-label">Yuborilgan xabarlar</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= !empty($settings['telegram_bot_token']) ? '✓' : '✗' ?></div>
        <div class="stat-label">Telegram ulangan</div>
    </div>
</div>

<div class="notification-container">
    <!-- Yangi xabar yuborish -->
    <div class="card">
        <h2 style="margin-top: 0; color: #1d1d1f;">📨 Yangi bildirishnoma</h2>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div class="form-group">
                <label>Xabar turi</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="notification_type" value="email" checked>
                        Faqat Email
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="notification_type" value="telegram">
                        Faqat Telegram
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="notification_type" value="both">
                        Ikkalasi
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Qabul qiluvchilar</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="recipient_filter" value="all" checked onchange="toggleUserSelect()">
                        Barchasi
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="recipient_filter" value="active" onchange="toggleUserSelect()">
                        Faol (30 kun)
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="recipient_filter" value="selected" onchange="toggleUserSelect()">
                        Tanlangan
                    </label>
                </div>
            </div>
            
            <div class="form-group hidden" id="userSelectGroup">
                <label>Foydalanuvchilarni tanlang</label>
                <div class="user-select-list">
                    <?php foreach ($all_users as $user): ?>
                        <label class="user-checkbox">
                            <input type="checkbox" name="selected_users[]" value="<?= $user['id'] ?>">
                            <span><?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['email']) ?>)</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group" id="subjectGroup">
                <label>Email mavzusi</label>
                <input type="text" name="subject" class="form-control" placeholder="Xabar mavzusi...">
            </div>
            
            <div class="form-group">
                <label>Xabar matni</label>
                <textarea name="message" class="form-control" placeholder="Xabar matnini kiriting..." required></textarea>
            </div>
            
            <button type="submit" name="send_notification" class="btn-primary">Yuborish</button>
        </form>
    </div>
    
    <!-- Tarix -->
    <div class="card">
        <h2 style="margin-top: 0; color: #1d1d1f;">📋 Yuborilgan xabarlar tarixi</h2>
        
        <?php if (empty($history)): ?>
            <p style="color: #86868b; text-align: center; padding: 40px;">Hozircha yuborilgan xabarlar yo'q</p>
        <?php else: ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Sana</th>
                        <th>Turi</th>
                        <th>Mavzu</th>
                        <th>Qabul qiluvchilar</th>
                        <th>Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $item): ?>
                        <tr>
                            <td><?= date('d.m.Y H:i', strtotime($item['sent_at'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $item['notification_type'] ?>">
                                    <?= $item['notification_type'] === 'email' ? 'Email' : ($item['notification_type'] === 'telegram' ? 'Telegram' : 'Email + Telegram') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(mb_substr($item['subject'] ?? '', 0, 30)) ?></td>
                            <td><?= $item['recipient_count'] ?></td>
                            <td><?= htmlspecialchars($item['admin_name'] ?? 'Noma\'lum') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleUserSelect() {
    const selectedRadio = document.querySelector('input[name="recipient_filter"]:checked');
    const userSelectGroup = document.getElementById('userSelectGroup');
    
    if (selectedRadio.value === 'selected') {
        userSelectGroup.classList.remove('hidden');
    } else {
        userSelectGroup.classList.add('hidden');
    }
}

// Email turini tanlaganda mavzu majburiy
const notificationTypeRadios = document.querySelectorAll('input[name="notification_type"]');
const subjectGroup = document.getElementById('subjectGroup');
const subjectInput = document.querySelector('input[name="subject"]');

notificationTypeRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'telegram') {
            subjectGroup.classList.add('hidden');
            subjectInput.removeAttribute('required');
        } else {
            subjectGroup.classList.remove('hidden');
            subjectInput.setAttribute('required', 'required');
        }
    });
});

// Sahifa yuklanganda holatni tekshirish
toggleUserSelect();
</script>

<?php include '../includes/admin-footer.php'; ?>
