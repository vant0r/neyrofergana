<?php
/**
 * API: Shifokorning bo'sh vaqtlarini olish
 * Tanlangan sana uchun mavjud time-slotlarni qaytaradi
 */

header('Content-Type: application/json');

// Konfiguratsiya
require_once __DIR__ . '/../includes/config.php';

// Faqat GET so'rovlar uchun
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Faqat GET so\'rovlari qabul qilinadi']);
    exit;
}

// Parametrlarni olish
$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Validatsiya
if ($doctor_id === 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Shifokor ID talab etiladi']);
    exit;
}

// Sana formatini tekshirish
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Noto\'g\'ri sana formati (YYYY-MM-DD kerak)']);
    exit;
}

// O'tgan sanalarni tekshirish
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'O\'tgan sanalar uchun navbat olish mumkin emas']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Shifokor ma'lumotlarini olish
    $stmt = $pdo->prepare("
        SELECT 
            id,
            first_name,
            last_name,
            specialization,
            schedule_json,
            is_active
        FROM doctors 
        WHERE id = ? AND is_active = 1
    ");
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Shifokor topilmadi yoki faol emas']);
        exit;
    }
    
    // Hafta kunini aniqlash (0 = Yakshanba, 1 = Dushanba, ...)
    $day_of_week = date('N', strtotime($date)); // 1 (Dushanba) - 7 (Yakshanba)
    
    // Shifokorning jadvalidan ushbu kun uchun vaqtlarni olish
    $schedule = json_decode($doctor['schedule_json'], true) ?? [];
    $day_key = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'][$day_of_week - 1];
    
    if (!isset($schedule[$day_key]) || empty($schedule[$day_key]['slots'])) {
        echo json_encode([
            'status' => 'success',
            'doctor_name' => $doctor['first_name'] . ' ' . $doctor['last_name'],
            'date' => $date,
            'slots' => [],
            'message' => 'Ushbu kun uchun qabul vaqtlari mavjud emas'
        ]);
        exit;
    }
    
    $all_slots = $schedule[$day_key]['slots'];
    
    // Band qilingan slotlarni olish
    $stmt = $pdo->prepare("
        SELECT time_slot 
        FROM appointments 
        WHERE doctor_id = ? 
        AND appointment_date = ? 
        AND status IN ('confirmed', 'pending', 'completed')
    ");
    $stmt->execute([$doctor_id, $date]);
    $booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Bo'sh slotlarni aniqlash
    $available_slots = array_diff($all_slots, $booked_slots);
    
    // Sortlash
    sort($available_slots);
    
    echo json_encode([
        'status' => 'success',
        'doctor_name' => $doctor['first_name'] . ' ' . $doctor['last_name'],
        'specialization' => $doctor['specialization'],
        'date' => $date,
        'day_of_week' => $day_key,
        'slots' => array_values($available_slots),
        'total_available' => count($available_slots),
        'total_booked' => count($booked_slots)
    ]);
    
} catch (PDOException $e) {
    error_log("Get Time Slots Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server xatolik yuz berdi'
    ]);
}
