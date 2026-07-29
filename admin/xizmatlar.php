<?php
// admin/xizmatlar.php - Xizmatlar katalogi CRUD
require_once '../includes/init.php';
require_once '../includes/auth.php';

checkAdminAuth();

$success = '';
$error = '';
$db = getDB();

// Xizmatni o'chirish
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        $error = "Xavfsizlik xatosi";
    } else {
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = "Xizmat o'chirildi";
        } else {
            $error = "O'chirishda xatolik";
        }
    }
}

// Xizmat qo'shish/tahrirlash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Xavfsizlik xatosi: CSRF token noto'g'ri";
    } else {
        $name = cleanInput($_POST['name'] ?? '');
        $category = cleanInput($_POST['category'] ?? '');
        $short_description = cleanInput($_POST['short_description'] ?? '');
        $full_description = cleanInput($_POST['full_description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $duration = (int)($_POST['duration'] ?? 30);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../uploads/services/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $result = uploadFile($_FILES['image'], $uploadDir, ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
            if ($result['success']) {
                $image = $result['filename'];
            } else {
                $error = "Rasm yuklashda xatolik: " . $result['error'];
            }
        }
        
        if (empty($error)) {
            if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
                // Tahrirlash
                $id = (int)$_POST['id'];
                if ($image) {
                    $stmt = $db->prepare("UPDATE services SET name=?, category=?, short_description=?, full_description=?, price=?, duration=?, image=?, is_active=? WHERE id=?");
                    $stmt->execute([$name, $category, $short_description, $full_description, $price, $duration, $image, $is_active, $id]);
                } else {
                    $stmt = $db->prepare("UPDATE services SET name=?, category=?, short_description=?, full_description=?, price=?, duration=?, is_active=? WHERE id=?");
                    $stmt->execute([$name, $category, $short_description, $full_description, $price, $duration, $is_active, $id]);
                }
                $success = "Xizmat yangilandi";
            } else {
                // Yangi qo'shish
                $stmt = $db->prepare("INSERT INTO services (name, category, short_description, full_description, price, duration, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $category, $short_description, $full_description, $price, $duration, $image, $is_active])) {
                    $success = "Xizmat qo'shildi";
                } else {
                    $error = "Qo'shishda xatolik";
                }
            }
        }
    }
}

// Filter va qidiruv
$categoryFilter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Kategoriyalar
$categories = ['Diagnostika', 'Stomatologiya', 'Terapiya', 'Xirurgiya', 'Kardiologiya', 'Nevrologiya', 'Dermatologiya', 'Ginekologiya', 'Urologiya', 'Oftalmologiya', 'ENT', 'Travmatologiya', 'Endokrinologiya', 'Gastroenterologiya', 'Boshqa'];

// Xizmatlarni olish
$where = [];
$params = [];

if ($categoryFilter) {
    $where[] = "category = ?";
    $params[] = $categoryFilter;
}

if ($search) {
    $where[] = "(name LIKE ? OR short_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $db->query("SELECT COUNT(*) FROM services $whereSQL", $params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $db->query("SELECT * FROM services $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tahrirlash uchun xizmat
$editService = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editService = $stmt->fetch(PDO::FETCH_ASSOC);
}

