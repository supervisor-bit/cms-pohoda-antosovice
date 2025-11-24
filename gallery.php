<?php
require_once 'config.php';
require_once 'includes/functions.php';

$current_page = 'gallery';
$current_slug = 'gallery';

// Načtení nastavení webu
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Fallback hodnoty pokud tabulka neexistuje
    $settings = [
        'site_title' => 'CMS Pohoda Antošovice',
        'site_description' => ''
    ];
}

// Načtení rychlých odkazů pro footer
try {
    $quickLinksStmt = $pdo->query("SELECT title, url, description FROM quick_links WHERE is_active = 1 ORDER BY position ASC LIMIT 3");
    $quickLinks = $quickLinksStmt->fetchAll();
} catch (PDOException $e) {
    $quickLinks = []; // Fallback pokud tabulka neexistuje
}

$site_title = $settings['site_title'] ?? 'CMS Pohoda Antošovice';
$site_description = $settings['site_description'] ?? '';

// Načíst menu
$menu = generateBootstrapMenu($pdo, $current_slug);

// Meta informace pro stránku
$page_title = 'Fotky okolí - ' . $site_title;
$meta_description = 'Galerie fotografií okolí Antošovic - příroda, památky a zajímavá místa v našem regionu.';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="assets/css/themes.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    
    <!-- Inline styles to match other pages -->
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

        /* Header stejný jako ostatní stránky */
        .page-header {
            background: #6f9183 !important;
            color: white;
            padding: 6rem 0 4rem;
            position: sticky;
            top: 80px;
            z-index: 100;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        /* Main Content */
        .main-content {
            padding: 4rem 0;
            position: relative;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.1);
            position: relative;
        }

        .content-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6f9183, var(--accent-color));
            border-radius: 20px 20px 0 0;
        }

        .content-card h1, .content-card h2 {
            color: #6f9183;
            margin-bottom: 1.5rem;
        }

        /* Primary buttons */
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

        /* Footer */
        footer {
            background: #6f9183 !important;
            color: rgba(255, 255, 255, 0.9);
            padding: 40px 0 20px;
            margin-top: 80px;
        }
        
        footer h5, footer h6 {
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

        /* Gallery specific styles */
        .gallery-item {
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        
        .gallery-image {
            border-radius: 15px;
        }

        /* Mobile optimizations */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                margin-top: 1rem;
                border-top: 1px solid rgba(255,255,255,0.1);
                padding-top: 1rem;
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
                position: relative;
                top: 0;
            }
            
            .main-content {
                padding: 40px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Include menu -->
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/sno-logo.png" alt="SNO Logo" class="navbar-logo me-2">
                <?= htmlspecialchars($site_title) ?>
            </a>
            
            <!-- Hamburger menu pro mobile -->
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
            <div class="page-header-content text-center">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-images me-3"></i>Fotky okolí
                </h1>
                <p class="lead">
                    Objevte krásy našeho regionu prostřednictvím fotografií přírody, památek a zajímavých míst v okolí Antošovic.
                </p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="main-content">
        <div class="container">
            <!-- Featured Photos Section -->
            <?php 
            $featured_photos = getGalleryPhotos($pdo, true);
            if (!empty($featured_photos)): 
            ?>
                <div class="content-card">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-star me-2"></i>Hlavní fotky
                    </h2>
                    <?= displayPhotoGallery($pdo, true, null, false, 3) ?>
                </div>
            <?php endif; ?>

            <!-- All Photos Section -->
            <div class="content-card">
                <h2 class="text-center mb-4">
                    <i class="fas fa-images me-2"></i>Všechny fotky
                </h2>
                
                <?php 
                $all_photos = getGalleryPhotos($pdo, false);
                if (!empty($all_photos)): 
                ?>
                    <?= displayPhotoGallery($pdo, false, null, false, 4) ?>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1 d-block mb-3 text-primary"></i>
                        <h4 class="text-primary">Zatím nejsou dostupné žádné fotky</h4>
                        <p class="mb-0">Fotografie budou brzy doplněny. Děkujeme za trpělivost!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Statistics -->
            <?php if (!empty($all_photos)): ?>
                <div class="content-card">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="bg-light p-4 rounded">
                                <i class="bi bi-images fs-1 text-primary d-block mb-2"></i>
                                <h5 class="text-primary"><?= count($all_photos) ?></h5>
                                <small class="text-muted">Celkem fotek</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="bg-light p-4 rounded">
                                <i class="bi bi-star-fill fs-1 text-warning d-block mb-2"></i>
                                <h5 class="text-primary"><?= count($featured_photos) ?></h5>
                                <small class="text-muted">Hlavních fotek</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="bg-light p-4 rounded">
                                <i class="bi bi-calendar fs-1 text-success d-block mb-2"></i>
                                <h5 class="text-primary"><?= date('Y') ?></h5>
                                <small class="text-muted">Aktuální rok</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Tlačítko zpět -->
            <div class="text-center my-5">
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Zpět na hlavní stránku
                </a>
            </div>
        </div>
    </section>

    <!-- Include footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-leaf me-2"></i><?= htmlspecialchars($site_title) ?></h5>
                    <p><?= htmlspecialchars($site_description ?: 'Naturistický kemp - relaxace v harmonii s přírodou') ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Kontakt</h6>
                    <p><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($settings['contact_email'] ?? 'info@pohoda-antosovice.cz') ?></p>
                    <p><i class="fas fa-phone me-2"></i><?= htmlspecialchars($settings['contact_phone'] ?? '+420 123 456 789') ?></p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($site_title) ?>. Všechna práva vyhrazena.</p>
            </div>
        </div>
    </footer>

    <!-- Modal pro zobrazení větší fotky -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="galleryModalImage" class="img-fluid" alt="">
                    <div id="galleryModalDescription" class="mt-3 text-muted"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/themes.js"></script>
    
    <script>
    function openGalleryModal(image, title, description) {
        document.getElementById('galleryModalImage').src = image;
        document.getElementById('galleryModalTitle').textContent = title || 'Fotka';
        document.getElementById('galleryModalDescription').textContent = description || '';
        
        const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
        modal.show();
    }
    
    // Hover efekt pro obrázky
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.gallery-image').forEach(img => {
            img.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });
            img.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    });
    </script>
</body>
</html>
</html>