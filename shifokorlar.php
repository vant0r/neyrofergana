<?php
require_once 'includes/init.php';

$mutaxassislik = $_GET['mutaxassislik'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;

try {
    $pdo = getDB();
    
    $sql = "SELECT * FROM doctors WHERE status = 'active'";
    $params = [];
    
    if ($mutaxassislik) {
        $sql .= " AND specialization = :mutaxassislik";
        $params[':mutaxassislik'] = $mutaxassislik;
    }
    
    if ($search) {
        $sql .= " AND (full_name LIKE :search OR biography LIKE :search)";
        $params[':search'] = "%{$search}%";
    }
    
    $sql .= " ORDER BY experience_years DESC";
    
    $countSql = str_replace('*', 'COUNT(*) as cnt', $sql);
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch()['cnt'];
    
    $sql .= " LIMIT :offset, :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', ($page - 1) * $limit, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $doctors = $stmt->fetchAll();
    
    $categories = $pdo->query("SELECT DISTINCT specialization FROM doctors WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    error_log("Shifokorlar xatolik: " . $e->getMessage());
    $doctors = [];
    $categories = [];
    $total = 0;
}

$siteSettings = getSiteSettings();
$pageTitle = "Shifokorlar Jamoasi - " . ($siteSettings['site_name'] ?? 'Klinika');
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>Shifokorlar Jamoasi</h1>
        <p>Mutaxassis shifokorlarimiz bilan tanishing</p>
    </div>
</div>

<div class="container section">
    <div class="filters-bar glass-card">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="mutaxassislik">Mutaxassislik:</label>
                <select name="mutaxassislik" id="mutaxassislik" onchange="this.form.submit()">
                    <option value="">Barchasi</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $mutaxassislik === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="search">Qidiruv:</label>
                <input type="text" name="search" id="search" placeholder="Ism yoki mutaxassislik..." 
                       value="<?= htmlspecialchars($search) ?>" oninput="debounceSearch()">
            </div>
        </form>
    </div>
    
    <?php if (empty($doctors)): ?>
        <div class="empty-state glass-card">
            <div class="empty-icon">👨‍⚕️</div>
            <h3>Shifokorlar topilmadi</h3>
            <p>Qidiruv mezonlarini o'zgartirib ko'ring</p>
        </div>
    <?php else: ?>
        <div class="doctors-grid">
            <?php foreach ($doctors as $doctor): ?>
                <div class="doctor-card glass-card">
                    <div class="doctor-image">
                        <?php if ($doctor['photo']): ?>
                            <img src="<?= htmlspecialchars($doctor['photo']) ?>" alt="<?= htmlspecialchars($doctor['full_name']) ?>">
                        <?php else: ?>
                            <div class="doctor-placeholder">👨‍⚕️</div>
                        <?php endif; ?>
                    </div>
                    <div class="doctor-info">
                        <h3><?= htmlspecialchars($doctor['full_name']) ?></h3>
                        <p class="doctor-specialty"><?= htmlspecialchars($doctor['specialization']) ?></p>
                        <p class="doctor-experience">Tajriba: <?= (int)$doctor['experience_years'] ?> yil</p>
                        <?php if ($doctor['room_number']): ?>
                            <p class="doctor-room">Xona: <?= htmlspecialchars($doctor['room_number']) ?></p>
                        <?php endif; ?>
                        <div class="doctor-actions">
                            <a href="shifokor.php?id=<?= (int)$doctor['id'] ?>" class="btn btn-primary">Batafsil</a>
                            <a href="user/navbat-olish.php?doctor_id=<?= (int)$doctor['id'] ?>" class="btn btn-outline">Navbatga yozilish</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($total > $limit): ?>
            <div class="pagination">
                <?php
                $totalPages = ceil($total / $limit);
                if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-outline">← Oldingi</a>
                <?php endif; ?>
                
                <span class="page-info"><?= $page ?> / <?= $totalPages ?></span>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-outline">Keyingi →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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

.filters-bar {
    margin-bottom: 30px;
    padding: 20px;
}

.filters-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-primary);
}

.filter-group select,
.filter-group input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 12px;
    background: rgba(255,255,255,0.8);
    font-size: 16px;
    transition: all 0.3s ease;
}

.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0,113,227,0.1);
}

.doctors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}

.doctor-card {
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.doctor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.doctor-image {
    height: 250px;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f5f7, #e8e8ed);
}

.doctor-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.doctor-card:hover .doctor-image img {
    transform: scale(1.05);
}

.doctor-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 80px;
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
}

.doctor-info {
    padding: 20px;
}

.doctor-info h3 {
    font-size: 20px;
    margin-bottom: 8px;
    color: var(--text-primary);
}

.doctor-specialty {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 8px;
}

.doctor-experience,
.doctor-room {
    color: var(--text-secondary);
    font-size: 14px;
    margin-bottom: 4px;
}

.doctor-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 16px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .doctors-grid {
        grid-template-columns: 1fr;
    }
    
    .doctor-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let searchTimeout;
function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.querySelector('.filters-form').submit();
    }, 500);
}
</script>

<?php require_once 'includes/footer.php'; ?>
