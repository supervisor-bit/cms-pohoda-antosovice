<?php
require_once '../config.php';
require_once 'includes/admin_header.php';

// Kontrola přihlášení
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Zpracování formulářů
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
                // Vytvoření nebo editace akce
                $title = $_POST['title'];
                $description = $_POST['description'] ?? '';
                $content = $_POST['content'] ?? '';
                $start_date = $_POST['start_date'];
                $start_time = $_POST['start_time'] ?? null;
                $end_date = $_POST['end_date'] ?? $start_date;
                $end_time = $_POST['end_time'] ?? null;
                $location = $_POST['location'] ?? '';
                $category = $_POST['category'] ?? 'general';
                $is_all_day = isset($_POST['is_all_day']) ? 1 : 0;
                $is_published = isset($_POST['is_published']) ? 1 : 0;
                
                // Vytvoření slug
                $slug = createSlug($title);
                
                if ($_POST['action'] === 'add') {
                    $stmt = $pdo->prepare("
                        INSERT INTO events (title, description, content, start_date, start_time, 
                                          end_date, end_time, location, category, is_all_day, 
                                          is_published, slug) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$title, $description, $content, $start_date, $start_time, 
                                  $end_date, $end_time, $location, $category, $is_all_day,
                                  $is_published, $slug]);
                    $success = "Akce byla úspěšně vytvořena.";
                } else {
                    $id = $_POST['id'];
                    $stmt = $pdo->prepare("
                        UPDATE events 
                        SET title=?, description=?, content=?, start_date=?, start_time=?, 
                            end_date=?, end_time=?, location=?, category=?, is_all_day=?, 
                            is_published=?, slug=?
                        WHERE id=?
                    ");
                    $stmt->execute([$title, $description, $content, $start_date, $start_time, 
                                  $end_date, $end_time, $location, $category, $is_all_day,
                                  $is_published, $slug, $id]);
                    $success = "Akce byla úspěšně aktualizována.";
                }
            } elseif ($_POST['action'] === 'delete') {
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Akce byla smazána.";
            } elseif ($_POST['action'] === 'add-category') {
                $name = $_POST['category_name'];
                $color = $_POST['category_color'];
                $icon = $_POST['category_icon'] ?? '';
                $position = $_POST['position'] ?? 0;
                
                $stmt = $pdo->prepare("INSERT INTO event_categories (name, color, icon, position, is_active) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$name, $color, $icon, $position]);
                $success = "Kategorie byla úspěšně přidána.";
            } elseif ($_POST['action'] === 'delete-category') {
                $id = $_POST['category_id'];
                $stmt = $pdo->prepare("DELETE FROM event_categories WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Kategorie byla smazána.";
            }
        } catch (Exception $e) {
            $error = "Chyba: " . $e->getMessage();
        }
    }
}

// Načtení akcí
$events = [];
try {
    $stmt = $pdo->query("
        SELECT e.*, c.name as category_name, c.color as category_color 
        FROM events e 
        LEFT JOIN event_categories c ON e.category = c.name 
        ORDER BY e.start_date DESC, e.start_time DESC
    ");
    $events = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Chyba při načítání akcí: " . $e->getMessage();
}

// Načtení kategorií
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM event_categories WHERE is_active = 1 ORDER BY position");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [
        ['name' => 'Workshopy', 'color' => '#28a745'],
        ['name' => 'Sportovní akce', 'color' => '#007bff'],
        ['name' => 'Kulturní akce', 'color' => '#6f42c1']
    ];
}

// Načtení konkrétní akce pro editaci
$editEvent = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editEvent = $stmt->fetch();
}

