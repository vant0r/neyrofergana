<?php
/**
 * Footer fayli - umumiy sahifa oxiri
 * Barcha JavaScript kodlar shu yerda joylashgan
 */
?>
    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <!-- Logo va ma'lumot -->
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="<?= getLogoUrl('white') ?>" alt="<?= e($siteSettings['site_name'] ?? 'Shifo Klinikasi') ?>">
                        <h3><?= e($siteSettings['site_name'] ?? 'Shifo Klinikasi') ?></h3>
                    </div>
                    <p>Sifatli tibbiy xizmat va malakali shifokorlar bilan sog'lig'ingizni ishonchli qo'llarga topshiring.</p>
                    <div class="social-links">
                        <?php if (!empty($siteContacts['social_media']['telegram'])): ?>
                            <a href="<?= e($siteContacts['social_media']['telegram']) ?>" target="_blank" aria-label="Telegram">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.223-.548.223l.188-2.85 5.18-4.686c.223-.198-.054-.306-.346-.106l-6.4 4.02-2.76-.86c-.6-.19-.612-.6.127-.89l10.782-4.156c.5-.187.937.114.777.834z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($siteContacts['social_media']['instagram'])): ?>
                            <a href="<?= e($siteContacts['social_media']['instagram']) ?>" target="_blank" aria-label="Instagram">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($siteContacts['social_media']['facebook'])): ?>
                            <a href="<?= e($siteContacts['social_media']['facebook']) ?>" target="_blank" aria-label="Facebook">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tezkor havolalar -->
                <div class="footer-section">
                    <h4>Tezkor havolalar</h4>
                    <ul>
                        <li><a href="/index.php">Bosh sahifa</a></li>
                        <li><a href="/haqimizda.php">Klinika haqida</a></li>
                        <li><a href="/xizmatlar.php">Xizmatlar</a></li>
                        <li><a href="/shifokorlar.php">Shifokorlar</a></li>
                        <li><a href="/yangiliklar.php">Yangiliklar</a></li>
                        <li><a href="/aloqa.php">Aloqa</a></li>
                    </ul>
                </div>
                
                <!-- Xizmatlar -->
                <div class="footer-section">
                    <h4>Xizmatlar</h4>
                    <ul>
                        <li><a href="/xizmatlar.php?category=diagnostika">Diagnostika</a></li>
                        <li><a href="/xizmatlar.php?category=stomatologiya">Stomatologiya</a></li>
                        <li><a href="/xizmatlar.php?category=terapiya">Terapiya</a></li>
                        <li><a href="/xizmatlar.php?category=xirurgiya">Xirurgiya</a></li>
                        <li><a href="/navbat-olish.php">Navbatga yozilish</a></li>
                    </ul>
                </div>
                
                <!-- Aloqa -->
                <div class="footer-section">
                    <h4>Aloqa</h4>
                    <ul class="contact-list">
                        <li>
                            <strong>Telefon:</strong>
                            <a href="tel:<?= e($siteContacts['phones'][0] ?? '') ?>"><?= e($siteContacts['phones'][0] ?? '') ?></a>
                        </li>
                        <?php if (!empty($siteContacts['phones'][1])): ?>
                        <li>
                            <a href="tel:<?= e($siteContacts['phones'][1]) ?>"><?= e($siteContacts['phones'][1]) ?></a>
                        </li>
                        <?php endif; ?>
                        <li>
                            <strong>Email:</strong>
                            <a href="mailto:<?= e($siteContacts['email'] ?? '') ?>"><?= e($siteContacts['email'] ?? '') ?></a>
                        </li>
                        <li>
                            <strong>Manzil:</strong>
                            <?= e($siteContacts['address'] ?? '') ?>
                        </li>
                        <li>
                            <strong>Ish vaqti:</strong>
                            Dushanba-Juma: <?= e($siteContacts['work_hours']['monday_friday'] ?? '') ?><br>
                            Shanba: <?= e($siteContacts['work_hours']['saturday'] ?? '') ?><br>
                            Yakshanba: <?= e($siteContacts['work_hours']['sunday'] ?? '') ?>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= e($siteSettings['site_name'] ?? 'Shifo Klinikasi') ?>. Barcha huquqlar himoyalangan.</p>
                <div class="footer-links">
                    <a href="/maxfiylik.php">Maxfiylik siyosati</a>
                    <a href="/shartlar.php">Foydalanish shartlari</a>
                </div>
            </div>
        </div>
        
        <style>
            .site-footer {
                background: rgba(29, 29, 31, 0.95);
                color: #f5f5f7;
                padding: 60px 0 30px;
                margin-top: 80px;
            }
            
            .footer-content {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 40px;
                margin-bottom: 40px;
            }
            
            .footer-section h3,
            .footer-section h4 {
                color: #f5f5f7;
                margin-bottom: 20px;
                font-size: 1.25rem;
            }
            
            .footer-logo {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
            }
            
            .footer-logo img {
                height: 40px;
                filter: brightness(0) invert(1);
            }
            
            .footer-section p {
                color: #86868b;
                line-height: 1.8;
            }
            
            .footer-section ul {
                list-style: none;
            }
            
            .footer-section ul li {
                margin-bottom: 12px;
            }
            
            .footer-section a {
                color: #86868b;
                transition: color 0.3s ease;
            }
            
            .footer-section a:hover {
                color: #5ac8fa;
            }
            
            .social-links {
                display: flex;
                gap: 12px;
                margin-top: 20px;
            }
            
            .social-links a {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                transition: all 0.3s ease;
            }
            
            .social-links a:hover {
                background: var(--primary-color);
                transform: translateY(-3px);
            }
            
            .contact-list strong {
                display: block;
                color: #f5f5f7;
                margin-bottom: 4px;
            }
            
            .footer-bottom {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-top: 30px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .footer-bottom p {
                color: #86868b;
                font-size: 0.875rem;
            }
            
            .footer-links {
                display: flex;
                gap: 20px;
            }
            
            .footer-links a {
                color: #86868b;
                font-size: 0.875rem;
            }
            
            @media (max-width: 768px) {
                .footer-content {
                    grid-template-columns: 1fr;
                    gap: 30px;
                }
                
                .footer-bottom {
                    flex-direction: column;
                    text-align: center;
                }
            }
        </style>
    </footer>
    
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Form validation helper
        window.validateForm = function(formId) {
            const form = document.getElementById(formId);
            if (!form) return false;
            
            let isValid = true;
            const inputs = form.querySelectorAll('[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = 'var(--error-color)';
                } else {
                    input.style.borderColor = 'rgba(0, 0, 0, 0.1)';
                }
            });
            
            return isValid;
        };
        
        // AJAX helper
        window.ajaxRequest = async function(url, method = 'GET', data = null) {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }
            
            try {
                const response = await fetch(url, options);
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Xatolik yuz berdi');
                }
                
                return result;
            } catch (error) {
                console.error('AJAX error:', error);
                showToast(error.message, 'error');
                throw error;
            }
        };
        
        // Loading state for buttons
        window.setButtonLoading = function(button, isLoading) {
            if (isLoading) {
                button.disabled = true;
                button.dataset.originalText = button.textContent;
                button.innerHTML = '<span class="spinner" style="width:20px;height:20px;border-width:2px;display:inline-block;vertical-align:middle"></span> Yuklanmoqda...';
            } else {
                button.disabled = false;
                button.textContent = button.dataset.originalText || 'Yuborish';
            }
        };
    </script>
</body>
</html>
