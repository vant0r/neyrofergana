<?php
/**
 * Validatsiya funksiyalari
 * Kiruvchi ma'lumotlarni tozalash va tekshirish
 */

/**
 * Email validatsiyasi
 */
function validateEmail($email) {
    if (empty($email)) {
        return ['valid' => false, 'error' => 'Email majburiy'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'error' => 'Noto\'g\'ri email formati'];
    }
    
    if (strlen($email) > 150) {
        return ['valid' => false, 'error' => 'Email juda uzun'];
    }
    
    return ['valid' => true];
}

/**
 * Telefon raqam validatsiyasi
 */
function validatePhone($phone) {
    if (empty($phone)) {
        return ['valid' => false, 'error' => 'Telefon raqami majburiy'];
    }
    
    // Faqat raqamlar va + belgisi
    $clean = preg_replace('/[^0-9+]/', '', $phone);
    
    // O'zbekiston formati: +998XXXXXXXXX
    if (!preg_match('/^\+998[0-9]{9}$/', $clean) && !preg_match('/^998[0-9]{9}$/', $clean)) {
        return ['valid' => false, 'error' => 'Noto\'g\'ri telefon formati (+998XXXXXXXXX)'];
    }
    
    return ['valid' => true, 'clean' => $clean];
}

/**
 * Parol validatsiyasi
 */
function validatePassword($password, $minLength = 8) {
    if (empty($password)) {
        return ['valid' => false, 'error' => 'Parol majburiy'];
    }
    
    if (strlen($password) < $minLength) {
        return ['valid' => false, 'error' => 'Parol kamida ' . $minLength . ' belgi bo\'lishi kerak'];
    }
    
    if (strlen($password) > 128) {
        return ['valid' => false, 'error' => 'Parol juda uzun'];
    }
    
    // Katta harf, kichik harf, raqam tekshiruvi
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'error' => 'Parol kamida bitta katta harf bo\'lishi kerak'];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'error' => 'Parol kamida bitta kichik harf bo\'lishi kerak'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'error' => 'Parol kamida bitta raqam bo\'lishi kerak'];
    }
    
    return ['valid' => true];
}

/**
 * Ism validatsiyasi
 */
function validateName($name, $fieldName = 'Ism') {
    if (empty($name)) {
        return ['valid' => false, 'error' => $fieldName . ' majburiy'];
    }
    
    if (strlen($name) < 2) {
        return ['valid' => false, 'error' => $fieldName . ' juda qisqa'];
    }
    
    if (strlen($name) > 100) {
        return ['valid' => false, 'error' => $fieldName . ' juda uzun'];
    }
    
    // Faqat harflar, bo'sh joy, tire va apostrof
    if (!preg_match('/^[a-zA-Z\s\'-]+$/u', $name)) {
        return ['valid' => false, 'error' => $fieldName . ' faqat harflardan iborat bo\'lishi kerak'];
    }
    
    return ['valid' => true];
}

/**
 * Sana validatsiyasi
 */
function validateDate($date, $format = 'Y-m-d') {
    if (empty($date)) {
        return ['valid' => false, 'error' => 'Sana majburiy'];
    }
    
    $dateTime = DateTime::createFromFormat($format, $date);
    
    if (!$dateTime || $dateTime->format($format) !== $date) {
        return ['valid' => false, 'error' => 'Noto\'g\'ri sana formati'];
    }
    
    return ['valid' => true, 'datetime' => $dateTime];
}

/**
 * Vaqt validatsiyasi
 */
function validateTime($time) {
    if (empty($time)) {
        return ['valid' => false, 'error' => 'Vaqt majburiy'];
    }
    
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
        return ['valid' => false, 'error' => 'Noto\'g\'ri vaqt formati (HH:MM)'];
    }
    
    return ['valid' => true];
}

/**
 * Integer validatsiyasi
 */
function validateInteger($value, $min = null, $max = null, $fieldName = 'Qiymat') {
    if (!is_numeric($value)) {
        return ['valid' => false, 'error' => $fieldName . ' son bo\'lishi kerak'];
    }
    
    $int = (int)$value;
    
    if ($min !== null && $int < $min) {
        return ['valid' => false, 'error' => $fieldName . ' kamida ' . $min . ' bo\'lishi kerak'];
    }
    
    if ($max !== null && $int > $max) {
        return ['valid' => false, 'error' => $fieldName . ' ko\'pi bilan ' . $max . ' bo\'lishi kerak'];
    }
    
    return ['valid' => true, 'value' => $int];
}

