<?php
require_once 'includes/init.php';

$doctorId = (int)($_GET['id'] ?? 0);
if ($doctorId <= 0) {
    header('Location: shifokorlar.php');
    exit;
}

try {
    $pdo = getDB();
    
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = :id AND status = 'active'");
    $stmt->execute([':id' => $doctorId]);
    $doctor = $stmt->fetch();
    
    if (!$doctor) {
        header('Location: 404.php');
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE doctor_id = :id AND status = 'approved' ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([':id' => $doctorId]);
    $reviews = $stmt->fetchAll();
    
    $avgRating = $pdo->prepare("SELECT AVG(rating) as avg FROM reviews WHERE doctor_id = :id AND status = 'approved'");
    $avgRating->execute([':id' => $doctorId]);
    $ratingData = $avgRating->fetch();
    $averageRating = round($ratingData['avg'] ?? 0, 1);
    
    $scheduleDays = json_decode($doctor['schedule_days'] ?? '[]', true) ?: [];
    $scheduleTimes = json_decode($doctor['schedule_times'] ?? '[]', true) ?: [];
    
} catch (Exception $e) {
    error_log("Shifokor sahifasi xatolik: " . $e->getMessage());
    header('Location: 500.php');
    exit;
}

$siteSettings = getSiteSettings();
$pageTitle = $doctor['full_name'] . " - " . ($siteSettings['site_name'] ?? 'Klinika');
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1><?= htmlspecialchars($doctor['full_name']) ?></h1>
        <p><?= htmlspecialchars($doctor['specialization']) ?></p>
    </div>
</div>

<div class="container section">
    <div class="doctor-detail-grid">
        <div class="doctor-main-card glass-card">
            <div class="doctor-photo-large">
                <?php if ($doctor['photo']): ?>
                    <img src="<?= htmlspecialchars($doctor['photo']) ?>" alt="<?= htmlspecialchars($doctor['full_name']) ?>">
                <?php else: ?>
                    <div class="doctor-placeholder-large">👨‍⚕️</div>
                <?php endif; ?>
            </div>
            
            <div class="doctor-rating-block">
                <div class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?= $i <= round($averageRating) ? 'filled' : '' ?>">★</span>
                    <?php endfor; ?>
                </div>
                <span class="rating-value"><?= $averageRating ?>/5</span>
                <span class="rating-count">(<?= count($reviews) ?> sharh)</span>
            </div>
            
            <div class="doctor-stats">
                <div class="stat-item">
                    <span class="stat-label">Tajriba</span>
                    <span class="stat-value"><?= (int)$doctor['experience_years'] ?> yil</span>
                </div>
                <?php if ($doctor['room_number']): ?>
                <div class="stat-item">
                    <span class="stat-label">Xona</span>
                    <span class="stat-value"><?= htmlspecialchars($doctor['room_number']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($doctor['appointment_price']): ?>
                <div class="stat-item">
                    <span class="stat-label">Ko'rik narxi</span>
                    <span class="stat-value price"><?= number_format($doctor['appointment_price'], 0, '', ' ') ?> so'm</span>
                </div>
                <?php endif; ?>
            </div>
            
            <a href="user/navbat-olish.php?doctor_id=<?= $doctor['id'] ?>" class="btn btn-primary btn-large">Navbatga yozilish</a>
        </div>
        
        <div class="doctor-info-section">
            <div class="info-card glass-card">
                <h2>Batafsil ma'lumot</h2>
                <div class="doctor-bio">
                    <?= nl2br(htmlspecialchars($doctor['biography'] ?? 'Ma\'lumot mavjud emas.')) ?>
                </div>
            </div>
            
            <?php if (!empty($scheduleDays)): ?>
            <div class="info-card glass-card">
                <h2>Qabul kunlari va vaqtlari</h2>
                <div class="schedule-table">
                    <?php 
                    $dayNames = [
                        'monday' => 'Dushanba',
                        'tuesday' => 'Seshanba',
                        'wednesday' => 'Chorshanba',
                        'thursday' => 'Payshanba',
                        'friday' => 'Juma',
                        'saturday' => 'Shanba',
                        'sunday' => 'Yakshanba'
                    ];
                    foreach ($scheduleDays as $index => $day): 
                        $time = $scheduleTimes[$index] ?? '';
                    ?>
                        <div class="schedule-row">
                            <span class="schedule-day"><?= $dayNames[$day] ?? $day ?></span>
                            <span class="schedule-time"><?= htmlspecialchars($time) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($doctor['phone'] || $doctor['email']): ?>
            <div class="info-card glass-card">
                <h2>Aloqa</h2>
                <?php if ($doctor['phone']): ?>
                <div class="contact-item">
                    <span class="contact-icon">📞</span>
                    <a href="tel:<?= htmlspecialchars($doctor['phone']) ?>"><?= htmlspecialchars($doctor['phone']) ?></a>
                </div>
                <?php endif; ?>
                <?php if ($doctor['email']): ?>
                <div class="contact-item">
                    <span class="contact-icon">✉️</span>
                    <a href="mailto:<?= htmlspecialchars($doctor['email']) ?>"><?= htmlspecialchars($doctor['email']) ?></a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($reviews)): ?>
    <div class="reviews-section">
        <h2>Bemorlar fikri</h2>
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card glass-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <?php if ($review['user_avatar']): ?>
                                <img src="<?= htmlspecialchars($review['user_avatar']) ?>" alt="Avatar" class="reviewer-avatar">
                            <?php else: ?>
                                <div class="reviewer-avatar-placeholder">👤</div>
                            <?php endif; ?>
                            <div>
                                <h4><?= htmlspecialchars($review['user_name']) ?></h4>
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <span class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <p class="review-text"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.page-hero {
    background: linear-gradient(135deg, var(--primary-color), #5ac8fa);
    color: white;
    padding: clamp(60px, 10vh, 100px) 0;
    text-align: center;
}

.page-hero h1 {
    font-size: clamp(28px, 5vw, 42px);
    margin-bottom: 10px;
}

.page-hero p {
    font-size: clamp(16px, 3vw, 20px);
    opacity: 0.9;
}

.doctor-detail-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 30px;
    margin-top: 30px;
}

.doctor-main-card {
    padding: 30px;
    text-align: center;
}

.doctor-photo-large {
    height: 350px;
    margin-bottom: 20px;
    overflow: hidden;
    border-radius: 16px;
    background: linear-gradient(135deg, #f5f5f7, #e8e8ed);
}

.doctor-photo-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.doctor-placeholder-large {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 120px;
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
}

.doctor-rating-block {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
}

.rating-stars .star {
    font-size: 24px;
    color: #ddd;
}

.rating-stars .star.filled {
    color: #ffc107;
}

.rating-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
}

.rating-count {
    color: var(--text-secondary);
}

.doctor-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 25px;
    padding: 20px;
    background: rgba(0,113,227,0.05);
    border-radius: 12px;
}

