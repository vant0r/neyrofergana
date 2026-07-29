<?php
/**
 * Admin Panel Header
 * Klinika Boshqaruv Paneli - Yuqori qism va navigatsiya
 */

if (!defined('ACCESS_ALLOWED')) {
    die('To\'g\'ridan-to\'g\'ri kirish taqiqlangan');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Admin tekshiruvi
checkAdminAuth();

$settings = getSettings();
$contacts = getContacts();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'admin';
currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['site_name'] ?? 'Klinika') ?> - Admin Panel</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="<?= htmlspecialchars(getLogoUrl('favicon')) ?>">
    <style>
        :root {
            --primary: #0071e3;
            --primary-hover: #0077ed;
            --success: #34c759;
            --danger: #ff3b30;
            --warning: #ff9500;
            --bg: #f5f5f7;
            --card-bg: rgba(255, 255, 255, 0.8);
            --text: #1d1d1f;
            --text-secondary: #86868b;
            --border: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            padding: 20px 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .admin-logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .admin-logo img {
            height: 40px;
            width: auto;
        }

        .admin-nav {
            list-style: none;
        }

        .admin-nav li {
            margin-bottom: 4px;
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(0, 113, 227, 0.1);
            border-left-color: var(--primary);
            color: var(--primary);
        }

        .admin-nav .nav-icon {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .admin-nav .badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        .admin-nav-section {
            padding: 12px 20px 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            letter-spacing: 0.5px;
        }

        /* Main Content */
        .admin-main {
            margin-left: 260px;
            padding: 20px 40px;
            min-height: 100vh;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }

        .admin-header h1 {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        .admin-user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-user-info {
            text-align: right;
        }

        .admin-user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .admin-user-role {
            font-size: 12px;
            color: var(--text-secondary);
            background: rgba(0, 113, 227, 0.1);
            padding: 2px 8px;
            border-radius: 12px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .btn-logout {
            padding: 8px 16px;
            background: rgba(255, 59, 48, 0.1);
            color: var(--danger);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: rgba(255, 59, 48, 0.2);
        }

        /* Content Card */
        .admin-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Table Styles */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .admin-table th,
        .admin-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .admin-table th {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-table tr:hover {
            background: rgba(0, 113, 227, 0.05);
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: rgba(52, 199, 89, 0.15);
            color: var(--success);
        }

        .status-inactive {
            background: rgba(134, 134, 139, 0.15);
            color: var(--text-secondary);
        }

        .status-pending {
            background: rgba(255, 149, 0, 0.15);
            color: var(--warning);
        }

        .status-danger {
            background: rgba(255, 59, 48, 0.15);
            color: var(--danger);
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 113, 227, 0.3);
        }

        .btn-secondary {
            background: rgba(134, 134, 139, 0.15);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: rgba(134, 134, 139, 0.25);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0;
        }

        .tab-btn {
            padding: 12px 20px;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }

            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .admin-table {
                font-size: 13px;
            }

            .admin-table th,
            .admin-table td {
                padding: 8px 12px;
            }
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
        }

        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification-toast.show {
            transform: translateX(0);
        }

        .notification-toast.success {
            border-left: 4px solid var(--success);
        }

        .notification-toast.error {
            border-left: 4px solid var(--danger);
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-logo">
                <img src="<?= htmlspecialchars(getLogoUrl('main')) ?>" alt="<?= htmlspecialchars($settings['site_name'] ?? 'Klinika') ?>">
            </div>
            
            <ul class="admin-nav">
                <li class="admin-nav-section">Asosiy</li>
                <li><a href="/admin/boshqaruv.php" class="<?= $currentPage === 'boshqaruv' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    Boshqaruv
                </a></li>
                <li><a href="/admin/sozlamalar.php" class="<?= $currentPage === 'sozlamalar' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.31.06-.63.06-.94 0-.31-.02-.63-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                    Sozlamalar
                </a></li>
                <li><a href="/admin/reklama.php" class="<?= $currentPage === 'reklama' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg>
                    Reklama
                    <?php if ($unreadAds = getUnreadNotifications('ads')): ?>
                        <span class="badge"><?= $unreadAds ?></span>
                    <?php endif; ?>
                </a></li>

                <li class="admin-nav-section">Katalog</li>
                <li><a href="/admin/xizmatlar.php" class="<?= $currentPage === 'xizmatlar' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h4v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z"/></svg>
                    Xizmatlar
                </a></li>
                <li><a href="/admin/shifokorlar.php" class="<?= $currentPage === 'shifokorlar' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    Shifokorlar
                </a></li>
                <li><a href="/admin/navbatlar.php" class="<?= $currentPage === 'navbatlar' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                    Navbatlar
                    <?php if ($pendingAppointments = getPendingAppointmentsCount()): ?>
                        <span class="badge"><?= $pendingAppointments ?></span>
                    <?php endif; ?>
                </a></li>

                <li class="admin-nav-section">Mijozlar</li>
                <li><a href="/admin/bemorlar.php" class="<?= $currentPage === 'bemorlar' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    Bemorlar
                </a></li>
                <li><a href="/admin/test-natijalari.php" class="<?= $currentPage === 'test-natijalari' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19.5 3.5L18 1.75l-1.5 1.75L15 1.75 13.5 3.5 12 1.75 10.5 3.5 9 1.75 7.5 3.5 6 1.75 4.5 3.5 3 1.75 1.5 3.5 0 1.75v14.63c0 .62.63 1.12 1.25.75.63-.38 1.25-.75 1.25-.75V3.5l1.5 1.75 1.5-1.75 1.5 1.75 1.5-1.75 1.5 1.75 1.5-1.75 1.5 1.75 1.5-1.75 1.5 1.75L19.5 3.5z"/></svg>
                    Test natijalari
                </a></li>
                <li><a href="/admin/fikrlar.php" class="<?= $currentPage === 'fikrlar' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    Fikrlar
                    <?php if ($pendingReviews = getPendingReviewsCount()): ?>
                        <span class="badge"><?= $pendingReviews ?></span>
                    <?php endif; ?>
                </a></li>

                <li class="admin-nav-section">Kontent</li>
                <li><a href="/admin/yangiliklar.php" class="<?= $currentPage === 'yangiliklar' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    Yangiliklar
                </a></li>
                <li><a href="/admin/galereya.php" class="<?= $currentPage === 'galereya' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                    Galereya
                </a></li>

                <li class="admin-nav-section">Aloqa</li>
                <li><a href="/admin/chat.php" class="<?= $currentPage === 'chat' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                    Chat
                    <?php if ($unreadMessages = getUnreadMessagesCount()): ?>
                        <span class="badge"><?= $unreadMessages ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="/admin/bildirishnomalar.php" class="<?= $currentPage === 'bildirishnomalar' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/></svg>
                    Bildirishnomalar
                </a></li>

                <?php if ($adminRole === 'superadmin'): ?>
                <li class="admin-nav-section">Tizim</li>
                <li><a href="/admin/foydalanuvchilar.php" class="<?= $currentPage === 'foydalanuvchilar' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    Foydalanuvchilar
                </a></li>
                <li><a href="/admin/htaccess.php" class="<?= $currentPage === 'htaccess' ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    Himoya
                </a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                        </svg>
                    </button>
                    <h1><?= htmlspecialchars($pageTitle ?? 'Boshqaruv Paneli') ?></h1>
                </div>
                
                <div class="admin-user-menu">
                    <div class="admin-user-info">
                        <div class="admin-user-name"><?= htmlspecialchars($adminName) ?></div>
                        <div class="admin-user-role"><?= htmlspecialchars(ucfirst($adminRole)) ?></div>
                    </div>
                    <div class="admin-avatar">
                        <?= strtoupper(substr($adminName, 0, 1)) ?>
                    </div>
                    <button class="btn-logout" onclick="logout()">Chiqish</button>
                </div>
            </header>

            <!-- Page Content -->
            <div class="admin-card">
                <script>
                    // Sidebar toggle for mobile
                    function toggleSidebar() {
                        document.getElementById('adminSidebar').classList.toggle('open');
                    }

                    // Logout function
                    function logout() {
                        if (confirm('Chiqishni xohlaysizmi?')) {
                            window.location.href = '/kirish-royxatdan-otish.php?logout=1';
                        }
                    }

                    // Show notification toast
                    function showNotification(message, type = 'success') {
                        const toast = document.createElement('div');
                        toast.className = `notification-toast ${type}`;
                        toast.textContent = message;
                        document.body.appendChild(toast);
                        
                        setTimeout(() => toast.classList.add('show'), 100);
                        setTimeout(() => {
                            toast.classList.remove('show');
                            setTimeout(() => toast.remove(), 300);
                        }, 3000);
                    }

                    // Tab functionality
                    function openTab(tabId) {
                        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
                        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                        document.getElementById(tabId).classList.add('active');
                        event.target.classList.add('active');
                    }

                    // Auto-hide sidebar on mobile when clicking outside
                    document.addEventListener('click', function(e) {
                        const sidebar = document.getElementById('adminSidebar');
                        const toggle = document.querySelector('.mobile-menu-toggle');
                        if (window.innerWidth <= 1024 && 
                            !sidebar.contains(e.target) && 
                            !toggle.contains(e.target) &&
                            sidebar.classList.contains('open')) {
                            sidebar.classList.remove('open');
                        }
                    });
                </script>
