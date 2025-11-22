<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Homepage logika - nepotřebujeme slug ze GET
try {
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
    
    // Výchozí hodnoty
    $settings = array_merge([
        'site_title' => 'Pohoda Antošovice',
        'site_description' => 'Naturistický kemp - relaxace v harmonii s přírodou',
        'contact_email' => 'info@pohoda-antosovice.cz',
        'contact_phone' => '+420 123 456 789',
        'facebook_url' => '',
        'instagram_url' => ''
    ], $settings);
    
    // Načtení nejnovějších příspěvků
    $stmt = $pdo->prepare("SELECT title, slug, excerpt, featured_image, created_at FROM posts WHERE is_published = 1 ORDER BY created_at DESC LIMIT 6");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $settings = [
        'site_title' => 'Pohoda Antošovice',
        'site_description' => 'Naturistický kemp - relaxace v harmonii s přírodou',
        'contact_email' => 'info@pohoda-antosovice.cz',
        'contact_phone' => '+420 123 456 789',
        'facebook_url' => '',
        'instagram_url' => ''
    ];
    $posts = [];
}

// Načíst menu
try {
    $menu = generateBootstrapMenu($pdo, 'home'); // Předáme 'home' pro označení jako aktivní
    
    // Debug: zkontrolujme, jestli máme nějaké stránky pro menu
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pages WHERE is_published = 1 AND menu_order >= 0");
    $stmt->execute();
    $page_count = $stmt->fetchColumn();
    
    // Pokud nemáme žádné stránky, přidáme základní menu
    if ($page_count == 0 || empty($menu)) {
        $menu = '<li class="nav-item"><a class="nav-link active" href="index.php">Domů</a></li>';
        $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=o-mist">O místě</a></li>';
        $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=kontakt">Kontakt</a></li>';
        $menu .= '<li class="nav-item"><a class="nav-link" href="admin/">Admin</a></li>';
    }
    
} catch (Exception $e) {
    // Fallback menu pokud selže databáze
    $menu = '<li class="nav-item"><a class="nav-link active" href="index.php">Domů</a></li>';
    $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=o-mist">O místě</a></li>';
    $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=kontakt">Kontakt</a></li>';
    $menu .= '<li class="nav-item"><a class="nav-link" href="admin/">Admin</a></li>';
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></title>
    <meta name="description" content="<?= htmlspecialchars($settings['site_description'] ?? 'Naturistický kemp - relaxace v harmonii s přírodou') ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="assets/css/fontawesome-all.min.css" rel="stylesheet">
    
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
            font-size: 1.75rem !important;
            color: white !important;
            text-decoration: none !important;
            display: flex !important;
            align-items: center !important;
        }
        
        .navbar-brand i {
            font-size: 1.75rem !important;
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
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }
        
        .navbar-nav .nav-link.active {
            color: white !important;
            background: rgba(255,255,255,0.2);
            font-weight: 600;
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
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0 60px;
            text-align: center;
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
            background: linear-gradient(135deg, rgba(45, 80, 22, 0.8) 0%, rgba(107, 142, 35, 0.6) 50%, rgba(34, 60, 16, 0.8) 100%);
            z-index: 1;
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
        
        /* Social Media Links */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .social-links .btn {
            min-width: 140px;
            transition: all 0.3s ease;
        }
        
        .social-links .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
        }
        
        @media (max-width: 768px) {
            .social-links .btn {
                min-width: 120px;
                font-size: 0.9rem;
                padding: 0.6rem 1rem;
            }
        }
        
        /* Main Content */
        .main-content {
            padding: 60px 0;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-leaf me-2"></i>Pohoda Antošovice
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
    <!-- Hero Section -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1><?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></h1>
                <p class="lead"><?= htmlspecialchars($settings['site_description'] ?? 'Naturistický kemp - relaxace v harmonii s přírodou') ?></p>
                <div class="mt-4">
                    <a href="page_new.php?slug=vice-informaci" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-info-circle me-2"></i>Více informací
                    </a>
                    <a href="page_new.php?slug=kontakt" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-envelope me-2"></i>Kontakt
                    </a>
                </div>
                
                <!-- Social Media Links -->
                <?php if (!empty($settings['facebook_url']) || !empty($settings['instagram_url'])): ?>
                <div class="mt-4 pt-3">
                    <h6 class="text-light mb-3">Sledujte nás na sociálních sítích</h6>
                    <div class="social-links">
                        <?php if (!empty($settings['facebook_url'])): ?>
                        <a href="<?= htmlspecialchars($settings['facebook_url']) ?>" target="_blank" class="btn btn-outline-light btn-lg me-3">
                            <i class="fab fa-facebook me-2"></i>Facebook
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['instagram_url'])): ?>
                        <a href="<?= htmlspecialchars($settings['instagram_url']) ?>" target="_blank" class="btn btn-outline-light btn-lg">
                            <i class="fab fa-instagram me-2"></i>Instagram
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Articles Section -->
    <section class="main-content">
        <div class="container">
            <h2 class="text-center mb-5">Nejnovější novinky</h2>
            <div class="row g-4">
                <?php if (empty($posts)): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="content-card">
                        <h5>Vítejte v našem kempu</h5>
                        <p>Jsme rádi, že jste navštívili naše stránky. Náš naturistický kemp nabízí jedinečný zážitek v souladu s přírodou.</p>
                        <a href="page_new.php?slug=vice-informaci" class="btn btn-primary">
                            <i class="fas fa-arrow-right me-2"></i>Více informací
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="content-card">
                        <h5>Rezervace</h5>
                        <p>Rezervujte si své místo v našem kempu jednoduše online nebo nás kontaktujte telefonicky.</p>
                        <a href="rezervace.php" class="btn btn-primary">
                            <i class="fas fa-calendar me-2"></i>Rezervovat
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="content-card">
                        <h5>Galerie</h5>
                        <p>Prohlédněte si fotografie našeho kempu a okolní přírody. Přesvědčte se o kráse našeho místa.</p>
                        <a href="galerie.php" class="btn btn-primary">
                            <i class="fas fa-images me-2"></i>Zobrazit galerii
                        </a>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="content-card">
                            <h5><?= htmlspecialchars($post['title']) ?></h5>
                            <p>
                                <?= htmlspecialchars(substr($post['excerpt'] ?? '', 0, 120)) ?><?= strlen($post['excerpt'] ?? '') > 120 ? '...' : '' ?>
                            </p>
                            <small class="text-muted d-block mb-3">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('j. n. Y', strtotime($post['created_at'])) ?>
                            </small>
                            <a href="post_new.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>Číst více
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Social Media Section -->
            <?php if (!empty($settings['facebook_url']) || !empty($settings['instagram_url'])): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <div class="content-card text-center">
                        <h4><i class="fas fa-share-alt me-2"></i>Sledujte nás</h4>
                        <p class="text-muted mb-4">Zůstaňte v kontaktu a sledujte naše nejnovější aktuality</p>
                        <div class="social-links">
                            <?php if (!empty($settings['facebook_url'])): ?>
                                <a href="<?= htmlspecialchars($settings['facebook_url']) ?>" class="btn btn-outline-primary me-3" target="_blank">
                                    <i class="fab fa-facebook-f me-2"></i>Facebook
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($settings['instagram_url'])): ?>
                                <a href="<?= htmlspecialchars($settings['instagram_url']) ?>" class="btn btn-outline-primary" target="_blank">
                                    <i class="fab fa-instagram me-2"></i>Instagram
                                </a>
                            <?php endif; ?>
                        </div>
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
                    <h5><i class="fas fa-leaf me-2"></i>Pohoda Antošovice</h5>
                    <p><?= htmlspecialchars($settings['site_description'] ?? 'Naturistický kemp - relaxace v harmonii s přírodou') ?></p>
                </div>
                <div class="col-md-3">
                    <h6>Rychlé odkazy</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Domů</a></li>
                        <?php if (!empty($quickLinks)): ?>
                            <?php foreach ($quickLinks as $link): ?>
                                <li><a href="<?= htmlspecialchars($link['url']) ?>" 
                                       title="<?= htmlspecialchars($link['description']) ?>"><?= htmlspecialchars($link['title']) ?></a></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a href="page_new.php?slug=o-organizaci">O organizaci</a></li>
                            <li><a href="page_new.php?slug=provozni-rad">Provozní řád</a></li>
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
