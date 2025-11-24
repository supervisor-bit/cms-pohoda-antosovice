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
    // Získat článek z databáze
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ? AND is_published = 1");
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
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
    
    // Získat všechny články pro sidebar a seskupit podle roku
    $stmt = $pdo->prepare("SELECT title, slug, excerpt, featured_image, created_at FROM posts WHERE is_published = 1 ORDER BY created_at DESC");
    $stmt->execute();
    $all_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Seskupit články podle roku
    $posts_by_year = [];
    foreach ($all_posts as $sidebar_post) {
        $post_year = date('Y', strtotime($sidebar_post['created_at']));
        if (!isset($posts_by_year[$post_year])) {
            $posts_by_year[$post_year] = [];
        }
        $posts_by_year[$post_year][] = $sidebar_post;
    }
    krsort($posts_by_year); // Seřadit roky sestupně
    
} catch (PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo "Chyba databáze: " . $e->getMessage();
    exit;
}

// Načíst menu
$menu = generateBootstrapMenu($pdo, '');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></title>
    <meta name="description" content="<?= htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 150)) ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6f9183;      /* Zemitá zelená */
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

        .dropdown-menu {
            z-index: 1050 !important;
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
            background: #6f9183 !important;
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
            background: #6f9183 !important;
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

        /* Article Header */
        .article-header {
            background: linear-gradient(135deg, #6f9183, #5a7a6b);
            color: white;
            padding: 6rem 0 4rem;
            position: relative;
            overflow: hidden;
        }

        .article-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,0 1000,0 1000,80 0,100"/></svg>') no-repeat bottom center;
            background-size: cover;
        }

        .article-header-content {
            position: relative;
            z-index: 2;
        }
        
        .article-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .article-meta {
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }
        
        .article-meta span {
            margin-right: 2rem;
        }
        
        /* Main Content */
        .main-content {
            padding: 4rem 0;
            position: relative;
        }

        .article-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.1);
            position: relative;
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

        .article-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6f9183, var(--accent-color));
            border-radius: 20px 20px 0 0;
        }
        
        .article-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin: 2rem 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .article-content h1, .article-content h2 {
            color: #6f9183;
            margin: 2rem 0 1rem 0;
        }
        
        .article-content h3 {
            color: #5a7a6b;
            margin: 1.5rem 0 1rem 0;
        }
        
        .article-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 1.5rem 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Related Articles */
        .related-articles {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        .related-articles h3 {
            color: #6f9183;
            margin-bottom: 1.5rem;
        }
        
        .related-article {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            border-left: 2px solid transparent;
        }
        
        .related-article.year-header {
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

        .related-article.year-header:first-child {
            margin-top: 0;
        }

        .related-article.year-header i {
            color: #6f9183;
            font-size: 1rem;
        }

        .related-article.year-header h5 {
            margin: 0;
            font-size: 1rem;
        }

        .related-article.article-child {
            padding-left: 2rem;
        }
        
        .related-article:last-child {
            margin-bottom: 0;
        }
        
        .related-article:hover {
            background: rgba(111, 145, 131, 0.05);
            border-left-color: #6f9183;
        }
        
        .related-article.active-article {
            background: rgba(111, 145, 131, 0.15);
            border-left: 3px solid #6f9183;
            font-weight: 600;
        }
        
        .related-article a {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .related-article a i {
            color: #6f9183;
            font-size: 0.9rem;
        }
        
        .related-article h5 {
            color: #333;
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.4;
        }
        
        .related-article:hover h5 {
            color: #6f9183;
        }
        
        .related-article small {
            font-size: 0.75rem;
            display: block;
            margin-top: 0.25rem;
            padding-left: 1.5rem;
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
            background: #6f9183 !important;
            border: 2px solid #6f9183 !important;
            color: white !important;
        }
        
        .btn-primary:hover {
            background: #5a7a6b !important;
            border-color: #5a7a6b !important;
            transform: translateY(-2px);
            color: white !important;
        }
        
        .btn-outline-primary {
            border: 2px solid #6f9183;
            color: #6f9183;
        }
        
        .btn-outline-primary:hover {
            background: #6f9183 !important;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Footer */
        footer {
            background: #6f9183 !important;
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
            
            .article-header h1 {
                font-size: 2rem;
            }
            
            .article-content {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                position: relative;
                top: 0;
                padding: 2rem 0;
            }
            
            .article-header {
                padding: 60px 0 40px;
            }
            
            .main-content {
                padding: 40px 0;
            }
        }
        
        /* Touch improvements for mobile */
        @media (hover: none) {
            .related-article:hover {
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
                <img src="images/sno-logo.png" alt="SNO Logo" class="navbar-logo me-2">
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
                <h1 class="display-4 mb-3"><?= htmlspecialchars($post['title']) ?></h1>
                <?php if (!empty($post['excerpt'])): ?>
                    <p class="lead"><?= htmlspecialchars($post['excerpt']) ?></p>
                <?php endif; ?>
                <div class="article-meta">
                    <span><i class="fas fa-calendar me-1"></i><?= date('j. n. Y', strtotime($post['created_at'])) ?></span>
                    <?php if (!empty($post['updated_at']) && $post['updated_at'] !== $post['created_at']): ?>
                        <span class="ms-3"><i class="fas fa-edit me-1"></i>Aktualizováno: <?= date('j. n. Y', strtotime($post['updated_at'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Article Content -->
                    <div class="article-content">
                        <?php if (!empty($post['featured_image'])): ?>
                            <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="article-image">
                        <?php endif; ?>
                        
                        <div class="content">
                            <?= $post['content'] ?? '' ?>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>Zpět na hlavní stránku
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Všechny články podle roku -->
                    <?php if (!empty($posts_by_year)): ?>
                    <div class="related-articles">
                        <h3><i class="fas fa-newspaper me-2"></i>Všechny články</h3>
                        <?php foreach ($posts_by_year as $year => $year_posts): ?>
                            <!-- Rok jako nadřazený prvek -->
                            <div class="related-article year-header">
                                <i class="fas fa-folder"></i>
                                <div>
                                    <h5><?= $year ?> <span class="text-muted">(<?= count($year_posts) ?>)</span></h5>
                                </div>
                            </div>
                            <!-- Články pod daným rokem -->
                            <?php foreach ($year_posts as $article): ?>
                                <div class="related-article article-child <?= $article['slug'] === $slug ? 'active-article' : '' ?>">
                                    <a href="post_new.php?slug=<?= htmlspecialchars($article['slug']) ?>">
                                        <i class="fas fa-file-alt"></i>
                                        <div>
                                            <h5><?= htmlspecialchars($article['title']) ?></h5>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i><?= date('j. n. Y', strtotime($article['created_at'])) ?>
                                            </small>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
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
