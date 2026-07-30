<?php
/**
 * admin/foydalanuvchilar.php - Admin foydalanuvchilarni boshqarish
 * Faqat superadmin uchun
 */

require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

// Faqat superadmin kirishi mumkin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../kirish-royxatdan-otish.php');
    exit;
}

$success_message = '';
$error_message = '';

// Yangi admin qo'shish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_message = "Xavfsizlik tokeni noto'g'ri";
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        
        if (empty($full_name) || empty($email) || empty($password)) {
            $error_message = "Barcha maydonlarni to'ldiring";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Email manzil noto'g'ri";
        } elseif (strlen($password) < 8) {
            $error_message = "Parol kamida 8 belgi bo'lishi kerak";
        } else {
            try {
                // Email mavjudligini tekshirish
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error_message = "Bu email bilan foydalanuvchi allaqachon mavjud";
                } else {
                    // Yangi admin qo'shish
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (full_name, email, password, role, is_active, created_at)
                        VALUES (?, ?, ?, ?, 1, NOW())
                    ");
                    $stmt->execute([$full_name, $email, $hashed_password, $role]);
                    
                    $success_message = "Yangi admin muvaffaqiyatli qo'shildi";
                }
            } catch (PDOException $e) {
                $error_message = "Foydalanuvchini qo'shishda xatolik: " . $e->getMessage();
            }
        }
    }
}

// Foydalanuvchini tahrirlash/o'chirish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_message = "Xavfsizlik tokeni noto'g'ri";
    } else {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $action = $_POST['action'];
        
        if ($user_id > 0) {
            try {
                if ($action === 'toggle_status') {
                    $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $success_message = "Foydalanuvchi holati o'zgartirildi";
                } elseif ($action === 'change_role') {
                    $new_role = $_POST['new_role'] ?? 'admin';
                    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$new_role, $user_id]);
                    $success_message = "Rol o'zgartirildi";
                } elseif ($action === 'delete_user') {
                    // O'zini o'chirishga ruxsat bermaslik
                    if ($user_id == $_SESSION['user_id']) {
                        $error_message = "O'zingizni o'chira olmaysiz";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $success_message = "Foydalanuvchi o'chirildi";
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Amalni bajarishda xatolik: " . $e->getMessage();
            }
        }
    }
}

// Barcha admin foydalanuvchilarni olish
try {
    $stmt = $pdo->query("
        SELECT * FROM users 
        WHERE role IN ('admin', 'superadmin')
        ORDER BY created_at DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Foydalanuvchilarni yuklashda xatolik";
    $users = [];
}

$page_title = "Foydalanuvchilarni boshqarish - Admin Panel";
include '../includes/admin-header.php';
?>

<style>
.users-grid {
    display: grid;
    gap: 20px;
}

.card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    padding: 24px;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.users-table th,
.users-table td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.users-table th {
    background: rgba(0, 113, 227, 0.1);
    color: #1d1d1f;
    font-weight: 600;
    font-size: 14px;
}

.users-table tr:hover {
    background: rgba(0, 113, 227, 0.05);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    background: #e5e5ea;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-superadmin {
    background: rgba(255, 149, 0, 0.1);
    color: #ff9500;
}

.badge-admin {
    background: rgba(0, 113, 227, 0.1);
    color: #0071e3;
}

.badge-active {
    background: rgba(52, 199, 89, 0.1);
    color: #34c759;
}

.badge-inactive {
    background: rgba(142, 142, 147, 0.1);
    color: #8e8e93;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-small {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

.btn-edit {
    background: rgba(0, 113, 227, 0.1);
    color: #0071e3;
}

.btn-edit:hover {
    background: rgba(0, 113, 227, 0.2);
}

.btn-danger {
    background: rgba(255, 59, 48, 0.1);
    color: #ff3b30;
}

.btn-danger:hover {
    background: rgba(255, 59, 48, 0.2);
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 32px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.modal-header h2 {
    margin: 0;
    color: #1d1d1f;
    font-size: 22px;
}

.close-modal {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #86868b;
    transition: color 0.3s ease;
}

.close-modal:hover {
    color: #1d1d1f;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #1d1d1f;
    font-weight: 600;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    font-size: 15px;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #0071e3;
    box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
}

.btn-primary {
    width: 100%;
    padding: 14px 24px;
    background: #0071e3;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #0077ed;
    transform: translateY(-2px);
}

.add-user-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #0071e3;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.add-user-btn:hover {
    background: #0077ed;
    transform: translateY(-2px);
}

select.form-control {
    cursor: pointer;
}
</style>

<div class="page-header">
    <h1>👥 Foydalanuvchilarni boshqarish</h1>
    <p>Admin va Superadmin foydalanuvchilar</p>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<button class="add-user-btn" onclick="openModal()">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
    </svg>
    Yangi admin qo'shish
</button>

<div class="card">
    <table class="users-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>F.I.Sh.</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Holat</th>
                <th>Ro'yxatdan o'tgan</th>
                <th>Harakatlar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="<?= !empty($user['avatar']) ? htmlspecialchars('../' . $user['avatar']) : '../uploads/avatars/default.png' ?>" 
                                 alt="Avatar" 
                                 class="user-avatar"
                                 onerror="this.src='../uploads/avatars/default.png'">
                            <span><?= htmlspecialchars($user['full_name']) ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="badge badge-<?= $user['role'] ?>">
                            <?= $user['role'] === 'superadmin' ? 'Superadmin' : 'Admin' ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $user['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $user['is_active'] ? 'Faol' : 'No faol' ?>
                        </span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="action" value="toggle_status">
                                <button type="submit" class="btn-small btn-edit">
                                    <?= $user['is_active'] ? 'No faol' : 'Faol' ?>
                                </button>
                            </form>
                            
                            <?php if ($user['role'] !== 'superadmin'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="action" value="change_role">
                                    <select name="new_role" class="form-control btn-small" onchange="this.form.submit()" style="padding: 6px 8px;">
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="superadmin" <?= $user['role'] === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Rostdan ham o\'chirmoqchimisiz?')">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <button type="submit" class="btn-small btn-danger">O'chirish</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Yangi admin qo'shish -->
<div class="modal" id="addUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Yangi admin qo'shish</h2>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div class="form-group">
                <label>F.I.Sh.</label>
                <input type="text" name="full_name" class="form-control" required placeholder="Ism Familiya">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required placeholder="email@example.com">
            </div>
            
            <div class="form-group">
                <label>Parol</label>
                <input type="password" name="password" class="form-control" required minlength="8" placeholder="Kamida 8 belgi">
            </div>
            
            <div class="form-group">
                <label>Rol</label>
                <select name="role" class="form-control">
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
            </div>
            
            <button type="submit" name="add_user" class="btn-primary">Qo'shish</button>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('addUserModal').classList.add('active');
}

function closeModal() {
    document.getElementById('addUserModal').classList.remove('active');
}

// Modal tashqarisiga bosganda yopish
document.getElementById('addUserModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Escape tugmasi bilan yopish
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?>