function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="admin-sidebar">
                <h5><i class="bi bi-calendar-event"></i> Správa akcí</h5>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action active" onclick="showSection('events')">
                        <i class="bi bi-calendar-week"></i> Všechny akce
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" onclick="showSection('add-event')">
                        <i class="bi bi-plus-circle"></i> Přidat akci
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" onclick="showSection('categories')">
                        <i class="bi bi-tags"></i> Kategorie
                    </a>
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-arrow-left"></i> Zpět na dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Seznam akcí -->
            <div id="events-section" class="admin-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-calendar-week"></i> Správa akcí</h2>
                    <button class="btn btn-primary" onclick="showSection('add-event')">
                        <i class="bi bi-plus"></i> Nová akce
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Název akce</th>
                                <th>Datum</th>
                                <th>Čas</th>
                                <th>Kategorie</th>
                                <th>Status</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($event['title']) ?></strong>
                                        <?php if ($event['location']): ?>
                                            <br><small class="text-muted">📍 <?= htmlspecialchars($event['location']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('j.n.Y', strtotime($event['start_date'])) ?>
                                        <?php if ($event['end_date'] && $event['end_date'] != $event['start_date']): ?>
                                            - <?= date('j.n.Y', strtotime($event['end_date'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($event['is_all_day']): ?>
                                            <span class="badge bg-info">Celý den</span>
                                        <?php else: ?>
                                            <?= $event['start_time'] ? date('H:i', strtotime($event['start_time'])) : '' ?>
                                            <?= $event['end_time'] ? ' - ' . date('H:i', strtotime($event['end_time'])) : '' ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($event['category_color']): ?>
                                            <span class="badge" style="background-color: <?= $event['category_color'] ?>">
                                                <?= htmlspecialchars($event['category_name'] ?? $event['category']) ?>
                                            </span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($event['category']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($event['is_published']): ?>
                                            <span class="badge bg-success">Publikováno</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Koncept</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?= $event['id'] ?>" class="btn btn-outline-primary" onclick="editEvent(<?= $event['id'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" onclick="deleteEvent(<?= $event['id'] ?>, '<?= htmlspecialchars($event['title']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-calendar-x"></i><br>
                                        Zatím nejsou vytvořeny žádné akce.<br>
                                        <button class="btn btn-primary mt-2" onclick="showSection('add-event')">
                                            Přidat první akci
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Formulář pro přidání/editaci akce -->
            <div id="add-event-section" class="admin-section" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-calendar-plus"></i> <?= $editEvent ? 'Editace akce' : 'Nová akce' ?></h2>
                    <button class="btn btn-secondary" onclick="showSection('events')">
                        <i class="bi bi-arrow-left"></i> Zpět na seznam
                    </button>
                </div>

                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="<?= $editEvent ? 'edit' : 'add' ?>">
                    <?php if ($editEvent): ?>
                        <input type="hidden" name="id" value="<?= $editEvent['id'] ?>">
                    <?php endif; ?>

                    <!-- Základní informace -->
                    <div class="col-12">
                        <h5>Základní informace</h5>
                        <hr>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Název akce *</label>
                        <input type="text" class="form-control" name="title" required 
                               value="<?= $editEvent ? htmlspecialchars($editEvent['title']) : '' ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kategorie</label>
                        <select class="form-select" name="category">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['name']) ?>" 
                                        <?= ($editEvent && $editEvent['category'] === $cat['name']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Krátký popis</label>
                        <textarea class="form-control" name="description" rows="3"><?= $editEvent ? htmlspecialchars($editEvent['description']) : '' ?></textarea>
                    </div>

                    <!-- Datum a čas -->
                    <div class="col-12 mt-4">
                        <h5>Datum a čas</h5>
                        <hr>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Začátek - datum *</label>
                        <input type="date" class="form-control" name="start_date" required 
                               value="<?= $editEvent ? $editEvent['start_date'] : '' ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Začátek - čas</label>
                        <input type="time" class="form-control" name="start_time" 
                               value="<?= $editEvent ? $editEvent['start_time'] : '' ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Konec - datum</label>
                        <input type="date" class="form-control" name="end_date" 
                               value="<?= $editEvent ? $editEvent['end_date'] : '' ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Konec - čas</label>
                        <input type="time" class="form-control" name="end_time" 
                               value="<?= $editEvent ? $editEvent['end_time'] : '' ?>">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_all_day" id="is_all_day"
                                   <?= ($editEvent && $editEvent['is_all_day']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_all_day">
                                Celodenní akce
                            </label>
                        </div>
                    </div>

                    <!-- Místo a další detaily -->
                    <div class="col-12 mt-4">
                        <h5>Další detaily</h5>
                        <hr>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Místo konání</label>
                        <input type="text" class="form-control" name="location" 
                               value="<?= $editEvent ? htmlspecialchars($editEvent['location']) : '' ?>">
                    </div>

                    <!-- Obsah -->
                    <div class="col-12 mt-4">
                        <label class="form-label">Detailní popis</label>
                        <textarea class="form-control" name="content" rows="10"><?= $editEvent ? htmlspecialchars($editEvent['content']) : '' ?></textarea>
                    </div>

                    <!-- Publikování -->
                    <div class="col-12 mt-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_published" 
                                   <?= (!$editEvent || $editEvent['is_published']) ? 'checked' : '' ?>>
                            <label class="form-check-label">
                                Publikovat akci
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> <?= $editEvent ? 'Uložit změny' : 'Vytvořit akci' ?>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="showSection('events')">
                            Zrušit
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sekce Kategorie -->
            <div id="categories-section" class="admin-section" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-tags"></i> Správa kategorií akcí</h2>
                </div>
                
                <!-- Přidat novou kategorii -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-plus-circle"></i> Přidat novou kategorii</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add-category">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Název kategorie</label>
                                    <input type="text" class="form-control" name="category_name" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Barva</label>
                                    <input type="color" class="form-control" name="category_color" value="#2d5016">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Ikona (FontAwesome)</label>
                                    <input type="text" class="form-control" name="category_icon" placeholder="calendar">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Pozice</label>
                                    <input type="number" class="form-control" name="position" value="0">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Přidat kategorii
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Existující kategorie -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bi bi-list"></i> Existující kategorie</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Název</th>
                                        <th>Barva</th>
                                        <th>Ikona</th>
                                        <th>Pozice</th>
                                        <th>Status</th>
                                        <th width="150">Akce</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($category['name']) ?></td>
                                            <td>
                                                <span class="badge" style="background-color: <?= htmlspecialchars($category['color']) ?>; color: white;">
                                                    <?= htmlspecialchars($category['color']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($category['icon']): ?>
                                                    <i class="fas fa-<?= htmlspecialchars($category['icon']) ?>"></i> <?= htmlspecialchars($category['icon']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Žádná</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-info"><?= $category['position'] ?></span></td>
                                            <td>
                                                <?php if ($category['is_active']): ?>
                                                    <span class="badge bg-success">Aktivní</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Neaktivní</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="editCategory(<?= $category['id'] ?? 0 ?>)" title="Editovat">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="deleteCategory(<?= $category['id'] ?? 0 ?>, '<?= htmlspecialchars($category['name']) ?>')" title="Smazat">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="bi bi-tag"></i><br>
                                                Zatím nejsou vytvořeny žádné kategorie.<br>
                                                <button class="btn btn-primary mt-2" type="button">
                                                    Přidat první kategorii
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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
                <p>Opravdu chcete smazat akci "<span id="delete-event-title"></span>"?</p>
                <p class="text-danger"><small>Tato akce je nevratná.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-event-id">
                    <button type="submit" class="btn btn-danger">Smazat akci</button>
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

function editEvent(id) {
    // Přesměrování je už v href, ale můžeme přidat JS logiku
    showSection('add-event');
}

function deleteEvent(id, title) {
    document.getElementById('delete-event-id').value = id;
    document.getElementById('delete-event-title').textContent = title;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function editCategory(id) {
    // TODO: Implementovat editaci kategorie
    alert('Editace kategorií bude implementována v další verzi.');
}

function deleteCategory(id, name) {
    if (confirm('Opravdu chcete smazat kategorii "' + name + '"?\n\nPozor: Všechny akce v této kategorii budou přesunuty do výchozí kategorie.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete-category';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'category_id';
        idInput.value = id;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Zobrazit správnou sekci při načtení
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($editEvent): ?>
        showSection('add-event');
    <?php else: ?>
        showSection('events');
    <?php endif; ?>
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>