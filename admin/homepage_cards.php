<?php
require_once '../config.php';
require_once 'includes/admin_header.php';

// Kontrola přihlášení
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Načtení všech publikovaných stránek pro výběr odkazu
try {
    $pagesStmt = $pdo->query("SELECT slug, title FROM pages WHERE is_published = 1 ORDER BY title ASC");
    $availablePages = $pagesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $availablePages = [];
}

// Zpracování akcí
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    $stmt = $pdo->prepare("INSERT INTO homepage_cards (title, description, button_text, button_link, icon, position, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['button_text'],
                        $_POST['button_link'],
                        $_POST['icon'],
                        $_POST['position'],
                        isset($_POST['is_active']) ? 1 : 0
                    ]);
                    $_SESSION['success'] = 'Karta byla úspěšně přidána.';
                    break;
                    
                case 'edit':
                    $stmt = $pdo->prepare("UPDATE homepage_cards SET title = ?, description = ?, button_text = ?, button_link = ?, icon = ?, position = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['button_text'],
                        $_POST['button_link'],
                        $_POST['icon'],
                        $_POST['position'],
                        isset($_POST['is_active']) ? 1 : 0,
                        $_POST['id']
                    ]);
                    $_SESSION['success'] = 'Karta byla úspěšně aktualizována.';
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM homepage_cards WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $_SESSION['success'] = 'Karta byla úspěšně smazána.';
                    break;
            }
            header('Location: homepage_cards.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Chyba: ' . $e->getMessage();
        }
    }
}

// Načtení karet
$stmt = $pdo->query("SELECT * FROM homepage_cards ORDER BY position ASC");
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

        <div class="admin-page-header">
            <div class="page-title-section">
                <h1><i class="fas fa-th-large me-2"></i>Správa karet na homepage</h1>
                <p class="page-subtitle">Zde můžete spravovat karty zobrazené na hlavní stránce</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addCardModal">
                    <i class="fas fa-plus me-2"></i>Přidat kartu
                </button>
            </div>
        </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Pozice</th>
                            <th>Titulek</th>
                            <th>Popisek</th>
                            <th>Tlačítko</th>
                            <th>Odkaz</th>
                            <th>Ikona</th>
                            <th>Stav</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cards as $card): ?>
                        <tr>
                            <td><?= $card['position'] ?></td>
                            <td><strong><?= htmlspecialchars($card['title']) ?></strong></td>
                            <td><?= htmlspecialchars(mb_substr($card['description'], 0, 60)) ?>...</td>
                            <td><?= htmlspecialchars($card['button_text']) ?></td>
                            <td><small><?= htmlspecialchars($card['button_link']) ?></small></td>
                            <td><i class="fas <?= htmlspecialchars($card['icon']) ?>"></i></td>
                            <td>
                                <?php if ($card['is_active']): ?>
                                    <span class="badge bg-success">Aktivní</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Neaktivní</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editCard(<?= htmlspecialchars(json_encode($card)) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Opravdu chcete smazat tuto kartu?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $card['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.icon-picker {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
    max-height: 300px;
    overflow-y: auto;
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
}
.icon-option {
    text-align: center;
    padding: 15px 10px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    background: white;
}
.icon-option:hover {
    border-color: #6f9183;
    background: #f0f7f5;
    transform: translateY(-2px);
}
.icon-option.selected {
    border-color: #6f9183;
    background: #e8f4f0;
    font-weight: bold;
}
.icon-option i {
    font-size: 24px;
    color: #6f9183;
    margin-bottom: 5px;
}
.icon-option small {
    display: block;
    font-size: 10px;
    color: #666;
    margin-top: 5px;
}
.selected-icon-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    border: 2px solid #6f9183;
    border-radius: 8px;
    background: #e8f4f0;
    margin-bottom: 15px;
}
.selected-icon-preview i {
    font-size: 32px;
    color: #6f9183;
}
</style>

