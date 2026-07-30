<?php
/**
 * user/fikrlar.php - Bemor sharhlari qoldirish sahifasi
 */
require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

// Faqat ro'yxatdan o'tgan bemorlar kirishi mumkin
checkAuth();
checkRole('user');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Shifokorlar ro'yxatini olish (faqat bemor qabul qilgan)
try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT d.id, d.full_name, d.specialty 
        FROM doctors d
        INNER JOIN appointments a ON d.id = a.doctor_id
        WHERE a.user_id = ? AND a.status IN ('completed', 'confirmed')
        ORDER BY d.full_name ASC
    ");
    $stmt->execute([$user_id]);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $doctors = [];
}

// Sharh yuborish
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Xavfsizlik tokeni noto'g'ri.";
    } else {
        $doctor_id = (int)($_POST['doctor_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        if ($doctor_id <= 0) {
            $error = "Shifokor tanlanmagan.";
        } elseif ($rating < 1 || $rating > 5) {
            $error = "Reyting 1 dan 5 gacha bo'lishi kerak.";
        } elseif (empty($comment)) {
            $error = "Sharh matnini kiriting.";
        } else {
            try {
                // Shifokor bilan qabulda bo'lganligini tekshirish
                $stmt = $pdo->prepare("
                    SELECT id FROM appointments 
                    WHERE user_id = ? AND doctor_id = ? AND status IN ('completed', 'confirmed')
                    LIMIT 1
                ");
                $stmt->execute([$user_id, $doctor_id]);
                
                if ($stmt->rowCount() === 0) {
                    $error = "Siz ushbu shifokor qabulida bo'lmagansiz.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO reviews (user_id, doctor_id, rating, comment, status, created_at)
                        VALUES (?, ?, ?, ?, 'pending', NOW())
                    ");
                    $stmt->execute([$user_id, $doctor_id, $rating, $comment]);
                    $success = "Sharhingiz yuborildi. Moderatsiyadan so'ng ko'rinadi.";
                }
            } catch (PDOException $e) {
                error_log("Review error: " . $e->getMessage());
                $error = "Xatolik yuz berdi. Iltimos, qaytadan urinib ko'ring.";
            }
        }
    }
}

// Mavjud sharhlarni olish
try {
    $stmt = $pdo->prepare("
        SELECT r.*, d.full_name as doctor_name 
        FROM reviews r
        INNER JOIN doctors d ON r.doctor_id = d.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reviews = [];
}

$page_title = "Mening fikrlarim";
include '../includes/user-header.php';
?>

<div class="user-container">
    <div class="page-header">
        <h1>💬 Mening fikrlarim</h1>
        <p>Shifokorlar haqida o'z fikringizni qoldiring</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Sharh qoldirish formasi -->
    <div class="glass-card" style="margin-bottom: 30px;">
        <h2 style="margin-bottom: 20px;">Yangi fikr qoldirish</h2>
        <form method="POST" action="" class="review-form">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="form-group">
                <label for="doctor_id">Shifokorni tanlang *</label>
                <select id="doctor_id" name="doctor_id" required class="form-control">
                    <option value="">-- Shifokor tanlang --</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['full_name']) ?> - <?= htmlspecialchars($doc['specialty']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($doctors)): ?>
                    <p class="help-text" style="color: #ff3b30; margin-top: 10px;">
                        ⚠️ Siz hali hech qanday shifokor qabulida bo'lmagansiz. Qabuldan so'ng fikr qoldirishingiz mumkin.
                    </p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Reyting *</label>
                <div class="rating-stars" id="ratingStars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star" data-value="<?= $i ?>" onclick="setRating(<?= $i ?>)">★</span>
                    <?php endfor; ?>
                    <input type="hidden" name="rating" id="ratingInput" value="0" required>
                    <span id="ratingText" style="margin-left: 10px; color: #86868b;">Tanlanmagan</span>
                </div>
            </div>

            <div class="form-group">
                <label for="comment">Fikringiz *</label>
                <textarea id="comment" name="comment" rows="5" placeholder="Shifokor haqida o'z tajribangizni baham ko'ring..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary" <?= empty($doctors) ? 'disabled' : '' ?>>
                Fikrni yuborish
            </button>
        </form>
    </div>

    <!-- Mavjud sharhlar ro'yxati -->
    <div class="glass-card">
        <h2 style="margin-bottom: 20px;">Mening sharhlarim</h2>
        
        <?php if (empty($reviews)): ?>
            <p style="text-align: center; color: #86868b; padding: 40px 0;">
                Siz hali hech qanday sharh qoldirmagansiz.
            </p>
        <?php else: ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item" style="border-bottom: 1px solid rgba(0,0,0,0.1); padding: 20px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                            <div>
                                <strong style="font-size: 16px;"><?= htmlspecialchars($review['doctor_name']) ?></strong>
                                <div class="rating-display" style="color: #ffc107; margin-top: 5px;">
                                    <?php for ($i = 0; $i < $review['rating']; $i++): ?>★<?php endfor; ?>
                                    <?php for ($i = $review['rating']; $i < 5; $i++): ?>☆<?php endfor; ?>
                                </div>
                            </div>
                            <span class="badge badge-<?= $review['status'] === 'approved' ? 'success' : ($review['status'] === 'rejected' ? 'error' : 'warning') ?>">
                                <?= $review['status'] === 'approved' ? '✅ Tasdiqlangan' : ($review['status'] === 'rejected' ? '❌ Rad etilgan' : '⏳ Ko'rib chiqilmoqda') ?>
                            </span>
                        </div>
                        <p style="color: #1d1d1f; line-height: 1.6; margin-bottom: 10px;">
                            <?= nl2br(htmlspecialchars($review['comment'])) ?>
                        </p>
                        <small style="color: #86868b;">
                            📅 <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.review-form .form-group {
    margin-bottom: 20px;
}

.review-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #1d1d1f;
}

.review-form .form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 12px;
    background: rgba(255,255,255,0.5);
    font-size: 16px;
    transition: all 0.3s ease;
}

