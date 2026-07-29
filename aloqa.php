<?php
require_once 'includes/init.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        $errorMessage = 'Xavfsizlik xatolik! Sahifani yangilab qayta urinib ko\'ring.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        
        $errors = [];
        if (strlen($name) < 2) $errors[] = 'Ism juda qisqa';
        if (strlen($phone) < 9) $errors[] = 'Telefon raqam noto\'g\'ri';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email noto\'g\'ri';
        if (strlen($message) < 10) $errors[] = 'Xabar juda qisqa';
        
        if (empty($errors)) {
            try {
                $pdo = getDB();
                $stmt = $pdo->prepare("INSERT INTO contact_messages (name, phone, email, message, status, created_at) VALUES (:name, :phone, :email, :message, 'new', NOW())");
                $stmt->execute([
                    ':name' => $name,
                    ':phone' => $phone,
                    ':email' => $email ?: null,
                    ':message' => $message
                ]);
                
                $successMessage = 'Xabaringiz qabul qilindi! Tez orada aloqaga chiqamiz.';
            } catch (Exception $e) {
                error_log("Aloqa xatolik: " . $e->getMessage());
                $errorMessage = 'Xatolik yuz berdi. Iltimos qayta urinib ko\'ring.';
            }
        } else {
            $errorMessage = implode(', ', $errors);
        }
    }
}

$contacts = getContacts();
$siteSettings = getSiteSettings();
$pageTitle = "Aloqa - " . ($siteSettings['site_name'] ?? 'Klinika');
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>Aloqa</h1>
        <p>Biz bilan bog'laning</p>
    </div>
</div>

<div class="container section">
    <?php if ($successMessage): ?>
        <div class="alert alert-success glass-card"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-error glass-card"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>
    
    <div class="contact-grid">
        <div class="contact-info glass-card">
            <h2>Aloqa ma'lumotlari</h2>
            
            <?php if (!empty($contacts['phones'])): ?>
            <div class="contact-item">
                <span class="contact-icon">📞</span>
                <div>
                    <h4>Telefon raqamlar:</h4>
                    <?php foreach (explode(',', $contacts['phones']) as $phone): ?>
                        <a href="tel:<?= htmlspecialchars(trim($phone)) ?>"><?= htmlspecialchars(trim($phone)) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($contacts['email']): ?>
            <div class="contact-item">
                <span class="contact-icon">✉️</span>
                <div>
                    <h4>Email:</h4>
                    <a href="mailto:<?= htmlspecialchars($contacts['email']) ?>"><?= htmlspecialchars($contacts['email']) ?></a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($contacts['work_hours'])): ?>
            <div class="contact-item">
                <span class="contact-icon">🕒</span>
                <div>
                    <h4>Ish vaqti:</h4>
                    <p><?= nl2br(htmlspecialchars($contacts['work_hours'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($contacts['address'])): ?>
            <div class="contact-item">
                <span class="contact-icon">📍</span>
                <div>
                    <h4>Manzil:</h4>
                    <p><?= nl2br(htmlspecialchars($contacts['address'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($contacts['landmark'])): ?>
            <div class="contact-item">
                <span class="contact-icon">🏢</span>
                <div>
                    <h4>Mo'ljal:</h4>
                    <p><?= htmlspecialchars($contacts['landmark']) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="social-links">
                <h4>Ijtimoiy tarmoqlar:</h4>
                <div class="social-icons">
                    <?php if (!empty($contacts['telegram'])): ?>
                        <a href="<?= htmlspecialchars($contacts['telegram']) ?>" class="social-icon telegram" target="_blank">TG</a>
                    <?php endif; ?>
                    <?php if (!empty($contacts['instagram'])): ?>
                        <a href="<?= htmlspecialchars($contacts['instagram']) ?>" class="social-icon instagram" target="_blank">IG</a>
                    <?php endif; ?>
                    <?php if (!empty($contacts['facebook'])): ?>
                        <a href="<?= htmlspecialchars($contacts['facebook']) ?>" class="social-icon facebook" target="_blank">FB</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="contact-form-wrapper glass-card">
            <h2>Xabar yuborish</h2>
            <form method="POST" class="contact-form" id="contactForm">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <div class="form-group">
                    <label for="name">Ismingiz *</label>
                    <input type="text" id="name" name="name" required placeholder="Ismingizni kiriting" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Telefon raqam *</label>
                    <input type="tel" id="phone" name="phone" required placeholder="+998 90 123 45 67" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email (ixtiyoriy)</label>
                    <input type="email" id="email" name="email" placeholder="example@mail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="message">Xabar *</label>
                    <textarea id="message" name="message" rows="5" required placeholder="Xabaringizni yozing..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-large">Yuborish</button>
            </form>
        </div>
    </div>
    
    <?php if (!empty($contacts['map_url'])): ?>
    <div class="map-section glass-card">
        <h2>Manzil xaritada</h2>
        <div class="map-container">
            <iframe src="<?= htmlspecialchars($contacts['map_url']) ?>" width="100%" height="450" style="border:0; border-radius: 16px;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.page-hero {
    background: linear-gradient(135deg, var(--primary-color), #5ac8fa);
    color: white;
    padding: clamp(60px, 10vh, 100px) 0;
    text-align: center;
}

.page-hero h1 {
    font-size: clamp(28px, 5vw, 42px);
    margin-bottom: 10px;
}

.page-hero p {
    font-size: clamp(16px, 3vw, 20px);
    opacity: 0.9;
}

.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success {
    background: rgba(52, 199, 89, 0.1);
    color: #34c759;
    border: 1px solid rgba(52, 199, 89, 0.3);
}

.alert-error {
    background: rgba(255, 59, 48, 0.1);
    color: #ff3b30;
    border: 1px solid rgba(255, 59, 48, 0.3);
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.contact-info,
.contact-form-wrapper {
    padding: 30px;
}

.contact-info h2,
.contact-form-wrapper h2 {
    font-size: 22px;
    margin-bottom: 25px;
    color: var(--text-primary);
}

.contact-item {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.contact-item h4 {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 5px;
    font-weight: 500;
}

.contact-item a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    display: block;
}

.contact-item p {
    color: var(--text-primary);
    line-height: 1.6;
}

.social-links {
    margin-top: 25px;
}

.social-links h4 {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 12px;
}

.social-icons {
    display: flex;
    gap: 10px;
}

.social-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.social-icon:hover {
    transform: translateY(-3px);
    opacity: 0.9;
}

.social-icon.telegram {
    background: #0088cc;
    color: white;
}

.social-icon.instagram {
    background: linear-gradient(135deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
    color: white;
}

.social-icon.facebook {
    background: #1877f2;
    color: white;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-primary);
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 12px;
    background: rgba(255,255,255,0.8);
    font-size: 16px;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0,113,227,0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.btn-large {
    width: 100%;
    padding: 16px;
    font-size: 18px;
}

.map-section {
    padding: 30px;
}

.map-section h2 {
    font-size: 22px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

.map-container {
    overflow: hidden;
    border-radius: 16px;
}

@media (max-width: 900px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    const phone = document.getElementById('phone').value;
    const phonePattern = /^[\+]?[(]?[0-9]{1,3}[)]?[-\s\.]?[0-9]{1,4}[-\s\.]?[0-9]{1,4}[-\s\.]?[0-9]{1,9}$/;
    
    if (!phonePattern.test(phone.replace(/\s/g, ''))) {
        e.preventDefault();
        showToast('Telefon raqam noto\'g\'ri formatda', 'error');
        return false;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
