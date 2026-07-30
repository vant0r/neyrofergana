<?php
/**
 * admin/htaccess.php - .htaccess boshqaruvi
 * Faqat superadmin uchun
 */

require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

// Faqat superadmin kirishi mumkin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../kirish-royxatdan-otish.php');
    exit;
}

$success_message = '';
$error_message = '';

$htaccess_path = __DIR__ . '/.htaccess';
$htaccess_content = '';

// .htaccess faylini o'qish
if (file_exists($htaccess_path)) {
    $htaccess_content = file_get_contents($htaccess_path);
} else {
    // Default .htaccess content
    $htaccess_content = "# Admin Panel Xavfsizlik
Options -Indexes
<FilesMatch \"\.(php|json)$\">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# IP Cheklovlari (kerak bo'lsa oching)
# Order Deny,Allow
# Deny from all
# Allow from 127.0.0.1
# Allow from 192.168.1

# Xavfsizlik Headerlari
<IfModule mod_headers.c>
    Header set X-Content-Type-Options \"nosniff\"
    Header set X-Frame-Options \"SAMEORIGIN\"
    Header set X-XSS-Protection \"1; mode=block\"
    Header set Referrer-Policy \"strict-origin-when-cross-origin\"
</IfModule>

# HTTPS majburiy (kerak bo'lsa oching)
# <IfModule mod_rewrite.c>
#     RewriteEngine On
#     RewriteCond %{HTTPS} off
#     RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
# </IfModule>";
}

// .htaccess ni saqlash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_htaccess'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_message = "Xavfsizlik tokeni noto'g'ri";
    } else {
        $new_content = $_POST['htaccess_content'] ?? '';
        
        try {
            if (file_put_contents($htaccess_path, $new_content)) {
                $success_message = ".htaccess fayli muvaffaqiyatli saqlandi";
                $htaccess_content = $new_content;
            } else {
                $error_message = ".htaccess faylini saqlashda xatolik";
            }
        } catch (Exception $e) {
            $error_message = "Xatolik: " . $e->getMessage();
        }
    }
}

// IP qo'shish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ip'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_message = "Xavfsizlik tokeni noto'g'ri";
    } else {
        $ip_address = trim($_POST['ip_address'] ?? '');
        
        if (empty($ip_address) || !filter_var($ip_address, FILTER_VALIDATE_IP)) {
            $error_message = "Noto'g'ri IP manzil";
        } else {
            // IP qo'shish
            if (strpos($htaccess_content, "Allow from $ip_address") === false) {
                $new_line = "Allow from $ip_address\n";
                $htaccess_content = str_replace("# Allow from 192.168.1", "# Allow from 192.168.1\n" . $new_line, $htaccess_content);
                
                if (file_put_contents($htaccess_path, $htaccess_content)) {
                    $success_message = "IP manzil qo'shildi: $ip_address";
                } else {
                    $error_message = "IP qo'shishda xatolik";
                }
            } else {
                $error_message = "Bu IP allaqachon qo'shilgan";
            }
        }
    }
}

// Parol himoyasini yoqish/o'chirish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_password_protection'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_message = "Xavfsizlik tokeni noto'g'ri";
    } else {
        $enable = isset($_POST['enable_protection']);
        
        if ($enable) {
            // Parol himoyasini yoqish
            $auth_content = "# Parol himoyasi\nAuthType Basic\nAuthName \"Admin Panel\"\nAuthUserFile " . __DIR__ . "/.htpasswd\nRequire valid-user\n\n";
            
            if (strpos($htaccess_content, "AuthType Basic") === false) {
                $htaccess_content = $auth_content . $htaccess_content;
                
                // .htpasswd faylini yaratish
                $htpasswd_path = __DIR__ . '/.htpasswd';
                $admin_user = $_SESSION['user_email'] ?? 'admin';
                $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
                file_put_contents($htpasswd_path, "$admin_user:$password_hash\n");
                
                if (file_put_contents($htaccess_path, $htaccess_content)) {
                    $success_message = "Parol himoyasi yoqildi. Login: $admin_user, Parol: admin123";
                }
            } else {
                $error_message = "Parol himoyasi allaqachon yoqilgan";
            }
        } else {
            // Parol himoyasini o'chirish
            $htaccess_content = preg_replace('/# Parol himoyasi\s*\nAuthType Basic\s*\nAuthName "Admin Panel"\s*\nAuthUserFile.*?\nRequire valid-user\s*\n\s*\n/s', '', $htaccess_content);
            
            if (file_put_contents($htaccess_path, $htaccess_content)) {
                $success_message = "Parol himoyasi o'chirildi";
            }
        }
    }
}

