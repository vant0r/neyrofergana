<?php
/**
 * API: Chat xabarlarni olish (Polling)
 * Har 3-5 soniyada chaqiriladi, yangi xabarlarni qaytaradi
 */

header('Content-Type: application/json');
session_start();

// Konfiguratsiya va autentifikatsiya
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Faqat avtorizatsiya qilingan foydalanuvchilar uchun
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Avtorizatsiya talab etiladi']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'user';

// Parametrlarni olish
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;

try {
    $pdo = getDBConnection();
    
    // Agar chat_id berilmagan bo'lsa, foydalanuvchining faol chatini topish
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
    
    // Xabarlarni olish (last_id dan keyingi xabarlar)
    $stmt = $pdo->prepare("
        SELECT 
            cm.id,
            cm.chat_id,
            cm.sender_type,
            cm.message,
            cm.file_url,
            cm.file_type,
            cm.is_read,
            cm.created_at,
            u.first_name,
            u.last_name
        FROM chat_messages cm
        LEFT JOIN users u ON cm.sender_id = u.id
        WHERE cm.chat_id = ? AND cm.id > ?
        ORDER BY cm.created_at ASC
    ");
    $stmt->execute([$chat_id, $last_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Xabarlarni formatlash
    $formatted_messages = [];
    foreach ($messages as $msg) {
        $formatted_messages[] = [
            'id' => (int)$msg['id'],
            'sender' => $msg['sender_type'] === 'user' ? 'user' : 'admin',
            'sender_name' => $msg['first_name'] . ' ' . $msg['last_name'],
            'message' => htmlspecialchars($msg['message'] ?? ''),
            'file' => $msg['file_url'] ? BASE_URL . $msg['file_url'] : null,
            'file_type' => $msg['file_type'],
            'created_at' => $msg['created_at'],
            'is_read' => (bool)$msg['is_read']
        ];
    }
    
    // O'qilmagan xabarlarni o'qilgan deb belgilash (agar admin yuborgan bo'lsa)
    if (!empty($messages) && $user_role === 'user') {
        $message_ids = array_column($messages, 'id');
        $placeholders = implode(',', array_fill(0, count($message_ids), '?'));
        $update_stmt = $pdo->prepare("
            UPDATE chat_messages 
            SET is_read = 1 
            WHERE id IN ($placeholders) AND sender_type = 'admin'
        ");
        $update_stmt->execute($message_ids);
    }
    
    echo json_encode([
        'status' => 'success',
        'chat_id' => $chat_id,
        'messages' => $formatted_messages,
        'count' => count($formatted_messages)
    ]);
    
} catch (PDOException $e) {
    error_log("Chat Poll Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server xatolik yuz berdi'
    ]);
}
