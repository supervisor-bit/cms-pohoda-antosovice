<?php
require_once '../config.php';
requireLogin();

// Získání statistik pro dashboard
$stats = [];

// Počet stránek
$stmt = $pdo->query("SELECT COUNT(*) FROM pages");
$stats['pages'] = $stmt->fetchColumn();

// Počet článků
$stmt = $pdo->query("SELECT COUNT(*) FROM posts");
$stats['posts'] = $stmt->fetchColumn();

// Poslední články
$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 5");
$recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include admin header with themes
include 'includes/admin_header.php';
?>

            <!-- Hero Section -->
            <div class="hero-section">
                <div class="hero-background">
                    <div class="hero-pattern"></div>
                </div>
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="hero-badge">
                            <i class="fas fa-rocket"></i>
                            <span>Administrace</span>
                        </div>
                        <h1 class="hero-title">Správa webu</h1>
                        <p class="hero-subtitle">Spravujte obsah svého webu jednoduše a rychle. Vše na jednom místě.</p>
                        
                        <div class="hero-stats">
                            <div class="hero-stat">
                                <div class="stat-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-number"><?= $stats['pages'] ?></span>
                                    <span class="stat-label">Stránek</span>
                                </div>
                            </div>
                            <div class="hero-stat">
                                <div class="stat-icon">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-number"><?= $stats['posts'] ?></span>
                                    <span class="stat-label">Článků</span>
                                </div>
                            </div>
                            <div class="hero-stat">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-number"><?= date('j') ?></span>
                                    <span class="stat-label"><?= date('M') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="hero-actions">
                            <a href="pages.php?action=add" class="btn-hero primary">
                                <i class="fas fa-plus"></i>
                                <span>Nová stránka</span>
                            </a>
                            <a href="posts.php?action=add" class="btn-hero secondary">
                                <i class="fas fa-pen"></i>
                                <span>Nový článek</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="hero-visual">
                        <div class="visual-card">
                            <div class="card-header">
                                <div class="card-dots">
                                    <span></span><span></span><span></span>
                                </div>
                                <span class="card-title">Váš web</span>
                            </div>
                            <div class="card-content">
                                <div class="content-preview">
                                    <div class="preview-nav"></div>
                                    <div class="preview-hero"></div>
                                    <div class="preview-content">
                                        <div class="preview-line"></div>
                                        <div class="preview-line short"></div>
                                        <div class="preview-line"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="floating-elements">
                            <div class="float-element" style="--delay: 0s; --x: 20px; --y: -30px;">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="float-element" style="--delay: 1s; --x: -25px; --y: 20px;">
                                <i class="fas fa-image"></i>
                            </div>
                            <div class="float-element" style="--delay: 2s; --x: 30px; --y: 40px;">
                                <i class="fas fa-cog"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions-section">
                <h2 class="section-title">Rychlé akce</h2>
                <div class="quick-actions-grid">
                    <a href="pages.php" class="action-card">
                        <div class="card-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="card-content">
                            <h3>Upravit stránky</h3>
                            <p>Změňte obsah existujících stránek</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="posts.php" class="action-card">
                        <div class="card-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="card-content">
                            <h3>Spravovat články</h3>
                            <p>Prohlédněte a upravte všechny články</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="settings.php" class="action-card">
                        <div class="card-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="card-content">
                            <h3>Nastavení webu</h3>
                            <p>Změňte název, kontakt a další nastavení</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="quick_links.php" class="action-card">
                        <div class="card-icon">
                            <i class="fas fa-external-link-alt"></i>
                        </div>
                        <div class="card-content">
                            <h3>Rychlé odkazy</h3>
                            <p>Spravujte odkazy v patičce webu</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    
                    <button type="button" class="action-card" data-bs-toggle="modal" data-bs-target="#helpModal">
                        <div class="card-icon help">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="card-content">
                            <h3>Nápověda</h3>
                            <p>Jak používat administraci</p>
                        </div>
                        <div class="card-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </button>
                </div>
            </div>

<?php include 'includes/help_modal.php'; ?>
<?php include 'includes/admin_footer.php'; ?>
