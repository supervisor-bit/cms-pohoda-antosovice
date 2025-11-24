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
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
            background: rgba(255,255,255,0.2) !important;
            font-weight: 600;
        }

        .page-header {
            background: #6f9183 !important;
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
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
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .article-item:hover {
            background: var(--bg-light);
            transform: translateX(5px);
        }

        .article-item.active {
            background: rgba(45, 80, 22, 0.1);
            border-left: 4px solid #6f9183;
            padding-left: 1rem;
        }

        .article-item a {
            color: var(--text-dark);
        }

        .article-item:hover a h6 {
            color: #6f9183;
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="display-4 mb-3">
                <i class="fas fa-newspaper me-3"></i>Všechny články
            </h1>
            <p class="lead">Novinky a aktuality ze spolku</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="main-content py-5">
        <div class="container">
            <?php if (!empty($posts)): ?>
            <div class="row g-4">
                <!-- Levý sloupec - Detail vybraného článku -->
                <div class="col-lg-8">
                    <?php 
                    // Získat vybraný článek z URL parametru nebo první článek
                    $selectedSlug = $_GET['selected'] ?? $posts[0]['slug'];
                    $selectedPost = null;
                    foreach ($posts as $post) {
                        if ($post['slug'] === $selectedSlug) {
                            $selectedPost = $post;
                            break;
                        }
                    }
                    if (!$selectedPost) $selectedPost = $posts[0];
                    ?>
                    
                    <div class="content-card">
                        <?php if ($selectedPost['featured_image']): ?>
                        <img src="<?= htmlspecialchars($selectedPost['featured_image']) ?>" class="img-fluid rounded mb-3 w-100" alt="<?= htmlspecialchars($selectedPost['title']) ?>">
                        <?php endif; ?>
                        
                        <h2 class="mb-3"><?= htmlspecialchars($selectedPost['title']) ?></h2>
                        <p class="text-muted mb-4">
                            <i class="fas fa-calendar me-2"></i>
                            <?= date('j.n.Y', strtotime($selectedPost['created_at'])) ?>
                        </p>
                        
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
                
                <!-- Pravý sloupec - Seznam všech článků -->
                <div class="col-lg-4">
                    <div class="content-card">
                        <h4 class="mb-4">
                            <i class="fas fa-list me-2"></i>Všechny články
                        </h4>
                        <div class="articles-list">
                            <?php foreach ($posts as $post): ?>
                            <div class="article-item mb-3 pb-3 border-bottom <?= $post['slug'] === $selectedSlug ? 'active' : '' ?>">
                                <a href="?selected=<?= htmlspecialchars($post['slug']) ?>" class="text-decoration-none">
                                    <h6 class="mb-2 <?= $post['slug'] === $selectedSlug ? 'text-primary fw-bold' : '' ?>">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('j.n.Y', strtotime($post['created_at'])) ?>
                                    </small>
                                </a>
                            </div>
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
