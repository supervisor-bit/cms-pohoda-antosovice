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
    
    // Načtení nadcházejících akcí
    try {
        $eventsStmt = $pdo->prepare("
            SELECT e.*, c.color as category_color 
            FROM events e 
            LEFT JOIN event_categories c ON e.category = c.name 
            WHERE e.is_published = 1 AND e.start_date >= CURDATE() 
            ORDER BY e.start_date ASC, e.start_time ASC 
            LIMIT 5
        ");
        $eventsStmt->execute();
        $upcomingEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $upcomingEvents = [];
    }
    
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
    $upcomingEvents = [];
    $quickLinks = [];
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
    }
    
} catch (Exception $e) {
    // Fallback menu pokud selže databáze
    $menu = '<li class="nav-item"><a class="nav-link active" href="index.php">Domů</a></li>';
    $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=o-mist">O místě</a></li>';
    $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=kontakt">Kontakt</a></li>';
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

        /* Main content */
        .main-content {
            margin: -2rem 0 4rem 0;
            position: relative;
            z-index: 2;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.1);
            position: relative;
            overflow: hidden;
        }

        .content-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .content-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(45, 80, 22, 0.15);
        }

        .btn-primary {
            background: var(--primary-color);
            border: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(45, 80, 22, 0.3);
        }

        /* Sidebar widgets */
        .sidebar-widget {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.1);
        }

        .widget-title {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent-color);
        }

        /* Events calendar styles */
        .events-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .event-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            background: rgba(45, 80, 22, 0.05);
            border-radius: 10px;
            transition: all 0.3s ease;
            border: 1px solid rgba(45, 80, 22, 0.1);
        }

        .event-item:hover {
            background: rgba(45, 80, 22, 0.1);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(45, 80, 22, 0.1);
        }

        .event-date {
            background: var(--primary-color);
            color: white;
            border-radius: 8px;
            padding: 0.5rem;
            text-align: center;
            min-width: 60px;
            margin-right: 1rem;
        }

        .event-date .day {
            display: block;
            font-size: 1.2rem;
            font-weight: bold;
            line-height: 1;
        }

        .event-date .month {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .event-details {
            flex: 1;
        }

        .event-title {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .event-title a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
        }

        .event-title a:hover {
            color: var(--primary-color);
        }

        .event-time, .event-location {
            display: block;
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 0.25rem;
        }

        .event-category {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.7rem;
            color: white;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .no-events {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--text-light);
        }

        .no-events i {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
            color: var(--accent-color);
        }

        /* Quick links widget */
        .quick-links-widget {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .quick-link-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            background: rgba(45, 80, 22, 0.05);
            border-radius: 8px;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .quick-link-item:hover {
            background: var(--primary-color);
            color: white;
            transform: translateX(5px);
        }

        /* Footer */
        .footer {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0 2rem;
            margin-top: auto;
        }

        .footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: white;
        }

        @media (max-width: 991px) {
            .sidebar-widget {
                margin-top: 2rem;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 4rem 0 2rem;
            }
            
            .content-card {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-tree-fill me-2"></i>
                <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?= $menu ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1 class="display-4 mb-3"><?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></h1>
                <p class="lead mb-4"><?= htmlspecialchars($settings['site_description'] ?? 'Naturistický kemp - relaxace v harmonii s přírodou') ?></p>
                <div class="mt-4">
                    <a href="page_new.php?slug=vice-informaci" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-info-circle me-2"></i>Více informací
                    </a>
                    <a href="page_new.php?slug=kontakt" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-envelope me-2"></i>Kontakt
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Articles Section -->
    <section class="main-content">
        <div class="container">
            <div class="row">
                <!-- Hlavní obsah - články -->
                <div class="col-lg-8">
                    <h2 class="mb-5">Nejnovější novinky</h2>
                    <div class="row g-4">
                        <?php if (empty($posts)): ?>
                        <div class="col-md-6">
                            <div class="content-card">
                                <h5>Vítejte v našem kempu</h5>
                                <p>Jsme rádi, že jste navštívili naše stránky. Náš naturistický kemp nabízí jedinečný zážitek v souladu s přírodou.</p>
                                <a href="page_new.php?slug=vice-informaci" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>Více informací
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="content-card">
                                <h5>Kontaktujte nás</h5>
                                <p>Máte otázky nebo chcete rezervovat pobyt? Neváhejte nás kontaktovat telefonicky nebo emailem.</p>
                                <a href="page_new.php?slug=kontakt" class="btn btn-primary">
                                    <i class="fas fa-envelope me-2"></i>Kontakt
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                        <div class="col-md-6 mb-4">
                            <div class="content-card h-100">
                                <?php if ($post['featured_image']): ?>
                                <img src="<?= htmlspecialchars($post['featured_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($post['title']) ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($post['excerpt'] ?? '') ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('j.n.Y', strtotime($post['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="post_new.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="btn btn-primary">
                                        <i class="fas fa-arrow-right me-2"></i>Číst více
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar s kalendářem akcí -->
                <div class="col-lg-4">
                    <!-- Kalendář nadcházejících akcí -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title">
                            <i class="fas fa-calendar-alt me-2"></i>Nadcházející akce
                        </h3>
                        
                        <?php if (!empty($upcomingEvents)): ?>
                            <div class="events-list">
                                <?php foreach ($upcomingEvents as $event): ?>
                                <div class="event-item">
                                    <div class="event-date">
                                        <span class="day"><?= date('j', strtotime($event['start_date'])) ?></span>
                                        <span class="month"><?= date('M', strtotime($event['start_date'])) ?></span>
                                    </div>
                                    <div class="event-details">
                                        <h6 class="event-title">
                                            <a href="event.php?slug=<?= htmlspecialchars($event['slug']) ?>">
                                                <?= htmlspecialchars($event['title']) ?>
                                            </a>
                                        </h6>
                                        <?php if ($event['start_time'] && !$event['is_all_day']): ?>
                                            <small class="event-time">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('H:i', strtotime($event['start_time'])) ?>
                                            </small>
                                        <?php elseif ($event['is_all_day']): ?>
                                            <small class="event-time">
                                                <i class="fas fa-clock me-1"></i>Celý den
                                            </small>
                                        <?php endif; ?>
                                        
                                        <?php if ($event['location']): ?>
                                            <small class="event-location">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?= htmlspecialchars($event['location']) ?>
                                            </small>
                                        <?php endif; ?>
                                        
                                        <?php if ($event['category_color']): ?>
                                            <span class="event-category" style="background-color: <?= $event['category_color'] ?>">
                                                <?= htmlspecialchars($event['category']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="events.php" class="btn btn-outline-primary">
                                    <i class="fas fa-calendar me-2"></i>Všechny akce
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="no-events">
                                <i class="fas fa-calendar-times"></i>
                                <p>Momentálně nejsou naplánované žádné akce.</p>
                                <small class="text-muted">Sledujte naše stránky pro aktuální informace.</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick links widget -->
                    <?php if (!empty($quickLinks)): ?>
                    <div class="sidebar-widget">
                        <h3 class="widget-title">
                            <i class="fas fa-external-link-alt me-2"></i>Rychlé odkazy
                        </h3>
                        <div class="quick-links-widget">
                            <?php foreach ($quickLinks as $link): ?>
                                <a href="<?= htmlspecialchars($link['url']) ?>" 
                                   class="quick-link-item" 
                                   title="<?= htmlspecialchars($link['description'] ?? '') ?>">
                                    <i class="fas fa-chevron-right me-2"></i>
                                    <?= htmlspecialchars($link['title']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></h5>
                    <p><?= htmlspecialchars($settings['site_description'] ?? '') ?></p>
                    
                    <?php if (!empty($settings['contact_email']) || !empty($settings['contact_phone'])): ?>
                    <p>
                        <?php if (!empty($settings['contact_email'])): ?>
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>">
                                <?= htmlspecialchars($settings['contact_email']) ?>
                            </a><br>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['contact_phone'])): ?>
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:<?= htmlspecialchars($settings['contact_phone']) ?>">
                                <?= htmlspecialchars($settings['contact_phone']) ?>
                            </a>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($quickLinks)): ?>
                        <h5>Rychlé odkazy</h5>
                        <div class="quick-links">
                            <?php foreach ($quickLinks as $link): ?>
                                <a href="<?= htmlspecialchars($link['url']) ?>" 
                                   title="<?= htmlspecialchars($link['description'] ?? '') ?>"
                                   class="d-block mb-2">
                                    <?= htmlspecialchars($link['title']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Social Media Links -->
                    <?php if (!empty($settings['facebook_url']) || !empty($settings['instagram_url'])): ?>
                    <div class="mt-4">
                        <h6>Sledujte nás</h6>
                        <?php if (!empty($settings['facebook_url'])): ?>
                            <a href="<?= htmlspecialchars($settings['facebook_url']) ?>" target="_blank" class="me-3">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['instagram_url'])): ?>
                            <a href="<?= htmlspecialchars($settings['instagram_url']) ?>" target="_blank">
                                <i class="fab fa-instagram"></i> Instagram
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>