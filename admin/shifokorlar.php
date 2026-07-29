<?php
// admin/shifokorlar.php - Shifokorlar jamoasi CRUD
require_once '../includes/init.php';
require_once '../includes/auth.php';

checkAdminAuth();

$success = '';
$error = '';
$db = getDB();

// Shifokorni o'chirish
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        $error = "Xavfsizlik xatosi";
    } else {
        $stmt = $db->prepare("DELETE FROM doctors WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = "Shifokor o'chirildi";
        } else {
            $error = "O'chirishda xatolik";
        }
    }
}

// Filter va qidiruv
$specialtyFilter = $_GET['specialty'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Mutaxassisliklar
$specialties = ['Terapevt', 'Xirurg', 'Kardiolog', 'Nevrolog', 'Dermatolog', 'Ginekolog', 'Urolog', 'Oftalmolog', 'ENT', 'Travmatolog', 'Endokrinolog', 'Gastroenterolog', 'Stomatolog', 'Pediatr', 'Onkolog', 'Boshqa'];

// Shifokorlarni olish
$where = [];
$params = [];

if ($specialtyFilter) {
    $where[] = "specialty = ?";
    $params[] = $specialtyFilter;
}

if ($search) {
    $where[] = "(full_name LIKE ? OR specialty LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $db->query("SELECT COUNT(*) FROM doctors $whereSQL", $params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $db->query("SELECT * FROM doctors $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>👨‍⚕️ Shifokorlar</h1>
        <p>Shifokorlar jamoasini boshqaring</p>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Qidiruv va Filter -->
    <div class="filter-bar glass-card">
        <form method="GET" class="filter-form">
            <select name="specialty" onchange="this.form.submit()">
                <option value="">Barcha mutaxassisliklar</option>
                <?php foreach ($specialties as $spec): ?>
                    <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo $specialtyFilter === $spec ? 'selected' : ''; ?>><?php echo htmlspecialchars($spec); ?></option>
                <?php endforeach; ?>
            </select>
            
            <input type="text" name="search" placeholder="Ism yoki mutaxassislik..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Qidirish</button>
            <a href="shifokorlar.php" class="btn btn-secondary">Tozalash</a>
        </form>
        
        <a href="shifokor-tahrirlash.php" class="btn btn-primary">+ Yangi Shifokor</a>
    </div>
    
    <!-- Grid -->
    <div class="doctors-grid">
        <?php if (empty($doctors)): ?>
            <p style="text-align: center; color: #86868b;">Shifokorlar topilmadi</p>
        <?php else: ?>
            <?php foreach ($doctors as $doctor): ?>
                <div class="doctor-card glass-card">
                    <div class="doctor-image">
                        <?php if ($doctor['image']): ?>
                            <img src="../uploads/doctors/<?php echo htmlspecialchars($doctor['image']); ?>" alt="<?php echo htmlspecialchars($doctor['full_name']); ?>">
                        <?php else: ?>
                            <div class="doctor-placeholder">👨‍⚕️</div>
                        <?php endif; ?>
                    </div>
                    <div class="doctor-info">
                        <h3><?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                        <p class="doctor-specialty"><?php echo htmlspecialchars($doctor['specialty']); ?></p>
                        <p class="doctor-experience"><?php echo $doctor['experience_years']; ?> yil tajriba</p>
                        <p class="doctor-room">Xona: <?php echo htmlspecialchars($doctor['room_number'] ?? '-'); ?></p>
                        <span class="badge badge-<?php echo $doctor['is_active'] ? 'success' : 'secondary'; ?>">
                            <?php echo $doctor['is_active'] ? 'Faol' : 'No faol'; ?>
                        </span>
                    </div>
                    <div class="doctor-actions">
                        <a href="shifokor-tahrirlash.php?id=<?php echo $doctor['id']; ?>" class="btn btn-primary">✏️ Tahrirlash</a>
                        <a href="?delete=<?php echo $doctor['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn btn-error" onclick="return confirm('O\'chirishga ishonchingiz komilmi?')">🗑️ O'chirish</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Paginatsiya -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&specialty=<?php echo urlencode($specialtyFilter); ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.doctors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }
.doctor-card { text-align: center; padding: 20px; transition: transform 0.3s ease; }
.doctor-card:hover { transform: translateY(-5px); }
.doctor-image { width: 120px; height: 120px; margin: 0 auto 15px; border-radius: 50%; overflow: hidden; background: rgba(0,113,227,0.1); }
.doctor-image img { width: 100%; height: 100%; object-fit: cover; }
.doctor-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 48px; }
.doctor-info h3 { font-size: 18px; margin-bottom: 5px; color: #1d1d1f; }
.doctor-specialty { color: #0071e3; font-weight: 500; margin-bottom: 5px; }
.doctor-experience, .doctor-room { color: #86868b; font-size: 14px; margin-bottom: 5px; }
.doctor-actions { display: flex; gap: 10px; justify-content: center; margin-top: 15px; }
.filter-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
.filter-form { display: flex; gap: 10px; flex-wrap: wrap; }
.badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; background: rgba(0,113,227,0.1); color: #0071e3; }
.badge-success { background: rgba(52,199,89,0.1); color: #34c759; }
.badge-secondary { background: rgba(134,134,139,0.1); color: #86868b; }
.pagination { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
.page-link { padding: 8px 16px; border-radius: 8px; background: rgba(255,255,255,0.7); text-decoration: none; color: #1d1d1f; transition: all 0.3s ease; }
.page-link:hover, .page-link.active { background: #0071e3; color: white; }
</style>

<?php include '../includes/admin-footer.php'; ?>
