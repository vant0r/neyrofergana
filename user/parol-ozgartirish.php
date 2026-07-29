<?php
/**
 * User Panel: Parol O'zgartirish
 * Foydalanuvchi parolini yangilaydi
 */
require_once '../includes/init.php';
requireAuth();

$user_id = $_SESSION['user_id'];
$page_title = "Parol O'zgartirish";
$success_message = '';
$error_message = '';

// Foydalanuvchi ma'lumotlarini olish
$user = getUserById($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Xavfsizlik xatosi. Iltimos, qayta urinib ko'ring.";
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validatsiya
        if (empty($current_password)) {
            $error_message = "Joriy parolni kiriting.";
        } elseif (empty($new_password)) {
            $error_message = "Yangi parolni kiriting.";
        } elseif (strlen($new_password) < 8) {
            $error_message = "Yangi parol kamida 8 belgidan iborat bo'lishi kerak.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Yangi parol va tasdiqlash mos kelmadi.";
        } else {
            // Joriy parolni tekshirish
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user_data || !password_verify($current_password, $user_data['password'])) {
                    $error_message = "Joriy parol noto'g'ri.";
                } else {
                    // Yangi parolni saqlash
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                    
                    $success_message = "Parol muvaffaqiyatli o'zgartirildi!";
                    
                    // Logga yozish
                    error_log("Password changed for user ID: $user_id at " . date('Y-m-d H:i:s'));
                }
            } catch (PDOException $e) {
                error_log("Password change error: " . $e->getMessage());
                $error_message = "Parolni o'zgartirishda xatolik yuz berdi.";
            }
        }
    }
}

ob_start();
?>
<div class="dashboard-container">
    <div class="page-header">
        <h1>🔑 Parol O'zgartirish</h1>
        <p>Hisobingiz xavfsizligi uchun parolni muntazam yangilab turing</p>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <div class="password-card glass-card">
        <form method="POST" class="password-form">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="form-group">
                <label for="current_password">Joriy parol *</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
            </div>
            
            <div class="form-divider"></div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="new_password">Yangi parol *</label>
                    <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
                    <small>Kamida 8 belgi: katta/kichik harf, raqam va maxsus belgi</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Yangi parolni tasdiqlash *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                </div>
            </div>
            
            <div class="password-requirements">
                <h4>Parol talablari:</h4>
                <ul>
                    <li id="req-length">✅ Kamida 8 belgi</li>
                    <li id="req-uppercase">⬜ Kamida bitta katta harf (A-Z)</li>
                    <li id="req-lowercase">⬜ Kamida bitta kichik harf (a-z)</li>
                    <li id="req-number">⬜ Kamida bitta raqam (0-9)</li>
                    <li id="req-special">⬜ Kamida bitta maxsus belgi (!@#$%^&*)</li>
                </ul>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Parolni o'zgartirish</button>
                <a href="profil.php" class="btn btn-secondary">↩️ Orqaga</a>
            </div>
        </form>
    </div>
    
    <div class="security-tips glass-card">
        <h3>🛡️ Xavfsizlik maslahatlari</h3>
        <ul>
            <li>Har 3 oyda parolni o'zgartirib turing</li>
            <li>Oddiy so'zlardan foydalanmang (masalan: "password123")</li>
            <li>Shaxsiy ma'lumotlarni ishlatmang (tug'ilgan sana, ism)</li>
            <li>Har bir saytda turli parollar ishlatng</li>
            <li>Parolni hech kimga aytmang</li>
        </ul>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 800px;
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

.password-card {
    padding: 2rem;
    margin-bottom: 1.5rem;
}

.password-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.form-group input {
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 12px;
    background: rgba(255,255,255,0.5);
    font-size: 1rem;
    transition: all 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(255,255,255,0.8);
    box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
}

.form-group small {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.form-divider {
    height: 1px;
    background: rgba(0,0,0,0.1);
    margin: 0.5rem 0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.password-requirements {
    background: rgba(0,0,0,0.03);
    padding: 1.5rem;
    border-radius: 12px;
}

.password-requirements h4 {
    font-size: 1rem;
    margin-bottom: 0.75rem;
    color: var(--text-primary);
}

.password-requirements ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.password-requirements li {
    padding: 0.5rem 0;
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.security-tips {
    padding: 2rem;
}

.security-tips h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.security-tips ul {
    padding-left: 1.5rem;
}

.security-tips li {
    padding: 0.5rem 0;
    color: var(--text-secondary);
    line-height: 1.6;
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }
    
    .password-card,
    .security-tips {
        padding: 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
const newPasswordInput = document.getElementById('new_password');

newPasswordInput.addEventListener('input', function() {
    const password = this.value;
    
    // Length check
    document.getElementById('req-length').innerHTML = password.length >= 8 ? '✅ Kamida 8 belgi' : '⬜ Kamida 8 belgi';
    
    // Uppercase check
    document.getElementById('req-uppercase').innerHTML = /[A-Z]/.test(password) ? '✅ Kamida bitta katta harf (A-Z)' : '⬜ Kamida bitta katta harf (A-Z)';
    
    // Lowercase check
    document.getElementById('req-lowercase').innerHTML = /[a-z]/.test(password) ? '✅ Kamida bitta kichik harf (a-z)' : '⬜ Kamida bitta kichik harf (a-z)';
    
    // Number check
    document.getElementById('req-number').innerHTML = /[0-9]/.test(password) ? '✅ Kamida bitta raqam (0-9)' : '⬜ Kamida bitta raqam (0-9)';
    
    // Special character check
    document.getElementById('req-special').innerHTML = /[!@#$%^&*(),.?":{}|<>]/.test(password) ? '✅ Kamida bitta maxsus belgi (!@#$%^&*)' : '⬜ Kamida bitta maxsus belgi (!@#$%^&*)';
});

// Form submit oldin tekshirish
document.querySelector('.password-form').addEventListener('submit', function(e) {
    const password = newPasswordInput.value;
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Parol kamida 8 belgidan iborat bo\'lishi kerak.');
        return false;
    }
});
</script>

<?php
$content = ob_get_clean();
require_once '../includes/user-header.php';
echo $content;
require_once '../includes/user-footer.php';
?>
