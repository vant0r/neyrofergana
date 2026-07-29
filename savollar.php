<?php
require_once 'includes/init.php';

try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM faq ORDER BY category, sort_order ASC");
    $faqs = $stmt->fetchAll();
    
    $categories = [];
    foreach ($faqs as $faq) {
        if (!isset($categories[$faq['category']])) {
            $categories[$faq['category']] = [];
        }
        $categories[$faq['category']][] = $faq;
    }
} catch (Exception $e) {
    error_log("FAQ xatolik: " . $e->getMessage());
    $categories = [];
}

$siteSettings = getSiteSettings();
$pageTitle = "Ko'p beriladigan savollar - " . ($siteSettings['site_name'] ?? 'Klinika');
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>Ko'p beriladigan savollar</h1>
        <p>Sizni qiziqtirgan savollarga javoblar</p>
    </div>
</div>

<div class="container section">
    <?php if (empty($categories)): ?>
        <div class="empty-state glass-card">
            <div class="empty-icon">❓</div>
            <h3>Savollar hozircha qo'shilmagan</h3>
            <p>Tez orada ma'lumotlar qo'shiladi</p>
        </div>
    <?php else: ?>
        <div class="faq-container">
            <?php foreach ($categories as $category => $items): ?>
                <div class="faq-category">
                    <h2 class="category-title"><?= htmlspecialchars($category) ?></h2>
                    <div class="faq-list">
                        <?php foreach ($items as $index => $faq): ?>
                            <div class="faq-item glass-card">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h3><?= htmlspecialchars($faq['question']) ?></h3>
                                    <span class="faq-icon">+</span>
                                </div>
                                <div class="faq-answer">
                                    <p><?= nl2br(htmlspecialchars($faq['answer'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="contact-cta glass-card">
        <h2>Boshqa savollaringiz bormi?</h2>
        <p>Biz bilan bog'laning va biz sizga yordam beramiz</p>
        <a href="aloqa.php" class="btn btn-primary">Aloqa sahifasi</a>
    </div>
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

.faq-container {
    max-width: 900px;
    margin: 0 auto;
}

.faq-category {
    margin-bottom: 40px;
}

.category-title {
    font-size: 24px;
    color: var(--text-primary);
    margin-bottom: 20px;
    padding-left: 10px;
    border-left: 4px solid var(--primary-color);
}

.faq-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.faq-item {
    overflow: hidden;
    transition: all 0.3s ease;
}

.faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    cursor: pointer;
    user-select: none;
}

.faq-question h3 {
    font-size: 17px;
    font-weight: 500;
    color: var(--text-primary);
    margin: 0;
    padding-right: 20px;
}

.faq-icon {
    font-size: 24px;
    color: var(--primary-color);
    font-weight: 300;
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.faq-item.active .faq-icon {
    transform: rotate(45deg);
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.faq-item.active .faq-answer {
    max-height: 500px;
}

.faq-answer p {
    padding: 0 25px 20px;
    line-height: 1.8;
    color: var(--text-secondary);
    margin: 0;
}

.contact-cta {
    max-width: 600px;
    margin: 50px auto 0;
    padding: 40px;
    text-align: center;
}

.contact-cta h2 {
    font-size: 24px;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.contact-cta p {
    color: var(--text-secondary);
    margin-bottom: 25px;
}

@media (max-width: 768px) {
    .faq-question {
        padding: 15px 20px;
    }
    
    .faq-question h3 {
        font-size: 16px;
    }
    
    .faq-answer p {
        padding: 0 20px 15px;
    }
    
    .contact-cta {
        padding: 30px 20px;
    }
}
</style>

<script>
function toggleFaq(element) {
    const faqItem = element.closest('.faq-item');
    const isActive = faqItem.classList.contains('active');
    
    document.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
    });
    
    if (!isActive) {
        faqItem.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const urlHash = window.location.hash;
    if (urlHash) {
        const targetFaq = document.querySelector(urlHash);
        if (targetFaq) {
            targetFaq.classList.add('active');
            targetFaq.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
