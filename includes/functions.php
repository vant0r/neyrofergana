<?php
/**
 * Umumiy yordamchi funksiyalar
 */

/**
 * Ma'lumotni xavfsiz chiqarish (XSS himoya)
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Random string generatsiya qilish
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Fayl nomini xavfsiz qilish
 */
function sanitizeFileName($filename) {
    $info = pathinfo($filename);
    $ext = strtolower($info['extension'] ?? '');
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];
    
    if (!in_array($ext, $allowed)) {
        return false;
    }
    
    return generateRandomString(16) . '.' . $ext;
}

/**
 * Telefon raqamini formatlash
 */
function formatPhone($phone) {
    // Faqat raqamlarni qoldirish
    $clean = preg_replace('/[^0-9]/', '', $phone);
    
    // O'zbekiston formati
    if (strlen($clean) === 9 && substr($clean, 0, 2) === '90') {
        return '+998' . $clean;
    } elseif (strlen($clean) === 12 && substr($clean, 0, 3) === '998') {
        return '+' . $clean;
    }
    
    return $phone;
}

/**
 * Sana formatlash
 */
function formatDate($date, $format = 'd.m.Y H:i') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Narx formatlash
 */
function formatPrice($price) {
    return number_format((float)$price, 0, '.', ' ') . ' so\'m';
}

/**
 * Vaqt oralig'ini hisoblash
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Hozirgina';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' daqiqa oldin';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' soat oldin';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' kun oldin';
    } else {
        return formatDate($datetime, 'd.m.Y');
    }
}

/**
 * Pagination yaratish
 */
function createPagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';
    
    $html = '<nav class="pagination"><ul>';
    
    // Oldingi sahifa
    if ($currentPage > 1) {
        $html .= '<li><a href="' . e($baseUrl . '?page=' . ($currentPage - 1)) . '">«</a></li>';
    }
    
    // Sahifalar
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="active">' . $i . '</li>';
        } else {
            $html .= '<li><a href="' . e($baseUrl . '?page=' . $i) . '">' . $i . '</a></li>';
        }
    }
    
    // Keyingi sahifa
    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . e($baseUrl . '?page=' . ($currentPage + 1)) . '">»</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Toast bildirishnoma chiqarish (session orqali)
 */
function setToast($message, $type = 'info') {
    $_SESSION['toast'] = [
        'message' => $message,
        'type' => $type // success, error, info, warning
    ];
}

function getToast() {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}

/**
 * Redirect qilish
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * JSON response qaytarish (API uchun)
 */
function jsonResponse($data, $status = 'success', $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'data' => $data
    ]);
    exit;
}

/**
 * Error response qaytarish (API uchun)
 */
function errorResponse($message, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

/**
 * Faylni o'chirish
 */
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

/**
 * Rasm o'lchamlarini olish
 */
function getImageDimensions($filePath) {
    if (file_exists($filePath)) {
        $info = getimagesize($filePath);
        if ($info) {
            return [
                'width' => $info[0],
                'height' => $info[1],
                'mime' => $info['mime']
            ];
        }
    }
    return false;
}

/**
 * Audit log yozish
 */
function auditLog($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        $pdo = getDBConnection();
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ipAddress
        ]);
    } catch (Exception $e) {
        error_log("Audit log error: " . $e->getMessage());
    }
}

/**
 * Yulduzcha reyting HTML i
 */
