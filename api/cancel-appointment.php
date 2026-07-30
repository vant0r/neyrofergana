<?php
/**
 * API: Navbatni bekor qilish
 * Bemor o'z navbatini bekor qilishi mumkin (24 soat oldin)
 */

header('Content-Type: application/json');
session_start();

// Konfiguratsiya va autentifikatsiya
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

// Faqat avtorizatsiya qilingan foydalanuvchilar uchun
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Avtorizatsiya talab etiladi']);
    exit;
}

$user_id = $_SESSION['user_id'];

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
    
    // Navbat ID olish
    $appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
    
    if ($appointment_id === 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Navbat ID talab etiladi']);
        exit;
    }
    
    // Navbat ma'lumotlarini olish va tekshirish
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.user_id,
            a.doctor_id,
            a.appointment_date,
            a.time_slot,
            a.status,
            d.first_name AS doctor_first_name,
            d.last_name AS doctor_last_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.id = ?
    ");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Navbat topilmadi']);
        exit;
    }
    
    // Foydalanuvchi huquqini tekshirish
    if ($appointment['user_id'] != $user_id && ($_SESSION['user_role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Bu navbatni bekor qilish huquqi yo\'q']);
        exit;
    }
    
    // Holatni tekshirish
    if ($appointment['status'] === 'cancelled') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Navbat allaqachon bekor qilingan']);
        exit;
    }
    
    if ($appointment['status'] === 'completed') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Bajarilgan navbatni bekor qilib bo\'lmaydi']);
        exit;
    }
    
    // 24 soat qoidasini tekshirish (faqat user uchun)
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        $appointment_datetime = strtotime($appointment['appointment_date'] . ' ' . $appointment['time_slot']);
        $now = time();
        $hours_until_appointment = ($appointment_datetime - $now) / 3600;
        
        if ($hours_until_appointment < 24) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Navbatni faqat 24 soat oldin bekor qilish mumkin',
                'hours_remaining' => round($hours_until_appointment, 1)
            ]);
            exit;
        }
    }
    
    // Navbatni bekor qilish
    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET status = 'cancelled', cancelled_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$appointment_id]);
    
    // Admin uchun bildirishnoma yuborish (ixtiyoriy)
    // Telegram yoki Email orqali
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Navbat muvaffaqiyatli bekor qilindi',
        'appointment_id' => $appointment_id,
        'refund_info' => 'Agar to\'lov amalga oshirilgan bo\'lsa, mablag\' 3-5 ish kuni ichida qaytariladi'
    ]);
    
} catch (PDOException $e) {
    error_log("Cancel Appointment Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server xatolik yuz berdi'
    ]);
}
