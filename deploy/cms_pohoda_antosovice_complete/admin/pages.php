<?php
require_once '../config.php';
requireLogin();

$success_message = '';
$error_message = '';

// Zpracování akcí
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

// Přidání/editace stránky
if ($action === 'add' || $action === 'edit') {
    $page = null;
    
    if ($action === 'edit' && $id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$page) {
            $error_message = 'Stránka nebyla nalezena.';
            $action = 'list';
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = sanitize($_POST['title'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        $content = $_POST['content'] ?? '';
        $meta_description = sanitize($_POST['meta_description'] ?? '');
        $status = $_POST['status'] ?? 'published';
        $parent_slug = empty($_POST['parent_slug']) ? null : sanitize($_POST['parent_slug']);
        $icon = sanitize($_POST['icon'] ?? '');
        $menu_order = intval($_POST['menu_order'] ?? 0);
        $has_sidebar_menu = isset($_POST['has_sidebar_menu']) ? 1 : 0;
        
        if (empty($title) || empty($slug)) {
            $error_message = 'Název a slug jsou povinné.';
        } else {
            try {
                if ($action === 'add') {
                    // Kontrola unikátnosti slug
                    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
                    $stmt->execute([$slug]);
                    if ($stmt->fetch()) {
                        $error_message = 'Slug už existuje, vyberte jiný.';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, meta_description, status, parent_slug, icon, menu_order, has_sidebar_menu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $slug, $content, $meta_description, $status, $parent_slug, $icon, $menu_order, $has_sidebar_menu]);
                        $success_message = 'Stránka byla přidána.';
                        $action = 'list';
                    }
                } else {
                    // Kontrola unikátnosti slug (kromě aktuální stránky)
                    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND id != ?");
                    $stmt->execute([$slug, $id]);
                    if ($stmt->fetch()) {
                        $error_message = 'Slug už existuje, vyberte jiný.';
                    } else {
                        $stmt = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, meta_description = ?, status = ?, parent_slug = ?, icon = ?, menu_order = ?, has_sidebar_menu = ? WHERE id = ?");
                        $stmt->execute([$title, $slug, $content, $meta_description, $status, $parent_slug, $icon, $menu_order, $has_sidebar_menu, $id]);
                        $success_message = 'Stránka byla aktualizována.';
                        $action = 'list';
                    }
                }
            } catch (PDOException $e) {
                $error_message = 'Chyba při ukládání stránky.';
            }
        }
    }
}

// Bulk akce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action']) && !empty($_POST['selected_ids'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_ids = array_map('intval', $_POST['selected_ids']);
    $success_count = 0;
    
    try {
        $pdo->beginTransaction();
        
        foreach ($selected_ids as $id) {
            switch ($bulk_action) {
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
                    break;
                case 'publish':
                    $stmt = $pdo->prepare("UPDATE pages SET is_published = 1 WHERE id = ?");
                    break;
                case 'draft':
                    $stmt = $pdo->prepare("UPDATE pages SET is_published = 0 WHERE id = ?");
                    break;
                default:
                    continue 2;
            }
            
            if ($stmt->execute([$id])) {
                $success_count++;
            }
        }
        
        $pdo->commit();
        
        $action_names = [
            'delete' => 'smazáno',
            'publish' => 'publikováno', 
            'draft' => 'skryto'
        ];
        
        $success_message = "{$success_count} stránek bylo " . $action_names[$bulk_action] . ".";
        $action = 'list';
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Chyba při hromadné akci: ' . $e->getMessage();
        $action = 'list';
    }
}

// Mazání stránky
if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $success_message = 'Stránka byla smazána.';
        $action = 'list';
    } catch (PDOException $e) {
        $error_message = 'Chyba při mazání stránky.';
        $action = 'list';
    }
}

