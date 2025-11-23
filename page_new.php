<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Získat slug ze GET parametru
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

try {
    // Získat stránku z databáze
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_published = 1");
    $stmt->execute([$slug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$page) {
        header('HTTP/1.0 404 Not Found');
        include '404.php';
        exit;
    }
    
    // Získat nastavení webu
    $settings = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Načtení rychlých odkazů pro footer
    try {
        $quickLinksStmt = $pdo->query("SELECT title, url, description FROM quick_links WHERE is_active = 1 ORDER BY position ASC LIMIT 3");
        $quickLinks = $quickLinksStmt->fetchAll();
    } catch (PDOException $e) {
        $quickLinks = []; // Fallback pokud tabulka neexistuje
    }
    
    // Výchozí hodnoty pro sociální sítě
    $settings = array_merge([
        'facebook_url' => '',
        'instagram_url' => ''
    ], $settings);
    
    // Načíst podstránky pokud má tato stránka boční menu
    $subpages = [];
    $parent_page = null;
    
    if ($page['has_sidebar_menu'] == 1) {
        // Tato stránka má boční menu - načti její podstránky
        $stmt = $pdo->prepare("SELECT title, slug, created_at FROM pages WHERE parent_slug = ? AND is_published = 1 ORDER BY title ASC");
        $stmt->execute([$page['slug']]);
        $subpages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif (!empty($page['parent_slug'])) {
        // Tato stránka je podstránka - načti hlavní stránku a všechny sourozence
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_published = 1 AND has_sidebar_menu = 1");
        $stmt->execute([$page['parent_slug']]);
        $parent_page = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($parent_page) {
            $stmt = $pdo->prepare("SELECT title, slug, created_at FROM pages WHERE parent_slug = ? AND is_published = 1 ORDER BY title ASC");
            $stmt->execute([$parent_page['slug']]);
            $subpages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
} catch (PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo "Chyba databáze: " . $e->getMessage();
    exit;
}

// Načíst menu
$menu = generateBootstrapMenu($pdo, $slug);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page['title']) ?> - <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></title>
    <meta name="description" content="<?= htmlspecialchars($page['meta_description'] ?? $page['excerpt'] ?? '') ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2d5016;      /* Zemitá zelená */
            --secondary-color: #4a7c59;    /* Lesní zelená */
            --accent-color: #6b8e23;       /* Olivová zelená */
            --text-dark: #2c3e50;
            --text-light: #6c757d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: linear-gradient(135deg, #f0f8e8 0%, #e8f5d8 100%);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: rgba(45, 80, 22, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700 !important;
            font-size: 1.4rem !important;
            color: white !important;
            text-decoration: none !important;
            display: flex !important;
            align-items: center !important;
            margin-right: 2rem !important;
        }
        
        .navbar-brand i {
            font-size: 1.4rem !important;
        }

        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.7rem 1.2rem !important;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 0 3px;
            position: relative;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            background: rgba(255,255,255,0.05);
            overflow: hidden;
        }
        
        /* Striped efekt pro navigation */
        .navbar-nav .nav-item:nth-child(odd) .nav-link {
            background: rgba(255,255,255,0.08);
        }
        
        .navbar-nav .nav-item:nth-child(even) .nav-link {
            background: rgba(0,0,0,0.08);
        }
        
        /* Shimmer efekt při hover */
        .navbar-nav .nav-link:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .navbar-nav .nav-link:hover:before {
            left: 100%;
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.25) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(255,255,255,0.1);
        }
        
        .navbar-nav .nav-link.active {
            color: white !important;
            background: rgba(255,255,255,0.3) !important;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(255,255,255,0.15);
        }
        
        /* Dropdown menu - Mobile friendly */
        .dropdown-menu {
            background: white;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 12px;
            margin-top: 0.5rem;
            padding: 0.5rem 0;
            min-width: 200px;
        }
        
        .dropdown-item {
            color: var(--text-dark) !important;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .dropdown-item:hover,
        .dropdown-item:focus {
            background: var(--primary-color) !important;
            color: white !important;
            transform: translateX(3px);
        }
        
        .dropdown-divider {
            margin: 0.5rem 1rem;
            border-color: rgba(45, 80, 22, 0.1);
        }
        
        /* Mobile menu toggle */
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2833, 37, 41, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            filter: invert(1);
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 6rem 0 4rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,0 1000,0 1000,80 0,100"/></svg>') no-repeat bottom center;
            background-size: cover;
        }

        .page-header-content {
            position: relative;
            z-index: 2;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        /* Main Content */
        .main-content {
            padding: 4rem 0;
            position: relative;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.1);
            position: relative;
        }

        .content-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 20px 20px 0 0;
        }
        
        .content-card h1, .content-card h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .content-card p {
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }
        
        .content-card img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 1.5rem 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Buttons */
        .btn {
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(45, 90, 57, 0.4);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Footer */
        footer {
            background: var(--primary-color);
            color: rgba(255, 255, 255, 0.9);
            padding: 40px 0 20px;
            margin-top: 80px;
        }
        
        footer h5 {
            color: white;
            margin-bottom: 20px;
        }
        
        footer a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        footer a:hover {
            color: white;
        }
        
        /* Mobile optimizations */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                margin-top: 1rem;
                border-top: 1px solid rgba(255,255,255,0.1);
                padding-top: 1rem;
            }
            
            .dropdown-menu {
                position: static !important;
                transform: none !important;
                background: rgba(45, 80, 22, 0.9) !important;
                border: 1px solid rgba(255,255,255,0.2) !important;
                margin: 0.5rem 0;
                box-shadow: none;
                border-radius: 8px;
            }
            
            .dropdown-menu .dropdown-item {
                color: white !important;
                padding: 0.75rem 1.5rem;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            
            .dropdown-menu .dropdown-item:hover {
                background: rgba(255,255,255,0.1) !important;
                color: white !important;
            }
            
            .dropdown-menu .dropdown-item:last-child {
                border-bottom: none;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .content-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 60px 0 40px;
            }
            
            .main-content {
                padding: 40px 0;
            }
        }
        
        /* Touch improvements for mobile */
        @media (hover: none) {
            .content-card:hover {
                transform: none;
            }
            
            .btn:hover {
                transform: none;
            }
        }
        
        /* MODERN SIDEBAR LAYOUT */
        .page-with-sidebar {
            display: flex !important;
            flex-direction: row !important;
            gap: 2rem !important;
            align-items: flex-start !important;
            max-width: 1200px !important;
            margin: 0 auto !important;
            padding: 0 20px !important;
        }
        
        .sidebar-menu {
            flex: 0 0 300px !important;
            order: 1 !important; /* Menu nalevo */
            position: sticky !important;
            top: 80px !important; /* Ještě vyšší pozice */
            height: fit-content !important;
            max-height: calc(100vh - 160px) !important; /* Více prostoru pro nadpis */
            overflow-y: auto !important;
        }
        
        /* STRIPED LINKS V OBSAHU STRÁNEK */
        .content-card a,
        .page-content-with-sidebar a {
            color: #667eea !important;
            text-decoration: none !important;
            padding: 2px 8px !important;
            border-radius: 6px !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08)) !important;
            border: 1px solid rgba(102, 126, 234, 0.15) !important;
            font-weight: 500 !important;
            display: inline-block !important;
            margin: 1px !important;
            overflow: hidden !important;
        }
        
        .content-card a:before,
        .page-content-with-sidebar a:before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: -100% !important;
            width: 100% !important;
            height: 100% !important;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.2), transparent) !important;
            transition: left 0.5s ease !important;
        }
        
        .content-card a:hover:before,
        .page-content-with-sidebar a:hover:before {
            left: 100% !important;
        }
        
        .content-card a:hover,
        .page-content-with-sidebar a:hover {
            color: white !important;
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
        }
        
        .page-content-with-sidebar {
            flex: 1 !important;
            order: 2 !important; /* Obsah napravo */
            min-width: 0 !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tree me-2"></i>
                <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?>
            </a>
            
            <!-- Hamburger menu pro mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php echo $menu; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content text-center">
                <h1 class="display-4 mb-3">
                    <?php if (!empty($page['icon'])): ?>
                        <i class="<?= htmlspecialchars($page['icon']) ?> me-3"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($page['title']) ?>
                </h1>
                <?php if (!empty($page['excerpt'])): ?>
                    <p class="lead"><?= htmlspecialchars($page['excerpt']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="main-content">
        <div class="container">
            <?php 
            $show_sidebar = ($page['has_sidebar_menu'] == 1 && !empty($subpages)) || ($parent_page && !empty($subpages));
            $menu_page = $parent_page ?: $page;
            ?>
            
            <?php if ($show_sidebar): ?>
                <!-- Layout s bočním menu -->
                <div class="page-with-sidebar">
                    <!-- Boční menu -->
                    <div class="sidebar-menu">
                        <div class="sidebar-header">
                            <h3><i class="fas fa-list me-2"></i><?= htmlspecialchars($menu_page['title']) ?></h3>
                        </div>
                        <nav class="sidebar-nav">
                            <!-- Hlavní stránka -->
                            <div class="sidebar-nav-item <?= $page['slug'] === $menu_page['slug'] ? 'active' : '' ?>">
                                <a href="page_new.php?slug=<?= htmlspecialchars($menu_page['slug']) ?>">
                                    <i class="fas fa-home me-2"></i>Úvod
                                </a>
                            </div>
                            <!-- Podstránky -->
                            <?php foreach ($subpages as $subpage): ?>
                                <div class="sidebar-nav-item <?= $page['slug'] === $subpage['slug'] ? 'active' : '' ?>">
                                    <a href="page_new.php?slug=<?= htmlspecialchars($subpage['slug']) ?>">
                                        <i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($subpage['title']) ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </nav>
                    </div>
                    
                    <!-- Obsah stránky -->
                    <div class="page-content-with-sidebar">
                        <div class="content-card">
                            <div class="content">
                                <?= $page['content'] ?>
                            </div>
                            
                            <?php if (!empty($page['updated_at'])): ?>
                                <div class="text-muted mt-4 pt-3 border-top">
                                    <small>
                                        <i class="fas fa-clock me-1"></i>
                                        Aktualizováno: <?= date('d.m.Y H:i', strtotime($page['updated_at'])) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Běžný layout bez bočního menu -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="content-card">
                            <div class="content">
                                <?= $page['content'] ?>
                            </div>
                            
                            <?php if (!empty($page['updated_at'])): ?>
                                <div class="text-muted mt-4 pt-3 border-top">
                                    <small>
                                        <i class="fas fa-clock me-1"></i>
                                        Aktualizováno: <?= date('d.m.Y H:i', strtotime($page['updated_at'])) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-leaf me-2"></i><?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></h5>
                    <p><?= htmlspecialchars($settings['site_description'] ?? 'Naturistický kemp - relaxace v harmonii s přírodou') ?></p>
                </div>
                <div class="col-md-3">
                    <h6>Rychlé odkazy</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Domů</a></li>
                        <li><a href="events.php">Akce</a></li>
                        <li><a href="gallery.php">Fotky okolí</a></li>
                        <?php if (!empty($quickLinks)): ?>
                            <?php foreach ($quickLinks as $link): ?>
                                <li><a href="<?= htmlspecialchars($link['url']) ?>" 
                                       title="<?= htmlspecialchars($link['description']) ?>"><?= htmlspecialchars($link['title']) ?></a></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a href="page_new.php?slug=o-organizaci">O organizaci</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Kontakt</h6>
                    <p><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($settings['contact_email'] ?? 'info@pohoda-antosovice.cz') ?></p>
                    <p><i class="fas fa-phone me-2"></i><?= htmlspecialchars($settings['contact_phone'] ?? '+420 123 456 789') ?></p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?>. Všechna práva vyhrazena.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Mobile menu auto-close -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Automaticky zavřít mobile menu po kliknutí na odkaz
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            const navbarToggler = document.querySelector('.navbar-toggler');
            
            navLinks.forEach(function(navLink) {
                navLink.addEventListener('click', function() {
                    if (navbarCollapse.classList.contains('show')) {
                        navbarToggler.click();
                    }
                });
            });
            
            // Dropdown support pro mobile
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    if (window.innerWidth < 992) { // Mobile breakpoint
                        if (navbarCollapse.classList.contains('show')) {
                            navbarToggler.click();
                        }
                    }
                });
            });
            
            // Zajistit správné zobrazení dropdown na mobilech
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth < 992) {
                        const dropdownMenu = toggle.nextElementSibling;
                        if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
                        }
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
