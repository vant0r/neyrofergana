<?php
/**
 * Autentifikatsiya va avtorizatsiya funksiyalari
 */

/**
 * Foydalanuvchini ro'yxatdan o'tkazish
 */
function registerUser($data) {
    try {
        $pdo = getDBConnection();
        
        // Telefon formatlash
        $phone = formatPhone($data['phone']);
        
        // Parolni hash qilish
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, phone, email, password_hash, gender, birth_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $phone,
            $data['email'],
            $passwordHash,
            $data['gender'] ?? null,
            $data['birth_date'] ?? null
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // Audit log
        auditLog('user_registered', 'users', $userId);
        
        return ['success' => true, 'user_id' => $userId];
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            if (strpos($e->getMessage(), 'phone') !== false) {
                return ['success' => false, 'error' => 'Bu telefon raqami bilan allaqachon ro\'yxatdan o\'tilgan'];
            } elseif (strpos($e->getMessage(), 'email') !== false) {
                return ['success' => false, 'error' => 'Bu email bilan allaqachon ro\'yxatdan o\'tilgan'];
            }
        }
        
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Ro\'yxatdan o\'tishda xatolik yuz berdi'];
    }
}

/**
 * Foydalanuvchini kirish
 */
function loginUser($identifier, $password, $remember = false) {
    try {
        // Brute force tekshiruvi
        $bruteForceCheck = checkBruteForce($identifier);
        if (!$bruteForceCheck['allowed']) {
            $mins = floor($bruteForceCheck['remaining_time'] / 60);
            return ['success' => false, 'error' => 'Juda ko\'p urinishlar. ' . $mins . ' daqiqadan keyin qayta urinib ko\'ring'];
        }
        
        $pdo = getDBConnection();
        
        // Email yoki telefon bo'yicha qidiruv
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, email, phone, password_hash, role, is_active, email_verified 
            FROM users 
            WHERE email = ? OR phone = ?
        ");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'error' => 'Foydalanuvchi topilmadi'];
        }
        
        // Parol tekshiruvi
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Noto\'g\'ri parol'];
        }
        
        // Faollik tekshiruvi
        if (!$user['is_active']) {
            return ['success' => false, 'error' => 'Hisobingiz bloklangan'];
        }
        
        // Sessiya yaratish
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        
        // Session regeneratsiya
        regenerateSession();
        
        // Brute force counter reset
        resetBruteForce($identifier);
        
        // Audit log
        auditLog('user_login', 'users', $user['id']);
        
        // Remember me (30 kun)
        if ($remember) {
            setRememberToken($user['id']);
        }
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'role' => $user['role']
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Kirish jarayonida xatolik yuz berdi'];
    }
}

/**
 * Chiqish
 */
function logoutUser() {
    // Audit log
    if (isset($_SESSION['user_id'])) {
        auditLog('user_logout', 'users', $_SESSION['user_id']);
    }
    
    // Session tozalash
    $_SESSION = [];
    
    // Cookie larni o'chirish
    if (isset($_COOKIE['remember_token'])) {
        deleteRememberToken();
    }
    
    // Session destroy
    session_destroy();
    
    // CSRF token yangilash (agar sessiya qayta boshlansa)
    session_start();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Foydalanuvchi autentifikatsiyasi tekshiruvi
 */
function requireAuth($redirectUrl = '/kirish-royxatdan-otish.php') {
    if (!isset($_SESSION['user_id'])) {
        // Remember me cookie tekshiruvi
        if (!checkRememberToken()) {
            setToast('Iltimos, avval tizimga kiring', 'info');
            redirect($redirectUrl);
        }
    }
}

/**
 * Admin autentifikatsiyasi tekshiruvi
 */
function requireAdmin($redirectUrl = '/kirish-royxatdan-otish.php') {
    requireAuth($redirectUrl);
    
    if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
        setToast('Bu sahifaga kirish huquqi yo\'q', 'error');
        redirect('/index.php');
    }
}

/**
 * Superadmin autentifikatsiyasi tekshiruvi
 */
function requireSuperAdmin($redirectUrl = '/admin/boshqaruv.php') {
    requireAdmin($redirectUrl);
    
    if ($_SESSION['user_role'] !== 'superadmin') {
        setToast('Bu sahifaga kirish huquqi yo\'q', 'error');
        redirect('/admin/boshqaruv.php');
    }
}

