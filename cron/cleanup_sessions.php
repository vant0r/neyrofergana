<?php
/**
 * Eskirgan sessiyalarni tozalash skripti
 * Har kuni soat 03:00 da ishga tushiriladi (cron orqali)
 * 
 * Foydalanish:
 * 0 3 * * * php /path/to/cron/cleanup_sessions.php
 */

// Loyiha konfiguratsiyasini yuklash
require_once __DIR__ . '/../includes/config.php';

// Skript faqat CLI orqali ishga tushirilishi kerak
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Bu skript faqat CLI orqali ishga tushirilishi mumkin.');
}

// Log fayli
$log_file = __DIR__ . '/cleanup.log';

function log_message($message, $log_file) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

log_message("=== Sessiyalarni tozalash boshlandi ===", $log_file);

try {
    // Session papkasini tozalash
    $session_path = session_save_path();
    
    if (empty($session_path)) {
        $session_path = sys_get_temp_dir();
    }
    
    log_message("Session papkasi: {$session_path}", $log_file);
    
    if (!is_dir($session_path)) {
        log_message("❌ Session papkasi mavjud emas", $log_file);
        exit(1);
    }
    
    // Sessiya fayllarini o'qish
    $files = scandir($session_path);
    $deleted_count = 0;
    $error_count = 0;
    
    // Sessiya maksimal yoshi (sekundlarda) - default 24 soat
    $max_lifetime = ini_get('session.gc_maxlifetime');
    if (empty($max_lifetime)) {
        $max_lifetime = 86400; // 24 soat
    }
    
    log_message("Maksimal sessiya yoshi: {$max_lifetime} sekund", $log_file);
    
    $current_time = time();
    
    foreach ($files as $file) {
        // Faqat sessiya fayllari (sess_ bilan boshlanadigan)
        if (strpos($file, 'sess_') === 0) {
            $file_path = $session_path . DIRECTORY_SEPARATOR . $file;
            
            if (is_file($file_path)) {
                $file_mtime = filemtime($file_path);
                $file_age = $current_time - $file_mtime;
                
                if ($file_age > $max_lifetime) {
                    if (@unlink($file_path)) {
                        $deleted_count++;
                        log_message("✅ O'chirildi: {$file} (yoshi: " . round($file_age / 3600, 1) . " soat)", $log_file);
                    } else {
                        $error_count++;
                        log_message("❌ Xatolik: {$file}", $log_file);
                    }
                }
            }
        }
    }
    
    log_message("=== Sessiya fayllari tozalandi ===", $log_file);
    log_message("✅ O'chirilgan: {$deleted_count}", $log_file);
    log_message("❌ Xatoliklar: {$error_count}", $log_file);
    
    // Agar DB session ishlatilsa, uni ham tozalash
    try {
        $db = getDB();
        
        // Eskirgan sessiyalarni o'chirish
        $stmt = $db->prepare("DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$max_lifetime]);
        
        $db_deleted = $stmt->rowCount();
        log_message("✅ DB dan o'chirilgan sessiyalar: {$db_deleted}", $log_file);
        
    } catch (PDOException $e) {
        log_message("ℹ️  DB sessiya jadvali mavjud emas yoki xatolik: " . $e->getMessage(), $log_file);
    }
    
    // PHP garbage collection ni chaqirish
    $gc_probability = ini_get('session.gc_probability');
    $gc_divisor = ini_get('session.gc_divisor');
    
    if ($gc_probability && $gc_divisor && $gc_probability > 0 && $gc_divisor > 0) {
        $gc_chance = ($gc_probability / $gc_divisor) * 100;
        log_message("♻️  GC ehtimolligi: {$gc_chance}%", $log_file);
        
        // Manual GC chaqirish
        session_gc();
        log_message("♻️  Manual GC bajarildi", $log_file);
    }
    
    // Vaqtinchalik fayllarni ham tozalash (uploads/chat/)
    $temp_dirs = [
        __DIR__ . '/../uploads/chat/temp',
        __DIR__ . '/../uploads/tests/temp',
        sys_get_temp_dir() . '/neurofergana'
    ];
    
    foreach ($temp_dirs as $temp_dir) {
        if (is_dir($temp_dir)) {
            $temp_files = array_diff(scandir($temp_dir), ['.', '..']);
            $temp_deleted = 0;
            
            foreach ($temp_files as $temp_file) {
                $temp_path = $temp_dir . DIRECTORY_SEPARATOR . $temp_file;
                
                if (is_file($temp_path)) {
                    $temp_mtime = filemtime($temp_path);
                    $temp_age = $current_time - $temp_mtime;
                    
                    // 1 soatdan eski vaqtinchalik fayllarni o'chirish
                    if ($temp_age > 3600) {
                        if (@unlink($temp_path)) {
                            $temp_deleted++;
                        }
                    }
                }
            }
            
            if ($temp_deleted > 0) {
                log_message("✅ Vaqtinchalik fayllar o'chirildi ({$temp_dir}): {$temp_deleted}", $log_file);
            }
        }
    }
    
    log_message("=== Tozalash yakunlandi ===", $log_file);
    log_message("📊 Jami o'chirilgan sessiyalar: " . ($deleted_count + ($db_deleted ?? 0)), $log_file);
    
} catch (Exception $e) {
    log_message("❌ Umumiy xatolik: " . $e->getMessage(), $log_file);
    exit(1);
}

exit(0);
