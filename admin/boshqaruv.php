<?php
/**
 * Admin Panel - Boshqaruv (Dashboard)
 * Klinika boshqaruv paneli asosiy sahifasi
 */

define('ACCESS_ALLOWED', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Admin tekshiruvi
checkAdminAuth();

$pageTitle = 'Boshqaruv Paneli';
$stats = getDashboardStats();
$recentAppointments = getRecentAppointments(5);
$recentPatients = getRecentPatients(5);

require_once __DIR__ . '/../includes/admin-header.php';
?>

<!-- Statistika Widgetlari -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #0071e3 0%, #0077ed 100%); border-radius: 16px; padding: 24px; color: white; box-shadow: 0 8px 32px rgba(0, 113, 227, 0.3);">
        <div style="font-size: 13px; opacity: 0.9; margin-bottom: 8px;">Jami Bemorlar</div>
        <div style="font-size: 36px; font-weight: 600; letter-spacing: -0.02em;"><?= number_format($stats['total_patients']) ?></div>
        <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;">+12% o'tgan oyga nisbatan</div>
    </div>
    
    <div style="background: linear-gradient(135deg, #34c759 0%, #30d158 100%); border-radius: 16px; padding: 24px; color: white; box-shadow: 0 8px 32px rgba(52, 199, 89, 0.3);">
        <div style="font-size: 13px; opacity: 0.9; margin-bottom: 8px;">Bugungi Navbatlar</div>
        <div style="font-size: 36px; font-weight: 600; letter-spacing: -0.02em;"><?= number_format($stats['today_appointments']) ?></div>
        <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;">O'rtacha: <?= $stats['avg_appointments'] ?>/kun</div>
    </div>
    
    <div style="background: linear-gradient(135deg, #ff9500 0%, #ffac33 100%); border-radius: 16px; padding: 24px; color: white; box-shadow: 0 8px 32px rgba(255, 149, 0, 0.3);">
        <div style="font-size: 13px; opacity: 0.9; margin-bottom: 8px;">Kunlik Tushum</div>
        <div style="font-size: 36px; font-weight: 600; letter-spacing: -0.02em;"><?= number_format($stats['daily_revenue'], 0, ',', ' ') ?> so'm</div>
        <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;">Faol xizmatlar: <?= $stats['active_services'] ?></div>
    </div>
    
    <div style="background: linear-gradient(135deg, #af52de 0%, #bf5af2 100%); border-radius: 16px; padding: 24px; color: white; box-shadow: 0 8px 32px rgba(175, 82, 222, 0.3);">
        <div style="font-size: 13px; opacity: 0.9; margin-bottom: 8px;">Faol Shifokorlar</div>
        <div style="font-size: 36px; font-weight: 600; letter-spacing: -0.02em;"><?= number_format($stats['active_doctors']) ?></div>
        <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;">Jamoa a'zolari</div>
    </div>
</div>

<!-- Asosiy Kontent -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
    <!-- Chap tomon - Oxirgi navbatlar -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">Oxirgi Navbatlar</h2>
            <a href="/admin/navbatlar.php" class="btn btn-secondary btn-sm">Barchasini ko'rish</a>
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Bemor</th>
                    <th>Shifokor</th>
                    <th>Xizmat</th>
                    <th>Sana</th>
                    <th>Holat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentAppointments)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                        Hozircha navbatlar yo'q
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($recentAppointments as $appointment): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 500;"><?= htmlspecialchars($appointment['patient_name'] ?? 'Noma\'lum') ?></div>
                            <div style="font-size: 12px; color: var(--text-secondary);"><?= htmlspecialchars($appointment['patient_phone'] ?? '') ?></div>
                        </td>
                        <td><?= htmlspecialchars($appointment['doctor_name'] ?? 'Noma\'lum') ?></td>
                        <td><?= htmlspecialchars($appointment['service_name'] ?? 'Noma\'lum') ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($appointment['appointment_date'])) ?></td>
                        <td>
                            <?php
                            $statusClass = 'status-pending';
                            $statusLabel = 'Kutilmoqda';
                            
                            switch ($appointment['status']) {
                                case 'confirmed':
                                    $statusClass = 'status-active';
                                    $statusLabel = 'Tasdiqlandi';
                                    break;
                                case 'completed':
                                    $statusClass = 'status-active';
                                    $statusLabel = 'Bajarildi';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'status-danger';
                                    $statusLabel = 'Bekor qilindi';
                                    break;
                            }
                            ?>
                            <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- O'ng tomon - Oxirgi bemorlar -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">Yangi Bemorlar</h2>
            <a href="/admin/bemorlar.php" class="btn btn-secondary btn-sm">Barchasini ko'rish</a>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php if (empty($recentPatients)): ?>
            <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                Hozircha yangi bemorlar yo'q
            </div>
            <?php else: ?>
                <?php foreach ($recentPatients as $patient): ?>
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(255, 255, 255, 0.5); border-radius: 12px; transition: all 0.3s ease;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #0071e3, #0077ed); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                        <?= strtoupper(substr($patient['full_name'], 0, 1)) ?>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 500; font-size: 14px;"><?= htmlspecialchars($patient['full_name']) ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);"><?= htmlspecialchars($patient['email']) ?></div>
                    </div>
                    <div style="font-size: 11px; color: var(--text-secondary);">
                        <?= date('d.m', strtotime($patient['created_at'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Tezkor Harakatlar -->
        <div style="margin-top: 30px;">
            <h2 style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em; margin-bottom: 16px;">Tezkor Harakatlar</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <a href="/admin/navbatlar.php?action=new" class="btn btn-primary" style="justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    Yangi navbat
                </a>
                <a href="/admin/shifokor-tahrirlash.php" class="btn btn-secondary" style="justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    Shifokor qo'shish
                </a>
                <a href="/admin/xizmatlar.php?action=new" class="btn btn-secondary" style="justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h4v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z"/></svg>
                    Xizmat qo'shish
                </a>
                <a href="/admin/yangiliklar.php?action=new" class="btn btn-secondary" style="justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    Yangilik qo'shish
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-refresh statistika har 30 soniyada
    setInterval(async () => {
        try {
            const response = await fetch('/api/dashboard-stats.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                // Update stats here if needed
                console.log('Stats updated:', data.stats);
            }
        } catch (error) {
            console.error('Failed to refresh stats:', error);
        }
    }, 30000);
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
