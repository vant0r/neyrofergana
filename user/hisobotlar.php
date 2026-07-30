<?php
/**
 * user/hisobotlar.php - To'lovlar tarixi va hisobotlar
 */
require_once '../includes/config.php';
require_once '../includes/init.php';
require_once '../includes/auth.php';

checkAuth();
checkRole('user');

$user_id = $_SESSION['user_id'];

// Filter parametrlari
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'all';

// To'lovlar ro'yxatini olish
try {
    $sql = "
        SELECT a.id, a.appointment_date, a.status as appointment_status,
               s.name as service_name, s.price,
               d.full_name as doctor_name,
               p.amount, p.payment_date, p.payment_method, p.status as payment_status,
               p.transaction_id
        FROM appointments a
        INNER JOIN services s ON a.service_id = s.id
        INNER JOIN doctors d ON a.doctor_id = d.id
        LEFT JOIN payments p ON a.id = p.appointment_id
        WHERE a.user_id = ? AND p.id IS NOT NULL
    ";
    
    $params = [$user_id];
    
    if ($start_date) {
        $sql .= " AND p.payment_date >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $sql .= " AND p.payment_date <= ?";
        $params[] = $end_date;
    }
    
    if ($status_filter !== 'all') {
        $sql .= " AND p.status = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " ORDER BY p.payment_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Umumiy summa
    $total_sql = "SELECT SUM(amount) as total FROM payments p 
                  INNER JOIN appointments a ON p.appointment_id = a.id 
                  WHERE a.user_id = ? AND p.status = 'completed'";
    if ($start_date) {
        $total_sql .= " AND p.payment_date >= ?";
    }
    if ($end_date) {
        $total_sql .= " AND p.payment_date <= ?";
    }
    
    $stmt = $pdo->prepare($total_sql);
    $stmt->execute($start_date && $end_date ? [$user_id, $start_date, $end_date] : [$user_id]);
    $total_amount = $stmt->fetchColumn() ?? 0;
    
} catch (PDOException $e) {
    error_log("Payments error: " . $e->getMessage());
    $payments = [];
    $total_amount = 0;
}

$page_title = "To'lovlar tarixi";
include '../includes/user-header.php';
?>

<div class="user-container">
    <div class="page-header">
        <h1>📊 To'lovlar tarixi</h1>
        <p>Barcha to'lovlar va cheklaringiz</p>
    </div>

    <!-- Filter -->
    <div class="glass-card" style="margin-bottom: 30px;">
        <form method="GET" action="" class="filter-form">
            <div class="filter-row">
                <div class="form-group">
                    <label for="start_date">Boshlanish sanasi</label>
                    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="end_date">Tugash sanasi</label>
                    <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="status">Holat</label>
                    <select id="status" name="status" class="form-control">
                        <option value="all">Barchasi</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Muvaffaqiyatli</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Kutilmoqda</option>
                        <option value="failed" <?= $status_filter === 'failed' ? 'selected' : '' ?>>Xatolik</option>
                    </select>
                </div>
                
                <div class="form-group" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary">Filtr</button>
                    <a href="" class="btn btn-secondary">Tozalash</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistika -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card glass-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <div class="stat-label">Jami to'lovlar</div>
                <div class="stat-value"><?= number_format($total_amount, 2) ?> so'm</div>
            </div>
        </div>
        <div class="stat-card glass-card">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <div class="stat-label">To'lovlar soni</div>
                <div class="stat-value"><?= count($payments) ?></div>
            </div>
        </div>
    </div>

    <!-- To'lovlar jadvali -->
    <div class="glass-card">
        <h2 style="margin-bottom: 20px;">To'lovlar ro'yxati</h2>
        
        <?php if (empty($payments)): ?>
            <p style="text-align: center; color: #86868b; padding: 40px 0;">
                Ushbu davrda to'lovlar topilmadi.
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Sana</th>
                            <th>Xizmat</th>
                            <th>Shifokor</th>
                            <th>Summa</th>
                            <th>To'lov usuli</th>
                            <th>Holat</th>
                            <th>Chek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= date('d.m.Y H:i', strtotime($payment['payment_date'])) ?></td>
                                <td><strong><?= htmlspecialchars($payment['service_name']) ?></strong></td>
                                <td><?= htmlspecialchars($payment['doctor_name']) ?></td>
                                <td><strong><?= number_format($payment['amount'], 2) ?> so'm</strong></td>
                                <td>
                                    <?php
                                    $methods = [
                                        'cash' => '💵 Naqd',
                                        'card' => '💳 Karta',
                                        'click' => '📱 Click',
                                        'payme' => '📱 Payme'
                                    ];
                                    echo $methods[$payment['payment_method']] ?? $payment['payment_method'];
                                    ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $payment['payment_status'] === 'completed' ? 'success' : ($payment['payment_status'] === 'pending' ? 'warning' : 'error') ?>">
                                        <?= $payment['payment_status'] === 'completed' ? '✅ Muvaffaqiyatli' : ($payment['payment_status'] === 'pending' ? '⏳ Kutilmoqda' : '❌ Xatolik') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($payment['transaction_id']): ?>
                                        <button onclick="showReceipt('<?= $payment['transaction_id'] ?>')" class="btn btn-sm btn-outline">
                                            📄 Ko'rish
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #86868b;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chek modal oynasi -->
<div id="receiptModal" class="modal">
    <div class="modal-content glass-card" style="max-width: 500px;">
        <div class="modal-header">
            <h2>📄 To'lov cheki</h2>
            <button class="modal-close" onclick="closeModal('receiptModal')">&times;</button>
        </div>
        <div class="modal-body" id="receiptContent">
            <p style="text-align: center; color: #86868b;">Yuklanmoqda...</p>
        </div>
        <div class="modal-footer">
            <button onclick="window.print()" class="btn btn-primary">🖨 Chop etish</button>
            <button onclick="closeModal('receiptModal')" class="btn btn-secondary">Yopish</button>
        </div>
    </div>
</div>

<style>
.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #1d1d1f;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 12px;
    background: rgba(255,255,255,0.5);
    font-size: 16px;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #0071e3;
    background: rgba(255,255,255,0.8);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.data-table th {
    font-weight: 600;
    color: #1d1d1f;
    background: rgba(0,0,0,0.02);
}

.data-table tr:hover {
    background: rgba(255,255,255,0.3);
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
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

.btn-sm {
    padding: 6px 12px;
    font-size: 14px;
}

.table-responsive {
    overflow-x: auto;
}

@media print {
    .user-container > *:not(#receiptModal) {
        display: none;
    }
    #receiptModal {
        position: static;
        display: block;
    }
    .modal-content {
        box-shadow: none;
        border: none;
    }
}

@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .data-table th,
    .data-table td {
        padding: 8px;
        font-size: 14px;
    }
}
</style>