/**
 * Float/Decimal validatsiyasi
 */
function validateFloat($value, $min = null, $max = null, $fieldName = 'Qiymat') {
    if (!is_numeric($value)) {
        return ['valid' => false, 'error' => $fieldName . ' son bo\'lishi kerak'];
    }
    
    $float = (float)$value;
    
    if ($min !== null && $float < $min) {
        return ['valid' => false, 'error' => $fieldName . ' kamida ' . $min . ' bo\'lishi kerak'];
    }
    
    if ($max !== null && $float > $max) {
        return ['valid' => false, 'error' => $fieldName . ' ko\'pi bilan ' . $max . ' bo\'lishi kerak'];
    }
    
    return ['valid' => true, 'value' => $float];
}

/**
 * Matn uzunligi validatsiyasi
 */
function validateTextLength($text, $min = null, $max = null, $fieldName = 'Matn') {
    if ($min !== null && strlen($text) < $min) {
        return ['valid' => false, 'error' => $fieldName . ' kamida ' . $min . ' belgi bo\'lishi kerak'];
    }
    
    if ($max !== null && strlen($text) > $max) {
        return ['valid' => false, 'error' => $fieldName . ' ko\'pi bilan ' . $max . ' belgi bo\'lishi kerak'];
    }
    
    return ['valid' => true];
}

/**
 * Majburiy maydon validatsiyasi
 */
function validateRequired($value, $fieldName = 'Maydon') {
    if (empty($value) && $value !== '0') {
        return ['valid' => false, 'error' => $fieldName . ' majburiy'];
    }
    
    return ['valid' => true];
}

/**
 * URL validatsiyasi
 */
function validateUrl($url, $required = false) {
    if (empty($url)) {
        if ($required) {
            return ['valid' => false, 'error' => 'URL majburiy'];
        }
        return ['valid' => true];
    }
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['valid' => false, 'error' => 'Noto\'g\'ri URL formati'];
    }
    
    return ['valid' => true];
}

/**
 * Fayl kengaytmasi validatsiyasi
 */
function validateFileExtension($filename, $allowedExtensions) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowedExtensions)) {
        return ['valid' => false, 'error' => 'Faqat quyidagi formatlar ruxsat etiladi: ' . implode(', ', $allowedExtensions)];
    }
    
    return ['valid' => true, 'extension' => $ext];
}

/**
 * Reyting validatsiyasi (1-5)
 */
function validateRating($rating) {
    return validateInteger($rating, 1, 5, 'Reyting');
}

/**
 * Forma ma'lumotlarini to'liq validatsiya qilish
 */
function validateForm($data, $rules) {
    $errors = [];
    $validated = [];
    
    foreach ($rules as $field => $ruleSet) {
        $value = $data[$field] ?? null;
        
        foreach ($ruleSet as $rule) {
            $result = call_user_func_array($rule['callback'], [$value] + ($rule['params'] ?? []));
            
            if (!$result['valid']) {
                $errors[$field] = $result['error'];
                break;
            }
            
            // Agar valid bo'lsa va clean qiymat bo'lsa, saqlash
            if (isset($result['value']) || isset($result['clean'])) {
                $validated[$field] = $result['value'] ?? $result['clean'];
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'validated' => $validated
    ];
}

/**
 * Xato xabarlarni chiqarish HTML formatda
 */
function displayErrors($errors) {
    if (empty($errors)) {
        return '';
    }
    
    $html = '<div class="error-messages">';
    foreach ($errors as $field => $error) {
        $html .= '<p class="error-message">' . e($error) . '</p>';
    }
    $html .= '</div>';
    
    return $html;
}

/**
 * Validatsiya xatolarini session ga saqlash
 */
function setValidationErrors($errors) {
    $_SESSION['validation_errors'] = $errors;
}

/**
 * Session dan validatsiya xatolarini olish
 */
function getValidationErrors() {
    $errors = $_SESSION['validation_errors'] ?? [];
    unset($_SESSION['validation_errors']);
    return $errors;
}

/**
 * Eski forma ma'lumotlarini session ga saqlash
 */
function setOldInput($data) {
    $_SESSION['old_input'] = $data;
}

/**
 * Eski forma ma'lumotlarini olish
 */
function getOldInput($field = null) {
    $old = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);
    
    if ($field !== null) {
        return $old[$field] ?? '';
    }
    
    return $old;
}
