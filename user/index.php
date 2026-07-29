<?php
/**
 * User Panel - Bosh sahifa
 * /user/ ga kirganda boshqaruvga yo'naltirish
 */

require_once '../includes/init.php';

// Agar user kirish qilmagan bo'lsa, login sahifasiga yo'naltirish
require_once '../includes/auth.php';
if (!isLoggedIn() || !isUser()) {
    redirect('../kirish-royxatdan-otish.php');
}

// Boshqaruv sahifasiga yo'naltirish
redirect('boshqaruv.php');