include '../includes/admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>🏥 Xizmatlar</h1>
        <p>Xizmatlar katalogini boshqaring</p>
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
            <select name="category" onchange="this.form.submit()">
                <option value="">Barcha kategoriyalar</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
            
            <input type="text" name="search" placeholder="Xizmat nomi..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Qidirish</button>
            <a href="xizmatlar.php" class="btn btn-secondary">Tozalash</a>
        </form>
        
        <button class="btn btn-primary" onclick="openModal('addModal')">+ Yangi Xizmat</button>
    </div>
    
    <!-- Jadval -->
    <div class="glass-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Rasm</th>
                    <th>Nomi</th>
                    <th>Kategoriya</th>
                    <th>Narxi</th>
                    <th>Davomiyligi</th>
                    <th>Holat</th>
                    <th>Harakatlar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr><td colspan="8" style="text-align: center;">Xizmatlar topilmadi</td></tr>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo $service['id']; ?></td>
                            <td>
                                <?php if ($service['image']): ?>
                                    <img src="../uploads/services/<?php echo htmlspecialchars($service['image']); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #e0e0e0; border-radius: 8px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($service['category']); ?></span></td>
                            <td><?php echo number_format($service['price'], 0, ',', ' '); ?> so'm</td>
                            <td><?php echo $service['duration']; ?> daqiqa</td>
                            <td>
                                <span class="badge badge-<?php echo $service['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $service['is_active'] ? 'Faol' : 'No faol'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary">✏️</a>
                                <a href="?delete=<?php echo $service['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn btn-sm btn-error" onclick="return confirm('O\'chirishga ishonchingiz komilmi?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Paginatsiya -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($categoryFilter); ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Qo'shish/Tahrirlash -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addModal')">&times;</span>
        <h2><?php echo $editService ? '✏️ Xizmatni Tahrirlash' : '➕ Yangi Xizmat Qo\'shish'; ?></h2>
        
        <form method="POST" enctype="multipart/form-data" class="glass-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <?php if ($editService): ?>
                <input type="hidden" name="id" value="<?php echo $editService['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Xizmat nomi *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($editService['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Kategoriya *</label>
                <select name="category" required>
                    <option value="">Tanlang</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($editService['category'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Qisqacha ta'rif</label>
                <textarea name="short_description" rows="2"><?php echo htmlspecialchars($editService['short_description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>To'liq ta'rif</label>
                <textarea name="full_description" rows="4"><?php echo htmlspecialchars($editService['full_description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Narxi (so'm) *</label>
                    <input type="number" name="price" value="<?php echo htmlspecialchars($editService['price'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Davomiyligi (daqiqa) *</label>
                    <input type="number" name="duration" value="<?php echo htmlspecialchars($editService['duration'] ?? 30); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Rasm</label>
                <?php if (!empty($editService['image'])): ?>
                    <img src="../uploads/services/<?php echo htmlspecialchars($editService['image']); ?>" alt="" style="max-height: 100px; margin-bottom: 10px;">
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
                <small>PNG, JPG, WEBP (maks 2MB)</small>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" <?php echo ($editService['is_active'] ?? 1) ? 'checked' : ''; ?>> Faol
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary"><?php echo $editService ? 'Yangilash' : 'Qo\'shish'; ?></button>
        </form>
    </div>
</div>

<script>
// Modal ochish/yopish
function openModal(id) {
    document.getElementById(id).style.display = 'block';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Agar edit parametresi bo'lsa, modalni ochish
<?php if ($editService): ?>
    openModal('addModal');
<?php endif; ?>

// Modal tashqarisiga bosganda yopish
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<style>
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
.modal-content { background: white; margin: 5% auto; padding: 30px; border-radius: 16px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
.close { float: right; font-size: 28px; cursor: pointer; color: #86868b; }
.close:hover { color: #1d1d1f; }
.filter-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
.filter-form { display: flex; gap: 10px; flex-wrap: wrap; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
.data-table th { background: rgba(0,113,227,0.1); font-weight: 600; }
.badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; background: rgba(0,113,227,0.1); color: #0071e3; }
.badge-success { background: rgba(52,199,89,0.1); color: #34c759; }
.badge-secondary { background: rgba(134,134,139,0.1); color: #86868b; }
.btn-sm { padding: 6px 12px; font-size: 14px; }
.pagination { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
.page-link { padding: 8px 16px; border-radius: 8px; background: rgba(255,255,255,0.7); text-decoration: none; color: #1d1d1f; transition: all 0.3s ease; }
.page-link:hover, .page-link.active { background: #0071e3; color: white; }
</style>

<?php include '../includes/admin-footer.php'; ?>