$page_title = ".htaccess Boshqaruvi - Admin Panel";
include '../includes/admin-header.php';
?>

<style>
.config-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

@media (max-width: 1024px) {
    .config-container {
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

.editor-container {
    position: relative;
}

.code-editor {
    width: 100%;
    min-height: 500px;
    padding: 16px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    background: rgba(28, 28, 30, 0.95);
    color: #f5f5f7;
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace;
    font-size: 14px;
    line-height: 1.6;
    resize: vertical;
    transition: all 0.3s ease;
}

.code-editor:focus {
    outline: none;
    border-color: #0071e3;
    box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.2);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 24px;
    background: #0071e3;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 16px;
}

.btn-primary:hover {
    background: #0077ed;
    transform: translateY(-2px);
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: rgba(142, 142, 147, 0.2);
    color: #1d1d1f;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: rgba(142, 142, 147, 0.3);
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

.security-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 12px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
}

.security-option:hover {
    background: rgba(255, 255, 255, 0.7);
}

.security-option h4 {
    margin: 0 0 4px 0;
    color: #1d1d1f;
    font-size: 15px;
}

.security-option p {
    margin: 0;
    color: #86868b;
    font-size: 13px;
}

.toggle-switch {
    position: relative;
    width: 50px;
    height: 28px;
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
    background-color: #d1d1d6;
    transition: 0.3s;
    border-radius: 28px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #34c759;
}

input:checked + .toggle-slider:before {
    transform: translateX(22px);
}

.ip-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.ip-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 8px;
    margin-bottom: 8px;
    font-family: monospace;
    font-size: 14px;
}

