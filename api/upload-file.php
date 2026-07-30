<?php
/**
 * API: Chat orqali fayl yuklash
 * Rasm, PDF va boshqa fayllarni yuklash uchun
 */

header('Content-Type: application/json');
session_start();

// Konfiguratsiya va autentifikatsiya
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
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

try {
    $pdo = getDBConnection();
    
    // Chat ID olish
    $chat_id = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;
    
    // Chat ID aniqlash (agar berilmagan bo'lsa)
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
    
    // Fayl mavjudligini tekshirish
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'Fayl hajmi server chegarasidan oshdi',
            UPLOAD_ERR_FORM_SIZE => 'Fayl hajmi forma chegarasidan oshdi',
            UPLOAD_ERR_PARTIAL => 'Fayl qisman yuklandi',
            UPLOAD_ERR_NO_FILE => 'Fayl yuklanmadi',
            UPLOAD_ERR_NO_TMP_DIR => 'Vaqtinchalik papka topilmadi',
            UPLOAD_ERR_CANT_WRITE => 'Faylni yozishda xatolik',
            UPLOAD_ERR_EXTENSION => 'PHP kengaytmasi to\'xtatdi'
        ];
        
        $error_code = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $error_message = $error_messages[$error_code] ?? 'Noma\'lum xatolik';
        
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $error_message,
            'error_code' => $error_code
        ]);
        exit;
    }
    
    $upload_dir = __DIR__ . '/../uploads/chat/';
    
    // Papka mavjudligini tekshirish va yaratish
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // .htaccess yaratish (xavfsizlik uchun)
    $htaccess_file = $upload_dir . '.htaccess';
    if (!file_exists($htaccess_file)) {
        file_put_contents($htaccess_file, "Options -ExecCGI\nAddType text/plain .php .phtml .php3 .php4 .php5\n");
    }
    
    $file = $_FILES['file'];
    
    // Ruxsat etilgan MIME turlari
    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'image/heic' => 'heic',
        'image/heif' => 'heif'
    ];
    
    $max_size = 10 * 1024 * 1024; // 10MB
    
    // MIME type tekshiruvi
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!array_key_exists($mime_type, $allowed_types)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Noto\'g\'ri fayl formati. Ruxsat etilgan formatlar: JPG, PNG, GIF, WEBP, PDF, HEIC, HEIF',
            'detected_mime' => $mime_type
        ]);
        exit;
    }
    
    // Hajm tekshiruvi
    if ($file['size'] > $max_size) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Fayl hajmi juda katta (maks 10MB)',
            'file_size' => $file['size'],
            'max_size' => $max_size
        ]);
        exit;
    }
    
    // Fayl nomini generatsiya qilish
    $extension = $allowed_types[$mime_type];
    $new_filename = uniqid('chat_') . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $new_filename;
    
    // Faylni ko'chirish
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Fayl ma'lumotlarini saqlash
        $sender_type = $user_role === 'admin' ? 'admin' : 'user';
        
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages 
            (chat_id, sender_id, sender_type, message, file_url, file_type, is_read, created_at) 
            VALUES (?, ?, ?, NULL, ?, ?, 0, NOW())
        ");
        $stmt->execute([$chat_id, $user_id, $sender_type, '/uploads/chat/' . $new_filename, $mime_type]);
        
        $message_id = $pdo->lastInsertId();
        
        // Fayl o'lchamini olish
        $file_size = filesize($destination);
        
        echo json_encode([
            'status' => 'success',
            'message_id' => (int)$message_id,
            'chat_id' => $chat_id,
            'file_id' => (int)$message_id,
            'file_url' => BASE_URL . '/uploads/chat/' . $new_filename,
            'file_type' => $mime_type,
            'file_size' => $file_size,
            'filename' => $new_filename
        ]);
        
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Faylni yuklashda xatolik yuz berdi'
        ]);
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Upload File Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server xatolik yuz berdi'
    ]);
}
