<?php
/**
 * User Panel: Profil
 * Bemor o'z profil ma'lumotlarini ko'radi va tahrirlaydi
 */
require_once '../includes/init.php';
requireAuth(); // Faqat ro'yxatdan o'tgan bemorlar

$user_id = $_SESSION['user_id'];
$page_title = "Mening Profilim";
$success_message = '';
$error_message = '';

// Profil ma'lumotlarini olish
$user = getUserById($user_id);

if (!$user) {
    session_destroy();
    header('Location: ../kirish-royxatdan-otish.php');
    exit;
}

// Forma yuborilganda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Xavfsizlik xatosi. Iltimos, qayta urinib ko'ring.";
    } else {
        $first_name = sanitizeInput($_POST['first_name'] ?? '');
        $last_name = sanitizeInput($_POST['last_name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $birth_date = sanitizeInput($_POST['birth_date'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $blood_type = sanitizeInput($_POST['blood_type'] ?? '');
        
        // Validatsiya
        if (empty($first_name) || empty($last_name)) {
            $error_message = "Ism va familiya majburiy.";
        } elseif (empty($phone)) {
            $error_message = "Telefon raqami majburiy.";
        } elseif (!preg_match('/^[+]?[0-9\s\-()]+$/', $phone)) {
            $error_message = "Telefon raqami noto'g'ri formatda.";
        } else {
            // Ma'lumotlarni yangilash
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, phone = ?, birth_date = ?, gender = ?, blood_type = ?
                    WHERE id = ?
                ");
                $stmt->execute([$first_name, $last_name, $phone, $birth_date ?: null, $gender ?: null, $blood_type ?: null, $user_id]);
                
                $success_message = "Profil ma'lumotlari muvaffaqiyatli yangilandi!";
                
                // Yangilangan ma'lumotlarni qayta yuklash
                $user = getUserById($user_id);
            } catch (PDOException $e) {
                error_log("Profile update error: " . $e->getMessage());
                $error_message = "Ma'lumotlarni yangilashda xatolik yuz berdi.";
            }
        }
    }
}

// Avatar yuklash
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES['avatar']['type'], $allowed_types)) {
        $error_message = "Faqat JPG, PNG yoki WEBP formatlari ruxsat etiladi.";
    } elseif ($_FILES['avatar']['size'] > $max_size) {
        $error_message = "Fayl hajmi 5MB dan oshmasligi kerak.";
    } else {
        $upload_dir = '../uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
            // Eski avatarni o'chirish
            if ($user['avatar'] && file_exists('../' . $user['avatar'])) {
                unlink('../' . $user['avatar']);
            }
            
            // DB da yangilash
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute(['uploads/avatars/' . $new_filename, $user_id]);
                
                $success_message = "Avatar muvaffaqiyatli yuklandi!";
                $user = getUserById($user_id);
            } catch (PDOException $e) {
                error_log("Avatar update error: " . $e->getMessage());
                $error_message = "Avatarni saqlashda xatolik yuz berdi.";
            }
        } else {
            $error_message = "Faylni yuklashda xatolik yuz berdi.";
        }
    }
}

