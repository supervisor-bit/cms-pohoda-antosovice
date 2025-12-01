<?php
// Admin layout header
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Správa webu - <?= $current_page === 'index' ? 'Dashboard' : ucfirst($current_page) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/admin.css?v=<?= time() ?>&nocache=<?= uniqid() ?>" />
    
    <!-- CKEditor 5 -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/translations/cs.js"></script>
</head>
<body class="admin-body">

    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="sidebar-logo">
                    <i class="fas fa-cogs"></i>
                    <span class="logo-text">Správa webu</span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?= $current_page === 'index' ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pages.php" class="nav-link <?= $current_page === 'pages' ? 'active' : '' ?>">
                            <i class="fas fa-file-alt"></i>
                            <span class="nav-text">Stránky</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="homepage_cards.php" class="nav-link <?= $current_page === 'homepage_cards' ? 'active' : '' ?>">
                            <i class="fas fa-th-large"></i>
                            <span class="nav-text">Karty (homepage)</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="posts.php" class="nav-link <?= $current_page === 'posts' ? 'active' : '' ?>">
                            <i class="fas fa-newspaper"></i>
                            <span class="nav-text">Články</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="events.php" class="nav-link <?= $current_page === 'events' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="nav-text">Kalendář akcí</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="gallery.php" class="nav-link <?= $current_page === 'gallery' ? 'active' : '' ?>">
                            <i class="fas fa-images"></i>
                            <span class="nav-text">Galerie okolí</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link <?= $current_page === 'settings' ? 'active' : '' ?>">
                            <i class="fas fa-cog"></i>
                            <span class="nav-text">Nastavení</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link <?= $current_page === 'profile' ? 'active' : '' ?>">
                            <i class="fas fa-user-cog"></i>
                            <span class="nav-text">Profil</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../" target="_blank" class="nav-link">
                            <i class="fas fa-external-link-alt"></i>
                            <span class="nav-text">Zobrazit web</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <div class="user-details">
                        <span class="user-label">Přihlášen jako:</span>
                        <span class="user-name"><?= $_SESSION['admin_username'] ?? 'Admin' ?></span>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-text">Odhlásit se</span>
                </a>
            </div>
        </aside>

        <!-- Top Header for mobile -->
        <header class="top-header">
            <button class="mobile-sidebar-toggle" id="mobileSidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-title">
                <h1><?= $current_page === 'index' ? 'Dashboard' : ucfirst($current_page) ?></h1>
            </div>
            <div class="mobile-user-info">
                <i class="fas fa-user-circle"></i>
                <span><?= $_SESSION['admin_username'] ?? 'Admin' ?></span>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            
        <!-- Sidebar JavaScript -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const mainContent = document.querySelector('.main-content');
            
            // Toggle sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('sidebar-collapsed');
            }
            
            // Toggle mobile sidebar
            function toggleMobileSidebar() {
                sidebar.classList.toggle('mobile-open');
            }
            
            // Event listeners
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            
            if (mobileSidebarToggle) {
                mobileSidebarToggle.addEventListener('click', toggleMobileSidebar);
            }
            
            // Close mobile sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !mobileSidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('mobile-open');
                    }
                }
            });
            
            // Handle resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('mobile-open');
                }
            });
        });
        </script>
        <?php
