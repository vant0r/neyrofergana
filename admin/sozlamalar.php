<?php
// admin/sozlamalar.php - Umumiy sozlamalar
require_once '../includes/init.php';
require_once '../includes/auth.php';

checkAdminAuth();

$success = '';
$error = '';

// JSON fayllarni o'qish
$settingsFile = '../data/settings.json';
$contactsFile = '../data/contacts.json';

$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];
$contacts = file_exists($contactsFile) ? json_decode(file_get_contents($contactsFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Xavfsizlik xatosi: CSRF token noto'g'ri";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'general') {
            $settings['site_name'] = cleanInput($_POST['site_name'] ?? '');
            $settings['site_description'] = cleanInput($_POST['site_description'] ?? '');
            $settings['site_keywords'] = cleanInput($_POST['site_keywords'] ?? '');
            $settings['opened_year'] = (int)($_POST['opened_year'] ?? date('Y'));
            
            if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $success = "Umumiy sozlamalar saqlandi";
            } else {
                $error = "Sozlamalarni saqlashda xatolik";
            }
        } elseif ($action === 'contacts') {
            $contacts['phones'] = array_filter(array_map('cleanInput', $_POST['phones'] ?? []));
            $contacts['email'] = cleanInput($_POST['email'] ?? '');
            $contacts['work_time'] = [
                'monday_friday' => cleanInput($_POST['work_time_monday_friday'] ?? ''),
                'saturday' => cleanInput($_POST['work_time_saturday'] ?? ''),
                'sunday' => cleanInput($_POST['work_time_sunday'] ?? '')
            ];
            $contacts['address'] = cleanInput($_POST['address'] ?? '');
            $contacts['landmark'] = cleanInput($_POST['landmark'] ?? '');
            $contacts['map_url'] = cleanInput($_POST['map_url'] ?? '');
            
            if (file_put_contents($contactsFile, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $success = "Aloqa ma'lumotlari saqlandi";
            } else {
                $error = "Aloqa ma'lumotlarini saqlashda xatolik";
            }
        } elseif ($action === 'social') {
            $settings['social'] = [
                'telegram' => cleanInput($_POST['telegram'] ?? ''),
                'instagram' => cleanInput($_POST['instagram'] ?? ''),
                'facebook' => cleanInput($_POST['facebook'] ?? ''),
                'youtube' => cleanInput($_POST['youtube'] ?? '')
            ];
            
            if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $success = "Ijtimoiy tarmoqlar saqlandi";
            } else {
                $error = "Ijtimoiy tarmoqlarni saqlashda xatolik";
            }
        } elseif ($action === 'logo') {
            $uploadDir = '../uploads/logo/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (!empty($_FILES['main_logo']['name'])) {
                $result = uploadFile($_FILES['main_logo'], $uploadDir, ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
                if ($result['success']) {
                    $settings['main_logo'] = $result['filename'];
                } else {
                    $error = "Asosiy logo: " . $result['error'];
                }
            }
            
            if (!empty($_FILES['white_logo']['name']) && empty($error)) {
                $result = uploadFile($_FILES['white_logo'], $uploadDir, ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
                if ($result['success']) {
                    $settings['white_logo'] = $result['filename'];
                } else {
                    $error = "Oq logo: " . $result['error'];
                }
            }
            
            if (!empty($_FILES['favicon']['name']) && empty($error)) {
                $faviconDir = '../uploads/favicon/';
                if (!is_dir($faviconDir)) mkdir($faviconDir, 0755, true);
                $result = uploadFile($_FILES['favicon'], $faviconDir, ['png', 'ico'], 512 * 1024);
                if ($result['success']) {
                    $settings['favicon'] = $result['filename'];
                } else {
                    $error = "Favicon: " . $result['error'];
                }
            }
            
            if (empty($error) && file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $success = "Logo va favicon saqlandi";
            }
        } elseif ($action === 'smtp') {
            $settings['smtp'] = [
                'host' => cleanInput($_POST['smtp_host'] ?? ''),
                'port' => (int)($_POST['smtp_port'] ?? 587),
                'username' => cleanInput($_POST['smtp_username'] ?? ''),
                'password' => cleanInput($_POST['smtp_password'] ?? $settings['smtp']['password'] ?? ''),
                'from_email' => cleanInput($_POST['smtp_from_email'] ?? ''),
                'from_name' => cleanInput($_POST['smtp_from_name'] ?? $settings['site_name'] ?? '')
            ];
            
            if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $success = "SMTP sozlamalari saqlandi";
            } else {
                $error = "SMTP sozlamalarini saqlashda xatolik";
            }
        } elseif ($action === 'telegram') {
            $settings['telegram'] = [
                'bot_token' => cleanInput($_POST['bot_token'] ?? ''),
                'webhook_url' => cleanInput($_POST['webhook_url'] ?? ''),
                'required_channel' => cleanInput($_POST['required_channel'] ?? '')
            ];
            
            if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $success = "Telegram bot sozlamalari saqlandi";
                
                // Webhook o'rnatish
                if (!empty($settings['telegram']['bot_token']) && !empty($settings['telegram']['webhook_url'])) {
                    $apiUrl = "https://api.telegram.org/bot" . $settings['telegram']['bot_token'] . "/setWebhook";
                    $postData = ['url' => $settings['telegram']['webhook_url']];
                    
                    $ch = curl_init($apiUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    
                    $resultData = json_decode($result, true);
                    if (($resultData['ok'] ?? false)) {
                        $success .= ". Webhook muvaffaqiyatli o'rnatildi";
                    }
                }
            } else {
                $error = "Telegram sozlamalarini saqlashda xatolik";
            }
        } elseif ($action === 'payment') {
            $settings['payment'] = [
                'bank_name' => cleanInput($_POST['bank_name'] ?? ''),
                'account_number' => cleanInput($_POST['account_number'] ?? ''),
                'inn' => cleanInput($_POST['inn'] ?? ''),
                'mfo' => cleanInput($_POST['mfo'] ?? '')
            ];
            
            if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $success = "To'lov rekvizitlari saqlandi";
            } else {
                $error = "To'lov rekvizitlarini saqlashda xatolik";
            }
        }
    }
}