<script>
function showReceipt(transactionId) {
    const modal = document.getElementById('receiptModal');
    const content = document.getElementById('receiptContent');
    
    modal.style.display = 'flex';
    content.innerHTML = '<p style="text-align: center; color: #86868b;">Yuklanmoqda...</p>';
    
    // AJAX orqali chek ma'lumotlarini olish
    fetch('../api/get-receipt.php?transaction_id=' + transactionId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                content.innerHTML = `
                    <div style="text-align: center; padding: 20px;">
                        <h3 style="margin-bottom: 20px;">NEURO FERGANA</h3>
                        <p><strong>Chek №:</strong> ${data.receipt.transaction_id}</p>
                        <p><strong>Sana:</strong> ${new Date(data.receipt.payment_date).toLocaleString('uz-UZ')}</p>
                        <hr style="margin: 20px 0; border: none; border-top: 1px dashed #ccc;">
                        <p><strong>Xizmat:</strong> ${data.receipt.service_name}</p>
                        <p><strong>Shifokor:</strong> ${data.receipt.doctor_name}</p>
                        <p><strong>To'lov usuli:</strong> ${data.receipt.payment_method}</p>
                        <hr style="margin: 20px 0; border: none; border-top: 1px dashed #ccc;">
                        <p style="font-size: 24px; font-weight: bold; color: #0071e3;">
                            ${Number(data.receipt.amount).toLocaleString('uz-UZ')} so'm
                        </p>
                        <p style="margin-top: 20px; color: #34c759;">✅ To'lov muvaffaqiyatli amalga oshirildi</p>
                    </div>
                `;
            } else {
                content.innerHTML = '<p style="text-align: center; color: #ff3b30;">Chek topilmadi.</p>';
            }
        })
        .catch(err => {
            content.innerHTML = '<p style="text-align: center; color: #ff3b30;">Xatolik yuz berdi.</p>';
        });
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Modal tashqarisiga bosganda yopish
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
