<?php
/**
 * Umumiy initsializatsiya fayli
 * Sessiya boshlash, xavfsizlik sozlamalari, avtomatik yuklash
 */

// Config faylini yuklash
require_once __DIR__ . '/config.php';

// Sessiyani boshlash (agar hali boshlanmagan bo'lsa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xavfsizlik headerlari
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CSRF token yaratish (agar yo'q bo'lsa)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Global funksiyalarni yuklash
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/validation.php';

// Foydalanuvchi ma'lumotlarini olish (agar sessiya mavjud bo'lsa)
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, role, avatar FROM users WHERE id = ? AND is_active = TRUE");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch();
        
        if ($currentUser) {
            $_SESSION['user_role'] = $currentUser['role'];
        }
    } catch (Exception $e) {
        error_log("User fetch error: " . $e->getMessage());
    }
}

// Sayt sozlamalarini global o'zgaruvchilarga olish
$siteSettings = getSettings();
$siteContacts = getContacts();
$siteAds = getAds();

// Sahifa nomi (har bir sahifada o'zgartiriladi)
$pageTitle = isset($pageTitle) ? $pageTitle : ($siteSettings['site_name'] ?? 'Shifo Klinikasi');
$pageDescription = isset($pageDescription) ? $pageDescription : ($siteSettings['site_description'] ?? '');
$pageKeywords = isset($pageKeywords) ? $pageKeywords : ($siteSettings['site_keywords'] ?? '');
