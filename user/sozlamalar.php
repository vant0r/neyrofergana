<?php
/**
 * User Panel: Sozlamalar
 * Bildirishnoma va tizim sozlamalari
 */
require_once '../includes/init.php';
requireAuth();

$user_id = $_SESSION['user_id'];
$page_title = "Sozlamalar";
$success_message = '';
$error_message = '';

// Foydalanuvchi sozlamalarini olish
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Agar sozlamalar yo'q bo'lsa, yangi yaratish
    if (!$settings) {
        $stmt = $pdo->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        
        $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Settings fetch error: " . $e->getMessage());
    $error_message = "Sozlamalarni yuklashda xatolik yuz berdi.";
    $settings = [
        'email_notifications' => 1,
        'telegram_notifications' => 0,
        'sms_notifications' => 0
    ];
}

// Forma yuborilganda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Xavfsizlik xatosi. Iltimos, qayta urinib ko'ring.";
    } else {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $telegram_notifications = isset($_POST['telegram_notifications']) ? 1 : 0;
        $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
        
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                UPDATE user_settings 
                SET email_notifications = ?, telegram_notifications = ?, sms_notifications = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$email_notifications, $telegram_notifications, $sms_notifications, $user_id]);
            
            $success_message = "Sozlamalar muvaffaqiyatli saqlandi!";
            
            // Yangilangan ma'lumotlarni qayta yuklash
            $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Settings update error: " . $e->getMessage());
            $error_message = "Sozlamalarni saqlashda xatolik yuz berdi.";
        }
    }
}

ob_start();
?>
<div class="dashboard-container">
    <div class="page-header">
        <h1>⚙️ Sozlamalar</h1>
        <p>Bildirishnoma va tizim parametrlarini boshqaring</p>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <div class="settings-grid">
        <!-- Bildirishnoma sozlamalari -->
        <div class="settings-card glass-card">
            <h2>🔔 Bildirishnoma Sozlamalari</h2>
            <p class="card-description">Qaysi kanallar orqali bildirishnomalar olishni tanlang</p>
            
            <form method="POST" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>📧 Email bildirishnomalar</h3>
                        <p>Navbatlar, test natijalari va aksiyalar haqida email xabarlar oling</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_notifications" <?= $settings['email_notifications'] ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>✈️ Telegram bildirishnomalar</h3>
                        <p>Telegram bot orqali tezkor xabarlar oling</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="telegram_notifications" <?= $settings['telegram_notifications'] ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>📱 SMS bildirishnomalar</h3>
                        <p>Muhim eslatmalar uchun SMS xabarlar (pullik xizmat)</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="sms_notifications" <?= $settings['sms_notifications'] ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Saqlash</button>
                </div>
            </form>
        </div>
        
        <!-- Hisob xavfsizligi -->
        <div class="settings-card glass-card">
            <h2>🔐 Xavfsizlik</h2>
            <p class="card-description">Hisobingiz xavfsizligini ta'minlash</p>
            
            <div class="security-settings">
                <div class="security-item">
                    <h3>Parolni o'zgartirish</h3>
                    <p>Muntazam ravishda parolingizni o'zgartirib turing</p>
                    <a href="parol-ozgartirish.php" class="btn btn-secondary">🔑 Parolni o'zgartirish</a>
                </div>
                
                <div class="security-item">
                    <h3>Sessiya boshqaruvi</h3>
                    <p>Oxirgi kirish: <strong><?= date('d.m.Y H:i', strtotime($_SESSION['last_activity'] ?? time())) ?></strong></p>
                    <p>IP manzil: <strong><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A') ?></strong></p>
                </div>
                
                <div class="security-item">
                    <h3>Ikkita faktorli autentifikatsiya</h3>
                    <p>Tez orada qo'shiladi</p>
                    <button class="btn btn-secondary" disabled>Kelajakda</button>
                </div>
            </div>
        </div>
        
        <!-- Maxfiylik -->
        <div class="settings-card glass-card">
            <h2>🔒 Maxfiylik</h2>
            <p class="card-description">Shaxsiy ma'lumotlaringizni boshqaring</p>
            
            <div class="privacy-settings">
                <div class="privacy-item">
                    <h3>Profil ko'rinishi</h3>
                    <p>Faqat siz va adminlar profil ma'lumotlarini ko'rishi mumkin</p>
                </div>
                
                <div class="privacy-item">
                    <h3>Ma'lumotlarni eksport qilish</h3>
                    <p>Barcha shaxsiy ma'lumotlaringizni yuklab oling</p>
                    <button class="btn btn-secondary" onclick="exportData()">📥 Yuklab olish</button>
                </div>
                
                <div class="privacy-item">
                    <h3>Hisobni o'chirish</h3>
                    <p>Hisobingizni butunlay o'chirish (qaytarib bo'lmaydi)</p>
                    <button class="btn btn-danger" onclick="confirmDeleteAccount()">⚠️ Hisobni o'chirish</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: clamp(1.5rem, 4vw, 2rem);
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.page-header p {
    color: var(--text-secondary);
}

.alert {
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    backdrop-filter: blur(10px);
}

.alert-success {
    background: rgba(52, 199, 89, 0.1);
    border: 1px solid var(--success);
    color: var(--success);
}

.alert-error {
    background: rgba(255, 59, 48, 0.1);
    border: 1px solid var(--danger);
    color: var(--danger);
}

.settings-grid {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.settings-card {
    padding: 2rem;
}

.settings-card h2 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.card-description {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.setting-item:last-child {
    border-bottom: none;
}

.setting-info h3 {
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
    color: var(--text-primary);
}

.setting-info p {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: var(--primary);
}

input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.security-settings,
.privacy-settings {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.security-item,
.privacy-item {
    padding: 1.5rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.security-item:last-child,
.privacy-item:last-child {
    border-bottom: none;
}

.security-item h3,
.privacy-item h3 {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.security-item p,
.privacy-item p {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
}

.form-actions {
    padding-top: 1.5rem;
}

.btn-danger {
    background: var(--danger);
    color: white;
    border: none;
}

.btn-danger:hover {
    background: #ff453a;
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }
    
    .settings-card {
        padding: 1.5rem;
    }
    
    .setting-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>

<script>
function exportData() {
    if (confirm('Barcha shaxsiy ma\'lumotlaringizni JSON formatida yuklab olmoqchimisiz?')) {
        // Bu yerda API chaqiruvi bo'ladi
        alert('Ma\'lumotlarni yuklab olish tez orada qo\'shiladi.');
    }
}

function confirmDeleteAccount() {
    if (confirm('⚠️ DIQQAT! Hisobingizni butunlay o\'chirishni xohlaysizmi?\n\nBu amal qaytarib bo\'lmaydi! Barcha ma\'lumotlaringiz o\'chiriladi.')) {
        if (confirm('Haqiqatan ham ishonchingiz komilmi?')) {
            // Bu yerda API chaqiruvi bo'ladi
            alert('Hisobni o\'chirish funksiyasi tez orada qo\'shiladi.');
        }
    }
}
</script>

<?php
$content = ob_get_clean();
require_once '../includes/user-header.php';
echo $content;
require_once '../includes/user-footer.php';
?>
