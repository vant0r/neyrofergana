<?php
/**
 * Xavfsizlik funksiyalari
 * CSRF, XSS, SQL injection himoya
 */

/**
 * CSRF token yaratish
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token tekshirish
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF input field HTML i
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
}

/**
 * IP adresni olish
 */
function getClientIp() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * User Agent ni olish
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Session regeneratsiya qilish (xavfsizlik uchun)
 */
function regenerateSession() {
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Rate limiting (so'rovlar sonini cheklash)
 */
function rateLimit($action, $limit = 10, $timeWindow = 60) {
    $ip = getClientIp();
    $key = 'rate_limit_' . $action . '_' . md5($ip);
    
    $now = time();
    $attempts = $_SESSION[$key] ?? ['count' => 0, 'reset' => $now];
    
    if ($now > $attempts['reset']) {
        $attempts = ['count' => 0, 'reset' => $now + $timeWindow];
    }
    
    $attempts['count']++;
    $_SESSION[$key] = $attempts;
    
    if ($attempts['count'] > $limit) {
        return false;
    }
    
    return true;
}

/**
 * Brute force himoyasi (kirish urinishlari)
 */
function checkBruteForce($identifier, $maxAttempts = 5, $lockTime = 900) {
    $key = 'brute_force_' . md5($identifier);
    $now = time();
    
    $attempts = $_SESSION[$key] ?? ['count' => 0, 'locked_until' => 0];
    
    // Agar lock qilingan bo'lsa
    if ($attempts['locked_until'] > $now) {
        $remainingTime = $attempts['locked_until'] - $now;
        return ['allowed' => false, 'remaining_time' => $remainingTime];
    }
    
    // Lock muddati o'tgan bo'lsa, reset qilish
    if ($attempts['locked_until'] > 0 && $now >= $attempts['locked_until']) {
        $attempts = ['count' => 0, 'locked_until' => 0];
    }
    
    $attempts['count']++;
    
    if ($attempts['count'] >= $maxAttempts) {
        $attempts['locked_until'] = $now + $lockTime;
        $_SESSION[$key] = $attempts;
        return ['allowed' => false, 'remaining_time' => $lockTime];
    }
    
    $_SESSION[$key] = $attempts;
    return ['allowed' => true, 'remaining_attempts' => $maxAttempts - $attempts['count']];
}

/**
 * Brute force counter reset qilish (muvaffaqiyatli kirishdan keyin)
 */
function resetBruteForce($identifier) {
    $key = 'brute_force_' . md5($identifier);
    unset($_SESSION[$key]);
}

/**
 * Fayl yuklash xavfsizligi
 */
function validateUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'], $maxSize = 10485760) {
    // Fayl mavjudligi
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Fayl yuklashda xatolik'];
    }
    
    // Hajm tekshiruvi
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'Fayl hajmi juda katta (maks: ' . ($maxSize / 1048576) . 'MB)'];
    }
    
    // MIME type tekshiruvi
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['valid' => false, 'error' => 'Noto\'g\'ri fayl formati'];
    }
    
    // Kengaytma tekshiruvi
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = array_map(function($type) {
        return str_replace('image/', '', $type);
    }, $allowedTypes);
    
    if (!in_array($ext, $allowedExts)) {
        return ['valid' => false, 'error' => 'Noto\'g\'ri fayl kengaytmasi'];
    }
    
    return ['valid' => true, 'mime' => $mimeType, 'ext' => $ext];
}

/**
 * SQL injection himoyasi uchun input tozalash
 */
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    
    // Trim va slashes olib tashlash
    $data = trim($data);
    $data = stripslashes($data);
    
    return $data;
}

/**
 * HTML tags tozalash (faqat ruxsat etilgan teglar qoladi)
 */
function stripTagsSafe($string, $allowedTags = '<p><br><strong><em><ul><ol><li><a><img>') {
    return strip_tags($string ?? '', $allowedTags);
}

/**
 * URL xavfsizligi
 */
function sanitizeUrl($url) {
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    return '';
}

/**
 * Email xavfsizligi
 */
function sanitizeEmail($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

/**
 * Integer xavfsizligi
 */
function sanitizeInt($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * XSS himoyasi uchun output encoding
 */
function htmlEncode($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * JavaScript context uchun encoding
 */
function jsEncode($string) {
    return json_encode($string ?? '');
}

/**
 * CSS context uchun encoding
 */
function cssEncode($string) {
    return addslashes($string ?? '');
}

/**
 * Attribute context uchun encoding
 */
function attrEncode($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Content Security Policy header
 */
function setCspHeader() {
    $csp = "default-src 'self'; "
         . "script-src 'self' 'unsafe-inline'; "
         . "style-src 'self' 'unsafe-inline'; "
         . "img-src 'self' data: https:; "
         . "font-src 'self'; "
         . "connect-src 'self'; "
         . "frame-ancestors 'self';";
    
    header("Content-Security-Policy: " . $csp);
}

/**
 * HTTPS ga majburiy yo'naltirish (production da ishlatish kerak)
 */
function forceHttps() {
    if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}

/**
 * Admin panel himoyasi (IP whitelist)
 */
function checkAdminIp($allowedIps = []) {
    if (empty($allowedIps)) {
        return true;
    }
    
    $clientIp = getClientIp();
    
    if (!in_array($clientIp, $allowedIps)) {
        http_response_code(403);
        die('Kirish rad etildi');
    }
    
    return true;
}
