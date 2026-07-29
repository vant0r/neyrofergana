<?php
/**
 * User Panel Header
 * Bemor shaxsiy kabineti uchun yuqori qism va navigatsiya
 */

if (!isset($pageTitle)) $pageTitle = "Bemor Kabineti";
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Agar user kirish qilmagan bo'lsa, login sahifasiga yo'naltirish
require_once __DIR__ . '/auth.php';
if (!isLoggedIn() || !isUser()) {
    redirect('kirish-royxatdan-otish.php');
}

$user = getCurrentUser();
$settings = getSettings();
$contacts = getContacts();

// O'qilmagan bildirishnomalar soni
$unreadNotifications = getUnreadNotificationsCount($user['id']);
// O'qilmagan xabarlar soni
$unreadMessages = getUnreadMessagesCount($user['id']);
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($settings['site_name']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($settings['meta_description']) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($settings['meta_keywords']) ?>">
    
    <?php if (!empty($settings['favicon'])): ?>
        <link rel="icon" href="<?= htmlspecialchars($settings['favicon']) ?>">
    <?php endif; ?>
    
    <style>
        /* Umumiy stillar */
        :root {
            --primary: #0071e3;
            --primary-hover: #0077ed;
            --success: #34c759;
            --danger: #ff3b30;
            --warning: #ffcc00;
            --text: #1d1d1f;
            --text-secondary: #86868b;
            --bg: #f5f5f7;
            --card-bg: rgba(255, 255, 255, 0.7);
            --border: rgba(255, 255, 255, 0.3);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
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
            min-height: 100vh;
        }
        
        /* User Panel Layout */
        .user-panel {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .user-sidebar {
            width: 280px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid var(--border);
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .user-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .user-logo img {
            max-width: 180px;
            height: auto;
        }
        
        .user-profile {
            text-align: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            margin-bottom: 1rem;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        
        .user-email {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .user-menu {
            list-style: none;
        }
        
        .user-menu-item {
            margin-bottom: 0.5rem;
        }
        
        .user-menu-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .user-menu-link:hover,
        .user-menu-link.active {
            background: rgba(0, 113, 227, 0.1);
            color: var(--primary);
        }
        
        .user-menu-icon {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .user-logout {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.875rem;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: #ff453a;
            transform: translateY(-2px);
        }
        
        /* Main Content */
        .user-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }
        
        .user-header {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            letter-spacing: -0.02em;
        }
        
        .user-breadcrumbs {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1100;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            width: 44px;
            height: 44px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .user-sidebar {
                transform: translateX(-100%);
            }
            
            .user-sidebar.active {
                transform: translateX(0);
            }
            
            .user-main {
                margin-left: 0;
                padding: 1rem;
                padding-top: 4rem;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .user-header {
                padding: 1rem 1.5rem;
            }
            
            .user-header h1 {
                font-size: 1.5rem;
            }
        }
        
        /* Card Styles */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }
        
        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        th {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        tr:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Status Badges */
        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: rgba(255, 204, 0, 0.2);
            color: #b88600;
        }
        
        .status-confirmed {
            background: rgba(52, 199, 89, 0.2);
            color: var(--success);
        }
        
        .status-cancelled {
            background: rgba(255, 59, 48, 0.2);
            color: var(--danger);
        }
        
        .status-completed {
            background: rgba(0, 113, 227, 0.2);
            color: var(--primary);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text);
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
            transform: translateX(150%);
            transition: transform 0.3s ease;
            z-index: 2000;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success {
            border-left-color: var(--success);
        }
        
        .toast.error {
            border-left-color: var(--danger);
        }
    </style>
</head>
<body>
    <button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="user-panel">
        <!-- Sidebar -->
        <aside class="user-sidebar" id="userSidebar">
            <div class="user-logo">
                <?php if (!empty($settings['logo'])): ?>
                    <img src="<?= htmlspecialchars($settings['logo']) ?>" alt="<?= htmlspecialchars($settings['site_name']) ?>">
                <?php else: ?>
                    <h2 style="color: var(--primary);"><?= htmlspecialchars($settings['site_name']) ?></h2>
                <?php endif; ?>
            </div>
            
            <div class="user-profile">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="<?= htmlspecialchars($user['full_name']) ?>" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar" style="background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            
            <ul class="user-menu">
                <li class="user-menu-item">
                    <a href="boshqaruv.php" class="user-menu-link <?= $current_page == 'boshqaruv' ? 'active' : '' ?>">
                        <span class="user-menu-icon">📊</span>
                        Boshqaruv
                    </a>
                </li>
                <li class="user-menu-item">
                    <a href="navbatlar.php" class="user-menu-link <?= $current_page == 'navbatlar' ? 'active' : '' ?>">
                        <span class="user-menu-icon">🗓️</span>
                        Navbatlarim
                    </a>
                </li>
                <li class="user-menu-item">
                    <a href="navbat-olish.php" class="user-menu-link <?= $current_page == 'navbat-olish' ? 'active' : '' ?>">
                        <span class="user-menu-icon">➕</span>
                        Navbat olish
                    </a>
                </li>
                <li class="user-menu-item">
                    <a href="testlar.php" class="user-menu-link <?= $current_page == 'testlar' ? 'active' : '' ?>">
                        <span class="user-menu-icon">🧪</span>
                        Test natijalari
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="badge"><?= $unreadNotifications ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="user-menu-item">
                    <a href="profil.php" class="user-menu-link <?= $current_page == 'profil' ? 'active' : '' ?>">
                        <span class="user-menu-icon">👤</span>
                        Profil
                    </a>
                </li>
                <li class="user-menu-item">
                    <a href="sozlamalar.php" class="user-menu-link <?= $current_page == 'sozlamalar' ? 'active' : '' ?>">
                        <span class="user-menu-icon">⚙️</span>
                        Sozlamalar
                    </a>
                </li>
                <li class="user-menu-item">
                    <a href="chat.php" class="user-menu-link <?= $current_page == 'chat' ? 'active' : '' ?>">
                        <span class="user-menu-icon">💬</span>
                        Chat
                        <?php if ($unreadMessages > 0): ?>
                            <span class="badge"><?= $unreadMessages ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="user-menu-item">
                    <a href="bildirishnomalar.php" class="user-menu-link <?= $current_page == 'bildirishnomalar' ? 'active' : '' ?>">
                        <span class="user-menu-icon">🔔</span>
                        Bildirishnomalar
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="badge"><?= $unreadNotifications ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="user-menu-item">
                    <a href="fikrlar.php" class="user-menu-link <?= $current_page == 'fikrlar' ? 'active' : '' ?>">
                        <span class="user-menu-icon">⭐</span>
                        Fikrlarim
                    </a>
                </li>
                <li class="user-menu-item">
                    <a href="hisobotlar.php" class="user-menu-link <?= $current_page == 'hisobotlar' ? 'active' : '' ?>">
                        <span class="user-menu-icon">📄</span>
                        Hisobotlar
                    </a>
                </li>
            </ul>
            
            <div class="user-logout">
                <a href="../api/logout.php" class="logout-btn">
                    <span style="margin-right: 0.5rem;">🚪</span>
                    Chiqish
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="user-main">
            <div class="user-header">
                <div>
                    <h1><?= htmlspecialchars($pageTitle) ?></h1>
                    <div class="user-breadcrumbs">
                        <a href="boshqaruv.php" style="color: var(--primary); text-decoration: none;">Boshqaruv</a>
                        <?php if ($current_page != 'boshqaruv'): ?>
                            / <span><?= htmlspecialchars($pageTitle) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
