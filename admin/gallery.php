<?php
require_once '../config.php';
requireLogin();

$success = '';
$error = '';

// Zpracování POST požadavků
if ($_POST) {
    try {
        if ($_POST['action'] === 'add-photo' && isset($_FILES['photo_file'])) {
            // Nahrání nové fotky
            $title = trim($_POST['photo_title']);
            $description = trim($_POST['photo_description'] ?? '');
            $alt_text = trim($_POST['alt_text'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            
            if (empty($title)) {
                throw new Exception("Název fotky je povinný.");
            }
            
            // Zpracování uploadu souboru
            $file = $_FILES['photo_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Chyba při nahrávání souboru.");
            }
            
            // Kontrola typu souboru
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception("Povolené jsou pouze obrázky (JPEG, PNG, GIF, WebP).");
            }
            
            // Kontrola velikosti (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("Soubor je příliš velký. Maximum je 5MB.");
            }
            
            // Vytvoření jedinečného názvu souboru
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $fileExtension;
            $uploadPath = '../uploads/gallery/' . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception("Nepodařilo se uložit soubor.");
            }
            
            // Získání rozměrů obrázku
            $imageInfo = getimagesize($uploadPath);
            $width = $imageInfo[0] ?? 0;
            $height = $imageInfo[1] ?? 0;
            
            // Uložení do databáze
            $stmt = $pdo->prepare("INSERT INTO gallery_photos (title, description, filename, file_path, file_size, image_width, image_height, alt_text, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $title,
                $description,
                $fileName,
                'uploads/gallery/' . $fileName,
                $file['size'],
                $width,
                $height,
                $alt_text,
                $is_featured,
                $sort_order
            ]);
            
            $success = "Fotka byla úspěšně přidána!";
            
        } elseif ($_POST['action'] === 'delete-photo') {
            // Smazání fotky
            $photoId = (int)$_POST['photo_id'];
            
            // Najít fotku pro smazání souboru
            $stmt = $pdo->prepare("SELECT file_path FROM gallery_photos WHERE id = ?");
            $stmt->execute([$photoId]);
            $photo = $stmt->fetch();
            
            if ($photo) {
                // Smazat soubor z disku
                $fullPath = '../' . $photo['file_path'];
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                
                // Smazat z databáze
                $stmt = $pdo->prepare("DELETE FROM gallery_photos WHERE id = ?");
                $stmt->execute([$photoId]);
                
                $success = "Fotka byla úspěšně smazána!";
            } else {
                $error = "Fotka nebyla nalezena.";
            }
            
        } elseif ($_POST['action'] === 'toggle-featured') {
            // Přepnutí featured status
            $photoId = (int)$_POST['photo_id'];
            $stmt = $pdo->prepare("UPDATE gallery_photos SET is_featured = NOT is_featured WHERE id = ?");
            $stmt->execute([$photoId]);
            $success = "Status hlavní fotky byl změněn!";
            
        } elseif ($_POST['action'] === 'toggle-published') {
            // Přepnutí published status
            $photoId = (int)$_POST['photo_id'];
            $stmt = $pdo->prepare("UPDATE gallery_photos SET is_published = NOT is_published WHERE id = ?");
            $stmt->execute([$photoId]);
            $success = "Status publikování byl změněn!";
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Načtení všech fotek
try {
    $stmt = $pdo->query("SELECT * FROM gallery_photos ORDER BY sort_order ASC, created_at DESC");
    $photos = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Chyba při načítání fotek: " . $e->getMessage();
    $photos = [];
}

require_once 'includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="admin-sidebar">
                <h5><i class="bi bi-images"></i> Galerie okolí</h5>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action active" onclick="showSection('photos')">
                        <i class="bi bi-grid-3x3"></i> Všechny fotky
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" onclick="showSection('add-photo')">
                        <i class="bi bi-plus-circle"></i> Přidat fotku
                    </a>
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-arrow-left"></i> Zpět na dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Seznam fotek -->
            <div id="photos-section" class="admin-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-images"></i> Galerie fotek okolí</h2>
                    <button class="btn btn-primary" onclick="showSection('add-photo')">
                        <i class="bi bi-plus"></i> Nová fotka
                    </button>
                </div>

                <?php if (empty($photos)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Zatím nejsou nahrané žádné fotky. <a href="#" onclick="showSection('add-photo')" class="alert-link">Přidejte první fotku</a>.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($photos as $photo): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100">
                                    <div class="position-relative">
                                        <img src="../<?= htmlspecialchars($photo['file_path']) ?>" 
                                             class="card-img-top" 
                                             alt="<?= htmlspecialchars($photo['alt_text'] ?: $photo['title']) ?>"
                                             style="height: 200px; object-fit: cover;">
                                        
                                        <!-- Badges -->
                                        <div class="position-absolute top-0 start-0 p-2">
                                            <?php if ($photo['is_featured']): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-star-fill"></i> Hlavní
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if (!$photo['is_published']): ?>
                                                <span class="badge bg-secondary">Skryto</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($photo['title']) ?></h5>
                                        
                                        <?php if ($photo['description']): ?>
                                            <p class="card-text text-muted small">
                                                <?= htmlspecialchars(substr($photo['description'], 0, 100)) ?>
                                                <?= strlen($photo['description']) > 100 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="small text-muted mb-2">
                                            <i class="bi bi-image"></i> <?= $photo['image_width'] ?>×<?= $photo['image_height'] ?>px
                                            <br>
                                            <i class="bi bi-calendar"></i> <?= date('j.n.Y H:i', strtotime($photo['created_at'])) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <div class="btn-group btn-group-sm w-100">
                                            <button class="btn btn-outline-warning" 
                                                    onclick="toggleFeatured(<?= $photo['id'] ?>)"
                                                    title="<?= $photo['is_featured'] ? 'Odstranit z hlavních' : 'Nastavit jako hlavní' ?>">
                                                <i class="bi bi-star<?= $photo['is_featured'] ? '-fill' : '' ?>"></i>
                                            </button>
                                            
                                            <button class="btn btn-outline-<?= $photo['is_published'] ? 'success' : 'secondary' ?>" 
                                                    onclick="togglePublished(<?= $photo['id'] ?>)"
                                                    title="<?= $photo['is_published'] ? 'Skrýt fotku' : 'Publikovat fotku' ?>">
                                                <i class="bi bi-eye<?= $photo['is_published'] ? '' : '-slash' ?>"></i>
                                            </button>
                                            
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deletePhoto(<?= $photo['id'] ?>, '<?= htmlspecialchars($photo['title']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Přidat fotku -->
            <div id="add-photo-section" class="admin-section" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-plus-circle"></i> Přidat novou fotku</h2>
                    <button class="btn btn-secondary" onclick="showSection('photos')">
                        <i class="bi bi-arrow-left"></i> Zpět na přehled
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add-photo">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Název fotky *</label>
                                    <input type="text" class="form-control" name="photo_title" required maxlength="255">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Pořadí</label>
                                    <input type="number" class="form-control" name="sort_order" value="0" min="0">
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                                        <label class="form-check-label" for="is_featured">
                                            Hlavní fotka
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Popis</label>
                                    <textarea class="form-control" name="photo_description" rows="3"></textarea>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Alt text (pro přístupnost)</label>
                                    <input type="text" class="form-control" name="alt_text" maxlength="255">
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">Soubor fotky *</label>
                                    <input type="file" class="form-control" name="photo_file" required accept="image/*">
                                    <div class="form-text">
                                        Povolené formáty: JPEG, PNG, GIF, WebP. Maximální velikost: 5MB.
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <hr>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload"></i> Nahrát fotku
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="showSection('photos')">
                                        Zrušit
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pro potvrzení smazání -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Potvrzení smazání</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Opravdu chcete smazat fotku "<span id="delete-photo-title"></span>"?</p>
                <p class="text-danger"><small>Tato akce je nevratná a smaže i soubor z disku.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete-photo">
                    <input type="hidden" name="photo_id" id="delete-photo-id">
                    <button type="submit" class="btn btn-danger">Smazat fotku</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(section) {
    // Skrýt všechny sekce
    document.querySelectorAll('.admin-section').forEach(s => s.style.display = 'none');
    
    // Zobrazit vybranou sekci
    document.getElementById(section + '-section').style.display = 'block';
    
    // Aktualizovat aktivní položku v menu
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.classList.remove('active');
    });
    event.target.classList.add('active');
}

function deletePhoto(id, title) {
    document.getElementById('delete-photo-id').value = id;
    document.getElementById('delete-photo-title').textContent = title;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleFeatured(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'toggle-featured';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'photo_id';
    idInput.value = id;
    
    form.appendChild(actionInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}

function togglePublished(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'toggle-published';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'photo_id';
    idInput.value = id;
    
    form.appendChild(actionInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}

// Zobrazit správnou sekci při načtení
document.addEventListener('DOMContentLoaded', function() {
    showSection('photos');
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>