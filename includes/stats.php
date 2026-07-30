<?php
/**
 * Statistika hisoblash funksiyalari
 * Admin dashboard uchun widget ma'lumotlari
 */

/**
 * Umumiy statistika ma'lumotlarini olish
 * 
 * @param PDO $db - Ma'lumotlar bazasi ulanishi
 * @return array - Statistika ma'lumotlari
 */
function getDashboardStats($db) {
    $stats = [];
    
    try {
        // Jami bemorlar soni
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'patient'");
        $stats['total_patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Bugungi navbatlar soni
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM appointments WHERE DATE(appointment_date) = ?");
        $stmt->execute([$today]);
        $stats['today_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ertaga bo'ladigan navbatlar
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM appointments WHERE DATE(appointment_date) = ?");
        $stmt->execute([$tomorrow]);
        $stats['tomorrow_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Kutilayotgan navbatlar (tasdiqlanmagan)
        $stmt = $db->query("SELECT COUNT(*) as total FROM appointments WHERE status = 'pending'");
        $stats['pending_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Tasdiqlangan navbatlar
        $stmt = $db->query("SELECT COUNT(*) as total FROM appointments WHERE status = 'confirmed'");
        $stats['confirmed_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Jami shifokorlar soni
        $stmt = $db->query("SELECT COUNT(*) as total FROM doctors WHERE status = 'active'");
        $stats['total_doctors'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Jami xizmatlar soni
        $stmt = $db->query("SELECT COUNT(*) as total FROM services WHERE status = 'active'");
        $stats['total_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // O'qilmagan chat xabarlar soni
        $stmt = $db->query("SELECT COUNT(*) as total FROM chat_messages WHERE is_read = 0 AND receiver_id IN (SELECT id FROM users WHERE role = 'admin')");
        $stats['unread_messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Yangi sharhlar (tasdiqlanmagan)
        $stmt = $db->query("SELECT COUNT(*) as total FROM reviews WHERE status = 'pending'");
        $stats['pending_reviews'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Tayyor test natijalari (o'qilmagan)
        $stmt = $db->query("SELECT COUNT(*) as total FROM test_results WHERE status = 'ready' AND is_viewed = 0");
        $stats['ready_tests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Kunlik tushum (bugun to'langan)
        $stmt = $db->prepare("SELECT SUM(amount) as total FROM payments WHERE DATE(paid_at) = ? AND status = 'completed'");
        $stmt->execute([$today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['daily_revenue'] = $result['total'] ?? 0;
        
        // Haftalik tushum
        $week_ago = date('Y-m-d', strtotime('-7 days'));
        $stmt = $db->prepare("SELECT SUM(amount) as total FROM payments WHERE DATE(paid_at) >= ? AND status = 'completed'");
        $stmt->execute([$week_ago]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['weekly_revenue'] = $result['total'] ?? 0;
        
        // Oylik tushum
        $month_ago = date('Y-m-d', strtotime('-30 days'));
        $stmt = $db->prepare("SELECT SUM(amount) as total FROM payments WHERE DATE(paid_at) >= ? AND status = 'completed'");
        $stmt->execute([$month_ago]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthly_revenue'] = $result['total'] ?? 0;
        
        // Oxirgi ro'yxatdan o'tgan bemorlar (5 ta)
        $stmt = $db->query("SELECT id, name, email, phone, created_at FROM users WHERE role = 'patient' ORDER BY created_at DESC LIMIT 5");
        $stats['recent_patients'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Yaqinlashayotgan navbatlar (5 ta)
        $stmt = $db->query("
            SELECT a.id, a.appointment_date, a.start_time, a.status,
                   u.name as patient_name, u.phone as patient_phone,
                   d.full_name as doctor_name, s.name as service_name
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN services s ON a.service_id = s.id
            WHERE a.appointment_date >= CURDATE()
            ORDER BY a.appointment_date ASC, a.start_time ASC
            LIMIT 5
        ");
        $stats['upcoming_appointments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Shifokorlar bo'yicha navbat yuklamasi
        $stmt = $db->query("
            SELECT d.id, d.full_name, COUNT(a.id) as appointment_count
            FROM doctors d
            LEFT JOIN appointments a ON d.id = a.doctor_id AND DATE(a.appointment_date) = CURDATE()
            GROUP BY d.id, d.full_name
            ORDER BY appointment_count DESC
            LIMIT 5
        ");
        $stats['doctor_workload'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Xizmatlar bo'yicha ommabopligi
        $stmt = $db->query("
            SELECT s.id, s.name, COUNT(a.id) as booking_count
            FROM services s
            LEFT JOIN appointments a ON s.id = a.service_id
            GROUP BY s.id, s.name
            ORDER BY booking_count DESC
            LIMIT 5
        ");
        $stats['popular_services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Har oylik tushum dinamikasi (oxirgi 6 oy)
        $stmt = $db->query("
            SELECT 
                DATE_FORMAT(paid_at, '%Y-%m') as month,
                SUM(amount) as total_amount,
                COUNT(*) as payment_count
            FROM payments
            WHERE status = 'completed' AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
            ORDER BY month ASC
        ");
        $stats['monthly_revenue_chart'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Har oylik navbatlar soni (oxirgi 6 oy)
        $stmt = $db->query("
            SELECT 
                DATE_FORMAT(appointment_date, '%Y-%m') as month,
                COUNT(*) as appointment_count
            FROM appointments
            WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(appointment_date, '%Y-%m')
            ORDER BY month ASC
        ");
        $stats['monthly_appointments_chart'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Statistika xatosi: " . $e->getMessage());
        $stats = [
            'error' => true,
            'message' => 'Statistika ma\'lumotlarini olishda xatolik yuz berdi'
        ];
    }
    
    return $stats;
}

/**
 * Bemorlar statistikasi
 * 
 * @param PDO $db
 * @param string $period - 'day', 'week', 'month', 'year'
 * @return array
 */
function getPatientStats($db, $period = 'month') {
    $interval = match($period) {
        'day' => '1 DAY',
        'week' => '7 DAY',
        'month' => '30 DAY',
        'year' => '1 YEAR',
        default => '30 DAY'
    };
    
    $stmt = $db->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as new_patients
        FROM users
        WHERE role = 'patient' AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Navbatlar statistikasi holat bo'yicha
 * 
 * @param PDO $db
 * @return array
 */
function getAppointmentStatusStats($db) {
    $stmt = $db->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM appointments
        GROUP BY status
    ");
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stats = [];
    
    foreach ($result as $row) {
        $stats[$row['status']] = (int)$row['count'];
    }
    
    return $stats;
}

/**
 * Shifokor reytingi va statistikasi
 * 
 * @param PDO $db
 * @param int|null $limit
 * @return array
 */
function getDoctorRankings($db, $limit = 10) {
    $stmt = $db->prepare("
        SELECT 
            d.id,
            d.full_name,
            d.specialty,
            COUNT(DISTINCT a.id) as total_appointments,
            COUNT(DISTINCT r.id) as total_reviews,
            AVG(r.rating) as average_rating,
            SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) as five_star_count,
            SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) as four_star_count,
            SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) as three_star_count,
            SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) as two_star_count,
            SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) as one_star_count
        FROM doctors d
        LEFT JOIN appointments a ON d.id = a.doctor_id
        LEFT JOIN reviews r ON d.id = r.doctor_id
        WHERE d.status = 'active'
        GROUP BY d.id, d.full_name, d.specialty
        HAVING total_reviews > 0
        ORDER BY average_rating DESC, total_appointments DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Xizmatlar statistikasi
 * 
 * @param PDO $db
 * @param string|null $category
 * @return array
 */
function getServiceStats($db, $category = null) {
    if ($category) {
        $stmt = $db->prepare("
            SELECT 
                s.id,
                s.name,
                s.category,
                s.price,
                COUNT(a.id) as total_bookings,
                SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
            FROM services s
            LEFT JOIN appointments a ON s.id = a.service_id
            WHERE s.category = ?
            GROUP BY s.id, s.name, s.category, s.price
            ORDER BY total_bookings DESC
        ");
        $stmt->execute([$category]);
    } else {
        $stmt = $db->query("
            SELECT 
                s.id,
                s.name,
                s.category,
                s.price,
                COUNT(a.id) as total_bookings,
                SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
            FROM services s
            LEFT JOIN appointments a ON s.id = a.service_id
            GROUP BY s.id, s.name, s.category, s.price
            ORDER BY total_bookings DESC
        ");
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Widget uchun tez statistika
 * 
 * @param PDO $db
 * @return array
 */
function getQuickStats($db) {
    $stats = getDashboardStats($db);
    
    return [
        'patients' => $stats['total_patients'] ?? 0,
        'appointments_today' => $stats['today_appointments'] ?? 0,
        'pending' => $stats['pending_appointments'] ?? 0,
        'revenue_today' => $stats['daily_revenue'] ?? 0,
        'unread_messages' => $stats['unread_messages'] ?? 0,
        'ready_tests' => $stats['ready_tests'] ?? 0
    ];
}

/**
 * Statistika CSS stillari
 */
function getStatsCSS() {
    return '
/* Statistika widgetlari */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 24px;
}

.stat-icon.blue { background: rgba(0, 113, 227, 0.1); color: #0071e3; }
.stat-icon.green { background: rgba(52, 199, 89, 0.1); color: #34c759; }
.stat-icon.orange { background: rgba(255, 149, 0, 0.1); color: #ff9500; }
.stat-icon.red { background: rgba(255, 59, 48, 0.1); color: #ff3b30; }
.stat-icon.purple { background: rgba(175, 82, 222, 0.1); color: #af52de; }

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1d1d1f;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.9rem;
    color: #86868b;
    font-weight: 500;
}

.stat-change {
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.stat-change.positive { color: #34c759; }
.stat-change.negative { color: #ff3b30; }

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
}
';
}