.stat-item {
    text-align: center;
}

.stat-label {
    display: block;
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
}

.stat-value.price {
    color: var(--primary-color);
}

.btn-large {
    padding: 16px 32px;
    font-size: 18px;
    width: 100%;
}

.doctor-info-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-card {
    padding: 30px;
}

.info-card h2 {
    font-size: 22px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

.doctor-bio {
    line-height: 1.8;
    color: var(--text-secondary);
}

.schedule-table {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.schedule-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 16px;
    background: rgba(0,113,227,0.05);
    border-radius: 8px;
}

.schedule-day {
    font-weight: 600;
    color: var(--text-primary);
}

.schedule-time {
    color: var(--primary-color);
    font-weight: 500;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-icon {
    font-size: 20px;
}

.contact-item a {
    color: var(--text-primary);
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-item a:hover {
    color: var(--primary-color);
}

.reviews-section {
    margin-top: 50px;
}

.reviews-section h2 {
    font-size: 28px;
    margin-bottom: 25px;
    text-align: center;
}

.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.review-card {
    padding: 25px;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.reviewer-avatar,
.reviewer-avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.reviewer-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
}

.reviewer-info h4 {
    font-size: 16px;
    margin-bottom: 4px;
    color: var(--text-primary);
}

.review-stars .star {
    font-size: 16px;
    color: #ddd;
}

.review-stars .star.filled {
    color: #ffc107;
}

.review-date {
    font-size: 13px;
    color: var(--text-secondary);
}

.review-text {
    line-height: 1.6;
    color: var(--text-secondary);
}

@media (max-width: 900px) {
    .doctor-detail-grid {
        grid-template-columns: 1fr;
    }
    
    .doctor-main-card {
        max-width: 500px;
        margin: 0 auto;
    }
    
    .doctor-stats {
        grid-template-columns: 1fr;
    }
    
    .reviews-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
