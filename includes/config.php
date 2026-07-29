<?php
/**
 * Ma'lumotlar bazasi ulanish konfiguratsiyasi
 * Bu fayl install.php orqali avtomatik yaratiladi/yangilanadi
 */

// DB sozlamalari (install jarayonida to'ldiriladi)
define('DB_HOST', 'localhost');
define('DB_NAME', 'clinic_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Sayt sozlamalari
define('SITE_URL', 'http://localhost');
define('BASE_PATH', dirname(__DIR__));

// Uploads yo'llari
define('UPLOAD_LOGO', BASE_PATH . '/uploads/logo/');
define('UPLOAD_FAVICON', BASE_PATH . '/uploads/favicon/');
define('UPLOAD_BANNERS', BASE_PATH . '/uploads/banners/');
define('UPLOAD_DOCTORS', BASE_PATH . '/uploads/doctors/');
define('UPLOAD_GALLERY', BASE_PATH . '/uploads/gallery/');
define('UPLOAD_TESTS', BASE_PATH . '/uploads/tests/');
define('UPLOAD_AVATARS', BASE_PATH . '/uploads/avatars/');
define('UPLOAD_ADS', BASE_PATH . '/uploads/ads/');
define('UPLOAD_CHAT', BASE_PATH . '/uploads/chat/');

// Data papkasi
define('DATA_PATH', BASE_PATH . '/data/');

// Session sozlamalari
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);

// Xatoliklarni log qilish (production da o'chirish kerak)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/error.log');

// Vaqt zonasi
date_default_timezone_set('Asia/Tashkent');

// PDO ulanish funksiyasi
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("DB Connection Error: " . $e->getMessage());
            throw new Exception("Ma'lumotlar bazasiga ulanishda xatolik yuz berdi.");
        }
    }
    
    return $pdo;
}

// JSON fayllarni o'qish funksiyasi
function getSettings() {
    static $settings = null;
    
    if ($settings === null) {
        $file = DATA_PATH . 'settings.json';
        if (file_exists($file)) {
            $settings = json_decode(file_get_contents($file), true);
        } else {
            $settings = [];
        }
    }
    
    return $settings;
}

function getContacts() {
    static $contacts = null;
    
    if ($contacts === null) {
        $file = DATA_PATH . 'contacts.json';
        if (file_exists($file)) {
            $contacts = json_decode(file_get_contents($file), true);
        } else {
            $contacts = [];
        }
    }
    
    return $contacts;
}

function getAds() {
    static $ads = null;
    
    if ($ads === null) {
        $file = DATA_PATH . 'ads.json';
        if (file_exists($file)) {
            $ads = json_decode(file_get_contents($file), true);
        } else {
            $ads = ['ads' => []];
        }
    }
    
    return $ads;
}

// Media URL larini olish
function getLogoUrl($type = 'main') {
    $settings = getSettings();
    $logoField = $type === 'white' ? 'logo_white' : 'logo_main';
    
    // DB dan logo nomini olish (keyinchalik amalga oshiriladi)
    $logoName = ''; // Placeholder
    
    if (!empty($logoName)) {
        return SITE_URL . '/uploads/logo/' . $logoName;
    }
    
    return SITE_URL . '/uploads/logo/default-logo.png';
}

function getFaviconUrl() {
    $settings = getSettings();
    $faviconName = ''; // Placeholder
    
    if (!empty($faviconName)) {
        return SITE_URL . '/uploads/favicon/' . $faviconName;
    }
    
    return SITE_URL . '/uploads/favicon/default-favicon.png';
}