// Seznam stránek
if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM pages ORDER BY 
        CASE WHEN parent_slug IS NULL THEN title ELSE parent_slug END,
        CASE WHEN parent_slug IS NULL THEN 0 ELSE 1 END,
        title");
    $stmt->execute();
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Include admin header
include 'includes/admin_header.php';
?>

        <div class="admin-page-header">
            <div class="page-title-section">
                <h1><i class="fas fa-file-alt me-2"></i>Správa stránek</h1>
                <p class="page-subtitle">Zde můžete spravovat všechny stránky vašeho webu</p>
            </div>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                    <a href="pages.php?action=add" class="btn btn-success btn-lg">
                        <i class="fas fa-plus me-2"></i>Přidat novou stránku
                    </a>
                <?php else: ?>
                    <a href="pages.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Zpět na seznam
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="table-responsive">
                    <!-- Bulk Actions Panel -->
                    <div class="bulk-actions-panel" id="bulkActionsPanel" style="display: none;">
                        <div class="bulk-actions-content">
                            <span class="selected-count">0 vybraných stránek</span>
                            <div class="bulk-buttons">
                                <button type="button" class="btn btn-warning" onclick="bulkAction('publish')">
                                    <i class="fas fa-eye"></i> Publikovat
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="bulkAction('draft')">
                                    <i class="fas fa-eye-slash"></i> Skrýt
                                </button>
                                <button type="button" class="btn btn-danger" onclick="bulkAction('delete')">
                                    <i class="fas fa-trash"></i> Smazat
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                                    <i class="fas fa-times"></i> Zrušit výběr
                                </button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="40">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </div>
                                </th>
                                <th>Název</th>
                                <th>URL</th>
                                <th>Vytvořeno</th>
                                <th>Status</th>
                                <th width="150">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pages)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                        <h5>Zatím nemáte žádné stránky</h5>
                                        <p class="text-muted">Přidejte svou první stránku kliknutím na tlačítko výše</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pages as $page): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input page-checkbox" type="checkbox" 
                                                       value="<?= $page['id'] ?>" onchange="updateBulkActions()">
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($page['title']) ?></strong>
                                            <?php if ($page['parent_slug']): ?>
                                                <br><small class="text-muted">Podstránka: <?= htmlspecialchars($page['parent_slug']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($page['slug']) ?></code>
                                        </td>
                                        <td>
                                            <?= date('d.m.Y', strtotime($page['created_at'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $page['status'] === 'published' ? 'success' : 'warning' ?>">
                                                <?= $page['status'] === 'published' ? 'Publikováno' : 'Koncept' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="../page_new.php?slug=<?= htmlspecialchars($page['slug']) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Zobrazit">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="pages.php?action=edit&id=<?= $page['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning"
                                                   title="Upravit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="pages.php?action=delete&id=<?= $page['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   title="Smazat"
                                                   onclick="return confirm('Opravdu chcete smazat stránku \'<?= htmlspecialchars($page['title']) ?>\'?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php else: // Formulář pro přidání/editaci ?>
                <div class="user-friendly-form">
                    <div class="form-header">
                        <h2><i class="fas fa-<?= $action === 'add' ? 'plus-circle' : 'edit' ?> me-2"></i><?= $action === 'add' ? 'Přidat novou stránku' : 'Upravit stránku' ?></h2>
                        <p class="text-muted">Vyplňte jednoduše název a obsah vaší stránky</p>
                    </div>
                    
                    <form method="post">
                        <div class="simple-form-section">
                            <div class="form-group-simple">
                                <label for="title" class="form-label-simple">
                                    <i class="fas fa-heading me-2"></i>Název stránky
                                    <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       id="title" 
                                       name="title" 
                                       value="<?= htmlspecialchars($page['title'] ?? $_POST['title'] ?? '') ?>" 
                                       class="form-control-simple" 
                                       placeholder="Například: O nás, Služby, Kontakt..."
                                       required>
                                <small class="form-help">Tento název se zobrazí v menu a jako nadpis stránky</small>
                            </div>
                        </div>
                        
                        <!-- Skryté pole pro slug - automaticky generované -->
                        <input type="hidden" id="slug" name="slug" value="<?= htmlspecialchars($page['slug'] ?? $_POST['slug'] ?? '') ?>">
                        
                        <!-- Výchozí nastavení pro běžné uživatele -->
                        <input type="hidden" name="status" value="published">
                        <input type="hidden" name="parent_slug" value="">
                        <input type="hidden" name="icon" value="fas fa-file-alt">
                        <input type="hidden" name="menu_order" value="0">
                        <input type="hidden" name="meta_description" value="">
                        
                        <div class="form-group">
                            <label for="slug">URL slug *</label>
                            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($page['slug'] ?? $_POST['slug'] ?? '') ?>" class="form-control" required>
                            <small style="color: #666;">URL adresa stránky (bez mezer, jen a-z, 0-9, pomlčky)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="parent_slug">Nadřazená stránka</label>
                            <select id="parent_slug" name="parent_slug" class="form-control">
                                <option value="">-- Hlavní stránka (bez submenu) --</option>
                                <?php
                                $parent_stmt = $pdo->prepare("SELECT slug, title FROM pages WHERE parent_slug IS NULL ORDER BY title");
                                $parent_stmt->execute();
                                $parent_pages = $parent_stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($parent_pages as $parent_page):
                                    $selected = ($page['parent_slug'] ?? $_POST['parent_slug'] ?? '') === $parent_page['slug'] ? ' selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($parent_page['slug']) ?>"<?= $selected ?>>
                                    <?= htmlspecialchars($parent_page['title']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color: #666;">Vyberte, pokud má být tato stránka v submenu jiné stránky</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="has_sidebar_menu" name="has_sidebar_menu" value="1" <?= isset($page['has_sidebar_menu']) && $page['has_sidebar_menu'] == 1 ? 'checked' : '' ?> class="form-check-input">
                                <label for="has_sidebar_menu" class="form-check-label">
                                    <strong>Zobrazit boční menu</strong>
                                </label>
                            </div>
                            <small class="form-text">Zaškrtněte pokud má tato stránka mít boční menu s podstránkami. Podstránky se zobrazí v pruhovém menu vlevo.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="icon">Ikona (FontAwesome)</label>
                            <div class="d-flex align-items-center gap-3">
                                <div style="flex: 1;">
                                    <input type="text" id="icon" name="icon" value="<?= htmlspecialchars($page['icon'] ?? $_POST['icon'] ?? '') ?>" class="form-control" placeholder="např: fas fa-home">
                                </div>
                                <div id="icon-preview" class="d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 2px solid #ddd; border-radius: 5px; background: #f8f9fa;">
                                    <i id="icon-display" class="<?= htmlspecialchars($page['icon'] ?? $_POST['icon'] ?? 'fas fa-file-alt') ?>" style="font-size: 18px; color: #666;"></i>
                                </div>
                            </div>
                            <small style="color: #666;">FontAwesome ikona pro menu - <a href="https://fontawesome.com/icons" target="_blank">seznam ikon</a> | Náhled se zobrazí vpravo</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="menu_order">Pořadí v menu</label>
                            <input type="number" id="menu_order" name="menu_order" value="<?= htmlspecialchars($page['menu_order'] ?? $_POST['menu_order'] ?? '0') ?>" class="form-control">
                            <small style="color: #666;">0 a vyšší = zobrazí se v menu, záporné číslo = skryje z menu (ale stránka zůstane dostupná přes odkaz)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="meta_description">Meta popis (pro vyhledávače)</label>
                            <textarea id="meta_description" name="meta_description" class="form-control" rows="2" maxlength="160"><?= htmlspecialchars($page['meta_description'] ?? $_POST['meta_description'] ?? '') ?></textarea>
                            <small style="color: #666;">Maximálně 160 znaků</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Obsah stránky</label>
                            
                            <!-- Image Insert Panel -->
                            <div class="image-insert-panel" style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                                <h6 style="margin-bottom: 15px;"><i class="fas fa-image me-2"></i>Vložení obrázku</h6>
                                
                                <!-- Tab Navigation -->
                                <ul class="nav nav-tabs" id="imageTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="url-tab" data-bs-toggle="tab" data-bs-target="#url-pane" type="button">
                                            <i class="fas fa-link me-1"></i>Z URL
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-pane" type="button">
                                            <i class="fas fa-upload me-1"></i>Z disku
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content mt-3" id="imageTabContent">
                                    <!-- URL Tab -->
                                    <div class="tab-pane fade show active" id="url-pane">
                                        <div class="row align-items-end">
                                            <div class="col-md-8">
                                                <label for="image-url" class="form-label">URL obrázku:</label>
                                                <input type="url" id="image-url" class="form-control" placeholder="https://example.com/obrazek.jpg">
                                                <small class="text-muted">Vložte URL obrázku (např. z Google Maps, Wikipedie, atd.)</small>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" class="btn btn-primary w-100" onclick="insertImageFromUrl()">
                                                    <i class="fas fa-plus me-2"></i>Vložit obrázek
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-info">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <strong>Tip:</strong> Pro Google Mapy klikněte pravým tlačítkem na mapu → "Kopírovat adresu obrázku"
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Upload Tab -->
                                    <div class="tab-pane fade" id="upload-pane">
                                        <div class="row align-items-end">
                                            <div class="col-md-8">
                                                <label for="image-file" class="form-label">Vybrat soubor:</label>
                                                <input type="file" id="image-file" accept="image/*" class="form-control">
                                                <small class="text-muted">JPG, PNG, GIF, WEBP (max 5MB)</small>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" class="btn btn-success w-100" onclick="uploadAndInsertImage()">
                                                    <i class="fas fa-upload me-2"></i>Nahrát a vložit
                                                </button>
                                            </div>
                                        </div>
                                        <div id="upload-status" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <textarea id="content" name="content" class="form-control-simple content-editor" rows="12" placeholder="Zde napište obsah vaší stránky...

Příklad:
Vítejte na naší stránke!

Jsme rádi, že jste nás navštívili. Naše společnost se zabývá...

Kontaktujte nás na telefonu 123 456 789."><?= htmlspecialchars($page['content'] ?? $_POST['content'] ?? '') ?></textarea>
                            <small class="form-help">
                                <strong>Základní formátování:</strong> 
                                <code>&lt;strong&gt;tučný text&lt;/strong&gt;</code>, 
                                <code>&lt;em&gt;kurzíva&lt;/em&gt;</code>, 
                                <code>&lt;br&gt;</code> pro nový řádek
                            </small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>
                                <?= $action === 'add' ? 'Vytvořit stránku' : 'Uložit změny' ?>
                            </button>
                            <a href="pages.php" class="btn btn-outline-secondary btn-lg ms-3">
                                <i class="fas fa-times me-2"></i>Zrušit
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Automatické generování slug z názvu stránky
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // odstranit speciální znaky
                .replace(/\s+/g, '-') // nahradit mezery pomlčkami
                .replace(/-+/g, '-') // nahradit více pomlček jednou
                .trim('-'); // odstranit pomlčky na začátku a konci
            
            document.getElementById('slug').value = slug;
        });

        // Inicializace CKEditor 5 WYSIWYG editoru jen pokud existuje normální formulář
        ClassicEditor
            .create(document.querySelector('#content'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'insertTable', 'blockQuote', '|',
                        'undo', 'redo', '|',
                        'sourceEditing'
                    ]
                },
                language: 'cs',
                table: {
                    contentToolbar: [
                        'tableColumn', 'tableRow', 'mergeTableCells'
                    ]
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                    ]
                },
                placeholder: 'Začněte psát obsah stránky...'
            })
            .then(editor => {
                window.editor = editor;
            })
            .catch(error => {
                console.error('Chyba při inicializaci CKEditor:', error);
            });

        // Automatické generování slug z názvu
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title
                .toLowerCase()
                .replace(/[áàâäã]/g, 'a')
                .replace(/[čćç]/g, 'c')
                .replace(/[ďđ]/g, 'd')
                .replace(/[éèêë]/g, 'e')
                .replace(/[íìîï]/g, 'i')
                .replace(/[ľĺ]/g, 'l')
                .replace(/[ňñ]/g, 'n')
                .replace(/[óòôöõ]/g, 'o')
                .replace(/[ř]/g, 'r')
                .replace(/[šś]/g, 's')
                .replace(/[ť]/g, 't')
                .replace(/[úůű]/g, 'u')
                .replace(/[ýÿ]/g, 'y')
                .replace(/[žź]/g, 'z')
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            
            document.getElementById('slug').value = slug;
        });

        // Live náhled ikon
        document.getElementById('icon').addEventListener('input', function() {
            const iconClass = this.value.trim();
            const iconDisplay = document.getElementById('icon-display');
            const iconPreview = document.getElementById('icon-preview');
            
            if (iconClass) {
                iconDisplay.className = iconClass;
                iconPreview.style.borderColor = '#28a745';
                iconPreview.style.backgroundColor = '#f8fff9';
            } else {
                iconDisplay.className = 'fas fa-file-alt';
                iconPreview.style.borderColor = '#ddd';
                iconPreview.style.backgroundColor = '#f8f9fa';
            }
        });

        // Populární ikony - rychlé vložení
        const popularIcons = [
            'fas fa-home',
            'fas fa-info-circle',
            'fas fa-phone',
            'fas fa-bed',
            'fas fa-euro-sign',
            'fas fa-users',
            'fas fa-shield-alt',
            'fas fa-file-alt',
            'fas fa-clipboard-list',
            'fas fa-calendar-alt',
            'fas fa-clock',
            'fas fa-gavel',
            'fas fa-parking',
            'fas fa-recycle',
            'fas fa-star',
            'fas fa-heart',
            'fas fa-leaf',
            'fas fa-camera',
            'fas fa-envelope',
            'fas fa-map-marker-alt'
        ];

        // Vytvoření funkce pro ikony - vyřešíme později pokud bude potřeba
        // Zatím se zaměřujeme na vkládání obrázků

        // Upload a vložení obrázku z disku
        function uploadAndInsertImage() {
            const fileInput = document.getElementById('image-file');
            const file = fileInput.files[0];
            const statusDiv = document.getElementById('upload-status');
            
            if (!file) {
                statusDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Prosím, vyberte soubor.</div>';
                return;
            }
            
            // Kontrola velikosti (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                statusDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Soubor je příliš velký (max 5MB).</div>';
                return;
            }
            
            // Kontrola typu souboru
            if (!file.type.startsWith('image/')) {
                statusDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Prosím, vyberte obrázek.</div>';
                return;
            }
            
            statusDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Nahrávám...</div>';
            
            // Vytvoření FormData pro upload
            const formData = new FormData();
            formData.append('image', file);
            
            // Upload na server
            fetch('upload_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Úspěšný upload - vložit do editoru
                    const imageUrl = data.url;
                    
                    if (window.editor) {
                        const imageHtml = `<p><img src="${imageUrl}" alt="${file.name}" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></p>`;
                        
                        window.editor.model.change(writer => {
                            const viewFragment = window.editor.data.processor.toView(imageHtml);
                            const modelFragment = window.editor.data.toModel(viewFragment);
                            window.editor.model.insertContent(modelFragment);
                        });
                        
                        statusDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check me-2"></i>Obrázek byl nahrán a vložen!</div>';
                        fileInput.value = ''; // Vyčistit input
                        
                        // Vyčistit status po 3 sekundách
                        setTimeout(() => {
                            statusDiv.innerHTML = '';
                        }, 3000);
                    }
                } else {
                    statusDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>${data.error}</div>`;
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                statusDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Chyba při nahrávání.</div>';
            });
        }
        
        // Vložení obrázku z URL
        function insertImageFromUrl() {
            const imageUrl = document.getElementById('image-url').value.trim();
            
            if (!imageUrl) {
                alert('Prosím, zadejte URL obrázku.');
                return;
            }
            
            // Kontrola, zda URL vypadá jako obrázek
            const imageExtensions = /\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i;
            if (!imageExtensions.test(imageUrl) && !imageUrl.includes('maps.googleapis.com') && !imageUrl.includes('googleusercontent.com')) {
                if (!confirm('URL nevypadá jako obrázek. Chcete pokračovat?')) {
                    return;
                }
            }
            
            if (window.editor) {
                // Vytvoření HTML pro obrázek
                const imageHtml = `<p><img src="${imageUrl}" alt="Obrázek" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></p>`;
                
                // Vložení do editoru
                window.editor.model.change(writer => {
                    const viewFragment = window.editor.data.processor.toView(imageHtml);
                    const modelFragment = window.editor.data.toModel(viewFragment);
                    window.editor.model.insertContent(modelFragment);
                });
                
                // Vyčištění pole a zobrazení potvrzení
                document.getElementById('image-url').value = '';
                
                // Krátké vizuální potvrzení
                const button = document.querySelector('button[onclick="insertImageFromUrl()"]');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check me-2"></i>Vloženo!';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                }, 1500);
            } else {
                alert('Editor není připraven. Zkuste to za chvíli.');
            }
        }
        
        // Vložení obrázku při stisknutí Enter
        document.addEventListener('DOMContentLoaded', function() {
            const imageUrlInput = document.getElementById('image-url');
            if (imageUrlInput) {
                imageUrlInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        insertImageFromUrl();
                    }
                });
            }
        });
    </script>

    <!-- Bulk Actions JavaScript -->
    <script>
        // Toggle select all checkbox
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.page-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            
            updateBulkActions();
        }
        
        // Update bulk actions panel
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.page-checkbox:checked');
            const panel = document.getElementById('bulkActionsPanel');
            const countSpan = document.querySelector('.selected-count');
            
            if (checkboxes.length > 0) {
                panel.style.display = 'block';
                countSpan.textContent = checkboxes.length + ' vybraných stránek';
            } else {
                panel.style.display = 'none';
                document.getElementById('selectAll').checked = false;
            }
        }
        
        // Clear all selections
        function clearSelection() {
            document.querySelectorAll('.page-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('selectAll').checked = false;
            updateBulkActions();
        }
        
        // Perform bulk action
        function bulkAction(action) {
            const checkboxes = document.querySelectorAll('.page-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);
            
            if (ids.length === 0) {
                alert('Nejprve vyberte stránky');
                return;
            }
            
            let confirmMsg = '';
            switch(action) {
                case 'delete':
                    confirmMsg = `Opravdu chcete smazat ${ids.length} vybraných stránek?`;
                    break;
                case 'publish':
                    confirmMsg = `Publikovat ${ids.length} vybraných stránek?`;
                    break;
                case 'draft':
                    confirmMsg = `Skrýt ${ids.length} vybraných stránek?`;
                    break;
            }
            
            if (confirm(confirmMsg)) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                // Add action
                const actionInput = document.createElement('input');
                actionInput.name = 'bulk_action';
                actionInput.value = action;
                form.appendChild(actionInput);
                
                // Add IDs
                ids.forEach(id => {
                    const idInput = document.createElement('input');
                    idInput.name = 'selected_ids[]';
                    idInput.value = id;
                    form.appendChild(idInput);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <!-- Bootstrap JS pro taby -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'includes/admin_footer.php'; ?>
