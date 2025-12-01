<?php
// Centrální funkce pro generování menu
function generateBootstrapMenu($pdo, $current_slug, $current_parent_slug = '') {
    try {
        // Načtení všech stránek (pouze s menu_order >= 0)
        $stmt = $pdo->prepare("SELECT id, title, slug, custom_url, parent_slug, icon, menu_order FROM pages WHERE is_published = 1 AND menu_order >= 0 ORDER BY menu_order ASC, title ASC");
        $stmt->execute();
        $all_pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Rozdělení na hlavní a podstránky
        $main_pages = [];
        $sub_pages = [];
        
        foreach ($all_pages as $page) {
            if (empty($page['parent_slug'])) {
                $main_pages[] = $page;
            } else {
                if (!isset($sub_pages[$page['parent_slug']])) {
                    $sub_pages[$page['parent_slug']] = [];
                }
                $sub_pages[$page['parent_slug']][] = $page;
            }
        }
        
        $menu_html = '';
        
        // Generování menu položek (z administrace)
        foreach ($main_pages as $page) {
            $has_submenu = isset($sub_pages[$page['slug']]);
            // Přesná shoda včetně kontroly, že current_slug není prázdný
            $is_active = (!empty($current_slug) && $current_slug === $page['slug']);
            
            if ($has_submenu) {
                // Stránka s podmenu
                $submenu_active = false;
                if (!empty($current_slug)) {
                    foreach ($sub_pages[$page['slug']] as $sub_page) {
                        if ($current_slug === $sub_page['slug']) {
                            $submenu_active = true;
                            break;
                        }
                    }
                }
                
                // Také aktivní když current_parent_slug odpovídá tomuto dropdownu
                $is_parent_active = (!empty($current_parent_slug) && $current_parent_slug === $page['slug']);
                
                // Aktivní pokud: 1) je to přesně tato stránka, 2) je aktivní její podstránka, 3) parent_slug ukazuje sem
                $active_class = ($is_active || $submenu_active || $is_parent_active) ? ' active' : '';
                
                $menu_html .= '<li class="nav-item dropdown">';
                $menu_html .= '<a class="nav-link dropdown-toggle' . $active_class . '" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                $menu_html .= htmlspecialchars($page['title']);
                $menu_html .= '</a>';
                $menu_html .= '<ul class="dropdown-menu">';
                
                // Hlavní stránka v dropdown
                $main_url = !empty($page['custom_url']) ? htmlspecialchars($page['custom_url']) : 'page_new.php?slug=' . htmlspecialchars($page['slug']);
                $menu_html .= '<li><a class="dropdown-item" href="' . $main_url . '">';
                $menu_html .= htmlspecialchars($page['title']) . ' - Úvod';
                $menu_html .= '</a></li>';
                $menu_html .= '<li><hr class="dropdown-divider"></li>';
                
                // Podstránky - nejdřív normální, pak custom_url oddělené
                $normal_sub_pages = [];
                $custom_sub_pages = [];
                
                foreach ($sub_pages[$page['slug']] as $sub_page) {
                    if (!empty($sub_page['custom_url'])) {
                        $custom_sub_pages[] = $sub_page;
                    } else {
                        $normal_sub_pages[] = $sub_page;
                    }
                }
                
                // Normální podstránky
                foreach ($normal_sub_pages as $sub_page) {
                    $sub_url = 'page_new.php?slug=' . htmlspecialchars($sub_page['slug']);
                    $menu_html .= '<li><a class="dropdown-item" href="' . $sub_url . '">';
                    $menu_html .= htmlspecialchars($sub_page['title']);
                    $menu_html .= '</a></li>';
                }
                
                // Oddělovač před custom_url položky
                if (!empty($custom_sub_pages)) {
                    $menu_html .= '<li><hr class="dropdown-divider"></li>';
                }
                
                // Custom URL podstránky
                foreach ($custom_sub_pages as $sub_page) {
                    $sub_url = htmlspecialchars($sub_page['custom_url']);
                    $menu_html .= '<li><a class="dropdown-item" href="' . $sub_url . '">';
                    if (!empty($sub_page['icon'])) {
                        $menu_html .= '<i class="' . htmlspecialchars($sub_page['icon']) . ' me-1"></i>';
                    }
                    $menu_html .= htmlspecialchars($sub_page['title']);
                    $menu_html .= '</a></li>';
                }
                
                $menu_html .= '</ul>';
                $menu_html .= '</li>';
            } else {
                // Jednoduchá stránka - pouze aktivní pokud je to přesně tato stránka
                $active_class = (!empty($current_slug) && $current_slug === $page['slug']) ? ' active' : '';
                $page_url = !empty($page['custom_url']) ? htmlspecialchars($page['custom_url']) : 'page_new.php?slug=' . htmlspecialchars($page['slug']);
                $menu_html .= '<li class="nav-item">';
                $menu_html .= '<a class="nav-link' . $active_class . '" href="' . $page_url . '">';
                $menu_html .= htmlspecialchars($page['title']);
                $menu_html .= '</a>';
                $menu_html .= '</li>';
            }
        }
        
        // Kalendář akcí odkaz
        $events_active = ($current_slug === 'events') ? ' active' : '';
        $menu_html .= '<li class="nav-item">';
        $menu_html .= '<a class="nav-link' . $events_active . '" href="events.php">';
        $menu_html .= 'Naše akce</a>';
        $menu_html .= '</li>';
        
        // Oddělovač před Domů
        $menu_html .= '<li class="nav-item nav-divider"><hr style="border-color: rgba(255,255,255,0.3); margin: 0.5rem 1rem;"></li>';
        
        // Domů odkaz - na konci menu
        $home_active = ($current_slug === '' || $current_slug === 'home') ? ' active' : '';
        $menu_html .= '<li class="nav-item">';
        $menu_html .= '<a class="nav-link' . $home_active . '" href="index.php">';
        $menu_html .= 'Domů</a>';
        $menu_html .= '</li>';
        
        return $menu_html;
        
    } catch (Exception $e) {
        // Fallback menu pokud selže databáze
        return '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=poslani">Poslání</a></li>' .
               '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=o-organizaci">Organizace</a></li>' .
               '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=kontakt">Kontakt</a></li>' .
               '<li class="nav-item"><a class="nav-link" href="events.php">Naše akce</a></li>' .
               '<li class="nav-item"><a class="nav-link" href="index.php">Domů</a></li>';
    }
}

