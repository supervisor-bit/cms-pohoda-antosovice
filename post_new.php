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
    
    // Získat související články
    $stmt = $pdo->prepare("SELECT title, slug, excerpt, featured_image, created_at FROM posts WHERE is_published = 1 AND slug != ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$slug]);
    $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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

        /* Article Header */
        .article-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0 60px;
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
            background: linear-gradient(135deg, rgba(45, 80, 22, 0.8) 0%, rgba(107, 142, 35, 0.6) 50%, rgba(34, 60, 16, 0.8) 100%);
            z-index: 1;
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
            padding: 60px 0;
        }
        
        .article-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            margin-bottom: 2rem;
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
            color: var(--primary-color);
            margin: 2rem 0 1rem 0;
        }
        
        .article-content h3 {
            color: var(--secondary-color);
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
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .related-article {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
            transition: transform 0.3s ease;
        }
        
        .related-article:last-child {
            border-bottom: none;
        }
        
        .related-article:hover {
            transform: translateX(5px);
        }
        
        .related-article h5 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .related-article a {
            text-decoration: none;
            color: inherit;
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
            
            .article-header h1 {
                font-size: 2rem;
            }
            
            .article-content {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 768px) {
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

    <!-- Article Header -->
    <section class="article-header">
        <div class="container">
            <div class="article-header-content text-center">
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <?php if (!empty($post['excerpt'])): ?>
                    <p class="lead"><?= htmlspecialchars($post['excerpt']) ?></p>
                <?php endif; ?>
                <div class="article-meta">
                    <span><i class="fas fa-calendar me-1"></i><?= date('j. n. Y', strtotime($post['created_at'])) ?></span>
                    <?php if (!empty($post['updated_at']) && $post['updated_at'] !== $post['created_at']): ?>
                        <span><i class="fas fa-edit me-1"></i>Aktualizováno: <?= date('j. n. Y', strtotime($post['updated_at'])) ?></span>
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
                            <?= $post['content'] ?>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>Zpět na hlavní stránku
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Related Articles -->
                    <?php if (!empty($related_posts)): ?>
                    <div class="related-articles">
                        <h3><i class="fas fa-newspaper me-2"></i>Související články</h3>
                        <?php foreach ($related_posts as $related): ?>
                            <div class="related-article">
                                <a href="post_new.php?slug=<?= htmlspecialchars($related['slug']) ?>">
                                    <h5><?= htmlspecialchars($related['title']) ?></h5>
                                    <?php if (!empty($related['excerpt'])): ?>
                                        <p class="text-muted small mb-1"><?= htmlspecialchars(substr($related['excerpt'], 0, 80)) ?>...</p>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i><?= date('j. n. Y', strtotime($related['created_at'])) ?>
                                    </small>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Quick Links -->
                    <div class="related-articles mt-4">
                        <h3><i class="fas fa-link me-2"></i>Rychlé odkazy</h3>
                        <?php if (!empty($quickLinks)): ?>
                            <?php foreach ($quickLinks as $link): ?>
                                <div class="related-article">
                                    <a href="<?= htmlspecialchars($link['url']) ?>">
                                        <h5><?= htmlspecialchars($link['title']) ?></h5>
                                        <p class="text-muted small mb-1"><?= htmlspecialchars($link['description']) ?></p>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="related-article">
                                <a href="page_new.php?slug=o-organizaci">
                                    <h5>O organizaci</h5>
                                    <p class="text-muted small mb-1">Zjistěte více o naší organizaci</p>
                                </a>
                            </div>
                            <div class="related-article">
                                <a href="page_new.php?slug=provozni-rad">
                                    <h5>Provozní řád</h5>
                                    <p class="text-muted small mb-1">Důležité informace a pravidla</p>
                                </a>
                            </div>
                            <div class="related-article">
                                <a href="page_new.php?slug=ubytovani-stravovani">
                                    <h5>Ubytování a stravování</h5>
                                    <p class="text-muted small mb-1">Informace o ubytování a stravování</p>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
