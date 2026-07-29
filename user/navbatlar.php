<?php
/**
 * User Panel - Navbatlar
 * Bemorning barcha navbatlari ro'yxati
 */

$pageTitle = "Navbatlarim";
require_once '../includes/user-header.php';

// Filter parametrlari
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

// Barcha navbatlarni olish
$appointments = getUserAppointmentsFiltered($user['id'], $statusFilter, $page, $limit);
$totalAppointments = getUserAppointmentsCount($user['id'], $statusFilter);
$totalPages = ceil($totalAppointments / $limit);
?>

<div class="card">
    <div class="card-header">🗓️ Mening navbatlarim</div>
    
    <!-- Filter tabs -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <a href="?status=all" class="btn <?= $statusFilter == 'all' ? 'btn-primary' : '' ?>" style="background: <?= $statusFilter == 'all' ? '' : 'var(--card-bg)' ?>;">Barchasi</a>
        <a href="?status=pending" class="btn <?= $statusFilter == 'pending' ? 'btn-primary' : '' ?>" style="background: <?= $statusFilter == 'pending' ? '' : 'var(--card-bg)' ?>;">Kutilmoqda</a>
        <a href="?status=confirmed" class="btn <?= $statusFilter == 'confirmed' ? 'btn-primary' : '' ?>" style="background: <?= $statusFilter == 'confirmed' ? '' : 'var(--card-bg)' ?>;">Tasdiqlandi</a>
        <a href="?status=completed" class="btn <?= $statusFilter == 'completed' ? 'btn-primary' : '' ?>" style="background: <?= $statusFilter == 'completed' ? '' : 'var(--card-bg)' ?>;">Bajarildi</a>
        <a href="?status=cancelled" class="btn <?= $statusFilter == 'cancelled' ? 'btn-primary' : '' ?>" style="background: <?= $statusFilter == 'cancelled' ? '' : 'var(--card-bg)' ?>;">Bekor qilindi</a>
    </div>
    
    <?php if (empty($appointments)): ?>
        <p style="color: var(--text-secondary); text-align: center; padding: 2rem 0;">
            <?= $statusFilter == 'all' ? 'Hozircha navbatlar yo\'q.' : 'Ushbu holatda navbatlar yo\'q.' ?>
        </p>
        <?php if ($statusFilter == 'all' || $statusFilter == 'cancelled'): ?>
            <div style="text-align: center;">
                <a href="navbat-olish.php" class="btn btn-primary">Yangi navbatga yozilish</a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sana</th>
                        <th>Vaqt</th>
                        <th>Shifokor</th>
                        <th>Xizmat</th>
                        <th>Narx</th>
                        <th>Holat</th>
                        <th>Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td>#<?= $appointment['id'] ?></td>
                            <td><?= date('d.m.Y', strtotime($appointment['appointment_date'])) ?></td>
                            <td><?= date('H:i', strtotime($appointment['appointment_time'])) ?></td>
                            <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                            <td style="font-weight: 600;"><?= number_format($appointment['price'], 0, ',', ' ') ?> so'm</td>
                            <td>
                                <span class="status-badge status-<?= $appointment['status'] ?>">
                                    <?php
                                    $statusLabels = [
                                        'pending' => 'Kutilmoqda',
                                        'confirmed' => 'Tasdiqlandi',
                                        'completed' => 'Bajarildi',
                                        'cancelled' => 'Bekor qilindi'
                                    ];
                                    echo $statusLabels[$appointment['status']] ?? $appointment['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if ($appointment['status'] == 'pending' || $appointment['status'] == 'confirmed'): ?>
                                        <button onclick="cancelAppointment(<?= $appointment['id'] ?>)" class="btn btn-danger" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Bekor qilish</button>
                                    <?php endif; ?>
                                    <?php if ($appointment['status'] == 'completed'): ?>
                                        <a href="fikrlar.php?appointment_id=<?= $appointment['id'] ?>" class="btn btn-success" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Sharh qoldirish</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginatsiya -->
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem; flex-wrap: wrap;">
                <?php if ($page > 1): ?>
                    <a href="?status=<?= $statusFilter ?>&page=<?= $page - 1 ?>" class="btn" style="background: var(--card-bg);">&laquo; Oldingi</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="btn btn-primary" style="cursor: default;"><?= $i ?></span>
                    <?php elseif ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <a href="?status=<?= $statusFilter ?>&page=<?= $i ?>" class="btn" style="background: var(--card-bg);"><?= $i ?></a>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <span style="padding: 0.875rem; color: var(--text-secondary);">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?status=<?= $statusFilter ?>&page=<?= $page + 1 ?>" class="btn" style="background: var(--card-bg);">Keyingi &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    function cancelAppointment(appointmentId) {
        if (!confirm('Haqiqatan ham ushbu navbatni bekor qilmoqchimisiz?\n\nEslatma: Navbat vaqtidan 24 soat oldin bekor qilish mumkin.')) {
            return;
        }
        
        fetch('../api/cancel-appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'appointment_id=' + appointmentId + '&csrf_token=<?= generateCSRFToken() ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('Navbat bekor qilindi', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Xatolik yuz berdi', 'error');
            }
        })
        .catch(error => {
            showToast('Xatolik yuz berdi', 'error');
            console.error('Error:', error);
        });
    }
</script>

<?php require_once '../includes/user-footer.php'; ?>
