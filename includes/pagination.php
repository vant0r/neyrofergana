<?php
/**
 * Paginatsiya (sahifalash) funksiyasi
 * 
 * Ushbu fayl ro'yxatlarni sahifalarga ajratish uchun ishlatiladi.
 * 
 * @param int $total_items - Jami elementlar soni
 * @param int $items_per_page - Har bir sahifadagi elementlar soni
 * @param int $current_page - Hozirgi sahifa raqami
 * @param string $base_url - Asosiy URL (masalan: ?page=)
 * @return array - Paginatsiya ma'lumotlari
 */

function generatePagination($total_items, $items_per_page = 10, $current_page = 1, $base_url = '?page=') {
    // Jami sahifalar sonini hisoblash
    $total_pages = ceil($total_items / $items_per_page);
    
    // Hozirgi sahifani tekshirish
    $current_page = max(1, min($current_page, $total_pages));
    
    // OFFSET hisoblash
    $offset = ($current_page - 1) * $items_per_page;
    
    // Ko'rsatiladigan sahifa tugmalari soni
    $visible_pages = 5;
    
    // Boshlanish va tugash sahifalarini aniqlash
    $start_page = max(1, $current_page - floor($visible_pages / 2));
    $end_page = min($total_pages, $start_page + $visible_pages - 1);
    
    // Agar oxirgi sahifaga yaqin bo'lsa, boshlanishni qayta hisoblash
    if ($end_page - $start_page + 1 < $visible_pages) {
        $start_page = max(1, $end_page - $visible_pages + 1);
    }
    
    // Natija массиви
    $pagination = [
        'total_items' => $total_items,
        'items_per_page' => $items_per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => $offset,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'prev_page' => $current_page > 1 ? $current_page - 1 : null,
        'next_page' => $current_page < $total_pages ? $current_page + 1 : null,
        'start_page' => $start_page,
        'end_page' => $end_page,
        'pages' => []
    ];
    
    // Sahifa raqamlarini qo'shish
    for ($i = $start_page; $i <= $end_page; $i++) {
        $pagination['pages'][] = [
            'number' => $i,
            'is_current' => $i === $current_page,
            'url' => $base_url . $i
        ];
    }
    
    // Birinchi va oxirgi sahifalarni qo'shish (agar kerak bo'lsa)
    if ($start_page > 1) {
        array_unshift($pagination['pages'], [
            'number' => 1,
            'is_current' => false,
            'url' => $base_url . '1',
            'is_first' => true
        ]);
        
        if ($start_page > 2) {
            array_unshift($pagination['pages'], [
                'number' => null,
                'is_ellipsis' => true
            ]);
        }
    }
    
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $pagination['pages'][] = [
                'number' => null,
                'is_ellipsis' => true
            ];
        }
        
        $pagination['pages'][] = [
            'number' => $total_pages,
            'is_current' => false,
            'url' => $base_url . $total_pages,
            'is_last' => true
        ];
    }
    
    return $pagination;
}

/**
 * Paginatsiya HTML kodini generatsiya qilish
 * Apple Style & Glassmorphism dizayn bilan
 * 
 * @param array $pagination - generatePagination() natijasi
 * @return string - HTML kod
 */
function renderPagination($pagination) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<nav class="pagination-container" aria-label="Sahifalash">';
    $html .= '<ul class="pagination-list">';
    
    // Oldingi sahifa tugmasi
    if ($pagination['has_prev']) {
        $html .= '<li class="pagination-item">';
        $html .= '<a href="' . htmlspecialchars($pagination['base_url'] ?? '?page=') . $pagination['prev_page'] . '" class="pagination-link pagination-prev" aria-label="Oldingi sahifa">';
        $html .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
        $html .= '<path d="M15 18l-6-6 6-6"/>';
        $html .= '</svg>';
        $html .= '</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="pagination-item pagination-disabled">';
        $html .= '<span class="pagination-link pagination-prev" aria-hidden="true">';
        $html .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
        $html .= '<path d="M15 18l-6-6 6-6"/>';
        $html .= '</svg>';
        $html .= '</span>';
        $html .= '</li>';
    }
    
    // Sahifa raqamlari
    foreach ($pagination['pages'] as $page) {
        if (isset($page['is_ellipsis']) && $page['is_ellipsis']) {
            $html .= '<li class="pagination-item pagination-ellipsis">';
            $html .= '<span class="pagination-ellipsis">…</span>';
            $html .= '</li>';
            continue;
        }
        
        $is_current = $page['is_current'] ?? false;
        $html .= '<li class="pagination-item' . ($is_current ? ' pagination-active' : '') . '">';
        
        if ($is_current) {
            $html .= '<span class="pagination-link pagination-current">' . $page['number'] . '</span>';
        } else {
            $html .= '<a href="' . htmlspecialchars($page['url']) . '" class="pagination-link">';
            $html .= $page['number'];
            $html .= '</a>';
        }
        
        $html .= '</li>';
    }
    
    // Keyingi sahifa tugmasi
    if ($pagination['has_next']) {
        $html .= '<li class="pagination-item">';
        $html .= '<a href="' . htmlspecialchars($pagination['base_url'] ?? '?page=') . $pagination['next_page'] . '" class="pagination-link pagination-next" aria-label="Keyingi sahifa">';
        $html .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
        $html .= '<path d="M9 18l6-6-6-6"/>';
        $html .= '</svg>';
        $html .= '</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="pagination-item pagination-disabled">';
        $html .= '<span class="pagination-link pagination-next" aria-hidden="true">';
        $html .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
        $html .= '<path d="M9 18l6-6-6-6"/>';
        $html .= '</svg>';
        $html .= '</span>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * LIMIT va OFFSET qiymatlarini olish
 * SQL so'rovlar uchun
 * 
 * @param array $pagination - generatePagination() natijasi
 * @return array - ['limit' => int, 'offset' => int]
 */
function getLimitOffset($pagination) {
    return [
        'limit' => $pagination['items_per_page'],
        'offset' => $pagination['offset']
    ];
}

/**
 * Paginatsiya CSS stillari (header.php ga qo'shish uchun)
 */
function getPaginationCSS() {
    return '
/* Paginatsiya stillari */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 2rem 0;
    padding: 0 1rem;
}

.pagination-list {
    display: flex;
    list-style: none;
    gap: 0.5rem;
    padding: 0;
    margin: 0;
    align-items: center;
}

.pagination-item {
    display: flex;
    align-items: center;
    justify-content: center;
}

.pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #1d1d1f;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.pagination-link:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.pagination-current,
.pagination-active .pagination-link {
    background: #0071e3;
    color: white;
    border-color: #0071e3;
    box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
}

.pagination-active .pagination-link:hover {
    background: #0077ed;
    transform: none;
}

.pagination-disabled .pagination-link {
    opacity: 0.4;
    cursor: not-allowed;
    pointer-events: none;
}

.pagination-ellipsis {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    color: #86868b;
    font-size: 18px;
    letter-spacing: 2px;
}

.pagination-prev svg,
.pagination-next svg {
    width: 18px;
    height: 18px;
}

@media (max-width: 768px) {
    .pagination-link {
        min-width: 36px;
        height: 36px;
        font-size: 14px;
        padding: 0 8px;
    }
    
    .pagination-list {
        gap: 0.3rem;
    }
}

@media (max-width: 480px) {
    .pagination-container {
        margin: 1.5rem 0;
    }
    
    .pagination-link {
        min-width: 32px;
        height: 32px;
        font-size: 13px;
    }
}
';
}
