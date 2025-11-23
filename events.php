<?php
require_once 'config.php';

try {
    // Načtení nastavení webu
    $settings = [];
    $settingsStmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $settingsStmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Načtení rychlých odkazů pro footer
    try {
        $quickLinksStmt = $pdo->query("SELECT title, url, description FROM quick_links WHERE is_active = 1 ORDER BY position ASC LIMIT 3");
        $quickLinks = $quickLinksStmt->fetchAll();
    } catch (PDOException $e) {
        $quickLinks = []; // Fallback pokud tabulka neexistuje
    }

    // Získání filtru z URL
    $category = $_GET['category'] ?? '';
    $month = $_GET['month'] ?? '';
    $year = $_GET['year'] ?? date('Y');

    // Sestavení SQL dotazu
    $sql = "
        SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon
        FROM events e 
        LEFT JOIN event_categories c ON e.category = c.name 
        WHERE e.is_published = 1
    ";
    
    $params = [];
    
    if ($category) {
        $sql .= " AND e.category = ?";
        $params[] = $category;
    }
    
    if ($month && $year) {
        $sql .= " AND YEAR(e.start_date) = ? AND MONTH(e.start_date) = ?";
        $params[] = $year;
        $params[] = $month;
    } elseif ($year) {
        $sql .= " AND YEAR(e.start_date) = ?";
        $params[] = $year;
    }
    
    $sql .= " ORDER BY e.start_date ASC, e.start_time ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

    // Načtení kategorií pro filtr
    try {
        $categoriesStmt = $pdo->query("SELECT * FROM event_categories WHERE is_active = 1 ORDER BY position");
        $categories = $categoriesStmt->fetchAll();
    } catch (Exception $e) {
        // Pokud tabulka neexistuje, vytvoří se prázdné pole
        $categories = [];
    }

} catch (Exception $e) {
    $error = "Chyba při načítání akcí: " . $e->getMessage();
    $events = [];
    $categories = [];
}

