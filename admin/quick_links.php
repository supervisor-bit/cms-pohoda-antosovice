<?php
require_once '../config.php';

// Kontrola přihlášení
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Zpracování formulářů
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $stmt = $pdo->prepare("INSERT INTO quick_links (title, url, description, position, is_active) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['url'],
                        $_POST['description'] ?? '',
                        intval($_POST['position'] ?? 0),
                        isset($_POST['is_active']) ? 1 : 0
                    ]);
                    $success = "Rychlý odkaz byl přidán.";
                    break;
                    
                case 'edit':
                    $stmt = $pdo->prepare("UPDATE quick_links SET title = ?, url = ?, description = ?, position = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['url'],
                        $_POST['description'] ?? '',
                        intval($_POST['position'] ?? 0),
                        isset($_POST['is_active']) ? 1 : 0,
                        intval($_POST['id'])
                    ]);
                    $success = "Rychlý odkaz byl upraven.";
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM quick_links WHERE id = ?");
                    $stmt->execute([intval($_POST['id'])]);
                    $success = "Rychlý odkaz byl smazán.";
                    break;
            }
        }
    } catch (PDOException $e) {
        $error = "Databázová chyba: " . $e->getMessage();
    }
}

// Načtení rychlých odkazů
try {
    $stmt = $pdo->query("SELECT * FROM quick_links ORDER BY position ASC, title ASC");
    $quickLinks = $stmt->fetchAll();
} catch (PDOException $e) {
    $quickLinks = [];
    $error = "Chyba při načítání odkazů: " . $e->getMessage();
}

// Editace - načtení dat
$editLink = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM quick_links WHERE id = ?");
        $stmt->execute([intval($_GET['edit'])]);
        $editLink = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Chyba při načítání odkazu pro editaci.";
    }
}

// Načtení dostupných stránek
try {
    $stmt = $pdo->query("SELECT title, slug FROM pages WHERE is_published = 1 ORDER BY title");
    $pages = $stmt->fetchAll();
} catch (PDOException $e) {
    $pages = [];
}
?>
?>

<?php include 'includes/admin_header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-external-link-alt me-2"></i>Správa rychlých odkazů</h2>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Zpět do administrace
                </a>
            </div>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check me-2"></i><?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Formulář pro přidání/úpravu -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-plus me-2"></i><?= $editLink ? 'Upravit rychlý odkaz' : 'Přidat rychlý odkaz' ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="<?= $editLink ? 'edit' : 'add' ?>">
                        <?php if ($editLink): ?>
                            <input type="hidden" name="id" value="<?= $editLink['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Název odkazu *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?= $editLink ? htmlspecialchars($editLink['title']) : '' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="position" class="form-label">Pořadí</label>
                                    <input type="number" class="form-control" id="position" name="position" 
                                           value="<?= $editLink ? $editLink['position'] : 0 ?>" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="url" class="form-label">URL odkazu *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="url" name="url" 
                                       value="<?= $editLink ? htmlspecialchars($editLink['url']) : '' ?>" required>
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    Stránky
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="setUrl('index.php')">Domovská stránka</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php foreach ($pages as $page): ?>
                                        <li><a class="dropdown-item" href="#" 
                                               onclick="setUrl('page_new.php?slug=<?= $page['slug'] ?>')"><?= htmlspecialchars($page['title']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="form-text">Můžete použít relativní URL (např. page_new.php?slug=kontakt) nebo absolutní URL</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Popis</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?= $editLink ? htmlspecialchars($editLink['description']) : '' ?></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?= (!$editLink || $editLink['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                Aktivní odkaz
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?= $editLink ? 'Uložit změny' : 'Přidat odkaz' ?>
                            </button>
                            <?php if ($editLink): ?>
                                <a href="quick_links.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Zrušit úpravu
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Seznam rychlých odkazů -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Stávající rychlé odkazy</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($quickLinks)): ?>
                        <p class="text-muted text-center py-4">Žádné rychlé odkazy nejsou vytvořeny.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pořadí</th>
                                        <th>Název</th>
                                        <th>URL</th>
                                        <th>Popis</th>
                                        <th>Stav</th>
                                        <th>Akce</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quickLinks as $link): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary"><?= $link['position'] ?></span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($link['title']) ?></strong>
                                            </td>
                                            <td>
                                                <code class="small"><?= htmlspecialchars($link['url']) ?></code>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= htmlspecialchars($link['description']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($link['is_active']): ?>
                                                    <span class="badge bg-success">Aktivní</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Neaktivní</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?edit=<?= $link['id'] ?>" class="btn btn-outline-primary" title="Upravit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteLink(<?= $link['id'] ?>, '<?= addslashes($link['title']) ?>')" title="Smazat">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setUrl(url) {
    document.getElementById('url').value = url;
}

function deleteLink(id, title) {
    if (confirm('Opravdu chcete smazat rychlý odkaz "' + title + '"?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.name = 'id';
        idInput.value = id;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>