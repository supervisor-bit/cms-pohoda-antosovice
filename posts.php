<?php
require_once 'config.php';
require_once 'includes/functions.php';

try {
    // Získat nastavení webu
    $settings = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
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
    
    // Načtení všech publikovaných příspěvků
    $stmt = $pdo->prepare("SELECT title, slug, excerpt, content, featured_image, created_at FROM posts WHERE is_published = 1 ORDER BY created_at DESC");
    $stmt->execute();
    $all_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Seskupení článků podle let
    $posts_by_year = [];
    foreach ($all_posts as $article_item) {
        $post_year = date('Y', strtotime($article_item['created_at']));
        if (!isset($posts_by_year[$post_year])) {
            $posts_by_year[$post_year] = [];
        }
        $posts_by_year[$post_year][] = $article_item;
    }
    krsort($posts_by_year); // Seřadit od nejnovějších
    
} catch (PDOException $e) {
    die("Chyba databáze: " . $e->getMessage());
}

// Generate menu
$current_slug = '';
$menu = generateBootstrapMenu($pdo, $current_slug);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Všechny články - <?= htmlspecialchars($settings['site_title']) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-green: #6f9183;
            --light-green: #97bc62;
            --accent-green: #6b9080;
            --text-dark: #2c3e50;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: linear-gradient(135deg, #f0f8e8 0%, #e8f5d8 100%);
            min-height: 100vh;
        }

        /* Oprava kurzívy a zarovnání textu */
        em, i {
            font-style: italic;
            vertical-align: baseline;
        }

        p {
            display: block !important;
        }

        /* Navbar */
        .navbar {
            background: #6f9183 !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .navbar .container {
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
        }

        .navbar-brand {
            font-weight: 700 !important;
            font-size: 1.4rem !important;
            color: white !important;
            text-decoration: none !important;
            display: flex !important;
            align-items: center !important;
            margin-right: 0.3rem !important;
            margin-left: 0 !important;
            padding-left: 0.3rem !important;
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
            margin: 0 3px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            background: transparent !important;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.15) !important;
        }

        .navbar-nav .nav-link.active {
            color: white !important;
            background: #5a7a6b !important;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .dropdown-menu {
            z-index: 1050 !important;
        }

        .page-header {
            background: #6f9183 !important;
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            position: sticky;
            top: 80px;
            z-index: 100;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .page-header-content h1 {
            color: white;
        }

        .article-meta {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            margin-top: 1rem;
        }

        .article-meta i {
            opacity: 0.8;
        }

        .content-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .content-card img {
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .btn-primary {
            background: #6f9183 !important;
            border: 2px solid #6f9183 !important;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            color: white !important;
        }

        .btn-primary:hover {
            background: #5a7a6b !important;
            border-color: #5a7a6b !important;
            transform: translateY(-2px);
            color: white !important;
        }

        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .articles-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .article-item {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            border-left: 2px solid transparent;
        }

        .article-item.year-header {
            background: rgba(111, 145, 131, 0.1);
            font-weight: 600;
            border-left: 3px solid #6f9183;
            padding: 0.75rem;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-dark);
        }

        .article-item.year-header:first-child {
            margin-top: 0;
        }

        .article-item.year-header i {
            color: #6f9183;
            font-size: 1rem;
        }

        .article-item.year-header h6 {
            margin: 0;
            font-size: 1rem;
        }

        .article-item.article-child {
            padding-left: 2rem;
        }

        .article-item:hover {
            background: rgba(111, 145, 131, 0.05);
            border-left-color: #6f9183;
        }

        .article-item.active {
            background: rgba(111, 145, 131, 0.15);
            border-left: 3px solid #6f9183;
            font-weight: 600;
        }

        .article-item a {
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .article-item a i {
            color: #6f9183;
            font-size: 0.9rem;
        }

        .article-item:hover a h6 {
            color: #6f9183;
        }
        
        .article-item h6 {
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.4;
        }
        
        .article-item small {
            font-size: 0.75rem;
            display: block;
            margin-top: 0.25rem;
            padding-left: 1.5rem;
        }

        @media (max-width: 768px) {
            .page-header {
                position: relative;
                top: 0;
                padding: 2rem 0;
            }
            
            /* Mobilní menu - zachovat barvu pozadí */
            .navbar-collapse {
                background: #6f9183 !important;
                padding: 1rem;
                border-radius: 8px;
                margin-top: 0.5rem;
            }
            
            .navbar-nav {
                background: transparent !important;
            }
            
            .navbar-nav .nav-link {
                background: transparent !important;
                margin: 0.2rem 0;
            }
            
            .navbar-nav .nav-link:hover {
                background: rgba(255,255,255,0.15) !important;
            }
            
            .navbar-nav .nav-link.active {
                background: #5a7a6b !important;
            }
            
            /* Dropdown v mobilním menu */
            .navbar-nav .dropdown-menu {
                background: #5a7a6b !important;
                border: none;
                box-shadow: none;
                position: static !important;
                float: none;
                width: auto;
                margin-top: 0;
                padding: 0.5rem 0;
            }
            
            .navbar-nav .dropdown-item {
                color: white !important;
                padding: 0.5rem 1rem;
            }
            
            .navbar-nav .dropdown-item:hover {
                background: rgba(255,255,255,0.15) !important;
                color: white !important;
            }
            
            .navbar-nav .dropdown-divider {
                border-color: rgba(255,255,255,0.2);
            }
            
            .navbar-nav .dropdown-toggle::after {
                color: white;
            }
        }

        footer {
            background: #6f9183 !important;
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: white;
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

    <?php 
    // Získat všechny články do jednoho pole pro snadnější práci
    $all_posts_flat = [];
    foreach ($posts_by_year as $year_posts) {
        $all_posts_flat = array_merge($all_posts_flat, $year_posts);
    }
    
    // Získat vybraný článek z URL parametru nebo první článek
    $selectedSlug = $_GET['selected'] ?? $all_posts_flat[0]['slug'];
    $selectedPost = null;
    foreach ($all_posts_flat as $post) {
        if ($post['slug'] === $selectedSlug) {
            $selectedPost = $post;
            break;
        }
    }
    if (!$selectedPost) $selectedPost = $all_posts_flat[0];
    ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content text-center">
                <h1 class="display-4 mb-3"><?= htmlspecialchars($selectedPost['title']) ?></h1>
                <?php if (!empty($selectedPost['excerpt'])): ?>
                    <p class="lead"><?= htmlspecialchars($selectedPost['excerpt']) ?></p>
                <?php endif; ?>
                <div class="article-meta">
                    <span><i class="fas fa-calendar me-1"></i><?= date('j. n. Y', strtotime($selectedPost['created_at'])) ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="main-content py-5">
        <div class="container">
            <?php if (!empty($posts_by_year)): ?>
            <div class="row g-4">
                <!-- Levý sloupec - Detail článku -->
                <div class="col-lg-8">
                    <div class="content-card">
                        <?php if ($selectedPost['featured_image']): ?>
                        <img src="<?= htmlspecialchars($selectedPost['featured_image']) ?>" class="img-fluid rounded mb-3 w-100" alt="<?= htmlspecialchars($selectedPost['title']) ?>">
                        <?php endif; ?>
                        
                        <div class="article-content">
                            <?= $selectedPost['content'] ?>
                        </div>
                    </div>
                    
                    <!-- Tlačítko zpět -->
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Zpět na hlavní stránku
                        </a>
                    </div>
                </div>
                
                <!-- Pravý sloupec - Seznam článků podle let -->
                <div class="col-lg-4">
                    <div class="content-card">
                        <h4 class="mb-4">
                            <i class="fas fa-list me-2"></i>Všechny články
                        </h4>
                        <div class="articles-list">
                            <?php foreach ($posts_by_year as $y => $year_posts): ?>
                                <!-- Rok jako nadřazený prvek -->
                                <div class="article-item year-header">
                                    <i class="fas fa-folder"></i>
                                    <div>
                                        <h6><?= $y ?> <span class="text-muted">(<?= count($year_posts) ?>)</span></h6>
                                    </div>
                                </div>
                                <!-- Články pod daným rokem -->
                                <?php foreach ($year_posts as $post): ?>
                                <div class="article-item article-child <?= $post['slug'] === $selectedSlug ? 'active' : '' ?>">
                                    <a href="?selected=<?= htmlspecialchars($post['slug']) ?>" class="text-decoration-none">
                                        <i class="fas fa-file-alt"></i>
                                        <div>
                                            <h6><?= htmlspecialchars($post['title']) ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('j.n.Y', strtotime($post['created_at'])) ?>
                                            </small>
                                        </div>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="content-card text-center py-5">
                        <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                        <h3>Zatím nejsou publikovány žádné články</h3>
                        <p class="text-muted">Sledujte naše stránky pro nejnovější aktuality.</p>
                        <a href="index.php" class="btn btn-primary mt-3">
                            <i class="fas fa-home me-2"></i>Zpět na hlavní stránku
                        </a>
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
            
            <!-- Social Media Links -->
            <?php if (!empty($settings['facebook_url']) || !empty($settings['instagram_url'])): ?>
            <div class="text-center mt-3">
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
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
