            </main>
        </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const userSidebar = document.getElementById('userSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        mobileMenuToggle.addEventListener('click', function() {
            userSidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            userSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
        
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show ' + type;
            
            setTimeout(function() {
                toast.classList.remove('show');
            }, 3000);
        }
        
        // Auto-hide toast after 3 seconds
        <?php if (isset($_SESSION['success'])): ?>
            showToast('<?= htmlspecialchars($_SESSION['success']) ?>', 'success');
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            showToast('<?= htmlspecialchars($_SESSION['error']) ?>', 'error');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        // Form validation helper
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = 'var(--danger)';
                    isValid = false;
                } else {
                    input.style.borderColor = 'var(--border)';
                }
            });
            
            return isValid;
        }
        
        // Confirm delete action
        function confirmDelete(message = 'Haqiqatan ham o\'chirmoqchimisiz?') {
            return confirm(message);
        }
    </script>
</body>
</html>
