<?php
/**
 * index.php - Bosh sahifa
 * Klinika haqida qisqacha ma'lumot, xizmatlar, shifokorlar, sharhlar
 */

require_once 'includes/init.php';
require_once 'includes/header.php';

// Ma'lumotlarni olish
$services = getServices(6); // Ommabop 6 ta xizmat
$doctors = getTopDoctors(4); // Top 4 shifokor
$reviews = getApprovedReviews(6); // Tasdiqlangan 6 ta sharh
$settings = getSettings();
$contacts = getContacts();
?>

<!-- Hero Banner -->
<section class="hero">
    <div class="hero-content">
        <h1 class="hero-title"><?= htmlspecialchars($settings['site_name'] ?? 'Klinika') ?></h1>
        <p class="hero-subtitle">Zamonaviy tibbiyot xizmatlari - sizning salomatligingiz bizning g'amxo'rligimizda</p>
        <div class="hero-buttons">
            <a href="/user/navbat-olish.php" class="btn btn-primary">📅 Navbatga Yozilish</a>
            <a href="/xizmatlar.php" class="btn btn-secondary">Xizmatlar</a>
        </div>
    </div>
</section>

<!-- Klinika Haqida Qisqacha -->
<section class="about-section">
    <div class="container">
        <h2 class="section-title">Nima uchun aynan biz?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🏥</div>
                <h3>Zamonaviy Uskunalar</h3>
                <p>Eng so'nggi tibbiy texnologiyalar va yuqori aniqlikdagi diagnostika</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👨‍⚕️</div>
                <h3>Tajribali Shifokorlar</h3>
                <p>O'z sohasining professional mutaxassislari va doimiy g'amxo'rlik</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⏰</div>
                <h3>Qulay Ish Vaqti</h3>
                <p>Dushanbadan Shanbagacha, sizga qulay bo'lgan vaqtda qabul</p>
            </div>
        </div>
    </div>
</section>

<!-- Ommabop Xizmatlar -->
<section class="services-section">
    <div class="container">
        <h2 class="section-title">Ommabop Xizmatlar</h2>
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
            <div class="service-card">
                <?php if (!empty($service['image'])): ?>
                <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="service-image">
                <?php endif; ?>
                <div class="service-content">
                    <h3><?= htmlspecialchars($service['name']) ?></h3>
                    <p class="service-description"><?= htmlspecialchars(mb_substr($service['description'], 0, 100)) ?>...</p>
                    <div class="service-footer">
                        <span class="service-price"><?= number_format($service['price'], 0, ',', ' ') ?> so'm</span>
                        <a href="/user/navbat-olish.php?service=<?= $service['id'] ?>" class="btn btn-sm">Navbatga</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="section-more">
            <a href="/xizmatlar.php" class="btn btn-outline">Barcha Xizmatlar</a>
        </div>
    </div>
</section>

<!-- Top Shifokorlar -->
<section class="doctors-section">
    <div class="container">
        <h2 class="section-title">Bizning Shifokorlar</h2>
        <div class="doctors-grid">
            <?php foreach ($doctors as $doctor): ?>
            <div class="doctor-card">
                <img src="<?= htmlspecialchars($doctor['photo'] ?? '/uploads/doctors/default.png') ?>" alt="<?= htmlspecialchars($doctor['full_name']) ?>" class="doctor-photo">
                <div class="doctor-info">
                    <h3><?= htmlspecialchars($doctor['full_name']) ?></h3>
                    <p class="doctor-specialty"><?= htmlspecialchars($doctor['specialty']) ?></p>
                    <p class="doctor-experience"><?= $doctor['experience_years'] ?> yil tajriba</p>
                    <div class="doctor-actions">
                        <a href="/shifokor.php?id=<?= $doctor['id'] ?>" class="btn btn-sm btn-outline">Batafsil</a>
                        <a href="/user/navbat-olish.php?doctor=<?= $doctor['id'] ?>" class="btn btn-sm">Qabulga</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="section-more">
            <a href="/shifokorlar.php" class="btn btn-outline">Barcha Shifokorlar</a>
        </div>
    </div>
</section>

<!-- Bemorlar Fikri -->
<section class="reviews-section">
    <div class="container">
        <h2 class="section-title">Bemorlar Fikri</h2>
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-avatar">
                        <?= strtoupper(mb_substr($review['patient_name'], 0, 1)) ?>
                    </div>
                    <div class="review-meta">
                        <h4><?= htmlspecialchars($review['patient_name']) ?></h4>
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <p class="review-text"><?= htmlspecialchars($review['comment']) ?></p>
                <p class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Aloqa Banner -->
<section class="contact-banner">
    <div class="container">
        <div class="contact-banner-content">
            <h2>Savollaringiz bormi?</h2>
            <p>Biz bilan bog'laning va biz sizga yordam beramiz</p>
            <div class="contact-banner-actions">
                <a href="/aloqa.php" class="btn btn-primary">Aloqa</a>
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contacts['phones'][0] ?? '') ?>" class="btn btn-secondary">📞 <?= htmlspecialchars($contacts['phones'][0] ?? '') ?></a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
