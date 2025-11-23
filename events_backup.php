<?php
require_once 'config.php';

try {
    // Načtení nastavení webu
    $settings = [];
    $settingsStmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $settingsStmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
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
    $categoriesStmt = $pdo->query("SELECT * FROM event_categories WHERE is_active = 1 ORDER BY position");
    $categories = $categoriesStmt->fetchAll();

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
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #6b8e23;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
        }

        /* Modern Header */
        .modern-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: relative;
            z-index: 1000;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
        }

        .header-logo {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-logo i {
            margin-right: 0.75rem;
            font-size: 1.75rem;
        }

        .header-nav {
            display: flex;
            gap: 1rem;
        }

        .header-nav a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .header-nav a:hover,
        .header-nav a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        /* Page Title Section */
        .page-title-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        .page-title-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.05);
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 2px, transparent 2px),
                radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 2px, transparent 2px);
            background-size: 50px 50px;
        }

        .page-title-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        /* Content Container */
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            margin-top: -1rem;
            position: relative;
            z-index: 2;
        }

        /* Modern Card */
        .modern-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(102, 126, 234, 0.1);
            position: relative;
            overflow: hidden;
        }

        .modern-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        /* Filters */
        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .filter-btn {
            background: rgba(102, 126, 234, 0.1);
            border: 2px solid rgba(102, 126, 234, 0.2);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .filter-btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .event-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15);
        }

        .event-date-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.5rem;
            border-radius: 8px;
            text-align: center;
            min-width: 60px;
        }

        .event-date-badge .day {
            display: block;
            font-size: 1.2rem;
            font-weight: bold;
            line-height: 1;
        }

        .event-date-badge .month {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .event-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-right: 4rem;
        }

        .event-title a {
            color: var(--text-dark);
            text-decoration: none;
        }

        .event-title a:hover {
            color: var(--primary-color);
        }

        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .event-meta-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .event-meta-item i {
            margin-right: 0.5rem;
            width: 16px;
        }

        .event-category {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            color: white;
        }

        .event-description {
            color: var(--text-light);
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .event-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .no-events {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .no-events i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .content-container {
                padding: 1rem;
            }
            
            .events-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .filters-container {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
        }

        /* Navbar styling */
        .navbar {
            background: rgba(45, 80, 22, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700 !important;
            font-size: 1.75rem !important;
            color: white !important;
        }

        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.7rem 1.2rem !important;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 0 3px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        /* Page header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0 2rem;
        }

        /* Filters */
        .filters-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .filter-btn {
            background: rgba(45, 80, 22, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.2);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            margin: 0.25rem;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--primary-color);
            color: white;
        }

        /* Event cards */
        .event-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .event-date-badge {
            background: var(--primary-color);
            color: white;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            min-width: 80px;
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
        }

        .event-category-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            color: white;
            font-weight: 500;
        }

        .event-meta {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .event-meta i {
            width: 16px;
            margin-right: 0.5rem;
        }

        .event-title {
            color: var(--primary-color);
            font-weight: 600;
            margin: 1rem 0;
        }

        .event-title a {
            color: inherit;
            text-decoration: none;
        }

        .event-title a:hover {
            color: var(--accent-color);
        }

        .btn-event {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .btn-event:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Empty state */
        .empty-events {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }

        .empty-events i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .event-card {
                padding: 1.5rem;
            }
            
            .page-header {
                padding: 2rem 0 1rem;
            }
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="modern-header">
        <div class="header-content">
            <a href="index.php" class="header-logo">
                <i class="fas fa-calendar-alt"></i>
                <span><?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></span>
            </a>
            <nav class="header-nav">
                <a href="index.php">
                    <i class="fas fa-home me-1"></i>Domů
                </a>
                <a href="events.php" class="active">
                    <i class="fas fa-calendar-alt me-1"></i>Akce
                </a>
                <?php if (!empty($menu)): ?>
                    <?= str_replace(['<li class="nav-item">', '</li>', 'class="nav-link"'], ['', '', 'class=""'], $menu) ?>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Page Title Section -->
    <section class="page-title-section">
        <div class="page-title-content">
            <h1 class="page-title">
                <i class="fas fa-calendar-alt me-3"></i>Kalendář akcí
            </h1>
            <p class="page-subtitle">Přehled všech nadcházejících akcí a událostí</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="content-container">
        <!-- Filters Card -->
        <div class="modern-card">
            <h2 class="card-title">
                <i class="fas fa-filter"></i>Filtrovat akce
            </h2>
            
            <div class="filters-container">
                <!-- Category Filter -->
                <div class="filter-group">
                    <div class="filter-label">Kategorie:</div>
                    <div class="filter-buttons">
                        <a href="events.php" class="filter-btn <?= empty($category) ? 'active' : '' ?>">
                            Všechny kategorie
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="events.php?category=<?= urlencode($cat['name']) ?>" 
                               class="filter-btn <?= $category === $cat['name'] ? 'active' : '' ?>"
                               style="<?= $cat['color'] ? 'border-color: '.$cat['color'].'; color: '.$cat['color'].';' : '' ?>">
                                <?php if ($cat['icon']): ?>
                                    <i class="fas fa-<?= htmlspecialchars($cat['icon']) ?> me-1"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Year Filter -->
                <div class="filter-group">
                    <div class="filter-label">Rok:</div>
                    <div class="filter-buttons">
                        <a href="events.php" class="filter-btn <?= empty($year) ? 'active' : '' ?>">
                            Všechny roky
                        </a>
                        <a href="events.php?year=<?= date('Y') ?>" class="filter-btn <?= $year == date('Y') ? 'active' : '' ?>">
                            <?= date('Y') ?>
                        </a>
                        <a href="events.php?year=<?= date('Y') + 1 ?>" class="filter-btn <?= $year == date('Y') + 1 ? 'active' : '' ?>">
                            <?= date('Y') + 1 ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events Grid -->
        <?php if (!empty($events)): ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <!-- Date Badge -->
                        <div class="event-date-badge">
                            <span class="day"><?= date('j', strtotime($event['start_date'])) ?></span>
                            <span class="month"><?= date('M', strtotime($event['start_date'])) ?></span>
                        </div>

                        <!-- Event Title -->
                        <h3 class="event-title">
                            <a href="event.php?slug=<?= htmlspecialchars($event['slug']) ?>">
                                <?= htmlspecialchars($event['title']) ?>
                            </a>
                        </h3>

                        <!-- Event Meta -->
                        <div class="event-meta">
                            <!-- Time -->
                            <div class="event-meta-item">
                                <i class="fas fa-clock"></i>
                                <?php if ($event['is_all_day']): ?>
                                    Celý den
                                <?php elseif ($event['start_time']): ?>
                                    <?= date('H:i', strtotime($event['start_time'])) ?>
                                    <?php if ($event['end_time']): ?>
                                        - <?= date('H:i', strtotime($event['end_time'])) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Čas neurčen
                                <?php endif; ?>
                            </div>

                            <!-- Location -->
                            <?php if ($event['location']): ?>
                                <div class="event-meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($event['location']) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Capacity -->
                            <?php if ($event['max_participants']): ?>
                                <div class="event-meta-item">
                                    <i class="fas fa-users"></i>
                                    Max. <?= $event['max_participants'] ?> účastníků
                                </div>
                            <?php endif; ?>

                            <!-- Price -->
                            <?php if ($event['price']): ?>
                                <div class="event-meta-item">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <?= htmlspecialchars($event['price']) ?> Kč
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Category Badge -->
                        <?php if ($event['category_color']): ?>
                            <div class="mb-2">
                                <span class="event-category" style="background-color: <?= $event['category_color'] ?>">
                                    <?php if ($event['category_icon']): ?>
                                        <i class="fas fa-<?= htmlspecialchars($event['category_icon']) ?> me-1"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($event['category_name'] ?? $event['category']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Description -->
                        <?php if ($event['description']): ?>
                            <p class="event-description">
                                <?= htmlspecialchars(mb_substr($event['description'], 0, 150)) ?>
                                <?= mb_strlen($event['description']) > 150 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="event-actions">
                            <a href="event.php?slug=<?= htmlspecialchars($event['slug']) ?>" class="btn-primary">
                                <i class="fas fa-info-circle me-1"></i>Detail akce
                            </a>
                            
                            <?php if ($event['registration_enabled'] && $event['registration_deadline'] >= date('Y-m-d')): ?>
                                <a href="event.php?slug=<?= htmlspecialchars($event['slug']) ?>#register" class="btn-primary">
                                    <i class="fas fa-user-plus me-1"></i>Přihlásit se
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="modern-card">
                <div class="no-events">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Žádné akce</h3>
                    <p>Momentálně nejsou naplánované žádné akce odpovídající vašim filtrům.</p>
                    <a href="events.php" class="btn-primary">
                        <i class="fas fa-refresh me-1"></i>Zobrazit všechny akce
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>