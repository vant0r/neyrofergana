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
