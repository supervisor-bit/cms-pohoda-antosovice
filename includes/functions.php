<?php
// Centrální funkce pro generování menu
function generateBootstrapMenu($pdo, $current_slug) {
    try {
        // Načtení všech stránek (pouze s menu_order >= 0)
        $stmt = $pdo->prepare("SELECT id, title, slug, parent_slug, icon, menu_order FROM pages WHERE is_published = 1 AND menu_order >= 0 ORDER BY menu_order ASC, title ASC");
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
        
        // Domů odkaz
        $home_active = ($current_slug === '' || $current_slug === 'home') ? ' active' : '';
        $menu_html .= '<li class="nav-item">';
        $menu_html .= '<a class="nav-link' . $home_active . '" href="index.php">';
        $menu_html .= 'Domů</a>';
        $menu_html .= '</li>';
        
        // Generování menu položek
        foreach ($main_pages as $page) {
            $has_submenu = isset($sub_pages[$page['slug']]);
            $is_active = ($current_slug === $page['slug']);
            
            if ($has_submenu) {
                // Stránka s podmenu
                $submenu_active = false;
                foreach ($sub_pages[$page['slug']] as $sub_page) {
                    if ($current_slug === $sub_page['slug']) {
                        $submenu_active = true;
                        break;
                    }
                }
                
                $menu_html .= '<li class="nav-item dropdown">';
                $menu_html .= '<a class="nav-link dropdown-toggle' . ($is_active || $submenu_active ? ' active' : '') . '" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                $menu_html .= htmlspecialchars($page['title']);
                $menu_html .= '</a>';
                $menu_html .= '<ul class="dropdown-menu">';
                
                // Hlavní stránka v dropdown
                $menu_html .= '<li><a class="dropdown-item" href="page_new.php?slug=' . htmlspecialchars($page['slug']) . '">';
                $menu_html .= htmlspecialchars($page['title']) . ' - Úvod';
                $menu_html .= '</a></li>';
                $menu_html .= '<li><hr class="dropdown-divider"></li>';
                
                // Podstránky
                foreach ($sub_pages[$page['slug']] as $sub_page) {
                    $menu_html .= '<li><a class="dropdown-item" href="page_new.php?slug=' . htmlspecialchars($sub_page['slug']) . '">';
                    $menu_html .= htmlspecialchars($sub_page['title']);
                    $menu_html .= '</a></li>';
                }
                
                $menu_html .= '</ul>';
                $menu_html .= '</li>';
            } else {
                // Jednoduchá stránka
                $active_class = $is_active ? ' active' : '';
                $menu_html .= '<li class="nav-item">';
                $menu_html .= '<a class="nav-link' . $active_class . '" href="page_new.php?slug=' . htmlspecialchars($page['slug']) . '">';
                $menu_html .= htmlspecialchars($page['title']);
                $menu_html .= '</a>';
                $menu_html .= '</li>';
            }
        }
        
        return $menu_html;
        
    } catch (Exception $e) {
        // Fallback menu pokud selže databáze
        return '<li class="nav-item"><a class="nav-link" href="index.php">Domů</a></li>' .
               '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=o-mist">O místě</a></li>' .
               '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=kontakt">Kontakt</a></li>' .
               '<li class="nav-item"><a class="nav-link" href="admin/">Admin</a></li>';
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
?>