function renderStars($rating, $max = 5) {
    $html = '<div class="stars">';
    for ($i = 1; $i <= $max; $i++) {
        if ($i <= $rating) {
            $html .= '<span class="star filled">★</span>';
        } else {
            $html .= '<span class="star">★</span>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * Status badge HTML i
 */
function renderStatusBadge($status) {
    $statuses = [
        'pending' => ['text' => 'Kutilmoqda', 'class' => 'status-pending'],
        'confirmed' => ['text' => 'Tasdiqlandi', 'class' => 'status-confirmed'],
        'completed' => ['text' => 'Bajarildi', 'class' => 'status-completed'],
        'cancelled' => ['text' => 'Bekor qilindi', 'class' => 'status-cancelled'],
        'processing' => ['text' => 'Jarayonda', 'class' => 'status-processing'],
        'ready' => ['text' => 'Tayyor', 'class' => 'status-ready'],
        'draft' => ['text' => 'Qoralama', 'class' => 'status-draft'],
        'published' => ['text' => 'Chop etilgan', 'class' => 'status-published'],
    ];
    
    $config = $statuses[$status] ?? ['text' => $status, 'class' => ''];
    return '<span class="badge ' . e($config['class']) . '">' . e($config['text']) . '</span>';
}

/**
 * Xizmat kategoriyalarini olish
 */
function getServiceCategories() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT id, name FROM service_categories WHERE status = 'active' ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("getServiceCategories error: " . $e->getMessage());
        return [];
    }
}

/**
 * Barcha xizmatlarni olish
 */
function getAllServices() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("
            SELECT s.*, sc.name as category_name 
            FROM services s 
            LEFT JOIN service_categories sc ON s.category_id = sc.id 
            WHERE s.status = 'active' 
            ORDER BY s.sort_order ASC, s.created_at DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("getAllServices error: " . $e->getMessage());
        return [];
    }
}

/**
 * Ommabop xizmatlarni olish
 */
function getServices($limit = 6) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT s.*, sc.name as category_name 
            FROM services s 
            LEFT JOIN service_categories sc ON s.category_id = sc.id 
            WHERE s.status = 'active' AND s.is_popular = 1
            ORDER BY s.sort_order ASC, s.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("getServices error: " . $e->getMessage());
        return [];
    }
}

/**
 * Top shifokorlarni olish
 */
function getTopDoctors($limit = 4) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT id, full_name, specialty, experience_years, photo 
            FROM doctors 
            WHERE status = 'active' AND is_top = 1 
            ORDER BY sort_order ASC, created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("getTopDoctors error: " . $e->getMessage());
        return [];
    }
}

/**
 * Tasdiqlangan sharhlarni olish
 */
function getApprovedReviews($limit = 6) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT r.*, u.name as patient_name 
            FROM reviews r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE r.status = 'approved' 
            ORDER BY r.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("getApprovedReviews error: " . $e->getMessage());
        return [];
    }
}


// ============================================================================
// ADMIN PANEL FUNKSIYALARI
// ============================================================================

/**
 * Admin foydalanuvchini tekshirish
 */
function checkAdminAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header('Location: /kirish-royxatdan-otish.php');
        exit;
    }
    
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'superadmin') {
        header('Location: /user/boshqaruv.php');
        exit;
    }
    
    define('ACCESS_ALLOWED', true);
}

/**
 * O'qilmagan xabarlar sonini olish
 */
function getUnreadMessagesCount() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM chat_messages 
                           WHERE recipient_type = 'admin' AND is_read = 0");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

/**
 * Tasdiqlanmagan sharhlar sonini olish
 */
function getPendingReviewsCount() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE status = 'pending'");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

/**
 * Kutilayotgan navbatlar sonini olish
 */
function getPendingAppointmentsCount() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

/**
 * O'qilmagan bildirishnomalar sonini olish
 */
function getUnreadNotifications($type = 'all') {
    // Hozircha placeholder - kelajakda implement qilinadi
    return 0;
}

/**
 * Dashboard statistikasi
 */
function getDashboardStats() {
    global $pdo;
    
    $stats = [];
    
    // Jami bemorlar
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $stats['total_patients'] = (int) $stmt->fetchColumn();
    
    // Bugungi navbatlar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments 
                           WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $stats['today_appointments'] = (int) $stmt->fetchColumn();
    
    // Kunlik tushum
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(price), 0) FROM appointments 
                           WHERE DATE(created_at) = CURDATE() AND status = 'completed'");
    $stmt->execute();
    $stats['daily_revenue'] = (float) $stmt->fetchColumn();
    
    // O'rtacha navbat soni (haftalik)
    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments 
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $weeklyAppointments = (int) $stmt->fetchColumn();
    $stats['avg_appointments'] = round($weeklyAppointments / 7, 1);
    
    // Faol shifokorlar
    $stmt = $pdo->query("SELECT COUNT(*) FROM doctors WHERE status = 'active'");
    $stats['active_doctors'] = (int) $stmt->fetchColumn();
    
    // Faol xizmatlar
    $stmt = $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'active'");
    $stats['active_services'] = (int) $stmt->fetchColumn();
    
    return $stats;
}

