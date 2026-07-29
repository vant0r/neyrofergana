<?php
/**
 * Admin Panel Footer
 * Klinika Boshqaruv Paneli - Pastki qism va yopish
 */

if (!defined('ACCESS_ALLOWED')) {
    die('To\'g\'ridan-to\'g\'ri kirish taqiqlangan');
}
?>
            </div>
        </main>
    </div>

    <script>
        // Global admin functions
        window.adminHelpers = {
            // Confirm delete action
            confirmDelete: function(message = 'O\'chirishni xohlaysizmi?') {
                return confirm(message);
            },

            // AJAX request helper
            request: async function(url, method = 'GET', data = null) {
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
                    showNotification(error.message, 'error');
                    throw error;
                }
            },

            // Form submission with AJAX
            submitForm: async function(formId, url) {
                const form = document.getElementById(formId);
                if (!form) return;

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                try {
                    const result = await this.request(url, 'POST', data);
                    showNotification(result.message || 'Muvaffaqiyatli!', 'success');
                    
                    if (result.redirect) {
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 1000);
                    }
                    
                    return result;
                } catch (error) {
                    return null;
                }
            }
        };

        // Helper shortcuts
        const { confirmDelete, request, submitForm } = window.adminHelpers;
    </script>
</body>
</html>
