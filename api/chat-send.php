<?php
/**
 * API: Chat xabar yuborish
 * Matn va fayl yuborish uchun ishlatiladi
 */

header('Content-Type: application/json');
session_start();

// Konfiguratsiya va autentifikatsiya
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/validation.php';

// Faqat avtorizatsiya qilingan foydalanuvchilar uchun
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Avtorizatsiya talab etiladi']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'user';

// Faqat POST so'rovlar uchun
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Faqat POST so\'rovlari qabul qilinadi']);
    exit;
}

// CSRF token tekshiruvi
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'CSRF token noto\'g\'ri']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Parametrlarni olish va tozalash
    $chat_id = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;
    $message = trim($_POST['message'] ?? '');
    $file_url = null;
    $file_type = null;
    
    // Chat ID aniqlash
    if ($chat_id === 0) {
        $stmt = $pdo->prepare("
            SELECT id FROM chats 
            WHERE user_id = ? AND status = 'active' 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $chat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$chat) {
            // Yangi chat yaratish
            $stmt = $pdo->prepare("
                INSERT INTO chats (user_id, status, created_at) 
                VALUES (?, 'active', NOW())
            ");
            $stmt->execute([$user_id]);
            $chat_id = $pdo->lastInsertId();
        } else {
            $chat_id = $chat['id'];
        }
    }
    
    // Xabar matni bo'sh bo'lmasa, validatsiya qilish
    if (!empty($message)) {
        $message = sanitizeInput($message);
        if (strlen($message) > 2000) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Xabar juda uzun (maks 2000 belgi)']);
            exit;
        }
    }
    
    // Fayl yuklash (agar mavjud bo'lsa)
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/chat/';
        
        // Papka mavjudligini tekshirish
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file = $_FILES['file'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        // MIME type tekshiruvi
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Noto\'g\'ri fayl formati. Ruxsat etilgan formatlar: JPG, PNG, GIF, WEBP, PDF']);
            exit;
        }
        
        // Hajm tekshiruvi
        if ($file['size'] > $max_size) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Fayl hajmi juda katta (maks 10MB)']);
            exit;
        }
        
        // Fayl nomini generatsiya qilish
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('chat_') . '.' . $extension;
        $destination = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $file_url = '/uploads/chat/' . $new_filename;
            $file_type = $mime_type;
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Faylni yuklashda xatolik']);
            exit;
        }
    }
    
    // Xabarni saqlash
    if (empty($message) && $file_url === null) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Xabar matni yoki fayl kerak']);
        exit;
    }
    
    $sender_type = $user_role === 'admin' ? 'admin' : 'user';
    
    $stmt = $pdo->prepare("
        INSERT INTO chat_messages 
        (chat_id, sender_id, sender_type, message, file_url, file_type, is_read, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
    ");
    $stmt->execute([$chat_id, $user_id, $sender_type, $message ?: null, $file_url, $file_type]);
    
    $message_id = $pdo->lastInsertId();
    
    // Admin uchun yangi xabar haqida Telegram orqali bildirishnoma yuborish (ixtiyoriy)
    if ($sender_type === 'user') {
        // Telegram bot orqali adminlarga xabar yuborish mumkin
        // requires ../includes/telegram.php
    }
    
    echo json_encode([
        'status' => 'success',
        'message_id' => (int)$message_id,
        'chat_id' => $chat_id,
        'file_url' => $file_url ? BASE_URL . $file_url : null
    ]);
    
} catch (PDOException $e) {
    error_log("Chat Send Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server xatolik yuz berdi'
    ]);
}