/**
 * Oxirgi navbatlarni olish
 */
function getRecentAppointments($limit = 5) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT a.*, u.full_name as patient_name, u.phone as patient_phone,
                                  d.full_name as doctor_name, s.name as service_name
                           FROM appointments a
                           LEFT JOIN users u ON a.user_id = u.id
                           LEFT JOIN doctors d ON a.doctor_id = d.id
                           LEFT JOIN services s ON a.service_id = s.id
                           ORDER BY a.created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Oxirgi ro'yxatdan o'tgan bemorlarni olish
 */
function getRecentPatients($limit = 5) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, full_name, email, phone, created_at 
                           FROM users 
                           WHERE role = 'user' 
                           ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Xizmat kategoriyalarini olish
 */
function getServiceCategories() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT DISTINCT category FROM services 
                         WHERE category IS NOT NULL AND category != '' 
                         ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Barcha xizmatlarni olish
 */
function getAllServices($filters = []) {
    global $pdo;
    
    $sql = "SELECT * FROM services WHERE 1=1";
    $params = [];
    
    if (!empty($filters['category'])) {
        $sql .= " AND category = :category";
        $params[':category'] = $filters['category'];
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $filters['status'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (name LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT :limit";
        $params[':limit'] = (int) $filters['limit'];
    }
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        if ($key === ':limit') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Paginatsiya bilan xizmatlarni olish
 */
function getServices($page = 1, $perPage = 12, $filters = []) {
    global $pdo;
    
    $offset = ($page - 1) * $perPage;
    
    // Where shartlari
    $where = ['status = :status'];
    $params = [':status' => 'active'];
    
    if (!empty($filters['category'])) {
        $where[] = "category = :category";
        $params[':category'] = $filters['category'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(name LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Umumiy soni
    $countSql = "SELECT COUNT(*) FROM services WHERE $whereClause";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    // Ma'lumotlar
    $sql = "SELECT * FROM services WHERE $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => (int) $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total / $perPage)
    ];
}

/**
 * Top shifokorlarni olish
 */
function getTopDoctors($limit = 6) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM doctors 
                           WHERE status = 'active' 
                           ORDER BY experience_years DESC, created_at DESC 
                           LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Barcha shifokorlarni olish
 */
function getAllDoctors($filters = []) {
    global $pdo;
    
    $sql = "SELECT * FROM doctors WHERE 1=1";
    $params = [];
    
    if (!empty($filters['specialty'])) {
        $sql .= " AND specialty = :specialty";
        $params[':specialty'] = $filters['specialty'];
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $filters['status'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (full_name LIKE :search OR specialty LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Tasdiqlangan sharhlarni olish
 */
function getApprovedReviews($limit = 6) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT r.*, u.full_name as patient_name, u.avatar as patient_avatar,
                                  d.full_name as doctor_name
                           FROM reviews r
                           LEFT JOIN users u ON r.user_id = u.id
                           LEFT JOIN doctors d ON r.doctor_id = d.id
                           WHERE r.status = 'approved'
                           ORDER BY r.created_at DESC 
                           LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Logo URL ni olish
 */
function getLogoUrl($type = 'main') {
    $settings = getSettings();
    
    switch ($type) {
        case 'favicon':
            return $settings['favicon_url'] ?? '/uploads/favicon/default.png';
        case 'white':
            return $settings['logo_white_url'] ?? '/uploads/logo/white.png';
        case 'mobile':
            return $settings['logo_mobile_url'] ?? $settings['logo_url'];
        default:
            return $settings['logo_url'] ?? '/uploads/logo/main.png';
    }
}