// Funkce pro načtení nastavení webu
function getWebSettings($pdo) {
    $settings = [
        'site_title' => 'Pohoda Antošovice',
        'site_description' => 'Naturistický kemp - relaxace v harmonii s přírodou',
        'contact_email' => 'info@pohoda-antosovice.cz',
        'contact_phone' => '+420 123 456 789',
        'facebook_url' => '',
        'instagram_url' => ''
    ];
    
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // Použije výchozí nastavení
    }
    
    return $settings;
}

// Funkce pro načtení fotek galerie
function getGalleryPhotos($pdo, $featured_only = false, $limit = null) {
    try {
        $sql = "SELECT * FROM gallery_photos WHERE is_published = 1";
        
        if ($featured_only) {
            $sql .= " AND is_featured = 1";
        }
        
        $sql .= " ORDER BY sort_order ASC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Funkce pro zobrazení galerie fotek
function displayPhotoGallery($pdo, $featured_only = false, $limit = null, $show_title = true, $grid_cols = 3) {
    $photos = getGalleryPhotos($pdo, $featured_only, $limit);
    
    if (empty($photos)) {
        return '';
    }
    
    $col_class = '';
    switch ($grid_cols) {
        case 2: $col_class = 'col-md-6'; break;
        case 3: $col_class = 'col-md-4'; break;
        case 4: $col_class = 'col-md-3'; break;
        case 6: $col_class = 'col-md-2'; break;
        default: $col_class = 'col-md-4'; break;
    }
    
    $html = '';
    
    if ($show_title) {
        $title = $featured_only ? 'Hlavní fotky okolí' : 'Galerie fotek okolí';
        $html .= '<div class="mb-4">';
        $html .= '<h2 class="text-center mb-4" style="color: #6f9183;">' . $title . '</h2>';
        $html .= '</div>';
    }
    
    $html .= '<div class="row g-4">';
    
    foreach ($photos as $photo) {
        $html .= '<div class="' . $col_class . '">';
        $html .= '<div class="card h-100 shadow-sm gallery-item">';
        $html .= '<div class="position-relative overflow-hidden">';
        $html .= '<img src="' . htmlspecialchars($photo['file_path']) . '" ';
        $html .= 'class="card-img-top gallery-image" ';
        $html .= 'alt="' . htmlspecialchars($photo['alt_text'] ?: $photo['title']) . '" ';
        $html .= 'style="height: 250px; object-fit: cover; transition: transform 0.3s ease; cursor: pointer;" ';
        $html .= 'onclick="openGalleryModal(\'' . htmlspecialchars($photo['file_path'], ENT_QUOTES) . '\', \'' . htmlspecialchars($photo['title'], ENT_QUOTES) . '\', \'' . htmlspecialchars($photo['description'], ENT_QUOTES) . '\')">';
        
        if ($photo['is_featured']) {
            $html .= '<div class="position-absolute top-0 end-0 p-2">';
            $html .= '<span class="badge bg-warning text-dark">';
            $html .= '<i class="bi bi-star-fill"></i>';
            $html .= '</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        if ($photo['title'] || $photo['description']) {
            $html .= '<div class="card-body">';
            if ($photo['title']) {
                $html .= '<h5 class="card-title">' . htmlspecialchars($photo['title']) . '</h5>';
            }
            if ($photo['description']) {
                $description = strlen($photo['description']) > 100 ? 
                    substr($photo['description'], 0, 100) . '...' : 
                    $photo['description'];
                $html .= '<p class="card-text text-muted">' . htmlspecialchars($description) . '</p>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>