<?php
/**
 * xizmatlar.php - Xizmatlar va Narxlar
 */

require_once 'includes/init.php';
require_once 'includes/header.php';

// Kategoriyalarni olish
$categories = getServiceCategories();
$services = getAllServices();

// Filter parametrlari
$categoryFilter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$searchQuery = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">Xizmatlar va Narxlar</h1>
        <p class="page-subtitle">Bizning barcha tibbiy xizmatlarimiz bilan tanishing</p>
    </div>
</section>

<!-- Filter Section -->
<section class="filter-section">
    <div class="container">
        <div class="filter-container">
            <!-- Kategoriya filteri -->
            <div class="filter-group">
                <label>Kategoriya:</label>
                <select id="categoryFilter" onchange="applyFilters()">
                    <option value="">Barchasi</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Qidiruv -->
            <div class="filter-group">
                <label>Qidiruv:</label>
                <input type="text" id="searchInput" placeholder="Xizmat nomini kiriting..." 
                       value="<?= htmlspecialchars($searchQuery) ?>" onkeyup="applyFilters()">
            </div>
        </div>
    </div>
</section>

<!-- Services Grid -->
<section class="services-section">
    <div class="container">
        <div class="services-grid" id="servicesGrid">
            <?php 
            $filteredServices = $services;
            
            // Kategoriya bo'yicha filter
            if ($categoryFilter) {
                $filteredServices = array_filter($filteredServices, function($s) use ($categoryFilter) {
                    return $s['category_id'] == $categoryFilter;
                });
            }
            
            // Qidiruv bo'yicha filter
            if ($searchQuery) {
                $filteredServices = array_filter($filteredServices, function($s) use ($searchQuery) {
                    return stripos($s['name'], $searchQuery) !== false || 
                           stripos($s['description'], $searchQuery) !== false;
                });
            }
            
            if (empty($filteredServices)): 
            ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                <p style="color: #86868b; font-size: 18px;">Hech qanday xizmat topilmadi</p>
            </div>
            <?php else: ?>
                <?php foreach ($filteredServices as $service): ?>
                <div class="service-card" data-category="<?= $service['category_id'] ?>" data-name="<?= htmlspecialchars(strtolower($service['name'])) ?>">
                    <?php if (!empty($service['image'])): ?>
                    <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="service-image">
                    <?php else: ?>
                    <div class="service-image-placeholder">🏥</div>
                    <?php endif; ?>
                    
                    <div class="service-content">
                        <span class="service-category"><?= htmlspecialchars($service['category_name'] ?? 'Boshqa') ?></span>
                        <h3><?= htmlspecialchars($service['name']) ?></h3>
                        <p class="service-description"><?= htmlspecialchars(mb_substr($service['description'], 0, 120)) ?><?= strlen($service['description']) > 120 ? '...' : '' ?></p>
                        
                        <div class="service-details">
                            <div class="service-detail">
                                <span class="detail-icon">⏱️</span>
                                <span><?= $service['duration_minutes'] ?> daqiqa</span>
                            </div>
                        </div>
                        
                        <div class="service-footer">
                            <span class="service-price"><?= number_format($service['price'], 0, ',', ' ') ?> so'm</span>
                            <a href="/user/navbat-olish.php?service=<?= $service['id'] ?>" class="btn btn-sm">Navbatga</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($filteredServices)): ?>
        <div class="services-count">
            Jami: <strong><?= count($filteredServices) ?></strong> ta xizmat
        </div>
        <?php endif; ?>
    </div>
</section>

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

.filter-section {
    padding: 40px 20px;
    background: #f5f5f7;
}

.filter-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.filter-group label {
    display: block;
    color: #1d1d1f;
    font-weight: 500;
    margin-bottom: 8px;
}

.filter-group select,
.filter-group input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #d2d2d7;
    border-radius: 12px;
    font-size: 16px;
    background: white;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #0071e3;
    box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
}

.services-section {
    padding: 60px 20px;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
}

.service-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.service-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.service-image-placeholder {
    width: 100%;
    height: 200px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
}

.service-content {
    padding: 20px;
}

.service-category {
    display: inline-block;
    background: rgba(0, 113, 227, 0.1);
    color: #0071e3;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 10px;
}

.service-content h3 {
    font-size: 20px;
    color: #1d1d1f;
    margin-bottom: 10px;
}

.service-description {
    color: #515154;
    line-height: 1.6;
    margin-bottom: 15px;
    font-size: 14px;
}

.service-details {
    margin-bottom: 15px;
}

.service-detail {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #86868b;
    font-size: 14px;
}

.detail-icon {
    font-size: 16px;
}

.service-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.service-price {
    font-size: 20px;
    font-weight: 600;
    color: #0071e3;
}

.services-count {
    text-align: center;
    margin-top: 40px;
    color: #86868b;
    font-size: 16px;
}

@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function applyFilters() {
    const category = document.getElementById('categoryFilter').value;
    const search = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.service-card');
    
    cards.forEach(card => {
        const cardCategory = card.dataset.category;
        const cardName = card.dataset.name;
        
        const matchCategory = !category || cardCategory === category;
        const matchSearch = !search || cardName.includes(search);
        
        if (matchCategory && matchSearch) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // URL ni yangilash
    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    
    window.history.pushState({}, '', url);
}
</script>

<?php require_once 'includes/footer.php'; ?>