include '../includes/admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>🛠 Sozlamalar</h1>
        <p>Klinika umumiy sozlamalarini boshqaring</p>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="tabs">
        <button class="tab-btn active" data-tab="general">Umumiy</button>
        <button class="tab-btn" data-tab="contacts">Aloqa</button>
        <button class="tab-btn" data-tab="social">Ijtimoiy tarmoqlar</button>
        <button class="tab-btn" data-tab="logo">Logo va Favicon</button>
        <button class="tab-btn" data-tab="smtp">Email (SMTP)</button>
        <button class="tab-btn" data-tab="telegram">Telegram Bot</button>
        <button class="tab-btn" data-tab="payment">To'lov rekvizitlari</button>
    </div>
    
    <div class="tab-content active" id="general">
        <form method="POST" class="glass-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="general">
            
            <div class="form-group">
                <label>Sayt nomi</label>
                <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'MediClinic'); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Sayt tavsifi (Meta Description)</label>
                <textarea name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Kalit so'zlar (Meta Keywords)</label>
                <input type="text" name="site_keywords" value="<?php echo htmlspecialchars($settings['site_keywords'] ?? ''); ?>" placeholder="klinika, shifokor, tibbiyot">
            </div>
            
            <div class="form-group">
                <label>Klinika ochilgan yil</label>
                <input type="number" name="opened_year" value="<?php echo htmlspecialchars($settings['opened_year'] ?? date('Y')); ?>" min="1900" max="<?php echo date('Y'); ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">Saqlash</button>
        </form>
    </div>
    
    <div class="tab-content" id="contacts">
        <form method="POST" class="glass-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="contacts">
            
            <div class="form-group">
                <label>Telefon raqamlar</label>
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <input type="tel" name="phones[]" value="<?php echo htmlspecialchars($contacts['phones'][$i] ?? ''); ?>" placeholder="+998 90 123 45 67" style="margin-bottom: 10px;">
                <?php endfor; ?>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($contacts['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Dushanba-Juma</label>
                    <input type="text" name="work_time_monday_friday" value="<?php echo htmlspecialchars($contacts['work_time']['monday_friday'] ?? '08:00-18:00'); ?>" placeholder="08:00-18:00">
                </div>
                <div class="form-group">
                    <label>Shanba</label>
                    <input type="text" name="work_time_saturday" value="<?php echo htmlspecialchars($contacts['work_time']['saturday'] ?? '09:00-15:00'); ?>" placeholder="09:00-15:00">
                </div>
                <div class="form-group">
                    <label>Yakshanba</label>
                    <input type="text" name="work_time_sunday" value="<?php echo htmlspecialchars($contacts['work_time']['sunday'] ?? 'Dam olish'); ?>" placeholder="Dam olish">
                </div>
            </div>
            
            <div class="form-group">
                <label>Manzil</label>
                <textarea name="address" rows="2"><?php echo htmlspecialchars($contacts['address'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Mo'ljal</label>
                <input type="text" name="landmark" value="<?php echo htmlspecialchars($contacts['landmark'] ?? ''); ?>" placeholder="Masalan: Metro bekati yonida">
            </div>
            
            <div class="form-group">
                <label>Xarita URL (Google Maps embed)</label>
                <input type="text" name="map_url" value="<?php echo htmlspecialchars($contacts['map_url'] ?? ''); ?>" placeholder="https://www.google.com/maps/embed?pb=...">
            </div>
            
            <button type="submit" class="btn btn-primary">Saqlash</button>
        </form>
    </div>
    
    <div class="tab-content" id="social">
        <form method="POST" class="glass-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="social">
            
            <div class="form-group">
                <label>Telegram</label>
                <input type="text" name="telegram" value="<?php echo htmlspecialchars($settings['social']['telegram'] ?? ''); ?>" placeholder="@username yoki https://t.me/username">
            </div>
            
            <div class="form-group">
                <label>Instagram</label>
                <input type="text" name="instagram" value="<?php echo htmlspecialchars($settings['social']['instagram'] ?? ''); ?>" placeholder="@username yoki https://instagram.com/username">
            </div>
            
            <div class="form-group">
                <label>Facebook</label>
                <input type="text" name="facebook" value="<?php echo htmlspecialchars($settings['social']['facebook'] ?? ''); ?>" placeholder="https://facebook.com/page">
            </div>
            
            <div class="form-group">
                <label>YouTube</label>
                <input type="text" name="youtube" value="<?php echo htmlspecialchars($settings['social']['youtube'] ?? ''); ?>" placeholder="https://youtube.com/channel">
            </div>
            
            <button type="submit" class="btn btn-primary">Saqlash</button>
        </form>
    </div>
    
    <div class="tab-content" id="logo">
        <form method="POST" enctype="multipart/form-data" class="glass-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="logo">
            
            <div class="form-group">
                <label>Asosiy Logotip</label>
                <?php if (!empty($settings['main_logo'])): ?>
                    <img src="../uploads/logo/<?php echo htmlspecialchars($settings['main_logo']); ?>" alt="Main Logo" style="max-height: 80px; margin-bottom: 10px;">
                <?php endif; ?>
                <input type="file" name="main_logo" accept="image/*">
                <small>PNG, JPG, WEBP (maks 2MB)</small>
            </div>
            
            <div class="form-group">
                <label>Oq fonli Logotip</label>
                <?php if (!empty($settings['white_logo'])): ?>
                    <img src="../uploads/logo/<?php echo htmlspecialchars($settings['white_logo']); ?>" alt="White Logo" style="max-height: 80px; margin-bottom: 10px; background: #f5f5f7;">
                <?php endif; ?>
                <input type="file" name="white_logo" accept="image/*">
                <small>Footer uchun (maks 2MB)</small>
            </div>
            
            <div class="form-group">
                <label>Favicon</label>
                <?php if (!empty($settings['favicon'])): ?>
                    <img src="../uploads/favicon/<?php echo htmlspecialchars($settings['favicon']); ?>" alt="Favicon" style="width: 32px; height: 32px; margin-bottom: 10px;">
                <?php endif; ?>
                <input type="file" name="favicon" accept="image/png,image/x-icon">
                <small>PNG yoki ICO (maks 512KB)</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Yuklash va Saqlash</button>
        </form>
    </div>
    
    <div class="tab-content" id="smtp">
        <form method="POST" class="glass-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="smtp">
            
            <div class="form-group">
                <label>SMTP Server</label>
                <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp']['host'] ?? ''); ?>" placeholder="smtp.gmail.com">
            </div>
            
            <div class="form-group">
                <label>SMTP Port</label>
                <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp']['port'] ?? 587); ?>" placeholder="587">
            </div>
            
            <div class="form-group">
                <label>SMTP Username</label>
                <input type="email" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp']['username'] ?? ''); ?>" placeholder="email@example.com">
            </div>
            
            <div class="form-group">
                <label>SMTP Password</label>
                <input type="password" name="smtp_password" placeholder="••••••••" value="<?php echo htmlspecialchars($settings['smtp']['password'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Yuboruvchi Email</label>
                <input type="email" name="smtp_from_email" value="<?php echo htmlspecialchars($settings['smtp']['from_email'] ?? ''); ?>" placeholder="noreply@clinic.com">
            </div>
            
            <div class="form-group">
                <label>Yuboruvchi Nomi</label>
                <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($settings['smtp']['from_name'] ?? ($settings['site_name'] ?? 'MediClinic')); ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">Saqlash</button>
            <button type="button" class="btn btn-secondary" onclick="alert('Test email yuborish funksiyasi tez orada qo\'shiladi')">Test Email Yuborish</button>
        </form>
    </div>
    
    <div class="tab-content" id="telegram">
        <form method="POST" class="glass-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="telegram">
            
            <div class="form-group">
                <label>Bot Token</label>
                <input type="text" name="bot_token" value="<?php echo htmlspecialchars($settings['telegram']['bot_token'] ?? ''); ?>" placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz">
                <small>@BotFather dan olingan token</small>
            </div>
            
            <div class="form-group">
                <label>Webhook URL</label>
                <input type="text" name="webhook_url" value="<?php echo htmlspecialchars($settings['telegram']['webhook_url'] ?? ''); ?>" placeholder="https://yourdomain.com/api/telegram-webhook.php">
                <small>To'liq HTTPS URL</small>
            </div>
            
            <div class="form-group">
                <label>Majburiy Kanal (@username)</label>
                <input type="text" name="required_channel" value="<?php echo htmlspecialchars($settings['telegram']['required_channel'] ?? ''); ?>" placeholder="@yourchannel">
                <small>Foydalanuvchilar obuna bo'lishi kerak</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Saqlash va Webhook O'rnatish</button>
        </form>
    </div>
    
    <div class="tab-content" id="payment">
        <form method="POST" class="glass-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="payment">
            
            <div class="form-group">
                <label>Bank Nomi</label>
                <input type="text" name="bank_name" value="<?php echo htmlspecialchars($settings['payment']['bank_name'] ?? ''); ?>" placeholder="Kapitalbank">
            </div>
            
            <div class="form-group">
                <label>Hisob Raqam</label>
                <input type="text" name="account_number" value="<?php echo htmlspecialchars($settings['payment']['account_number'] ?? ''); ?>" placeholder="20208000100000000000">
            </div>
            
            <div class="form-group">
                <label>INN</label>
                <input type="text" name="inn" value="<?php echo htmlspecialchars($settings['payment']['inn'] ?? ''); ?>" placeholder="123456789">
            </div>
            
            <div class="form-group">
                <label>MFO</label>
                <input type="text" name="mfo" value="<?php echo htmlspecialchars($settings['payment']['mfo'] ?? ''); ?>" placeholder="123">
            </div>
            
            <button type="submit" class="btn btn-primary">Saqlash</button>
        </form>
    </div>
</div>

<script>
// Tabs funksiyasi
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?>