.review-form .form-control:focus {
    outline: none;
    border-color: #0071e3;
    background: rgba(255,255,255,0.8);
}

.rating-stars {
    display: flex;
    align-items: center;
    gap: 5px;
}

.rating-stars .star {
    font-size: 32px;
    color: #d1d1d1;
    cursor: pointer;
    transition: all 0.2s ease;
}

.rating-stars .star.active,
.rating-stars .star:hover {
    color: #ffc107;
    transform: scale(1.1);
}

.review-item {
    transition: all 0.3s ease;
}

.review-item:hover {
    background: rgba(255,255,255,0.3);
    border-radius: 12px;
    padding: 20px !important;
    margin: 0 -20px;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: rgba(52, 199, 89, 0.15);
    color: #34c759;
}

.badge-warning {
    background: rgba(255, 193, 7, 0.15);
    color: #ff9500;
}

.badge-error {
    background: rgba(255, 59, 48, 0.15);
    color: #ff3b30;
}

@media (max-width: 768px) {
    .rating-stars .star {
        font-size: 28px;
    }
}
</style>

<script>
function setRating(value) {
    document.getElementById('ratingInput').value = value;
    document.getElementById('ratingText').textContent = getValueText(value);
    
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < value) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

function getValueText(value) {
    const texts = ['', 'Juda yomon', 'Yomon', 'O\'rtacha', 'Yaxshi', 'A\'lo'];
    return texts[value] || '';
}

// Formani yuborishda validatsiya
document.querySelector('.review-form').addEventListener('submit', function(e) {
    const rating = document.getElementById('ratingInput').value;
    if (rating == 0) {
        e.preventDefault();
        showToast('Iltimos, reytingni tanlang!', 'error');
    }
});
</script>

<?php include '../includes/footer.php'; ?>