// Načíst menu
try {
    require_once 'includes/functions.php';
    $menu = generateBootstrapMenu($pdo, 'events');
    
    if (empty($menu)) {
        $menu = '<li class="nav-item"><a class="nav-link" href="index.php">Domů</a></li>';
        $menu .= '<li class="nav-item"><a class="nav-link active" href="events.php">Akce</a></li>';
    }
} catch (Exception $e) {
    $menu = '<li class="nav-item"><a class="nav-link" href="index.php">Domů</a></li>';
    $menu .= '<li class="nav-item"><a class="nav-link active" href="events.php">Akce</a></li>';
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalendář akcí | <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></title>
    <meta name="description" content="Přehled všech nadcházejících akcí v kempu <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?>">
    
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
            background: #6f9183 !important;
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
        
        .navbar-logo {
            height: 90px !important;
            width: auto !important;
            object-fit: contain !important;
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
        
        .navbar-nav .nav-item:nth-child(odd) .nav-link {
            background: rgba(255,255,255,0.08);
        }
        
        .navbar-nav .nav-item:nth-child(even) .nav-link {
            background: rgba(0,0,0,0.08);
        }
        
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

        /* Page header */
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

        /* Filters */
        .filters-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            position: relative;
            z-index: 2;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.1);
        }

        .filters-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 20px 20px 0 0;
        }

        .filter-btn {
            background: rgba(45, 80, 22, 0.1);
            border: 2px solid rgba(45, 80, 22, 0.2);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            margin: 0.25rem;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(45, 80, 22, 0.3);
            text-decoration: none;
        }

        /* Event cards */
        .event-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .event-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(45, 80, 22, 0.15);
        }

        .event-date-badge {
            background: var(--primary-color);
            color: white;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            min-width: 80px;
            box-shadow: 0 4px 15px rgba(45, 80, 22, 0.3);
        }

        .event-date-badge .day {
            display: block;
            font-size: 1.5rem;
            font-weight: bold;
            line-height: 1;
        }

        .event-date-badge .month {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-top: 2px;
            opacity: 0.9;
        }

        .event-category-badge {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .event-meta {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.8;
        }

        .event-meta i {
            width: 16px;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .event-title {
            color: var(--primary-color);
            font-weight: 600;
            margin: 1rem 0;
            font-size: 1.3rem;
        }

        .event-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .event-title a:hover {
            color: var(--accent-color);
        }

        .btn-event {
            background: var(--primary-color);
            border: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-event:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(45, 80, 22, 0.3);
            text-decoration: none;
        }

        /* Empty state */
        .empty-events {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .empty-events i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        /* Footer */
        .footer {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0 2rem;
            margin-top: 4rem;
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

        /* Responsive */
        @media (max-width: 768px) {
            .event-card {
                padding: 1.5rem;
            }
            
            .page-header {
                padding: 4rem 0 2rem;
            }

            .filters-section {
                margin: 2rem 0;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/sno-logo.png" alt="SNO Logo" class="navbar-logo me-2">
                <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?= $menu ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content text-center">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-calendar-alt me-3"></i>Kalendář akcí
                </h1>
                <p class="lead">Přehled všech nadcházejících akcí a událostí v našem kempu</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Filtry -->
            <div class="filters-section">
                <h5 class="mb-3">
                    <i class="fas fa-filter me-2" style="color: var(--primary-color);"></i>
                    <span style="color: var(--primary-color); font-weight: 600;">Filtry</span>
                </h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 style="color: var(--text-dark);">Kategorie:</h6>
                        <a href="events.php" class="filter-btn <?= !$category ? 'active' : '' ?>">Všechny kategorie</a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="events.php?category=<?= urlencode($cat['name']) ?>" 
                               class="filter-btn <?= $category === $cat['name'] ? 'active' : '' ?>"
                               style="<?= $cat['color'] ? 'border-color: '.$cat['color'] : '' ?>">
                                <?php if ($cat['icon']): ?>
                                    <i class="fas fa-<?= htmlspecialchars($cat['icon']) ?> me-1"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <h6 style="color: var(--text-dark);">Rok:</h6>
                        <a href="events.php" class="filter-btn <?= empty($year) ? 'active' : '' ?>">Všechny roky</a>
                        <a href="events.php?year=<?= date('Y') ?>" class="filter-btn <?= $year == date('Y') ? 'active' : '' ?>">
                            <?= date('Y') ?>
                        </a>
                        <a href="events.php?year=<?= date('Y') + 1 ?>" class="filter-btn <?= $year == date('Y') + 1 ? 'active' : '' ?>">
                            <?= date('Y') + 1 ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Seznam akcí -->
            <div class="row">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="col-12">
                            <div class="event-card">
                                <!-- Kategorie badge -->
                                <?php if ($event['category_color']): ?>
                                    <span class="event-category-badge" style="background-color: <?= $event['category_color'] ?>">
                                        <?php if ($event['category_icon']): ?>
                                            <i class="fas fa-<?= htmlspecialchars($event['category_icon']) ?>"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($event['category_name'] ?? $event['category']) ?>
                                    </span>
                                <?php endif; ?>

                                <div class="row align-items-center">
                                    <!-- Datum -->
                                    <div class="col-md-2">
                                        <div class="event-date-badge">
                                            <span class="day"><?= date('j', strtotime($event['start_date'])) ?></span>
                                            <span class="month"><?php 
                                                $czech_months = [
                                                    1 => 'led', 2 => 'úno', 3 => 'bře', 4 => 'dub', 
                                                    5 => 'kvě', 6 => 'čer', 7 => 'čvc', 8 => 'srp', 
                                                    9 => 'zář', 10 => 'říj', 11 => 'lis', 12 => 'pro'
                                                ];
                                                echo $czech_months[(int)date('n', strtotime($event['start_date']))];
                                            ?></span>
                                        </div>
                                    </div>

                                    <!-- Obsah -->
                                    <div class="col-md-7">
                                        <h4 class="event-title">
                                            <a href="event.php?slug=<?= htmlspecialchars($event['slug']) ?>">
                                                <?= htmlspecialchars($event['title']) ?>
                                            </a>
                                        </h4>

                                        <?php if ($event['description']): ?>
                                            <p class="mb-2"><?= htmlspecialchars($event['description']) ?></p>
                                        <?php endif; ?>

                                        <div class="event-meta">
                                            <!-- Čas -->
                                            <div class="mb-1">
                                                <i class="fas fa-clock"></i>
                                                <?php if ($event['is_all_day']): ?>
                                                    Celodenní akce
                                                <?php else: ?>
                                                    <?= $event['start_time'] ? date('H:i', strtotime($event['start_time'])) : '' ?>
                                                    <?= $event['end_time'] ? ' - ' . date('H:i', strtotime($event['end_time'])) : '' ?>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Místo -->
                                            <?php if ($event['location']): ?>
                                                <div class="mb-1">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?= htmlspecialchars($event['location']) ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Cena -->
                                            <?php if ($event['price']): ?>
                                                <div class="mb-1">
                                                    <i class="fas fa-tag"></i>
                                                    <?= htmlspecialchars($event['price']) ?> Kč
                                                </div>
                                            <?php endif; ?>

                                            <!-- Registrace -->
                                            <?php if (isset($event['registration_enabled']) && $event['registration_enabled']): ?>
                                                <div class="mb-1">
                                                    <i class="fas fa-clipboard-list"></i>
                                                    <span class="text-warning">Vyžaduje registraci</span>
                                                    <?php if (isset($event['registration_deadline']) && $event['registration_deadline']): ?>
                                                        (do <?= date('j.n.Y', strtotime($event['registration_deadline'])) ?>)
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Akce -->
                                    <div class="col-md-3 text-end">
                                        <a href="event.php?slug=<?= htmlspecialchars($event['slug']) ?>" 
                                           class="btn-event">
                                            <i class="fas fa-info-circle"></i>Detail akce
                                        </a>
                                        
                                        <?php if (isset($event['registration_enabled']) && $event['registration_enabled']): ?>
                                            <a href="mailto:<?= htmlspecialchars($settings['contact_email'] ?? 'info@pohoda-antosovice.cz') ?>?subject=Registrace na akci: <?= urlencode($event['title']) ?>" 
                                               class="btn btn-outline-primary btn-sm mt-2 d-block">
                                                <i class="fas fa-envelope me-1"></i>Registrovat
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-events">
                            <i class="fas fa-calendar-times"></i>
                            <h4>Žádné akce nenalezeny</h4>
                            <p>
                                <?php if ($category || $month || $year != date('Y')): ?>
                                    Pro vybrané filtry nebyly nalezeny žádné akce.
                                    <br><a href="events.php" class="btn btn-primary mt-2">Zobrazit všechny akce</a>
                                <?php else: ?>
                                    Momentálně nejsou naplánované žádné akce.
                                    <br><small class="text-muted">Sledujte naše stránky pro aktuální informace.</small>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-leaf me-2"></i><?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></h5>
                    <p><?= htmlspecialchars($settings['site_description'] ?? 'Naturistický kemp - relaxace v harmonii s přírodou') ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Kontakt</h6>
                    <?php if (!empty($settings['contact_email'])): ?>
                        <p><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($settings['contact_email']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($settings['contact_phone'])): ?>
                        <p><i class="fas fa-phone me-2"></i><?= htmlspecialchars($settings['contact_phone']) ?></p>
                    <?php endif; ?>
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
</body>
</html>