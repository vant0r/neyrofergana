<?php
/**
 * Header fayli - umumiy sahifa boshi
 * Barcha CSS stillar shu yerda joylashgan
 */

// Agar to'g'ridan-to'g'ri ochilgan bo'lsa, init orqali yuklash
if (!isset($siteSettings)) {
    require_once __DIR__ . '/init.php';
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Meta tags -->
    <title><?= e($pageTitle ?? 'Shifo Klinikasi') ?></title>
    <meta name="description" content="<?= e($pageDescription ?? '') ?>">
    <meta name="keywords" content="<?= e($pageKeywords ?? '') ?>">
    <meta name="author" content="Shifo Klinikasi">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= getFaviconUrl() ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= getFaviconUrl() ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($pageTitle ?? 'Shifo Klinikasi') ?>">
    <meta property="og:description" content="<?= e($pageDescription ?? '') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= SITE_URL ?><?= $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:image" content="<?= getLogoUrl() ?>">
    
    <style>
        /* ============================================
           RESET & BASE STYLES
           ============================================ */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            /* Ranglar */
            --primary-color: #0071e3;
            --primary-hover: #0077ed;
            --success-color: #34c759;
            --error-color: #ff3b30;
            --warning-color: #ff9500;
            --info-color: #5ac8fa;
            
            /* Fon va matn ranglari */
            --bg-color: #f5f5f7;
            --card-bg: rgba(255, 255, 255, 0.7);
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --text-light: #ffffff;
            
            /* Glassmorphism */
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.3);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --blur-amount: 10px;
            
            /* Border radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 50%;
            
            /* Shadows */
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
            
            /* Transitions */
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.5s ease;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            background-image: linear-gradient(135deg, #f5f5f7 0%, #e8f5e9 100%);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        a {
            text-decoration: none;
            color: var(--primary-color);
            transition: color var(--transition-fast);
        }
        
        a:hover {
            color: var(--primary-hover);
        }
        
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        
        ul, ol {
            list-style: none;
        }
        
        button, input, textarea, select {
            font-family: inherit;
            font-size: inherit;
        }
        
        /* ============================================
           TYPOGRAPHY
           ============================================ */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            letter-spacing: -0.02em;
            line-height: 1.3;
            color: var(--text-primary);
        }
        
        h1 {
            font-size: clamp(2rem, 5vw, 3rem);
        }
        
        h2 {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
        }
        
        h3 {
            font-size: clamp(1.25rem, 3vw, 1.75rem);
        }
        
        p {
            font-weight: 400;
            color: var(--text-secondary);
        }
        
        /* ============================================
           CONTAINER & LAYOUT
           ============================================ */
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
            }
        }
        
        .section {
            padding: clamp(40px, 8vw, 80px) 0;
        }
        
        /* ============================================
           GLASSMORPHISM CARD
           ============================================ */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            box-shadow: var(--glass-shadow);
            padding: 24px;
            transition: transform var(--transition-normal), box-shadow var(--transition-normal);
        }
        
        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        /* ============================================
           BUTTONS
           ============================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all var(--transition-normal);
            text-align: center;
            white-space: nowrap;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: var(--text-light);
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            color: var(--text-light);
            transform: scale(1.02);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .btn-secondary:hover {
            background: var(--primary-color);
            color: var(--text-light);
        }
        
        .btn-success {
            background: var(--success-color);
            color: var(--text-light);
        }
        
        .btn-danger {
            background: var(--error-color);
            color: var(--text-light);
        }
        
        .btn-block {
            width: 100%;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.875rem;
        }
        
        .btn-lg {
            padding: 16px 32px;
            font-size: 1.125rem;
        }
        
        /* ============================================
           HEADER / NAVIGATION
           ============================================ */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: var(--shadow-sm);
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            max-width: 1280px;
            margin: 0 auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .logo img {
            height: 40px;
            width: auto;
        }
        
        .main-nav {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            color: var(--text-primary);
            font-weight: 500;
            padding: 8px 0;
            position: relative;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width var(--transition-normal);
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .phone-link {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-normal);
        }
        
        .user-btn:hover {
            background: var(--primary-color);
            color: var(--text-light);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-full);
            object-fit: cover;
        }
        
        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            gap: 4px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
        }
        
        .mobile-menu-toggle span {
            width: 24px;
            height: 2px;
            background: var(--text-primary);
            border-radius: 2px;
            transition: all var(--transition-normal);
        }
        
        @media (max-width: 1024px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.98);
                flex-direction: column;
                padding: 20px;
                gap: 16px;
                box-shadow: var(--shadow-lg);
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .mobile-menu-toggle.active span:nth-child(1) {
                transform: rotate(45deg) translate(6px, 6px);
            }
            
            .mobile-menu-toggle.active span:nth-child(2) {
                opacity: 0;
            }
            
            .mobile-menu-toggle.active span:nth-child(3) {
                transform: rotate(-45deg) translate(6px, -6px);
            }
        }
        
        /* ============================================
           FORM ELEMENTS
           ============================================ */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--radius-md);
            background: var(--card-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            color: var(--text-primary);
            transition: all var(--transition-fast);
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .error-message {
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 4px;
        }
        
        .success-message {
            color: var(--success-color);
            font-size: 0.875rem;
            margin-top: 4px;
        }
        
        /* ============================================
           BADGES & STATUS
           ============================================ */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: rgba(255, 149, 0, 0.1);
            color: var(--warning-color);
        }
        
        .status-confirmed,
        .status-ready,
        .status-published {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success-color);
        }
        
        .status-cancelled {
            background: rgba(255, 59, 48, 0.1);
            color: var(--error-color);
        }
        
        .status-completed {
            background: rgba(90, 200, 250, 0.1);
            color: var(--info-color);
        }
        
        .status-processing {
            background: rgba(255, 149, 0, 0.1);
            color: var(--warning-color);
        }
        
        .status-draft {
            background: rgba(134, 134, 139, 0.1);
            color: var(--text-secondary);
        }
        
        /* ============================================
           TOAST NOTIFICATIONS
           ============================================ */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .toast {
            min-width: 300px;
            max-width: 400px;
            padding: 16px 20px;
            background: var(--card-bg);
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }
        
        .toast.success {
            border-left: 4px solid var(--success-color);
        }
        
        .toast.error {
            border-left: 4px solid var(--error-color);
        }
        
        .toast.info {
            border-left: 4px solid var(--info-color);
        }
        
        .toast.warning {
            border-left: 4px solid var(--warning-color);
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* ============================================
           PAGINATION
           ============================================ */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 40px;
        }
        
        .pagination li {
            display: inline-block;
        }
        
        .pagination a,
        .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--radius-md);
            background: var(--card-bg);
            color: var(--text-primary);
            font-weight: 500;
            transition: all var(--transition-fast);
        }
        
        .pagination a:hover {
            background: var(--primary-color);
            color: var(--text-light);
            border-color: var(--primary-color);
        }
        
        .pagination .active {
            background: var(--primary-color);
            color: var(--text-light);
            border-color: var(--primary-color);
        }
        
        /* ============================================
           LOADING SPINNER
           ============================================ */
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 113, 227, 0.1);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        
        /* ============================================
           UTILITY CLASSES
           ============================================ */
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .mt-1 { margin-top: 8px; }
        .mt-2 { margin-top: 16px; }
        .mt-3 { margin-top: 24px; }
        .mt-4 { margin-top: 32px; }
        
        .mb-1 { margin-bottom: 8px; }
        .mb-2 { margin-bottom: 16px; }
        .mb-3 { margin-bottom: 24px; }
        .mb-4 { margin-bottom: 32px; }
        
        .hidden {
            display: none !important;
        }
        
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        /* ============================================
           RESPONSIVE UTILITIES
           ============================================ */
        @media (max-width: 768px) {
            .hide-mobile {
                display: none !important;
            }
        }
        
        @media (min-width: 769px) {
            .hide-desktop {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Toast container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Site Header -->
    <header class="site-header">
        <div class="header-content">
            <!-- Logo -->
            <a href="/index.php" class="logo">
                <img src="<?= getLogoUrl() ?>" alt="<?= e($siteSettings['site_name'] ?? 'Shifo Klinikasi') ?>">
                <span class="hide-mobile"><?= e($siteSettings['site_name'] ?? 'Shifo Klinikasi') ?></span>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menyu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <!-- Main Navigation -->
            <nav class="main-nav">
                <ul class="nav-links" id="navLinks">
                    <li><a href="/index.php">Bosh sahifa</a></li>
                    <li><a href="/haqimizda.php">Klinika haqida</a></li>
                    <li><a href="/xizmatlar.php">Xizmatlar</a></li>
                    <li><a href="/shifokorlar.php">Shifokorlar</a></li>
                    <li><a href="/yangiliklar.php">Yangiliklar</a></li>
                    <li><a href="/aloqa.php">Aloqa</a></li>
                </ul>
                
                <div class="header-actions">
                    <?php if ($currentUser): ?>
                        <div class="user-menu">
                            <a href="/user/boshqaruv.php" class="user-btn">
                                <?php if ($currentUser['avatar']): ?>
                                    <img src="<?= e($currentUser['avatar']) ?>" alt="" class="user-avatar">
                                <?php endif; ?>
                                <span class="hide-mobile"><?= e($currentUser['first_name']) ?></span>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="/kirish-royxatdan-otish.php" class="btn btn-primary btn-sm">Kirish</a>
                    <?php endif; ?>
                    
                    <a href="tel:<?= e(($siteContacts['phones'][0] ?? '')) ?>" class="phone-link hide-mobile">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                        </svg>
                        <?= e(explode(' ', $siteContacts['phones'][0] ?? '')[0]) ?>
                    </a>
                </div>
            </nav>
        </div>
    </header>
    
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('mobileMenuToggle');
            const navLinks = document.getElementById('navLinks');
            
            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    navLinks.classList.toggle('active');
                });
            }
            
            // Toast notification function
            window.showToast = function(message, type = 'info') {
                const container = document.getElementById('toastContainer');
                const toast = document.createElement('div');
                toast.className = 'toast ' + type;
                toast.textContent = message;
                container.appendChild(toast);
                
                setTimeout(function() {
                    toast.style.animation = 'slideIn 0.3s ease reverse';
                    setTimeout(function() {
                        toast.remove();
                    }, 300);
                }, 3000);
            };
            
            <?php
            // Session dan toast xabarni chiqarish
            $toast = getToast();
            if ($toast):
            ?>
            showToast('<?= e($toast['message']) ?>', '<?= e($toast['type']) ?>');
            <?php endif; ?>
        });
    </script>
