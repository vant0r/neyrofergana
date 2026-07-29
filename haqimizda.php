<?php
/**
 * haqimizda.php - Klinika haqida sahifa
 */

require_once 'includes/init.php';
require_once 'includes/header.php';

$settings = getSettings();
$contacts = getContacts();
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">Klinika Haqida</h1>
        <p class="page-subtitle">Bizning tariximiz, missiyamiz va qadriyatlarimiz</p>
    </div>
</section>

<!-- Main Content -->
<section class="about-content">
    <div class="container">
        <!-- Tarix va Missiya -->
        <div class="about-block">
            <div class="about-text">
                <h2>Bizning Tariximiz</h2>
                <p><?= htmlspecialchars($settings['site_name'] ?? 'Klinika') ?> <?= date('Y') - (int)($settings['opened_year'] ?? date('Y')) ?> yildan beri bemorlarga xizmat ko'rsatib kelmoqda. Bizning maqsadimiz - zamonaviy tibbiyot yutuqlaridan foydalangan holda aholiga sifatli tibbiy yordam ko'rsatish.</p>
                <p>Yillar davomida biz minglab bemorlarning ishonchini qozondik va doimiy ravishda xizmatlarimizni kengaytirib, shifokorlarimiz malakasini oshirib bordik.</p>
            </div>
            <div class="about-image">
                <img src="/uploads/banners/about-clinic.jpg" alt="Klinika binosi" onerror="this.src='https://via.placeholder.com/600x400?text=Klinika'">
            </div>
        </div>

        <!-- Missiya -->
        <div class="about-block reverse">
            <div class="about-text">
                <h2>Bizning Missiyamiz</h2>
                <ul class="mission-list">
                    <li>✅ Har bir bemorga individual yondashuv</li>
                    <li>✅ Zamonaviy diagnostika va davolash usullari</li>
                    <li>✅ Tajribali mutaxassislar jamoasi</li>
                    <li>✅ Qulay sharoit va g'amxo'rlik</li>
                    <li>✅ Hamyonbop narxlar va sifatli xizmat</li>
                </ul>
            </div>
            <div class="about-image">
                <img src="/uploads/banners/mission.jpg" alt="Missiya" onerror="this.src='https://via.placeholder.com/600x400?text=Missiya'">
            </div>
        </div>
    </div>
</section>

<!-- Statistik -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= date('Y') - (int)($settings['opened_year'] ?? date('Y')) ?>+</div>
                <div class="stat-label">Yillik Tajriba</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">5000+</div>
                <div class="stat-label">Bemorlar Soni</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">20+</div>
                <div class="stat-label">Shifokorlar</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">100%</div>
                <div class="stat-label">Sifat Kafolati</div>
            </div>
        </div>
    </div>
</section>

<!-- Sertifikatlar -->
<section class="certificates-section">
    <div class="container">
        <h2 class="section-title">Sertifikat va Litsenziyalar</h2>
        <div class="certificates-grid">
            <div class="certificate-item" onclick="openLightbox(this)">
                <img src="/uploads/certificates/cert1.jpg" alt="Sertifikat 1" onerror="this.src='https://via.placeholder.com/300x400?text=Sertifikat+1'">
            </div>
            <div class="certificate-item" onclick="openLightbox(this)">
                <img src="/uploads/certificates/cert2.jpg" alt="Sertifikat 2" onerror="this.src='https://via.placeholder.com/300x400?text=Sertifikat+2'">
            </div>
            <div class="certificate-item" onclick="openLightbox(this)">
                <img src="/uploads/certificates/cert3.jpg" alt="Sertifikat 3" onerror="this.src='https://via.placeholder.com/300x400?text=Sertifikat+3'">
            </div>
            <div class="certificate-item" onclick="openLightbox(this)">
                <img src="/uploads/certificates/license.jpg" alt="Litsenziya" onerror="this.src='https://via.placeholder.com/300x400?text=Litsenziya'">
            </div>
        </div>
    </div>
</section>

<!-- Galereya Havola -->
<section class="gallery-preview">
    <div class="container">
        <h2 class="section-title">Fotogalereya</h2>
        <p style="text-align: center; color: #86868b; margin-bottom: 30px;">Klinikamizning ichki muhiti va zamonaviy uskunalari bilan tanishing</p>
        <div style="text-align: center;">
            <a href="/galereya.php" class="btn btn-primary">Galereyani Ko'rish</a>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <img id="lightbox-img" src="" alt="">
</div>

<style>
.page-header {
    background: linear-gradient(135deg, rgba(0, 113, 227, 0.9), rgba(118, 75, 162, 0.9));
    padding: 80px 20px;
    text-align: center;
    color: white;
}

.page-title {
    font-size: clamp(28px, 5vw, 42px);
    font-weight: 600;
    margin-bottom: 10px;
}

.page-subtitle {
    font-size: clamp(16px, 3vw, 18px);
    opacity: 0.9;
}

.about-content {
    padding: 60px 20px;
}

.about-block {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 60px;
    align-items: center;
}

.about-block.reverse {
    direction: rtl;
}

.about-block.reverse > * {
    direction: ltr;
}

.about-text h2 {
    font-size: 28px;
    color: #1d1d1f;
    margin-bottom: 20px;
}

.about-text p {
    color: #515154;
    line-height: 1.8;
    margin-bottom: 15px;
}

.mission-list {
    list-style: none;
    padding: 0;
}

.mission-list li {
    padding: 10px 0;
    color: #515154;
    font-size: 16px;
}

.about-image img {
    width: 100%;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.stats-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 60px 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 30px;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.stat-number {
    font-size: 42px;
    font-weight: 700;
    color: white;
    margin-bottom: 10px;
}

.stat-label {
    color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
}

.certificates-section {
    padding: 60px 20px;
}

.certificates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.certificate-item {
    cursor: pointer;
    border-radius: 16px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.certificate-item:hover {
    transform: scale(1.05);
}

.certificate-item img {
    width: 100%;
    height: 350px;
    object-fit: cover;
}

.gallery-preview {
    padding: 60px 20px;
    background: #f5f5f7;
}

.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.lightbox.active {
    display: flex;
}

.lightbox img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 8px;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 40px;
    color: white;
    cursor: pointer;
}

@media (max-width: 768px) {
    .about-block,
    .about-block.reverse {
        grid-template-columns: 1fr;
        direction: ltr;
    }
    
    .stat-number {
        font-size: 32px;
    }
}
</style>

<script>
function openLightbox(element) {
    const img = element.querySelector('img');
    document.getElementById('lightbox-img').src = img.src;
    document.getElementById('lightbox').classList.add('active');
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
}
</script>

<?php require_once 'includes/footer.php'; ?>