<!-- Modal pro přidání karty -->
<div class="modal fade" id="addCardModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Přidat novou kartu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="icon" id="add_icon_value" value="fa-arrow-right">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titulek</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Popisek</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Text tlačítka</label>
                            <input type="text" class="form-control" name="button_text" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Odkaz na stránku</label>
                            <select class="form-select" name="button_link" id="add_link_select" required>
                                <option value="">-- Vyberte stránku --</option>
                                <option value="#">Sociální sítě (speciální)</option>
                                <?php foreach ($availablePages as $page): ?>
                                    <option value="page_new.php?slug=<?= htmlspecialchars($page['slug']) ?>">
                                        <?= htmlspecialchars($page['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="posts.php">Všechny články</option>
                                <option value="events.php">Kalendář akcí</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vybraná ikona</label>
                        <div class="selected-icon-preview" id="add_icon_preview">
                            <i class="fas fa-arrow-right"></i>
                            <div>
                                <strong>fa-arrow-right</strong>
                                <br><small class="text-muted">Šipka vpravo</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vyberte ikonu</label>
                        <div class="icon-picker" id="add_icon_picker">
                            <!-- Icons will be loaded by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pozice (pořadí)</label>
                            <input type="number" class="form-control" name="position" value="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" class="form-check-input" id="is_active_add" name="is_active" checked>
                                <label class="form-check-label" for="is_active_add">Aktivní</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                    <button type="submit" class="btn btn-primary">Přidat kartu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pro editaci karty -->
<div class="modal fade" id="editCardModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upravit kartu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editCardForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="icon" id="edit_icon_value">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titulek</label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Popisek</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Text tlačítka</label>
                            <input type="text" class="form-control" name="button_text" id="edit_button_text" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Odkaz na stránku</label>
                            <select class="form-select" name="button_link" id="edit_link_select" required>
                                <option value="">-- Vyberte stránku --</option>
                                <option value="#">Sociální sítě (speciální)</option>
                                <?php foreach ($availablePages as $page): ?>
                                    <option value="page_new.php?slug=<?= htmlspecialchars($page['slug']) ?>">
                                        <?= htmlspecialchars($page['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="posts.php">Všechny články</option>
                                <option value="events.php">Kalendář akcí</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vybraná ikona</label>
                        <div class="selected-icon-preview" id="edit_icon_preview">
                            <i class="fas fa-arrow-right"></i>
                            <div>
                                <strong>fa-arrow-right</strong>
                                <br><small class="text-muted">Šipka vpravo</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vyberte ikonu</label>
                        <div class="icon-picker" id="edit_icon_picker">
                            <!-- Icons will be loaded by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pozice (pořadí)</label>
                            <input type="number" class="form-control" name="position" id="edit_position" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">Aktivní</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                    <button type="submit" class="btn btn-primary">Uložit změny</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Populární Font Awesome ikony pro karty
const popularIcons = [
    { icon: 'fa-arrow-right', name: 'Šipka vpravo' },
    { icon: 'fa-info-circle', name: 'Informace' },
    { icon: 'fa-envelope', name: 'Email' },
    { icon: 'fa-phone', name: 'Telefon' },
    { icon: 'fa-map-marker-alt', name: 'Místo' },
    { icon: 'fa-calendar-alt', name: 'Kalendář' },
    { icon: 'fa-users', name: 'Uživatelé' },
    { icon: 'fa-home', name: 'Domů' },
    { icon: 'fa-star', name: 'Hvězda' },
    { icon: 'fa-heart', name: 'Srdce' },
    { icon: 'fa-thumbs-up', name: 'Palec nahoru' },
    { icon: 'fa-check-circle', name: 'Zaškrtnuto' },
    { icon: 'fa-share-alt', name: 'Sdílet' },
    { icon: 'fa-camera', name: 'Fotoaparát' },
    { icon: 'fa-image', name: 'Obrázek' },
    { icon: 'fa-images', name: 'Galerie' },
    { icon: 'fa-video', name: 'Video' },
    { icon: 'fa-music', name: 'Hudba' },
    { icon: 'fa-bell', name: 'Zvonek' },
    { icon: 'fa-gift', name: 'Dárek' },
    { icon: 'fa-book', name: 'Kniha' },
    { icon: 'fa-newspaper', name: 'Noviny' },
    { icon: 'fa-file-alt', name: 'Dokument' },
    { icon: 'fa-download', name: 'Stáhnout' },
    { icon: 'fa-upload', name: 'Nahrát' },
    { icon: 'fa-search', name: 'Hledat' },
    { icon: 'fa-cog', name: 'Nastavení' },
    { icon: 'fa-list', name: 'Seznam' },
    { icon: 'fa-th-large', name: 'Mřížka' },
    { icon: 'fa-link', name: 'Odkaz' }
];

// Inicializace icon pickeru
function initIconPicker(pickerId, previewId, inputId, selectedIcon = 'fa-arrow-right') {
    const picker = document.getElementById(pickerId);
    const preview = document.getElementById(previewId);
    const input = document.getElementById(inputId);
    
    picker.innerHTML = '';
    
    popularIcons.forEach(item => {
        const option = document.createElement('div');
        option.className = 'icon-option' + (item.icon === selectedIcon ? ' selected' : '');
        option.innerHTML = `
            <i class="fas ${item.icon}"></i>
            <small>${item.name}</small>
        `;
        option.onclick = () => selectIcon(item.icon, item.name, pickerId, previewId, inputId);
        picker.appendChild(option);
    });
    
    // Nastavit preview
    updateIconPreview(selectedIcon, previewId);
}

function selectIcon(icon, name, pickerId, previewId, inputId) {
    // Aktualizovat hidden input
    document.getElementById(inputId).value = icon;
    
    // Aktualizovat preview
    updateIconPreview(icon, previewId);
    
    // Označit vybranou ikonu
    document.querySelectorAll(`#${pickerId} .icon-option`).forEach(opt => {
        opt.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
}

function updateIconPreview(icon, previewId) {
    const preview = document.getElementById(previewId);
    const iconData = popularIcons.find(i => i.icon === icon) || { icon: icon, name: icon };
    preview.innerHTML = `
        <i class="fas ${iconData.icon}"></i>
        <div>
            <strong>${iconData.icon}</strong>
            <br><small class="text-muted">${iconData.name}</small>
        </div>
    `;
}

// Inicializace při otevření modálů
document.getElementById('addCardModal').addEventListener('shown.bs.modal', function() {
    initIconPicker('add_icon_picker', 'add_icon_preview', 'add_icon_value', 'fa-arrow-right');
});

function editCard(card) {
    document.getElementById('edit_id').value = card.id;
    document.getElementById('edit_title').value = card.title;
    document.getElementById('edit_description').value = card.description;
    document.getElementById('edit_button_text').value = card.button_text;
    document.getElementById('edit_link_select').value = card.button_link;
    document.getElementById('edit_icon_value').value = card.icon;
    document.getElementById('edit_position').value = card.position;
    document.getElementById('edit_is_active').checked = card.is_active == 1;
    
    // Inicializovat icon picker s vybranou ikonou
    initIconPicker('edit_icon_picker', 'edit_icon_preview', 'edit_icon_value', card.icon);
    
    new bootstrap.Modal(document.getElementById('editCardModal')).show();
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
