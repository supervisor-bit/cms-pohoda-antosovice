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
        <!-- Modern Header -->
        <header class="admin-header">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <i class="fas fa-cogs me-2"></i>
                        <h1>Správa webu</h1>
                    </a>
                </div>
                
                <nav class="admin-nav">
                    <ul>
                        <li><a href="index.php" <?= $current_page === 'index' ? 'class="active"' : '' ?>>
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a></li>
                        <li><a href="pages.php" <?= $current_page === 'pages' ? 'class="active"' : '' ?>>
                            <i class="fas fa-file-alt me-2"></i>Stránky
                        </a></li>
                        <li><a href="posts.php" <?= $current_page === 'posts' ? 'class="active"' : '' ?>>
                            <i class="fas fa-newspaper me-2"></i>Články
                        </a></li>
                        <li><a href="settings.php" <?= $current_page === 'settings' ? 'class="active"' : '' ?>>
                            <i class="fas fa-cog me-2"></i>Nastavení
                        </a></li>
                        <li><a href="quick_links.php" <?= $current_page === 'quick_links' ? 'class="active"' : '' ?>>
                            <i class="fas fa-external-link-alt me-2"></i>Rychlé odkazy
                        </a></li>
                        <li><a href="profile.php" <?= $current_page === 'profile' ? 'class="active"' : '' ?>>
                            <i class="fas fa-user-cog me-2"></i>Profil
                        </a></li>
                        <li><a href="../" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>Zobrazit web
                        </a></li>
                        <li><a href="logout.php" class="logout-link">
                            <i class="fas fa-sign-out-alt me-2"></i>Odhlásit se
                        </a></li>
                    </ul>
                </nav>
                
                <div class="user-info">
                    <i class="fas fa-user-circle me-2"></i>
                    Přihlášen jako: <strong><?= $_SESSION['admin_username'] ?? 'Admin' ?></strong>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-content"><?php