ob_start();
?>
<div class="dashboard-container">
    <div class="page-header">
        <h1>👤 Mening Profilim</h1>
        <p>Shaxsiy ma'lumotlaringizni ko'ring va tahrirlang</p>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <!-- Avatar kartasi -->
        <div class="profile-avatar-card glass-card">
            <div class="avatar-wrapper">
                <?php if ($user['avatar']): ?>
                    <img src="../<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="profile-avatar">
                <?php else: ?>
                    <div class="profile-avatar-placeholder">
                        <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="avatar-form">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <label for="avatar-upload" class="btn btn-secondary btn-sm">
                    📷 Avatar o'zgartirish
                </label>
                <input type="file" id="avatar-upload" name="avatar" accept="image/*" onchange="this.form.submit()" style="display: none;">
                <p class="avatar-hint">JPG, PNG, WEBP (max 5MB)</p>
            </form>
        </div>
        
        <!-- Ma'lumotlar formasi -->
        <div class="profile-form-card glass-card">
            <form method="POST" class="profile-form">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Ism *</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Familiya *</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Telefon *</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required placeholder="+998 90 123 45 67">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="disabled-input">
                        <small>Email o'zgartirilmaydi</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="birth_date">Tug'ilgan sana</label>
                        <input type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Jinsi</label>
                        <select id="gender" name="gender">
                            <option value="">Tanlanmagan</option>
                            <option value="male" <?= $user['gender'] == 'male' ? 'selected' : '' ?>>Erkak</option>
                            <option value="female" <?= $user['gender'] == 'female' ? 'selected' : '' ?>>Ayol</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="blood_type">Qon guruhi</label>
                    <select id="blood_type" name="blood_type">
                        <option value="">Noma'lum</option>
                        <option value="A+" <?= $user['blood_type'] == 'A+' ? 'selected' : '' ?>>A+</option>
                        <option value="A-" <?= $user['blood_type'] == 'A-' ? 'selected' : '' ?>>A-</option>
                        <option value="B+" <?= $user['blood_type'] == 'B+' ? 'selected' : '' ?>>B+</option>
                        <option value="B-" <?= $user['blood_type'] == 'B-' ? 'selected' : '' ?>>B-</option>
                        <option value="AB+" <?= $user['blood_type'] == 'AB+' ? 'selected' : '' ?>>AB+</option>
                        <option value="AB-" <?= $user['blood_type'] == 'AB-' ? 'selected' : '' ?>>AB-</option>
                        <option value="O+" <?= $user['blood_type'] == 'O+' ? 'selected' : '' ?>>O+</option>
                        <option value="O-" <?= $user['blood_type'] == 'O-' ? 'selected' : '' ?>>O-</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Saqlash</button>
                    <a href="parol-ozgartirish.php" class="btn btn-secondary">🔑 Parolni o'zgartirish</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Qo'shimcha ma'lumotlar -->
    <div class="profile-info-grid">
        <div class="info-card glass-card">
            <h3>📊 Hisob ma'lumotlari</h3>
            <div class="info-row">
                <span>Ro'yxatdan o'tgan:</span>
                <strong><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></strong>
            </div>
            <div class="info-row">
                <span>Holat:</span>
                <span class="badge badge-success">Faol</span>
            </div>
            <div class="info-row">
                <span>Oxirgi kirish:</span>
                <strong><?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Hech qachon' ?></strong>
            </div>
        </div>
        
        <div class="info-card glass-card">
            <h3>⚡ Tezkor havolalar</h3>
            <div class="quick-links">
                <a href="navbatlar.php" class="quick-link">📋 Mening navbatlarim</a>
                <a href="testlar.php" class="quick-link">🧪 Test natijalari</a>
                <a href="fikrlar.php" class="quick-link">💬 Sharh qoldirish</a>
                <a href="bildirishnomalar.php" class="quick-link">🔔 Bildirishnomalar</a>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
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

.profile-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.profile-avatar-card {
    text-align: center;
    padding: 2rem;
}

.avatar-wrapper {
    margin-bottom: 1.5rem;
}

.profile-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255,255,255,0.5);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.profile-avatar-placeholder {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #5ac8fa);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 600;
    color: white;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.avatar-form {
    margin-top: 1rem;
}

.avatar-hint {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
}

.profile-form-card {
    padding: 2rem;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
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

.form-group input,
.form-group select {
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 12px;
    background: rgba(255,255,255,0.5);
    font-size: 1rem;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(255,255,255,0.8);
    box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
}

.disabled-input {
    background: rgba(0,0,0,0.05);
    cursor: not-allowed;
}

.form-group small {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.profile-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.info-card {
    padding: 1.5rem;
}

.info-card h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.info-row:last-child {
    border-bottom: none;
}

.quick-links {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.quick-link {
    padding: 0.75rem 1rem;
    background: rgba(255,255,255,0.5);
    border-radius: 12px;
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.3s;
}

.quick-link:hover {
    background: rgba(0, 113, 227, 0.1);
    transform: translateX(4px);
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }
    
    .profile-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-avatar-card {
        order: -1;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .profile-info-grid {
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

<?php
$content = ob_get_clean();
require_once '../includes/user-header.php';
echo $content;
require_once '../includes/user-footer.php';
?>