.ip-actions {
    display: flex;
    gap: 8px;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-icon-danger {
    background: rgba(255, 59, 48, 0.1);
    color: #ff3b30;
}

.btn-icon-danger:hover {
    background: rgba(255, 59, 48, 0.2);
}

.info-box {
    background: rgba(0, 113, 227, 0.1);
    border-left: 4px solid #0071e3;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.info-box p {
    margin: 0;
    color: #1d1d1f;
    font-size: 14px;
    line-height: 1.6;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: rgba(52, 199, 89, 0.1);
    color: #34c759;
}

.status-inactive {
    background: rgba(142, 142, 147, 0.1);
    color: #8e8e93;
}
</style>

<div class="page-header">
    <h1>🔒 .htaccess Boshqaruvi</h1>
    <p>Fayl tahriri va xavfsizlik sozlamalari</p>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="info-box">
    <p>⚠️ <strong>Diqqat!</strong> .htaccess faylini noto'g'ri tahrirlash sayt ishlamay qolishiga olib kelishi mumkin. O'zgarishlardan oldin zaxira nusxasini oling.</p>
</div>

<div class="config-container">
    <!-- .htaccess Editor -->
    <div class="card">
        <h2 style="margin-top: 0; color: #1d1d1f;">📝 .htaccess Fayli</h2>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div class="editor-container">
                <textarea name="htaccess_content" class="code-editor" spellcheck="false"><?= htmlspecialchars($htaccess_content) ?></textarea>
            </div>
            
            <button type="submit" name="save_htaccess" class="btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
                </svg>
                Saqlash
            </button>
        </form>
    </div>
    
    <!-- Xavfsizlik Sozlamalari -->
    <div class="card">
        <h2 style="margin-top: 0; color: #1d1d1f;">🛡️ Xavfsizlik</h2>
        
        <!-- Parol himoyasi -->
        <div class="security-option">
            <div>
                <h4>Parol Himoyasi</h4>
                <p>Qo'shimcha autentifikatsiya</p>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" name="enable_protection" <?= strpos($htaccess_content, 'AuthType Basic') !== false ? 'checked' : '' ?> onchange="this.form.submit()">
                <span class="toggle-slider"></span>
            </label>
        </div>
        <form method="POST" style="display: contents;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="toggle_password_protection" value="1">
        </form>
        
        <?php if (strpos($htaccess_content, 'AuthType Basic') !== false): ?>
            <div class="status-badge status-active" style="margin-bottom: 20px;">Faol</div>
        <?php else: ?>
            <div class="status-badge status-inactive" style="margin-bottom: 20px;">No faol</div>
        <?php endif; ?>
        
        <!-- IP Cheklovlari -->
        <h3 style="color: #1d1d1f; font-size: 16px; margin: 24px 0 16px;">IP Cheklovlari</h3>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div class="form-group">
                <input type="text" name="ip_address" class="form-control" placeholder="IP manzil (masalan: 192.168.1.1)" required>
            </div>
            
            <button type="submit" name="add_ip" class="btn-secondary" style="width: 100%;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                IP Qo'shish
            </button>
        </form>
        
        <h4 style="color: #1d1d1f; font-size: 14px; margin: 20px 0 12px;">Ruxsat berilgan IP lar:</h4>
        <ul class="ip-list">
            <?php
            preg_match_all('/Allow from ([\d\.]+)/', $htaccess_content, $matches);
            $ips = $matches[1] ?? [];
            if (empty($ips)):
            ?>
                <li style="color: #86868b; font-size: 14px;">Hech qanday IP qo'shilmagan</li>
            <?php else: ?>
                <?php foreach ($ips as $ip): ?>
                    <li class="ip-item">
                        <span><?= htmlspecialchars($ip) ?></span>
                        <div class="ip-actions">
                            <button class="btn-icon btn-icon-danger" title="O'chirish" onclick="removeIP('<?= $ip ?>')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                </svg>
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        
        <!-- Xavfsizlik Headerlari -->
        <h3 style="color: #1d1d1f; font-size: 16px; margin: 24px 0 16px;">Xavfsizlik Headerlari</h3>
        
        <div class="security-option">
            <div>
                <h4>X-Content-Type-Options</h4>
                <p>MIME sniffing himoyasi</p>
            </div>
            <span class="status-badge status-active">✓</span>
        </div>
        
        <div class="security-option">
            <div>
                <h4>X-Frame-Options</h4>
                <p>Clickjacking himoyasi</p>
            </div>
            <span class="status-badge status-active">✓</span>
        </div>
        
        <div class="security-option">
            <div>
                <h4>X-XSS-Protection</h4>
                <p>XSS hujumlari himoyasi</p>
            </div>
            <span class="status-badge status-active">✓</span>
        </div>
    </div>
</div>

<script>
function removeIP(ip) {
    if (confirm(ip + ' IP manzilni o\'chirmoqchimisiz?')) {
        // Bu yerda AJAX orqali IP o'chirish amalini bajarish kerak
        // Hozircha faqat sahifani yangilaymiz
        alert('IP o\'chirish funksiyasi qo\'shimcha ishlab chiqishni talab qiladi');
    }
}

// Tab tugmasi bilan kod kiritish
const editor = document.querySelector('.code-editor');
editor.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        e.preventDefault();
        const start = this.selectionStart;
        const end = this.selectionEnd;
        this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
        this.selectionStart = this.selectionEnd = start + 4;
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?>
