<?php
require_once '../config.php';
requireLogin();

$success_message = '';
$error_message = '';

// Zpracování akcí
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

// Přidání/editace článku
if ($action === 'add' || $action === 'edit') {
    $post = null;
    
    if ($action === 'edit' && $id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            $error_message = 'Článek nebyl nalezen.';
            $action = 'list';
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = sanitize($_POST['title'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        $content = $_POST['content'] ?? '';
        $excerpt = sanitize($_POST['excerpt'] ?? '');
        $status = $_POST['status'] ?? 'published';
        
        if (empty($title) || empty($slug)) {
            $error_message = 'Název a slug jsou povinné.';
        } else {
            try {
                if ($action === 'add') {
                    // Kontrola unikátnosti slug
                    $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
                    $stmt->execute([$slug]);
                    if ($stmt->fetch()) {
                        $error_message = 'Slug už existuje, vyberte jiný.';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, excerpt, status) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $slug, $content, $excerpt, $status]);
                        $success_message = 'Článek byl přidán.';
                        $action = 'list';
                    }
                } else {
                    // Kontrola unikátnosti slug (kromě aktuálního článku)
                    $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = ? AND id != ?");
                    $stmt->execute([$slug, $id]);
                    if ($stmt->fetch()) {
                        $error_message = 'Slug už existuje, vyberte jiný.';
                    } else {
                        $stmt = $pdo->prepare("UPDATE posts SET title = ?, slug = ?, content = ?, excerpt = ?, status = ? WHERE id = ?");
                        $stmt->execute([$title, $slug, $content, $excerpt, $status, $id]);
                        $success_message = 'Článek byl aktualizován.';
                        $action = 'list';
                    }
                }
            } catch (PDOException $e) {
                $error_message = 'Chyba při ukládání článku.';
            }
        }
    }
}

// Mazání článku
if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $success_message = 'Článek byl smazán.';
        $action = 'list';
    } catch (PDOException $e) {
        $error_message = 'Chyba při mazání článku.';
        $action = 'list';
    }
}

// Seznam článků
if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Include admin header
include 'includes/admin_header.php';
?>

        <div class="admin-page-header">
            <div class="page-title-section">
                <h1><i class="fas fa-newspaper me-2"></i>Správa článků</h1>
                <p class="page-subtitle">Zde můžete spravovat všechny články vašeho webu</p>
            </div>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                    <a href="posts.php?action=add" class="btn btn-success btn-lg">
                        <i class="fas fa-plus me-2"></i>Přidat nový článek
                    </a>
                <?php else: ?>
                    <a href="posts.php" class="btn btn-secondary btn-lg">
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
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Název</th>
                                <th>URL</th>
                                <th>Vytvořeno</th>
                                <th>Status</th>
                                <th width="150">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                        <h5>Zatím nemáte žádné články</h5>
                                        <p class="text-muted">Přidejte svůj první článek kliknutím na tlačítko výše</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($post['title']) ?></strong>
                                            <?php if ($post['excerpt']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars(mb_substr($post['excerpt'], 0, 80)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($post['slug']) ?></code>
                                        </td>
                                        <td>
                                            <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $post['status'] === 'published' ? 'success' : 'warning' ?>">
                                                <?= $post['status'] === 'published' ? 'Publikováno' : 'Koncept' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="../post_new.php?slug=<?= htmlspecialchars($post['slug']) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Zobrazit">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="posts.php?action=edit&id=<?= $post['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning"
                                                   title="Upravit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="posts.php?action=delete&id=<?= $post['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   title="Smazat"
                                                   onclick="return confirm('Opravdu chcete smazat článek \'<?= htmlspecialchars($post['title']) ?>\'?')">
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
                        <h2><i class="fas fa-<?= $action === 'add' ? 'plus-circle' : 'edit' ?> me-2"></i><?= $action === 'add' ? 'Přidat nový článek' : 'Upravit článek' ?></h2>
                        <p class="text-muted">Vyplňte jednoduše název a obsah vašeho článku</p>
                    </div>
                
                <form method="post">
                    <div class="section">
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label for="title">Název článku *</label>
                                <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title'] ?? $_POST['title'] ?? '') ?>" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="published"<?= ($post['status'] ?? $_POST['status'] ?? 'published') === 'published' ? ' selected' : '' ?>>Publikováno</option>
                                    <option value="draft"<?= ($post['status'] ?? $_POST['status'] ?? '') === 'draft' ? ' selected' : '' ?>>Koncept</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="slug">URL slug *</label>
                            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($post['slug'] ?? $_POST['slug'] ?? '') ?>" class="form-control" required>
                            <small class="form-text">URL adresa článku (bez mezer, jen a-z, 0-9, pomlčky)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="excerpt">Krátký popis (výňatek)</label>
                            <textarea id="excerpt" name="excerpt" class="form-control" rows="3"><?= htmlspecialchars($post['excerpt'] ?? $_POST['excerpt'] ?? '') ?></textarea>
                            <small style="color: #666;">Krátký popis článku pro náhled na hlavní stránce</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Obsah článku</label>
                            
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
                            
                            <textarea id="content" name="content" class="form-control editor"><?= htmlspecialchars($post['content'] ?? $_POST['content'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><?= $action === 'add' ? 'Přidat článek' : 'Uložit změny' ?></button>
                            <a href="posts.php" class="btn btn-secondary">Zrušit</a>
                        </div>
                    </div>
                </form>
                </div>
            <?php endif; ?>

<?php include 'includes/admin_footer.php'; ?>

    <script>
        // Inicializace CKEditor 5 WYSIWYG editoru
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
                placeholder: 'Začněte psát obsah článku...'
            })
            .then(editor => {
                window.editor = editor;
                console.log('CKEditor 5 byl úspěšně inicializován');
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

    <!-- Bootstrap JS pro taby -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'includes/admin_footer.php'; ?>
