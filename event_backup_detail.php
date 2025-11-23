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

} catch (PDOException $e) {
    header("HTTP/1.0 500 Internal Server Error");
    die("Chyba databáze: " . $e->getMessage());
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

        .header-nav a:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        /* Hero Section */
        .event-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        .event-hero::before {
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

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .event-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .event-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .event-meta-hero {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            font-size: 1.1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.1);
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }

        .meta-item i {
            margin-right: 0.75rem;
            font-size: 1.2rem;
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

        /* Modern Cards */
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

        /* Event Info Grid */
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
            background: rgba(102, 126, 234, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(102, 126, 234, 0.1);
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
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
        }

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
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-secondary {
            background: rgba(102, 126, 234, 0.1);
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        /* Related Content */
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .related-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 45px rgba(102, 126, 234, 0.15);
        }

        .related-card h5 {
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .related-card h5 a {
            color: var(--text-dark);
            text-decoration: none;
        }

        .related-card h5 a:hover {
            color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .event-title {
                font-size: 2rem;
            }
            
            .content-container {
                padding: 1rem;
            }
            
            .event-meta-hero {
                flex-direction: column;
                gap: 1rem;
            }
            
            .event-info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
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
                <a href="events.php">
                    <i class="fas fa-calendar-alt me-1"></i>Akce
                </a>
            </nav>
        </div>
    </header>

    <!-- Event Hero Section -->
    <section class="event-hero">
        <div class="hero-content">
            <!-- Category Badge -->
            <?php if ($event['category_color']): ?>
                <div class="category-badge" style="background-color: <?= $event['category_color'] ?>">
                    <?php if ($event['category_icon']): ?>
                        <i class="fas fa-<?= htmlspecialchars($event['category_icon']) ?> me-2"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($event['category_name'] ?? $event['category']) ?>
                </div>
            <?php endif; ?>

            <h1 class="event-title"><?= htmlspecialchars($event['title']) ?></h1>
            
            <?php if ($event['description']): ?>
                <p class="event-subtitle"><?= htmlspecialchars($event['description']) ?></p>
            <?php endif; ?>

            <div class="event-meta-hero">
                <!-- Date & Time -->
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>
                        <?= date('j. n. Y', strtotime($event['start_date'])) ?>
                        <?php if ($event['is_all_day']): ?>
                            - Celý den
                        <?php elseif ($event['start_time']): ?>
                            v <?= date('H:i', strtotime($event['start_time'])) ?>
                        <?php endif; ?>
                    </span>
                </div>

                <!-- Location -->
                <?php if ($event['location']): ?>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($event['location']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Price -->
                <?php if ($event['price']): ?>
                    <div class="meta-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span><?= htmlspecialchars($event['price']) ?> Kč</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="content-container">
        <div class="row">
            <!-- Main Event Details -->
            <div class="col-lg-8">
                <!-- Event Information -->
                <div class="modern-card">
                    <h2 class="card-title">
                        <i class="fas fa-info-circle"></i>Podrobnosti o akci
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
                        <?php if ($event['registration_enabled']): ?>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Registrace</h6>
                                    <p>
                                        Vyžadována registrace
                                        <?php if ($event['registration_deadline']): ?>
                                            <br><small>Do: <?= date('j. n. Y', strtotime($event['registration_deadline'])) ?></small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Contact -->
                        <?php if ($event['contact_info']): ?>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="info-content">
                                    <h6>Kontakt</h6>
                                    <p><?= htmlspecialchars($event['contact_info']) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Full Description -->
                    <?php if ($event['full_description']): ?>
                        <div class="mt-4">
                            <h5><i class="fas fa-align-left me-2"></i>Popis akce</h5>
                            <div class="content">
                                <?= nl2br(htmlspecialchars($event['full_description'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Featured Image -->
                    <?php if ($event['featured_image']): ?>
                        <div class="mt-4">
                            <img src="<?= htmlspecialchars($event['featured_image']) ?>" 
                                 alt="<?= htmlspecialchars($event['title']) ?>" 
                                 class="img-fluid rounded">
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <?php if ($event['registration_enabled'] && $event['registration_deadline'] >= date('Y-m-d')): ?>
                            <a href="mailto:<?= htmlspecialchars($settings['contact_email'] ?? 'info@pohoda-antosovice.cz') ?>?subject=Registrace na akci: <?= urlencode($event['title']) ?>" 
                               class="btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Přihlásit se na akci
                            </a>
                        <?php endif; ?>
                        
                        <a href="events.php" class="btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Zpět na přehled akcí
                        </a>
                        
                        <?php if ($event['external_link']): ?>
                            <a href="<?= htmlspecialchars($event['external_link']) ?>" 
                               target="_blank" 
                               class="btn-secondary">
                                <i class="fas fa-external-link-alt me-2"></i>Více informací
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Related Posts -->
                <?php if (!empty($relatedPosts)): ?>
                    <div class="modern-card">
                        <h2 class="card-title">
                            <i class="fas fa-newspaper"></i>Související články
                        </h2>
                        
                        <div class="related-grid">
                            <?php foreach ($relatedPosts as $post): ?>
                                <div class="related-card">
                                    <h5>
                                        <a href="post_new.php?slug=<?= htmlspecialchars($post['slug']) ?>">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h5>
                                    <?php if ($post['excerpt']): ?>
                                        <p class="text-muted"><?= htmlspecialchars(mb_substr($post['excerpt'], 0, 100)) ?>...</p>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('j. n. Y', strtotime($post['created_at'])) ?>
                                    </small>
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
                    <div class="modern-card">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-check"></i>Další akce
                        </h3>
                        
                        <?php foreach ($similarEvents as $similar): ?>
                            <div class="related-card mb-3">
                                <h6>
                                    <a href="event.php?slug=<?= htmlspecialchars($similar['slug']) ?>">
                                        <?= htmlspecialchars($similar['title']) ?>
                                    </a>
                                </h6>
                                <p class="mb-1">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('j. n. Y', strtotime($similar['start_date'])) ?>
                                    <?php if ($similar['start_time']): ?>
                                        v <?= date('H:i', strtotime($similar['start_time'])) ?>
                                    <?php endif; ?>
                                </p>
                                <?php if ($similar['location']): ?>
                                    <p class="mb-0">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($similar['location']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <a href="events.php" class="btn-secondary w-100">
                            <i class="fas fa-calendar-alt me-2"></i>Všechny akce
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Share -->
                <div class="modern-card">
                    <h3 class="card-title">
                        <i class="fas fa-share-alt"></i>Sdílet akci
                    </h3>
                    
                    <div class="d-grid gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                           target="_blank" 
                           class="btn-secondary">
                            <i class="fab fa-facebook-f me-2"></i>Sdílet na Facebook
                        </a>
                        
                        <a href="mailto:?subject=<?= urlencode($event['title']) ?>&body=<?= urlencode('Podívej se na tuto akci: ' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                           class="btn-secondary">
                            <i class="fas fa-envelope me-2"></i>Poslat emailem
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>