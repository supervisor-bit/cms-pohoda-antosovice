<?php
require_once 'config.php';

// Získání slug z URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}

try {
    // Načtení akce podle slug
    $stmt = $pdo->prepare("
        SELECT e.*, c.name as category_name, c.color as category_color, c.icon as category_icon
        FROM events e 
        LEFT JOIN event_categories c ON e.category = c.name 
        WHERE e.slug = ? AND e.is_published = 1
    ");
    $stmt->execute([$slug]);
    $event = $stmt->fetch();
    
    if (!$event) {
        header("HTTP/1.0 404 Not Found");
        include '404.php';
        exit;
    }

    // Načtení souvisejících článků
    $relatedPostsStmt = $pdo->prepare("
        SELECT title, slug, excerpt, created_at 
        FROM posts 
        WHERE event_id = ? AND is_published = 1 
        ORDER BY created_at DESC
    ");
    $relatedPostsStmt->execute([$event['id']]);
    $relatedPosts = $relatedPostsStmt->fetchAll();

    // Načtení dalších akcí ze stejné kategorie
    $similarEventsStmt = $pdo->prepare("
        SELECT title, slug, start_date, start_time, location 
        FROM events 
        WHERE category = ? AND id != ? AND is_published = 1 AND start_date >= CURDATE()
        ORDER BY start_date ASC 
        LIMIT 3
    ");
    $similarEventsStmt->execute([$event['category'], $event['id']]);
    $similarEvents = $similarEventsStmt->fetchAll();

    // Načtení nastavení webu pro meta tagy
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

} catch (PDOException $e) {
    header("HTTP/1.0 500 Internal Server Error");
    die("Chyba databáze: " . $e->getMessage());
}

// Načíst menu
try {
    require_once 'includes/functions.php';
    $menu = generateBootstrapMenu($pdo, 'event');
    
    if (empty($menu)) {
        $menu = '<li class="nav-item"><a class="nav-link" href="index.php">Domů</a></li>';
        $menu .= '<li class="nav-item"><a class="nav-link" href="events.php">Akce</a></li>';
    }
} catch (Exception $e) {
    $menu = '<li class="nav-item"><a class="nav-link" href="index.php">Domů</a></li>';
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> | <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></title>
    <meta name="description" content="<?= htmlspecialchars($event['description'] ?? '') ?>">
    
    <!-- Open Graph pro sdílení na sociálních sítích -->
    <meta property="og:title" content="<?= htmlspecialchars($event['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($event['description'] ?? '') ?>">
    <meta property="og:type" content="event">
    <?php if ($event['featured_image']): ?>
    <meta property="og:image" content="<?= htmlspecialchars($settings['site_url'] ?? '') ?>/<?= htmlspecialchars($event['featured_image']) ?>">
    <?php endif; ?>
    
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

        /* Navbar styling (zkopírované z hlavní stránky) */
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

        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.7rem 1.2rem !important;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 0 3px;
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

        /* Event header */
        .event-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        .event-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,0 1000,0 1000,80 0,100"/></svg>') no-repeat bottom center;
            background-size: cover;
        }

        .event-header-content {
            position: relative;
            z-index: 2;
        }

        /* Main content */
        .main-content {
            padding: 4rem 0;
            position: relative;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.1);
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

        .event-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: rgba(45, 80, 22, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(45, 80, 22, 0.1);
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: rgba(45, 80, 22, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(45, 80, 22, 0.1);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .info-content h6 {
            margin: 0 0 0.25rem 0;
            font-weight: 600;
            color: var(--text-dark);
        }

        .info-content p {
            margin: 0;
            color: var(--text-light);
        }

        /* Category Badge */
        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            color: white;
            font-weight: 500;
            margin-bottom: 1rem;
            gap: 0.5rem;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(45, 80, 22, 0.3);
            color: white;
        }

        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
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

        /* Related content */
        .related-event-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            background: rgba(45, 80, 22, 0.05);
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(45, 80, 22, 0.1);
        }

        .related-event-item:hover {
            background: rgba(45, 80, 22, 0.1);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(45, 80, 22, 0.1);
        }

        .related-event-date {
            background: var(--primary-color);
            color: white;
            border-radius: 8px;
            padding: 0.5rem;
            text-align: center;
            min-width: 50px;
            margin-right: 1rem;
            font-size: 0.9rem;
        }

        .related-event-title a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
        }

        .related-event-title a:hover {
            color: var(--primary-color);
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

        /* Responsive */
        @media (max-width: 768px) {
            .event-header {
                padding: 3rem 0 1.5rem;
            }
            
            .content-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .event-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tree me-2"></i>
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

    <!-- Event Header -->
    <section class="event-header">
        <div class="container">
            <div class="event-header-content">
                <!-- Category Badge -->
                <?php if ($event['category_color']): ?>
                    <div class="category-badge" style="background-color: <?= $event['category_color'] ?>">
                        <?php if ($event['category_icon']): ?>
                            <i class="fas fa-<?= htmlspecialchars($event['category_icon']) ?>"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($event['category_name'] ?? $event['category']) ?>
                    </div>
                <?php endif; ?>

                <h1 class="display-4 mb-3"><?= htmlspecialchars($event['title']) ?></h1>
                
                <?php if ($event['description']): ?>
                    <p class="lead mb-4"><?= htmlspecialchars($event['description']) ?></p>
                <?php endif; ?>

                <!-- Quick Meta -->
                <div class="d-flex flex-wrap gap-4">
                    <div>
                        <i class="fas fa-calendar-alt me-2"></i>
                        <?= date('j. n. Y', strtotime($event['start_date'])) ?>
                        <?php if ($event['is_all_day']): ?>
                            - Celý den
                        <?php elseif ($event['start_time']): ?>
                            v <?= date('H:i', strtotime($event['start_time'])) ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($event['location']): ?>
                        <div>
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?= htmlspecialchars($event['location']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($event['price']): ?>
                        <div>
                            <i class="fas fa-money-bill-wave me-2"></i>
                            <?= htmlspecialchars($event['price']) ?> Kč
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="main-content">
        <div class="container">
            <div class="row">
                <!-- Main Event Details -->
                <div class="col-lg-8">
                    <!-- Event Information -->
                    <div class="content-card" style="position: relative;">
                        <h2 style="color: var(--primary-color); margin-bottom: 2rem;">
                            <i class="fas fa-info-circle me-2"></i>Podrobnosti o akci
                        </h2>

                        <div class="event-info-grid">
                            <!-- Date & Time -->
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Datum a čas</h6>
                                    <p>
                                        <?= date('j. n. Y', strtotime($event['start_date'])) ?>
                                        <?php if ($event['end_date'] && $event['end_date'] != $event['start_date']): ?>
                                            - <?= date('j. n. Y', strtotime($event['end_date'])) ?>
                                        <?php endif; ?>
                                        <br>
                                        <?php if ($event['is_all_day']): ?>
                                            Celodenní akce
                                        <?php elseif ($event['start_time']): ?>
                                            <?= date('H:i', strtotime($event['start_time'])) ?>
                                            <?= $event['end_time'] ? ' - ' . date('H:i', strtotime($event['end_time'])) : '' ?>
                                        <?php else: ?>
                                            Čas bude upřesněn
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Location -->
                            <?php if ($event['location']): ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>Místo konání</h6>
                                        <p><?= htmlspecialchars($event['location']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Capacity -->
                            <?php if ($event['max_participants']): ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>Kapacita</h6>
                                        <p>Maximálně <?= $event['max_participants'] ?> účastníků</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Price -->
                            <?php if ($event['price']): ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>Cena</h6>
                                        <p><?= htmlspecialchars($event['price']) ?> Kč</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Registration -->
                            <?php if (isset($event['registration_enabled']) && $event['registration_enabled']): ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>Registrace</h6>
                                        <p>
                                            Vyžadována registrace
                                            <?php if (isset($event['registration_deadline']) && $event['registration_deadline']): ?>
                                                <br><small>Do: <?= date('j. n. Y', strtotime($event['registration_deadline'])) ?></small>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Contact -->
                            <?php if (!empty($event['contact_info'])): ?>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>Kontakt</h6>
                                        <p><?= htmlspecialchars($event['contact_info']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Full Description -->
                        <?php if (!empty($event['full_description'])): ?>
                            <div class="mt-4">
                                <h5 style="color: var(--primary-color);">
                                    <i class="fas fa-align-left me-2"></i>Popis akce
                                </h5>
                                <div class="content">
                                    <?= nl2br(htmlspecialchars($event['full_description'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Featured Image -->
                        <?php if (!empty($event['featured_image'])): ?>
                            <div class="mt-4">
                                <img src="<?= htmlspecialchars($event['featured_image']) ?>" 
                                     alt="<?= htmlspecialchars($event['title']) ?>" 
                                     class="img-fluid rounded">
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="mt-4 d-flex flex-wrap gap-3">
                            <?php if (isset($event['registration_enabled']) && $event['registration_enabled'] && isset($event['registration_deadline']) && $event['registration_deadline'] >= date('Y-m-d')): ?>
                                <a href="mailto:<?= htmlspecialchars($settings['contact_email'] ?? 'info@pohoda-antosovice.cz') ?>?subject=Registrace na akci: <?= urlencode($event['title']) ?>" 
                                   class="btn-primary">
                                    <i class="fas fa-user-plus"></i>Přihlásit se na akci
                                </a>
                            <?php endif; ?>
                            
                            <a href="events.php" class="btn-outline-primary">
                                <i class="fas fa-arrow-left"></i>Zpět na přehled akcí
                            </a>
                            
                            <?php if (!empty($event['external_link'])): ?>
                                <a href="<?= htmlspecialchars($event['external_link']) ?>" 
                                   target="_blank" 
                                   class="btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i>Více informací
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Related Posts -->
                    <?php if (!empty($relatedPosts)): ?>
                        <div class="content-card" style="position: relative;">
                            <h2 style="color: var(--primary-color); margin-bottom: 2rem;">
                                <i class="fas fa-newspaper me-2"></i>Související články
                            </h2>
                            
                            <div class="row">
                                <?php foreach ($relatedPosts as $post): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="related-event-item">
                                            <div>
                                                <h6>
                                                    <a href="post_new.php?slug=<?= htmlspecialchars($post['slug']) ?>">
                                                        <?= htmlspecialchars($post['title']) ?>
                                                    </a>
                                                </h6>
                                                <?php if ($post['excerpt']): ?>
                                                    <p class="text-muted small"><?= htmlspecialchars(mb_substr($post['excerpt'], 0, 100)) ?>...</p>
                                                <?php endif; ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?= date('j. n. Y', strtotime($post['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Similar Events -->
                    <?php if (!empty($similarEvents)): ?>
                        <div class="sidebar-widget">
                            <h4 class="widget-title">
                                <i class="fas fa-calendar-check me-2"></i>Další akce
                            </h4>
                            
                            <?php foreach ($similarEvents as $similar): ?>
                                <div class="related-event-item">
                                    <div class="related-event-date">
                                        <?= date('j.', strtotime($similar['start_date'])) ?>
                                        <br><?= date('M', strtotime($similar['start_date'])) ?>
                                    </div>
                                    <div>
                                        <h6 class="related-event-title">
                                            <a href="event.php?slug=<?= htmlspecialchars($similar['slug']) ?>">
                                                <?= htmlspecialchars($similar['title']) ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <?php if ($similar['start_time']): ?>
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('H:i', strtotime($similar['start_time'])) ?>
                                            <?php endif; ?>
                                            <?php if ($similar['location']): ?>
                                                <br><i class="fas fa-map-marker-alt me-1"></i>
                                                <?= htmlspecialchars($similar['location']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="events.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-calendar me-2"></i>Všechny akce
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Kontakt -->
                    <div class="sidebar-widget">
                        <h4 class="widget-title">
                            <i class="fas fa-phone me-2"></i>Kontakt
                        </h4>
                        <p>Máte otázky k této akci?</p>
                        
                        <?php if (!empty($settings['contact_email'])): ?>
                            <p><strong>Email:</strong><br>
                            <a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>"><?= htmlspecialchars($settings['contact_email']) ?></a></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['contact_phone'])): ?>
                            <p><strong>Telefon:</strong><br>
                            <a href="tel:<?= htmlspecialchars($settings['contact_phone']) ?>"><?= htmlspecialchars($settings['contact_phone']) ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>
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
                <div class="col-md-3">
                    <h6>Rychlé odkazy</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Domů</a></li>
                        <li><a href="events.php">Akce</a></li>
                        <?php 
                        try {
                            $photo_check = $pdo->query("SELECT COUNT(*) FROM gallery_photos WHERE is_published = 1");
                            if ($photo_check && $photo_check->fetchColumn() > 0): ?>
                                <li><a href="gallery.php">Fotky okolí</a></li>
                        <?php endif;
                        } catch (Exception $e) {}
                        ?>
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