/**
 * Remember me token yaratish
 */
function setRememberToken($userId) {
    $token = bin2hex(random_bytes(32));
    $expires = time() + (30 * 24 * 60 * 60); // 30 kun
    
    setcookie('remember_token', $token, $expires, '/', '', false, true);
    setcookie('remember_user', $userId, $expires, '/', '', false, true);
    
    // Token ni DB ga saqlash (ixtiyoriy)
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, remember_expires = ? WHERE id = ?");
        $stmt->execute([$token, date('Y-m-d H:i:s', $expires), $userId]);
    } catch (Exception $e) {
        error_log("Remember token save error: " . $e->getMessage());
    }
}

/**
 * Remember me token tekshiruvi
 */
function checkRememberToken() {
    if (!isset($_COOKIE['remember_token']) || !isset($_COOKIE['remember_user'])) {
        return false;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, role FROM users WHERE id = ? AND remember_token = ? AND remember_expires > NOW()");
        $stmt->execute([$_COOKIE['remember_user'], $_COOKIE['remember_token']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            return true;
        }
    } catch (Exception $e) {
        error_log("Remember token check error: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Remember me token o'chirish
 */
function deleteRememberToken() {
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('remember_user', '', time() - 3600, '/');
    
    if (isset($_COOKIE['remember_user'])) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE id = ?");
            $stmt->execute([$_COOKIE['remember_user']]);
        } catch (Exception $e) {
            error_log("Remember token delete error: " . $e->getMessage());
        }
    }
}

/**
 * Parolni tiklash kodi yaratish
 */
function createPasswordResetToken($email) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE email = ? AND is_active = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'error' => 'Foydalanuvchi topilmadi'];
        }
        
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 soat
        
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);
        
        // Reset link yuborish
        $resetLink = SITE_URL . '/parol-tiklash.php?token=' . $token;
        
        // Email yuborish (keyinchalik amalga oshiriladi)
        // sendPasswordResetEmail($user['email'], $user['first_name'], $resetLink);
        
        return ['success' => true, 'link' => $resetLink];
        
    } catch (Exception $e) {
        error_log("Password reset token error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Xatolik yuz berdi'];
    }
}

/**
 * Parolni tiklash
 */
function resetPassword($token, $newPassword) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'error' => 'Token muddati tugagan yoki noto\'g\'ri'];
        }
        
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$passwordHash, $user['id']]);
        
        auditLog('password_reset', 'users', $user['id']);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Parolni o\'zgartirishda xatolik yuz berdi'];
    }
}

/**
 * Parolni o'zgartirish
 */
function changePassword($userId, $oldPassword, $newPassword) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Eski parol noto\'g\'ri'];
        }
        
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $userId]);
        
        auditLog('password_changed', 'users', $userId);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        error_log("Change password error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Parolni o\'zgartirishda xatolik yuz berdi'];
    }
}

/**
 * Email tasdiqlash kodi yaratish
 */
function createEmailVerificationCode($userId) {
    $code = sprintf('%06d', mt_rand(0, 999999));
    $expires = date('Y-m-d H:i:s', time() + 600); // 10 daqiqa
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET verification_code = ?, verification_expires = ? WHERE id = ?");
        $stmt->execute([$code, $expires, $userId]);
        
        // Email yuborish (keyinchalik amalga oshiriladi)
        // sendVerificationEmail($user['email'], $user['first_name'], $code);
        
        return ['success' => true, 'code' => $code];
    } catch (Exception $e) {
        error_log("Email verification code error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Xatolik yuz berdi'];
    }
}

/**
 * Email tasdiqlash
 */
function verifyEmail($userId, $code) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND verification_code = ? AND verification_expires > NOW()");
        $stmt->execute([$userId, $code]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'error' => 'Kod noto\'g\'ri yoki muddati tugagan'];
        }
        
        $stmt = $pdo->prepare("UPDATE users SET email_verified = TRUE, verification_code = NULL, verification_expires = NULL WHERE id = ?");
        $stmt->execute([$userId]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        error_log("Email verification error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Tasdiqlashda xatolik yuz berdi'];
    }
}
