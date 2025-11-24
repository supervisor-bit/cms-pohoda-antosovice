<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Nastavení HTTP 404 hlavičky
http_response_code(404);

// Načtení nastavení webu
try {
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
        $quickLinks = [];
    }
    
    // Výchozí hodnoty
    $settings = array_merge([
        'site_title' => 'Pohoda Antošovice',
        'site_description' => 'Naturistický kemp - relaxace v harmonii s přírodou',
        'contact_email' => 'info@pohoda-antosovice.cz',
        'contact_phone' => '+420 123 456 789'
    ], $settings);
    
} catch (PDOException $e) {
    $settings = [
        'site_title' => 'Pohoda Antošovice',
        'site_description' => 'Naturistický kemp - relaxace v harmonii s přírodou'
    ];
    $quickLinks = [];
}

// Načíst menu
try {
    $menu = generateBootstrapMenu($pdo, '404');
    
    if (empty($menu)) {
        $menu = '<li class="nav-item"><a class="nav-link" href="index.php">Domů</a></li>';
        $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=o-mist">O místě</a></li>';
        $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=kontakt">Kontakt</a></li>';
    }
    
} catch (Exception $e) {
    $menu = '<li class="nav-item"><a class="nav-link" href="index.php">Domů</a></li>';
    $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=o-mist">O místě</a></li>';
    $menu .= '<li class="nav-item"><a class="nav-link" href="page_new.php?slug=kontakt">Kontakt</a></li>';
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Stránka nenalezena | <?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></title>
    <meta name="description" content="<?= htmlspecialchars($settings['site_description'] ?? 'Naturistický kemp - relaxace v harmonii s přírodou') ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="assets/css/fontawesome-all.min.css" rel="stylesheet">
    
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

        /* Error section */
        .error-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 0;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(45, 80, 22, 0.1);
            border: 1px solid rgba(45, 80, 22, 0.1);
        }

        .error-icon {
            font-size: 6rem;
            color: #6f9183;
            margin-bottom: 2rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .error-code {
            font-size: 4rem;
            font-weight: bold;
            color: #6f9183;
            margin-bottom: 1rem;
        }

        .error-title {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .error-message {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 3rem;
            line-height: 1.7;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .btn-primary-custom {
            background: #6f9183;
            border: 2px solid #6f9183;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .btn-primary-custom:hover {
            background: #5a7a6b;
            border-color: #5a7a6b;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(45, 80, 22, 0.3);
            color: white;
        }

        .btn-secondary-custom {
            background: transparent;
            border: 2px solid #6f9183;
            color: #6f9183;
            padding: 10px 28px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .btn-secondary-custom:hover {
            background: #6f9183;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(45, 80, 22, 0.2);
        }

        .helpful-links {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(45, 80, 22, 0.2);
        }

        .helpful-links h5 {
            color: #6f9183;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .helpful-links a {
            color: #6f9183;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .helpful-links a:hover {
            color: #5a7a6b;
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            background: #6f9183 !important;
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

        @media (max-width: 768px) {
            .error-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .error-icon {
                font-size: 4rem;
            }
            
            .error-code {
                font-size: 3rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }

            .helpful-links a {
                display: block;
                margin: 0.5rem 0;
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

    <main class="error-section">
        <div class="container">
            <div class="error-container">
                <div class="error-icon">
                    <i class="bi bi-compass"></i>
                </div>
                
                <div class="error-code">404</div>
                <h1 class="error-title">Stránka nenalezena</h1>
                <p class="error-message">
                    Omlouváme se, ale stránka kterou hledáte neexistuje nebo byla přesunuta.<br>
                    Možná jste se dostali sem přes zastaralý odkaz nebo došlo k překlepu v URL.
                </p>
                
                <div class="error-actions">
                    <a href="index.php" class="btn-primary-custom">
                        <i class="bi bi-house-fill"></i>
                        Domovská stránka
                    </a>
                    <a href="javascript:history.back()" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        Zpět
                    </a>
                </div>
                
                <div class="helpful-links">
                    <h5>Možná hledáte:</h5>
                    <a href="page_new.php?slug=o-organizaci">O organizaci</a>
                    <a href="page_new.php?slug=provozni-rad">Provozní řád</a>
                    <a href="page_new.php?slug=ubytovani-stravovani">Ubytování</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= htmlspecialchars($settings['site_title'] ?? 'Pohoda Antošovice') ?></h5>
                    <p><?= htmlspecialchars($settings['site_description'] ?? '') ?></p>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($quickLinks)): ?>
                        <h5>Rychlé odkazy</h5>
                        <div class="quick-links">
                            <?php foreach ($quickLinks as $link): ?>
                                <a href="<?= htmlspecialchars($link['url']) ?>" title="<?= htmlspecialchars($link['description'] ?? '') ?>">
                                    <?= htmlspecialchars($link['title']) ?>
                                </a>
                            <?php endforeach; ?>
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
